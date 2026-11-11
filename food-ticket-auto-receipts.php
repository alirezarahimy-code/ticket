<?php
declare(strict_types=1);

/**
 * ثبت سیستمی فیش در پایان روز؛ این مسیر هرگز صف چاپ فیزیکی را فراخوانی نمی‌کند.
 * source_uid برای نمایش عنوان دقیق و source_key برای idempotency استفاده می‌شود.
 */
function food_ticket_auto_receipt_label(string $eventType, string $sourceUid): string
{
    return match ($sourceUid) {
        'auto-holiday-lunch' => 'چاپ خودکار تعطیلات',
        'auto-dinner' => 'چاپ خودکار شام',
        'auto-guest-holiday-lunch' => 'چاپ خودکار مهمان در تعطیلات',
        'auto-guest-dinner' => 'چاپ خودکار مهمان شام',
        default => match ($eventType) {
            'auto_printed' => 'چاپ خودکار',
            'auto_guest' => 'چاپ خودکار مهمان',
            default => '',
        },
    };
}

function food_ticket_auto_receipt_date(string $date, bool $allowToday = false): string
{
    $date = trim($date);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $match)
        || !checkdate((int) $match[2], (int) $match[3], (int) $match[1])) {
        throw new RuntimeException('تاریخ ثبت خودکار فیش معتبر نیست.');
    }
    $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran')))->format('Y-m-d');
    // حالت پیش‌فرض (تماس دستی/قدیمی) همچنان فقط روزهایِ پایان‌یافته را می‌پذیرد.
    // گذر پایان‌روزِ Worker صراحتاً $allowToday = true می‌دهد (ساعت ۲۳ به بعد).
    if ($date > $today || (!$allowToday && $date === $today)) {
        throw new RuntimeException('ثبت خودکار فقط برای روزی انجام می‌شود که به پایان رسیده باشد.');
    }
    return $date;
}

/** پنجشنبه/جمعه یا تعطیلی فعالِ جدول holidays. */
function food_ticket_auto_receipt_holiday(string $date): array
{
    $dayOfWeek = (int) (new DateTimeImmutable($date, new DateTimeZone('UTC')))->format('N');
    $weekend = in_array($dayOfWeek, [4, 5], true); // Thursday / Friday
    $title = '';
    try {
        $stmt = db()->prepare('SELECT title FROM holidays WHERE holiday_date = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$date]);
        $title = trim((string) ($stmt->fetchColumn() ?: ''));
    } catch (Throwable $error) {
        // اگر جدول تقویم در دسترس نباشد، پنجشنبه/جمعه همچنان تعطیل‌اند؛
        // روزهای کاری هرگز صرفاً به‌دلیل خطای خواندن تقویم تعطیل فرض نمی‌شوند.
        app_error_log('[food-auto-receipts] holiday lookup failed: ' . $error->getMessage());
    }
    return ['holiday' => $weekend || $title !== '', 'weekend' => $weekend, 'title' => $title];
}

function food_ticket_auto_receipt_table_exists(PDO $pdo, string $table): bool
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = ?");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

function food_ticket_auto_receipt_columns(PDO $pdo, string $table): array
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return [];
    }
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $rows = $pdo->query('PRAGMA table_info(`' . $table . '`)')->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn (array $row): string => (string) ($row['name'] ?? ''), $rows);
    }
    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function food_ticket_auto_receipt_schema_ready(): bool
{
    try {
        $pdo = db();
        foreach (['food_orders', 'users', 'food_calendar_items', 'food_catalog', 'food_ticket_events', 'food_ticket_daily_absence', 'food_guest_requests'] as $table) {
            if (!food_ticket_auto_receipt_table_exists($pdo, $table)) {
                return false;
            }
        }
        $eventColumns = food_ticket_auto_receipt_columns($pdo, 'food_ticket_events');
        $guestColumns = food_ticket_auto_receipt_columns($pdo, 'food_guest_requests');
        if (!in_array('meal_type', $eventColumns, true)
            || !in_array('event_type', $eventColumns, true)
            || !in_array('source_key', $eventColumns, true)
            || !in_array('print_status', $eventColumns, true)
            || !in_array('delivery_status', $eventColumns, true)
            || !in_array('delivered_at', $eventColumns, true)
            || !in_array('processed_at', $eventColumns, true)
            || !in_array('source_payload', $eventColumns, true)
            || !in_array('meal_type', $guestColumns, true)) {
            return false;
        }
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver !== 'sqlite') {
            $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_ticket_events' AND COLUMN_NAME = 'event_type'");
            $stmt->execute();
            $eventType = strtolower((string) $stmt->fetchColumn());
            return str_contains($eventType, 'auto_printed') && str_contains($eventType, 'auto_guest');
        }
        return true;
    } catch (Throwable $error) {
        app_error_log('[food-auto-receipts] schema check failed: ' . $error->getMessage());
        return false;
    }
}

