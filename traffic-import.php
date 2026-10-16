<?php
declare(strict_types=1);

/**
 * ورود کنترل‌شدهٔ سوابق تردد از Excel/CSV؛ مسیر این فایل API عمومی ندارد.
 * فرم فقط از مسیر مخفی index.php?page=traffic-control&tab=import باز می‌شود.
 */
require_once __DIR__ . '/food-ticket-import.php';

const TRAFFIC_IMPORT_MAX_BYTES = 10485760; // 10 MiB
const TRAFFIC_IMPORT_MAX_ROWS = 10000;
const TRAFFIC_IMPORT_MAX_COLUMNS = 64;
const TRAFFIC_IMPORT_PREVIEW_ROWS = 300;
const TRAFFIC_IMPORT_PREVIEW_TTL = 1800;

/** نام‌های پذیرفته‌شده برای سرستون‌های فایل‌های قدیمی (بعد از نرمال‌سازی). */
function traffic_import_column_aliases(): array
{
    return [
        'serial_no' => ['شماره برگ', 'شماره ثبت', 'شماره تردد', 'شماره مراجعه', 'serial', 'serial no', 'serial_no', 'ticket number'],
        'visit_date' => ['تاریخ تردد', 'تاریخ مراجعه', 'تاریخ ورود', 'تاریخ ثبت', 'تاریخ', 'visit date', 'visit_date', 'entry date', 'entry_date', 'date'],
        'visit_datetime' => ['تاریخ و ساعت ورود', 'تاریخ ساعت ورود', 'تاریخ و زمان ورود', 'تاریخ و زمان تردد', 'تاریخ و ساعت تردد', 'تاریخ و ساعت', 'تاریخ ساعت', 'تاریخ/ساعت ورود', 'entry datetime', 'entry_datetime', 'visit datetime', 'visit_datetime', 'datetime'],
        'full_name' => ['نام و نام خانوادگی', 'نام و فامیل', 'نام مراجعه کننده', 'نام مراجعه‌کننده', 'نام میهمان', 'نام مهمان', 'نام مراجع', 'نام کامل', 'نام شخص', 'full name', 'full_name', 'visitor name', 'visitor_name', 'guest name'],
        'first_name' => ['نام', 'نام کوچک', 'نام مراجعه', 'first name', 'firstname', 'first_name', 'given name', 'name'],
        'last_name' => ['نام خانوادگی', 'نام‌خانوادگی', 'فامیلی', 'شهرت', 'last name', 'lastname', 'last_name', 'surname', 'family name'],
        'national_code' => ['کد ملی', 'کدملی', 'شماره ملی', 'شماره ملی مراجعه کننده', 'کد شناسایی', 'national code', 'national_code', 'national id', 'national_id', 'meli', 'cod_meli'],
        'phone' => ['تلفن همراه', 'شماره همراه', 'شماره تماس', 'موبایل', 'تلفن', 'phone', 'mobile', 'mobile number', 'cell phone'],
        'company' => ['موسسه شرکت', 'موسسه', 'مؤسسه', 'شرکت', 'سازمان', 'company', 'organization', 'organisation', 'institute'],
        'entry_time' => ['ساعت ورود', 'زمان ورود', 'ساعت مراجعه', 'ورود', 'entry time', 'entry_time', 'time in', 'time_in', 'check in', 'checkin'],
        'exit_time' => ['ساعت خروج', 'زمان خروج', 'خروج', 'exit time', 'exit_time', 'time out', 'time_out', 'check out', 'checkout'],
        'meeting_with' => ['ملاقات با', 'میزبان', 'نام میزبان', 'مقصد ملاقات', 'مقصد', 'meeting with', 'meeting_with', 'host', 'destination'],
        'approved_by' => ['تایید کننده', 'تأیید کننده', 'تاییدکننده', 'تأییدکننده', 'نام تایید کننده', 'نام تأیید کننده', 'approved by', 'approved_by', 'approver'],
        'description' => ['توضیحات', 'شرح', 'هدف از تردد', 'علت مراجعه', 'موضوع مراجعه', 'description', 'purpose', 'reason', 'notes', 'note'],
        'no_visit' => ['بدون بازدید', 'بدون ملاقات', 'no visit', 'no_visit', 'not visited'],
        'with_car' => ['با خودرو', 'خودرو', 'ماشین', 'وسیله نقلیه', 'with car', 'with_car', 'car'],
        'with_mobile' => ['با تلفن همراه', 'با موبایل', 'تلفن همراه', 'موبایل همراه', 'with mobile', 'with_mobile', 'mobile carried'],
        'is_notice' => ['تردد خاص', 'تردد ویژه', 'اعلام تردد', 'تردد اطلاع رسانی', 'is notice', 'is_notice', 'special visit'],
    ];
}

/** پیدا کردن ردیف سرستون در ۲۰ ردیف اول. نام و تاریخ/تاریخ‌وساعت باید تشخیص داده شوند. */
function traffic_import_detect_header(array $sheet): array
{
    $aliases = [];
    foreach (traffic_import_column_aliases() as $field => $names) {
        foreach ($names as $name) {
            $key = food_ticket_import_key((string) $name);
            if ($key !== '' && !isset($aliases[$key])) {
                $aliases[$key] = $field;
            }
        }
    }

    $best = null;
    $bestScore = -1;
    foreach ($sheet as $index => $cells) {
        if ((int) $index >= 20 || !is_array($cells)) {
            continue;
        }
        $found = [];
        foreach ($cells as $column => $cell) {
            $key = food_ticket_import_key((string) $cell);
            if ($key !== '' && isset($aliases[$key]) && !isset($found[$aliases[$key]])) {
                $found[$aliases[$key]] = (int) $column;
            }
        }
        $hasName = isset($found['full_name']) || isset($found['first_name']) || isset($found['last_name']);
        $hasDate = isset($found['visit_date']) || isset($found['visit_datetime']);
        if (!$hasName || !$hasDate) {
            continue;
        }
        $score = count($found);
        if ($score > $bestScore) {
            $best = ['row' => (int) $index, 'columns' => $found];
            $bestScore = $score;
        }
    }

    return $best ?? ['row' => null, 'columns' => []];
}

