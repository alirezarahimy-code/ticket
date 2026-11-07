<?php
declare(strict_types=1);

/**
 * Expert work scheduling for the Request Center (phase 3).
 *
 * Table: request_center_appointments (migration 035).
 * - Appointments belong to a ticket and an assignee; they never change the
 *   ticket status, SLA or OLA clocks by themselves.
 * - Conflicts are detected inside a transaction with FOR UPDATE on InnoDB
 *   (MariaDB 10.4+ in production/CI; SQLite path used by offline tests has no
 *   row locking and relies on same-transaction consistency instead).
 */
final class AppointmentRepository
{
    public const KINDS = [
        'work'     => 'رسیدگی / اقدام',
        'visit'    => 'بازدید حضوری',
        'await'    => 'انتظار پاسخ',
        'followup' => 'پیگیری',
    ];

    public const STATUS_PLANNED = 'planned';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Roles allowed to be chosen as appointment assignee (module policy).
     * Single source of truth for both the interactive staff list
     * (assignableStaff) and the POST-side validation in the calendar handler.
     * Superset of core staff_role_codes() (adds 'admin' and 'department_manager')
     * to keep the historical module list visible; primary_admin already covers
     * administrators.
     */
    public const ASSIGNABLE_ROLES = [
        'agent', 'manager', 'support_manager', 'supervisor', 'admin', 'primary_admin', 'department_manager', 'inspector',
    ];

    /** @return string[] */
    public static function assignableRoleCodes(): array
    {
        return self::ASSIGNABLE_ROLES;
    }

    public function __construct(private PDO $pdo)
    {
    }