function food_ticket_auto_receipt_insert(array $event): bool
{
    $pdo = db();
    $sourceKey = (string) ($event['source_key'] ?? '');
    if (!preg_match('/^[a-f0-9]{64}$/', $sourceKey)) {
        throw new RuntimeException('کلید یکتای ثبت خودکار فیش معتبر نیست.');
    }
    $processedAt = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran')))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO food_ticket_events '
        . '(source_key, source_uid, source_card, punch_date, punch_time, personnel_code, user_id, national_code, full_name, food_type, ticket_key, '
        . 'event_type, print_status, last_error, source_payload, processed_at, meal_type, delivery_status, delivered_at) '
        . 'VALUES (?, ?, NULL, ?, NULL, ?, ?, ?, ?, ?, NULL, ?, \'printed\', NULL, ?, ?, ?, ?, ?)'
    );
    try {
        $stmt->execute([
            $sourceKey,
            (string) ($event['source_uid'] ?? ''),
            (string) ($event['punch_date'] ?? ''),
            ($event['personnel_code'] ?? '') !== '' ? (string) $event['personnel_code'] : null,
            !empty($event['user_id']) ? (int) $event['user_id'] : null,
            ($event['national_code'] ?? '') !== '' ? (string) $event['national_code'] : null,
            (string) ($event['full_name'] ?? ''),
            (string) ($event['food_type'] ?? ''),
            (string) ($event['event_type'] ?? ''),
            (string) ($event['source_payload'] ?? ''),
            $processedAt,
            (string) ($event['meal_type'] ?? 'lunch'),
            (string) ($event['delivery_status'] ?? 'pending'),
            ($event['delivery_status'] ?? 'pending') === 'delivered' ? $processedAt : null,
        ]);
        return true;
    } catch (PDOException $error) {
        // درج هم‌زمان یا اجرای مجدد: کلید یکتا جلوی هرگونه فیش خودکار تکراری را می‌گیرد.
        $check = $pdo->prepare('SELECT id FROM food_ticket_events WHERE source_key = ? LIMIT 1');
        $check->execute([$sourceKey]);
        if ($check->fetchColumn()) {
            return false;
        }
        throw $error;
    }
}

/**
 * اجرای idempotent برای یک روزِ پایان‌یافته (یا امروز، فقط از گذر پایان‌روز ساعت ۲۳ به بعد).
 * @return array<string,int|string|bool>
 */