/** تبدیل جزءهای تاریخ میلادی یا جلالی به Y-m-d میلادی. */
function traffic_import_date_from_parts(int $year, int $month, int $day): ?string
{
    if ($year >= 1700 && $year <= 2200) {
        if (!checkdate($month, $day, $year)) {
            return null;
        }
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    if ($year < 1000 || $year > 1699 || !function_exists('jalali_input_to_gregorian')) {
        return null;
    }
    $converted = jalali_input_to_gregorian(sprintf('%04d/%02d/%02d', $year, $month, $day));
    return is_string($converted) && preg_match('/^\d{4}-\d{2}-\d{2} /', $converted) === 1
        ? substr($converted, 0, 10)
        : null;
}

/** Excel serial fraction or seconds → normalized HH:MM:SS. */
function traffic_import_seconds_to_time(int $seconds): string
{
    $seconds = max(0, min(86399, $seconds));
    return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
}

/**
 * تاریخ شمسی، ISO میلادی، تاریخ متن‌دار یا serial اکسل را می‌خواند.
 * اگر سلول تاریخ اکسل ساعت هم داشته باشد، time را نیز برمی‌گرداند.
 * @return array{date:string,time:?string}|null
 */
function traffic_import_parse_date_value(string $raw): ?array
{
    $value = trim(food_ticket_import_normalize(str_replace(['٫', '٬'], ['.', ','], $raw)));
    if ($value === '') {
        return null;
    }
    $numeric = str_replace(',', '', $value);

    // تاریخ فشردهٔ YYYYMMDD، پیش از بررسی سریال اکسل.
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $numeric, $match) === 1) {
        $date = traffic_import_date_from_parts((int) $match[1], (int) $match[2], (int) $match[3]);
        return $date !== null ? ['date' => $date, 'time' => null] : null;
    }

    // Excel 1900 date system. بخش کسری زمان را هم نگه می‌داریم.
    if (is_numeric($numeric)) {
        $serial = (float) $numeric;
        if ($serial >= 20000 && $serial <= 80000) {
            $timestamp = (int) round(($serial - 25569) * 86400);
            $date = gmdate('Y-m-d', $timestamp);
            $fraction = $serial - floor($serial);
            $time = $fraction > 0.0000001 ? traffic_import_seconds_to_time((int) round($fraction * 86400)) : null;
            return ['date' => $date, 'time' => $time];
        }
    }

    $datePart = $value;
    $time = null;
    if (preg_match('/^(.*?)[\sT]+(?:ساعت\s*)?(\d{1,2}:\d{2}(?::\d{2})?(?:\s*(?:AM|PM))?)$/iu', $value, $match) === 1) {
        $datePart = trim((string) $match[1]);
        $time = traffic_import_parse_time((string) $match[2]);
    }

    // YYYY/MM/DD، YYYY-MM-DD یا YYYY.MM.DD.
    if (preg_match('/^(\d{4})[\/.\-](\d{1,2})[\/.\-](\d{1,2})$/', $datePart, $match) === 1) {
        $date = traffic_import_date_from_parts((int) $match[1], (int) $match[2], (int) $match[3]);
        return $date !== null ? ['date' => $date, 'time' => $time] : null;
    }

    // Excel فارسی معمولاً روز/ماه/سال را متنی صادر می‌کند؛ اینجا روز/ماه/سال فرض می‌شود.
    if (preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})$/', $datePart, $match) === 1) {
        $year = (int) $match[3];
        $first = (int) $match[1];
        $second = (int) $match[2];
        $date = traffic_import_date_from_parts($year, $second, $first); // قالب پیش‌فرض ایرانی: روز/ماه/سال
        if ($date === null && $year >= 1700) {
            $date = traffic_import_date_from_parts($year, $first, $second); // فایل میلادی آمریکایی: ماه/روز/سال
        }
        return $date !== null ? ['date' => $date, 'time' => $time] : null;
    }

    return null;
}

/** ساعت متنی، عدد HHMM یا کسر روز Excel را به HH:MM:SS تبدیل می‌کند. */
function traffic_import_parse_time(string $raw): ?string
{
    $value = trim(food_ticket_import_normalize(str_replace('٫', '.', $raw)));
    if ($value === '') {
        return null;
    }

    if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?$/i', $value, $match) === 1) {
        $hour = (int) $match[1];
        $minute = (int) $match[2];
        $second = isset($match[3]) && $match[3] !== '' ? (int) $match[3] : 0;
        $ampm = strtoupper((string) ($match[4] ?? ''));
        if ($minute > 59 || $second > 59 || ($ampm !== '' && ($hour < 1 || $hour > 12)) || ($ampm === '' && $hour > 23)) {
            return null;
        }
        if ($ampm === 'AM') {
            $hour = $hour === 12 ? 0 : $hour;
        } elseif ($ampm === 'PM') {
            $hour = $hour === 12 ? 12 : $hour + 12;
        }
        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }

    // Excel/بعض خروجی‌ها ساعت را به‌صورت 8.30 یا 13.05 می‌نویسند.
    if (preg_match('/^(\d{1,2})\.(\d{2})$/', $value, $match) === 1) {
        $hour = (int) $match[1];
        $minute = (int) $match[2];
        return $hour <= 23 && $minute <= 59 ? sprintf('%02d:%02d:00', $hour, $minute) : null;
    }

    // ساعت Excel، کسری از یک شبانه‌روز؛ مقدار صفر هم ساعت ۰۰:۰۰ است.
    if (is_numeric($value)) {
        $number = (float) $value;
        if ($number >= 0 && $number < 1) {
            return traffic_import_seconds_to_time((int) round($number * 86400));
        }
    }

    // خروجی‌های متنی 930، 0930، 1330 یا فقط 9.
    if (preg_match('/^\d{3,4}$/', $value) === 1) {
        $digits = str_pad($value, 4, '0', STR_PAD_LEFT);
        $hour = (int) substr($digits, 0, 2);
        $minute = (int) substr($digits, 2, 2);
        return $hour <= 23 && $minute <= 59 ? sprintf('%02d:%02d:00', $hour, $minute) : null;
    }
    if (preg_match('/^\d{1,2}$/', $value) === 1) {
        $hour = (int) $value;
        return $hour <= 23 ? sprintf('%02d:00:00', $hour) : null;
    }

    return null;
}

