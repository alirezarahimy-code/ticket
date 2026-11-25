<?php
declare(strict_types=1);

/**
 * لایهٔ منبع سفارش فیش.
 * در این نسخه سفارش فقط از MySQL داخلی خوانده می‌شود؛ این فایل به ODBC سفارش وصل نمی‌شود.
 * منبع بیرونی تردد در مسیر اصلی Worker دست‌نخورده باقی می‌ماند.
 */

/**
 * @param string|null $mealType 'lunch' | 'dinner' | null (هر دو وعده؛ اولویت ناهار)
 */
function food_order_internal_map(string $isoDate, ?string $mealType = null): array
{
    food_order_schema_ensure();
    $date = food_order_iso_date($isoDate);
    if ($date === null) {
        throw new RuntimeException('تاریخ نقشهٔ سفارش داخلی معتبر نیست.');
    }
    // ⚠️ پیش‌تر این پرس‌وجو فقط «lunch» را می‌خواند. نتیجه: برای شام
    // (و ناهار روزهای پنجشنبه/جمعه/تعطیل — که خودکار صادر می‌شوند) نقشه
    // خالی برمی‌گشت و ثبت دستی فیش پیام «سفارش فعال ناهار پیدا نشد»
    // می‌داد، در حالی که سفارش شام در سامانه ثبت شده بود.
    // حالا هر دو وعده خوانده می‌شود؛ در تخصیص، ناهار اولویت دارد چون
    // فیشِ روز بر اساس ناهار ساخته می‌شود و شام «سلف‌بسته» است.
    $mealFilter = in_array($mealType, ['lunch', 'dinner'], true) ? $mealType : null;
    // فقط آیتم‌ها و غذاهای فعال؛ غذای غیرفعال‌شده فیش نمی‌شود.
    $sql = "SELECT o.employee_id, u.national_code, c.food_name, o.meal_type
        FROM food_orders o
        JOIN users u ON u.id = o.employee_id
        JOIN food_calendar_items i ON i.id = o.calendar_item_id AND i.active = 1
        JOIN food_catalog c ON c.id = i.food_id AND c.active = 1
        WHERE o.food_date = ? AND o.status = 'active'";
    $params = [$date];
    if ($mealFilter !== null) {
        $sql .= " AND o.meal_type = ?";
        $params[] = $mealFilter;
    }
    $sql .= " ORDER BY o.id";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $map = [];
    $nationalOwners = [];
    $conflicts = [];
    $byMealPreference = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $national = food_order_digits($row['national_code'] ?? '');
        $food = trim((string) ($row['food_name'] ?? ''));
        $employeeId = (int) ($row['employee_id'] ?? 0);
        if ($national === '' || $food === '') {
            continue;
        }
        // اگر هر دو وعده سفارش دارند، ناهار اولویت دارد (فیشِ روز = ناهار).
        $rowMeal = strtolower(trim((string) ($row['meal_type'] ?? '')));
        if ($mealFilter === null && $rowMeal === 'dinner') {
            $key = $national;
            if (isset($byMealPreference[$key])) {
                continue; // ناهارِ همین شخص از قبل ثبت شده
            }
            $byMealPreference[$key] = $food;
            continue;
        }
        // کد ملی تکراری فقط همان فرد(ها) را از فیش خارج می‌کند؛ بقیهٔ کارکنان عادی چاپ می‌شوند.
        if (isset($nationalOwners[$national]) && $nationalOwners[$national] !== $employeeId) {
            $conflicts[$national] = true;
            continue;
        }
        $nationalOwners[$national] = $employeeId;
        $map[$national] = $food;
        $map[ltrim($national, '0') ?: '0'] = $food;
    }
    foreach (array_keys($conflicts) as $bad) {
        // کلیدهای عددی آرایه (مثلاً 1234567890) در PHP به int تبدیل می‌شوند؛ ltrim فقط رشته می‌پذیرد.
        $bad = (string) $bad;
        unset($map[$bad], $map[ltrim($bad, '0') ?: '0']);
    }
    // شام فقط برای کسی که ناهار ندارد.
    foreach ($byMealPreference as $national => $food) {
        $national = (string) $national;
        if (isset($conflicts[$national]) || isset($map[$national])) {
            continue;
        }
        $map[$national] = $food;
        $map[ltrim($national, '0') ?: '0'] = $food;
    }
    $GLOBALS['__food_order_conflicts'][$date] = array_keys($conflicts);
    return $map;
}