function food_ticket_auto_receipts_run(string $targetDate, bool $allowToday = false): array
{
    $date = food_ticket_auto_receipt_date($targetDate, $allowToday);
    if (!food_ticket_auto_receipt_schema_ready()) {
        throw new RuntimeException('ساختار ثبت خودکار فیش آماده نیست؛ مهاجرت 021 را اجرا و وضعیت جدول‌ها را بررسی کنید.');
    }

    $pdo = db();
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $lockName = 'food_auto_receipts_' . str_replace('-', '', $date);
    $lockAcquired = false;
    if ($driver !== 'sqlite') {
        $lockQuery = $pdo->prepare('SELECT GET_LOCK(?, 0)');
        $lockQuery->execute([$lockName]);
        $lockAcquired = (int) $lockQuery->fetchColumn() === 1;
        if (!$lockAcquired) {
            return ['date' => $date, 'holiday' => false, 'staff_created' => 0, 'guest_created' => 0, 'skipped_absence' => 0, 'skipped_real_event' => 0, 'already_registered' => 0, 'locked' => true];
        }
    }

    try {
        $holiday = food_ticket_auto_receipt_holiday($date);
        $absenceStmt = $pdo->prepare('SELECT user_id FROM food_ticket_daily_absence WHERE absence_date = ?');
        $absenceStmt->execute([$date]);
        $absentUserIds = array_fill_keys(array_map('intval', $absenceStmt->fetchAll(PDO::FETCH_COLUMN)), true);

        $result = [
            'date' => $date,
            'holiday' => (bool) $holiday['holiday'],
            'staff_created' => 0,
            'guest_created' => 0,
            'skipped_absence' => 0,
            'skipped_real_event' => 0,
            'already_registered' => 0,
            'locked' => false,
        ];

        $orders = $pdo->prepare(
            'SELECT o.id AS order_id, o.employee_id AS user_id, o.food_date, o.meal_type, '
            . 'u.employee_number, u.national_code, u.first_name, u.last_name, u.full_name, c.food_name '
            . 'FROM food_orders o '
            . 'LEFT JOIN users u ON u.id = o.employee_id '
            . 'LEFT JOIN food_calendar_items i ON i.id = o.calendar_item_id '
            . 'LEFT JOIN food_catalog c ON c.id = i.food_id '
            . 'WHERE o.food_date = ? AND o.status = \'active\' AND o.meal_type IN (\'lunch\', \'dinner\') '
            . 'ORDER BY o.meal_type, o.employee_id, o.id'
        );
        $orders->execute([$date]);
        $realEvent = $pdo->prepare(
            'SELECT id FROM food_ticket_events '
            . 'WHERE user_id = ? AND punch_date = ? AND COALESCE(meal_type, \'lunch\') = ? '
            . 'AND print_status = \'printed\' AND event_type IN (\'printed\', \'print\', \'guest\') LIMIT 1'
        );

        foreach ($orders->fetchAll(PDO::FETCH_ASSOC) as $order) {
            $mealType = (string) ($order['meal_type'] ?? '');
            if ($mealType === 'lunch' && empty($holiday['holiday'])) {
                continue;
            }
            if (!in_array($mealType, ['lunch', 'dinner'], true)) {
                continue;
            }
            $userId = (int) ($order['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }
            if (isset($absentUserIds[$userId])) {
                $result['skipped_absence']++;
                continue;
            }
            $realEvent->execute([$userId, $date, $mealType]);
            if ($realEvent->fetchColumn()) {
                $result['skipped_real_event']++;
                continue;
            }

            $reason = $mealType === 'dinner' ? 'dinner' : 'holiday';
            $sourceUid = $mealType === 'dinner' ? 'auto-dinner' : 'auto-holiday-lunch';
            $label = food_ticket_auto_receipt_label('auto_printed', $sourceUid);
            $first = trim((string) ($order['first_name'] ?? ''));
            $last = trim((string) ($order['last_name'] ?? ''));
            $fullName = trim((string) ($order['full_name'] ?? ''));
            if ($fullName === '') {
                $fullName = trim($first . ' ' . $last);
            }
            $payload = json_encode([
                'auto_receipt' => [
                    'reason' => $reason,
                    'label' => $label,
                    'meal_type' => $mealType,
                    'order_id' => (int) ($order['order_id'] ?? 0),
                    'physical_print' => false,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            $sourceKey = hash('sha256', 'auto-food-receipt|' . $date . '|' . $mealType . '|' . (int) $order['order_id']);
            $inserted = food_ticket_auto_receipt_insert([
                'source_key' => $sourceKey,
                'source_uid' => $sourceUid,
                'punch_date' => $date,
                'personnel_code' => trim((string) ($order['employee_number'] ?? '')),
                'user_id' => $userId,
                'national_code' => trim((string) ($order['national_code'] ?? '')),
                'full_name' => $fullName !== '' ? $fullName : 'پرسنل',
                'food_type' => trim((string) ($order['food_name'] ?? '')) ?: 'سفارش غذا',
                'event_type' => 'auto_printed',
                'source_payload' => is_string($payload) ? $payload : '{}',
                'meal_type' => $mealType,
                // وعدهٔ سلف‌بسته (شام؛ ناهارِ پنجشنبه/جمعه و تعطیلات رسمی) خودکار تحویل‌شده ثبت می‌شود —
                // طبق تصمیم مالک: این ثبت‌ها باید در سامانهٔ تحویل غذا و گزارش‌ها دیده شوند (بدون چاپ فیزیکی).
                'delivery_status' => 'delivered',
            ]);
            if ($inserted) {
                $result['staff_created']++;
            } else {
                $result['already_registered']++;
            }
        }

        $requests = $pdo->prepare(
            'SELECT r.id, r.request_date, r.meal_type, r.guest_count, r.organization, r.requester_name, r.created_by, c.food_name '
            . 'FROM food_guest_requests r '
            . 'LEFT JOIN food_catalog c ON c.id = r.food_id '
            . 'WHERE r.request_date = ? AND r.status = \'active\' AND r.created_by IS NOT NULL '
            . 'AND r.meal_type IN (\'lunch\', \'dinner\') ORDER BY r.id'
        );
        $requests->execute([$date]);
        foreach ($requests->fetchAll(PDO::FETCH_ASSOC) as $request) {
            $mealType = (string) ($request['meal_type'] ?? 'lunch');
            if ($mealType === 'lunch' && empty($holiday['holiday'])) {
                continue;
            }
            if (!in_array($mealType, ['lunch', 'dinner'], true)) {
                continue;
            }
            $guestCount = max(0, min(500, (int) ($request['guest_count'] ?? 0)));
            $sourceUid = $mealType === 'dinner' ? 'auto-guest-dinner' : 'auto-guest-holiday-lunch';
            $label = food_ticket_auto_receipt_label('auto_guest', $sourceUid);
            for ($guestIndex = 1; $guestIndex <= $guestCount; $guestIndex++) {
                $payload = json_encode([
                    'auto_receipt' => [
                        'reason' => $mealType === 'dinner' ? 'dinner' : 'holiday',
                        'label' => $label,
                        'meal_type' => $mealType,
                        'request_id' => (int) ($request['id'] ?? 0),
                        'guest_index' => $guestIndex,
                        'physical_print' => false,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                $organization = trim((string) ($request['organization'] ?? ''));
                $guestName = $organization !== '' ? $organization . ' — مهمان ' . $guestIndex : 'مهمان ' . $guestIndex;
                $sourceKey = hash('sha256', 'auto-guest-receipt|' . $date . '|' . (int) $request['id'] . '|' . $mealType . '|' . $guestIndex);
                $inserted = food_ticket_auto_receipt_insert([
                    'source_key' => $sourceKey,
                    'source_uid' => $sourceUid,
                    'punch_date' => $date,
                    'user_id' => null,
                    'personnel_code' => '',
                    'national_code' => '',
                    'full_name' => $guestName,
                    'food_type' => trim((string) ($request['food_name'] ?? '')) ?: 'مهمان',
                    'event_type' => 'auto_guest',
                    'source_payload' => is_string($payload) ? $payload : '{}',
                    'meal_type' => $mealType,
                    'delivery_status' => 'pending',
                ]);
                if ($inserted) {
                    $result['guest_created']++;
                } else {
                    $result['already_registered']++;
                }
            }
        }
        return $result;
    } finally {
        if ($lockAcquired) {
            try {
                $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
                $release->execute([$lockName]);
            } catch (Throwable) {
                // قفل session هنگام پایان PDO هم آزاد می‌شود.
            }
        }
    }
}