/** مقادیر رایج yes/no فارسی و انگلیسی؛ null یعنی مقدار باید در پیش‌نمایش بررسی شود. */
function traffic_import_parse_bool(string $raw): ?int
{
    $displayValue = trim(food_ticket_import_normalize($raw));
    if (in_array($displayValue, ['✓', '✔', '√', '☑'], true)) {
        return 1;
    }
    if (in_array($displayValue, ['×', '✗', '☒', '☐'], true)) {
        return 0;
    }
    $value = food_ticket_import_key($displayValue);
    if ($value === '') {
        return 0;
    }
    $yes = ['1', 'yes', 'y', 'true', 'بله', 'بلی', 'نعم', 'آری', 'دارد', 'فعال', 'باماشین', 'باخودرو', 'باهمراه', 'بدونبازدید', 'بدونملاقات', 'ترددخاص', 'ترددویژه'];
    $no = ['0', 'no', 'n', 'false', 'خیر', 'نه', 'ندارد', 'غیرفعال', 'نداردبازدید', 'عادی', '×', '✗'];
    if (in_array($value, $yes, true)) {
        return 1;
    }
    if (in_array($value, $no, true)) {
        return 0;
    }
    if (str_contains($value, 'بله') || str_contains($value, 'دارد') || str_contains($value, 'باخودرو') || str_contains($value, 'باهمراه')) {
        return 1;
    }
    if (str_contains($value, 'خیر') || str_contains($value, 'ندارد')) {
        return 0;
    }
    return null;
}

function traffic_import_text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/** علامت‌های متداول اکسل برای مقدار اختیاری خالی، مانند خط تیره و N/A. */
function traffic_import_optional_marker(string $value): string
{
    $value = trim($value);
    $key = food_ticket_import_key($value);
    return in_array($key, ['', 'na', 'null', 'none', 'نامشخص', 'ثبتنشده', 'ندارد'], true) || in_array($value, ['-', '–', '—'], true) ? '' : $value;
}