    public function tableExists(): bool
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT 1 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'request_center_appointments'"
            );
            $st->execute();
            return (bool) $st->fetchColumn();
        } catch (Throwable) {
            try {
                $st = $this->pdo->query(
                    "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'request_center_appointments'"
                );
                return $st ? (bool) $st->fetchColumn() : false;
            } catch (Throwable) {
                return false;
            }
        }
    }

    /** '2026-11-07T14:30' (datetime-local) → '2026-11-07 14:30:00' */
    public static function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})[ ]?(\d{2}):(\d{2})(:(\d{2}))?$/', $value, $m) !== 1) {
            return null;
        }
        $y = (int) $m[1]; $mo = (int) $m[2]; $d = (int) $m[3];
        $h = (int) $m[4]; $i = (int) $m[5]; $s = isset($m[7]) ? (int) $m[7] : 0;
        if (!checkdate($mo, $d, $y)) {
            // تاریخ نامعتبر (مثل ۳۱ فوریه) را reject کن؛ createFromFormat آن را silent می‌کرد.
            return null;
        }
        if ($h > 23 || $i > 59 || $s > 59) {
            return null;
        }
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $y, $mo, $d, $h, $i, $s);
    }

    /**
     * Create an appointment. Caller MUST have already checked that the user
     * may see the ticket and schedule for the target assignee.
     *
     * @return array{ok: bool, id?: int, error?: string}
     */
    public function create(int $ticketId, int $assigneeId, string $startsAt, string $endsAt, string $kind, ?string $note, int $actorId): array
    {
        if (!isset(self::KINDS[$kind])) {
            return ['ok' => false, 'error' => 'نوع نوبت نامعتبر است.'];
        }
        $startTs = strtotime($startsAt);
        $endTs = strtotime($endsAt);
        if (!$startTs || !$endTs) {
            return ['ok' => false, 'error' => 'زمان شروع یا پایان نامعتبر است.'];
        }
        if ($endTs <= $startTs) {
            return ['ok' => false, 'error' => 'پایان نوبت باید بعد از شروع آن باشد.'];
        }
        if (($endTs - $startTs) > 8 * 3600) {
            return ['ok' => false, 'error' => 'طول هر نوبت حداکثر ۸ ساعت می‌تواند باشد.'];
        }
        if ($startTs < time() - 3600) {
            return ['ok' => false, 'error' => 'زمان شروع نمی‌تواند در گذشته باشد.'];
        }
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        $note = trim((string) $note);
        if (mb_strlen($note) > 500) {
            $note = mb_substr($note, 0, 500);
        }
        $note = $note === '' ? null : $note;

        try {
            $this->pdo->beginTransaction();

            // Locking point واقعی هم‌زمانی: قفل روی ردیف کاربر «مسئول» در همان تراکنش.
            // چرا کافی نیست روی request_center_appointments با LIMIT 1 FOR UPDATE تکیه کنیم؟
            // - در حالت صفر ردیف هم‌پوشان (اولین نوبت کارشناس)، دو تراکنش روی گپ بعدی/قبلی
            //   قفل‌های گپ می‌گیرند که با هم سازگارند؛ هر دو INSERT می‌کنند و صف‌بندی insert-intention
            //   باعث deadlock/timeout می‌شود (یا بدتر: باREAD COMMITTED که گپ‌لاک ندارد، هیچ کدام).
            // - قفل ردیف والد (users) نقطهٔ سریال‌سازی قطعی می‌سازد: تراکنش دوم صبر می‌کند تا
            //   اولی commit کند؛ سپس در خواندن قفل‌دار (FOR UPDATE) آخرین نگارش ثبت‌شده را می‌بیند
            //   و باهمان predicate «هر نوبت کانسل‌نشده هم‌پوشان است؟» رد می‌شود. رفتار
            //   همهٔ سطوح ایزولیشن یکسان است.
            try {
                $lock = $this->pdo->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
                $lock->execute([$assigneeId]);
            } catch (Throwable) {
                // SQLite: قفل ردیف موجود نیست؛ fixture تک‌نخی است و تست رفتار را پاس می‌کند.
                $lock = $this->pdo->prepare('SELECT id FROM users WHERE id = ?');
                $lock->execute([$assigneeId]);
            }
            if ((int) $lock->fetchColumn() <= 0) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'کارشناس هدف معتبر نیست یا حساب او فعال نیست.'];
            }

            $conflictSql = 'SELECT id FROM request_center_appointments
                            WHERE assignee_id = ? AND status <> ?
                              AND starts_at < ? AND ends_at > ? LIMIT 1 FOR UPDATE';
            try {
                $conflict = $this->pdo->prepare($conflictSql);
                $conflict->execute([$assigneeId, self::STATUS_CANCELLED, $endsAt, $startsAt]);
            } catch (Throwable) {
                // SQLite (offline tests) has no FOR UPDATE — same predicate without the lock.
                $conflict = $this->pdo->prepare('SELECT id FROM request_center_appointments
                    WHERE assignee_id = ? AND status <> ? AND starts_at < ? AND ends_at > ? LIMIT 1');
                $conflict->execute([$assigneeId, self::STATUS_CANCELLED, $endsAt, $startsAt]);
            }
            if ($conflict->fetchColumn() !== false) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'این بازه با نوبت فعال دیگری روی تقویم کارشناس تداخل دارد.'];
            }

            $ins = $this->pdo->prepare('INSERT INTO request_center_appointments
                (ticket_id, assignee_id, starts_at, ends_at, kind, status, note, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$ticketId, $assigneeId, $startsAt, $endsAt, $kind, self::STATUS_PLANNED, $note, $actorId]);
            $id = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return ['ok' => true, 'id' => $id];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'ثبت نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /** Next planned appointments (for the signed-in expert, or everyone when $canManageAll).
     *
     * @internal مصرف‌کننده‌های فعلی فقط تست SQLite ماژول هستند
     *           (tests/test_request_center_appointments_sqlite.php)؛ مسیرهای محصول از listRange() استفاده می‌کنند.
     */
    public function listUpcoming(?int $assigneeId, string $now, int $limit = 14): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        $limit = max(1, min(50, $limit));
        $where = "WHERE a.status = ? AND a.starts_at >= ?";
        $params = [self::STATUS_PLANNED, $now];
        if ($assigneeId !== null) {
            $where .= ' AND a.assignee_id = ?';
            $params[] = $assigneeId;
        }
        $st = $this->pdo->prepare("SELECT a.*, t.ticket_no, t.subject, u.full_name AS assignee_name
            FROM request_center_appointments a
            LEFT JOIN tickets t ON t.id = a.ticket_id
            LEFT JOIN users u ON u.id = a.assignee_id
            {$where}
            ORDER BY a.starts_at ASC
            LIMIT {$limit}");
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Cancel an appointment. Owner may cancel own; elevated users may cancel any. */
    public function cancel(int $appointmentId, int $actorId, bool $elevated): array
    {
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        try {
            $this->pdo->beginTransaction();
            try {
                $st = $this->pdo->prepare('SELECT assignee_id, status FROM request_center_appointments WHERE id = ? FOR UPDATE');
                $st->execute([$appointmentId]);
            } catch (Throwable) {
                // SQLite (offline tests) has no FOR UPDATE — same predicate without the lock.
                $st = $this->pdo->prepare('SELECT assignee_id, status FROM request_center_appointments WHERE id = ?');
                $st->execute([$appointmentId]);
            }
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'نوبت یافت نشد.'];
            }
            if ((string) $row['status'] !== self::STATUS_PLANNED) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'فقط نوبت «برنامه‌ریزی‌شده» قابل لغو است.'];
            }
            if (!$elevated && (int) $row['assignee_id'] !== $actorId) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'فقط مالک نوبت یا مدیر می‌تواند آن را لغو کند.'];
            }
            $upd = $this->pdo->prepare("UPDATE request_center_appointments SET status = ? WHERE id = ? AND status = ?");
            $upd->execute([self::STATUS_CANCELLED, $appointmentId, self::STATUS_PLANNED]);
            if ($upd->rowCount() > 0) {
                $this->pdo->commit();
                return ['ok' => true];
            }
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => 'لغو نوبت انجام نشد؛ مقدار وضعیت هم‌زمان تغییر کرد.'];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'لغو نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /**
     * Range listing with ticket + assignee join (used by calendar cells/agenda).
     *
     * @param array{kind?: string|null, status?: string|null} $filters
     */
    public function listRange(?int $assigneeId, string $fromDatetime, string $toDatetime, array $filters = []): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        $where = 'WHERE a.starts_at < ? AND a.ends_at > ?';
        $params = [$toDatetime, $fromDatetime];
        if ($assigneeId !== null) {
            $where .= ' AND a.assignee_id = ?';
            $params[] = $assigneeId;
        }
        $kind = trim((string) ($filters['kind'] ?? ''));
        if ($kind !== '' && isset(self::KINDS[$kind])) {
            $where .= ' AND a.kind = ?';
            $params[] = $kind;
        }
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $where .= ' AND a.status = ?';
            $params[] = $status;
        }
        $st = $this->pdo->prepare(
            "SELECT a.*, t.ticket_no, t.subject, u.full_name AS assignee_name
             FROM request_center_appointments a
             LEFT JOIN tickets t ON t.id = a.ticket_id
             LEFT JOIN users u ON u.id = a.assignee_id
             {$where}
             ORDER BY a.starts_at ASC, a.id ASC"
        );
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Team capacity: per-assignee appointment counts for a window. Planning view only;
     * does not influence dispatch or routing decisions.
     */
    public function capacitySummary(string $fromDatetime, string $toDatetime, ?string $now = null): array
    {
        if (!$this->tableExists()) {
            return [];
        }
        $now = $now ?? date('Y-m-d H:i:s');
        $st = $this->pdo->prepare(
            "SELECT a.assignee_id, u.full_name AS assignee_name,
                    COUNT(*) AS total,
                    SUM(a.kind = 'work') AS k_work,
                    SUM(a.kind = 'visit') AS k_visit,
                    SUM(a.kind = 'await') AS k_await,
                    SUM(a.kind = 'followup') AS k_followup,
                    SUM(a.status = 'done') AS done_count,
                    SUM(a.status = 'planned' AND a.ends_at < ?) AS overdue_planned
             FROM request_center_appointments a
             LEFT JOIN users u ON u.id = a.assignee_id
             WHERE a.status <> ? AND a.starts_at >= ? AND a.starts_at < ?
             GROUP BY a.assignee_id, u.full_name
             ORDER BY total DESC, a.assignee_id ASC"
        );
        $st->execute([$now, self::STATUS_CANCELLED, $fromDatetime, $toDatetime]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** KPI strip counters for the calendar page (visible scope-aware). */
    public function countsForRange(?int $assigneeId, string $fromDatetime, string $toDatetime, string $now): array
    {
        if (!$this->tableExists()) {
            return ['today' => 0, 'active' => 0, 'overdue' => 0, 'done' => 0];
        }
        $dayStart = date('Y-m-d 00:00:00', strtotime($now));
        $dayEnd = date('Y-m-d 23:59:59', strtotime($now));
        $scope = $assigneeId !== null ? ' AND assignee_id = ?' : '';
        $args = [];
        $st = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS active,
                SUM(starts_at < ? AND ends_at > ?) AS today,
                SUM(status = 'planned' AND ends_at < ?) AS overdue,
                SUM(status = 'done') AS done
             FROM request_center_appointments
             WHERE status <> 'cancelled' AND starts_at < ? AND ends_at > ?" . $scope
        );
        $params = [$dayEnd, $dayStart, $now, $toDatetime, $fromDatetime];
        if ($assigneeId !== null) {
            $params[] = $assigneeId;
        }
        $st->execute($params);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'active' => (int) ($row['active'] ?? 0),
            'today' => (int) ($row['today'] ?? 0),
            'overdue' => (int) ($row['overdue'] ?? 0),
            'done' => (int) ($row['done'] ?? 0),
        ];
    }

    /** Mark a planned appointment as done. Owner may complete own; elevated may complete any. */
    public function complete(int $appointmentId, int $actorId, bool $elevated): array
    {
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        try {
            $this->pdo->beginTransaction();
            try {
                $st = $this->pdo->prepare('SELECT assignee_id, status FROM request_center_appointments WHERE id = ? FOR UPDATE');
                $st->execute([$appointmentId]);
            } catch (Throwable) {
                // SQLite (offline tests) has no FOR UPDATE — same predicate without the lock.
                $st = $this->pdo->prepare('SELECT assignee_id, status FROM request_center_appointments WHERE id = ?');
                $st->execute([$appointmentId]);
            }
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'نوبت یافت نشد.'];
            }
            if ((string) $row['status'] !== self::STATUS_PLANNED) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'فقط نوبت «برنامه‌ریزی‌شده» قابل اتمام است.'];
            }
            if (!$elevated && (int) $row['assignee_id'] !== $actorId) {
                $this->pdo->rollBack();
                return ['ok' => false, 'error' => 'فقط مالک نوبت یا مدیر می‌تواند آن را اتمام کند.'];
            }
            $upd = $this->pdo->prepare("UPDATE request_center_appointments SET status = ? WHERE id = ? AND status = ?");
            $upd->execute([self::STATUS_DONE, $appointmentId, self::STATUS_PLANNED]);
            if ($upd->rowCount() > 0) {
                $this->pdo->commit();
                return ['ok' => true];
            }
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => 'اتمام نوبت انجام نشد؛ مقدار وضعیت هم‌زمان تغییر کرد.'];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'اتمام نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /** Titles for assignees shown in forms (active staff, bounded). */
    public function assignableStaff(int $limit = 200): array
    {
        // No module ownership of the users table; read-only via existing columns.
        try {
            $roles = self::assignableRoleCodes();
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $st = $this->pdo->prepare(
                "SELECT id, full_name, role FROM users
                 WHERE is_active = 1
                   AND role IN ($placeholders)
                 ORDER BY full_name ASC, id ASC
                 LIMIT " . max(1, min(500, $limit))
            );
            $st->execute($roles);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }
}
