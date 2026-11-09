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
    public function create(int $ticketId, int $assigneeId, string $startsAt, string $endsAt, string $kind, ?string $note, int $actorId, bool $allowOutsideBusinessHours = false): array
    {
        $pre = $this->precheckAppointment($startsAt, $endsAt, $kind, $note, $assigneeId, $allowOutsideBusinessHours);
        if (!$pre['ok']) {
            return ['ok' => false, 'error' => $pre['error']];
        }
        $note = $pre['note'];
        try {
            $this->pdo->beginTransaction();
            $result = $this->insertAppointmentWithinTransaction($ticketId, $assigneeId, $startsAt, $endsAt, $kind, $note, $actorId);
            if (!$result['ok']) {
                $this->pdo->rollBack();
                return $result;
            }
            $this->pdo->commit();
            return $result;
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'ثبت نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /**
     * ثبت گروهی نوبت‌ها با سیاست «همه یا هیچ» (تصمیم مالک ۲۰۲۶-۱۱-۱۰):
     *   ۱) همهٔ پیش‌بررسی‌ها (نوع/زمان/طول/گذشته/پنجرهٔ کاری/جدول/یادداشت) برای همهٔ ردیف‌ها
     *      بدون نوشتن انجام می‌شود؛ اگر حتی یک ردیف نامعتبر باشد، هیچ‌چیزی ثبت نمی‌شود و همهٔ
     *      خطاهای معنایی یک‌جا برمی‌گردند.
     *   ۲) درج‌ها در یک تراکنش واحد انجام می‌شود؛ شکست هر ردیف کل دسته را برمی‌گرداند
     *      (rollBack)، پس نوبت نیمه‌کاره باقی نمی‌ماند. بررسی تعارض هم‌پوشانی و سقف ظرفیت
     *      روزانه (که ردیف‌های این دسته را هم می‌بیند) در همان تراکنش اعمال می‌شود.
     *
     * @param array<int, array{ticket_id:int, assigneeId:int, startsAt:string, endsAt:string, kind:string, note:mixed, actorId:int, allowOutsideBusinessHours:bool}> $items
     * @return array{ok:bool, ids?:array<int,int>, error?:string, ticket_id?:int, errors?:array<int,string>}
     */
    public function createBatch(array $items): array
    {
        if ($items === []) {
            return ['ok' => true, 'ids' => []];
        }

        // ── فاز پیش‌بررسی (بدون نوشتن): جمع‌آوری همهٔ خطاهای معنایی ──
        $prechecked = [];
        $errors = [];
        foreach ($items as $item) {
            $ticketId = (int) ($item['ticket_id'] ?? 0);
            $pre = $this->precheckAppointment(
                (string) ($item['startsAt'] ?? ''),
                (string) ($item['endsAt'] ?? ''),
                (string) ($item['kind'] ?? ''),
                $item['note'] ?? null,
                (int) ($item['assigneeId'] ?? 0),
                (bool) ($item['allowOutsideBusinessHours'] ?? false)
            );
            if (!$pre['ok']) {
                $errors[] = 'درخواست #' . ($ticketId > 0 ? $ticketId : '?') . ': ' . $pre['error'];
                continue;
            }
            $prechecked[] = [
                'ticket_id' => $ticketId,
                'assigneeId' => (int) ($item['assigneeId'] ?? 0),
                'startsAt' => (string) $item['startsAt'],
                'endsAt' => (string) $item['endsAt'],
                'kind' => (string) $item['kind'],
                'note' => $pre['note'],
                'actorId' => (int) ($item['actorId'] ?? 0),
            ];
        }
        if ($errors !== []) {
            return ['ok' => false, 'error' => implode(' | ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? ' | و ' . (count($errors) - 3) . ' مورد دیگر' : ''), 'errors' => $errors];
        }

        // ── فاز درج در یک تراکنش؛ شکست هر مرحله کل دسته را برمی‌گرداند ──
        $this->pdo->beginTransaction();
        try {
            $ids = [];
            foreach ($prechecked as $entry) {
                $result = $this->insertAppointmentWithinTransaction(
                    $entry['ticket_id'], $entry['assigneeId'], $entry['startsAt'], $entry['endsAt'], $entry['kind'], $entry['note'], $entry['actorId']
                );
                if (!$result['ok']) {
                    $this->pdo->rollBack();
                    $reason = (string) ($result['error'] ?? 'ثبت نوبت انجام نشد.');
                    return ['ok' => false, 'error' => 'درخواست #' . $entry['ticket_id'] . ': ' . $reason, 'ticket_id' => $entry['ticket_id']];
                }
                $ids[] = (int) $result['id'];
            }
            $this->pdo->commit();
            return ['ok' => true, 'ids' => $ids];
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'ثبت نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /**
     * پیش‌بررسی‌های غیرتراکنشیِ ثبت نوبت (بدون نوشتن): نوع، زمان، طول، گذشته،
     * پنجرهٔ کاری کارشناس، وجود جدول، و نرمال‌سازی یادداشت.
     * @return array{ok:bool, note?:string|null, error?:string}
     */
    private function precheckAppointment(string $startsAt, string $endsAt, string $kind, $note, int $assigneeId, bool $allowOutsideBusinessHours): array
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

        // ——— پنجرهٔ ساعات کاری روز (پیش‌فرض سراسری از sla.*، override کارشناس از rc.work_hours_user_<id>) ———
        // رفتار: fail-closed؛ مدیر سامانه می‌تواند با پرچم allowOutsideBusinessHours پیاده شود.
        if (!$allowOutsideBusinessHours) {
            [$winStartMin, $winEndMin] = $this->workingWindow($assigneeId);
            $sameCalendarDay = date('Y-m-d', $startTs) === date('Y-m-d', $endTs);
            $startMin = (int) date('H', $startTs) * 60 + (int) date('i', $startTs);
            $endMin = (int) date('H', $endTs) * 60 + (int) date('i', $endTs);
            if (!$sameCalendarDay || $startMin < $winStartMin || $endMin > $winEndMin) {
                return ['ok' => false, 'error' => 'این نوبت خارج از ساعات کاری است (پنجرهٔ مجاز ' . $this->formatMinutes($winStartMin) . ' تا ' . $this->formatMinutes($winEndMin) . '). مدیر سامانه می‌تواند با «ثبت حتی بیرون از ساعت کاری» ثبت کند.'];
            }
        }
        if (!$this->tableExists()) {
            return ['ok' => false, 'error' => 'migration_035_missing'];
        }
        $note = trim((string) $note);
        if (mb_strlen($note) > 500) {
            $note = mb_substr($note, 0, 500);
        }
        $note = $note === '' ? null : $note;
        return ['ok' => true, 'note' => $note];
    }

    /**
     * بدنهٔ تراکنشیِ ثبت نوبت (قفل ردیف کارشناس، سقف ظرفیت روزانه، تعارض، درج).
     * بدون begin/commit/rollBack؛ مدیریت تراکنش بر عهدهٔ فراخوان است تا ثبت تکی و گروهی
     * هر دو از یک مسیر بگذرند. روی مغایرت‌ها فقط شکست برمی‌گرداند؛ برگرداندن تراکنش با فراخوان است.
     * @return array{ok:bool, id?:int, error?:string}
     */
    private function insertAppointmentWithinTransaction(int $ticketId, int $assigneeId, string $startsAt, string $endsAt, string $kind, ?string $note, int $actorId): array
    {
        $startTs = (int) strtotime($startsAt);
        try {
            // Locking point واقعی هم‌زمانی: قفل روی ردیف کاربر «مسئول» در همان تراکنش.
            // چرا کافی نیست روی request_center_appointments با LIMIT 1 FOR UPDATE تکیه کنیم؟
            // - در حالت صفر ردیف هم‌پوشان (اولین نوبت کارشناس)، دو تراکنش روی گپ بعدی/قبلی
            //   قفل‌های گپ می‌گیرند که با هم سازگارند؛ هر دو INSERT می‌کنند و صف‌بندی insert-intention
            //   باعث deadlock/timeout می‌شود (یا بدتر: باREAD COMMITTED که گپ‌لاک ندارد، هیچ کدام).
            // - قفل ردیف والد (users) نقطهٔ سریال‌سازی قطعی می‌سازد: تراکنش دوم صبر می‌کند تا
            //   اولی commit کند؛ سپس در خواندن قفل‌دار (FOR UPDATE) آخرین نگارش ثبت‌شده را می‌بیند
            //   و باهمان predicate «هر نوبت کانسل‌نشده هم‌پوشان است؟» رد می‌شود. رفتار
            //   همهٔ سطوح ایزولیشن یکسان است.
            $assigneeRow = null;
            try {
                $lock = $this->pdo->prepare('SELECT id, department_id FROM users WHERE id = ? FOR UPDATE');
                $lock->execute([$assigneeId]);
                $assigneeRow = $lock->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Throwable) {
                // SQLite یا fixture بدون قفل ردیف: تلاش دوم بدون FOR UPDATE ولی با ستون department_id
                try {
                    $lock = $this->pdo->prepare('SELECT id, department_id FROM users WHERE id = ?');
                    $lock->execute([$assigneeId]);
                    $assigneeRow = $lock->fetch(PDO::FETCH_ASSOC) ?: null;
                } catch (Throwable) {
                    // fixture قدیمی بدون ستون department_id — فقط وجود ردیف بررسی می‌شود
                    $lock = $this->pdo->prepare('SELECT id FROM users WHERE id = ?');
                    $lock->execute([$assigneeId]);
                    $assigneeRow = $lock->fetch(PDO::FETCH_ASSOC) ?: null;
                    if (is_array($assigneeRow)) {
                        $assigneeRow['department_id'] = null;
                    }
                }
            }
            if (!is_array($assigneeRow) || (int) ($assigneeRow['id'] ?? 0) <= 0) {
                return ['ok' => false, 'error' => 'کارشناس هدف معتبر نیست یا حساب او فعال نیست.'];
            }
            $assigneeDepartmentId = isset($assigneeRow['department_id']) && (int) $assigneeRow['department_id'] > 0
                ? (int) $assigneeRow['department_id']
                : null;

            // ——— سقف ظرفیت روزانه (همان‌جا که سریال‌سازی وجود دارد) ———
            $dayStart = date('Y-m-d 00:00:00', $startTs);
            $dayEnd = date('Y-m-d 23:59:59', $startTs);
            $limits = $this->dailyLimits($assigneeDepartmentId);
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM request_center_appointments WHERE assignee_id = ? AND status <> ? AND starts_at >= ? AND starts_at <= ?"
            );
            $countStmt->execute([$assigneeId, self::STATUS_CANCELLED, $dayStart, $dayEnd]);
            if ((int) $countStmt->fetchColumn() >= $limits['assignee']) {
                return ['ok' => false, 'error' => "ظرفیت روزانهٔ این کارشناس برای این تاریخ تکمیل است (سقف {$limits['assignee']} نوبت)."];
            }
            if ($limits['team'] > 0 && $assigneeDepartmentId !== null) {
                $teamStmt = $this->pdo->prepare(
                    "SELECT COUNT(*) FROM request_center_appointments a
                     INNER JOIN users u ON u.id = a.assignee_id
                     WHERE u.department_id = ? AND a.status <> ? AND a.starts_at >= ? AND a.starts_at <= ?"
                );
                $teamStmt->execute([$assigneeDepartmentId, self::STATUS_CANCELLED, $dayStart, $dayEnd]);
                if ((int) $teamStmt->fetchColumn() >= $limits['team']) {
                    return ['ok' => false, 'error' => "ظرفیت روزانهٔ تیم شما برای این تاریخ تکمیل است (سقف {$limits['team']} نوبت)."];
                }
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
                return ['ok' => false, 'error' => 'این بازه با نوبت فعال دیگری روی تقویم کارشناس تداخل دارد.'];
            }

            $ins = $this->pdo->prepare('INSERT INTO request_center_appointments
                (ticket_id, assignee_id, starts_at, ends_at, kind, status, note, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$ticketId, $assigneeId, $startsAt, $endsAt, $kind, self::STATUS_PLANNED, $note, $actorId]);
            $id = (int) $this->pdo->lastInsertId();
            return ['ok' => true, 'id' => $id];
        } catch (Throwable) {
            return ['ok' => false, 'error' => 'ثبت نوبت انجام نشد؛ لطفاً دوباره تلاش کنید.'];
        }
    }

    /**
     * پنجرهٔ ساعات کاری روز برای کارشناس — override تخصصی (rc.work_hours_user_<id> = 'HH:MM-HH:MM')
     * در صورت نبود/نامعتبر، به‌صورت fail-closed به پنجرهٔ سراسری sla.work_start_hour/sla.work_end_hour برمی‌گردد.
     *
     * @return array{0:int,1:int} دقیقه‌های شروع و پایان (مثلاً [480, 960])
     */
    private function workingWindow(int $assigneeId): array
    {
        $override = $this->rcSetting('rc.work_hours_user_' . $assigneeId);
        if ($override !== null
            && preg_match('/^([01]?\d|2[0-3]):([0-5]\d)\s*-\s*([01]?\d|2[0-4]):([0-5]\d)$/', trim($override), $m)
        ) {
            $startMin = ((int) $m[1]) * 60 + ((int) $m[2]);
            $endMin = ((int) $m[3]) * 60 + ((int) $m[4]);
            if ($endMin > $startMin) {
                return [$startMin, $endMin];
            }
        }
        $startHour = (int) ($this->rcSetting('sla.work_start_hour') ?? 8);
        $endHour = (int) ($this->rcSetting('sla.work_end_hour') ?? 16);
        if ($startHour < 0 || $endHour > 24 || $endHour <= $startHour) {
            $startHour = 8;
            $endHour = 16;
        }
        return [$startHour * 60, $endHour * 60];
    }

    private function formatMinutes(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * سقف ظرفیت روزانه: کارشناس (rc.daily_capacity_assignee، پیش‌فرض ۸) و تیم تجمعی
     * (rc.team_daily_capacity_dept_<department_id> با fallback به rc.team_daily_capacity، پیش‌فرض ۲۴؛ صفر = غیرفعال).
     *
     * @return array{assignee:int, team:int}
     */
    private function dailyLimits(?int $departmentId): array
    {
        $assigneeRaw = $this->rcSetting('rc.daily_capacity_assignee');
        $assignee = ($assigneeRaw !== null && is_numeric(trim($assigneeRaw))) ? max(1, (int) $assigneeRaw) : 8;

        $teamRaw = null;
        if ($departmentId !== null) {
            $teamRaw = $this->rcSetting('rc.team_daily_capacity_dept_' . $departmentId);
        }
        if ($teamRaw === null || !is_numeric(trim((string) $teamRaw))) {
            $teamRaw = $this->rcSetting('rc.team_daily_capacity');
        }
        $team = ($teamRaw !== null && is_numeric(trim((string) $teamRaw))) ? max(0, (int) $teamRaw) : 24;

        return ['assignee' => $assignee, 'team' => $team];
    }

    /** خواندن مستقیم از جدول settings — مستقل از helper اپ (برای CI/tests)؛ نبودن جدول/کلید null. */
    private function rcSetting(string $key): ?string
    {
        try {
            $st = $this->pdo->prepare('SELECT `value` FROM settings WHERE `key` = ? LIMIT 1');
            $st->execute([$key]);
            $value = $st->fetchColumn();
            return $value === false ? null : (string) $value;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * بار ظرفیت امروز (یا یک روز مشخص): شمارش per-assignee و per-department.
     *
     * @return array{per_assignee:array<int,array{assignee_id:int, full_name:string, used:int, cap:int}>, per_dept:array<int,int>}
     */
    public function dailyLoadSnapshot(string $dayDate, int $defaultAssigneeCap = 8): array
    {
        $empty = ['per_assignee' => [], 'per_dept' => []];
        if (!$this->tableExists()) {
            return $empty;
        }
        $dayStart = substr($dayDate, 0, 10) . ' 00:00:00';
        $dayEnd = substr($dayDate, 0, 10) . ' 23:59:59';

        $assigneeStmt = $this->pdo->prepare(
            "SELECT a.assignee_id, COALESCE(u.full_name, '') AS full_name, COUNT(*) AS used
             FROM request_center_appointments a
             LEFT JOIN users u ON u.id = a.assignee_id
             WHERE a.status <> ? AND a.starts_at >= ? AND a.starts_at <= ?
             GROUP BY a.assignee_id, u.full_name
             ORDER BY used DESC, a.assignee_id ASC"
        );
        $assigneeStmt->execute([self::STATUS_CANCELLED, $dayStart, $dayEnd]);
        $perAssignee = [];
        foreach ($assigneeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $assigneeId = (int) $row['assignee_id'];
            $raw = $this->rcSetting('rc.daily_capacity_assignee');
            $cap = ($raw !== null && is_numeric(trim($raw))) ? max(1, (int) $raw) : $defaultAssigneeCap;
            $perAssignee[] = ['assignee_id' => $assigneeId, 'full_name' => (string) $row['full_name'], 'used' => (int) $row['used'], 'cap' => $cap];
        }

        $deptStmt = $this->pdo->prepare(
            "SELECT u.department_id AS dept_id, COUNT(*) AS used
             FROM request_center_appointments a
             INNER JOIN users u ON u.id = a.assignee_id
             WHERE a.status <> ? AND a.starts_at >= ? AND a.starts_at <= ? AND u.department_id IS NOT NULL
             GROUP BY u.department_id"
        );
        $deptStmt->execute([self::STATUS_CANCELLED, $dayStart, $dayEnd]);
        $perDept = [];
        foreach ($deptStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $perDept[(int) $row['dept_id']] = (int) $row['used'];
        }

        return ['per_assignee' => $perAssignee, 'per_dept' => $perDept];
    }
    /** سقف روزانهٔ تیم (تجمعی) برای یک دایرهٔ مشخص — صفر یعنی غیرفعال. */
    public function teamDailyCapacity(int $departmentId): int
    {
        $limits = $this->dailyLimits($departmentId);
        return (int) $limits['team'];
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