/** نگاشت فایل شیت به ردیف‌های نرمال‌شده؛ دیتابیس در این مرحله تغییر نمی‌کند. */
function traffic_import_parse_sheet(array $sheet): array
{
    if (count($sheet) > TRAFFIC_IMPORT_MAX_ROWS + 20) {
        return ['ok' => false, 'error' => 'فایل بیش از ۱۰٬۰۰۰ ردیف دارد؛ آن را به چند فایل کوچک‌تر تقسیم کنید.', 'rows' => []];
    }
    foreach ($sheet as $cells) {
        if (is_array($cells) && ($cells !== [] && max(array_map('intval', array_keys($cells))) >= TRAFFIC_IMPORT_MAX_COLUMNS)) {
            return ['ok' => false, 'error' => 'فایل بیش از ۶۴ ستون دارد؛ فقط ستون‌های لازم و پشتیبانی‌شده را نگه دارید.', 'rows' => []];
        }
    }
    $header = traffic_import_detect_header($sheet);
    if ($header['row'] === null) {
        return ['ok' => false, 'error' => 'سرستون‌های لازم پیدا نشد. فایل باید ستون «نام و نام خانوادگی» (یا «نام» و «نام خانوادگی») و «تاریخ تردد» داشته باشد؛ قالب نمونه را از همین صفحه دریافت کنید.', 'rows' => []];
    }

    $rows = [];
    foreach ($sheet as $index => $cells) {
        if ((int) $index <= (int) $header['row'] || !is_array($cells)) {
            continue;
        }
        $value = static function (string $field) use ($header, $cells): string {
            if (!isset($header['columns'][$field])) {
                return '';
            }
            return trim((string) ($cells[$header['columns'][$field]] ?? ''));
        };
        $hasContent = count(array_filter($cells, static fn (mixed $cell): bool => trim((string) $cell) !== '')) > 0;
        if (!$hasContent) {
            continue;
        }

        $name = $value('full_name');
        if ($name === '') {
            $name = trim($value('first_name') . ' ' . $value('last_name'));
        }
        $rawDate = $value('visit_date') !== '' ? $value('visit_date') : $value('visit_datetime');
        $parsedDate = traffic_import_parse_date_value($rawDate);
        $rawEntry = $value('entry_time');
        $entryTime = $rawEntry !== '' ? traffic_import_parse_time($rawEntry) : ($parsedDate['time'] ?? null);
        $rawExit = $value('exit_time');
        $exitTime = $rawExit !== '' ? traffic_import_parse_time($rawExit) : null;
        $nationalRaw = traffic_import_optional_marker($value('national_code'));
        $nationalCode = food_ticket_import_digits($nationalRaw);
        $errors = [];
        $warnings = [];

        if ($name === '') {
            $errors[] = 'نام و نام خانوادگی خالی است.';
        } elseif (traffic_import_text_length($name) > 190) {
            $errors[] = 'نام بیش از ۱۹۰ نویسه است.';
        }
        if ($parsedDate === null) {
            $errors[] = 'تاریخ تردد خالی یا نامعتبر است.';
        }
        if ($rawEntry !== '' && $entryTime === null) {
            $errors[] = 'ساعت ورود قابل تشخیص نیست.';
        }
        if ($rawExit !== '' && $exitTime === null) {
            $errors[] = 'ساعت خروج قابل تشخیص نیست.';
        }
        if ($nationalRaw !== '' && $nationalCode === '') {
            $errors[] = 'کد ملی شامل رقم قابل تشخیص نیست.';
        } elseif ($nationalCode !== '' && strlen($nationalCode) > 10) {
            $errors[] = 'کد ملی بیش از ۱۰ رقم است.';
        } elseif ($nationalCode !== '' && strlen($nationalCode) < 10) {
            // Excel ممکن است صفر ابتدای کد ملی را حذف کرده باشد.
            $nationalCode = str_pad($nationalCode, 10, '0', STR_PAD_LEFT);
        }
        if ($nationalCode !== '' && function_exists('valid_iranian_national_code') && !valid_iranian_national_code($nationalCode)) {
            $warnings[] = 'کد ملی از نظر رقم کنترل معتبر نیست؛ پیش از ثبت نهایی بررسی شود.';
        }

        $phoneRaw = traffic_import_optional_marker($value('phone'));
        $phone = food_ticket_import_digits($phoneRaw);
        if ($phoneRaw !== '' && $phone === '') {
            $errors[] = 'شماره تلفن شامل رقم قابل تشخیص نیست.';
        } elseif (strlen($phone) > 20) {
            $errors[] = 'شماره تلفن بیش از ۲۰ رقم است.';
        }
        $serial = traffic_import_optional_marker($value('serial_no'));
        if ($serial !== '' && (traffic_import_text_length($serial) > 30 || preg_match('/[\x00-\x1F\x7F]/', $serial) === 1)) {
            $errors[] = 'شماره برگ بیش از حد مجاز یا دارای نویسهٔ کنترلی است.';
        }

        $flags = [];
        foreach (['no_visit' => 'بدون بازدید', 'with_car' => 'با خودرو', 'with_mobile' => 'با تلفن همراه', 'is_notice' => 'تردد خاص'] as $field => $label) {
            $rawFlag = $value($field);
            $flag = traffic_import_parse_bool($rawFlag);
            if ($flag === null) {
                $warnings[] = 'مقدار ستون «' . $label . '» شناخته نشد و فعلاً خیر در نظر گرفته می‌شود.';
                $flag = 0;
            }
            $flags[$field] = $flag;
        }

        $date = $parsedDate['date'] ?? '';
        if ($date !== '' && $date > date('Y-m-d')) {
            $warnings[] = 'تاریخ تردد در آینده است.';
        }
        if ($entryTime === null && $date !== '') {
            $warnings[] = 'ساعت ورود خالی است.';
        }
        if ($exitTime === null && $date !== '' && $date < date('Y-m-d')) {
            $warnings[] = 'ساعت خروج خالی است؛ این سابقه در سامانه «بدون خروج» دیده می‌شود.';
        }
        if ($entryTime !== null && $exitTime !== null && $exitTime < $entryTime) {
            $warnings[] = 'ساعت خروج پیش از ساعت ورود است؛ احتمال تردد شبانه را بررسی کنید.';
        }

        $company = trim($value('company'));
        $meetingWith = trim($value('meeting_with'));
        $approvedBy = trim($value('approved_by'));
        $description = trim($value('description'));
        foreach (['نام شرکت' => $company, 'میزبان/مقصد' => $meetingWith, 'تأییدکننده' => $approvedBy] as $label => $text) {
            if (traffic_import_text_length($text) > 190) {
                $errors[] = $label . ' بیش از ۱۹۰ نویسه است.';
            }
        }
        if (strlen($description) > 60000) {
            $errors[] = 'توضیحات بیش از حد مجاز است.';
        }

        $rows[] = [
            'line' => (int) $index + 1,
            'source_date' => $rawDate,
            'visit_date' => $date,
            'full_name' => $name,
            'national_code' => $nationalCode,
            'phone' => $phone,
            'company' => $company,
            'entry_time' => $entryTime,
            'exit_time' => $exitTime,
            'serial_no' => $serial,
            'meeting_with' => $meetingWith,
            'approved_by' => $approvedBy,
            'description' => $description,
            'no_visit' => $flags['no_visit'],
            'with_car' => $flags['with_car'],
            'with_mobile' => $flags['with_mobile'],
            'is_notice' => $flags['is_notice'],
            'errors' => $errors,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    if ($rows === []) {
        return ['ok' => false, 'error' => 'بعد از سرستون‌ها ردیف داده‌ای پیدا نشد.', 'rows' => []];
    }
    if (count($rows) > TRAFFIC_IMPORT_MAX_ROWS) {
        return ['ok' => false, 'error' => 'فایل بیش از ۱۰٬۰۰۰ ردیف داده دارد؛ آن را به چند فایل کوچک‌تر تقسیم کنید.', 'rows' => []];
    }
    return ['ok' => true, 'header_row' => (int) $header['row'] + 1, 'rows' => $rows, 'total' => count($rows)];
}

/** محافظت از حافظه در برابر XLSX فشرده‌شدهٔ غیرعادی. */
function traffic_import_xlsx_archive_error(string $path): string
{
    if (!class_exists('ZipArchive') || !function_exists('simplexml_load_string')) {
        return 'خواندن Excel در این سرور ممکن نیست؛ افزونه‌های zip و SimpleXML باید فعال باشند. فایل را با فرمت CSV ذخیره کنید.';
    }
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return 'فایل Excel باز نشد؛ احتمالاً فایل خراب است یا پسوند آن اشتباه است.';
    }
    $totalUncompressed = 0;
    $hasSheet = false;
    if ($zip->numFiles > 2000) {
        $zip->close();
        return 'ساختار فایل Excel غیرعادی است.';
    }
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        if (is_array($stat)) {
            $totalUncompressed += (int) ($stat['size'] ?? 0);
        }
        $name = (string) ($stat['name'] ?? '');
        if (preg_match('~^xl/worksheets/sheet\d+\.xml$~', $name) === 1) {
            $hasSheet = true;
        }
        if ($totalUncompressed > 50 * 1024 * 1024) {
            $zip->close();
            return 'حجم بازشدهٔ فایل Excel بیش از ۵۰ مگابایت است؛ فایل را کوچک‌تر یا به CSV تبدیل کنید.';
        }
    }
    $zip->close();
    return $hasSheet ? '' : 'در فایل Excel هیچ کاربرگی پیدا نشد.';
}

/** جلوگیری از CSVهای بسیار عریض پیش از ساخت آرایهٔ سلول‌ها در حافظه. */
function traffic_import_csv_shape_error(string $path): string
{
    $raw = @file_get_contents($path);
    if (!is_string($raw)) {
        return 'خواندن فایل CSV ممکن نشد.';
    }
    $raw = (string) preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    $sample = substr($raw, 0, 4000);
    $delimiters = food_ticket_import_csv_delimiter_counts($sample);
    arsort($delimiters);
    $delimiter = (string) array_key_first($delimiters);
    if (($delimiters[$delimiter] ?? 0) === 0) {
        $delimiter = ',';
    }

    $columns = 1;
    $records = 0;
    $inQuotes = false;
    $fieldStart = true;
    $length = strlen($raw);
    for ($index = 0; $index < $length; $index++) {
        $char = $raw[$index];
        if ($inQuotes) {
            if ($char === '"') {
                if (($raw[$index + 1] ?? '') === '"') {
                    $index++;
                } else {
                    $inQuotes = false;
                }
            }
            continue;
        }
        if ($char === '"' && $fieldStart) {
            $inQuotes = true;
            $fieldStart = false;
        } elseif ($char === $delimiter) {
            $columns++;
            if ($columns > TRAFFIC_IMPORT_MAX_COLUMNS) {
                return 'فایل بیش از ۶۴ ستون دارد؛ فقط ستون‌های لازم و پشتیبانی‌شده را نگه دارید.';
            }
            $fieldStart = true;
        } elseif ($char === "\n" || $char === "\r") {
            $records++;
            if ($records > TRAFFIC_IMPORT_MAX_ROWS + 20) {
                return 'فایل بیش از ۱۰٬۰۰۰ ردیف دارد؛ آن را به چند فایل کوچک‌تر تقسیم کنید.';
            }
            if ($char === "\r" && ($raw[$index + 1] ?? '') === "\n") {
                $index++;
            }
            $columns = 1;
            $fieldStart = true;
        } else {
            $fieldStart = false;
        }
    }
    if ($inQuotes) {
        return 'ساختار نقل‌قول‌های CSV کامل نیست؛ فایل را دوباره از Excel ذخیره کنید.';
    }
    $lastChar = $length > 0 ? $raw[$length - 1] : '';
    if ($length > 0 && $lastChar !== "\n" && $lastChar !== "\r") {
        $records++;
    }
    return $records > TRAFFIC_IMPORT_MAX_ROWS + 20
        ? 'فایل بیش از ۱۰٬۰۰۰ ردیف دارد؛ آن را به چند فایل کوچک‌تر تقسیم کنید.'
        : '';
}

