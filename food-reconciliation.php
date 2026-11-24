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
    $required = ['food_meal_reconciliations', 'food_monthly_invoices', 'food_monthly_prices', 'food_leftover_allocations', 'food_reconciliation_change_audit'];
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
 * - تقاضای کل = سفارش فعال پرسنل + درخواست فعال مهمان (نفرات)؛ لغوشده‌ها داخل این جمع نیستند
 * - «کسری/اضافهٔ سفارش» بر همین تقاضای کل استوار است
 * - مانده = پرس سفارش/تحویل واقعی − کل تحویل‌داده‌شده‌ها (توزیع پرسنل + توزیع مهمان)؛ می‌تواند منفی باشد
 * - فاصلهٔ سفارش = تقاضای کل − پرس سفارش/تحویل پیمانکار؛ مثبت = کسر سفارش، منفی = اضافه سفارش
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
    $actualSummary = [];
    try {
        if (function_exists('food_guest_actuals_schema_ensure')) {
            $guestActual = db()->prepare("SELECT food_date, meal_type, GROUP_CONCAT(CONCAT(root_name, ' ', actual_count) ORDER BY actual_count DESC SEPARATOR ' · ') AS summary, SUM(actual_count) AS total FROM food_guest_actuals WHERE food_date BETWEEN ? AND ? GROUP BY food_date, meal_type");
            $guestActual->execute([$from, $to]);
            foreach ($guestActual->fetchAll(PDO::FETCH_ASSOC) as $actualRow) {
                $actualSummary[$actualRow['food_date'] . "\0" . $actualRow['meal_type']] = [
                    'summary' => (string) $actualRow['summary'],
                    'total' => (int) $actualRow['total'],
                ];
            }
        }
    } catch (Throwable $actualError) {
        app_error_log('[food-reconciliation] guest actuals summary unavailable: ' . $actualError->getMessage());
    }
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
            'total_employee_orders' => $currentEmployees + (int) $row['cancelled_employee_orders'],
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
            'guest_actuals_summary' => $actualSummary[$key]['summary'] ?? null,
            'guest_actuals_total' => $actualSummary[$key]['total'] ?? null,
            'status' => $isRegistered ? (string) $row['status'] : 'not_ordered',
            'note' => (string) ($row['note'] ?? ''),
            'snapshot_at' => (string) ($row['snapshot_at'] ?? ''),
            'ordered_at' => (string) ($row['ordered_at'] ?? ''),
            'closed_at' => (string) ($row['closed_at'] ?? ''),
        ];
    }
    $ids=array_values(array_filter(array_map(static fn($item)=>$item['id']??null,$items)));
    $allocationMap=[];
    if($ids){
        $marks=implode(',',array_fill(0,count($ids),'?'));
        $allocStmt=db()->prepare("SELECT a.reconciliation_id,a.employee_id,a.quantity,COALESCE(u.full_name,CONCAT('کاربر ',a.employee_id)) AS employee_name FROM food_leftover_allocations a LEFT JOIN users u ON u.id=a.employee_id WHERE a.is_current=1 AND a.reconciliation_id IN ({$marks}) ORDER BY u.full_name,a.id");
        $allocStmt->execute($ids);
        foreach($allocStmt->fetchAll(PDO::FETCH_ASSOC) as $alloc){$rid=(int)$alloc['reconciliation_id'];$allocationMap[$rid][]=['employee_id'=>(int)$alloc['employee_id'],'employee_name'=>(string)$alloc['employee_name'],'quantity'=>(int)$alloc['quantity']];}
    }
    foreach($items as &$item){$item['leftover_allocations']=$item['id']!==null?($allocationMap[(int)$item['id']]??[]):[];}
    unset($item);
    return $items;
}

