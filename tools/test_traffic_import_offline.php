<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$oldConfig = getenv('ITSM_CONFIG_PATH');
$tempConfig = tempnam(sys_get_temp_dir(), 'ticket-traffic-import-');
if ($tempConfig === false) {
    throw new RuntimeException('Could not create temporary app config.');
}
file_put_contents($tempConfig, "<?php return ['app' => ['timezone' => 'UTC']];\n");
putenv('ITSM_CONFIG_PATH=' . $tempConfig);

try {
    require_once $root . '/bootstrap.php';
    require_once $root . '/xlsx-writer.php';
    require_once $root . '/traffic-import.php';
    require_once $root . '/chatbot.php';

    $assert = static function (bool $condition, string $message): void {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    };

    $jalali = traffic_import_parse_date_value('۱۴۰۳/۰۷/۲۴');
    $expectedJalali = jalali_input_to_gregorian('1403/07/24');
    $assert(is_array($jalali) && $jalali['date'] === substr((string) $expectedJalali, 0, 10), 'Persian-digit Jalali date parsing');

    $dateTime = traffic_import_parse_date_value('2024-10-15 14:35:00');
    $assert($dateTime === ['date' => '2024-10-15', 'time' => '14:35:00'], 'ISO date and embedded time parsing');
    $assert(traffic_import_parse_date_value('10/15/2024')['date'] === '2024-10-15', 'Unambiguous US month/day/year CSV dates');

    $excelDateSerial = (strtotime('2024-10-15 14:35:00 UTC') / 86400) + 25569;
    $excelDate = traffic_import_parse_date_value((string) $excelDateSerial);
    $assert(is_array($excelDate) && $excelDate['date'] === '2024-10-15' && $excelDate['time'] === '14:35:00', 'Excel date serial parsing');
    $assert(traffic_import_parse_time('۰۸:۳۰') === '08:30:00', 'Persian-digit clock parsing');
    $assert(traffic_import_parse_time('9.30') === '09:30:00', 'dot-separated clock parsing');
    $assert(traffic_import_parse_time('0.5') === '12:00:00', 'Excel day-fraction time parsing');
    $assert(traffic_import_parse_bool('✓') === 1 && traffic_import_parse_bool('✗') === 0, 'Checkbox symbols map to explicit yes/no values');

    $sheet = [
        ['سوابق قدیمی'],
        ['شماره برگ', 'تاریخ تردد', 'نام و نام خانوادگی', 'کد ملی', 'تلفن همراه', 'موسسه / شرکت', 'ساعت ورود', 'ساعت خروج', 'ملاقات با', 'تأییدکننده', 'بدون بازدید', 'با خودرو', 'با تلفن همراه', 'توضیحات', 'تردد خاص'],
        ['', '۱۴۰۳/۰۷/۲۴', 'علی رضایی', '—', '۰۹۱۲۰۰۰۰۰۰۰', 'شرکت نمونه', '۰۸:۳۰', '۰۹:۱۵', 'مدیریت اداری', 'مدیر', '۰', 'بله', 'خیر', 'مراجعهٔ اداری', '۰'],
        ['', '1403/07/24', 'علی رضایی', '—', '09120000000', 'شرکت نمونه', '08:30', '09:15', 'مدیریت اداری', 'مدیر', '0', '1', '0', 'مراجعهٔ اداری', '0'],
    ];
    $parsed = traffic_import_parse_sheet($sheet);
    $assert(!empty($parsed['ok']) && count($parsed['rows']) === 2, 'Header detection and row parsing');
    $first = $parsed['rows'][0];
    $assert($first['visit_date'] === substr((string) $expectedJalali, 0, 10), 'Parsed visit date preserved');
    $assert($first['entry_time'] === '08:30:00' && $first['exit_time'] === '09:15:00', 'Entry/exit history preserved');
    $assert($first['national_code'] === '' && $first['errors'] === [], 'Optional dash values remain empty');
    $assert($first['with_car'] === 1 && $first['with_mobile'] === 0, 'Persian/English boolean flags');

    $csvPath = tempnam(sys_get_temp_dir(), 'ticket-traffic-import-csv-');
    if ($csvPath === false) {
        throw new RuntimeException('Could not create temporary CSV.');
    }
    try {
        $csvHandle = fopen($csvPath, 'wb');
        fputcsv($csvHandle, traffic_import_template_headers(), ',', '"', '');
        fputcsv($csvHandle, ['', '1403/07/24', 'علی رضایی', '', '09120000000', 'شرکت نمونه', '08:30', '09:15', 'مدیریت اداری', 'مدیر', '0', '1', '0', 'مراجعهٔ اداری', '0'], ',', '"', '');
        fclose($csvHandle);
        $csvRead = food_ticket_import_read_csv($csvPath);
        $csvParsed = traffic_import_parse_sheet($csvRead['rows'] ?? []);
        $assert(traffic_import_csv_shape_error($csvPath) === '', 'CSV preflight accepts normal quoted/UTF-8 records');
        $assert(empty($csvRead['error']) && !empty($csvParsed['ok']) && $csvParsed['rows'][0]['entry_time'] === '08:30:00', 'CSV template columns and parser round trip');
    } finally {
        @unlink($csvPath);
    }
    $semicolonCsvPath = tempnam(sys_get_temp_dir(), 'ticket-traffic-import-semicolon-csv-');
    if ($semicolonCsvPath === false) {
        throw new RuntimeException('Could not create temporary semicolon CSV.');
    }
    try {
        $semicolonHandle = fopen($semicolonCsvPath, 'wb');
        fputcsv($semicolonHandle, traffic_import_template_headers(), ';', '"', '');
        fputcsv($semicolonHandle, ['', '1403/07/24', 'علی رضایی', '', '09120000000', 'شرکت، واحد یک, بخش الف', '08:30', '09:15', 'مدیریت اداری', 'مدیر', '0', '1', '0', 'مراجعهٔ اداری', '0'], ';', '"', '');
        fclose($semicolonHandle);
        $semicolonRead = food_ticket_import_read_csv($semicolonCsvPath);
        $semicolonParsed = traffic_import_parse_sheet($semicolonRead['rows'] ?? []);
        $assert(traffic_import_csv_shape_error($semicolonCsvPath) === '' && !empty($semicolonParsed['ok']) && $semicolonParsed['rows'][0]['company'] === 'شرکت، واحد یک, بخش الف', 'Semicolon CSV detection ignores commas inside quoted fields');
    } finally {
        @unlink($semicolonCsvPath);
    }
    $wideCsvPath = tempnam(sys_get_temp_dir(), 'ticket-traffic-import-wide-csv-');
    if ($wideCsvPath === false) {
        throw new RuntimeException('Could not create temporary wide CSV.');
    }
    try {
        file_put_contents($wideCsvPath, implode(',', array_fill(0, TRAFFIC_IMPORT_MAX_COLUMNS + 1, 'x')) . "\n");
        $assert(str_contains(traffic_import_csv_shape_error($wideCsvPath), '۶۴ ستون'), 'CSV files wider than the column cap are rejected before parsing');
        file_put_contents($wideCsvPath, str_repeat("x\n", TRAFFIC_IMPORT_MAX_ROWS + 21));
        $assert(str_contains(traffic_import_csv_shape_error($wideCsvPath), '۱۰٬۰۰۰ ردیف'), 'CSV files with excessive records are rejected before parsing');
    } finally {
        @unlink($wideCsvPath);
    }
    $xlsxPath = tempnam(sys_get_temp_dir(), 'ticket-traffic-import-xlsx-');
    if ($xlsxPath === false) {
        throw new RuntimeException('Could not create temporary XLSX.');
    }
    try {
        $xlsxBytes = xlsx_build([[
            'name' => 'Traffic history',
            'rows' => [
                traffic_import_template_headers(),
                ['', '1403/07/24', 'علی رضایی', '', '09120000000', 'شرکت نمونه', '08:30', '09:15', 'مدیریت اداری', 'مدیر', '0', '1', '0', 'مراجعهٔ اداری', '0'],
            ],
        ]]);
        file_put_contents($xlsxPath, $xlsxBytes);
        $archiveError = traffic_import_xlsx_archive_error($xlsxPath);
        $xlsxRead = food_ticket_import_read_xlsx($xlsxPath, TRAFFIC_IMPORT_MAX_COLUMNS, TRAFFIC_IMPORT_MAX_ROWS + 20);
        $xlsxParsed = traffic_import_parse_sheet($xlsxRead['rows'] ?? []);
        $assert($archiveError === '' && empty($xlsxRead['error']) && !empty($xlsxParsed['ok']) && $xlsxParsed['rows'][0]['entry_time'] === '08:30:00', 'XLSX archive validation, first worksheet read and parser round trip');

        $wideBytes = xlsx_build([['name' => 'Wide', 'rows' => [array_fill(0, TRAFFIC_IMPORT_MAX_COLUMNS + 1, 'x')]]]);
        file_put_contents($xlsxPath, $wideBytes);
        $wideRead = food_ticket_import_read_xlsx($xlsxPath, TRAFFIC_IMPORT_MAX_COLUMNS, TRAFFIC_IMPORT_MAX_ROWS + 20);
        $assert(($wideRead['error'] ?? '') !== '', 'XLSX columns beyond the importer limit are rejected before row arrays expand');
    } finally {
        @unlink($xlsxPath);
    }
    $assert(str_starts_with(traffic_import_template_csv_bytes(), "\xEF\xBB\xBF"), 'CSV template includes a UTF-8 BOM for Excel');

    $plan = traffic_import_build_plan($parsed['rows']);
    $assert($plan['counts']['insert'] === 1 && $plan['counts']['duplicate'] === 1, 'Duplicate rows in the imported file are classified once');
    $jalaliParts = gregorian_to_jalali(...array_map('intval', explode('-', $first['visit_date'])));
    $expectedSerial = sprintf('%04d%02d%02d-0001', $jalaliParts[0], $jalaliParts[1], $jalaliParts[2]);
    $assert($plan['rows'][0]['serial_no'] === $expectedSerial, 'Blank serial number is generated for its historical date');
    $reservedAcrossDates = traffic_import_build_plan([$first], [], [$expectedSerial]);
    $assert($reservedAcrossDates['rows'][0]['serial_no'] !== $expectedSerial, 'Historical serials with a generated-date prefix are reserved even across mismatched visit dates');

    $explicitCollision = traffic_import_build_plan([
        ['visit_date' => $first['visit_date'], 'full_name' => 'نفر بدون شماره', 'national_code' => '', 'phone' => '', 'company' => '', 'entry_time' => '08:00:00', 'serial_no' => '', 'errors' => [], 'warnings' => []],
        ['visit_date' => $first['visit_date'], 'full_name' => 'نفر با شماره', 'national_code' => '', 'phone' => '', 'company' => '', 'entry_time' => '08:10:00', 'serial_no' => $expectedSerial, 'errors' => [], 'warnings' => []],
    ]);
    $assert($explicitCollision['counts']['insert'] === 2, 'Explicit serials later in a file are reserved before auto-generation');
    $assert($explicitCollision['rows'][0]['serial_no'] !== $expectedSerial && $explicitCollision['rows'][1]['serial_no'] === $expectedSerial, 'Generated serial does not steal a later explicit serial');

    $sameExplicitSerial = traffic_import_build_plan([
        ['visit_date' => $first['visit_date'], 'full_name' => 'نفر یک', 'national_code' => '', 'phone' => '', 'company' => '', 'entry_time' => '08:00:00', 'serial_no' => 'CUSTOM-1', 'errors' => [], 'warnings' => []],
        ['visit_date' => $first['visit_date'], 'full_name' => 'نفر دو', 'national_code' => '', 'phone' => '', 'company' => '', 'entry_time' => '08:10:00', 'serial_no' => 'custom-1', 'errors' => [], 'warnings' => []],
    ]);
    $assert($sameExplicitSerial['counts']['invalid'] === 2, 'Repeated explicit serials in one file are rejected safely');

    $existing = [
        'serial_no' => 'EXISTING-1',
        'visit_date' => $first['visit_date'],
        'full_name' => 'علی رضایی',
        'national_code' => '',
        'phone' => '09120000000',
        'company' => 'شرکت نمونه',
        'entry_time' => '08:30:00',
    ];
    $existingPlan = traffic_import_build_plan([$first], [$existing]);
    $assert($existingPlan['counts']['exists'] === 1, 'Records already in the database are recognized as existing');

    $maskedPlan = traffic_import_build_plan([array_replace($first, ['national_code' => '0012345678'])]);
    $preview = traffic_import_preview_session_data($maskedPlan, str_repeat('a', 64), 'history.csv', 42);
    $assert($preview['rows'][0]['national_code'] === '••••••5678', 'Preview session retains only the last four national-code digits');
    $assert(strlen($preview['token']) === 32 && $preview['actor_id'] === 42, 'Preview is tokenized and scoped to its owner');

    $separateNameHeaders = traffic_import_detect_header([
        ['نام', 'نام خانوادگی', 'تاریخ', 'زمان ورود'],
    ]);
    $assert(isset($separateNameHeaders['columns']['first_name'], $separateNameHeaders['columns']['last_name'], $separateNameHeaders['columns']['visit_date'], $separateNameHeaders['columns']['entry_time']), 'Separate first/last name and entry-time aliases do not collide');
    $guide = chatbot_guide_search('آپلود سوابق ترددهای گذشته اکسل');
    $assert(is_array($guide) && str_contains($guide['title'], 'بارگذاری سوابق قدیمی تردد'), 'Chatbot finds the new hidden traffic-import guide section');

    echo "PASS traffic import offline: dates/times, CSV/XLSX, aliases, limits, duplicate handling, generated serials, masking, preview token and chatbot\n";
} finally {
    @unlink($tempConfig);
    if ($oldConfig === false) {
        putenv('ITSM_CONFIG_PATH');
    } else {
        putenv('ITSM_CONFIG_PATH=' . $oldConfig);
    }
}