/** خواندن آپلود به‌صورت موقت؛ هیچ فایل کاربر روی دیسک برنامه ذخیره نمی‌شود. */
function traffic_import_read_upload(mixed $upload): array
{
    if (!is_array($upload)) {
        return ['ok' => false, 'error' => 'فایلی انتخاب نشده است.'];
    }
    $uploadError = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'حجم فایل از محدودیت آپلود PHP بیشتر است.',
            UPLOAD_ERR_FORM_SIZE => 'حجم فایل از حد مجاز فرم بیشتر است.',
            UPLOAD_ERR_PARTIAL => 'آپلود فایل کامل نشد؛ دوباره تلاش کنید.',
            UPLOAD_ERR_NO_FILE => 'فایلی انتخاب نشده است.',
            UPLOAD_ERR_NO_TMP_DIR => 'پوشهٔ موقت آپلود روی سرور موجود نیست.',
            UPLOAD_ERR_CANT_WRITE => 'ذخیرهٔ موقت فایل روی سرور ناموفق بود.',
            UPLOAD_ERR_EXTENSION => 'یک افزونهٔ سرور آپلود فایل را متوقف کرد.',
        ];
        return ['ok' => false, 'error' => $messages[$uploadError] ?? 'آپلود فایل ناموفق بود.'];
    }
    $path = (string) ($upload['tmp_name'] ?? '');
    if ($path === '' || !is_uploaded_file($path) || !is_file($path)) {
        return ['ok' => false, 'error' => 'فایل آپلودشده معتبر نیست.'];
    }
    $size = (int) (filesize($path) ?: ($upload['size'] ?? 0));
    if ($size <= 0) {
        return ['ok' => false, 'error' => 'فایل خالی است.'];
    }
    if ($size > TRAFFIC_IMPORT_MAX_BYTES) {
        return ['ok' => false, 'error' => 'حداکثر حجم فایل ۱۰ مگابایت است.'];
    }
    $originalName = basename(str_replace('\\', '/', (string) ($upload['name'] ?? 'traffic-data')));
    $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, ['xlsx', 'xlsm', 'csv', 'txt'], true)) {
        return ['ok' => false, 'error' => 'فقط فایل Excel با پسوند xlsx/xlsm یا CSV/TXT پذیرفته می‌شود. فایل قدیمی xls را ابتدا با Excel به xlsx یا CSV ذخیره کنید.'];
    }

    if (in_array($extension, ['xlsx', 'xlsm'], true)) {
        $archiveError = traffic_import_xlsx_archive_error($path);
        if ($archiveError !== '') {
            return ['ok' => false, 'error' => $archiveError];
        }
        $read = food_ticket_import_read_xlsx($path, TRAFFIC_IMPORT_MAX_COLUMNS, TRAFFIC_IMPORT_MAX_ROWS + 20);
    } else {
        $csvShapeError = traffic_import_csv_shape_error($path);
        if ($csvShapeError !== '') {
            return ['ok' => false, 'error' => $csvShapeError];
        }
        $read = food_ticket_import_read_csv($path);
    }
    if (!is_array($read) || trim((string) ($read['error'] ?? '')) !== '') {
        return ['ok' => false, 'error' => (string) ($read['error'] ?? 'محتوای فایل خوانده نشد.')];
    }
    $parsed = traffic_import_parse_sheet(is_array($read['rows'] ?? null) ? $read['rows'] : []);
    if (empty($parsed['ok'])) {
        return $parsed;
    }
    $hash = hash_file('sha256', $path);
    if (!is_string($hash) || $hash === '') {
        return ['ok' => false, 'error' => 'بررسی یکسان‌بودن فایل انجام نشد؛ دوباره تلاش کنید.'];
    }
    return $parsed + [
        'file_name' => mb_substr($originalName, 0, 190, 'UTF-8'),
        'hash' => $hash,
        'size' => $size,
    ];
}

function traffic_import_identity(array $row): string
{
    $name = food_ticket_import_key((string) ($row['full_name'] ?? ''));
    $code = food_ticket_import_digits((string) ($row['national_code'] ?? ''));
    $phone = food_ticket_import_digits((string) ($row['phone'] ?? ''));
    $secondary = $code !== '' ? 'n:' . $code : ($phone !== '' ? 'p:' . $phone : 'c:' . food_ticket_import_key((string) ($row['company'] ?? '')));
    $entry = trim((string) ($row['entry_time'] ?? ''));
    return implode('|', [(string) ($row['visit_date'] ?? ''), $name, $secondary, $entry]);
}

function traffic_import_serial_key(string $serial): string
{
    return mb_strtolower(trim($serial), 'UTF-8');
}

function traffic_import_generated_serial(string $visitDate, int $sequence): string
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $visitDate, $parts) || !function_exists('gregorian_to_jalali')) {
        throw new RuntimeException('تاریخ برای ساخت شماره برگ معتبر نیست.');
    }
    [$jy, $jm, $jd] = gregorian_to_jalali((int) $parts[1], (int) $parts[2], (int) $parts[3]);
    return sprintf('%04d%02d%02d-%04d', $jy, $jm, $jd, $sequence);
}