/** Current active lunch choice for the actual employee, not a batch-wide cached map. */
function food_order_internal_food_for_employee(int $employeeId, string $isoDate): ?string
{
    $date = food_order_iso_date($isoDate);
    if ($employeeId <= 0 || $date === null) {
        throw new RuntimeException('شناسه یا تاریخ سفارش برای چاپ معتبر نیست.');
    }
    $stmt = db()->prepare("SELECT c.food_name, o.meal_type FROM food_orders o
        JOIN food_calendar_items i ON i.id = o.calendar_item_id AND i.active = 1
        JOIN food_calendar cal ON cal.id = i.calendar_id AND cal.food_date = o.food_date AND cal.meal_type = o.meal_type
        JOIN food_catalog c ON c.id = i.food_id AND c.active = 1
        WHERE o.employee_id = ? AND o.food_date = ? AND o.status = 'active'");
    $stmt->execute([$employeeId, $date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // یک نفر می‌تواند هم ناهار و هم شام سفارش داده باشد؛ این «چند سفارش»
    // به معنی خرابی نیست. فیشِ روز بر پایهٔ ناهار صادر می‌شود؛ شام فقط
    // وقتی ناهار نیست. پس اول ناهار را جدا می‌کنیم.
    $lunchRows = array_values(array_filter($rows, static fn ($r) => strtolower(trim((string) ($r['meal_type'] ?? ''))) === 'lunch'));
    if (count($lunchRows) > 1) throw new RuntimeException('چند سفارش ناهار فعال برای این پرسنل وجود دارد؛ چاپ متوقف شد.');
    if ($lunchRows !== []) $rows = $lunchRows;
    if (count($rows) > 1) throw new RuntimeException('چند سفارش فعال برای این پرسنل وجود دارد؛ چاپ متوقف شد.');
    $food = trim((string) ($rows[0]['food_name'] ?? ''));
    return $food !== '' ? $food : null;
}

/** خروجی با قرارداد صفحهٔ سفارش‌های پنل چاپ فیش (nat/pc/first/last/food/foodDate/...). */
function food_order_internal_items(string $fromDate, ?string $toDate = null, ?array $orgUnitIds = null): array
{
    food_order_schema_ensure();
    $from = food_order_iso_date($fromDate);
    $to = food_order_iso_date($toDate ?? $fromDate);
    if ($from === null || $to === null || new DateTimeImmutable($from, new DateTimeZone('UTC')) > new DateTimeImmutable($to, new DateTimeZone('UTC'))) {
        throw new RuntimeException('بازهٔ تاریخ سفارش داخلی معتبر نیست.');
    }
    $params = [$from, $to];
    $scopeJoin = '';
    $scopeWhere = '';
    $scopeSelect = '';
    if ($orgUnitIds !== null) {
        $orgUnitIds = array_values(array_unique(array_filter(array_map('intval', $orgUnitIds), static fn (int $id): bool => $id > 0)));
        if ($orgUnitIds === []) return [];
        $scopeJoin = ' JOIN org_units scope_unit ON scope_unit.id = u.org_unit_id AND scope_unit.is_active = 1';
        $scopeWhere = ' AND u.is_active = 1 AND scope_unit.id IN (' . implode(',', array_fill(0, count($orgUnitIds), '?')) . ')';
        $scopeSelect = ', u.org_unit_id';
        array_push($params, ...$orgUnitIds);
    }
    $stmt = db()->prepare("SELECT o.employee_id, o.food_date, o.created_at, i.food_id, u.employee_number, u.national_code,
            u.first_name, u.last_name, u.full_name, c.food_name" . $scopeSelect . "
        FROM food_orders o
        JOIN users u ON u.id = o.employee_id
        JOIN food_calendar_items i ON i.id = o.calendar_item_id
        JOIN food_catalog c ON c.id = i.food_id" . $scopeJoin . "
        WHERE o.food_date BETWEEN ? AND ? AND o.meal_type = 'lunch' AND o.status = 'active'" . $scopeWhere . "
        ORDER BY o.food_date, u.full_name, o.id");
    $stmt->execute($params);
    $items = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $first = trim((string) ($row['first_name'] ?? ''));
        $last = trim((string) ($row['last_name'] ?? ''));
        if ($first === '' && $last === '') {
            $parts = preg_split('/\s+/u', trim((string) ($row['full_name'] ?? '')), 2) ?: [];
            $first = (string) ($parts[0] ?? '');
            $last = (string) ($parts[1] ?? '');
        }
        $created = (string) ($row['created_at'] ?? '');
        $isoDate = (string) $row['food_date'];
        $item = [
            'nat' => food_order_digits($row['national_code'] ?? ''),
            'pc' => (string) ($row['employee_number'] ?? ''),
            'first' => $first,
            'last' => $last,
            'food' => (string) ($row['food_name'] ?? ''),
            'foodId' => (int) ($row['food_id'] ?? 0),
            'foodDate' => $isoDate,
            'rawDate' => $isoDate,
            'reserveDate' => substr($created, 0, 10),
            'reserveTime' => substr($created, 11, 8),
        ];
        if ($orgUnitIds !== null) {
            $item['org_unit_id'] = (int) ($row['org_unit_id'] ?? 0);
            $item['user_id'] = (int) ($row['employee_id'] ?? 0);
        }
        $items[] = $item;
    }
    return $items;
}
