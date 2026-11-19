function food_ticket_api_absent(string $fromDate, ?string $toDate = null): array
{
    $fromDate = food_ticket_parse_date($fromDate) ?: trim($fromDate);
    $toDate = food_ticket_parse_date($toDate ?? $fromDate) ?: trim((string) ($toDate ?? $fromDate));
    if ($fromDate === '' || $toDate === '' || $fromDate > $toDate) {
        return [];
    }
    if (!function_exists('food_order_internal_items') || !function_exists('food_order_mode') || food_order_mode() !== 'INTERNAL-DB') {
        throw new RuntimeException('گزارش غایبین در دسترس نیست.');
    }

    // سفارش‌های بازه فقط از منبع داخلی خوانده می‌شوند؛ خطای MySQL نباید به «بدون سفارش» تبدیل شود.
    $orders = [];
    try {
        $items = food_ticket_api_orders($fromDate, '', $toDate);
        foreach ($items as $it) {
            $nat = food_ticket_digits((string) ($it['nat'] ?? ''));
            $pc = trim((string) ($it['pc'] ?? ''));
            if ($nat === '' && $pc === '') {
                continue; // بدون کد ملی و پرسنلی هیچ تطبیق امنی ممکن نیست.
            }
            $day = (string) ($it['foodDate'] ?? $fromDate);
            $identityKey = $nat !== '' ? 'nat:' . $nat : 'pc:' . $pc;
            $orders[$day][$identityKey] = [
                'nat' => $nat,
                'pc' => $pc,
                'first' => (string) ($it['first'] ?? ''),
                'last' => (string) ($it['last'] ?? ''),
                'food' => (string) ($it['food'] ?? ''),
                'foodDate' => $day,
            ];
        }
    } catch (Throwable $e) {
        app_error_log('[absent] internal orders query failed: ' . $e->getMessage());
        throw $e;
    }

    // کسانی که فیش چاپ‌شده دارند از لیست غایب خارج می‌شوند
    $printed = [];
    try {
        $q = db()->prepare(
            "SELECT punch_date, national_code, personnel_code FROM food_ticket_events
             WHERE punch_date BETWEEN ? AND ?
               AND print_status = 'printed'
               AND COALESCE(meal_type, 'lunch') = 'lunch'
               AND event_type IN ('printed','print','auto_printed')"
        );
        $q->execute([$fromDate, $toDate]);
        foreach ($q->fetchAll() as $row) {
            $day = (string) ($row['punch_date'] ?? '');
            $nat = food_ticket_digits((string) ($row['national_code'] ?? ''));
            if ($nat !== '') {
                $printed[$day]['nat:' . $nat] = true;
            }
            $pc = trim((string) ($row['personnel_code'] ?? ''));
            if ($pc !== '') {
                $printed[$day]['pc:' . $pc] = true;
            }
        }
    } catch (Throwable $e) {
        app_error_log('[absent] events: ' . $e->getMessage());
        throw new RuntimeException('وضعیت چاپ برای محاسبهٔ فیش‌های دریافت‌نشده در دسترس نیست.', 0, $e);
    }

    // «غایب اعلام‌شده»: کسانی که برای همان تاریخ در فرم گروه، غایب ثبت شده‌اند
    // (آمار بازرسی گروه‌های بیرون از ساختمان) — این‌ها با برچسب جداگانه نمایش داده می‌شوند.
    $declaredByDay = [];
    if (function_exists('food_ticket_absence_ensure') && food_ticket_absence_ensure()) {
        try {
            $aq = db()->prepare('SELECT absence_date, user_id FROM food_ticket_daily_absence WHERE absence_date BETWEEN ? AND ?');
            $aq->execute([$fromDate, $toDate]);
            foreach ($aq->fetchAll() as $arow) {
                $declaredByDay[(string) $arow['absence_date']][(int) $arow['user_id']] = true;
            }
        } catch (Throwable $e) {
            app_error_log('[absent] declared absence: ' . $e->getMessage());
        }
    }
    $userIdsByNational = [];
    $userIdsByPersonnel = [];
    $absent = [];
    foreach ($orders as $day => $dayOrders) {
        foreach ($dayOrders as $row) {
            $nat = food_ticket_digits((string) ($row['nat'] ?? ''));
            $pc = trim((string) ($row['pc'] ?? ''));
            if (($nat !== '' && !empty($printed[$day]['nat:' . $nat]))
                || ($pc !== '' && !empty($printed[$day]['pc:' . $pc]))) {
                continue;
            }
            $row['full_name'] = trim(($row['first'] ?? '') . ' ' . ($row['last'] ?? ''));
            $declared = false;
            try {
                $uid = 0;
                if ($nat !== '') {
                    if (!isset($userIdsByNational[$nat])) {
                        $uq = db()->prepare('SELECT id FROM users WHERE national_code = ? OR national_code = ? LIMIT 1');
                        $uq->execute([$nat, ltrim($nat, '0') ?: '0']);
                        $userIdsByNational[$nat] = (int) ($uq->fetchColumn() ?: 0);
                    }
                    $uid = (int) $userIdsByNational[$nat];
                } elseif ($pc !== '') {
                    if (!isset($userIdsByPersonnel[$pc])) {
                        $uq = db()->prepare('SELECT id FROM users WHERE employee_number = ? LIMIT 1');
                        $uq->execute([$pc]);
                        $userIdsByPersonnel[$pc] = (int) ($uq->fetchColumn() ?: 0);
                    }
                    $uid = (int) $userIdsByPersonnel[$pc];
                }
                $declared = $uid > 0 && !empty($declaredByDay[(string) $day][$uid]);
            } catch (Throwable $e) {
                app_error_log('[absent] user lookup: ' . $e->getMessage());
            }
            $row['declared_absent'] = $declared;
            $row['status'] = $declared
                ? 'غایب اعلام‌شده — فیش چاپ نشده'
                : 'غایب — سفارش دارد، فیش چاپ نشده';
            $absent[] = $row;
        }
    }
    return $absent;
}

/**
 * پایش رویدادها.
 * پیش‌فرض (بدون بازه) = فقط «امروزِ کاری سامانه» (به وقت تهران)؛ اگر امروز رویدادی نباشد،
 * خروجی خالی است — نه ۲۰۰ رکورد آخرِ همهٔ تاریخ‌ها. بازهٔ گذشته را باید گزارش‌ها بخواهند
 * (from/to). داده‌های قبلی حذف نمی‌شوند.
 */