function traffic_import_generated_serial_prefix(string $visitDate): string
{
    $serial = traffic_import_generated_serial($visitDate, 1);
    return substr($serial, 0, (int) strrpos($serial, '-'));
}

/** طبقه‌بندی خالص ردیف‌ها؛ مناسب تست آفلاین و پیش‌نمایش. */
function traffic_import_build_plan(array $rows, array $existingRecords = [], array $extraExistingSerials = []): array
{
    $existingIdentities = [];
    $reservedSerials = [];
    foreach ($existingRecords as $record) {
        if (!is_array($record)) {
            continue;
        }
        $serial = trim((string) ($record['serial_no'] ?? ''));
        if ($serial !== '') {
            $reservedSerials[traffic_import_serial_key($serial)] = true;
        }
        if (isset($record['visit_date'], $record['full_name'])) {
            $identity = traffic_import_identity($record);
            if ($identity !== '') {
                $existingIdentities[$identity] = true;
            }
        }
    }
    foreach ($extraExistingSerials as $serial) {
        if (is_scalar($serial) && trim((string) $serial) !== '') {
            $reservedSerials[traffic_import_serial_key((string) $serial)] = true;
        }
    }

    $explicitSerialCounts = [];
    foreach ($rows as $candidate) {
        if (!is_array($candidate)) {
            continue;
        }
        $explicit = traffic_import_optional_marker(trim((string) ($candidate['serial_no'] ?? '')));
        if ($explicit !== '') {
            $key = traffic_import_serial_key($explicit);
            $explicitSerialCounts[$key] = ($explicitSerialCounts[$key] ?? 0) + 1;
        }
    }

    $counts = ['insert' => 0, 'exists' => 0, 'duplicate' => 0, 'invalid' => 0, 'warnings' => 0];
    $seenRows = [];
    $out = [];
    $nextByDate = [];
    foreach ($rows as $sourceRow) {
        if (!is_array($sourceRow)) {
            continue;
        }
        $row = $sourceRow;
        $errors = array_values(array_filter(array_map('strval', (array) ($row['errors'] ?? [])), static fn (string $message): bool => trim($message) !== ''));
        $warnings = array_values(array_unique(array_filter(array_map('strval', (array) ($row['warnings'] ?? [])), static fn (string $message): bool => trim($message) !== '')));
        $date = (string) ($row['visit_date'] ?? '');
        if ($date === '' || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $dateParts) !== 1 || !checkdate((int) $dateParts[2], (int) $dateParts[3], (int) $dateParts[1])) {
            $errors[] = 'تاریخ تردد معتبر نیست.';
        }
        if (trim((string) ($row['full_name'] ?? '')) === '') {
            $errors[] = 'نام و نام خانوادگی الزامی است.';
        }
        $serial = trim((string) ($row['serial_no'] ?? ''));
        if ($serial !== '' && (traffic_import_text_length($serial) > 30 || preg_match('/[\x00-\x1F\x7F]/', $serial) === 1)) {
            $errors[] = 'شماره برگ معتبر نیست.';
        }
        $identity = traffic_import_identity($row);
        $action = 'invalid';
        $reason = '';

        if ($errors !== []) {
            $reason = implode(' ', array_values(array_unique($errors)));
        } elseif (isset($seenRows[$identity])) {
            $action = 'duplicate';
            $reason = 'این ردیف در همین فایل پیش‌تر آمده است.';
        } elseif (isset($existingIdentities[$identity])) {
            $action = 'exists';
            $reason = 'تردد مشابه از قبل در سامانه ثبت شده است.';
            $seenRows[$identity] = true;
        } else {
            if ($serial === '') {
                $sequenceKey = $date;
                $sequence = (int) ($nextByDate[$sequenceKey] ?? 1);
                do {
                    $serial = traffic_import_generated_serial($date, $sequence++);
                    $generatedKey = traffic_import_serial_key($serial);
                } while (isset($reservedSerials[$generatedKey]) || isset($explicitSerialCounts[$generatedKey]));
                $nextByDate[$sequenceKey] = $sequence;
            }
            $serialKey = traffic_import_serial_key($serial);
            if ($serial !== '' && ($explicitSerialCounts[$serialKey] ?? 0) > 1) {
                $action = 'invalid';
                $reason = 'همین شماره برگ در چند ردیف فایل تکرار شده است؛ شماره‌ها را اصلاح کنید یا خالی بگذارید تا خودکار ساخته شوند.';
            } elseif (isset($reservedSerials[$serialKey])) {
                $action = 'invalid';
                $reason = 'شماره برگ تکراری است؛ شمارهٔ موجود را اصلاح کنید یا ستون شماره برگ را خالی بگذارید تا خودکار ساخته شود.';
            } else {
                $action = 'insert';
                $row['serial_no'] = $serial;
                $reservedSerials[$serialKey] = true;
                $seenRows[$identity] = true;
                $reason = $warnings !== [] ? implode(' ', $warnings) : 'آمادهٔ ثبت';
            }
        }
        if ($warnings !== []) {
            $counts['warnings']++;
        }
        $counts[$action]++;
        $row['action'] = $action;
        $row['reason'] = $reason;
        $row['warnings'] = $warnings;
        $row['errors'] = $errors;
        $row['serial_no'] = $serial !== '' ? $serial : (string) ($row['serial_no'] ?? '');
        $out[] = $row;
    }
    return ['rows' => $out, 'counts' => $counts, 'total' => count($out)];
}

