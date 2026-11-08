<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/**
 * ثبت تطبیق سفارش، خرید و توزیع غذا و بایگانی امن فاکتورهای ماهانه.
 * فایل فاکتور در storage/uploads نگه‌داری می‌شود تا در بکاپ پیوست‌ها نیز بیاید؛
 * دسترسی مستقیم وب بسته است و دریافت فقط از مسیر API احراز هویت‌شده انجام می‌شود.
 */
const FOOD_RECONCILIATION_MAX_INVOICE_BYTES = 8 * 1024 * 1024;

function food_reconciliation_schema_ensure(): void
{
    food_order_schema_ensure();
    $required = ['food_meal_reconciliations', 'food_monthly_invoices'];
    $placeholders = implode(',', array_fill(0, count($required), '?'));
    $stmt = db()->prepare("SELECT TABLE_NAME FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})");
    $stmt->execute($required);
    $present = array_fill_keys(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
    $missing = array_values(array_filter($required, static fn (string $table): bool => !isset($present[$table])));
    if ($missing !== []) {
        throw new RuntimeException('ثبت تطبیق غذا آماده نیست؛ ابتدا بستهٔ ۱.۶۲.۷۴ و مهاجرت ۰۲۲ را اجرا کنید.');
    }
    $col = db()->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_meal_reconciliations' AND COLUMN_NAME = 'leftover_staff_quantity'");
    $col->execute();
    if ((int) $col->fetchColumn() !== 1) {
        throw new RuntimeException('ثبت تطبیق غذا آماده نیست؛ ابتدا مهاجرت ۰۳۶ (سرنوشت غذای باقیمانده) را اجرا کنید.');
    }
}

/**
 * محاسبات خالص (بدون پایگاه‌داده) برای «تطبیق روزانه».
 * - تقاضای فعال = سفارش فعال پرسنل + درخواست فعال مهمان
 * - مانده = پرس تحویل واقعی − کل تحویل‌داده‌شده‌ها (توزیع پرسنل + توزیع مهمان)؛ می‌تواند منفی باشد
 * - فاصلهٔ سفارش = تقاضای فعال − پرس سفارش‌داده‌شده از پیمانکار؛ مثبت = کسر سفارش، منفی = اضافه سفارش
 */
function food_reconciliation_calculate(
    int $activeEmployeeOrders,
    int $activeGuestMeals,
    ?int $supplierOrderQuantity,
    ?int $receivedQuantity,
    int $employeeServed,
    int $guestServed
): array {
    $activeEmployeeOrders = max(0, $activeEmployeeOrders);
    $activeGuestMeals = max(0, $activeGuestMeals);
    $employeeServed = max(0, $employeeServed);
    $guestServed = max(0, $guestServed);
    $demand = $activeEmployeeOrders + $activeGuestMeals;
    $served = $employeeServed + $guestServed;
    return [
        'active_demand' => $demand,
        'served_employee' => $employeeServed,
        'served_guest' => $guestServed,
        'served_total' => $served,
        'remaining_quantity' => $receivedQuantity === null ? null : $receivedQuantity - $served,
        'order_gap' => $supplierOrderQuantity === null ? null : $demand - $supplierOrderQuantity,
    ];
}

/** شمارش زندهٔ تحویل‌گرفته‌ها از رویدادهای فیش (printed+delivered) به تفکیک تاریخ+وعده؛ همان تعریف «گزارش تحویل غذا». */
function food_reconciliation_served_counts(string $from, string $to): array
{
    if (function_exists('food_ticket_manual_attendance_reconcile_range')) {
        try {
            food_ticket_manual_attendance_reconcile_range($from, $to);
        } catch (Throwable $reconcileError) {
            app_error_log('[food-reconciliation] manual-attendance reconcile failed: ' . $reconcileError->getMessage());
        }
    }
    $map = [];
    $staff = db()->prepare("SELECT punch_date, COALESCE(meal_type, 'lunch') AS meal,
            COUNT(DISTINCT user_id) AS served
        FROM food_ticket_events
        WHERE punch_date BETWEEN ? AND ? AND user_id IS NOT NULL
          AND event_type NOT IN ('guest', 'auto_guest')
          AND ((print_status = 'printed' AND delivery_status = 'delivered')
               OR (source_uid = 'manual-attendance' AND event_type = 'printed' AND delivery_status = 'delivered'))
        GROUP BY punch_date, COALESCE(meal_type, 'lunch')");
    $staff->execute([$from, $to]);
    foreach ($staff->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[$row['punch_date'] . "\0" . $row['meal']]['employee'] = (int) $row['served'];
    }
    // مهمان‌ها عملاً «تحویل» ثبت‌شده‌ای ندارند؛ مانند «گزارش تحویل غذا»، فیش چاپی مهمان (دستی guest یا خودکار auto_guest) ملاک توزیع است.
    $guest = db()->prepare("SELECT punch_date, COALESCE(meal_type, 'lunch') AS meal, COUNT(*) AS served
        FROM food_ticket_events
        WHERE punch_date BETWEEN ? AND ?
          AND event_type IN ('guest', 'auto_guest')
          AND print_status = 'printed'
        GROUP BY punch_date, COALESCE(meal_type, 'lunch')");
    $guest->execute([$from, $to]);
    foreach ($guest->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[$row['punch_date'] . "\0" . $row['meal']]['guest'] = (int) $row['served'];
    }
    foreach ($map as $key => $parts) {
        $map[$key] = ['employee' => (int) ($parts['employee'] ?? 0), 'guest' => (int) ($parts['guest'] ?? 0)];
    }
    return $map;
}

/** «غایبین»: سفارش‌دهندگان فعال وعده که فیش تحویل برایشان ثبت نشده است (شامل غیبت‌های ثبت‌شده در گروه غذایی). */
function food_reconciliation_absentee_counts(string $from, string $to, array $servedMap): array
{
    $stmt = db()->prepare("SELECT food_date, meal_type, COUNT(DISTINCT employee_id) AS active_people
        FROM food_orders WHERE status = 'active' AND food_date BETWEEN ? AND ?
        GROUP BY food_date, meal_type");
    $stmt->execute([$from, $to]);
    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = $row['food_date'] . "\0" . $row['meal_type'];
        $delivered = $servedMap[$key]['employee'] ?? 0;
        $map[$key] = max(0, (int) $row['active_people'] - $delivered);
    }
    return $map;
}

/** خلاصهٔ فارسی فشردهٔ تعیین تکلیف باقیمانده برای نمایش در جدول و اکسل. */
function food_reconciliation_disposition_summary(?int $staff, ?int $waste, ?int $keep, ?int $other, ?string $otherNote): ?string
{
    $parts = [];
    if ($staff !== null && $staff > 0) {
        $parts[] = 'توزیع پرسنل: ' . $staff;
    }
    if ($waste !== null && $waste > 0) {
        $parts[] = 'دورریز: ' . $waste;
    }
    if ($keep !== null && $keep > 0) {
        $parts[] = 'نگه‌داری: ' . $keep;
    }
    if ($other !== null && $other > 0) {
        $parts[] = 'سایر: ' . $other;
    }
    if ($parts === []) {
        return null;
    }
    $summary = implode(' · ', $parts);
    $otherNote = trim((string) $otherNote);
    if ($otherNote !== '') {
        $summary .= ' (سایر: ' . $otherNote . ')';
    }
    return $summary;
}

function food_reconciliation_count(mixed $value, string $label, bool $nullable = false, int $max = 1000000): ?int
{
    $raw = trim(food_order_normalize_digits($value));
    $raw = str_replace([',', '٬', ' '], '', $raw);
    if ($raw === '') {
        if ($nullable) {
            return null;
        }
        throw new RuntimeException($label . ' را وارد کنید.');
    }
    if (!preg_match('/^\d+$/', $raw)) {
        throw new RuntimeException($label . ' باید عدد صحیحِ نامنفی باشد.');
    }
    $number = (int) $raw;
    if ($number < 0 || $number > $max) {
        throw new RuntimeException($label . ' از محدودهٔ مجاز خارج است.');
    }
    return $number;
}

function food_reconciliation_month(mixed $value): string
{
    $raw = trim(food_order_normalize_digits($value));
    if (!preg_match('/^(\d{4})[-\/](\d{1,2})$/', $raw, $parts)) {
        throw new RuntimeException('ماه فاکتور معتبر نیست؛ سال و ماه شمسی را انتخاب کنید.');
    }
    $year = (int) $parts[1];
    $month = (int) $parts[2];
    if ($year >= 1200 && $year <= 1600) {
        $jalali = food_order_jalali_month_range(sprintf('%04d/%02d', $year, $month));
        return $jalali['from'];
    }
    if ($year < 1900 || $year > 2200 || $month < 1 || $month > 12) {
        throw new RuntimeException('ماه فاکتور خارج از محدودهٔ مجاز است.');
    }
    return sprintf('%04d-%02d-01', $year, $month);
}

function food_reconciliation_month_range(string $monthStart): array
{
    $jalaliLabel = food_order_jalali_label($monthStart);
    if (preg_match('/^(\d{4})\/(\d{2})\/\d{2}$/', $jalaliLabel)) {
        $range = food_order_jalali_month_range(substr($jalaliLabel, 0, 7));
        if ($range['from'] === $monthStart) {
            $first = $range['from'];
            $last = $range['to'];
            $next = (new DateTimeImmutable($last, new DateTimeZone('UTC')))->modify('+1 day')->format('Y-m-d');
            return [$first, $next, $last];
        }
    }
    $first = new DateTimeImmutable($monthStart, new DateTimeZone('UTC'));
    $next = $first->modify('+1 month');
    return [$first->format('Y-m-d'), $next->format('Y-m-d'), $next->modify('-1 day')->format('Y-m-d')];
}

function food_reconciliation_month_key(string $monthStart): string
{
    $jalali = food_order_jalali_label($monthStart);
    if (preg_match('/^(\d{4})\/(\d{2})\/\d{2}$/', $jalali)) {
        $range = food_order_jalali_month_range(substr($jalali, 0, 7));
        if ($range['from'] === $monthStart) {
            return substr($jalali, 0, 7);
        }
    }
    return substr($monthStart, 0, 7);
}

function food_reconciliation_date_range(string $fromRaw, string $toRaw): array
{
    $from = food_order_iso_date($fromRaw);
    $to = food_order_iso_date($toRaw);
    if ($from === null || $to === null || $from > $to) {
        throw new RuntimeException('بازهٔ تاریخ گزارش معتبر نیست.');
    }
    $days = (int) (new DateTimeImmutable($from, new DateTimeZone('UTC')))->diff(new DateTimeImmutable($to, new DateTimeZone('UTC')))->days;
    if ($days > 366) {
        throw new RuntimeException('بازهٔ گزارش حداکثر می‌تواند یک سال باشد.');
    }
    return [$from, $to];
}

function food_reconciliation_meal_filter(mixed $value): string
{
    $meal = strtolower(trim((string) $value));
    if (!in_array($meal, ['all', 'lunch', 'dinner'], true)) {
        throw new RuntimeException('وعدهٔ گزارش معتبر نیست.');
    }
    return $meal;
}

function food_reconciliation_order_counts(string $date, string $mealType): array
{
    $orders = db()->prepare("SELECT
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
        FROM food_orders WHERE food_date = ? AND meal_type = ?");
    $orders->execute([$date, $mealType]);
    $orderRow = $orders->fetch(PDO::FETCH_ASSOC) ?: [];

    $guests = db()->prepare("SELECT
            SUM(CASE WHEN status = 'active' THEN guest_count ELSE 0 END) AS active_meals,
            SUM(CASE WHEN status = 'cancelled' THEN guest_count ELSE 0 END) AS cancelled_meals
        FROM food_guest_requests WHERE request_date = ? AND meal_type = ?");
    $guests->execute([$date, $mealType]);
    $guestRow = $guests->fetch(PDO::FETCH_ASSOC) ?: [];
    return [
        'active_employee_orders' => (int) ($orderRow['active_count'] ?? 0),
        'cancelled_employee_orders' => (int) ($orderRow['cancelled_count'] ?? 0),
        'active_guest_meals' => (int) ($guestRow['active_meals'] ?? 0),
        'cancelled_guest_meals' => (int) ($guestRow['cancelled_meals'] ?? 0),
    ];
}

/** دادهٔ روزانه؛ شمارش فعال‌ها همیشه status=active است و لغوشده‌ها فقط در ستون مستقل می‌آیند. */
function food_reconciliation_rows(string $from, string $to, string $mealFilter = 'all'): array
{
    food_reconciliation_schema_ensure();
    $servedMap = food_reconciliation_served_counts($from, $to);
    $absentMap = food_reconciliation_absentee_counts($from, $to, $servedMap);
    $sql = "SELECT k.food_date, k.meal_type,
            COALESCE(o.active_count, 0) AS current_active_employee_orders,
            COALESCE(o.cancelled_count, 0) AS cancelled_employee_orders,
            COALESCE(g.active_meals, 0) AS current_active_guest_meals,
            COALESCE(g.cancelled_meals, 0) AS cancelled_guest_meals,
            r.id AS reconciliation_id, r.supplier_order_quantity, r.received_quantity,
            r.status, r.note,
            r.leftover_staff_quantity, r.leftover_waste_quantity, r.leftover_keep_quantity,
            r.leftover_other_quantity, r.leftover_other_note,
            r.snapshot_at, r.ordered_at, r.closed_at
        FROM (
            SELECT food_date, meal_type FROM food_orders WHERE food_date BETWEEN ? AND ?
            UNION
            SELECT request_date AS food_date, meal_type FROM food_guest_requests WHERE request_date BETWEEN ? AND ?
            UNION
            SELECT food_date, meal_type FROM food_meal_reconciliations WHERE food_date BETWEEN ? AND ?
        ) AS k
        LEFT JOIN (
            SELECT food_date, meal_type,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
            FROM food_orders WHERE food_date BETWEEN ? AND ? GROUP BY food_date, meal_type
        ) AS o ON o.food_date = k.food_date AND o.meal_type = k.meal_type
        LEFT JOIN (
            SELECT request_date, meal_type,
                SUM(CASE WHEN status = 'active' THEN guest_count ELSE 0 END) AS active_meals,
                SUM(CASE WHEN status = 'cancelled' THEN guest_count ELSE 0 END) AS cancelled_meals
            FROM food_guest_requests WHERE request_date BETWEEN ? AND ? GROUP BY request_date, meal_type
        ) AS g ON g.request_date = k.food_date AND g.meal_type = k.meal_type
        LEFT JOIN food_meal_reconciliations AS r ON r.food_date = k.food_date AND r.meal_type = k.meal_type
        WHERE (? = 'all' OR k.meal_type = ?)
        ORDER BY k.food_date DESC, (k.meal_type = 'lunch') DESC";
    $params = [
        $from, $to, $from, $to, $from, $to,
        $from, $to, $from, $to,
        $mealFilter, $mealFilter,
    ];
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $date = (string) $row['food_date'];
        $mealType = (string) $row['meal_type'];
        $key = $date . "\0" . $mealType;
        $currentEmployees = (int) $row['current_active_employee_orders'];
        $currentGuests = (int) $row['current_active_guest_meals'];
        $served = $servedMap[$key] ?? ['employee' => 0, 'guest' => 0];
        $isRegistered = $row['reconciliation_id'] !== null;
        $supplierOrder = $isRegistered ? (int) $row['supplier_order_quantity'] : null;
        $received = $row['received_quantity'] === null ? null : (int) $row['received_quantity'];
        $calc = food_reconciliation_calculate(
            $currentEmployees,
            $currentGuests,
            $supplierOrder,
            $received,
            $served['employee'],
            $served['guest']
        );
        $orderGap = $calc['order_gap'];
        $gapSide = $orderGap === null ? null : ($orderGap > 0 ? 'deficit' : ($orderGap < 0 ? 'surplus' : 'even'));
        $leftoverStaff = $row['leftover_staff_quantity'] === null ? null : (int) $row['leftover_staff_quantity'];
        $leftoverWaste = $row['leftover_waste_quantity'] === null ? null : (int) $row['leftover_waste_quantity'];
        $leftoverKeep = $row['leftover_keep_quantity'] === null ? null : (int) $row['leftover_keep_quantity'];
        $leftoverOther = $row['leftover_other_quantity'] === null ? null : (int) $row['leftover_other_quantity'];
        $leftoverOtherNote = $row['leftover_other_note'] === null ? null : (string) $row['leftover_other_note'];
        $items[] = [
            'date' => $date,
            'date_jalali' => food_order_jalali_label($date),
            'meal_type' => $mealType,
            'active_employee_orders' => $currentEmployees,
            'active_guest_meals' => $currentGuests,
            'active_demand' => $calc['active_demand'],
            'cancelled_employee_orders' => (int) $row['cancelled_employee_orders'],
            'cancelled_guest_meals' => (int) $row['cancelled_guest_meals'],
            'id' => $isRegistered ? (int) $row['reconciliation_id'] : null,
            'supplier_order_quantity' => $supplierOrder,
            'received_quantity' => $received,
            'employee_served_quantity' => $calc['served_employee'],
            'guest_served_quantity' => $calc['served_guest'],
            'served_total' => $calc['served_total'],
            'remaining_quantity' => $calc['remaining_quantity'],
            'order_gap' => $orderGap,
            'order_gap_side' => $gapSide,
            'absentees' => (int) ($absentMap[$key] ?? 0),
            'leftover_staff_quantity' => $leftoverStaff,
            'leftover_waste_quantity' => $leftoverWaste,
            'leftover_keep_quantity' => $leftoverKeep,
            'leftover_other_quantity' => $leftoverOther,
            'leftover_other_note' => $leftoverOtherNote,
            'leftover_disposition_summary' => food_reconciliation_disposition_summary(
                $leftoverStaff, $leftoverWaste, $leftoverKeep, $leftoverOther, $leftoverOtherNote
            ),
            'status' => $isRegistered ? (string) $row['status'] : 'not_ordered',
            'note' => (string) ($row['note'] ?? ''),
            'snapshot_at' => (string) ($row['snapshot_at'] ?? ''),
            'ordered_at' => (string) ($row['ordered_at'] ?? ''),
            'closed_at' => (string) ($row['closed_at'] ?? ''),
        ];
    }
    return $items;
}

function food_reconciliation_invoice_rows(string $monthStart): array
{
    food_reconciliation_schema_ensure();
    $stmt = db()->prepare('SELECT id, invoice_month, invoice_date, supplier_name, invoice_number,
            portion_count, amount_toman, original_name, mime_type, size_bytes, created_at
        FROM food_monthly_invoices WHERE invoice_month = ? ORDER BY invoice_date DESC, id DESC');
    $stmt->execute([$monthStart]);
    return array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'invoice_month' => (string) $row['invoice_month'],
            'invoice_date' => (string) $row['invoice_date'],
            'supplier_name' => (string) $row['supplier_name'],
            'invoice_number' => (string) $row['invoice_number'],
            'portion_count' => (int) $row['portion_count'],
            'amount_toman' => $row['amount_toman'] === null ? null : (int) $row['amount_toman'],
            'original_name' => (string) $row['original_name'],
            'mime_type' => (string) $row['mime_type'],
            'size_bytes' => (int) $row['size_bytes'],
            'created_at' => (string) $row['created_at'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function food_reconciliation_report(string $fromRaw, string $toRaw, string $mealRaw, string $monthRaw): array
{
    [$from, $to] = food_reconciliation_date_range($fromRaw, $toRaw);
    $meal = food_reconciliation_meal_filter($mealRaw);
    $defaultMonthLabel = food_order_jalali_label($from);
    $defaultMonth = preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $defaultMonthLabel) ? substr($defaultMonthLabel, 0, 7) : substr($from, 0, 7);
    $monthStart = food_reconciliation_month($monthRaw !== '' ? $monthRaw : $defaultMonth);
    [$monthFrom, $monthToExclusive, $monthTo] = food_reconciliation_month_range($monthStart);
    $items = food_reconciliation_rows($from, $to, $meal);
    $monthItems = food_reconciliation_rows($monthFrom, $monthTo, 'all');
    $invoices = food_reconciliation_invoice_rows($monthStart);

    $summary = [
        'current_active_employee_orders' => 0,
        'current_active_guest_meals' => 0,
        'current_active_demand' => 0,
        'cancelled_employee_orders' => 0,
        'cancelled_guest_meals' => 0,
        'supplier_order_quantity' => 0,
        'received_quantity' => 0,
        'received_quantity_recorded_rows' => 0,
        'received_quantity_missing_rows' => 0,
        'served_settled_total' => 0,
        'remaining_quantity' => 0,
        'absentees' => 0,
    ];
    foreach ($items as $item) {
        $summary['current_active_employee_orders'] += $item['active_employee_orders'];
        $summary['current_active_guest_meals'] += $item['active_guest_meals'];
        $summary['current_active_demand'] += $item['active_demand'];
        $summary['cancelled_employee_orders'] += $item['cancelled_employee_orders'];
        $summary['cancelled_guest_meals'] += $item['cancelled_guest_meals'];
        $summary['supplier_order_quantity'] += (int) ($item['supplier_order_quantity'] ?? 0);
        if ($item['received_quantity'] === null) {
            if ($item['active_demand'] > 0 || $item['id'] !== null) {
                $summary['received_quantity_missing_rows']++;
            }
        } else {
            $summary['received_quantity'] += $item['received_quantity'];
            $summary['received_quantity_recorded_rows']++;
        }
        $summary['served_settled_total'] = (int) $summary['served_settled_total'] + (int) ($item['served_total'] ?? 0);
        $summary['remaining_quantity'] += (int) ($item['remaining_quantity'] ?? 0);
        $summary['absentees'] += (int) $item['absentees'];
    }

    $monthSummary = [
        'month' => food_reconciliation_month_key($monthStart),
        'invoice_count' => count($invoices),
        'invoice_portions' => 0,
        'invoice_amount_toman' => 0,
        'daily_received_portions' => 0,
        'daily_received_recorded_rows' => 0,
        'daily_received_missing_rows' => 0,
        'invoice_difference' => null,
    ];
    foreach ($invoices as $invoice) {
        $monthSummary['invoice_portions'] += $invoice['portion_count'];
        $monthSummary['invoice_amount_toman'] += (int) ($invoice['amount_toman'] ?? 0);
    }
    foreach ($monthItems as $item) {
        if ($item['received_quantity'] === null) {
            if ($item['active_demand'] > 0 || $item['id'] !== null) {
                $monthSummary['daily_received_missing_rows']++;
            }
        } else {
            $monthSummary['daily_received_portions'] += $item['received_quantity'];
            $monthSummary['daily_received_recorded_rows']++;
        }
    }
    if ($monthSummary['invoice_count'] > 0 && $monthSummary['daily_received_recorded_rows'] > 0 && $monthSummary['daily_received_missing_rows'] === 0) {
        $monthSummary['invoice_difference'] = $monthSummary['invoice_portions'] - $monthSummary['daily_received_portions'];
    }
    return [
        'from' => $from,
        'to' => $to,
        'meal_type' => $meal,
        'month' => food_reconciliation_month_key($monthStart),
        'items' => $items,
        'invoices' => $invoices,
        'summary' => $summary,
        'month_summary' => $monthSummary,
    ];
}

function food_reconciliation_audit(string $action, array $user, int $targetId, mixed $oldValue, mixed $newValue, ?string $reason = null): void
{
    try {
        food_order_audit($action, $user, 'food_meal_reconciliation', $targetId, $oldValue, $newValue, $reason);
    } catch (Throwable $e) {
        app_error_log('[food-reconciliation] audit write failed: ' . $e->getMessage());
    }
}

/**
 * اعتبارسنجی «تعیین تکلیف غذای باقیمانده».
 * اگر مانده (پرس تحویل واقعی − کل تحویل‌داده‌شده‌ها) کمتر از ۱ باشد، تعیین تکلیف لازم نیست و همه‌چیز null می‌شود.
 * اگر مانده ≥ ۱ باشد: حداقل یک گزینه الزامی است و مجموع تعدادها نباید از مانده بیشتر شود.
 *
 * @return array{leftover_staff_quantity: ?int, leftover_waste_quantity: ?int, leftover_keep_quantity: ?int, leftover_other_quantity: ?int, leftover_other_note: ?string}
 */
function food_reconciliation_disposition_parse(array $body, int $remaining): array
{
    if ($remaining < 1) {
        return ['leftover_staff_quantity' => null, 'leftover_waste_quantity' => null, 'leftover_keep_quantity' => null, 'leftover_other_quantity' => null, 'leftover_other_note' => null];
    }
    $staff = food_reconciliation_count($body['leftover_staff_quantity'] ?? '0', 'تعداد توزیع بین پرسنل', true) ?? 0;
    $waste = food_reconciliation_count($body['leftover_waste_quantity'] ?? '0', 'تعداد دورریز', true) ?? 0;
    $keep = food_reconciliation_count($body['leftover_keep_quantity'] ?? '0', 'تعداد نگه‌داری برای وعدهٔ بعد', true) ?? 0;
    $other = food_reconciliation_count($body['leftover_other_quantity'] ?? '0', 'تعداد سایر', true) ?? 0;
    $otherNote = trim((string) ($body['leftover_other_note'] ?? ''));
    if (function_exists('mb_substr')) {
        $otherNote = mb_substr($otherNote, 0, 500, 'UTF-8');
    } else {
        $otherNote = substr($otherNote, 0, 500);
    }
    $sum = $staff + $waste + $keep + $other;
    if ($sum > $remaining) {
        throw new RuntimeException('مجموع تعیین تکلیف (' . $sum . ' پرس) از ماندهٔ امروز (' . $remaining . ' پرس) بیشتر است.');
    }
    if ($sum === 0) {
        throw new RuntimeException('برای بستن این روز باید سرنوشت غذای باقیمانده را مشخص کنید؛ حداقل یک گزینه را با تعداد وارد کنید.');
    }
    return [
        'leftover_staff_quantity' => $staff,
        'leftover_waste_quantity' => $waste,
        'leftover_keep_quantity' => $keep,
        'leftover_other_quantity' => $other,
        'leftover_other_note' => $otherNote !== '' ? $otherNote : null,
    ];
}

function food_reconciliation_save(array $body, array $user): array
{
    food_order_require_permission($user, 'food.menu_edit');
    food_reconciliation_schema_ensure();
    $date = food_order_iso_date((string) ($body['food_date'] ?? ''));
    $meal = strtolower(trim((string) ($body['meal_type'] ?? 'lunch')));
    if ($date === null || !in_array($meal, ['lunch', 'dinner'], true)) {
        throw new RuntimeException('تاریخ و وعدهٔ غذا معتبر نیست.');
    }
    $action = strtolower(trim((string) ($body['action'] ?? 'order')));
    if (!in_array($action, ['order', 'close'], true)) {
        throw new RuntimeException('مرحلهٔ ثبت آمار غذا معتبر نیست.');
    }
    if ($action === 'close' && $date > food_order_today()) {
        throw new RuntimeException('بستن آمار غذا برای تاریخ آینده مجاز نیست.');
    }
    $supplierOrder = food_reconciliation_count($body['supplier_order_quantity'] ?? '', 'تعداد پرس سفارش‌داده‌شده', $action === 'close');
    $received = food_reconciliation_count($body['received_quantity'] ?? '', 'تعداد پرس تحویل واقعی', true);
    $note = trim((string) ($body['note'] ?? ''));
    $reason = trim((string) ($body['edit_reason'] ?? ''));
    if (function_exists('mb_substr')) {
        $note = mb_substr($note, 0, 1000, 'UTF-8');
        $reason = mb_substr($reason, 0, 500, 'UTF-8');
    } else {
        $note = substr($note, 0, 1000);
        $reason = substr($reason, 0, 500);
    }
    // «توزیع پرسنل» و «توزیع مهمان» از رویدادهای فیش (printed+delivered) محاسبهٔ زنده می‌شوند؛ ورودی دستی ندارند.
    $servedMap = food_reconciliation_served_counts($date, $date);
    $servedKey = $date . "\0" . $meal;
    $employeeServed = (int) ($servedMap[$servedKey]['employee'] ?? 0);
    $guestServed = (int) ($servedMap[$servedKey]['guest'] ?? 0);
    $servedTotal = $employeeServed + $guestServed;
    $remaining = $received === null ? null : $received - $servedTotal;
    $disposition = ['leftover_staff_quantity' => null, 'leftover_waste_quantity' => null, 'leftover_keep_quantity' => null, 'leftover_other_quantity' => null, 'leftover_other_note' => null];
    if ($action === 'close') {
        if ($received === null) {
            throw new RuntimeException('برای بستن آمار، مقدار «پرس تحویل واقعی» را وارد کنید؛ در صورت صفر بودن، عدد صفر بنویسید.');
        }
        $disposition = food_reconciliation_disposition_parse($body, (int) $remaining);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $existingStmt = $pdo->prepare('SELECT * FROM food_meal_reconciliations WHERE food_date = ? AND meal_type = ? FOR UPDATE');
        $existingStmt->execute([$date, $meal]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $wasClosed = $existing !== null && (string) $existing['status'] === 'closed';
        if ($wasClosed && $reason === '') {
            throw new RuntimeException('این روز بسته شده است؛ برای اصلاح، دلیل را وارد کنید.');
        }
        $counts = food_reconciliation_order_counts($date, $meal);
        $snapshotEmployee = $existing !== null && $existing['employee_orders_at_order'] !== null
            ? (int) $existing['employee_orders_at_order'] : $counts['active_employee_orders'];
        $snapshotGuest = $existing !== null && $existing['guest_meals_at_order'] !== null
            ? (int) $existing['guest_meals_at_order'] : $counts['active_guest_meals'];
        $orderedQuantity = $supplierOrder ?? (int) ($existing['supplier_order_quantity'] ?? -1);
        if ($orderedQuantity < 0) {
            throw new RuntimeException('تعداد پرس سفارش‌داده‌شده به پیمانکار را وارد کنید.');
        }
        $oldValue = $existing;
        $nowAction = $action === 'close' ? 'closed' : ($wasClosed ? 'closed' : 'ordered');
        $receivedValue = $action === 'close' ? $received : ($existing['received_quantity'] ?? null);
        $orderedBy = $existing !== null && $existing['ordered_by'] !== null ? (int) $existing['ordered_by'] : (int) ($user['id'] ?? 0);
        $orderedAt = $existing !== null && $existing['ordered_at'] !== null ? (string) $existing['ordered_at'] : null;
        $snapshotAt = $existing !== null && $existing['snapshot_at'] !== null ? (string) $existing['snapshot_at'] : null;
        $closedBy = $wasClosed ? (int) ($existing['closed_by'] ?? 0) : null;
        $closedAt = $wasClosed ? (string) ($existing['closed_at'] ?? '') : null;
        if ($action === 'order' && !$wasClosed) {
            // با هر ثبت/اصلاح مقدار خرید، snapshot تقاضای فعال همان لحظه تازه می‌شود.
            $snapshotEmployee = $counts['active_employee_orders'];
            $snapshotGuest = $counts['active_guest_meals'];
            $snapshotAt = date('Y-m-d H:i:s');
            $orderedAt = date('Y-m-d H:i:s');
            $orderedBy = (int) ($user['id'] ?? 0);
        }
        if ($action === 'close') {
            $closedAt = date('Y-m-d H:i:s');
            $closedBy = (int) ($user['id'] ?? 0);
            if ($orderedAt === null) {
                $orderedAt = $closedAt;
                $orderedBy = (int) ($user['id'] ?? 0);
            }
            if ($snapshotAt === null) {
                $snapshotAt = $closedAt;
            }
        }
        // تعیین تکلیف باقیمانده: فقط در مرحلهٔ «بستن» نوشته/پاک می‌شود؛ در مرحلهٔ «سفارش» دست‌نخورده می‌ماند.
        if ($action === 'close') {
            $leftoverStaff = $disposition['leftover_staff_quantity'];
            $leftoverWaste = $disposition['leftover_waste_quantity'];
            $leftoverKeep = $disposition['leftover_keep_quantity'];
            $leftoverOther = $disposition['leftover_other_quantity'];
            $leftoverOtherNote = $disposition['leftover_other_note'];
        } else {
            $leftoverStaff = $existing !== null && $existing['leftover_staff_quantity'] !== null ? (int) $existing['leftover_staff_quantity'] : null;
            $leftoverWaste = $existing !== null && $existing['leftover_waste_quantity'] !== null ? (int) $existing['leftover_waste_quantity'] : null;
            $leftoverKeep = $existing !== null && $existing['leftover_keep_quantity'] !== null ? (int) $existing['leftover_keep_quantity'] : null;
            $leftoverOther = $existing !== null && $existing['leftover_other_quantity'] !== null ? (int) $existing['leftover_other_quantity'] : null;
            $leftoverOtherNote = $existing !== null && $existing['leftover_other_note'] !== null ? (string) $existing['leftover_other_note'] : null;
        }
        $wasteValue = $existing !== null ? (int) ($existing['waste_quantity'] ?? 0) : 0;
        if ($existing === null) {
            $insert = $pdo->prepare('INSERT INTO food_meal_reconciliations
                (food_date, meal_type, employee_orders_at_order, guest_meals_at_order, supplier_order_quantity,
                 received_quantity, employee_served_quantity, guest_served_quantity, waste_quantity,
                 leftover_staff_quantity, leftover_waste_quantity, leftover_keep_quantity, leftover_other_quantity, leftover_other_note,
                 status, note,
                 ordered_by, ordered_at, snapshot_at, closed_by, closed_at, created_by, updated_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([
                $date, $meal, $snapshotEmployee, $snapshotGuest, $orderedQuantity,
                $receivedValue, $employeeServed, $guestServed, $wasteValue,
                $leftoverStaff, $leftoverWaste, $leftoverKeep, $leftoverOther, $leftoverOtherNote,
                $nowAction, $note !== '' ? $note : null,
                $orderedBy, $orderedAt, $snapshotAt, $closedBy, $closedAt,
                (int) ($user['id'] ?? 0), (int) ($user['id'] ?? 0),
            ]);
            $id = (int) $pdo->lastInsertId();
        } else {
            $update = $pdo->prepare('UPDATE food_meal_reconciliations SET
                employee_orders_at_order = ?, guest_meals_at_order = ?, supplier_order_quantity = ?,
                received_quantity = ?, employee_served_quantity = ?, guest_served_quantity = ?, waste_quantity = ?,
                leftover_staff_quantity = ?, leftover_waste_quantity = ?, leftover_keep_quantity = ?,
                leftover_other_quantity = ?, leftover_other_note = ?,
                status = ?, note = ?, ordered_by = ?, ordered_at = ?, snapshot_at = ?, closed_by = ?, closed_at = ?,
                updated_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $update->execute([
                $snapshotEmployee, $snapshotGuest, $orderedQuantity,
                $receivedValue, $employeeServed, $guestServed, $wasteValue,
                $leftoverStaff, $leftoverWaste, $leftoverKeep, $leftoverOther, $leftoverOtherNote,
                $nowAction, $note !== '' ? $note : null, $orderedBy,
                $orderedAt, $snapshotAt, $closedBy, $closedAt,
                (int) ($user['id'] ?? 0), (int) $existing['id'],
            ]);
            $id = (int) $existing['id'];
        }
        $pdo->commit();
        $newValue = [
            'food_date' => $date,
            'meal_type' => $meal,
            'employee_orders_at_order' => $snapshotEmployee,
            'guest_meals_at_order' => $snapshotGuest,
            'supplier_order_quantity' => $orderedQuantity,
            'received_quantity' => $receivedValue,
            'employee_served_quantity' => $employeeServed,
            'guest_served_quantity' => $guestServed,
            'leftover_staff_quantity' => $leftoverStaff,
            'leftover_waste_quantity' => $leftoverWaste,
            'leftover_keep_quantity' => $leftoverKeep,
            'leftover_other_quantity' => $leftoverOther,
            'leftover_other_note' => $leftoverOtherNote,
            'status' => $nowAction,
        ];
        food_reconciliation_audit('food_meal_reconciliation_saved', $user, $id, $oldValue, $newValue, $reason !== '' ? $reason : null);
        return ['ok' => true, 'id' => $id, 'status' => $nowAction, 'message' => $nowAction === 'closed' ? 'جمع‌بندی وعده بسته و ثبت شد.' : 'تعداد سفارش به پیمانکار و سفارش‌های فعال همان لحظه ثبت شد.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function food_reconciliation_upload_directory(): string
{
    return APP_ROOT . '/storage/uploads/food-invoices';
}

function food_reconciliation_validate_file(array $upload): array
{
    if ((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($upload['tmp_name'] ?? ''))) {
        throw new RuntimeException('فایل فاکتور دریافت نشد یا بارگذاری ناقص بود.');
    }
    $size = (int) ($upload['size'] ?? 0);
    if ($size < 1 || $size > FOOD_RECONCILIATION_MAX_INVOICE_BYTES) {
        throw new RuntimeException('حجم فایل فاکتور باید حداکثر ۸ مگابایت باشد.');
    }
    $original = str_replace('\\', '/', (string) ($upload['name'] ?? ''));
    $original = basename($original);
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowedMimes = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
    ];
    if (!isset($allowedMimes[$extension])) {
        throw new RuntimeException('فقط PDF، تصویر JPG/PNG یا فایل Excel (XLS/XLSX) برای فاکتور پذیرفته می‌شود.');
    }
    $mime = 'application/octet-stream';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_file($finfo, (string) $upload['tmp_name']);
            finfo_close($finfo);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }
    }
    if (!in_array($mime, $allowedMimes[$extension], true)) {
        throw new RuntimeException('محتوای فایل با قالب انتخاب‌شده هم‌خوانی ندارد.');
    }
    if ($extension === 'pdf') {
        $handle = fopen((string) $upload['tmp_name'], 'rb');
        $signature = $handle ? fread($handle, 5) : '';
        if (is_resource($handle)) {
            fclose($handle);
        }
        if ($signature !== '%PDF-') {
            throw new RuntimeException('امضای فایل PDF معتبر نیست.');
        }
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        $image = @getimagesize((string) $upload['tmp_name']);
        $expected = $extension === 'png' ? 'image/png' : 'image/jpeg';
        if (!is_array($image) || (string) ($image['mime'] ?? '') !== $expected) {
            throw new RuntimeException('فایل تصویر معتبر نیست.');
        }
    } elseif ($extension === 'xls') {
        $handle = fopen((string) $upload['tmp_name'], 'rb');
        $signature = $handle ? fread($handle, 8) : '';
        if (is_resource($handle)) {
            fclose($handle);
        }
        if ($signature !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
            throw new RuntimeException('ساختار فایل Excel قدیمی معتبر نیست.');
        }
    } elseif ($extension === 'xlsx') {
        $handle = fopen((string) $upload['tmp_name'], 'rb');
        $signature = $handle ? fread($handle, 4) : '';
        if (is_resource($handle)) {
            fclose($handle);
        }
        if ($signature !== "PK\x03\x04") {
            throw new RuntimeException('ساختار فایل Excel معتبر نیست.');
        }
    }
    if (function_exists('mb_substr')) {
        $original = mb_substr($original, 0, 250, 'UTF-8');
    } else {
        $original = substr($original, 0, 250);
    }
    $original = preg_replace('/[\x00-\x1F\x7F]/u', '', $original) ?? 'invoice.' . $extension;
    return ['original_name' => $original, 'extension' => $extension, 'mime_type' => $mime, 'size_bytes' => $size];
}

function food_reconciliation_upload_invoice(array $post, array $files, array $user): array
{
    food_order_require_permission($user, 'food.menu_edit');
    food_reconciliation_schema_ensure();
    $monthStart = food_reconciliation_month($post['invoice_month'] ?? $post['month'] ?? '');
    $invoiceDate = food_order_iso_date((string) ($post['invoice_date'] ?? ''));
    [$invoiceMonthFrom, $invoiceMonthToExclusive] = food_reconciliation_month_range($monthStart);
    if ($invoiceDate === null || $invoiceDate < $invoiceMonthFrom || $invoiceDate >= $invoiceMonthToExclusive) {
        throw new RuntimeException('تاریخ فاکتور باید در همان ماه انتخاب‌شده باشد.');
    }
    $supplier = trim((string) ($post['supplier_name'] ?? ''));
    $invoiceNumber = trim((string) ($post['invoice_number'] ?? ''));
    if ($supplier === '' || $invoiceNumber === '') {
        throw new RuntimeException('نام پیمانکار و شمارهٔ فاکتور را وارد کنید.');
    }
    $supplier = function_exists('mb_substr') ? mb_substr($supplier, 0, 190, 'UTF-8') : substr($supplier, 0, 190);
    $invoiceNumber = function_exists('mb_substr') ? mb_substr($invoiceNumber, 0, 100, 'UTF-8') : substr($invoiceNumber, 0, 100);
    $portions = food_reconciliation_count($post['portion_count'] ?? '', 'تعداد پرس طبق فاکتور', false, 1000000);
    if ($portions < 1) {
        throw new RuntimeException('تعداد پرس فاکتور باید دست‌کم یک باشد.');
    }
    $amountRaw = trim(food_order_normalize_digits($post['amount_toman'] ?? ''));
    $amountRaw = str_replace([',', '٬', ' '], '', $amountRaw);
    $amount = null;
    if ($amountRaw !== '') {
        if (!preg_match('/^\d+$/', $amountRaw) || strlen(ltrim($amountRaw, '0')) > 15) {
            throw new RuntimeException('مبلغ فاکتور باید عدد صحیحِ نامنفی و به تومان باشد.');
        }
        $amount = (int) $amountRaw;
    }
    $upload = $files['file'] ?? null;
    if (!is_array($upload)) {
        throw new RuntimeException('فایل فاکتور را انتخاب کنید.');
    }
    $fileInfo = food_reconciliation_validate_file($upload);
    $duplicate = db()->prepare('SELECT id FROM food_monthly_invoices WHERE invoice_month = ? AND supplier_name = ? AND invoice_number = ? LIMIT 1');
    $duplicate->execute([$monthStart, $supplier, $invoiceNumber]);
    if ($duplicate->fetchColumn() !== false) {
        throw new RuntimeException('این شمارهٔ فاکتور برای همین ماه و پیمانکار قبلاً ثبت شده است.');
    }

    $directory = food_reconciliation_upload_directory();
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('پوشهٔ امن نگه‌داری فاکتورها ساخته نشد.');
    }
    @file_put_contents($directory . '/.htaccess', "Require all denied\nOptions -Indexes\n");
    @file_put_contents($directory . '/web.config', '<?xml version="1.0" encoding="UTF-8"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security><directoryBrowse enabled="false"/></system.webServer></configuration>');
    $storedName = bin2hex(random_bytes(24)) . '.' . $fileInfo['extension'];
    $destination = $directory . '/' . $storedName;
    if (!move_uploaded_file((string) $upload['tmp_name'], $destination)) {
        throw new RuntimeException('ذخیرهٔ امن فایل فاکتور ناموفق بود.');
    }
    @chmod($destination, 0640);
    $sha256 = hash_file('sha256', $destination) ?: '';
    try {
        $insert = db()->prepare('INSERT INTO food_monthly_invoices
            (invoice_month, invoice_date, supplier_name, invoice_number, portion_count, amount_toman,
             original_name, stored_name, mime_type, size_bytes, sha256, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->execute([
            $monthStart, $invoiceDate, $supplier, $invoiceNumber, $portions, $amount,
            $fileInfo['original_name'], $storedName, $fileInfo['mime_type'], $fileInfo['size_bytes'], $sha256,
            (int) ($user['id'] ?? 0),
        ]);
    } catch (Throwable $e) {
        @unlink($destination);
        if ($e instanceof PDOException && (string) $e->getCode() === '23000') {
            throw new RuntimeException('این شمارهٔ فاکتور برای همین ماه و پیمانکار قبلاً ثبت شده است.');
        }
        throw $e;
    }
    $id = (int) db()->lastInsertId();
    try {
        food_order_audit('food_monthly_invoice_uploaded', $user, 'food_monthly_invoice', $id, null, [
            'month' => food_reconciliation_month_key($monthStart),
            'invoice_number' => $invoiceNumber,
            'portion_count' => $portions,
            'amount_toman' => $amount,
            'file_size' => $fileInfo['size_bytes'],
        ]);
    } catch (Throwable $e) {
        app_error_log('[food-reconciliation] invoice audit write failed: ' . $e->getMessage());
    }
    return ['ok' => true, 'id' => $id, 'message' => 'فاکتور ماهانه و تعداد پرس آن ثبت شد.'];
}

function food_reconciliation_download_invoice(int $invoiceId, array $user): never
{
    food_order_require_permission($user, 'food.reports');
    food_reconciliation_schema_ensure();
    $stmt = db()->prepare('SELECT stored_name, original_name, mime_type, size_bytes FROM food_monthly_invoices WHERE id = ? LIMIT 1');
    $stmt->execute([$invoiceId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        exit('فاکتور پیدا نشد.');
    }
    $stored = (string) ($row['stored_name'] ?? '');
    if (!preg_match('/^[a-f0-9]{48}\.(pdf|jpg|jpeg|png|xls|xlsx)$/', $stored)) {
        http_response_code(404);
        exit('فاکتور پیدا نشد.');
    }
    $base = realpath(food_reconciliation_upload_directory());
    $path = $base ? realpath($base . DIRECTORY_SEPARATOR . $stored) : false;
    $prefix = $base ? rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '';
    if (!$base || !$path || !str_starts_with($path, $prefix) || !is_file($path)) {
        http_response_code(404);
        exit('فایل فاکتور روی سرور پیدا نشد.');
    }
    $original = str_replace(["\r", "\n", "\0"], '', (string) ($row['original_name'] ?? 'invoice'));
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . (string) filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store, max-age=0');
    header('Content-Security-Policy: sandbox');
    header('Content-Disposition: attachment; filename="food-invoice-' . $invoiceId . '.' . pathinfo($stored, PATHINFO_EXTENSION) . '"; filename*=UTF-8\'\'' . rawurlencode($original));
    readfile($path);
    exit;
}

function food_reconciliation_export(string $fromRaw, string $toRaw, string $mealRaw, array $user, string $monthRaw = ''): never
{
    food_order_require_permission($user, 'food.reports');
    $fromIso = food_order_iso_date($fromRaw) ?: '';
    $fromJalali = food_order_jalali_label($fromIso);
    $reportMonth = trim($monthRaw) !== ''
        ? $monthRaw
        : (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $fromJalali) ? substr($fromJalali, 0, 7) : substr($fromIso, 0, 7));
    $report = food_reconciliation_report($fromRaw, $toRaw, $mealRaw, $reportMonth);
    $headers = [
        'تاریخ', 'وعده', 'سفارش فعال پرسنل', 'درخواست فعال مهمان', 'تقاضای فعال',
        'لغوشدهٔ پرسنل', 'لغوشدهٔ مهمان',
        'پرس سفارش‌داده‌شده', 'پرس تحویل واقعی', 'توزیع پرسنل', 'توزیع مهمان',
        'کل توزیع', 'پرس باقیمانده', 'کسری/اضافهٔ سفارش', 'غایبین', 'سرنوشت باقیمانده',
        'وضعیت', 'یادداشت',
        'ماه فاکتور', 'تعداد فاکتور ماه', 'پرس طبق فاکتور ماه', 'جمع تحویل واقعی روزانهٔ ماه',
        'اختلاف فاکتور − تحویل ماه', 'وضعیت مقایسهٔ ماهانه',
    ];
    $gapText = static function (?int $gap): string {
        if ($gap === null) {
            return '—';
        }
        if ($gap > 0) {
            return $gap . ' کسر سفارش';
        }
        if ($gap < 0) {
            return abs($gap) . ' اضافه سفارش';
        }
        return '۰ هم‌سطح';
    };
    $data = [];
    foreach ($report['items'] as $item) {
        $data[] = [
            $item['date_jalali'],
            $item['meal_type'] === 'dinner' ? 'شام' : 'ناهار',
            $item['active_employee_orders'], $item['active_guest_meals'], $item['active_demand'],
            $item['cancelled_employee_orders'], $item['cancelled_guest_meals'],
            $item['supplier_order_quantity'] ?? '—', $item['received_quantity'] ?? '—',
            $item['employee_served_quantity'] ?? '—', $item['guest_served_quantity'] ?? '—',
            $item['served_total'] ?? '—', $item['remaining_quantity'] ?? '—',
            $gapText($item['order_gap']), $item['absentees'],
            $item['leftover_disposition_summary'] ?? '—',
            $item['status'] === 'closed' ? 'بسته' : ($item['status'] === 'ordered' ? 'سفارش ثبت شد' : 'بدون ثبت خرید'),
            $item['note'], '', '', '', '', '', '',
        ];
    }
    $monthSummary = $report['month_summary'];
    $comparisonStatus = $monthSummary['invoice_difference'] !== null
        ? 'قابل مقایسه'
        : ($monthSummary['daily_received_missing_rows'] > 0 ? 'ثبت روزانه ناقص است'
            : ($monthSummary['invoice_count'] === 0 ? 'فاکتور ثبت نشده'
                : ($monthSummary['daily_received_recorded_rows'] === 0 ? 'تحویل روزانه ثبت نشده' : 'اطلاعات مقایسه کامل نیست')));
    $monthlyRow = array_fill(0, count($headers), '');
    $monthlyRow[0] = 'خلاصهٔ ماهانه';
    $monthlyRow[1] = 'فاکتور و تحویل مستقل‌اند و با هم جمع نمی‌شوند';
    $monthlyRow[18] = $report['month'];
    $monthlyRow[19] = $monthSummary['invoice_count'];
    $monthlyRow[20] = $monthSummary['invoice_portions'];
    $monthlyRow[21] = $monthSummary['daily_received_portions'];
    $monthlyRow[22] = $monthSummary['invoice_difference'] ?? '—';
    $monthlyRow[23] = $comparisonStatus;
    $data[] = $monthlyRow;
    foreach ($data as &$row) {
        foreach ($row as &$value) {
            $value = food_order_export_safe_value($value);
        }
        unset($value);
    }
    unset($row);
    food_reconciliation_audit('food_meal_reconciliation_exported', $user, 0, null, [
        'from' => $report['from'], 'to' => $report['to'], 'meal_type' => $report['meal_type'], 'rows' => count($data),
    ]);
    $range = $report['from'] === $report['to']
        ? food_order_jalali_label($report['from'])
        : food_order_jalali_label($report['from']) . ' تا ' . food_order_jalali_label($report['to']);
    $summary = $report['summary'];
    $monthSummary = $report['month_summary'];
    $monthlyDifference = $monthSummary['invoice_difference'] === null ? '—' : (string) $monthSummary['invoice_difference'];
    $title = 'تطبیق سفارش و توزیع غذا | تحویل واقعی بازه: ' . $summary['received_quantity']
        . ' پرس | فاکتور ماه ' . $report['month'] . ': ' . $monthSummary['invoice_portions']
        . ' پرس | تحویل واقعی ماه: ' . $monthSummary['daily_received_portions']
        . ' پرس | اختلاف مستقل فاکتور − تحویل: ' . $monthlyDifference . ' پرس';
    $token = str_replace('-', '', $report['from']) . '-' . str_replace('-', '', $report['to']);
    food_order_send_xlsx('تطبیق-غذا-' . $token . '.xlsx', $title, $headers, $data, $range);
}

function food_reconciliation_api_handle(string $route, string $method, array $user): never
{
    if ($method === 'GET' && $route === 'food-menu/reconciliation') {
        food_order_require_permission($user, 'food.reports');
        $today = food_order_today();
        $from = (string) ($_GET['from'] ?? $today);
        $to = (string) ($_GET['to'] ?? $today);
        $meal = (string) ($_GET['meal_type'] ?? 'all');
        $month = (string) ($_GET['month'] ?? '');
        food_order_send_json(food_reconciliation_report($from, $to, $meal, $month));
    }
    if ($method === 'POST' && $route === 'food-menu/reconciliation-save') {
        food_order_send_json(food_reconciliation_save(food_order_api_body(), $user));
    }
    if ($method === 'POST' && $route === 'food-menu/invoice-upload') {
        food_order_send_json(food_reconciliation_upload_invoice($_POST, $_FILES, $user));
    }
    if ($method === 'GET' && $route === 'food-menu/invoice-file') {
        food_reconciliation_download_invoice(max(0, (int) ($_GET['id'] ?? 0)), $user);
    }
    if ($method === 'GET' && $route === 'food-menu/reconciliation-export') {
        food_reconciliation_export(
            (string) ($_GET['from'] ?? ''),
            (string) ($_GET['to'] ?? ''),
            (string) ($_GET['meal_type'] ?? 'all'),
            $user,
            (string) ($_GET['month'] ?? '')
        );
    }
    food_order_send_json(['error' => 'مسیر تطبیق غذا پیدا نشد.'], 404);
}