function food_reconciliation_invoice_rows(string $monthStart): array
{
    food_reconciliation_schema_ensure();
    $stmt = db()->prepare('SELECT id, invoice_month, invoice_date, supplier_name, invoice_number,
            portion_count, amount_irr, finalized_at, original_name, mime_type, size_bytes, created_at
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
            'amount_irr' => $row['amount_irr'] === null ? null : (int) $row['amount_irr'],
            'is_finalized' => !empty($row['finalized_at']),
            'original_name' => (string) $row['original_name'],
            'mime_type' => (string) $row['mime_type'],
            'size_bytes' => (int) $row['size_bytes'],
            'created_at' => (string) $row['created_at'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function food_reconciliation_month_foods(string $from, string $to, string $monthStart): array
{
    $stmt = db()->prepare("SELECT food_id AS id, food_name FROM (SELECT DISTINCT c.id AS food_id,c.food_name FROM food_orders o JOIN food_calendar_items i ON i.id=o.calendar_item_id JOIN food_catalog c ON c.id=i.food_id WHERE o.food_date BETWEEN ? AND ? AND o.status='active' UNION SELECT DISTINCT c.id AS food_id,c.food_name FROM food_guest_requests g JOIN food_catalog c ON c.id=g.food_id WHERE g.request_date BETWEEN ? AND ? AND g.status='active' AND g.food_id IS NOT NULL) selected_foods ORDER BY food_name,id");
    $stmt->execute([$from, $to, $from, $to]);
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $current = db()->prepare('SELECT p.food_id,p.price_irr FROM food_monthly_prices p WHERE p.invoice_month=?');
    $current->execute([$monthStart]);
    $currentMap = [];
    foreach ($current->fetchAll(PDO::FETCH_ASSOC) as $row) $currentMap[(int)$row['food_id']] = (int)$row['price_irr'];
    $finalStmt=db()->prepare('SELECT 1 FROM food_monthly_invoices WHERE invoice_month=? AND finalized_at IS NOT NULL LIMIT 1');
    $finalStmt->execute([$monthStart]); $monthIsFinal=$finalStmt->fetchColumn()!==false;
    $previous = db()->prepare('SELECT p.food_id, p.price_irr FROM food_monthly_prices p JOIN (SELECT food_id, MAX(invoice_month) AS last_month FROM food_monthly_prices WHERE invoice_month < ? GROUP BY food_id) x ON x.food_id=p.food_id AND x.last_month=p.invoice_month');
    $previous->execute([$monthStart]);
    $previousMap = [];
    foreach ($previous->fetchAll(PDO::FETCH_ASSOC) as $row) $previousMap[(int)$row['food_id']] = (int)$row['price_irr'];
    return array_map(static function(array $food) use ($currentMap,$previousMap,$monthIsFinal): array {
        $id=(int)$food['id'];
        if($monthIsFinal && array_key_exists($id,$currentMap)) return ['food_id'=>$id,'food_name'=>(string)$food['food_name'],'price_irr'=>$currentMap[$id],'price_status'=>'final'];
        if(array_key_exists($id,$currentMap)) return ['food_id'=>$id,'food_name'=>(string)$food['food_name'],'price_irr'=>$currentMap[$id],'price_status'=>'draft'];
        return ['food_id'=>$id,'food_name'=>(string)$food['food_name'],'price_irr'=>$previousMap[$id]??null,'price_status'=>isset($previousMap[$id])?'estimated':'missing'];
    }, $foods);
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
        'invoice_amount_irr' => 0,
        'daily_received_portions' => 0,
        'daily_received_recorded_rows' => 0,
        'daily_received_missing_rows' => 0,
        'invoice_difference' => null,
    ];
    foreach ($invoices as $invoice) {
        $monthSummary['invoice_portions'] += $invoice['portion_count'];
        $monthSummary['invoice_amount_irr'] += (int) ($invoice['amount_irr'] ?? 0);
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
    $priceFoods = food_reconciliation_month_foods($monthFrom, $monthTo, $monthStart);
    $invoiceFinalized = count(array_filter($invoices, static fn(array $invoice): bool => !empty($invoice['is_finalized']))) > 0;
    return [
        'price_foods' => $priceFoods,
        'invoice_finalized' => $invoiceFinalized,
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
 * «قفل سخت» وعده/روز بسته‌شده (تصمیم مالک ۲۰۲۶-۱۱-۲۰):
 * بعد از بستن، هرگونه تغییر در روز یا وعدهٔ بسته‌شده — سفارش، لغو، تغییر برنامه،
 * درخواست/ویرایش مهمان، ثبت حضور/تحویل/چاپ — کاملاً مسدود است و هیچ راهی
 * (حتی دلیل اصلاح) آن را باز نمی‌کند.
 *
 * اگر جدول تطبیق یا ستون وضعیت در دسترس نباشد fail-open می‌شود تا نصب‌های نیمه‌ترش
 * از کار نیفتند؛ اما برای نصب کامل، بستن همیشه مسدودکننده است.
 */
function food_meal_closed_guard(string $date, string $mealType, string $action = ''): void
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return;
    try {
        $monthStart = food_reconciliation_month(substr($date, 0, 7));
        $stmt = db()->prepare('SELECT id FROM food_monthly_invoices WHERE invoice_month = ? AND finalized_at IS NOT NULL LIMIT 1');
        $stmt->execute([$monthStart]);
        if ($stmt->fetchColumn() !== false) {
            throw new RuntimeException('فاکتور ماه نهایی شده است؛ تغییر سفارش یا تطبیق در این ماه مسدود است.');
        }
    } catch (RuntimeException $e) { throw $e; }
    catch (Throwable $e) { app_error_log('[food-reconciliation] monthly lock check failed: '.$e->getMessage()); throw new RuntimeException('قفل ماهانه بررسی نشد؛ برای جلوگیری از تغییر ناخواسته درخواست رد شد.'); }
}

/**
 * باقیماندهٔ زندهٔ یک روز+وعده (تک‌منبع حقیقت برای «فرانت تازه‌سازی قبل از تأیید» و پاپ‌آپ‌های بستن).
 * هر بار لحظه‌ای از پایگاه‌داده محاسبه می‌شود — هیچ کشی بین صفحه و سرور لازم نیست.
 *
 * @return array{date:string, meal_type:string, supplier_order_quantity:?int, received_quantity:?int,
 *               employee_served_quantity:int, guest_served_quantity:int, served_total:int,
 *               remaining_quantity:?int, status:string}
 */
function food_reconciliation_day_remainder(string $date, string $mealType): array
{
    food_reconciliation_schema_ensure();
    $mealType = $mealType === 'dinner' ? 'dinner' : 'lunch';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new RuntimeException('تاریخ معتبر نیست.');
    }
    $servedMap = food_reconciliation_served_counts($date, $date);
    $key = $date . "\0" . $mealType;
    $employeeServed = (int) ($servedMap[$key]['employee'] ?? 0);
    $guestServed = (int) ($servedMap[$key]['guest'] ?? 0);
    $servedTotal = $employeeServed + $guestServed;
    $stmt = db()->prepare('SELECT supplier_order_quantity, received_quantity, status FROM food_meal_reconciliations WHERE food_date = ? AND meal_type = ? LIMIT 1');
    $stmt->execute([$date, $mealType]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $monthStart=food_reconciliation_month(substr($date,0,7));
    $finalStmt=db()->prepare('SELECT id FROM food_monthly_invoices WHERE invoice_month=? AND finalized_at IS NOT NULL LIMIT 1');
    $finalStmt->execute([$monthStart]);
    $monthFinalized=$finalStmt->fetchColumn()!==false;
    $supplier = $existing !== null && $existing['supplier_order_quantity'] !== null ? (int) $existing['supplier_order_quantity'] : null;
    $received = $existing !== null && $existing['received_quantity'] !== null ? (int) $existing['received_quantity'] : $supplier;
    return [
        'date' => $date,
        'meal_type' => $mealType,
        'supplier_order_quantity' => $supplier,
        'received_quantity' => $received,
        'employee_served_quantity' => $employeeServed,
        'guest_served_quantity' => $guestServed,
        'served_total' => $servedTotal,
        'remaining_quantity' => $received === null ? null : $received - $servedTotal,
        'status' => $existing !== null ? (string) $existing['status'] : 'not_ordered',
        'month_finalized' => $monthFinalized,
    ];
}

/**
 * اعتبارسنجی «تعیین تکلیف غذای باقیمانده».
 * اگر مانده (پرس سفارش/تحویل واقعی − کل تحویل‌داده‌شده‌ها) کمتر از ۱ باشد، تعیین تکلیف لازم نیست و همه‌چیز null می‌شود.
 * اگر مانده ≥ ۱ باشد: حداقل یک گزینه الزامی است و مجموع تعدادها باید **دقیقاً** برابر مانده باشد (تصمیم مالک ۲۰۲۶-۱۱-۲۰).
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
    $allocations = $body['leftover_allocations'] ?? [];
    if (!is_array($allocations)) throw new RuntimeException('فهرست تخصیص پرسنل معتبر نیست.');
    $allocationTotal = 0; $seenEmployees = [];
    foreach ($allocations as $allocation) {
        if (!is_array($allocation)) continue;
        $employeeId = (int)($allocation['employee_id'] ?? 0);
        $quantity = food_reconciliation_count($allocation['quantity'] ?? '', 'تعداد تخصیص فرد', false);
        if ($employeeId < 1 || $quantity < 1 || isset($seenEmployees[$employeeId])) throw new RuntimeException('تخصیص پرسنل تکراری یا نامعتبر است.');
        $seenEmployees[$employeeId] = true; $allocationTotal += $quantity;
    }
    if ($allocationTotal !== $staff) throw new RuntimeException('مجموع تخصیص فردی باید دقیقاً برابر تعداد «توزیع بین پرسنل» باشد.');
    $sum = $staff + $waste + $keep + $other;
    if ($sum === 0) {
        throw new RuntimeException('برای بستن این روز باید سرنوشت غذای باقیمانده را مشخص کنید؛ حداقل یک گزینه را با تعداد وارد کنید.');
    }
    if ($sum !== $remaining) {
        throw new RuntimeException('مجموع تعیین تکلیف (' . $sum . ' پرس) باید دقیقاً برابر با ماندهٔ امروز (' . $remaining . ' پرس) باشد؛ اختلاف: ' . abs($remaining - $sum) . ' پرس.');
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
    // ۱٫۶۳ — ادغام «پرس سفارش‌داده‌شده از پیمانکار» و «پرس تحویل واقعی» در یک عدد واحد (تصمیم مالک ۲۰۲۶-۱۱-۱۰).
    // فرم جدید فقط portion_quantity می‌فرستد؛ payload دو فیلدیِ قبلی همچنان پذیرفته می‌شود (سازگاری با تب/نسخهٔ باز).
    $portionPayload = array_key_exists('portion_quantity', $body);
    if ($portionPayload) {
        $portion = food_reconciliation_count((string) $body['portion_quantity'], 'تعداد پرس سفارش/تحویل واقعی', $action === 'close');
        $supplierOrder = $portion;
        $received = $portion;
    } else {
        $supplierOrder = food_reconciliation_count($body['supplier_order_quantity'] ?? '', 'تعداد پرس سفارش‌داده‌شده', $action === 'close');
        $received = food_reconciliation_count($body['received_quantity'] ?? '', 'تعداد پرس تحویل واقعی', true);
    }
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
            throw new RuntimeException('برای بستن آمار، مقدار «پرس سفارش/تحویل واقعی» را وارد کنید؛ در صورت صفر بودن، عدد صفر بنویسید.');
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
        $monthStart = food_reconciliation_month(substr($date, 0, 7));
        $lock = $pdo->prepare('SELECT id FROM food_monthly_invoices WHERE invoice_month = ? AND finalized_at IS NOT NULL LIMIT 1 FOR UPDATE');
        $lock->execute([$monthStart]);
        if ($lock->fetchColumn() !== false) throw new RuntimeException('فاکتور این ماه نهایی شده است؛ روزهای ماه دیگر قابل ویرایش نیستند.');
        $counts = food_reconciliation_order_counts($date, $meal);
        $snapshotEmployee = $existing !== null && $existing['employee_orders_at_order'] !== null
            ? (int) $existing['employee_orders_at_order'] : $counts['active_employee_orders'];
        $snapshotGuest = $existing !== null && $existing['guest_meals_at_order'] !== null
            ? (int) $existing['guest_meals_at_order'] : $counts['active_guest_meals'];
        $orderedQuantity = $supplierOrder ?? (int) ($existing['supplier_order_quantity'] ?? -1);
        if ($orderedQuantity < 0) {
            throw new RuntimeException('تعداد پرس سفارش/تحویل واقعی پیمانکار را وارد کنید.');
        }
        $oldValue = $existing;
        if ($existing !== null) { $oldAllocStmt=$pdo->prepare('SELECT employee_id,quantity,created_by,created_at FROM food_leftover_allocations WHERE reconciliation_id=? AND is_current=1 ORDER BY id'); $oldAllocStmt->execute([(int)$existing['id']]); $oldValue['leftover_allocations']=$oldAllocStmt->fetchAll(PDO::FETCH_ASSOC); }
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
        if ($action === 'close') {
            $delAlloc = $pdo->prepare('UPDATE food_leftover_allocations SET is_current=0 WHERE reconciliation_id = ? AND is_current=1'); $delAlloc->execute([$id]);
            $allocations = is_array($body['leftover_allocations'] ?? null) ? $body['leftover_allocations'] : [];
            $allocIns = $pdo->prepare('INSERT INTO food_leftover_allocations (reconciliation_id, employee_id, quantity, created_by) VALUES (?,?,?,?)');
            foreach ($allocations as $allocation) if (is_array($allocation)) {
                $personId=(int)$allocation['employee_id'];
                $personCheck=$pdo->prepare("SELECT id FROM users WHERE id=? AND company_id=? AND is_active=1 AND role IN ('user','local_user') LIMIT 1");
                $personCheck->execute([$personId,(int)($user['company_id']??1)]);
                if($personCheck->fetchColumn()===false) throw new RuntimeException('یکی از پرسنل انتخاب‌شده دیگر فعال یا مجاز نیست؛ فهرست را تازه‌سازی کنید.');
                $allocIns->execute([$id,$personId,(int)$allocation['quantity'],(int)($user['id']??0)]);
            }
        }
        $auditAllocations=$action==='close'&&is_array($body['leftover_allocations']??null)?$body['leftover_allocations']:($oldValue['leftover_allocations']??[]);
        $auditNew=['food_date'=>$date,'meal_type'=>$meal,'supplier_order_quantity'=>$orderedQuantity,'received_quantity'=>$receivedValue,'status'=>$nowAction,'leftover_disposition'=>$disposition,'leftover_allocations'=>$auditAllocations];
        $audit=$pdo->prepare('INSERT INTO food_reconciliation_change_audit (reconciliation_id,action,changed_by,reason,old_value,new_value) VALUES (?,?,?,?,?,?)');
        $audit->execute([$id,'food_meal_reconciliation_saved',(int)($user['id']??0),$reason!==''?$reason:null,json_encode($oldValue,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),json_encode($auditNew,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)]);
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
            'leftover_allocations' => is_array($body['leftover_allocations'] ?? null) ? $body['leftover_allocations'] : [],
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
    $monthStart = food_reconciliation_month($post['invoice_month'] ?? '');
    $invoiceDate = food_order_iso_date((string)($post['invoice_date'] ?? ''));
    [$monthFrom, $monthToExclusive] = food_reconciliation_month_range($monthStart);
    if ($invoiceDate === null || $invoiceDate < $monthFrom || $invoiceDate >= $monthToExclusive) throw new RuntimeException('تاریخ فاکتور باید در همان ماه انتخاب‌شده باشد.');
    $supplier=trim((string)($post['supplier_name']??'')); $number=trim((string)($post['invoice_number']??''));
    if($supplier===''||$number==='') throw new RuntimeException('نام پیمانکار و شمارهٔ فاکتور الزامی است.');
    $supplier=function_exists('mb_substr')?mb_substr($supplier,0,190,'UTF-8'):substr($supplier,0,190);
    $number=function_exists('mb_substr')?mb_substr($number,0,100,'UTF-8'):substr($number,0,100);
    $portions=food_reconciliation_count($post['portion_count']??'','تعداد پرس طبق فاکتور',false,1000000);
    if($portions<1) throw new RuntimeException('تعداد پرس فاکتور باید دست‌کم یک باشد.');
    $amountRaw=trim(food_order_normalize_digits($post['amount_irr']??'')); $amountRaw=str_replace([',','٬',' '],'',$amountRaw);
    if(!preg_match('/^\d+$/',$amountRaw)||strlen(ltrim($amountRaw,'0'))>15) throw new RuntimeException('مبلغ کل فاکتور به ریال الزامی و باید عدد صحیح مثبت باشد.');
    $amount=(int)$amountRaw; if($amount<1) throw new RuntimeException('مبلغ فاکتور باید بزرگ‌تر از صفر باشد.');
    $upload=$files['file']??null; if(!is_array($upload)) throw new RuntimeException('فایل فاکتور را انتخاب کنید.');
    $fileInfo=food_reconciliation_validate_file($upload);
    $rawPrices=json_decode((string)($post['prices_json']??''),true);
    if(!is_array($rawPrices)) throw new RuntimeException('فهرست قیمت غذاها کامل نیست.');
    $foods=food_reconciliation_month_foods($monthFrom,(new DateTimeImmutable($monthToExclusive))->modify('-1 day')->format('Y-m-d'),$monthStart);
    $requiredIds=array_map(static fn($f)=>(int)$f['food_id'],$foods); sort($requiredIds);
    $prices=[];
    foreach($rawPrices as $entry){
        if(!is_array($entry)) continue;
        $id=(int)($entry['food_id']??0); $raw=trim(food_order_normalize_digits($entry['price_irr']??'')); $raw=str_replace([',','٬',' '],'',$raw);
        if($id<1||!preg_match('/^\d+$/',$raw)||strlen(ltrim($raw,'0'))>12||(int)$raw<1) throw new RuntimeException('قیمت ریالی همهٔ غذاهای فهرست را به‌صورت عدد مثبت وارد کنید.');
        if(isset($prices[$id])) throw new RuntimeException('یک غذا در فهرست قیمت تکرار شده است.');
        $prices[$id]=(int)$raw;
    }
    $submittedIds=array_keys($prices); sort($submittedIds);
    if($submittedIds!==$requiredIds) throw new RuntimeException('فهرست غذاهای قیمت‌گذاری‌شده با منوی ماه انتخاب‌شده همخوان نیست؛ گزارش را تازه‌سازی و دوباره تلاش کنید.');
    $directory=food_reconciliation_upload_directory();
    if(!is_dir($directory)&&!mkdir($directory,0750,true)&&!is_dir($directory)) throw new RuntimeException('پوشهٔ امن نگه‌داری فاکتورها ساخته نشد.');
    @file_put_contents($directory.'/.htaccess',"Require all denied\nOptions -Indexes\n");
    $storedName=bin2hex(random_bytes(24)).'.'.$fileInfo['extension']; $destination=$directory.'/'.$storedName;
    if(!move_uploaded_file((string)$upload['tmp_name'],$destination)) throw new RuntimeException('ذخیرهٔ امن فایل فاکتور ناموفق بود.');
    @chmod($destination,0640); $sha256=hash_file('sha256',$destination)?:''; $pdo=db();
    $oldStoredName=null; $oldInvoiceAudit=null; $oldPriceAudit=[];
    try {
        $pdo->beginTransaction();
        $check=$pdo->prepare('SELECT id,invoice_month,invoice_date,supplier_name,invoice_number,portion_count,amount_toman,amount_irr,finalized_at,original_name,stored_name,mime_type,size_bytes,sha256,uploaded_by,created_at FROM food_monthly_invoices WHERE invoice_month=? ORDER BY id LIMIT 2 FOR UPDATE');
        $check->execute([$monthStart]); $existingInvoices=$check->fetchAll(PDO::FETCH_ASSOC);
        foreach($existingInvoices as $existingInvoice) if(!empty($existingInvoice['finalized_at'])) throw new RuntimeException('برای این ماه فاکتور قبلاً نهایی شده است.');
        if(count($existingInvoices)===1){
            $oldInvoiceAudit=$existingInvoices[0];
            $oldPriceStmt=$pdo->prepare('SELECT food_id,price_irr,invoice_id FROM food_monthly_prices WHERE invoice_month=? ORDER BY food_id');
            $oldPriceStmt->execute([$monthStart]); $oldPriceAudit=$oldPriceStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if(count($existingInvoices)>1) throw new RuntimeException('برای این ماه چند فاکتور قدیمی وجود دارد؛ برای جلوگیری از ثبت تکراری ابتدا سوابق ماه را بررسی کنید.');
        if($existingInvoices){
            $invoiceId=(int)$existingInvoices[0]['id']; $oldStoredName=(string)$existingInvoices[0]['stored_name'];
            $ins=$pdo->prepare('UPDATE food_monthly_invoices SET invoice_date=?,supplier_name=?,invoice_number=?,portion_count=?,amount_toman=NULL,amount_irr=?,original_name=?,stored_name=?,mime_type=?,size_bytes=?,sha256=?,uploaded_by=?,finalized_at=CURRENT_TIMESTAMP WHERE id=? AND finalized_at IS NULL');
            $ins->execute([$invoiceDate,$supplier,$number,$portions,$amount,$fileInfo['original_name'],$storedName,$fileInfo['mime_type'],$fileInfo['size_bytes'],$sha256,(int)($user['id']??0),$invoiceId]);
            if($ins->rowCount()!==1) throw new RuntimeException('فاکتور قدیمی هم‌زمان تغییر کرده است؛ صفحه را تازه‌سازی کنید.');
        } else {
            $ins=$pdo->prepare('INSERT INTO food_monthly_invoices (invoice_month,invoice_date,supplier_name,invoice_number,portion_count,amount_toman,amount_irr,finalized_at,original_name,stored_name,mime_type,size_bytes,sha256,uploaded_by) VALUES (?,?,?,?,?,NULL,?,CURRENT_TIMESTAMP,?,?,?,?,?,?)');
            $ins->execute([$monthStart,$invoiceDate,$supplier,$number,$portions,$amount,$fileInfo['original_name'],$storedName,$fileInfo['mime_type'],$fileInfo['size_bytes'],$sha256,(int)($user['id']??0)]);
            $invoiceId=(int)$pdo->lastInsertId();
        }
        $priceIns=$pdo->prepare('INSERT INTO food_monthly_prices (invoice_month,food_id,price_irr,invoice_id,created_by) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE price_irr=VALUES(price_irr),invoice_id=VALUES(invoice_id),created_by=VALUES(created_by),created_at=CURRENT_TIMESTAMP');
        foreach($prices as $foodId=>$price) $priceIns->execute([$monthStart,$foodId,$price,$invoiceId,(int)($user['id']??0)]);
        $audit=$pdo->prepare('INSERT INTO food_reconciliation_change_audit (reconciliation_id,action,changed_by,old_value,new_value) VALUES (0,?,?,?,?)');
        $audit->execute(['food_monthly_invoice_finalized',(int)($user['id']??0),json_encode(['invoice'=>$oldInvoiceAudit,'prices'=>$oldPriceAudit],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),json_encode(['invoice_id'=>$invoiceId,'invoice_month'=>$monthStart,'amount_irr'=>$amount,'food_prices'=>$prices],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)]);
        $pdo->commit();
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); @unlink($destination); if($e instanceof PDOException&&(string)$e->getCode()==='23000') throw new RuntimeException('فاکتور یا قیمت این ماه قبلاً ثبت شده است.'); throw $e; }
    if($oldStoredName!==null && $oldStoredName!==$storedName && preg_match('/^[a-f0-9]{48}\\.(pdf|jpg|jpeg|png|xls|xlsx)$/',$oldStoredName)) @unlink($directory.'/'.$oldStoredName);
    try { food_order_audit('food_monthly_invoice_finalized',$user,'food_monthly_invoice',$invoiceId,null,['month'=>food_reconciliation_month_key($monthStart),'invoice_number'=>$number,'portion_count'=>$portions,'amount_irr'=>$amount,'food_prices'=>$prices]); } catch(Throwable $e){app_error_log('[food-reconciliation] invoice audit failed: '.$e->getMessage());}
    return ['ok'=>true,'id'=>$invoiceId,'message'=>'فاکتور، فایل و قیمت‌های ماه با موفقیت نهایی شدند؛ روزهای این ماه قفل شد.'];
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
        'تاریخ', 'وعده', 'کل سفارشات پرسنل', 'تقاضای فعال پرسنل', 'لغوشدهٔ پرسنل',
        'درخواست مهمان (نفرات)',
        'پرس سفارش/تحویل واقعی پیمانکار', 'توزیع پرسنل', 'مهمان توزیع‌شده',
        'کل توزیع', 'پرس باقیمانده', 'کسری/اضافهٔ سفارش', 'غایبین', 'سرنوشت باقیمانده',
        'وضعیت', 'یادداشت',
        'مهمان واقعی (به‌تفکیک بخش)',
        'ماه فاکتور', 'تعداد فاکتور ماه', 'پرس طبق فاکتور ماه', 'جمع تحویل واقعی روزانهٔ ماه',
        'اختلاف فاکتور − تحویل ماه', 'وضعیت مقایسهٔ ماهانه', 'مبلغ کل فاکتور ماه (ریال)', 'تخصیص فردی غذای باقیمانده',
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
            $item['total_employee_orders'], $item['active_employee_orders'], $item['cancelled_employee_orders'],
            $item['active_guest_meals'],
            ($item['received_quantity'] ?? $item['supplier_order_quantity']) ?? '—',
            $item['employee_served_quantity'] ?? '—', $item['guest_served_quantity'] ?? '—',
            $item['served_total'] ?? '—', $item['remaining_quantity'] ?? '—',
            $gapText($item['order_gap']), $item['absentees'],
            $item['leftover_disposition_summary'] ?? '—',
            $item['status'] === 'closed' ? 'بسته' : ($item['status'] === 'ordered' ? 'سفارش ثبت شد' : 'بدون ثبت خرید'),
            $item['note'],
            $item['guest_actuals_summary'] ?? '—',
            '', '', '', '', '', '', '',
            implode(' · ',array_map(static fn($a)=>(string)$a['employee_name'].' × '.(string)$a['quantity'],$item['leftover_allocations']??[])),
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
    $monthlyRow[17] = $report['month'];
    $monthlyRow[18] = $monthSummary['invoice_count'];
    $monthlyRow[19] = $monthSummary['invoice_portions'];
    $monthlyRow[20] = $monthSummary['daily_received_portions'];
    $monthlyRow[21] = $monthSummary['invoice_difference'] ?? '—';
    $monthlyRow[22] = $comparisonStatus;
    $monthlyRow[23] = $monthSummary['invoice_amount_irr'];
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
    $title = 'تطبیق سفارش و توزیع غذا | پرس سفارش/تحویل پیمانکار بازه: ' . $summary['received_quantity']
        . ' پرس | فاکتور ماه ' . $report['month'] . ': ' . $monthSummary['invoice_portions']
        . ' پرس | تحویل واقعی ماه: ' . $monthSummary['daily_received_portions']
        . ' پرس | اختلاف مستقل فاکتور − تحویل: ' . $monthlyDifference . ' پرس';
    $token = str_replace('-', '', $report['from']) . '-' . str_replace('-', '', $report['to']);
    food_order_send_xlsx('تطبیق-غذا-' . $token . '.xlsx', $title, $headers, $data, $range);
}

/**
 * ۱.۶۳.۳ (C1) — پاپ‌آپ انتساب مهمان هنگام بستن روز (بند ۱۲ طرح کاربر).
 * فهرست ۶ بخش (مدیرعامل، واحدهای مستقل، معاونت‌ها، مدیریت مستقل، دفتر مدیرعامل، شرکت همکار)
 * + ستون خواندنی «درخواست‌شده» (از درخواست‌های فعال همان روز/وعده، نرمال‌شده به سرشاخهٔ ۶ بخشی)
 * + ورودی «واقعی»؛ مجموع باید دقیقاً برابر فیش‌های مهمان چاپ‌شده باشد.
 */
function food_guest_actuals_schema_ensure(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $exists = false;
    try {
        $stmt = db()->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'food_guest_actuals'");
        $stmt->execute();
        $exists = (int) $stmt->fetchColumn() === 1;
    } catch (Throwable $e) {
        $exists = false;
    }
    if (!$exists) {
        throw new RuntimeException('ثبت انتساب مهمان آماده نیست؛ ابتدا مهاجرت ۰۳۸ (food_guest_actuals) را روی پایگاه‌داده اجرا کنید.');
    }
    $done = true;
}

/** درخواست‌های فعال همان روز/وعده را به نزدیک‌ترین سرشاخهٔ ۶ بخشی نرمال می‌کند. خروجی: [map rootId=>qty, noneQty]. */
function food_guest_actuals_requested_map(string $date, string $mealType): array
{
    $map = [];
    $none = 0;
    try {
        $stmt = db()->prepare("SELECT deputy_unit_id, SUM(guest_count) AS qty FROM food_guest_requests WHERE request_date = ? AND meal_type = ? AND status = 'active' GROUP BY deputy_unit_id");
        $stmt->execute([$date, $mealType]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stored = $row['deputy_unit_id'] === null ? 0 : (int) $row['deputy_unit_id'];
            $rootId = function_exists('org_stored_attribution_root_id') ? org_stored_attribution_root_id($stored) : $stored;
            $qty = (int) $row['qty'];
            if ($rootId <= 0) {
                $none += $qty;
            } else {
                $map[$rootId] = ($map[$rootId] ?? 0) + $qty;
            }
        }
    } catch (Throwable $e) {
        app_error_log('[food-guest-actuals] requested map failed: ' . $e->getMessage());
    }
    return [$map, $none];
}

/** بارگذاری ردیف‌های واقعی ثبت‌شدهٔ همان روز/وعده (برای پیش‌پرکردن پاپ‌آپ و شرط ردیف «بدون جایگاه»). */
function food_guest_actuals_stored_map(string $date, string $mealType): array
{
    $map = [];
    try {
        $stmt = db()->prepare('SELECT root_unit_id, root_name, root_type, actual_count FROM food_guest_actuals WHERE food_date = ? AND meal_type = ?');
        $stmt->execute([$date, $mealType]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['root_unit_id']] = [
                'name' => (string) $row['root_name'],
                'type' => (string) $row['root_type'],
                'actual' => (int) $row['actual_count'],
            ];
        }
    } catch (Throwable $e) {
        app_error_log('[food-guest-actuals] stored map failed: ' . $e->getMessage());
    }
    return $map;
}

/** نام و نوع نمایشی یک واحد، حتی اگر دیگر فعال نباشد؛ برای ردیف‌های تاریخی. */
function food_guest_actuals_unit_meta(array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0)));
    if ($ids === []) {
        return [];
    }
    $map = [];
    try {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT id, name, unit_type FROM org_units WHERE id IN ($in)");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $unit = ['name' => (string) $row['name'], 'unit_type' => (string) $row['unit_type']];
            $map[(int) $row['id']] = [
                'name' => function_exists('org_root_display_name') ? org_root_display_name($unit) : (string) $row['name'],
                'type' => (string) $row['unit_type'],
            ];
        }
    } catch (Throwable $e) {
        app_error_log('[food-guest-actuals] unit meta failed: ' . $e->getMessage());
    }
    return $map;
}