/** کوئری‌های تطبیق به‌صورت دسته‌ای اجرا می‌شوند تا فایل چند هزارردیفی هم پایدار بماند. */
function traffic_import_plan_from_database(array $rows): array
{
    $dates = [];
    $customSerials = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $date = (string) ($row['visit_date'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $dates[$date] = true;
        }
        $serial = trim((string) ($row['serial_no'] ?? ''));
        if ($serial !== '') {
            $customSerials[$serial] = true;
        }
    }

    $pdo = db();
    $existing = [];
    $serialSet = [];
    $dateList = array_keys($dates);
    sort($dateList);
    foreach (array_chunk($dateList, 400) as $chunk) {
        if ($chunk === []) {
            continue;
        }
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $query = $pdo->prepare("SELECT serial_no, visit_date, full_name, national_code, phone, company, entry_time, exit_time
            FROM traffic_visits WHERE visit_date IN ({$placeholders})" . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
        $query->execute($chunk);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $record) {
            $existing[] = $record;
            $serial = trim((string) ($record['serial_no'] ?? ''));
            if ($serial !== '') {
                $serialSet[traffic_import_serial_key($serial)] = true;
            }
        }
    }

    // شمارهٔ تولیدی روزانه باید با serialهای همان پیشوند، حتی اگر visit_date قدیمی ناسازگار باشد، تصادم نکند.
    foreach (array_chunk($dateList, 100) as $chunk) {
        if ($chunk === []) {
            continue;
        }
        $patterns = array_map(static fn (string $date): string => traffic_import_generated_serial_prefix($date) . '-%', $chunk);
        $conditions = implode(' OR ', array_fill(0, count($patterns), 'serial_no LIKE ?'));
        $query = $pdo->prepare("SELECT serial_no FROM traffic_visits WHERE ({$conditions})" . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
        $query->execute($patterns);
        foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $serial) {
            $serialSet[traffic_import_serial_key((string) $serial)] = true;
        }
    }

    // شمارهٔ دستی ممکن است با رکوردی از تاریخ دیگری تداخل داشته باشد.
    $serialList = array_keys($customSerials);
    foreach (array_chunk($serialList, 400) as $chunk) {
        if ($chunk === []) {
            continue;
        }
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $query = $pdo->prepare("SELECT serial_no FROM traffic_visits WHERE serial_no IN ({$placeholders})" . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
        $query->execute($chunk);
        foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $serial) {
            $serialSet[traffic_import_serial_key((string) $serial)] = true;
        }
    }
    return traffic_import_build_plan($rows, $existing, array_keys($serialSet));
}

/** اطلاعات لازم برای پیش‌نمایش در session؛ کد ملی فقط با چهار رقم پایانی نگه‌داری می‌شود. */
function traffic_import_preview_session_data(array $plan, string $hash, string $fileName, int $actorId): array
{
    $sample = [];
    foreach (array_slice((array) ($plan['rows'] ?? []), 0, TRAFFIC_IMPORT_PREVIEW_ROWS) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $code = (string) ($row['national_code'] ?? '');
        $maskedCode = $code !== '' ? str_repeat('•', max(0, strlen($code) - 4)) . substr($code, -4) : '';
        $sample[] = [
            'line' => (int) ($row['line'] ?? 0),
            'serial_no' => (string) ($row['serial_no'] ?? ''),
            'visit_date' => (string) ($row['visit_date'] ?? ''),
            'full_name' => (string) ($row['full_name'] ?? ''),
            'national_code' => $maskedCode,
            'entry_time' => (string) ($row['entry_time'] ?? ''),
            'exit_time' => (string) ($row['exit_time'] ?? ''),
            'action' => (string) ($row['action'] ?? 'invalid'),
            'reason' => (string) ($row['reason'] ?? ''),
        ];
    }
    return [
        'token' => bin2hex(random_bytes(16)),
        'hash' => $hash,
        'actor_id' => $actorId,
        'expires_at' => time() + TRAFFIC_IMPORT_PREVIEW_TTL,
        'file_name' => mb_substr($fileName, 0, 190, 'UTF-8'),
        'counts' => (array) ($plan['counts'] ?? []),
        'total' => (int) ($plan['total'] ?? count($sample)),
        'rows' => $sample,
        'truncated' => (int) ($plan['total'] ?? 0) > count($sample),
    ];
}

function traffic_import_current_preview(int $actorId): ?array
{
    $preview = $_SESSION['traffic_import_preview'] ?? null;
    if (!is_array($preview)) {
        unset($_SESSION['traffic_import_preview']);
        return null;
    }
    if ((int) ($preview['actor_id'] ?? 0) !== $actorId || (int) ($preview['expires_at'] ?? 0) < time()
        || !is_string($preview['token'] ?? null) || !is_string($preview['hash'] ?? null)) {
        unset($_SESSION['traffic_import_preview']);
        return null;
    }
    return $preview;
}

/** CSRF-protected preview/confirm dispatcher called from the ordinary POST handler. */
function traffic_import_handle_post(string $action, array $user): never
{
    $requiredActions = ['traffic_import_preview', 'traffic_import_apply'];
    if (!in_array($action, $requiredActions, true)) {
        throw new RuntimeException('عملیات ورود سوابق تردد نامعتبر است.');
    }
    if (($_GET['page'] ?? '') !== 'traffic-control' || ($_GET['tab'] ?? '') !== 'import') {
        http_response_code(404);
        exit('مسیر ورود سوابق تردد پیدا نشد.');
    }
    if (!function_exists('user_can') || !user_can($user, 'traffic.view') || !user_can($user, 'traffic.manage')) {
        http_response_code(403);
        exit('برای ورود سوابق تردد، دسترسی‌های «مشاهده» و «مدیریت کنترل تردد» لازم است.');
    }
    $actorId = (int) ($user['id'] ?? 0);
    $destination = 'index.php?page=traffic-control&tab=import';

    try {
        if ($action === 'traffic_import_preview') {
            unset($_SESSION['traffic_import_preview']);
            $read = traffic_import_read_upload($_FILES['file'] ?? null);
            if (empty($read['ok'])) {
                throw new RuntimeException((string) ($read['error'] ?? 'خواندن فایل انجام نشد.'));
            }
            $plan = traffic_import_plan_from_database((array) $read['rows']);
            $_SESSION['traffic_import_preview'] = traffic_import_preview_session_data(
                $plan,
                (string) $read['hash'],
                (string) $read['file_name'],
                $actorId
            );
            flash('info', 'پیش‌نمایش آماده شد. ردیف‌های قابل ثبت را بررسی کنید؛ هنوز هیچ ترددی ذخیره نشده است.');
            redirect($destination);
        }

        $preview = traffic_import_current_preview($actorId);
        $postedToken = (string) ($_POST['preview_token'] ?? '');
        if (!$preview || $postedToken === '' || !hash_equals((string) $preview['token'], $postedToken)) {
            throw new RuntimeException('پیش‌نمایش معتبر یا تازه‌ای پیدا نشد؛ فایل را دوباره بررسی کنید.');
        }
        $read = traffic_import_read_upload($_FILES['file'] ?? null);
        if (empty($read['ok'])) {
            throw new RuntimeException((string) ($read['error'] ?? 'خواندن فایل انجام نشد.'));
        }
        if (!hash_equals((string) $preview['hash'], (string) $read['hash'])) {
            throw new RuntimeException('فایل انتخاب‌شده با فایل پیش‌نمایش یکی نیست؛ همان فایل را دوباره انتخاب کنید.');
        }

        // DDL احتمالی لاگ قبل از تراکنش انجام شود تا CREATE TABLE موجب commit ضمنی MySQL نشود.
        if (function_exists('activity_logs_ensure')) {
            activity_logs_ensure();
        }
        // برنامه‌ریزی دوباره داخل تراکنش؛ رکوردهایی که از زمان preview اضافه شده‌اند حذف تکراری می‌شوند.
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $plan = traffic_import_plan_from_database((array) $read['rows']);
            $counts = traffic_import_apply_plan_in_transaction($plan, $actorId, $pdo);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        unset($_SESSION['traffic_import_preview']);
        $inserted = (int) ($counts['insert'] ?? 0);
        flash($inserted > 0 ? 'success' : 'info', 'پایان ورود سوابق: ' . $inserted . ' تردد ثبت شد، '
            . (int) ($counts['exists'] ?? 0) . ' مورد از قبل موجود بود، '
            . (int) ($counts['duplicate'] ?? 0) . ' مورد تکراری در فایل و '
            . (int) ($counts['invalid'] ?? 0) . ' مورد نامعتبر بود.');
        redirect($destination);
    } catch (RuntimeException $exception) {
        if (isset($GLOBALS['activity_post'])) {
            $GLOBALS['activity_post']['failed'] = true;
        }
        flash('danger', $exception->getMessage());
        redirect($destination);
    } catch (Throwable $exception) {
        if (isset($GLOBALS['activity_post'])) {
            $GLOBALS['activity_post']['failed'] = true;
        }
        if (function_exists('app_error_log')) {
            app_error_log('[traffic-import] ' . $exception->getMessage());
        }
        flash('danger', 'ورود سوابق انجام نشد؛ گزارش خطا در سامانه ثبت شد.');
        redirect($destination);
    }
}