/** ترتیب نمایش سرشاخه‌ها: مدیرعامل، مستقل‌ها، معاونت‌ها، مدیریت مستقل، دفتر، شرکت همکار، بدون جایگاه. */
function food_guest_actuals_type_order(string $type): int
{
    return match ($type) {
        'ceo' => 0,
        'liaison' => 1,
        'deputy' => 2,
        'management' => 3,
        'office' => 4,
        'company_manager' => 5,
        default => 6,
    };
}

function food_guest_actuals_report(string $date, string $mealType): array
{
    food_reconciliation_schema_ensure();
    food_guest_actuals_schema_ensure();
    $mealType = $mealType === 'dinner' ? 'dinner' : 'lunch';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new RuntimeException('تاریخ معتبر نیست.');
    }
    $servedMap = food_reconciliation_served_counts($date, $date);
    $printed = (int) ($servedMap[$date . "\0" . $mealType]['guest'] ?? 0);
    $status = 'not_ordered';
    try {
        $stmt = db()->prepare('SELECT status FROM food_meal_reconciliations WHERE food_date = ? AND meal_type = ? LIMIT 1');
        $stmt->execute([$date, $mealType]);
        $status = (string) ($stmt->fetchColumn() ?: 'not_ordered');
    } catch (Throwable $e) {
        // fail-open: وضعیت فقط برای نمایش است
    }
    [$requested, $noneRequested] = food_guest_actuals_requested_map($date, $mealType);
    $stored = food_guest_actuals_stored_map($date, $mealType);

    $rows = [];
    // ۶ بخش فعال (اگر ماژول سازمان در دسترس باشد)
    if (function_exists('org_root_units')) {
        foreach (org_root_units() as $section) {
            $id = (int) $section['id'];
            $rows[$id] = [
                'id' => $id,
                'name' => (string) $section['name'],
                'unit_type' => (string) $section['unit_type'],
                'requested' => (int) ($requested[$id] ?? 0),
                'actual' => (int) ($stored[$id]['actual'] ?? 0),
            ];
        }
    }
    // سرشاخه‌های غیرفعالی که برای آن‌ها درخواست یا واقعی ثبت شده — ردیفشان نباید گم شود
    $missingIds = array_merge(array_keys($requested), array_keys($stored));
    $meta = food_guest_actuals_unit_meta($missingIds);
    foreach ($meta as $id => $info) {
        if (!isset($rows[$id])) {
            $rows[$id] = [
                'id' => $id,
                'name' => $info['name'],
                'unit_type' => $info['type'],
                'requested' => (int) ($requested[$id] ?? 0),
                'actual' => (int) ($stored[$id]['actual'] ?? 0),
            ];
        }
    }
    // ردیف «بدون جایگاه» فقط اگر دادهٔ تاریخی بی‌جایگاه وجود دارد یا واقعیِ 0 ثبت شده (C7)
    $noneRequestedTotal = (int) $noneRequested;
    $noneActual = (int) ($stored[0]['actual'] ?? 0);
    if ($noneRequestedTotal > 0 || $noneActual > 0 || isset($stored[0])) {
        $rows[0] = [
            'id' => 0,
            'name' => 'بدون جایگاه',
            'unit_type' => 'none',
            'requested' => $noneRequestedTotal,
            'actual' => $noneActual,
        ];
    }
    $sections = array_values($rows);
    usort($sections, static function (array $a, array $b): int {
        return [food_guest_actuals_type_order((string) $a['unit_type']), (string) $a['name']] <=> [food_guest_actuals_type_order((string) $b['unit_type']), (string) $b['name']];
    });
    $totalActual = 0;
    $totalRequested = 0;
    foreach ($sections as $row) {
        $totalActual += (int) $row['actual'];
        $totalRequested += (int) $row['requested'];
    }
    return [
        'ok' => true,
        'date' => $date,
        'meal_type' => $mealType,
        'printed' => $printed,
        'sections' => $sections,
        'none_row' => isset($rows[0]),
        'total_requested' => $totalRequested,
        'total_actual' => $totalActual,
        'saved' => $stored !== [],
        'status' => $status,
    ];
}