/** درج نهایی فقط پس از بازشدن تراکنش تأیید انجام می‌شود. */
function traffic_import_apply_plan_in_transaction(array $plan, int $actorId, PDO $pdo): array
{
    if (!$pdo->inTransaction()) {
        throw new RuntimeException('تراکنش ثبت سوابق باز نیست.');
    }
    $insert = $pdo->prepare('INSERT INTO traffic_visits (serial_no, visit_date, full_name, national_code, phone, company, entry_time, exit_time, no_visit, with_car, with_mobile, meeting_with, approved_by, description, is_notice, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ((array) ($plan['rows'] ?? []) as $row) {
        if (!is_array($row) || ($row['action'] ?? '') !== 'insert') {
            continue;
        }
        $insert->execute([
            (string) $row['serial_no'],
            (string) $row['visit_date'],
            (string) $row['full_name'],
            trim((string) ($row['national_code'] ?? '')) !== '' ? (string) $row['national_code'] : null,
            trim((string) ($row['phone'] ?? '')) !== '' ? (string) $row['phone'] : null,
            trim((string) ($row['company'] ?? '')) !== '' ? (string) $row['company'] : null,
            $row['entry_time'] ?? null,
            $row['exit_time'] ?? null,
            (int) ($row['no_visit'] ?? 0),
            (int) ($row['with_car'] ?? 0),
            (int) ($row['with_mobile'] ?? 0),
            trim((string) ($row['meeting_with'] ?? '')) !== '' ? (string) $row['meeting_with'] : null,
            trim((string) ($row['approved_by'] ?? '')) !== '' ? (string) $row['approved_by'] : null,
            trim((string) ($row['description'] ?? '')) !== '' ? (string) $row['description'] : null,
            (int) ($row['is_notice'] ?? 0),
            $actorId,
        ]);
    }
    $counts = is_array($plan['counts'] ?? null) ? $plan['counts'] : [];
    if ((int) ($counts['insert'] ?? 0) > 0 && function_exists('save_audit')) {
        save_audit($actorId, 'traffic_records_imported', null, [
            'inserted' => (int) ($counts['insert'] ?? 0),
            'existing' => (int) ($counts['exists'] ?? 0),
            'duplicates' => (int) ($counts['duplicate'] ?? 0),
            'invalid' => (int) ($counts['invalid'] ?? 0),
        ]);
    }
    return $counts;
}

function traffic_import_template_headers(): array
{
    return [
        'شماره برگ', 'تاریخ تردد', 'نام و نام خانوادگی', 'کد ملی', 'تلفن همراه', 'موسسه / شرکت',
        'ساعت ورود', 'ساعت خروج', 'ملاقات با', 'تأییدکننده', 'بدون بازدید', 'با خودرو', 'با تلفن همراه', 'توضیحات', 'تردد خاص',
    ];
}

function traffic_import_template_csv_bytes(): string
{
    $handle = fopen('php://temp', 'r+');
    if ($handle === false) {
        throw new RuntimeException('ساخت قالب CSV ممکن نشد.');
    }
    fputcsv($handle, traffic_import_template_headers(), ',', '"', '');
    rewind($handle);
    $body = stream_get_contents($handle);
    fclose($handle);
    return "\xEF\xBB\xBF" . (is_string($body) ? $body : '');
}

function traffic_import_download_template(string $format): never
{
    $headers = traffic_import_template_headers();
    if ($format === 'xlsx') {
        if (!function_exists('xlsx_build') || !function_exists('xlsx_send') || !class_exists('ZipArchive')) {
            http_response_code(503);
            exit('ساخت قالب XLSX ممکن نیست؛ از قالب CSV استفاده کنید.');
        }
        $headerRow = array_map(static fn (string $header): array => ['v' => $header, 's' => 'header'], $headers);
        $bytes = xlsx_build([[
            'name' => 'قالب ورود تردد',
            'rows' => [$headerRow],
            'widths' => [16, 17, 25, 15, 18, 22, 14, 14, 20, 20, 15, 12, 18, 32, 12],
        ]]);
        xlsx_send('traffic-import-template.xlsx', $bytes);
    }
    $bytes = traffic_import_template_csv_bytes();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Length: ' . strlen($bytes));
    header('Content-Disposition: attachment; filename="traffic-import-template.csv"');
    header('X-Content-Type-Options: nosniff');
    echo $bytes;
    exit;
}