/** ذخیرهٔ واقعی‌های مهمان؛ مجموع باید دقیقاً برابر فیش‌های مهمان چاپ‌شده باشد (نه کمتر، نه بیشتر). */
function food_guest_actuals_save(array $body, array $user): array
{
    food_order_require_permission($user, 'food.menu_edit');
    food_reconciliation_schema_ensure();
    $date = food_order_iso_date((string) ($body['food_date'] ?? $body['date'] ?? ''));
    $mealType = strtolower(trim((string) ($body['meal_type'] ?? 'lunch')));
    if ($date === null || !in_array($mealType, ['lunch', 'dinner'], true)) {
        throw new RuntimeException('تاریخ و وعدهٔ غذا معتبر نیست.');
    }
    food_meal_closed_guard($date, $mealType, 'ثبت انتساب واقعی مهمان');
    food_guest_actuals_schema_ensure();
    $servedMap = food_reconciliation_served_counts($date, $date);
    $printed = (int) ($servedMap[$date . "\0" . $mealType]['guest'] ?? 0);
    if ($printed <= 0) {
        throw new RuntimeException('فیش مهمان چاپ‌شده‌ای برای این روز/وعده ثبت نشده است؛ انتسابی لازم نیست.');
    }
    [$requested, $noneRequested] = food_guest_actuals_requested_map($date, $mealType);

    $rawActuals = $body['actuals'] ?? null;
    if (!is_array($rawActuals)) {
        throw new RuntimeException('فهرست واقعی‌ها ارسال نشد.');
    }
    $seen = [];
    $sum = 0;
    foreach ($rawActuals as $entry) {
        if (!is_array($entry)) {
            throw new RuntimeException('قالب واقعی‌ها نامعتبر است.');
        }
        $rootId = (int) ($entry['root_unit_id'] ?? 0);
        $countRaw = food_order_normalize_digits((string) ($entry['actual_count'] ?? '0'));
        $count = filter_var($countRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
        if ($count === false) {
            throw new RuntimeException('تعداد واقعی هر بخش باید عددی بین ۰ تا ۱۰۰۰۰۰ باشد.');
        }
        if (isset($seen[$rootId])) {
            throw new RuntimeException('یک بخش در فهرست واقعی‌ها تکرار شده است.');
        }
        $seen[$rootId] = $count;
        $sum += $count;
    }
    if ($sum !== $printed) {
        throw new RuntimeException('مجموع واقعی مهمان‌ها (' . $sum . ' پرس) باید دقیقاً برابر فیش‌های مهمان چاپ‌شده (' . $printed . ' پرس) باشد.');
    }
    if (isset($seen[0])) {
        $noneActualExisting = food_guest_actuals_stored_map($date, $mealType);
        if ($noneRequested <= 0 && !isset($noneActualExisting[0])) {
            throw new RuntimeException('ردیف «بدون جایگاه» برای این روز/وعده فعال نیست.');
        }
    }
    $nonZero = array_values(array_filter(array_keys($seen), static fn (int $id): bool => $id > 0));
    $types = function_exists('org_root_types') ? org_root_types() : ['ceo', 'liaison', 'deputy', 'management', 'office', 'company_manager'];
    $meta = food_guest_actuals_unit_meta($nonZero);
    if ($nonZero) {
        $in = implode(',', array_fill(0, count($nonZero), '?'));
        $tin = implode(',', array_fill(0, count($types), '?'));
        $stmt = db()->prepare('SELECT id FROM org_units WHERE id IN (' . $in . ') AND unit_type IN (' . $tin . ')');
        $stmt->execute(array_merge($nonZero, $types));
        $valid = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        foreach ($nonZero as $id) {
            if (!in_array($id, $valid, true)) {
                throw new RuntimeException('یکی از بخش‌های ارسالی در چارت سازمانی معتبر نیست.');
            }
        }
    }
    $userId = (int) ($user['id'] ?? 0) ?: null;
    db()->beginTransaction();
    try {
        $del = db()->prepare('DELETE FROM food_guest_actuals WHERE food_date = ? AND meal_type = ?');
        $del->execute([$date, $mealType]);
        $ins = db()->prepare('INSERT INTO food_guest_actuals (food_date, meal_type, root_unit_id, root_name, root_type, requested_count, actual_count, created_by, updated_by) VALUES (?,?,?,?,?,?,?,?,?)');
        foreach ($seen as $rootId => $count) {
            if ($rootId === 0) {
                $name = 'بدون جایگاه';
                $type = 'none';
            } else {
                $name = (string) ($meta[$rootId]['name'] ?? ('بخش ' . $rootId));
                $type = (string) ($meta[$rootId]['type'] ?? '');
            }
            $ins->execute([$date, $mealType, $rootId, $name, $type, (int) ($requested[$rootId] ?? 0), $count, $userId, $userId]);
        }
        db()->commit();
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        throw $e;
    }
    if (function_exists('food_order_audit')) {
        food_order_audit('food_guest_actuals_save', $user, 'food_meal_reconciliation', 0, null, [
            'food_date' => $date, 'meal_type' => $mealType, 'printed' => $printed, 'total_actual' => $sum, 'sections' => count($seen),
        ]);
    }
    return ['ok' => true, 'printed' => $printed, 'total_actual' => $sum, 'saved' => count($seen)];
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
    if ($method === 'GET' && $route === 'food-menu/reconciliation-remainder') {
        food_order_require_permission($user, 'food.reports');
        $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
        $meal = strtolower(trim((string) ($_GET['meal_type'] ?? 'lunch')));
        if ($date === null || !in_array($meal, ['lunch', 'dinner'], true)) {
            food_order_send_json(['error' => 'تاریخ و وعده معتبر نیست.'], 400);
        }
        food_order_send_json(food_reconciliation_day_remainder($date, $meal));
    }
    if ($method === 'GET' && $route === 'food-menu/guest-actuals') {
        food_order_require_permission($user, 'food.reports');
        $date = food_order_iso_date((string) ($_GET['date'] ?? ''));
        $meal = strtolower(trim((string) ($_GET['meal_type'] ?? 'lunch')));
        if ($date === null || !in_array($meal, ['lunch', 'dinner'], true)) {
            food_order_send_json(['error' => 'تاریخ و وعده معتبر نیست.'], 400);
        }
        try {
            food_order_send_json(food_guest_actuals_report($date, $meal));
        } catch (RuntimeException $e) {
            food_order_send_json(['error' => $e->getMessage()], 503);
        }
    }
    if ($method === 'POST' && $route === 'food-menu/guest-actuals') {
        try {
            food_order_send_json(food_guest_actuals_save(food_order_api_body(), $user));
        } catch (RuntimeException $e) {
            food_order_send_json(['error' => $e->getMessage()], 422);
        }
    }
    if ($method === 'GET' && $route === 'food-menu/reconciliation-employees') {
        food_order_require_permission($user, 'food.menu');
        $stmt=db()->prepare("SELECT id, full_name, employee_number FROM users WHERE company_id=? AND is_active=1 AND role IN ('user','local_user') ORDER BY full_name LIMIT 3000");
        $stmt->execute([(int)($user['company_id']??1)]);
        food_order_send_json(['items'=>array_map(static fn($r)=>['id'=>(int)$r['id'],'name'=>(string)$r['full_name'],'employee_number'=>(string)($r['employee_number']??'')],$stmt->fetchAll(PDO::FETCH_ASSOC))]);
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
