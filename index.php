<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/xlsx-writer.php';
if (is_file(__DIR__ . '/admin-layout-design.php')) {
    require_once __DIR__ . '/admin-layout-design.php';
}
require __DIR__ . '/inventory.php';
require __DIR__ . '/asset-profile.php';
require __DIR__ . '/domain-scan.php';
require __DIR__ . '/asset-names.php';
require __DIR__ . '/asset-inventory-page.php';
require __DIR__ . '/organization.php';
require __DIR__ . '/global-search.php';
require __DIR__ . '/backup.php';
require __DIR__ . '/chatbot.php';
require __DIR__ . '/system-update.php';
require __DIR__ . '/cd-dvd.php';
require __DIR__ . '/food-ticket.php';
require __DIR__ . '/traffic-control.php';
require __DIR__ . '/local-accounts.php';
require __DIR__ . '/system-health.php';

ticket_number_ensure_schema(); // پیش از هر تراکنش: DDL اینجا بی‌خطر است و داخل تراکنش دیگر اجرا نمی‌شود
ticket_number_autofill();
role_permissions_seed();
// تبدیل رکوردهای قدیمی غذا فقط هنگام بارگذاری فهرست کارکنان پنل غذا انجام می‌شود،
// نه در هر درخواست و نه قبل از نمایش فرم ورود.

if (isset($_GET['installed'])) {
    flash('success', 'سامانه با موفقیت نصب شد.');
}

function post_value(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function csv_cell(mixed $value): string
{
    $value = (string) $value;
    return preg_match('/^[=+\-@]/', ltrim($value)) ? "'" . $value : $value;
}

function excel_cell(mixed $value): string
{
    return htmlspecialchars(csv_cell($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function excel_download(string $filename, string $title, array $headers, array $rows, array $dateRange = []): never
{
    $from = trim((string) ($dateRange['from'] ?? $dateRange['start'] ?? ''));
    $to = trim((string) ($dateRange['to'] ?? $dateRange['end'] ?? ''));
    $singleDate = trim((string) ($dateRange['date'] ?? ''));
    if ($singleDate !== '') {
        $from = $singleDate;
        $to = $singleDate;
    }
    if ($from === '' && $to === '') {
        $from = $to = 'بدون فیلتر تاریخی';
    } elseif ($from === '') {
        $from = 'بدون محدودیت شروع';
    } elseif ($to === '') {
        $to = 'بدون محدودیت پایان';
    }
    $jalaliLabel = static function (string $value): string {
        $value = trim($value);
        $digits = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        if (preg_match('/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/', $digits, $parts) === 1) {
            $year = (int) $parts[1];
            $month = (int) $parts[2];
            $day = (int) $parts[3];
            if ($year >= 1300 && $year <= 1600) {
                return sprintf('%04d/%02d/%02d', $year, $month, $day);
            }
            if ($year >= 1900 && $year <= 2200 && function_exists('gregorian_to_jalali')) {
                try {
                    [$jy, $jm, $jd] = gregorian_to_jalali($year, $month, $day);
                    return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
                } catch (Throwable) {
                    // Preserve the original label if the supplied date is invalid.
                }
            }
        }
        return $value;
    };
    $from = $jalaliLabel($from);
    $to = $jalaliLabel($to);
    $from = $jalaliLabel($from);
    $to = $jalaliLabel($to);
    $columnCount = max(2, count($headers));
    $sheetRows = [];
    $sheetRows[] = [['v' => $title, 's' => 'title']];
    $sheetRows[] = [['v' => 'تاریخ شروع', 's' => 'header'], ['v' => $from, 's' => 'body']];
    $sheetRows[] = [['v' => 'تاریخ پایان', 's' => 'header'], ['v' => $to, 's' => 'body']];
    $sheetRows[] = array_map(static fn ($header): array => ['v' => (string) $header, 's' => 'header'], array_values($headers));
    foreach ($rows as $row) {
        // همهٔ مقادیر رشته‌ای می‌شوند تا صفرهای ابتدای کدها (مثل کد ملی) حفظ شود
        $sheetRows[] = array_map(static fn ($value): array => ['v' => is_scalar($value) ? (string) $value : '', 's' => 'body'], array_values((array) $row));
    }
    $bytes = xlsx_build([[
        'name' => 'گزارش',
        'rows' => $sheetRows,
        'merges' => ['A1:' . xlsx_col_name($columnCount - 1) . '1'],
        'widths' => array_fill(0, $columnCount, 20),
    ]]);
    xlsx_send($filename, $bytes);
}

function valid_choice(string $value, array $allowed, string $fallback): string
{
    return in_array($value, $allowed, true) ? $value : $fallback;
}

/** مرتب‌سازی سمت سرور لاگ‌ها: فقط کلیدهای مجاز از فهرست سفید می‌شوند. */
function log_sort_order(array $map, string $key, string $dir, string $default): string
{
    if ($key === '' || !isset($map[$key])) {
        return $default;
    }
    $direction = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
    return ' ORDER BY ' . $map[$key] . ' ' . $direction . ', ' . ($map['__tie'] ?? 'l.id') . ' ' . $direction;
}

/** سرستون‌های قابل مرتب‌سازی؛ کلیک: صعودی ← نزولی ← بدون مرتب‌سازی. */
function log_sort_header_row(array $columns, string $sortParam, string $dirParam, array $args, string $activeKey, string $activeDir, string $class = 'table-head'): string
{
    $html = '<div class="' . $class . '">';
    foreach ($columns as [$key, $label]) {
        if ($key === '') { $html .= '<span>' . e($label) . '</span>'; continue; }
        $isActive = $key === $activeKey && $activeKey !== '';
        $nextArgs = $args; unset($nextArgs[$sortParam], $nextArgs[$dirParam]);
        if ($isActive) {
            $arrow = strtolower($activeDir) === 'desc' ? ' ▼' : ' ▲';
            if (strtolower($activeDir) !== 'desc') { $nextArgs[$sortParam] = $key; $nextArgs[$dirParam] = 'desc'; }
        } else {
            $arrow = '';
            $nextArgs[$sortParam] = $key; $nextArgs[$dirParam] = 'asc';
        }
        $nextArgs['p'] = 1;
        $html .= '<span><a class="log-sort" style="color:inherit;text-decoration:none" href="index.php?' . e(http_build_query($nextArgs)) . '" title="مرتب‌سازی بر اساس ' . e($label) . '">' . e($label) . e($arrow) . '</a></span>';
    }
    return $html . '</div>';
}

function activity_log_filters(): array
{
    $conditions = [];
    $params = [];
    $userId = (int) ($_GET['user_id'] ?? 0);
    if ($userId > 0) {
        $conditions[] = 'l.user_id = ?';
        $params[] = $userId;
    }
    $actionCode = trim((string) ($_GET['action_code'] ?? ''));
    if ($actionCode !== '') {
        $conditions[] = 'l.action_code = ?';
        $params[] = $actionCode;
    }
    $module = trim((string) ($_GET['module'] ?? ''));
    if ($module !== '') {
        $conditions[] = 'l.module = ?';
        $params[] = $module;
    }
    $ip = trim((string) ($_GET['ip'] ?? ''));
    if ($ip !== '') {
        $conditions[] = 'l.ip_address LIKE ?';
        $params[] = '%' . $ip . '%';
    }
    $from = trim((string) ($_GET['from'] ?? ''));
    if ($from !== '') {
        $date = jalali_input_to_gregorian($from);
        if ($date) {
            $conditions[] = 'l.created_at >= ?';
            $params[] = $date;
        }
    }
    $to = trim((string) ($_GET['to'] ?? ''));
    if ($to !== '') {
        $date = jalali_input_to_gregorian($to, true);
        if ($date) {
            $conditions[] = 'l.created_at <= ?';
            $params[] = $date;
        }
    }
    $search = trim((string) ($_GET['q'] ?? ''));
    if ($search !== '') {
        $conditions[] = '(l.full_name LIKE ? OR l.username LIKE ? OR l.action_label LIKE ? OR l.action_code LIKE ? OR l.computer_name LIKE ? OR l.ip_address LIKE ? OR l.meta_json LIKE ?)';
        $params = array_merge($params, array_fill(0, 7, '%' . $search . '%'));
    }
    $category = valid_choice((string) ($_GET['category'] ?? 'all'), ['all', 'auth', 'admin', 'errors'], 'all');
    if ($category === 'auth') {
        $conditions[] = '(l.module = ? OR l.action_code LIKE ? OR l.action_code = ?)';
        array_push($params, 'auth', 'login_%', 'logout');
    } elseif ($category === 'admin') {
        $conditions[] = '(l.module IN (?, ?, ?) OR l.action_code LIKE ? OR l.action_code LIKE ? OR (l.module IS NULL AND l.action_code NOT LIKE ? AND l.action_code <> ? AND l.action_code <> ?))';
        array_push($params, 'settings', 'permissions', 'audit', 'settings_%', 'role_%', 'login_%', 'logout', 'page_view');
    } elseif ($category === 'errors') {
        $conditions[] = '(l.action_code LIKE ? OR l.action_code LIKE ? OR l.action_code LIKE ? OR l.module = ?)';
        array_push($params, '%failed%', '%error%', '%locked%', 'error');
    }
    return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
}

/** تبدیل جزئیات فنی لاگ به جفت‌های عنوان/مقدار قابل‌خواندن، با حذف رازها. */
function activity_log_meta_lines(?string $json): array
{
    if (!$json) return [];
    $data = json_decode($json, true);
    if (!is_array($data)) return [['جزئیات', $json]];
    $labels = ['before'=>'قبل','after'=>'بعد','old'=>'مقدار قبلی','new'=>'مقدار جدید','old_status'=>'وضعیت قبلی','new_status'=>'وضعیت جدید','status'=>'وضعیت','role'=>'نقش','theme'=>'تم','reason'=>'علت','rows'=>'تعداد ردیف','deleted'=>'حذف‌شده','days'=>'بازه (روز)','ip'=>'آی‌پی','module'=>'بخش','page'=>'صفحه','target'=>'مورد','field'=>'فیلد','value'=>'مقدار','from'=>'از','to'=>'به','ok'=>'نتیجه','file'=>'فایل','line'=>'خط','uri'=>'نشانی درخواست','request_uri'=>'نشانی درخواست','attempts'=>'تعداد تلاش'];
    $lines=[]; $walk=function(array $items,string $prefix='') use (&$walk,&$lines,$labels): void {
        foreach($items as $key=>$value) {
            $key=(string)$key; if (preg_match('/pass(word)?|token|secret|credential|private.?key/i',$key)) {$lines[]=[($labels[$key]??$key),'••••••'];continue;}
            $label=$labels[$key]??str_replace('_',' ',$key); $label=$prefix!==''?$prefix.' · '.$label:$label;
            if(is_array($value)){ if($value===[]) {$lines[]=[$label,'—'];} else {$walk($value,$label);} }
            elseif(is_bool($value)) {$lines[]=[$label,$value?'بله':'خیر'];}
            elseif($value===null || $value==='') {$lines[]=[$label,'—'];}
            else {$lines[]=[$label,mb_substr((string)$value,0,500,'UTF-8')];}
            if(count($lines)>=20) return;
        }
    };
    $walk($data);
    return $lines;
}

function imported_calendar_date(string $value): ?string
{
    $value = trim(str_replace(['-', '.'], '/', strtr($value, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9'])));
    if (preg_match('/^(13|14)\d{2}\/\d{1,2}\/\d{1,2}$/', $value)) {
        return jalali_input_to_gregorian($value);
    }
    if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
        $date = DateTimeImmutable::createFromFormat('!Y/n/j', $matches[1] . '/' . $matches[2] . '/' . $matches[3]);
        return $date && $date->format('Y/n/j') === $matches[1] . '/' . (int) $matches[2] . '/' . (int) $matches[3] ? $date->format('Y-m-d 00:00:00') : null;
    }
    if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $value, $matches)) {
        return imported_calendar_date($matches[1] . '/' . $matches[2] . '/' . $matches[3]);
    }
    return null;
}

function calendar_unescape(string $value): string
{
    return str_replace(['\\n', '\\N', '\\,', '\\;'], ["\n", "\n", ',', ';'], trim($value));
}

function parse_calendar_import(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || (int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('فایل تقویم باید سالم و حداکثر ۲ مگابایت باشد.');
    }
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ['csv', 'ics', 'txt'], true)) {
        throw new RuntimeException('فرمت تقویم باید CSV یا iCalendar با پسوند ICS باشد.');
    }
    $content = file_get_contents((string) $file['tmp_name']);
    if (!is_string($content) || trim($content) === '') {
        throw new RuntimeException('فایل تقویم خالی است.');
    }
    $events = [];
    if ($extension === 'ics') {
        $lines = preg_split('/\r\n|\n|\r/', preg_replace('/\r\n[ \t]/', '', $content) ?? $content) ?: [];
        $event = [];
        foreach ($lines as $line) {
            if (trim($line) === 'BEGIN:VEVENT') {
                $event = [];
            } elseif (trim($line) === 'END:VEVENT') {
                $date = imported_calendar_date((string) ($event['DTSTART'] ?? ''));
                $title = calendar_unescape((string) ($event['SUMMARY'] ?? 'تعطیلی رسمی'));
                if ($date && $title !== '') {
                    $events[] = ['date' => $date, 'title' => $title];
                }
                $event = [];
            } elseif (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $key = strtoupper(trim(explode(';', $key, 2)[0]));
                if (in_array($key, ['DTSTART', 'SUMMARY'], true)) {
                    $event[$key] = trim($value);
                }
            }
        }
    } else {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\n|\r/', trim($content)) ?: [];
        $delimiter = str_contains((string) ($lines[0] ?? ''), ';') ? ';' : (str_contains((string) ($lines[0] ?? ''), "\t") ? "\t" : ',');
        $firstLine = (string) array_shift($lines);
        $header = array_map(static fn (string $value): string => strtolower(trim($value)), str_getcsv($firstLine, $delimiter));
        $dateIndex = null;
        $titleIndex = null;
        foreach ($header as $index => $name) {
            if ($dateIndex === null && in_array($name, ['date', 'holiday_date', 'dtstart', 'تاریخ', 'تاریخ تعطیلی'], true)) {
                $dateIndex = $index;
            }
            if ($titleIndex === null && in_array($name, ['title', 'summary', 'name', 'description', 'عنوان', 'مناسبت'], true)) {
                $titleIndex = $index;
            }
        }
        if ($dateIndex === null && $titleIndex === null) {
            array_unshift($lines, $firstLine);
        }
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $columns = str_getcsv($line, $delimiter);
            $rawDate = $dateIndex === null ? (string) ($columns[0] ?? '') : (string) ($columns[$dateIndex] ?? '');
            $rawTitle = $titleIndex === null ? (string) ($columns[1] ?? 'تعطیلی رسمی') : (string) ($columns[$titleIndex] ?? 'تعطیلی رسمی');
            $date = imported_calendar_date($rawDate);
            if ($date) {
                $events[] = ['date' => $date, 'title' => calendar_unescape($rawTitle) ?: 'تعطیلی رسمی'];
            }
        }
    }
    if (!$events) {
        throw new RuntimeException('هیچ رویداد قابل استفاده‌ای در فایل پیدا نشد.');
    }
    return $events;
}

function store_attachment(int $messageId, array $file): void
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return;
    }
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('بارگذاری فایل انجام نشد.');
    }
    $max = (int) cfg('security.max_upload_mb', 8) * 1024 * 1024;
    $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if ((int) $file['size'] > $max || !in_array($extension, (array) cfg('security.allowed_uploads', []), true)) {
        throw new RuntimeException('نوع یا حجم فایل مجاز نیست.');
    }
    $mime = 'application/octet-stream';
    $mimeChecked = false;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string) (finfo_file($finfo, (string) $file['tmp_name']) ?: $mime);
            $mimeChecked = true;
            finfo_close($finfo);
        }
    }
    $allowedMimes = [
        'pdf' => ['application/pdf'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'doc' => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'txt' => ['text/plain', 'text/csv', 'application/octet-stream'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];
    if ($mimeChecked && isset($allowedMimes[$extension]) && !in_array($mime, $allowedMimes[$extension], true)) {
        throw new RuntimeException('محتوای فایل با پسوند آن مطابقت ندارد.');
    }
    $directory = APP_ROOT . '/storage/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('پوشه ذخیره فایل قابل ایجاد نیست.');
    }
    $stored = bin2hex(random_bytes(18)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $stored)) {
        throw new RuntimeException('ذخیره فایل انجام نشد.');
    }
    try {
        $query = db()->prepare('INSERT INTO ticket_attachments (message_id, original_name, stored_name, mime, size_bytes) VALUES (?, ?, ?, ?, ?)');
        $query->execute([$messageId, basename((string) $file['name']), $stored, $mime, (int) $file['size']]);
    } catch (Throwable $exception) {
        @unlink($directory . '/' . $stored);
        throw $exception;
    }
}

function store_profile_photo(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? '')) || (int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('عکس پروفایل باید سالم و حداکثر ۲ مگابایت باشد.');
    }
    $image = @getimagesize((string) ($file['tmp_name'] ?? ''));
    $types = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg'];
    $extension = $types[(int) ($image[2] ?? 0)] ?? null;
    if ($extension === null || (int) ($image[0] ?? 0) > 5000 || (int) ($image[1] ?? 0) > 5000) {
        throw new RuntimeException('عکس پروفایل باید PNG یا JPG باشد.');
    }
    $directory = APP_ROOT . '/storage/profile';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('پوشه ذخیره عکس پروفایل قابل ایجاد نیست.');
    }
    $stored = 'profile-' . bin2hex(random_bytes(18)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $stored)) {
        throw new RuntimeException('ذخیره عکس پروفایل انجام نشد.');
    }
    return $stored;
}

function valid_iranian_national_code(string $value): bool
{
    if (!preg_match('/^\d{10}$/', $value) || preg_match('/^(\d)\1{9}$/', $value)) {
        return false;
    }
    $check = (int) $value[9];
    $sum = 0;
    for ($index = 0; $index < 9; $index++) {
        $sum += (int) $value[$index] * (10 - $index);
    }
    $remainder = $sum % 11;
    return $check === ($remainder < 2 ? $remainder : 11 - $remainder);
}

function fetch_ticket(int $id): ?array
{
    $query = db()->prepare('SELECT t.*, c.name AS category_name, sc.name AS service_name, sc.code AS service_code, d.name AS department_name, ou.name AS requesting_unit_name, r.full_name AS requester_name, r.username AS requester_username, a.full_name AS assignee_name, ass.asset_tag, ass.hostname, tr.score AS rating_score, tr.comment AS rating_comment, tr.created_at AS rating_at FROM tickets t LEFT JOIN categories c ON c.id = t.category_id LEFT JOIN service_catalog sc ON sc.id = t.service_id LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN org_units ou ON ou.id = t.requesting_unit_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to LEFT JOIN assets ass ON ass.id = t.asset_id LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id WHERE t.id = ? LIMIT 1');
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

function can_view_ticket(array $ticket, array $user): bool
{
    // جداسازی حوزه پیش از درخواست‌های خود و مجوزهای سفارشی؛ کارمند پشتیبانی فقط پشتیبانی.
    // این کنترل روی جزئیات، پیوست، پاسخ، ارجاع و عملیات گروهی هم اعمال می‌شود.
    $role = (string) ($user['role'] ?? '');
    $ticketGroup = (string) ($ticket['service_group'] ?? '');
    if ($role === 'support_manager' && $ticketGroup !== 'support') {
        return false;
    }
    if (in_array($role, ['agent', 'manager'], true) && $ticketGroup !== user_service_group($user)) {
        return false;
    }
    // ۱.۳۷.۸ — دسترسی‌های مشاهدهٔ درخواست از سطوح دسترسی نقش‌ها خوانده می‌شود:
    // ticket.view_own (درخواست‌های خودم)، ticket.view_unit (واحد/زیرمجموعه)، ticket.view_all (همهٔ درخواست‌ها).
    if ((int) $ticket['requester_id'] === (int) $user['id'] && user_can($user, 'ticket.view_own')) {
        return true;
    }
    if (is_global_ticket_role($user['role']) && user_can($user, 'ticket.view_all')) {
        return true;
    }
    if (!user_can($user, 'ticket.view_unit')) {
        return false;
    }
    if (($user['role'] ?? '') === 'support_manager') {
        // مدیر پشتیبانی فقط حوزه پشتیبانی؛ بدون دسترسی به درخواست‌های IT و بدون فیلتر معاونت.
        return (string) ($ticket['service_group'] ?? '') === 'support';
    }
    if ($user['role'] === 'agent') {
        return (string) ($ticket['service_group'] ?? '') === user_service_group($user);
    }
    if ($user['role'] === 'manager') {
        // هم‌تراز با ticket_scope: مدیر بدون معاونت، کل حوزه را می‌بیند؛ وگرنه فقط معاونت خودش.
        if ((string) ($ticket['service_group'] ?? '') !== user_service_group($user)) {
            return false;
        }
        $managerDept = (int) ($user['department_id'] ?? 0);
        return $managerDept <= 0 || (int) ($ticket['department_id'] ?? 0) === $managerDept;
    }
    return false;
}

/**
 * ۱.۶۲.۶ — آیا زیرساخت چارت سازمانی برای تشخیص «مدیر سازمانی» در دسترس است؟
 * نتیجه کش می‌شود تا ثبت درخواست روی نصب‌های قدیمیِ بدون این جدول‌ها خطا نگیرد.
 */
function ticket_org_manager_rank_sql(): string
{
    static $ready = null;
    if ($ready === null) {
        try {
            db()->query('SELECT 1 FROM org_units LIMIT 1');
            db()->query('SELECT 1 FROM org_unit_managers LIMIT 1');
            $ready = true;
        } catch (Throwable) {
            $ready = false;
        }
    }
    return $ready
        ? 'CASE WHEN EXISTS (SELECT 1 FROM org_units ou WHERE ou.is_active = 1 AND (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id))) THEN 1 ELSE 0 END, '
        : '';
}

function choose_handling_agent(string $serviceGroup, ?int $departmentId = null): ?int
{
    $serviceGroup = in_array($serviceGroup, ['it', 'support'], true) ? $serviceGroup : 'it';
    $departmentId = (int) ($departmentId ?? 0);
    $loadJoin = ' LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status NOT IN ("resolved", "closed", "cancelled")';
    // ۱.۶۲.۶ — مدیران چارت سازمانی (معاون/مدیر اداره) آخرِ صف ارجاع خودکار می‌روند تا اگر
    // نقش/پرچم اشتباهی (مثل «ادمین اصلی» شدن ناخواسته) داشته باشند، تا وقتی کاندیدای
    // واقعی فعال هست انتخاب نشوند. برای اصلاح ریشه‌ای، نقش‌ها از صفحهٔ سازمان ترمیم شوند.
    $orgManagerRank = ticket_org_manager_rank_sql();

    // مالک اصلی هر حوزه اولویت دارد: IT ← ادمین اصلی؛ پشتیبانی ← مدیر پشتیبانی.
    // اگر برای یک حوزه چند مالک فعال تعریف شده باشد، کمترین بارِ باز ملاک می‌شود.
    $ownerCondition = $serviceGroup === 'it'
        ? '(u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1))'
        : 'u.role = "support_manager"';
    $ownerQuery = db()->query('SELECT u.id FROM users u' . $loadJoin . ' WHERE u.is_active = 1 AND ' . $ownerCondition . ' GROUP BY u.id ORDER BY ' . $orgManagerRank . 'COUNT(t.id), u.id LIMIT 1');
    $id = $ownerQuery->fetchColumn();
    if ($id !== false) {
        return (int) $id;
    }

    // اگر مسئول اصلی غیرفعال/تعریف‌نشده باشد، فقط کارشناس همان حوزه جایگزین می‌شود.
    // عمداً دیگر به کارشناس حوزهٔ دیگر ارجاع نمی‌دهیم؛ در نبود جایگزین، درخواست در manager_review می‌ماند.
    $fallback = db()->prepare(
        'SELECT u.id FROM users u '
        . 'LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id'
        . $loadJoin
        . ' WHERE u.is_active = 1 AND u.role IN ("agent", "manager", "support_manager", "primary_admin") AND hu.code = ?'
        // مدیر عادی فقط مسئول همان معاونت/واحدِ ثبت‌شده روی درخواست است؛ از معاونت دیگری انتخاب نشود.
        . ' AND (u.role <> "manager" OR (? > 0 AND u.department_id = ?))'
        . ' GROUP BY u.id ORDER BY ' . $orgManagerRank . 'COUNT(t.id), u.id LIMIT 1'
    );
    $fallback->execute([$serviceGroup, $departmentId, $departmentId]);
    $id = $fallback->fetchColumn();
    return $id === false ? null : (int) $id;
}

/**
 * مسیر مجاز تغییر وضعیت توسط کارکنان (مدل رسمی گردش درخواست).
 * new/manager_review -> assigned -> in_progress <-> waiting_user -> resolved -> (امتیاز کاربر) -> closed توسط سوپروایزر
 */
function ticket_allowed_transitions(string $from): array
{
    $map = [
        'new' => ['manager_review', 'assigned', 'in_progress'],
        'manager_review' => ['assigned', 'in_progress'],
        'assigned' => ['in_progress', 'waiting_user', 'resolved', 'manager_review'],
        'in_progress' => ['waiting_user', 'resolved', 'assigned', 'manager_review'],
        'waiting_user' => ['in_progress', 'resolved', 'assigned', 'manager_review'],
        'pending' => ['in_progress', 'waiting_user', 'resolved', 'assigned', 'manager_review'],
        'resolved' => ['in_progress', 'assigned'],
        'closed' => [],
        'cancelled' => [],
    ];
    return $map[$from] ?? [];
}

function ticket_requester_cancelable_statuses(): array
{
    // درخواست حل‌شده هم تا پیش از بسته‌شدن نهایی، با تأیید مسئول قابل لغو است.
    return ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved'];
}

function ticket_requester_can_cancel_directly(array $ticket): bool
{
    return in_array((string) ($ticket['status'] ?? ''), ['new', 'manager_review'], true)
        && empty($ticket['assigned_to'])
        && empty($ticket['first_response_at'])
        && empty($ticket['resolved_at']);
}

function ticket_requester_can_cancel(array $ticket, array $user): bool
{
    return user_can($user, 'ticket.create')
        && (int) ($ticket['requester_id'] ?? 0) === (int) ($user['id'] ?? 0)
        && in_array((string) ($ticket['status'] ?? ''), ticket_requester_cancelable_statuses(), true)
        && empty($ticket['cancel_requested_at']);
}

/** امتیازی معتبر است که ثبت شده باشد و قدیمی‌تر از آخرین «حل‌شدن» درخواست نباشد. */
function ticket_rating_is_current(array $ticket): bool
{
    if (empty($ticket['rating_score'])) {
        return false;
    }
    if (empty($ticket['resolved_at']) || empty($ticket['rating_at'])) {
        return true;
    }
    return strtotime((string) $ticket['rating_at']) >= strtotime((string) $ticket['resolved_at']);
}

/**
 * سطح اختیار کاربر روی درخواست: manager (ارجاع/تغییر کامل)، assignee (کارشناس مسئول)، یا رشتهٔ خالی.
 */
function ticket_handler_level(array $ticket, array $user): string
{
    if (!can_view_ticket($ticket, $user)) {
        return '';
    }
    // جداسازی حوزه حتی اگر مجوز ارجاع به‌اشتباه داده شده باشد: مدیر پشتیبانی فقط پشتیبانی.
    $ticketGroup = (string) ($ticket['service_group'] ?? '');
    if (($user['role'] ?? '') === 'support_manager' && $ticketGroup !== 'support') {
        return '';
    }
    if (in_array(($user['role'] ?? ''), ['agent', 'manager'], true) && $ticketGroup !== user_service_group($user)) {
        return '';
    }
    if (user_can($user, 'ticket.assign')) {
        return 'manager';
    }
    if ((int) ($ticket['assigned_to'] ?? 0) === (int) $user['id'] && is_assignable_role((string) ($user['role'] ?? ''))) {
        return 'assignee';
    }
    return '';
}

/** اعلان «حل شد» به کاربر (برای ثبت امتیاز یا بازگشایی) و به مسئولان (برای پیگیری تأیید نهایی). */
function notify_ticket_resolved(int $ticketId, int $actorId): void
{
    $resolvedTicket = fetch_ticket($ticketId);
    if (!$resolvedTicket) {
        return;
    }
    notify_user((int) $resolvedTicket['requester_id'], 'ticket_resolved', 'درخواست شما انجام شد', 'درخواست ' . ticket_number($ticketId) . ' حل شد. لطفاً نتیجه را بررسی و امتیاز رضایت خود را ثبت کنید؛ اگر مشکل برطرف نشده است درخواست را بازگشایی کنید.', $ticketId);
    notify_ticket_staff($ticketId, 'ticket_resolved', 'درخواست حل شد و منتظر امتیاز کاربر است', 'درخواست ' . ticket_number($ticketId) . ' حل‌شده است و پس از ثبت امتیاز کاربر قابل تأیید نهایی است.', $actorId);
}
function report_query_filters(array $user): array
{
    [$scope, $params] = ticket_scope($user);
    $conditions = $scope !== '' ? [substr($scope, 6)] : [];
    $ticketColumns = db_table_columns('tickets');
    $status = valid_choice((string) ($_GET['status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'active', 'all'], '');
    if ($status === 'active') {
        // «در حال پیگیری» = همهٔ وضعیت‌های باز (کارت داشبورد)
        $conditions[] = 't.status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending")';
    } elseif ($status === 'resolved') {
        // «حل‌شده» در داشبورد شامل درخواست‌های بسته‌شده هم می‌شود
        $conditions[] = 't.status IN ("resolved", "closed")';
    } elseif ($status !== '' && $status !== 'all') {
        $conditions[] = 't.status = ?';
        $params[] = $status;
    }
    $priority = valid_choice((string) ($_GET['priority'] ?? ''), ['normal', 'urgent', 'critical'], '');
    if ($priority !== '') {
        $conditions[] = 't.priority = ?';
        $params[] = $priority;
    }
    $ticketType = valid_choice((string) ($_GET['ticket_type'] ?? ''), ['incident', 'request', 'problem', 'change'], '');
    if ($ticketType !== '' && isset($ticketColumns['ticket_type'])) {
        $conditions[] = 't.ticket_type = ?';
        $params[] = $ticketType;
    }
    $serviceGroup = valid_choice((string) ($_GET['service_group'] ?? ''), ['it', 'support'], '');
    if ($serviceGroup !== '' && isset($ticketColumns['service_group'])) {
        $conditions[] = 't.service_group = ?';
        $params[] = $serviceGroup;
    }
    foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
        $value = (string) ($_GET[$key] ?? '');
        $converted = jalali_input_to_gregorian($value, $key === 'to');
        if ($converted !== null) {
            $conditions[] = 't.created_at ' . $operator . ' ?';
            $params[] = $converted;
        }
    }
    foreach (['department_id', 'assigned_to'] as $key) {
        $value = (int) ($_GET[$key] ?? 0);
        if ($value > 0) {
            $conditions[] = 't.' . $key . ' = ?';
            $params[] = $value;
        }
    }
    $search = trim((string) ($_GET['q'] ?? ''));
    if ($search !== '') {
        $conditions[] = '(t.subject LIKE ? OR t.description LIKE ? OR r.full_name LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    return [$conditions ? 'WHERE ' . implode(' AND ', $conditions) : '', $params];
}

function render_header(string $title, ?array $user = null): void
{
    global $page;
    $appName = setting('app_name', (string) cfg('app.name', 'سامانه درخواست پشتیبانی'));
    $logo = setting('app_logo', (string) cfg('app.logo', ''));
    $flashes = take_flash();
    $theme = $user ? valid_choice((string) setting('theme_user_' . (int) $user['id'], 'current'), ['current', 'ruby', 'indigo', 'copper'], 'current') : 'current';
    $notificationSummary = $user ? notification_header_summary((int) $user['id'], $user) : ['unread' => 0, 'latest' => 0];
    $notificationCount = $notificationSummary['unread'];
    $layoutPage = 'dashboard';
    $layoutAdmin = false;
    $layoutCss = '';
    try {
        if (function_exists('admin_layout_design_page_key')
            && function_exists('admin_layout_design_is_admin')
            && function_exists('admin_layout_design_css_for_page')) {
            $layoutPage = admin_layout_design_page_key($page ?? ($_GET['page'] ?? 'dashboard'));
            $layoutAdmin = $user !== null && admin_layout_design_is_admin($user);
            $layoutCss = admin_layout_design_css_for_page($layoutPage);
        }
    } catch (Throwable $exception) {
        app_error_log('page layout enhancement skipped: ' . $exception->getMessage());
        $layoutPage = 'dashboard';
        $layoutAdmin = false;
        $layoutCss = '';
    }
    $bodyPageClass = ($page ?? '') === 'organization' ? 'page-organization' : '';
    $themeMode = app_theme_mode();
    echo '<!doctype html><html lang="fa" dir="rtl" data-theme="' . e($theme) . '" data-theme-mode="' . e($themeMode) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="' . ($themeMode === 'dark' ? '#101915' : '#174f43') . '"><title>' . e($title . ' | ' . $appName) . '</title><link rel="stylesheet" href="assets/style.css?v=3.4.46">' . ($layoutAdmin ? '<link rel="stylesheet" href="assets/admin-layout-design.css?v=9">' : '') . ($layoutCss !== '' ? '<style id="admin-page-layout-rules">' . $layoutCss . '</style>' : '') . '<link rel="stylesheet" href="assets/theme-mode.css?v=3"></head><body class="' . e($bodyPageClass) . '" data-theme-mode="' . e($themeMode) . '" data-layout-page="' . e($layoutPage) . '" data-layout-design-admin="' . ($layoutAdmin ? '1' : '0') . '"><div class="app-shell"><header class="topbar"><div class="topbar-inner"><a class="brand" href="index.php"><span class="brand-mark">' . ($logo ? '<img src="' . e($logo) . '" alt="' . e($appName) . '">' : '<span class="brand-glyph">پ</span>') . '</span><span><strong>' . e($appName) . '</strong><small>مرکز خدمات و پشتیبانی</small></span></a>' . (!$user ? app_theme_toggle_markup() : '');
    if ($user) {
        $menuIcons = [
            'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'ticket' => '<path d="M3 9a3 3 0 0 0 0 6v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/>',
            'disc' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>',
            'traffic' => '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/>',
            'food' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
            'support' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/>',
            'reports' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        ];
        $menuIcon = static function (string $name) use ($menuIcons): string {
            return '<svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($menuIcons[$name] ?? '') . '</svg>';
        };
        $menuLink = static function (string $href, string $label, string $icon) use ($menuIcon): string {
            return '<a href="' . e($href) . '">' . $menuIcon($icon) . '<span>' . e($label) . '</span></a>';
        };
        $submenuLink = static function (string $href, string $label): string {
            return '<li><a href="' . e($href) . '">' . e($label) . '</a></li>';
        };
        $submenu = static function (string $id, string $label, string $icon, array $links) use ($menuIcon): string {
            if (!$links) {
                return '';
            }
            return '<li class="has-submenu"><details class="nav-dropdown"><summary aria-controls="' . e($id) . '">' . $menuIcon($icon) . '<span>' . e($label) . '</span><span class="arrow" aria-hidden="true"></span></summary><ul class="submenu" id="' . e($id) . '">' . implode('', $links) . '</ul></details></li>';
        };
        $supportLinks = [
            $submenuLink('index.php?page=services', 'خدمات'),
            $submenuLink('index.php?page=knowledge', 'دانش‌نامه'),
        ];
        if (user_can($user, 'queue.view')) {
            $supportLinks[] = $submenuLink('index.php?page=queue', 'صف کاری');
        }
        if (user_can($user, 'assets.view')) {
            $supportLinks[] = $submenuLink('index.php?page=assets', 'شناسنامه‌های فنی');
        }
        // ۱.۳۷.۷ — «تغییر و مشکل» فقط برای کارمندان: پیش‌تر این آیتم در منوی کاربران عادی هم
        // دیده می‌شد، ولی خود صفحه با require_staff() رد می‌کرد.
        if (user_can($user, 'governance.view') && is_staff_role((string) $user['role'])) {
            $supportLinks[] = $submenuLink('governance.php', 'تغییر و مشکل');
        }
        if (user_can($user, 'supervisor.panel')) {
            $supportLinks[] = $submenuLink('index.php?page=supervisor', 'پنل سوپروایزر');
        }
        if (user_can($user, 'ola.view')) {
            $supportLinks[] = $submenuLink('ola.php', 'OLA');
        }
        $reportLinks = [];
        if (user_can($user, 'reports.view')) {
            $reportLinks[] = $submenuLink('index.php?page=reports', 'گزارش درخواست‌ها');
        }
        if (user_can($user, 'analytics.view')) {
            $reportLinks[] = $submenuLink('index.php?page=analytics', 'چارت‌ها');
        }
        if (user_can_any($user, ['logs.activity', 'audit.view', 'logs.view'])) {
            $reportLinks[] = $submenuLink('index.php?page=activity-log', 'لاگ سامانه');
        }
        if (user_can($user, 'system.health')) {
            $reportLinks[] = $submenuLink('index.php?page=system-health', 'سلامت سامانه');
        }
        $adminLinks = [];
        if (user_can($user, 'org.view')) {
            $adminLinks[] = $submenuLink('index.php?page=organization', 'سازمان');
        }
        if (user_can_any($user, ['settings.general', 'settings.domain', 'settings.users'])) {
            $adminLinks[] = $submenuLink('index.php?page=settings', 'تنظیمات');
        }
        if (user_can($user, 'backup.view')) {
            $adminLinks[] = $submenuLink('index.php?page=backup', 'به‌روزرسانی و پشتیبان‌گیری');
        }
        if (user_can($user, 'chatbot.manage')) {
            $adminLinks[] = $submenuLink('index.php?page=chatbot-admin', 'مدیریت بات سخنگو');
        }
        $menuItems = [
            '<li>' . $menuLink('index.php', 'داشبورد', 'home') . '</li>',
        ];
        if (user_can($user, 'ticket.create')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=new-ticket', 'درخواست', 'ticket') . '</li>';
        }
        if (user_can($user, 'cddvd.view')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=cd-dvd', 'کنترل CD/DVD', 'disc') . '</li>';
        }
        if (user_can($user, 'traffic.view')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=traffic-control', 'کنترل تردد', 'traffic') . '</li>';
        }
        if (user_can($user, 'foodorder.self')) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=food-order', 'سفارش غذا', 'food') . '</li>';
        }
        if (food_ticket_is_allowed($user)) {
            $menuItems[] = '<li>' . $menuLink('index.php?page=food-ticket', 'سامانه مدیریت غذا', 'food') . '</li>';
        }
        $menuItems[] = $submenu('menu-support', 'پشتیبانی', 'support', $supportLinks);
        $menuItems[] = $submenu('menu-reports', 'گزارش‌ها', 'reports', $reportLinks);
        $menuItems[] = $submenu('menu-management', 'مدیریت', 'settings', $adminLinks);
        $menuItems[] = '<li>' . $menuLink('index.php?page=help', 'راهنما', 'support') . '</li>';
        if (user_can($user, 'notif.view')) {
    $menuItems[] = '<li class="notification-item"><a class="notification-link" href="index.php?page=notifications">' . $menuIcon('bell') . '<span>اعلان‌ها</span>' . ($notificationCount > 0 ? '<b>' . (int) $notificationCount . '</b>' : '') . '</a></li>';
        }
        $menuItems[] = '<li class="logout"><form method="post" class="logout-form">' . csrf_field() . '<input type="hidden" name="action" value="logout"><button class="logout-link" type="submit">' . $menuIcon('logout') . '<span>خروج</span></button></form></li>';
        echo '<div class="header-tools"><form class="global-search header-search" method="get"><input type="hidden" name="page" value="search"><input name="q" placeholder="جست‌وجو در صفحه‌ها، درخواست، دارایی یا دانش‌نامه"></form>' . app_theme_toggle_markup() . ($layoutAdmin ? '<button class="layout-design-launch" type="button" data-layout-design-launch data-layout-page-title="' . e(admin_layout_design_page_label($layoutPage)) . '" aria-pressed="false">🎨 طراحی همین صفحه</button>' : '') . '<a class="profile-shortcut" href="index.php?page=profile#appearance">🎨 انتخاب تم</a><a class="profile-shortcut" href="index.php?page=profile">پروفایل: ' . e((string) $user['username']) . '</a></div>';
        echo '<nav class="navbar" aria-label="ناوبری اصلی"><ul class="menu">' . implode('', $menuItems) . '</ul></nav>';
    }
      echo '</div></header><div class="content-wrap">';
      echo '<div class="ambient-orbits" aria-hidden="true"><span class="orbit orbit-reusable orbit-reusable--top orbit-reusable--lg"></span><span class="orbit orbit-reusable orbit-reusable--ring orbit-reusable--md"></span><span class="orbit orbit-reusable orbit-reusable--bottom orbit-reusable--xl"></span><span class="orbit orbit-reusable orbit-reusable--side orbit-reusable--sm"></span><span class="orbit orbit-reusable orbit-reusable--corner orbit-reusable--md"></span></div>';
     if ($user) {
         echo '<div id="notification-toasts" class="notification-toasts" data-cursor="' . (int) $notificationSummary['latest'] . '" aria-live="polite"></div>';
     }
     foreach ($flashes as $flash) {
         echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
     }
     // توکن CSRF را پیش از بستن session بساز تا فرم‌های همین صفحه معتبر بمانند.
     csrf_token();
     // آزادسازی قفل فایل session پس از رندر هدر تا درخواست‌های موازی (پولینگ اعلان‌ها و
     // بارگذاری تصاویر/فونت) پشت سر هم بلاک نشوند. فقط خواندن session بعد از این ممکن است.
     if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
         @session_write_close();
     }
 }

/**
 * صفحه‌هایی که لیست‌شان در سمت سرور محدود یا صفحه‌بندی شده است، مرتب‌ساز موقت را ندارند
 * تا فقط بخشی از داده مرتب نشود. پس از اضافه‌شدن «کل نتایج فیلتر» این فهرست خالی می‌شود.
 */
function list_sort_page_off(): bool
{
    return in_array((string) ($_GET['page'] ?? ''), ['logs', 'activity-log', 'traffic-control', 'domain-scan'], true);
}

function inspection_food_reminder_markup(?array $user): string
{
    if (!$user || setting('inspection_food_absence_alarm_enabled', '1') === '0') {
        return '';
    }
    $role = (string) ($user['role'] ?? '');
    $isInspectionUser = in_array($role, ['inspector', 'guard'], true)
        || (function_exists('user_is_key_role') && user_is_key_role($user, 'inspection'));
    if (!$isInspectionUser) {
        return '';
    }

    $now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
    $todayReminder = $now->setTime(11, 0, 0);
    $dueNow = $now >= $todayReminder;
    $nextReminder = $dueNow ? $todayReminder->modify('+1 day') : $todayReminder;
    $name = trim((string) ($user['full_name'] ?? ''));
    if ($name === '') {
        $name = (string) ($user['username'] ?? '');
    }
    return '<div class="inspection-food-reminder" id="inspection-food-reminder" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="inspection-food-reminder-title" data-user-id="' . (int) ($user['id'] ?? 0) . '" data-timezone="' . e(date_default_timezone_get()) . '" data-server-now="' . $now->getTimestamp() . '" data-today-reminder-at="' . $todayReminder->getTimestamp() . '" data-next-reminder-at="' . $nextReminder->getTimestamp() . '" data-due-now="' . ($dueNow ? '1' : '0') . '"><section class="inspection-food-reminder-card"><div class="inspection-food-reminder-icon" aria-hidden="true">!</div><p class="inspection-food-reminder-heading">توجه</p><h2 id="inspection-food-reminder-title">آقای «' . e($name) . '» لطفا استعلام غایبین گروه های غذایی رو انجام بدید برای ثبت در گروه غذایی.</h2><button type="button" class="button inspection-food-reminder-dismiss">متوجه شدم</button></section></div>';
}

function render_footer(): void
{
    $appName = setting('app_name', (string) cfg('app.name', 'سامانه درخواست پشتیبانی'));
    echo '<script nonce="' . e(csp_nonce()) . '">window.ITSM_LIST_SORT_OFF = ' . (list_sort_page_off() ? 'true' : 'false') . '; window.ITSM_CSRF = "' . e(csrf_token()) . '";</script><script defer src="assets/list-sort.js?v=2"></script><script defer src="assets/server-sort.js?v=1"></script>';
    $layoutUser = current_user();
    if ($layoutUser && function_exists('admin_layout_design_is_admin') && admin_layout_design_is_admin($layoutUser)) {
        echo '<script defer src="assets/admin-layout-design.js?v=10"></script>';
    }
    echo '</div><footer class="footer">' . e($appName) . ' • نسخه سازمانی فارسی • سازنده سامانه: Ali.r.Rahimi • نسخهٔ سامانه ' . e(app_version()) . '</footer></div><script nonce="' . e(csp_nonce()) . '">window.ITSM_HOLIDAYS = ';
    try {
        $__h = db()->query("SELECT holiday_date, title FROM holidays WHERE is_active = 1")->fetchAll();
        $__out = [];
        foreach ($__h as $__row) {
            $g = (string) $__row["holiday_date"];
            $parts = explode("-", substr($g, 0, 10));
            if (count($parts) === 3) {
                [$gy, $gm, $gd] = [(int) $parts[0], (int) $parts[1], (int) $parts[2]];
                [$jy, $jm, $jd] = gregorian_to_jalali($gy, $gm, $gd);
                $__out[] = ["date" => $g, "jalali" => sprintf("%04d/%02d/%02d", $jy, $jm, $jd), "title" => (string) $__row["title"]];
            }
        }
        echo json_encode($__out, JSON_UNESCAPED_UNICODE);
    } catch (Throwable) {
        echo "[]";
    }
    $inspectionReminder = inspection_food_reminder_markup($layoutUser);
    echo ';</script><script src="assets/jalali-calendar.js?v=3.4.7"></script><script src="assets/app.js?v=3.4.27&theme-mode=2"></script>' . particle_effect_script_tag() . triangle_effect_script_tag() . chatbot_render_widget($layoutUser) . $inspectionReminder;
    if ($inspectionReminder !== '') {
        echo '<script defer src="assets/inspection-food-reminder.js?v=1"></script>';
    }
    echo '</body></html>';
}

if (($_GET['action'] ?? '') === 'logout') {
    redirect('index.php');
}


// ═══ ناحیهٔ ۱: خروج از حساب (قبل از هر چیز) ═══
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    require_csrf();
    if (function_exists('activity_log')) {
        activity_log(['action_code' => 'logout', 'module' => 'auth']);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    // توکن CSRF و کوکی آن را هم پاک کن تا پس از خروج، توکن قدیمی معتبر نماند.
    setcookie('itsm_csrf', '', time() - 42000, '/');
    unset($_COOKIE['itsm_csrf']);
    session_destroy();
    redirect('index.php?page=login');
}

$user = current_user();

// ═══ ناحیهٔ ۲: تعیین صفحهٔ جاری و پیش‌شرط‌های آن ═══
$page = (string) ($_GET['page'] ?? ($user ? 'dashboard' : 'login'));
// ۱.۵۰: کاربر محلی/بازرسی به سفارش غذا هدایت می‌شود؛ رمز پیش‌فرض باید در اولین ورود عوض شود.
if ($user && !isset($_GET['page'])) {
    if ((int) ($user['must_change_password'] ?? 0) === 1) {
        $page = 'change-password';
    } elseif (lac_is_local_role($user)) {
        $page = 'food-order';
    }
}
if ($user && (int) ($user['must_change_password'] ?? 0) === 1 && !in_array($page, ['change-password'], true)) {
    redirect('index.php?page=change-password');
}
if (isset($_GET['layout_design_api'])) {
    if (function_exists('admin_layout_design_api_handle')) {
        admin_layout_design_api_handle($user);
    }
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('ابزار طراحی چیدمان در این نصب فعال نیست.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && in_array($page, ['organization', 'settings'], true)) {
    register_shutdown_function(static function (): void {
        try {
            org_sync_position_roles();
        } catch (Throwable $ignored) {
        }
    });
}

if ($user && function_exists('activity_log_view')) {
    activity_log_view($page);
}

if ($page === 'food-ticket' && isset($_GET['food_api'])) {
    // Rate limit جدا برای خواندن و نوشتن: پنل بازخوانی خودکار دارد (پایش، سلامت، سفارش‌ها) و نباید
    // ذخیرهٔ تنظیمات یا «آزمون کامل» را مسدود کند. خواندن‌ها: ۳۰۰ در دقیقه، نوشتن/آزمون‌ها: ۶۰ در دقیقه.
    if ($user) {
        $foodIsWrite = ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET';
        check_api_rate_limit(
            'food_ticket_api_' . ($foodIsWrite ? 'w_' : 'r_') . $user['id'],
            $foodIsWrite ? 60 : 300,
            60
        );
    }
    food_ticket_api_handle((string) $_GET['food_api'], $user ?? []);
}

if ($page === 'food-order' && isset($_GET['food_api'])) {
    if ($user) {
        $foodOrderIsWrite = ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET';
        check_api_rate_limit(
            'food_order_api_' . ($foodOrderIsWrite ? 'w_' : 'r_') . (int) $user['id'],
            $foodOrderIsWrite ? 180 : 300,
            60
        );
    }
    food_order_api_handle((string) $_GET['food_api'], $user ?? []);
}

if ($page === 'traffic-control' && isset($_GET['traffic_api'])) {
    // خواندن‌ها: ۳۰۰ در دقیقه، نوشتن‌ها: ۶۰ در دقیقه (برای هر کاربر)
    if ($user) {
        $trafficIsWrite = ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET';
        check_api_rate_limit(
            'traffic_api_' . ($trafficIsWrite ? 'w_' : 'r_') . $user['id'],
            $trafficIsWrite ? 60 : 300,
            60
        );
    }
    traffic_api_handle((string) $_GET['traffic_api'], $user ?? []);
}

if (($_GET['action'] ?? '') === 'profile_photo') {
    $photoUser = require_login();
    $stored = basename((string) ($photoUser['profile_photo'] ?? ''));
    if ($stored === '' || !preg_match('/^profile-[a-f0-9]{36}\.(png|jpg)$/', $stored)) {
        http_response_code(404);
        exit('عکس پروفایل پیدا نشد.');
    }
    $candidates = [
        APP_ROOT . '/storage/profile/' . $stored,
    ];
    $photoPath = null;
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $photoPath = $candidate;
            break;
        }
    }
    if ($photoPath === null) {
        http_response_code(404);
        exit('عکس پروفایل روی سرور موجود نیست.');
    }
    $mime = (string) (mime_content_type($photoPath) ?: 'application/octet-stream');
    if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
        http_response_code(404);
        exit('نوع عکس پروفایل مجاز نیست.');
    }
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=3600');
    header('X-Content-Type-Options: nosniff');
    readfile($photoPath);
    exit;
}

if (($_GET['action'] ?? '') === 'notification_feed') {
    if (!$user) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Rate limit: 60 requests per minute per user
    check_api_rate_limit('notification_feed_' . $user['id'], 60, 60);
    // این اپیندپوینت هر ۳۰ ثانیه صدا زده می‌شود؛ قفل session را فوراً آزاد می‌کنیم
    // تا بقیهٔ درخواست‌های کاربر پشت آن بلاک نشوند.
    if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
        @session_write_close();
    }
    $afterId = max(0, (int) ($_GET['after'] ?? 0));
    $feedQuery = db()->prepare('SELECT n.id, n.ticket_id, n.notification_type, n.title, n.body, n.created_at FROM notifications n WHERE n.user_id = ? AND n.id > ?' . notification_visibility_sql((int) $user['id'], $user, 'n') . ' ORDER BY n.id ASC LIMIT 20');
    $feedQuery->execute([(int) $user['id'], $afterId]);
    $items = array_map(static function (array $item): array {
        return [
            'id' => (int) $item['id'],
            'ticket_id' => $item['ticket_id'] !== null ? (int) $item['ticket_id'] : null,
            'notification_type' => (string) ($item['notification_type'] ?? ''),
            'title' => (string) $item['title'],
            'body' => (string) $item['body'],
            'href' => notification_target_url($item),
        ];
    }, $feedQuery->fetchAll());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_GET['action'] ?? '') === 'download_backup') {
    require_primary_admin();
    $filePath = backup_download_path((string) ($_GET['file'] ?? ''));
    if (!$filePath) {
        http_response_code(404);
        exit('فایل پشتیبان پیدا نشد.');
    }
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($extension === 'sql' ? 'application/sql' : 'application/octet-stream'));
    header('Content-Length: ' . (string) filesize($filePath));
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode(basename($filePath)));
    readfile($filePath);
    exit;
}

if (($_GET['action'] ?? '') === 'export_activity_logs') {
    require_permission('logs.activity_export');
    activity_logs_ensure();
    [$where, $params] = activity_log_filters();
    $exportOrder = log_sort_order(['time' => 'l.created_at', 'user' => 'l.full_name', 'event' => 'COALESCE(l.action_label, l.action_code)', 'module' => 'l.module', 'ip' => 'l.ip_address', 'meta' => 'l.meta_json'], (string) ($_GET['sort_a'] ?? ''), (string) ($_GET['dir_a'] ?? ''), ' ORDER BY l.created_at DESC, l.id DESC');
    $query = db()->prepare('SELECT * FROM activity_logs l' . $where . $exportOrder . ' LIMIT 20000');
    $query->execute($params);
    $rows = [];
    foreach ($query->fetchAll() as $row) {
        $rows[] = [
            persian_date($row['created_at']),
            $row['full_name'] !== '' ? $row['full_name'] : ($row['username'] ?: '-'),
            $row['username'] ?: '-',
            permission_roles()[(string) ($row['role'] ?? '')] ?? (string) ($row['role'] ?? '-'),
            $row['action_label'] ?: $row['action_code'],
            $row['action_code'],
            $row['module'] ?: '-',
            $row['target_type'] ? $row['target_type'] . ($row['target_id'] ? ' #' . $row['target_id'] : '') : '-',
            $row['ip_address'] ?: '-',
            $row['computer_name'] ?: '-',
            $row['meta_json'] ?: '',
        ];
    }
    activity_log(['action_code' => 'activity_logs_exported', 'action_label' => 'خروجی Excel فعالیت کاربران', 'module' => 'activity-log', 'meta' => ['rows' => count($rows)]]);
    $excelFrom = jalali_input_to_gregorian((string) ($_GET['from'] ?? ''));
    $excelTo = jalali_input_to_gregorian((string) ($_GET['to'] ?? ''), true);
    excel_download('activity-log-' . date('Y-m-d') . '.xlsx', 'گزارش فعالیت کاربران', ['زمان', 'کاربر', 'نام کاربری', 'نقش', 'عملیات', 'کد عملیات', 'صفحه', 'هدف', 'آی‌پی', 'نام کامپیوتر', 'جزئیات'], $rows, ['from' => $excelFrom !== null ? substr($excelFrom, 0, 10) : '', 'to' => $excelTo !== null ? substr($excelTo, 0, 10) : '']);
}

if (($_GET['action'] ?? '') === 'export_report') {
    $reportUser = require_permission('reports.view');
    $reportMissingColumns = db_missing_columns('tickets', ['subject', 'description', 'status', 'priority', 'requester_id', 'department_id', 'service_group', 'created_at', 'updated_at', 'assigned_to', 'ticket_type', 'assigned_at', 'first_response_at', 'resolved_at', 'closed_at', 'due_at', 'sla_minutes']);
    if ($reportMissingColumns) {
        http_response_code(503);
        exit('ساختار پایگاه‌داده کامل نیست. ابتدا upgrade-1.2-itsm.sql و upgrade-1.8-routing.sql را اجرا کنید. ستون‌های ناقص: ' . e(implode(', ', $reportMissingColumns)));
    }
    [$where, $params] = report_query_filters($reportUser);
    $query = db()->prepare('SELECT t.id, t.subject, t.ticket_type, t.status, t.priority, t.created_at, t.updated_at, t.assigned_at, t.first_response_at, t.resolved_at, t.closed_at, t.due_at, t.sla_minutes, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $where . ' ORDER BY t.created_at DESC LIMIT 5001');
    $query->execute($params);
    $rows = [];
    while ($row = $query->fetch()) {
        $rows[] = [ticket_number((int) $row['id']), $row['subject'], ticket_type_label($row['ticket_type']), $row['department_name'], $row['requester_name'], $row['assignee_name'], status_label($row['status']), priority_label($row['priority']), persian_date($row['created_at']), persian_date($row['updated_at']), $row['assigned_at'] ? persian_date($row['assigned_at']) : '', $row['first_response_at'] ? persian_date($row['first_response_at']) : '', $row['resolved_at'] ? persian_date($row['resolved_at']) : '', $row['closed_at'] ? persian_date($row['closed_at']) : '', $row['due_at'] ? persian_date($row['due_at']) : '', $row['sla_minutes']];
    }
    $exportTruncated = count($rows) > 5000;
    if ($exportTruncated) {
        $rows = array_slice($rows, 0, 5000);
    }
    $excelFrom = jalali_input_to_gregorian((string) ($_GET['from'] ?? ''));
    $excelTo = jalali_input_to_gregorian((string) ($_GET['to'] ?? ''), true);
    excel_download('ticket-report-' . date('Y-m-d') . '.xlsx', $exportTruncated ? 'گزارش درخواست‌ها (۵۰۰۰ ردیف اول؛ فیلتر را محدودتر کنید)' : 'گزارش کامل درخواست‌ها', ['شماره', 'عنوان', 'نوع', 'واحد', 'ثبت‌کننده', 'کارشناس', 'وضعیت', 'اولویت', 'ثبت', 'آخرین تغییر', 'ارجاع', 'اولین پاسخ', 'حل', 'بسته‌شدن', 'مهلت SLA', 'دقیقه SLA'], $rows, ['from' => $excelFrom !== null ? substr($excelFrom, 0, 10) : '', 'to' => $excelTo !== null ? substr($excelTo, 0, 10) : '']);
}

// خروجی XLSX از نمای مرتب‌شدهٔ لیست در مرورگر (فقط داده‌های همان صفحه؛ ترتیب از کاربر می‌آید)
if (($_GET['action'] ?? '') === 'export_view_xlsx') {
    require_login();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('روش درخواست مجاز نیست.');
    }
    require_csrf();
    $payload = json_decode((string) file_get_contents('php://input', false, null, 0, 2_000_000), true);
    if (!is_array($payload) || !is_array($payload['headers'] ?? null) || !is_array($payload['rows'] ?? null)) {
        http_response_code(400);
        exit('داده‌های خروجی معتبر نیست.');
    }
    $clip = static fn ($v, int $max = 2000): string => mb_substr(is_scalar($v) ? (string) $v : '', 0, $max, 'UTF-8');
    $headers = array_slice(array_map(static fn ($h) => $clip($h, 200), $payload['headers']), 0, 60);
    $columnCount = max(1, count($headers));
    $rows = array_slice($payload['rows'], 0, 20000);
    $sheetRows = [];
    $title = $clip($payload['title'] ?? 'گزارش', 200);
    $sheetRows[] = [['v' => $title, 's' => 'title']];
    $sheetRows[] = array_map(static fn (string $h): array => ['v' => $h, 's' => 'header'], $headers);
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $line = [];
        foreach (array_slice(array_values($row), 0, $columnCount) as $value) {
            $line[] = ['v' => $clip($value), 's' => 'body'];
        }
        $sheetRows[] = $line;
    }
    $bytes = xlsx_build([[
        'name' => 'گزارش',
        'rows' => $sheetRows,
        'merges' => ['A1:' . xlsx_col_name($columnCount - 1) . '1'],
        'widths' => array_fill(0, $columnCount, 20),
    ]]);
    xlsx_send($clip($payload['filename'] ?? 'report', 120) ?: 'report', $bytes);
}

if (($_GET['action'] ?? '') === 'export_assets') {
    $assetUser = require_permission('assets.view');
    asset_profile_ensure_schema();
    [$where, $params] = asset_scope($assetUser);
    $query = db()->prepare('SELECT a.*, d.name AS department_name, u.full_name AS owner_name FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN users u ON u.id = a.owner_user_id ' . $where . ' ORDER BY a.hostname, a.asset_tag');
    $query->execute($params);
    $profileColumns = asset_profile_export_columns();
    $headers = ['شناسه سیستم', 'نام سیستم', 'شماره سریال', 'نوع رایانه', 'معاونت', 'مالک', 'منبع استخراج', 'وضعیت شبکه', 'آخرین برداشت'];
    foreach ($profileColumns as $column) {
        $headers[] = $column['label'] . ' (' . $column['section'] . ')';
    }
    $rows = [];
    $assetRows = $query->fetchAll();
    $profileMap = asset_profile_map(array_column($assetRows, 'id'));
    foreach ($assetRows as $row) {
        $profile = asset_profile_fill_from_asset($profileMap[(int) $row['id']] ?? [], $row);
        $online = (int) ($profile['ad_online'] ?? 0) === 1;
        $wasScanned = (string) ($profile['ad_scanned_at'] ?? '') !== '';
        $netStatus = $online ? 'آنلاین' : ($wasScanned ? 'آفلاین' : 'پینگ نشده');
        $sourceLabels = ['server_inventory' => 'استخراج سرور پنل', 'agent' => 'Agent کلاینت', 'domain' => 'اسکن/دامنه', 'manual' => 'دستی'];
        $sourceLabel = $sourceLabels[(string) $row['source']] ?? (string) $row['source'];
        $exportRow = [
            asset_tag_label((string) $row['asset_tag'], (string) $row['hostname']),
            asset_display_name((string) $row['hostname']),
            (string) $row['serial_number'],
            (string) $row['computer_type'],
            (string) ($row['department_name'] ?? ''),
            (string) ($row['owner_name'] ?? ''),
            $sourceLabel,
            $netStatus,
            $row['last_inventory_at'] ? persian_date((string) $row['last_inventory_at']) : '',
        ];
        foreach ($profileColumns as $column) {
            $exportRow[] = asset_profile_export_value($column, $profile[$column['key']] ?? null);
        }
        $rows[] = $exportRow;
    }
    excel_download('asset-full-inventory-' . date('Y-m-d') . '.xlsx', 'گزارش کامل شناسنامه‌های فنی سیستم‌ها', $headers, $rows, ['from' => 'بدون فیلتر تاریخی', 'to' => 'بدون فیلتر تاریخی']);
}

if (($_GET['action'] ?? '') === 'export_cd_dvd') {
    cd_dvd_export(require_permission('cddvd.export'));
}

if (($_GET['action'] ?? '') === 'export_traffic_report') {
    require_permission('traffic.view');
    traffic_export_report();
}

if (($_GET['action'] ?? '') === 'download_attachment') {
    $downloadUser = require_login();
    $attachmentId = (int) ($_GET['id'] ?? 0);
    $attachmentQuery = db()->prepare('SELECT a.*, m.ticket_id, m.is_internal FROM ticket_attachments a JOIN ticket_messages m ON m.id = a.message_id WHERE a.id = ? LIMIT 1');
    $attachmentQuery->execute([$attachmentId]);
    $attachment = $attachmentQuery->fetch();
    $attachmentTicket = $attachment ? fetch_ticket((int) $attachment['ticket_id']) : null;
    $canDownloadInternal = is_staff_role($downloadUser['role']);
    if (!$attachment || !$attachmentTicket || !can_view_ticket($attachmentTicket, $downloadUser) || ((int) $attachment['is_internal'] === 1 && !$canDownloadInternal)) {
        http_response_code(404);
        exit('فایل پیدا نشد.');
    }
    $filePath = APP_ROOT . '/storage/uploads/' . basename((string) $attachment['stored_name']);
    if (!is_file($filePath)) {
        http_response_code(404);
        exit('فایل روی سرور موجود نیست.');
    }
    header('Content-Type: ' . ((string) $attachment['mime'] ?: 'application/octet-stream'));
    header('Content-Length: ' . (string) filesize($filePath));
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode((string) $attachment['original_name']));
    readfile($filePath);
    exit;
}

if ($page === 'food-personnel-link') {
    require_once __DIR__ . '/food-personnel-link.php';
    fpl_render_request();
}


// ═══ ناحیهٔ ۴: پردازش همهٔ اکشن‌های POST (74 اکشن، گروه‌بندی با بانر) ═══
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $GLOBALS['activity_post'] = ['action' => $action, 'page' => (string) ($_GET['page'] ?? ''), 'failed' => false];
    register_shutdown_function(static function (): void {
        $pending = $GLOBALS['activity_post'] ?? null;
        if (!$pending || $pending['action'] === '' || $pending['failed'] || !function_exists('activity_log')) {
            return;
        }
        if (!empty($GLOBALS['activity_audited'])) {
            return;
        }
        $isAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        if ($isAjax || str_starts_with((string) ($pending['page'] ?? ''), 'api')) {
            return;
        }
        static $skip = ['login', 'logout'];
        if (in_array($pending['action'], $skip, true)) {
            return;
        }
        activity_log([
            'action_code' => $pending['action'],
            'module' => $pending['page'] !== '' ? $pending['page'] : 'action',
        ]);
    });
    try {

        // ───── گروه اکشن: احراز هویت، پروفایل و تم — ۳ اکشن از ۱.۶۲.۱۸ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/account-actions.php';

        // ───── گروه اکشن: برند فیش غذا — ۱ اکشن از ۱.۶۲.۱۸ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/food-brand-actions.php';
        food_ticket_handle_post($action, $user);
        traffic_handle_post($action, $user);

        // ───── گروه اکشن: آرشیو CD/DVD — ۳ اکشن از ۱.۶۲.۱۸ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/cd-dvd-actions.php';

        // ───── گروه اکشن: پشتیبان‌گیری و به‌روزرسانی سامانه — ۳ اکشن از ۱.۶۲.۲۰ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/backup-actions.php';

        // ───── گروه اکشن: اعلان‌ها — ۲ اکشن از ۱.۶۲.۱۸ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/notification-actions.php';

        // ───── گروه اکشن: تصمیم‌های سرپرست و امتیازدهی — ۴ اکشن از ۱.۶۲.۱۷ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/supervisor-actions.php';


        // ───── گروه اکشن: اموال، دارایی‌ها و اسکن دامین — ۱۰ اکشن از ۱.۶۲.۱۳ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/asset-actions.php';


        // ───── گروه اکشن: چرخهٔ کامل درخواست‌ها — ۷ اکشن از ۱.۶۲.۱۳ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/ticket-actions.php';


        // ───── گروه اکشن: خدمات، دسته‌ها و پایگاه دانش — ۷ اکشن از ۱.۶۲.۱۴ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/service-actions.php';

        

        // ───── گروه اکشن: تقویم و تعطیلات — ۴ اکشن از ۱.۶۲.۱۷ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/holiday-actions.php';


        // ───── گروه اکشن: ارتباط دارایی و عملیات گروهی درخواست‌ها — ۲ اکشن از ۱.۶۲.۱۹ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/bulk-actions.php';


        // ───── گروه اکشن: ساختارهای پایه و کاربران — ۶ اکشن از ۱.۶۲.۱۹ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/user-structure-actions.php';


        // ───── گروه اکشن: ساختار سازمانی (چارت) — ۱۳ اکشن از ۱.۶۲.۱۲ در فایل جدا ─────
        require __DIR__ . '/actions/org-actions.php';


        // ───── گروه اکشن: نقش‌های کلیدی و دسترسی‌ها — ۴ اکشن از ۱.۶۲.۱۶ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/role-actions.php';


        // ───── گروه اکشن: نگهداری لاگ‌ها/LDAP و تنظیمات عمومی — ۵ اکشن از ۱.۶۲.۱۶ در فایل جدا (شرح فارسی در سرآیند همان فایل) ─────
        require __DIR__ . '/actions/settings-actions.php';
    } catch (Throwable $exception) {
        if (isset($GLOBALS['activity_post'])) {
            $GLOBALS['activity_post']['failed'] = true;
        }
        if (db()->inTransaction()) {
            db_rollback('index.php');
        }
        app_error_log('ITSM request failed: ' . $exception->getMessage() . ' | ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        system_log('error', 'request', $exception->getMessage(), ['uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''), 'file' => $exception->getFile(), 'line' => $exception->getLine()]);
        flash('danger', $exception instanceof RuntimeException ? $exception->getMessage() : 'عملیات انجام نشد. گزارش خطا در لاگ سامانه ثبت شد.');
        redirect('index.php?page=' . urlencode($page));
    }
}

if ($page === 'chatbot-api') {
    chatbot_api_handle(current_user());
}

if (!$user) {
    $loginTitle = setting('login_title', 'به سامانه درخواست پشتیبانی خوش آمدید');
    $loginIntro = setting('login_intro', 'برای ثبت و پیگیری درخواست‌های خود وارد شوید.');
    $loginNoteTitle = setting('login_note_title', 'ورود یکپارچه سازمانی');
    $loginNoteText = setting('login_note_text', 'کاربران شبکه با حساب Active Directory خود وارد می‌شوند.');
    $loginAnnouncement = setting('login_announcement', '');
    $loginAnnouncementLines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) setting('login_announcements', '')) ?: [])));
    if (!$loginAnnouncementLines && trim($loginAnnouncement) !== '') {
        $loginAnnouncementLines = [trim($loginAnnouncement)];
    }
    $loginAnnouncementLines = array_slice($loginAnnouncementLines, 0, 5);
    $loginAnnouncementSpeed = max(5, min(60, (int) setting('login_announcement_speed', '18')));
    $loginAnnouncementFontSize = max(11, min(22, (int) setting('login_announcement_font_size', '12')));
    $loginAnnouncementHtml = $loginAnnouncementLines
        ? '<div class="login-announcement" data-announcements="' . e((string) json_encode($loginAnnouncementLines, JSON_UNESCAPED_UNICODE)) . '" data-announcement-speed="' . $loginAnnouncementSpeed . '"><span aria-hidden="true">✦</span><div data-announcement-text style="font-size:' . $loginAnnouncementFontSize . 'px;animation-duration:' . $loginAnnouncementSpeed . 's">' . e($loginAnnouncementLines[0]) . '</div></div>'
        : '';
    $loginLogo = setting('app_login_logo', setting('app_logo', (string) cfg('app.logo', '')));
    render_header('ورود به سامانه');
    echo '<p class="auth-version" style="text-align:center;color:#64748b;font-size:13px;margin:0 0 8px">نسخهٔ سامانه: ' . e(app_version()) . '</p>';
    echo '<main class="auth-layout"><section class="auth-panel">' . ($loginLogo ? '<div class="login-brand-logo"><img src="' . e($loginLogo) . '" alt="لوگوی سامانه"></div>' : '') . '<div class="auth-kicker">' . e(setting('app_name', (string) cfg('app.name', 'مرکز خدمات سازمان'))) . '</div><h1>' . e($loginTitle) . '</h1><p class="muted">' . e($loginIntro) . '</p>' . $loginAnnouncementHtml . '<form method="post" class="auth-form">' . csrf_field() . '<input type="hidden" name="action" value="login"><label>نام کاربری شبکه یا سامانه<input name="username" required autocomplete="username" autofocus placeholder="نام کاربری شما"></label><label>رمز عبور<input type="password" name="password" required autocomplete="current-password" placeholder="رمز عبور"></label><button class="button wide" type="submit">ورود امن</button></form><div class="login-note"><span>◆</span><div><strong>' . e($loginNoteTitle) . '</strong><small>' . e($loginNoteText) . '</small></div></div></section><aside class="auth-art"><div class="art-orb orb-one"></div><div class="art-orb orb-two"></div><div class="art-card"><span class="art-icon">ت</span><strong>' . e(setting('login_art_title', 'پشتیبانی، ساده و شفاف')) . '</strong><p>' . e(setting('login_art_text', 'هر درخواست یک شماره پیگیری دارد و مسیر رسیدگی آن برای شما روشن است.')) . '</p></div></aside></main>';
    render_footer();
    exit;
}

$pagePermissions = [
    'profile' => 'profile.edit',
    'queue' => 'queue.view',
    'reports' => 'reports.view',
    'analytics' => 'analytics.view',
    'supervisor' => 'supervisor.panel',
    'inventory' => 'inventory.view',
    'inventory-diagnostics' => 'inventory.diagnostics',
    'domain-scan' => 'domain.scan',
    'dashboard' => 'dash.view',
    'assets' => 'assets.view',
    'asset' => 'asset.view',
    'asset-history' => 'asset.history',
    'cd-dvd' => 'cddvd.view',
    'cd-dvd-history' => 'cddvd.history',
    'traffic-control' => 'traffic.view',
    'traffic-control-print' => 'traffic.view',
    'knowledge' => 'knowledge.view',
    'backup' => 'backup.view',
    'chatbot-admin' => 'chatbot.manage',
    'organization' => 'org.view',
    'holidays' => 'holidays.view',
    'services' => 'services.view',
    // 'food-ticket' — به‌جای یک کد تکی، با food_ticket_is_allowed() سنجیده می‌شود (پایین‌تر).
];
// ۱.۳۷.۷ — دروازهٔ «سامانه مدیریت غذا» با فهرست کدهای food.* یکی شد: پیش‌تر منو با
// food_ticket_is_allowed() (هر کد food.*) نمایش داده می‌شد ولی صفحه فقط با food.monitor باز
// می‌شد؛ یعنی نقشی که مثلاً فقط food.orders داشت، لینک را می‌دید و به ۴۰۳ می‌خورد.
if ($page === 'food-ticket' && !(function_exists('food_ticket_is_allowed') ? food_ticket_is_allowed($user) : user_can($user, 'food.monitor'))) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه هماهنگ کنید.</p></div><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
    render_footer();
    exit;
}
if (isset($pagePermissions[$page]) && !user_can($user, $pagePermissions[$page])) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه هماهنگ کنید.</p></div><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
    render_footer();
    exit;
}
if ($page === 'settings' && !user_can_any($user, ['settings.general', 'settings.domain', 'settings.users', 'settings.key_roles'])) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به تنظیمات مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه هماهنگ کنید.</p></div><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
    render_footer();
    exit;
}

if ($page === 'change-password') {
    lac_change_password_request();
}
if ($page === 'local-accounts') {
    lac_admin_request();
}
if ($page === 'profile') {
    $profileUser = require_login();
    $profileTheme = valid_choice((string) setting('theme_user_' . (int) $profileUser['id'], 'current'), ['current', 'ruby', 'indigo', 'copper'], 'current');
    $profileFirst = trim((string) ($profileUser['first_name'] ?? ''));
    $profileLast = trim((string) ($profileUser['last_name'] ?? ''));
    if ($profileFirst === '' && $profileLast === '') {
        $parts = preg_split('/\s+/u', trim((string) ($profileUser['full_name'] ?? '')), 2) ?: [];
        $profileFirst = (string) ($parts[0] ?? '');
        $profileLast = (string) ($parts[1] ?? '');
    }
    render_header('پروفایل کاربری', $profileUser);
    $profileInitial = $profileFirst !== '' ? $profileFirst : (string) $profileUser['full_name'];
    $profileInitial = function_exists('mb_substr') ? mb_substr($profileInitial, 0, 1) : substr($profileInitial, 0, 1);
    echo '<section class="page-heading"><div><span class="eyebrow">حساب کاربری</span><h1>پروفایل من</h1><p>اطلاعات تکمیلی شما برای شناسایی و خدمات سازمانی در این بخش نگهداری می‌شود.</p></div><a class="button secondary" href="index.php">بازگشت به داشبورد</a></section><section class="card form-card theme-preference" id="appearance"><h2>ظاهر دلخواه سامانه</h2><p class="muted">انتخاب شما فقط برای حساب خودتان ذخیره می‌شود و در ورودهای بعدی هم فعال می‌ماند؛ چیدمان صفحه‌ها تغییر نمی‌کند.</p><form method="post" class="theme-preference-form">' . csrf_field() . '<input type="hidden" name="action" value="save_theme"><label>تم پیش‌فرض من<select name="theme"><option value="current"' . ($profileTheme === 'current' ? ' selected' : '') . '>تم فعلی · سبز و کرم</option><option value="ruby"' . ($profileTheme === 'ruby' ? ' selected' : '') . '>یاقوتی رسمی</option><option value="indigo"' . ($profileTheme === 'indigo' ? ' selected' : '') . '>لاجوردی مدیریتی</option><option value="copper"' . ($profileTheme === 'copper' ? ' selected' : '') . '>آجری روشن و کرم</option></select></label><button class="button" type="submit">ذخیره و اعمال تم</button></form></section><section class="profile-layout"><div class="card profile-card"><div class="profile-avatar">' . (!empty($profileUser['profile_photo']) ? '<img src="index.php?action=profile_photo" alt="عکس پروفایل">' : '<span>' . e($profileInitial) . '</span>') . '</div><h2>' . e($profileUser['full_name']) . '</h2><p class="muted">' . e($profileUser['username']) . '</p><span class="status in_progress">اطلاعات شخصی</span></div><div class="card form-card profile-form-card"><h2>اطلاعات تکمیلی</h2><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save_profile"><div class="form-grid"><label>نام<input name="first_name" value="' . e($profileFirst) . '" required></label><label>نام خانوادگی<input name="last_name" value="' . e($profileLast) . '" required></label><label>کد ملی<input name="national_code" value="' . e($profileUser['national_code'] ?? '') . '" inputmode="numeric" maxlength="10" pattern="[0-9۰-۹]{10}" placeholder="۱۰ رقم معتبر"></label><label>عکس پروفایل<input type="file" name="profile_photo" accept="image/png,image/jpeg"><small>PNG یا JPG، حداکثر ۲ مگابایت؛ عکس در فضای محافظت‌شده ذخیره می‌شود.</small></label></div><div class="actions"><button class="button" type="submit">ذخیره پروفایل</button></div></form></div></section>';
    render_footer();
    exit;
}

if ($page === 'governance') {
    redirect('governance.php');
}

if ($page === 'food-ticket') {
    food_ticket_render_page($user ?? []);
    exit;
}

if ($page === 'food-order' && $user && !user_can($user, 'foodorder.self')) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه تماس بگیرید.</p></div></section>';
    render_footer();
    exit;
}
if ($page === 'food-order') {
    food_order_render_page($user ?? []);
}

if ($page === 'cd-dvd') {
    cd_dvd_render_page($user);
}

if ($page === 'cd-dvd-history') {
    cd_dvd_render_history($user);
}

if ($page === 'traffic-control') {
    traffic_render_page($user);
}
if ($page === 'traffic-control-print') {
    traffic_render_print($user, (int) ($_GET['id'] ?? 0));
}

if ($page === 'services') {
    $serviceUser = require_login();
    $isServiceAdmin = is_admin_role((string) $serviceUser['role']) || (int) ($serviceUser['is_primary_admin'] ?? 0) === 1;
    $serviceGroupFilter = valid_choice((string) ($_GET['group'] ?? ''), ['', 'it', 'support'], '');
    $listSql = 'SELECT s.*, d.name AS department_name, c.name AS category_name, hu.name AS handling_unit_name, COUNT(f.id) AS field_count FROM service_catalog s LEFT JOIN departments d ON d.id = s.department_id LEFT JOIN categories c ON c.id = s.category_id LEFT JOIN handling_units hu ON hu.id = s.handling_unit_id LEFT JOIN service_catalog_fields f ON f.service_id = s.id WHERE s.is_active = 1';
    $listParams = [];
    if ($serviceGroupFilter !== '') {
        $listSql .= ' AND s.service_group = ?';
        $listParams[] = $serviceGroupFilter;
    }
    $listSql .= ' GROUP BY s.id, d.name, c.name, hu.name ORDER BY s.service_group, s.name';
    $listQuery = db()->prepare($listSql);
    $listQuery->execute($listParams);
    $services = $listQuery->fetchAll();
    $serviceFieldsById = [];
    if ($services) {
        $serviceIds = array_map(static fn (array $service): int => (int) $service['id'], $services);
        $fieldQuery = db()->prepare('SELECT * FROM service_catalog_fields WHERE service_id IN (' . implode(',', array_fill(0, count($serviceIds), '?')) . ') ORDER BY service_id, sort_order, id');
        $fieldQuery->execute($serviceIds);
        foreach ($fieldQuery->fetchAll() as $field) {
            $serviceFieldsById[(int) $field['service_id']][] = $field;
        }
    }
    $categories = db()->query('SELECT * FROM categories ORDER BY is_active DESC, service_group, name')->fetchAll();
    $handlingUnits = db()->query('SELECT id, code, name FROM handling_units WHERE is_active = 1 ORDER BY code')->fetchAll();
    $serviceDepartments = db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
    $categoryOptions = static function (int $selected) use ($categories): string {
        $html = '<option value="">بدون دسته</option>';
        foreach ($categories as $category) {
            if ((int) $category['is_active'] !== 1) {
                continue;
            }
            $html .= '<option value="' . (int) $category['id'] . '" ' . ($selected === (int) $category['id'] ? 'selected' : '') . '>' . e($category['name']) . '</option>';
        }
        return $html;
    };
    $handlingOptions = static function (int $selected) use ($handlingUnits): string {
        $html = '';
        foreach ($handlingUnits as $unit) {
            $html .= '<option value="' . (int) $unit['id'] . '" ' . ($selected === (int) $unit['id'] ? 'selected' : '') . '>' . e($unit['name']) . '</option>';
        }
        return $html;
    };
    $departmentOptions = static function (int $selected) use ($serviceDepartments): string {
        $html = '<option value="">سراسری</option>';
        foreach ($serviceDepartments as $department) {
            $html .= '<option value="' . (int) $department['id'] . '" ' . ($selected === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        return $html;
    };
    $ticketTypeOptions = static function (string $selected): string {
        $types = ['incident' => 'رخداد', 'request' => 'درخواست خدمت', 'problem' => 'مشکل ریشه‌ای', 'change' => 'تغییر'];
        $html = '';
        foreach ($types as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($selected === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        return $html;
    };
    $priorityOptions = static function (string $selected): string {
        $items = ['normal' => 'عادی', 'urgent' => 'فوری', 'critical' => 'حیاتی'];
        $html = '';
        foreach ($items as $value => $label) {
            $html .= '<option value="' . $value . '" ' . ($selected === $value ? 'selected' : '') . '>' . $label . '</option>';
        }
        return $html;
    };
    // فرم مشترک ساخت/ویرایش خدمت (بدون تگ form، تا در مودال و فرم ایجاد استفاده شود)
    $serviceFormFields = static function (array $service) use ($categoryOptions, $handlingOptions, $departmentOptions, $ticketTypeOptions, $priorityOptions): string {
        $group = (string) ($service['service_group'] ?? 'it');
        return '<div class="form-grid">'
            . '<label>نام خدمت<input name="service_name" required value="' . e((string) ($service['name'] ?? '')) . '" placeholder="مثلاً دسترسی به سامانه داخلی"></label>'
            . '<label>کد خدمت انگلیسی<input name="service_code" required value="' . e((string) ($service['code'] ?? '')) . '" placeholder="IT-ACCESS"></label>'
            . '<label>حوزه خدمت<select name="service_group"><option value="it" ' . ($group === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ($group === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label>'
            . '<label>دسته‌بندی<select name="category_id">' . $categoryOptions((int) ($service['category_id'] ?? 0)) . '</select></label>'
            . '<label>واحد رسیدگی<select name="handling_unit_id">' . $handlingOptions((int) ($service['handling_unit_id'] ?? 0)) . '</select></label>'
            . '<label>معاونت مالک<select name="department_id">' . $departmentOptions((int) ($service['department_id'] ?? 0)) . '</select></label>'
            . '<label>نوع پیش‌فرض<select name="default_ticket_type">' . $ticketTypeOptions((string) ($service['default_ticket_type'] ?? 'incident')) . '</select></label>'
            . '<label>اولویت پیش‌فرض<select name="default_priority">' . $priorityOptions((string) ($service['default_priority'] ?? 'normal')) . '</select></label>'
            . '<label class="full">توضیح خدمت<textarea name="service_description" rows="3">' . e((string) ($service['description'] ?? '')) . '</textarea></label>'
            . '<p class="field-help full">برای همهٔ خدمات حوزهٔ IT، انتخاب سیستم از شناسنامهٔ فنی الزامی است؛ این شرط برای خدمات پشتیبانی اعمال نمی‌شود.</p>'
            . '<label class="check-label"><input type="checkbox" name="is_active" value="1" ' . ((int) ($service['is_active'] ?? 1) === 1 ? 'checked' : '') . '> فعال</label>'
            . '</div>';
    };
    render_header('کاتالوگ خدمات', $serviceUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Service Catalog</span><h1>کاتالوگ خدمات سازمان</h1><p>هر خدمت فرم تکمیلی، اولویت پیش‌فرض و مسیر رسیدگی مستقل دارد. با سربرگ‌ها بین بخش‌ها جابه‌جا شوید.</p></div><a class="button" href="index.php?page=new-ticket">ثبت درخواست</a></section>';

    // --- سربرگ‌ها (تب‌ها) ---
    echo '<div class="settings-tabs service-tabs" data-service-tabs>';
    echo '<a href="#tab-create" data-service-tab="tab-create">تعریف خدمت</a>';
    echo '<a href="#tab-list" data-service-tab="tab-list">لیست خدمات</a>';
    if ($isServiceAdmin) {
        echo '<a href="#tab-categories" data-service-tab="tab-categories">دسته‌بندی‌ها</a>';
    }
    echo '</div>';

    // --- تب ۱: تعریف خدمت (فقط فرم ایجاد خدمت) ---
    echo '<div class="service-tab-panels">';
    echo '<section class="card form-card service-tab-panel" id="tab-create">';
    if ($isServiceAdmin) {
        echo '<h2>تعریف خدمت جدید</h2><p class="muted">حوزه، دسته‌بندی، واحد رسیدگی و الزام شناسنامه فنی را از همین‌جا تعیین کنید. فیلدهای تکمیلی هر خدمت را بعد از ساخت، از دکمهٔ «فیلدها» در فهرست خدمات مدیریت کنید.</p><form method="post"><input type="hidden" name="action" value="add_service">' . csrf_field() . $serviceFormFields([]) . '<div class="actions"><button class="button" type="submit">ایجاد خدمت</button></div></form>';
    } else {
        echo '<h2>تعریف خدمت</h2><p class="muted">فقط ادمین‌ها می‌توانند خدمت جدید تعریف کنند.</p>';
    }
    echo '</section>';

    // --- تب ۲: لیست خدمات + فیلتر ---
    echo '<section class="card form-card service-tab-panel" id="tab-list" hidden>';
    echo '<h2>لیست خدمات</h2><p class="muted">با انتخاب حوزه، فهرست سرویس‌ها فیلتر می‌شود.</p>';
     echo '<form method="get" class="service-filter-bar"><input type="hidden" name="page" value="services"><input type="hidden" name="tab" value="tab-list"><label>فیلتر حوزه خدمت<select name="group" data-service-filter><option value="" ' . ($serviceGroupFilter === '' ? 'selected' : '') . '>همه حوزه‌ها</option><option value="it" ' . ($serviceGroupFilter === 'it' ? 'selected' : '') . '>خدمات IT</option><option value="support" ' . ($serviceGroupFilter === 'support' ? 'selected' : '') . '>خدمات پشتیبانی</option></select></label><button class="button secondary service-filter-submit" type="submit">اعمال فیلتر</button></form>';
    echo '<div class="service-list"><div class="service-row service-row-head"><span>خدمت</span><span>حوزه</span><span>دسته</span><span>واحد رسیدگی</span><span>اولویت</span>' . ($isServiceAdmin ? '<span data-no-sort>عملیات</span>' : '') . '</div>';
    foreach ($services as $service) {
        $sid = (int) $service['id'];
        echo '<div class="service-row"><span class="service-name"><strong>' . e($service['name']) . '</strong><small>' . e($service['code']) . ' • ' . (int) $service['field_count'] . ' فیلد' . ((int) $service['requires_asset'] === 1 ? ' • نیازمند شناسنامه' : '') . '</small></span>';
        echo '<span>' . ($service['service_group'] === 'it' ? 'IT' : 'پشتیبانی') . '</span>';
        echo '<span>' . e($service['category_name'] ?: '—') . '</span>';
        echo '<span>' . e($service['handling_unit_name'] ?: 'تعیین‌نشده') . '</span>';
        echo '<span><i class="priority-dot ' . e($service['default_priority']) . '"></i>' . e(priority_label($service['default_priority'])) . '</span>';
        if ($isServiceAdmin) {
            echo '<span class="service-actions"><button type="button" class="mini-button" data-modal-open="service-fields-' . $sid . '">فیلدها</button>';
            echo '<button type="button" class="mini-button" data-modal-open="service-edit-' . $sid . '">ویرایش</button>';
            echo '<form method="post" class="service-delete" data-confirm="خدمت از فهرست فعال خارج شود؟"><input type="hidden" name="action" value="delete_service"><input type="hidden" name="service_id" value="' . $sid . '">' . csrf_field() . '<button class="mini-button danger" type="submit">حذف</button></form></span>';
        }
        echo '</div>';
    }
    if (!$services) {
        echo '<div class="service-row"><span class="muted">با این فیلتر خدمتی پیدا نشد.</span></div>';
    }
    echo '</div></section>';

    // --- تب ۳: دسته‌بندی‌ها ---
    if ($isServiceAdmin) {
        echo '<section class="card form-card service-tab-panel" id="tab-categories" hidden>';
        echo '<h2>دسته‌بندی خدمات</h2><p class="muted">دسته‌بندی لایهٔ میانی بین حوزه و خدمت است؛ هر خدمت به یک دسته وصل می‌شود.</p>';
        echo '<form method="post" class="inline-form"><input type="hidden" name="action" value="save_service_category">' . csrf_field() . '<label>نام دسته<input name="category_name" required placeholder="مثلاً شبکه و اینترنت"></label><label>کد<input name="category_code" placeholder="IT-CAT-NET"></label><label>حوزه<select name="category_group"><option value="it">IT</option><option value="support">پشتیبانی</option></select></label><button class="button" type="submit">افزودن دسته</button></form>';
        echo '<div class="category-list">';
        foreach ($categories as $category) {
            $cid = (int) $category['id'];
            echo '<div><span><strong>' . e($category['name']) . '</strong><small>' . e($category['code'] ?: 'بدون کد') . ' • ' . ($category['service_group'] === 'it' ? 'IT' : 'پشتیبانی') . ' • ' . ((int) $category['is_active'] === 1 ? 'فعال' : 'غیرفعال') . '</small></span><span class="service-actions"><button type="button" class="mini-button" data-modal-open="category-edit-' . $cid . '">ویرایش</button><form method="post" class="service-delete" data-confirm="دسته غیرفعال شود؟ خدمت‌های وابسته حذف نمی‌شوند."><input type="hidden" name="action" value="delete_service_category"><input type="hidden" name="category_id" value="' . $cid . '">' . csrf_field() . '<button class="mini-button danger" type="submit">حذف</button></form></span></div>';
        }
        echo '</div></section>';
    }
    echo '</div>';

    // --- پنجره‌های ویرایش/فیلد (rendered outside the tab panels, fixed overlay) ---
    $serviceModals = '';
    if ($isServiceAdmin) {
        foreach ($services as $service) {
            $sid = (int) $service['id'];
            $serviceFields = $serviceFieldsById[$sid] ?? [];
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-edit-title-' . $sid . '" data-modal="service-edit-' . $sid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="service-edit-title-' . $sid . '">ویرایش خدمت</h3><p class="muted">' . e($service['name']) . ' • ' . e($service['code']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><form method="post" id="service-edit-form-' . $sid . '" class="service-edit-form"><input type="hidden" name="action" value="edit_service"><input type="hidden" name="service_id" value="' . $sid . '">' . csrf_field() . $serviceFormFields($service) . '</form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>انصراف</button><button type="submit" class="button" form="service-edit-form-' . $sid . '">ذخیره</button></div></div></div>';
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-fields-title-' . $sid . '" data-modal="service-fields-' . $sid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="service-fields-title-' . $sid . '">فیلدهای تکمیلی خدمت</h3><p class="muted">خدمت: ' . e($service['name']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><p class="muted">فیلد پویا یعنی پرسش اضافی که هنگام ثبت درخواست برای همین خدمت از درخواست‌کننده پرسیده می‌شود؛ در فرم ثبت خدمت نمایش داده نمی‌شود.</p><div class="category-list service-field-list">';
            foreach ($serviceFields as $field) {
                $serviceModals .= '<div><span><strong>' . e($field['label']) . '</strong><small>' . e($field['field_key']) . ' • ' . e($field['field_type']) . '</small></span><span>' . ((int) $field['is_required'] === 1 ? 'اجباری' : 'اختیاری') . '</span></div>';
            }
            if (!$serviceFields) {
                $serviceModals .= '<div><span class="muted">هنوز فیلد تکمیلی برای این خدمت تعریف نشده است.</span></div>';
            }
            $serviceModals .= '</div><form method="post" id="service-fields-form-' . $sid . '" class="service-field-form">' . csrf_field() . '<input type="hidden" name="action" value="add_service_field"><input type="hidden" name="service_id" value="' . $sid . '"><h4>افزودن فیلد برای فرم ثبت درخواست</h4><div class="form-grid"><label>کلید انگلیسی<input name="field_key" pattern="[a-z][a-z0-9_]{1,79}" required placeholder="device_model"></label><label>عنوان نمایشی<input name="field_label" required placeholder="مثلاً مدل دستگاه"></label><label>نوع فیلد<select name="field_type"><option value="text">متن</option><option value="textarea">متن چندخطی</option><option value="number">عدد</option><option value="select">انتخابی</option><option value="date">تاریخ</option></select></label><label>ترتیب نمایش<input type="number" name="sort_order" value="0"></label><label class="full">گزینه‌های فیلد انتخابی<small>هر گزینه در یک خط یا با ویرگول جدا شود</small><textarea name="field_options" rows="3"></textarea></label><label class="check-label"><input type="checkbox" name="field_required" value="1"> اجباری</label></div></form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>بستن</button><button type="submit" class="button" form="service-fields-form-' . $sid . '">افزودن فیلد</button></div></div></div>';
        }
        foreach ($categories as $category) {
            $cid = (int) $category['id'];
            $serviceModals .= '<div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="category-edit-title-' . $cid . '" data-modal="category-edit-' . $cid . '" hidden><div class="service-modal-card"><div class="service-modal-head"><h3 id="category-edit-title-' . $cid . '">ویرایش دسته‌بندی</h3><p class="muted">' . e($category['name']) . '</p><button type="button" class="service-modal-close" aria-label="بستن" data-modal-close>&times;</button></div><div class="service-modal-body"><form method="post" id="category-edit-form-' . $cid . '" class="service-edit-form"><input type="hidden" name="action" value="save_service_category"><input type="hidden" name="category_id" value="' . $cid . '">' . csrf_field() . '<div class="form-grid"><label>نام<input name="category_name" required value="' . e($category['name']) . '"></label><label>کد<input name="category_code" value="' . e($category['code'] ?? '') . '"></label><label>حوزه<select name="category_group"><option value="it" ' . ($category['service_group'] === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ($category['service_group'] === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label></div><label class="check-label"><input type="checkbox" name="category_active" value="1" ' . ((int) $category['is_active'] === 1 ? 'checked' : '') . '> فعال</label></form></div><div class="service-modal-foot"><button type="button" class="button secondary" data-modal-close>انصراف</button><button type="submit" class="button" form="category-edit-form-' . $cid . '">ذخیره</button></div></div></div>';
        }
    }
    if ($serviceModals !== '') {
        echo '<div class="service-modal-layer" data-service-modal-layer hidden>' . $serviceModals . '</div>';
    }
    render_footer();
    exit;
}

if ($page === 'help') {
    $helpUser = require_login();
    render_header('راهنمای سامانه', $helpUser);
    require __DIR__ . '/help-guide.php';
    render_footer();
    exit;
}

if ($page === 'knowledge') {
    $knowledgeUser = require_login();
    $articleId = (int) ($_GET['id'] ?? 0);
    $article = null;
    if ($articleId > 0) {
        $articleQuery = db()->prepare('SELECT k.*, u.full_name AS author_name FROM knowledge_articles k LEFT JOIN users u ON u.id = k.created_by WHERE k.id = ? AND (k.is_published = 1 OR ? = 1) LIMIT 1');
        $articleQuery->execute([$articleId, is_staff_role($knowledgeUser['role']) ? 1 : 0]);
        $article = $articleQuery->fetch();
    }
    $knowledgeSearch = trim((string) ($_GET['q'] ?? ''));
    $knowledgeSql = 'SELECT id, title, category, updated_at FROM knowledge_articles WHERE ' . (is_staff_role($knowledgeUser['role']) ? '1 = 1' : 'is_published = 1');
    $knowledgeParams = [];
    if ($knowledgeSearch !== '') {
        $knowledgeSql .= ' AND (title LIKE ? OR body LIKE ? OR category LIKE ?)';
        $knowledgeParams = array_fill(0, 3, '%' . $knowledgeSearch . '%');
    }
    $knowledgeSql .= ' ORDER BY updated_at DESC LIMIT 100';
    $knowledgeQuery = db()->prepare($knowledgeSql);
    $knowledgeQuery->execute($knowledgeParams);
    $articles = $knowledgeQuery->fetchAll();
    render_header('دانش‌نامه', $knowledgeUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Knowledge Base</span><h1>دانش‌نامه و راهنمای حل مشکل</h1><p>قبل از ثبت درخواست، راه‌حل‌های تاییدشده سازمان را جست‌وجو کنید.</p></div><form class="inline-search" method="get"><input type="hidden" name="page" value="knowledge"><input name="q" value="' . e($knowledgeSearch) . '" placeholder="جست‌وجو در دانش‌نامه"><button class="button" type="submit">جست‌وجو</button></form></section>';
    if ($article) {
        echo '<article class="card knowledge-article"><span class="eyebrow">' . e($article['category'] ?: 'راهنما') . '</span><h2>' . e($article['title']) . '</h2><p class="muted">نویسنده: ' . e($article['author_name'] ?: 'سامانه') . ' • ' . e(jalali_date($article['updated_at'])) . '</p><div class="article-body">' . nl2br(e($article['body'])) . '</div><a class="button secondary" href="index.php?page=knowledge">بازگشت به دانش‌نامه</a></article>';
    } else {
        echo '<section class="knowledge-grid">';
        foreach ($articles as $item) { echo '<a class="card knowledge-card" href="index.php?page=knowledge&id=' . (int) $item['id'] . '"><span class="eyebrow">' . e($item['category'] ?: 'راهنما') . '</span><h2>' . e($item['title']) . '</h2><small>به‌روزرسانی: ' . e(jalali_date($item['updated_at'], false)) . '</small></a>'; }
        if (!$articles) { echo '<div class="card empty-state"><h2>مقاله‌ای پیدا نشد.</h2><p>عبارت دیگری جست‌وجو کنید یا از کارشناس انفورماتیک کمک بگیرید.</p></div>'; }
        echo '</section>';
    }
    if (is_staff_role($knowledgeUser['role'])) {
        echo '<section class="card form-card"><h2>افزودن مقاله دانش‌نامه</h2><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="add_knowledge"><div class="form-grid"><label>عنوان مقاله<input name="article_title" required></label><label>دسته‌بندی<input name="article_category" placeholder="شبکه، پرینتر، نرم‌افزار"></label><label class="full">متن راهنما<textarea name="article_body" rows="7" required></textarea></label><label class="check-label"><input type="checkbox" name="is_published" value="1" checked> منتشر شود</label></div><button class="button" type="submit">ذخیره مقاله</button></form></section>';
    }
    render_footer();
    exit;
}

if ($page === 'notifications' && $user && !user_can($user, 'notif.view')) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه تماس بگیرید.</p></div></section>';
    render_footer();
    exit;
}
if ($page === 'notifications') {
    $notificationUser = require_login();
    $notificationQuery = db()->prepare('SELECT n.*, t.subject FROM notifications n LEFT JOIN tickets t ON t.id = n.ticket_id WHERE n.user_id = ?' . notification_visibility_sql((int) $notificationUser['id'], $notificationUser, 'n') . ' ORDER BY n.created_at DESC LIMIT 100');
    $notificationQuery->execute([(int) $notificationUser['id']]);
    render_header('اعلان‌های داخلی', $notificationUser);
    echo '<section class="page-heading"><div><span class="eyebrow">اعلان داخلی آفلاین</span><h1>اعلان‌های شما</h1><p>تغییرات درخواست‌ها بدون نیاز به ایمیل یا پیامک در همین سامانه اعلام می‌شوند.</p></div><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="mark_all_notifications"><button class="button secondary" type="submit">خوانده‌شدن همه</button></form></section><section class="card notification-list">';
    foreach ($notificationQuery->fetchAll() as $notification) {
        $target = notification_target_url($notification);
        echo '<div class="notification-item ' . ((int) $notification['is_read'] === 0 ? 'unread' : '') . '"><a href="' . $target . '"><strong>' . e($notification['title']) . '</strong><span>' . e($notification['body']) . '</span><small>' . e(jalali_date($notification['created_at'])) . ($notification['subject'] ? ' • ' . e($notification['subject']) : '') . '</small></a>';
        if ((int) $notification['is_read'] === 0) { echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="mark_notification"><input type="hidden" name="notification_id" value="' . (int) $notification['id'] . '"><button class="mini-button" type="submit">خوانده شد</button></form>'; }
        echo '</div>';
    }
    echo '</section>';
    render_footer();
    exit;
}

if ($page === 'audit') {
    $legacyAuditUser = require_login();
    if (!user_can_any($legacyAuditUser, ['logs.activity', 'audit.view', 'logs.view'])) {
        http_response_code(403); exit('دسترسی به لاگ سامانه مجاز نیست.');
    }
    redirect('index.php?page=activity-log');
}

if ($page === 'system-health') {
    system_health_render(require_login());
}

if ($page === 'logs') {
    $legacyLogUser = require_login();
    if (!user_can_any($legacyLogUser, ['logs.view', 'logs.activity', 'audit.view'])) { http_response_code(403); exit('دسترسی به لاگ سامانه مجاز نیست.'); }
    redirect(user_can($legacyLogUser, 'system.health') ? 'index.php?page=system-health' : 'index.php?page=activity-log&category=errors');
}

if ($page === 'activity-log') {
    $logUser = require_login();
    $canViewActivityLogs = user_can_any($logUser, ['logs.activity', 'audit.view']);
    $canViewSystemLogs = user_can($logUser, 'logs.view');
    if (!$canViewActivityLogs && !$canViewSystemLogs) {
        http_response_code(403); exit('دسترسی به لاگ سامانه مجاز نیست.');
    }
    if (!$canViewActivityLogs && $canViewSystemLogs && (string) ($_GET['category'] ?? 'errors') !== 'errors') {
        redirect('index.php?page=activity-log&category=errors');
    }
    if ((string) ($_GET['category'] ?? '') === 'errors' && user_can($logUser, 'system.health')) {
        redirect('index.php?page=system-health');
    }
    activity_logs_ensure();
    $category = valid_choice((string) ($_GET['category'] ?? 'all'), ['all', 'auth', 'admin', 'errors'], 'all');
    if ($canViewActivityLogs) {
        [$logWhere, $logParams] = activity_log_filters();
    } else {
        $logWhere = ' WHERE 1 = 0';
        $logParams = [];
    }
    $perPage = (in_array($category, ['all', 'errors'], true) && $canViewSystemLogs) ? 50 : 100;
    $logPage = max(1, (int) ($_GET['p'] ?? 1));
    $countQuery = db()->prepare('SELECT COUNT(*) FROM activity_logs l' . $logWhere);
    $countQuery->execute($logParams);
    $activityFilteredTotal = (int) $countQuery->fetchColumn();
    $systemRows = []; $systemFilteredTotal = 0; $systemTotal = 0; $systemWhere = ''; $systemParams = [];
    if (in_array($category, ['all', 'errors'], true) && user_can($logUser, 'logs.view')) {
        system_logs_ensure();
        $systemConditions = [];
        $systemLevel = valid_choice((string) ($_GET['level'] ?? ''), ['debug', 'info', 'warning', 'error'], '');
        if ($systemLevel !== '') { $systemConditions[] = 's.level = ?'; $systemParams[] = $systemLevel; }
        $systemSearch = trim((string) ($_GET['q'] ?? ''));
        if ($systemSearch !== '') {
            $systemConditions[] = '(s.context LIKE ? OR s.message LIKE ? OR s.meta_json LIKE ? OR s.request_uri LIKE ?)';
            foreach (range(1, 4) as $_) $systemParams[] = '%' . $systemSearch . '%';
        }
        $systemUserId = (int) ($_GET['user_id'] ?? 0);
        if ($systemUserId > 0) { $systemConditions[] = 's.user_id = ?'; $systemParams[] = $systemUserId; }
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            $dateText = trim((string) ($_GET[$key] ?? ''));
            $dateValue = $dateText !== '' ? jalali_input_to_gregorian($dateText, $key === 'to') : null;
            if ($dateValue !== null) { $systemConditions[] = 's.created_at ' . $operator . ' ?'; $systemParams[] = $dateValue; }
        }
        $systemWhere = $systemConditions ? ' WHERE ' . implode(' AND ', $systemConditions) : '';
        $systemCount = db()->prepare('SELECT COUNT(*) FROM system_logs s' . $systemWhere);
        $systemCount->execute($systemParams);
        $systemFilteredTotal = (int) $systemCount->fetchColumn();
        $systemTotal = (int) db()->query('SELECT COUNT(*) FROM system_logs')->fetchColumn();
    }
    $filteredTotal = $activityFilteredTotal + $systemFilteredTotal;
    $pages = max(1, (int) ceil(max($activityFilteredTotal, $systemFilteredTotal) / $perPage));
    $logPage = min($logPage, $pages);
    $offset = ($logPage - 1) * $perPage;
    $logSortA = (string) ($_GET['sort_a'] ?? ''); $logDirA = (string) ($_GET['dir_a'] ?? '');
    $logSortS = (string) ($_GET['sort_s'] ?? ''); $logDirS = (string) ($_GET['dir_s'] ?? '');
    $activityOrder = log_sort_order(['time' => 'l.created_at', 'user' => 'l.full_name', 'event' => 'COALESCE(l.action_label, l.action_code)', 'module' => 'l.module', 'ip' => 'l.ip_address', 'meta' => 'l.meta_json'], $logSortA, $logDirA, ' ORDER BY l.created_at DESC, l.id DESC');
    $activityQuery = db()->prepare('SELECT * FROM activity_logs l' . $logWhere . $activityOrder . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $activityQuery->execute($logParams);
    $activityRows = $activityQuery->fetchAll();
    if (in_array($category, ['all', 'errors'], true) && user_can($logUser, 'logs.view')) {
        $systemQuery = db()->prepare('SELECT s.*, u.username, u.full_name FROM system_logs s LEFT JOIN users u ON u.id = s.user_id' . $systemWhere . log_sort_order(['time' => 's.created_at', 'user' => 'u.full_name', 'level' => "FIELD(s.level, 'debug', 'info', 'warning', 'error')", 'context' => 's.context', 'request' => 's.request_uri', 'message' => 's.message', 'meta' => 's.meta_json', '__tie' => 's.id'], $logSortS, $logDirS, ' ORDER BY s.created_at DESC, s.id DESC') . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $systemQuery->execute($systemParams);
        $systemRows = $systemQuery->fetchAll();
    }
    $activityTotal = $canViewActivityLogs ? (int) db()->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn() : 0;
    $activityUsers = $canViewActivityLogs ? db()->query('SELECT id, full_name, username FROM users ORDER BY full_name')->fetchAll() : [];
    $activityActions = $canViewActivityLogs ? db()->query('SELECT action_code, MAX(action_label) AS label, COUNT(*) AS total FROM activity_logs GROUP BY action_code ORDER BY total DESC LIMIT 200')->fetchAll() : [];
    $activityModules = $canViewActivityLogs ? db()->query("SELECT module, COUNT(*) AS total FROM activity_logs WHERE module IS NOT NULL AND module <> '' GROUP BY module ORDER BY total DESC")->fetchAll() : [];
    $retentionDays = activity_log_retention_days();
    $activityExported = user_can($logUser, 'logs.activity_export');
    $queryArgs = $_GET; unset($queryArgs['page'], $queryArgs['p']); $queryArgs['page']='activity-log';
    $filterUrl = 'index.php?' . http_build_query($queryArgs);
    $exportArgs = $queryArgs; unset($exportArgs['page']);
    $tabNames = ['all'=>'همه رویدادها','auth'=>'ورود و خروج','admin'=>'مدیریتی و تنظیمات','errors'=>'خطاها و تلاش‌های ناموفق'];
    render_header('لاگ سامانه', $logUser);
    echo '<section class="page-heading"><div><span class="eyebrow">ردیابی و ممیزی یکپارچه</span><h1>لاگ سامانه</h1><p>یک گزارش واحد از ورود و خروج، فعالیت کاربران، تغییرات مدیریتی و خطاها؛ ' . number_format($filteredTotal) . ' رکورد در این نما؛ ' . number_format($activityTotal + $systemTotal) . ' رویداد و خطای ثبت‌شده در مجموع.</p></div><div class="actions">' . ($activityExported ? '<a class="button secondary" href="index.php?action=export_activity_logs' . ($exportArgs ? '&' . e(http_build_query($exportArgs)) : '') . '">خروجی فعالیت کاربران (Excel)</a>' : '') . '<a class="button secondary" href="index.php?page=settings#activity-log-settings">تنظیم نگهداری</a></div></section>';
    echo '<nav class="log-tabs" aria-label="دسته‌بندی لاگ‌ها">';
    foreach($tabNames as $tab=>$label){ echo '<a class="' . ($category===$tab?'active':'') . '" href="index.php?page=activity-log&category=' . e($tab) . '">' . e($label) . '</a>'; }
    echo '</nav><section class="card filter-card"><form method="get"><input type="hidden" name="page" value="activity-log"><input type="hidden" name="category" value="' . e($category) . '"><div class="filter-grid">';
    if ($canViewSystemLogs && in_array($category, ['all', 'errors'], true)) {
        $selectedLevel = valid_choice((string) ($_GET['level'] ?? ''), ['debug', 'info', 'warning', 'error'], '');
        echo '<label>شدت خطا<select name="level"><option value="">همه سطح‌ها</option><option value="error"' . ($selectedLevel === 'error' ? ' selected' : '') . '>خطا</option><option value="warning"' . ($selectedLevel === 'warning' ? ' selected' : '') . '>هشدار</option><option value="info"' . ($selectedLevel === 'info' ? ' selected' : '') . '>اطلاعات</option><option value="debug"' . ($selectedLevel === 'debug' ? ' selected' : '') . '>اشکال‌زدایی</option></select></label>';
    }
    if ($canViewActivityLogs) {
        echo '<label>کاربر<select name="user_id"><option value="">همه کاربران</option>';
        foreach ($activityUsers as $option) echo '<option value="' . (int)$option['id'] . '"' . ((int)($_GET['user_id']??0)===(int)$option['id']?' selected':'') . '>' . e($option['full_name'].' ('.$option['username'].')') . '</option>';
        echo '</select></label><label>نوع عملیات<select name="action_code"><option value="">همه عملیات</option>';
        foreach($activityActions as $option) echo '<option value="'.e($option['action_code']).'"'.((string)($_GET['action_code']??'')===(string)$option['action_code']?' selected':'').'>'.e($option['label']?:$option['action_code']).' ('.(int)$option['total'].')</option>';
        echo '</select></label><label>بخش سامانه<select name="module"><option value="">همه بخش‌ها</option>';
        foreach($activityModules as $option) echo '<option value="'.e($option['module']).'"'.((string)($_GET['module']??'')===(string)$option['module']?' selected':'').'>'.e($option['module']).' ('.(int)$option['total'].')</option>';
        echo '</select></label>';
    }
    echo '<label>از تاریخ<input name="from" placeholder="۱۴۰۵/۰۷/۰۱" value="' . e($_GET['from'] ?? '') . '"></label><label>تا تاریخ<input name="to" placeholder="۱۴۰۵/۰۷/۳۰" value="' . e($_GET['to'] ?? '') . '"></label>' . ($canViewActivityLogs ? '<label>آی‌پی<input name="ip" placeholder="مثلاً 192.168.1." value="' . e($_GET['ip'] ?? '') . '"></label>' : '') . '<label class="full">جست‌وجوی متن<input type="search" name="q" placeholder="کاربر، عملیات، نام رایانه یا جزئیات" value="' . e($_GET['q'] ?? '') . '"></label></div><div class="actions"><button class="button" type="submit">اعمال فیلتر</button><a class="button secondary" href="index.php?page=activity-log&category=' . e($category) . '">پاک‌کردن فیلترها</a></div></form></section>';
    if (in_array($category, ['all', 'errors'], true) && user_can($logUser, 'logs.view')) {
        echo '<section class="card ticket-table audit-table unified-log-table system-error-table"><div class="log-section-heading"><h2>خطاها و هشدارهای فنی</h2><span class="muted">' . number_format($systemFilteredTotal) . ' مورد</span></div>' . log_sort_header_row([['time','زمان'],['user','کاربر'],['level','شدت و بخش'],['request','درخواست'],['message','پیام'],['meta','جزئیات']], 'sort_s', 'dir_s', $queryArgs, $logSortS, $logDirS) . '';
        if ($systemRows) { foreach ($systemRows as $systemRow) {
            $systemMeta = activity_log_meta_lines(isset($systemRow['meta_json']) ? (string) $systemRow['meta_json'] : null);
            $systemDetails = $systemMeta ? '<details class="log-details"><summary>جزئیات فنی (' . count($systemMeta) . ')</summary><dl>' . implode('', array_map(static fn (array $line): string => '<div><dt>' . e((string) $line[0]) . '</dt><dd>' . e((string) $line[1]) . '</dd></div>', $systemMeta)) . '</dl></details>' : '<span class="muted">—</span>';
            echo '<div class="table-row"><span class="date-cell">' . e(persian_date($systemRow['created_at'])) . '</span><span>' . e($systemRow['full_name'] ?: $systemRow['username'] ?: 'کاربر ناشناس') . '</span><span><em class="log-level ' . e($systemRow['level']) . '">' . e(strtoupper((string) $systemRow['level'])) . '</em><br><small>' . e($systemRow['context'] ?: 'سامانه') . '</small></span><span class="muted">' . e($systemRow['request_uri'] ?: '—') . '</span><span>' . e($systemRow['message'] ?: '—') . '</span><span>' . $systemDetails . '</span></div>';
        }} else { echo '<div class="empty-state"><p>خطای فنی با فیلترهای انتخاب‌شده پیدا نشد.</p></div>'; }
        echo '</section>';
    }
    echo '<section class="card ticket-table audit-table unified-log-table"><div class="log-section-heading"><h2>' . ($category === 'errors' ? 'ورودهای ناموفق و تلاش‌های مسدودشده' : 'فعالیت کاربران و ممیزی') . '</h2><span class="muted">' . number_format($activityFilteredTotal) . ' مورد</span></div>' . log_sort_header_row([['time','زمان'],['user','کاربر'],['event','رویداد'],['module','مورد/بخش'],['ip','نشانی کاربر'],['meta','جزئیات قابل‌خواندن']], 'sort_a', 'dir_a', $queryArgs, $logSortA, $logDirA) . '';
    if($activityRows){ foreach($activityRows as $row){
        $metaLines=activity_log_meta_lines(isset($row['meta_json'])?(string)$row['meta_json']:null);
        $details=$metaLines?'<details class="log-details"><summary>جزئیات (' . count($metaLines) . ')</summary><dl>'.implode('',array_map(static fn(array $line):string=>'<div><dt>'.e((string)$line[0]).'</dt><dd>'.e((string)$line[1]).'</dd></div>',$metaLines)).'</dl></details>':'<span class="muted">بدون جزئیات افزوده</span>';
        $target=$row['target_label']?:($row['target_type']?($row['target_type'].($row['target_id']?' #'.$row['target_id']:'')):'—');
        $module=(string)($row['module']??'');
        echo '<div class="table-row"><span class="date-cell">'.e(persian_date($row['created_at'])).'</span><span><strong>'.e($row['full_name']?:$row['username']?:'سامانه/API').'</strong><br><small class="muted">'.e($row['username']?:'کاربر ناشناس').'</small><br><small class="muted">'.e(permission_roles()[(string)($row['role']??'')]??($row['role']?:'—')).'</small></span><span><strong>'.e($row['action_label']?:activity_action_label((string)$row['action_code'])).'</strong><br><small class="muted">'.e($row['action_code']).'</small></span><span>'.e($target).'<br><small class="muted">'.e($module!==''?$module:'—').'</small></span><span class="muted">'.e($row['ip_address']?:'—').'<br>'.e($row['computer_name']?:'نام رایانه نامشخص').'</span><span>'.$details.'</span></div>';
    }} else { echo '<div class="empty-state"><p>' . ($category === 'errors' && $systemRows ? 'تلاش ناموفق کاربری با این فیلتر ثبت نشده است.' : 'رکوردی با این فیلتر پیدا نشد؛ فیلترها را پاک کنید یا عبارت دیگری را جست‌وجو کنید.') . '</p></div>'; }
    echo '</section>';
    if($pages>1){ echo '<nav class="pagination" aria-label="صفحه‌بندی لاگ">'; for($i=max(1,$logPage-3);$i<=min($pages,$logPage+3);$i++){ $args=$queryArgs;$args['p']=$i; echo '<a class="'.($i===$logPage?'active':'').'" href="index.php?'.e(http_build_query($args)).'">'.number_format($i).'</a>'; } echo '</nav>'; }
    render_footer(); exit;
}

if ($page === 'backup') {
    $backupUser = require_permission('backup.view');
    // ساخت و بازیابی فقط با backup.manage؛ بقیه فهرست نسخه‌ها را می‌بینند.
    $backupInert = user_can($backupUser, 'backup.manage') ? '' : ' inert';
    $runs = backup_runs();
    $databaseName = (string) cfg('database.name', 'persian_ticketing');
    $dumpAvailable = backup_mysql_binary('mysqldump') !== null;
    $mysqlAvailable = backup_mysql_binary('mysql') !== null;
    render_header('به‌روزرسانی و پشتیبان‌گیری', $backupUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Backup & Restore</span><h1>به‌روزرسانی و پشتیبان‌گیری</h1><p>این بخش فقط برای ادمین اصلی است و از اطلاعات دیتابیس و پیوست‌ها محافظت می‌کند.</p></div><span class="admin-badge">ادمین اصلی</span></section>';
    if (user_can($backupUser, 'update.view') && user_is_primary_admin($backupUser)) {
        $updateInert = user_can($backupUser, 'update.apply') ? '' : ' inert';
        echo '<section class="card form-card" id="system-update"' . $updateInert . '><h2>به‌روزرسانی سامانه</h2>'
            . '<p class="muted">فایل ZIP پک به‌روزرسانی را انتخاب کنید. قبل از اعمال، نسخهٔ پشتیبان گرفته می‌شود و نسخهٔ جدیدتر نصب می‌شود. نصب مجدد همان نسخه مجاز است. برگشت به نسخهٔ قدیمی‌تر فقط با تیک تأیید انجام می‌شود و فایل‌های نسخهٔ جدیدتر را که در پک قدیمی نیستند حذف نمی‌کند. نسخهٔ فعلی: ' . e(system_update_current_version()) . '</p>'
            . '<form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="system_update">'
            . '<label class="field">فایل به‌روزرسانی (ZIP)<input type="file" name="update_package" accept=".zip" required></label>'
            . '<label class="check-label"><input type="checkbox" name="allow_downgrade" value="1"> برگشت به نسخهٔ قدیمی‌تر را تأیید می‌کنم (فقط اگر پک قدیمی‌تر از نسخهٔ فعلی است)</label>'
            . '<button class="button" type="submit">به‌روزرسانی</button></form></section>';
    }
    echo '<section class="settings-grid backup-grid"' . $backupInert . '><div class="card form-card"><h2>ساخت نسخه پشتیبان</h2><p class="muted">یک نسخه از دیتابیس و پوشه پیوست‌ها می‌سازد. قبل از هر بازیابی نیز یک نسخه ایمنی خودکار ساخته می‌شود.</p><p class="' . ($dumpAvailable ? 'alert success' : 'alert danger') . '">' . ($dumpAvailable ? 'ابزار mysqldump پیدا شد و پشتیبان‌گیری آماده است.' : 'mysqldump پیدا نشد؛ مسیر ITSM_MYSQLDUMP_PATH یا مسیر XAMPP را تنظیم کنید.') . '</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="create_backup"><button class="button" type="submit" ' . ($dumpAvailable ? '' : 'disabled') . '>ساخت نسخه پشتیبان اکنون</button></form><small class="muted">مسیر ذخیره: ' . e(backup_root_path()) . '</small></div>';
    echo '<div class="card form-card"><h2>بازیابی نسخه پشتیبان</h2><p class="muted">فقط فایل SQL را انتخاب کنید؛ فایل ZIP پیوست‌ها اختیاری است. پیش از بازیابی، سامانه نسخه ایمنی می‌سازد.</p><p class="alert danger">بازیابی می‌تواند اطلاعات فعلی را جایگزین کند. ابتدا روی staging آزمایش کنید.</p><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="restore_backup"><input type="hidden" name="MAX_FILE_SIZE" value="268435456"><label class="file-input">فایل SQL<input type="file" name="sql_dump" accept=".sql,text/plain" required><small>حداکثر ۲۵۶ مگابایت</small></label><label class="file-input">آرشیو پیوست‌ها، اختیاری<input type="file" name="uploads_archive" accept=".zip,application/zip"><small>فقط ZIP ساخته‌شده توسط سامانه</small></label><label class="check-label"><input type="checkbox" name="restore_confirm" value="1" required> خطر جایگزینی اطلاعات فعلی را می‌پذیرم</label><button class="button danger-button" type="submit" ' . ($dumpAvailable && $mysqlAvailable ? '' : 'disabled') . '>بازیابی اطلاعات</button></form></div></section>';
    echo '<section class="card form-card"><h2>نسخه‌های موجود</h2><p class="muted">دانلود نسخه پشتیبان فقط برای ادمین اصلی فعال است. فایل تنظیمات شامل رمزها عمداً داخل این بسته‌ها قرار نمی‌گیرد.</p><div class="category-list backup-list">';
    foreach ($runs as $run) {
        $base = $run['name'] . '/';
        $sqlFile = backup_download_path($base . $databaseName . '.sql');
        $zipFile = backup_download_path($base . 'uploads.zip');
        $sqlAction = $sqlFile ? '<a class="button secondary" href="index.php?action=download_backup&file=' . rawurlencode($base . $databaseName . '.sql') . '">دریافت SQL</a>' : '<span class="muted">SQL ناقص</span>';
        $zipAction = $zipFile ? '<a class="button secondary" href="index.php?action=download_backup&file=' . rawurlencode($base . 'uploads.zip') . '">دریافت پیوست‌ها</a>' : '<span class="muted">ZIP موجود نیست</span>';
        echo '<div><span><strong>' . e($run['name']) . '</strong><small>' . e($run['created_at']) . ' • ' . e(number_format((float) $run['size'] / 1048576, 1)) . ' MB</small></span><span class="actions">' . $sqlAction . $zipAction . '</span></div>';
    }
    if (!$runs) {
        echo '<div class="empty-state"><h2>هنوز نسخه‌ای ثبت نشده است.</h2><p>اولین نسخه را از فرم بالا بسازید.</p></div>';
    }
    echo '</div></section>';
    render_footer();
    exit;
}

if ($page === 'search' && $user && !user_can($user, 'search.global')) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه تماس بگیرید.</p></div></section>';
    render_footer();
    exit;
}
if ($page === 'search') {
    $searchUser = require_login();
    $searchTerm = trim((string) ($_GET['q'] ?? ''));
    // ۱.۳۷.۷ — جست‌وجو در «بخش‌ها و صفحه‌های سامانه» هم انجام می‌شود؛ مثلاً نوشتن «تردد» صفحه‌های
    // مرتبط با تردد را نشان می‌دهد. فقط صفحه‌هایی که کاربر اجازهٔ دیدنشان را دارد فهرست می‌شوند.
    $searchSections = global_search_sections(
        global_search_catalog(),
        $searchTerm,
        static function (array $section) use ($searchUser): bool {
            $special = (string) ($section['special'] ?? '');
            if ($special === 'food') {
                return function_exists('food_ticket_is_allowed') ? food_ticket_is_allowed($searchUser) : false;
            }
            if ($special === 'governance') {
                return is_staff_role((string) ($searchUser['role'] ?? '')) && user_can($searchUser, 'governance.manage');
            }
            $codes = [];
            if (isset($section['perm']) && (string) $section['perm'] !== '') {
                $codes[] = (string) $section['perm'];
            }
            foreach ((array) ($section['perms'] ?? []) as $code) {
                $codes[] = (string) $code;
            }
            return $codes === [] || user_can_any($searchUser, $codes);
        }
    );
    render_header('جست‌وجوی سراسری', $searchUser);
    echo '<section class="page-heading"><div><span class="eyebrow">جست‌وجوی سراسری</span><h1>نتایج جست‌وجو</h1><p>در صفحه‌ها، درخواست‌ها، شناسنامه‌ها، تردد، رسانه‌ها و دانش‌نامه جست‌وجو کنید.</p></div></section><form class="card filter-card global-search-page" method="get"><input type="hidden" name="page" value="search"><input name="q" required value="' . e($searchTerm) . '" placeholder="مثلاً تردد، فیش غذا، پشتیبان‌گیری ..."><button class="button" type="submit">جست‌وجو</button></form>';
    if ($searchTerm !== '') {
        $needles = array_values(array_unique(array_filter([$searchTerm, global_search_normalize($searchTerm)], static fn(string $v): bool => $v !== '')));
        [$searchScope, $searchParams] = ticket_scope($searchUser);
        $ticketGroups=[]; $ticketTerms=[];
        foreach($needles as $needle){$like='%'.$needle.'%';$ticketGroups[]='(t.subject LIKE ? OR t.description LIKE ? OR r.full_name LIKE ?)';array_push($ticketTerms,$like,$like,$like);}
        $ticketSql = 'SELECT t.id, t.subject, t.status, t.ticket_type, d.name AS department_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id ' . ($searchScope ?: 'WHERE 1 = 1') . ' AND (' . implode(' OR ', $ticketGroups) . ') ORDER BY t.updated_at DESC LIMIT 30';
        $ticketQuery = db()->prepare($ticketSql); $ticketQuery->execute(array_merge($searchParams,$ticketTerms)); $ticketRows=$ticketQuery->fetchAll();
        [$assetSearchScope, $assetSearchParams] = asset_scope($searchUser);
        $assetProfileColumns = db_table_columns('asset_profiles');
        $assetProfileJoin = $assetProfileColumns ? ' LEFT JOIN asset_profiles p ON p.asset_id = a.id ' : ' ';
        $assetSearchFields = ['a.asset_tag','a.hostname','a.serial_number'];
        if ($assetProfileColumns) {
            foreach (['record_number','owner_full_name','registrar_name','unit_name','personnel_number','user_login','computer_type'] as $profileField) {
                if (isset($assetProfileColumns[$profileField])) $assetSearchFields[] = 'p.' . $profileField;
            }
        }
        $assetGroups=[];$assetTerms=[];
        foreach($needles as $needle){$like='%'.$needle.'%';$assetGroups[]='('.implode(' OR ',array_map(static fn(string $field):string=>$field.' LIKE ?',$assetSearchFields)).')';foreach($assetSearchFields as $_field)$assetTerms[]=$like;}
        $ownerSelect = isset($assetProfileColumns['owner_full_name']) ? 'p.owner_full_name' : 'NULL';
        $recordSelect = isset($assetProfileColumns['record_number']) ? 'p.record_number' : 'NULL';
        $assetSql = 'SELECT a.id, a.asset_tag, a.hostname, a.serial_number, d.name AS department_name, ' . $ownerSelect . ' AS profile_owner, ' . $recordSelect . ' AS profile_record FROM assets a LEFT JOIN departments d ON d.id = a.department_id ' . $assetProfileJoin . ($assetSearchScope ?: 'WHERE 1 = 1') . ' AND (' . implode(' OR ', $assetGroups) . ') ORDER BY a.updated_at DESC LIMIT 30';
        $assetQuery = db()->prepare($assetSql); $assetQuery->execute(array_merge($assetSearchParams,$assetTerms)); $assetRows=$assetQuery->fetchAll();
        $articleGroups=[];$articleTerms=[];
        foreach($needles as $needle){$like='%'.$needle.'%';$articleGroups[]='(title LIKE ? OR body LIKE ? OR category LIKE ?)';array_push($articleTerms,$like,$like,$like);}
        $articleQuery = db()->prepare('SELECT id, title, category, updated_at FROM knowledge_articles WHERE is_published = 1 AND (' . implode(' OR ', $articleGroups) . ') ORDER BY updated_at DESC LIMIT 30');
        $articleQuery->execute($articleTerms); $articleRows=$articleQuery->fetchAll();
        $trafficRows = []; $mediaRows = [];
        if (user_can($searchUser, 'traffic.view')) {
            try {
                $trafficGroups=[]; $trafficTerms=[];
                foreach($needles as $needle){$like='%'.$needle.'%';$trafficGroups[]='(v.serial_no LIKE ? OR v.full_name LIKE ? OR v.national_code LIKE ? OR v.phone LIKE ? OR v.company LIKE ? OR v.meeting_with LIKE ? OR v.description LIKE ?)';array_push($trafficTerms,$like,$like,$like,$like,$like,$like,$like);}
                $trafficQuery=db()->prepare('SELECT v.id,v.serial_no,v.visit_date,v.full_name,v.company,v.meeting_with,v.entry_time FROM traffic_visits v WHERE '.implode(' OR ',$trafficGroups).' ORDER BY v.visit_date DESC,v.id DESC LIMIT 20');
                $trafficQuery->execute($trafficTerms);$trafficRows=$trafficQuery->fetchAll();
            } catch (Throwable) { $trafficRows=[]; }
        }
        if (user_can($searchUser, 'cddvd.view')) {
            try {
                [$mediaScope,$mediaScopeParams]=cd_dvd_scope($searchUser,'r');
                $mediaGroups=[];$mediaTerms=[];
                foreach($needles as $needle){$like='%'.$needle.'%';$mediaGroups[]='(r.serial LIKE ? OR r.info_type LIKE ? OR r.info_desc LIKE ? OR r.receiver LIKE ? OR r.receiver_unit LIKE ? OR r.brought_by LIKE ? OR r.exit_sheet LIKE ? OR r.note LIKE ?)';array_push($mediaTerms,$like,$like,$like,$like,$like,$like,$like,$like);}
                $mediaSql='SELECT r.id,r.serial,r.media,r.direction,r.info_type,r.info_desc,r.date_str,r.receiver,r.brought_by FROM cd_dvd_records r '.($mediaScope!==''?' '.$mediaScope:'WHERE 1=1').' AND ('.implode(' OR ',$mediaGroups).') ORDER BY r.sort_key DESC,r.id DESC LIMIT 20';
                $mediaQuery=db()->prepare($mediaSql);$mediaQuery->execute(array_merge($mediaScopeParams,$mediaTerms));$mediaRows=$mediaQuery->fetchAll();
            } catch (Throwable) { $mediaRows=[]; }
        }
        $foundAny = (bool) ($searchSections || $ticketRows || $assetRows || $articleRows || $trafficRows || $mediaRows);
        if (!$foundAny) {
            echo '<section class="card search-not-found"><span class="search-empty-icon">⌕</span><h2>یافت نشد</h2><p>برای «' . e($searchTerm) . '» در صفحه‌ها و اطلاعات قابل‌دسترسی سامانه نتیجه‌ای پیدا نشد.</p><small>املای واژه را بررسی کنید یا عبارت دیگری جست‌وجو کنید.</small></section>';
        } else {
            // کارت «بخش‌ها و صفحه‌ها» اکنون فقط هنگام وجود نتیجه نمایش داده می‌شود.
            echo '<section class="search-results">';
            if($searchSections){echo '<div class="card"><h2>صفحه‌ها و بخش‌های مرتبط <span class="muted">('.count($searchSections).')</span></h2><div class="category-list">';foreach($searchSections as $result){echo '<a class="search-result" href="'.e((string)$result['url']).'"><strong>'.e((string)$result['title']).'</strong><small class="muted">باز کردن این بخش</small></a>'; }echo '</div></div>';}
            if($ticketRows){echo '<div class="card"><h2>درخواست‌ها <span class="muted">('.count($ticketRows).')</span></h2><div class="category-list">';foreach($ticketRows as $result){echo '<a class="search-result" href="index.php?page=ticket&id='.(int)$result['id'].'"><strong>'.e(ticket_number((int)$result['id'])).' • '.e($result['subject']).'</strong><small>'.e($result['department_name']?:'بدون معاونت').' • '.e(ticket_type_label($result['ticket_type'])).' • '.e(status_label($result['status'])).'</small></a>'; }echo '</div></div>';}
            if($assetRows){echo '<div class="card"><h2>شناسنامه‌ها و دارایی‌ها <span class="muted">('.count($assetRows).')</span></h2><div class="category-list">';foreach($assetRows as $result){echo '<a class="search-result" href="index.php?page=inventory&id='.(int)$result['id'].'"><strong>'.asset_tag_label($result['asset_tag'],$result['hostname']).' • '.e(asset_display_name($result['hostname'])).'</strong><small>'.e($result['profile_record']?:$result['serial_number']?:'شناسه ثبت نشده').' • '.e($result['profile_owner']?:$result['department_name']?:'بدون مالک ثبت‌شده').'</small></a>'; }echo '</div></div>';}
            if($articleRows){echo '<div class="card"><h2>دانش‌نامه <span class="muted">('.count($articleRows).')</span></h2><div class="category-list">';foreach($articleRows as $result){echo '<a class="search-result" href="index.php?page=knowledge&id='.(int)$result['id'].'"><strong>'.e($result['title']).'</strong><small>'.e($result['category']?:'راهنما').' • '.e(jalali_date($result['updated_at'],false)).'</small></a>'; }echo '</div></div>';}
            if($trafficRows){echo '<div class="card"><h2>سوابق تردد <span class="muted">('.count($trafficRows).')</span></h2><div class="category-list">';foreach($trafficRows as $result){echo '<a class="search-result" href="index.php?page=traffic-control&tab=history&search='.rawurlencode($searchTerm).'"><strong>'.e($result['full_name']).' • '.e($result['serial_no']).'</strong><small>'.e($result['visit_date']).' • '.e($result['company']?:'بدون شرکت').' • '.e($result['meeting_with']?:'بدون مقصد').'</small></a>'; }echo '</div></div>';}
            if($mediaRows){echo '<div class="card"><h2>سوابق CD/DVD <span class="muted">('.count($mediaRows).')</span></h2><div class="category-list">';foreach($mediaRows as $result){echo '<a class="search-result" href="index.php?page=cd-dvd&tab=history&search='.rawurlencode($searchTerm).'"><strong>'.e($result['media']).' • '.e($result['serial']).' • '.e($result['info_type']).'</strong><small>'.e($result['date_str']).' • '.e($result['direction']==='IN'?'ورود':'خروج').' • '.e($result['receiver']?:$result['brought_by']?:'').'</small></a>'; }echo '</div></div>';}
            echo '</section>';
        }
    }
    render_footer();
    exit;
}

if ($page === 'queue') {
    $queueUser = require_permission('queue.view');
    $queueName = valid_choice((string) ($_GET['queue'] ?? 'all'), ['all', 'unassigned', 'overdue', 'mine', 'waiting_user', 'resolved', 'cancellation_requested', 'cancelled'], 'all');
    [$queueScope, $queueParams] = ticket_scope($queueUser);
    $queueConditions = $queueScope !== '' ? [substr($queueScope, 6)] : [];
    if ($queueName === 'unassigned') { $queueConditions[] = 't.assigned_to IS NULL'; }
    if ($queueName === 'overdue') { $queueConditions[] = 't.sla_paused_at IS NULL AND t.status NOT IN ("resolved", "closed", "cancelled") AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.first_response_at IS NULL AND t.ola_response_due_at IS NOT NULL AND t.ola_response_due_at < NOW()) OR (t.ola_due_at IS NOT NULL AND t.ola_due_at < NOW()) OR (t.ola_escalation_1_at IS NOT NULL AND t.ola_escalation_1_at < NOW()) OR (t.ola_escalation_2_at IS NOT NULL AND t.ola_escalation_2_at < NOW()))'; }
    if ($queueName === 'mine') { $queueConditions[] = 't.assigned_to = ?'; $queueParams[] = (int) $queueUser['id']; }
    if ($queueName === 'waiting_user') { $queueConditions[] = 't.status IN ("waiting_user", "pending")'; }
    if ($queueName === 'resolved') { $queueConditions[] = 't.status = "resolved"'; }
    if ($queueName === 'cancellation_requested') { $queueConditions[] = 't.cancel_requested_at IS NOT NULL'; }
    if ($queueName === 'cancelled') { $queueConditions[] = 't.status = "cancelled"'; }
    else { $queueConditions[] = 't.status <> "cancelled"'; }
    $queueWhere = $queueConditions ? 'WHERE ' . implode(' AND ', $queueConditions) : '';
    $queuePerPage = 100;
    $queuePage = max(1, (int) ($_GET['p'] ?? 1));
    $queueCountQuery = db()->prepare('SELECT COUNT(*) FROM tickets t ' . $queueWhere);
    $queueCountQuery->execute($queueParams);
    $queueTotal = (int) $queueCountQuery->fetchColumn();
    $queuePages = max(1, (int) ceil($queueTotal / $queuePerPage));
    $queuePage = min($queuePage, $queuePages);
    $queueOffset = ($queuePage - 1) * $queuePerPage;
        $queueQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $queueWhere . ' ORDER BY CASE WHEN t.cancel_requested_at IS NOT NULL THEN 0 WHEN (t.status NOT IN ("resolved", "closed", "cancelled") AND t.sla_paused_at IS NULL AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.first_response_at IS NULL AND t.ola_response_due_at IS NOT NULL AND t.ola_response_due_at < NOW()) OR (t.ola_due_at IS NOT NULL AND t.ola_due_at < NOW()) OR (t.ola_escalation_1_at IS NOT NULL AND t.ola_escalation_1_at < NOW()) OR (t.ola_escalation_2_at IS NOT NULL AND t.ola_escalation_2_at < NOW()))) THEN 1 ELSE 2 END, FIELD(t.priority, "critical", "urgent", "normal"), t.created_at ASC LIMIT ' . $queuePerPage . ' OFFSET ' . $queueOffset);
    $queueQuery->execute($queueParams);
    $queueRows = $queueQuery->fetchAll();
    render_header('صف کاری', $queueUser);
     echo '<section class="page-heading"><div><span class="eyebrow">صف مرکزی خدمات</span><h1>صف کار واحد رسیدگی</h1><p>درخواست‌های هر حوزه جدا هستند؛ موارد خارج از SLA/OLA و اولویت‌های حیاتی بالاتر قرار می‌گیرند. نمایش ' . ($queueRows ? number_format($queueOffset + 1) . ' تا ' . number_format($queueOffset + count($queueRows)) . ' از ' . number_format($queueTotal) : '۰ از ' . number_format($queueTotal)) . ' درخواست.</p></div><a class="button secondary" href="index.php?page=reports">گزارش کامل</a></section><div class="queue-tabs"><a class="' . ($queueName === 'all' ? 'active' : '') . '" href="index.php?page=queue&queue=all">همه</a><a class="' . ($queueName === 'unassigned' ? 'active' : '') . '" href="index.php?page=queue&queue=unassigned">بدون مسئول</a><a class="' . ($queueName === 'overdue' ? 'active' : '') . '" href="index.php?page=queue&queue=overdue">خارج از SLA/OLA</a><a class="' . ($queueName === 'mine' ? 'active' : '') . '" href="index.php?page=queue&queue=mine">مسئول من</a><a class="' . ($queueName === 'waiting_user' ? 'active' : '') . '" href="index.php?page=queue&queue=waiting_user">انتظار کاربر</a><a class="' . ($queueName === 'resolved' ? 'active' : '') . '" href="index.php?page=queue&queue=resolved">حل‌شده</a><a class="' . ($queueName === 'cancellation_requested' ? 'active' : '') . '" href="index.php?page=queue&queue=cancellation_requested">درخواست لغو</a><a class="' . ($queueName === 'cancelled' ? 'active' : '') . '" href="index.php?page=queue&queue=cancelled">لغوشده</a></div><form method="post"><section class="card ticket-table queue-table"><div class="table-head"><span>انتخاب / درخواست</span><span>معاونت</span><span>ثبت‌کننده</span><span>مسئول</span><span>وضعیت</span></div>';
    foreach ($queueRows as $queueTicket) {
        echo '<label class="table-row queue-row"><span class="ticket-title"><input type="checkbox" name="ticket_ids[]" value="' . (int) $queueTicket['id'] . '"><b>' . e(ticket_number((int) $queueTicket['id'])) . '</b><a href="index.php?page=ticket&id=' . (int) $queueTicket['id'] . '"><strong>' . e($queueTicket['subject']) . '</strong></a><small>' . e(ticket_type_label($queueTicket['ticket_type'])) . ' • ' . e(priority_label($queueTicket['priority'])) . (!empty($queueTicket['cancel_requested_at']) ? ' • درخواست لغو در انتظار بررسی' : '') . '</small></span><span>' . e($queueTicket['department_name'] ?: 'بدون معاونت') . '</span><span>' . e($queueTicket['requester_name']) . '</span><span>' . e($queueTicket['assignee_name'] ?: 'بدون مسئول') . '</span><span><em class="status ' . e($queueTicket['status']) . '">' . e(status_label($queueTicket['status'])) . '</em></span></label>';
        }
    echo '</section><div class="bulk-actions"><input type="hidden" name="action" value="bulk_update_tickets">' . csrf_field() . '<select name="bulk_status"><option value="in_progress">در حال انجام</option><option value="waiting_user">در انتظار پاسخ کاربر</option><option value="resolved">حل‌شده</option></select><label class="bulk-note">پیام مشترک (برای انتظار/حل‌شده الزامی)<textarea name="bulk_note" rows="2" maxlength="5000" placeholder="این پیام در همهٔ درخواست‌های انتخاب‌شده ثبت می‌شود"></textarea></label><button class="button" type="submit">اعمال روی انتخاب‌ها</button></div></form>';
    if ($queuePages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی صف">';
        for ($queueNav = max(1, $queuePage - 3); $queueNav <= min($queuePages, $queuePage + 3); $queueNav++) {
            echo '<a class="' . ($queueNav === $queuePage ? 'active' : '') . '" href="index.php?page=queue&queue=' . e($queueName) . '&p=' . $queueNav . '">' . number_format($queueNav) . '</a>';
        }
        echo '</nav>';
    }
    // اسکریپت درون‌صفحه: تضمین کارکرد سربرگ‌ها و مودال‌ها مستقل از کش app.js

    render_footer();
    exit;
}

if ($page === 'chatbot-admin') {
    $chatbotAdmin = require_permission('chatbot.manage');
    if (isset($_GET['lab_wav'])) {
        $labName = basename((string) $_GET['lab_wav']);
        if (!preg_match('/^[A-Za-z0-9_.-]+\\.wav$/', $labName)) {
            http_response_code(400);
            echo 'نام فایل نامعتبر است.';
            exit;
        }
        $labFile = APP_ROOT . '/storage/tts/_lab/' . $labName;
        if (!is_file($labFile) || filesize($labFile) < 100) {
            http_response_code(404);
            echo 'فایل آزمایشگاه یافت نشد.';
            exit;
        }
        $labAudio = (string) file_get_contents($labFile);
        header('Content-Type: audio/wav');
        header('Content-Length: ' . strlen($labAudio));
        echo $labAudio;
        exit;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        require_csrf();
        try {
            if (isset($_POST['tts_settings'])) {
                chatbot_save_tts($_POST, $chatbotAdmin);
                flash('success', 'تنظیمات گویندگی بات ذخیره شد.');
            } elseif (isset($_POST['widget_settings'])) {
                chatbot_save_widget($_POST, $chatbotAdmin);
                flash('success', 'تنظیمات آریا ذخیره شد.');
            } elseif (isset($_POST['tts_test'])) {
                $ttsTest = chatbot_tts_say('سلام، می‌خواهم به شما کمک کنم. درخواست شما ثبت می‌شود.');
                if ($ttsTest['ok']) {
                    $ttsActive = chatbot_tts_active();
                    $ttsTestVoice = ($ttsActive['kind'] === 'piper')
                        ? ' موتور عصبی Piper فعال است.'
                        : ' موتور eSpeak فعال است.';
                    if (chatbot_tts_colloquial_enabled()) {
                        $ttsTestVoice .= ' خواندن محاوره‌ای روشن است.';
                    }
                    flash('success', 'موتور صوتی سرور سالم است و فایل تست ساخته شد.' . $ttsTestVoice);
                } else {
                    flash('danger', $ttsTest['error']);
                }
            } elseif (isset($_POST['tts_ezafe_pairs'])) {
                $addedPairs = chatbot_tts_save_mined_pairs($_POST, $chatbotAdmin);
                flash('success', $addedPairs > 0 ? ('فهرست کسره با ' . $addedPairs . ' مورد به‌روز شد.') : 'موردی انتخاب نشد.');
            } elseif (isset($_POST['tts_lab'])) {
                $labText = (string) ($_POST['lab_text'] ?? '');
                $labVariant = valid_choice((string) ($_POST['lab_variant'] ?? 'all'), ['all', 'a', 'b', 'c', 'd', 'e', 'f', 'g'], 'all');
                $labWav = !empty($_POST['lab_wav']);
                $labLines = preg_split('/\R/u', $labText) ?: [];
                $labRows = [];
                $labVars = $labVariant === 'all' ? ['a', 'b', 'c', 'd', 'e', 'f', 'g'] : [$labVariant];
                foreach ($labLines as $labLine) {
                    $labLine = trim((string) $labLine);
                    if ($labLine === '') {
                        continue;
                    }
                    foreach ($labVars as $labV) {
                        $one = chatbot_tts_lab_run($labLine, (string) $labV, $labWav);
                        $labRows[] = [
                            'input' => $labLine,
                            'variant' => $labV,
                            'stages' => $one['stages'] ?? [],
                            'ipa' => $one['ipa'] ?? [],
                            'wav' => $one['wav'] ?? '',
                            'wav_error' => $one['wav_error'] ?? '',
                            'final' => $one['final'] ?? '',
                        ];
                    }
                }
                chatbot_render_admin($chatbotAdmin, [
                    'text' => $labText,
                    'variant' => $labVariant,
                    'wav' => $labWav,
                    'rows' => $labRows,
                ]);
                exit;
            } elseif (isset($_POST['tts_sample'])) {
                $ttsSample = chatbot_tts_say('سلام، می‌خواهم به شما کمک کنم. درخواست شما ثبت می‌شود.');
                if (!$ttsSample['ok']) {
                    flash('danger', $ttsSample['error']);
                    redirect('index.php?page=chatbot-admin');
                }
                $sampleAudio = (string) file_get_contents($ttsSample['file']);
                header('Content-Type: audio/wav');
                header('Content-Length: ' . strlen($sampleAudio));
                header('Content-Disposition: attachment; filename="aria-test.wav"');
                echo $sampleAudio;
                exit;
            } else {
                chatbot_save_faq($_POST, $chatbotAdmin);
                flash('success', 'مدخل بات ذخیره شد.');
            }
        } catch (RuntimeException $chatbotError) {
            flash('danger', $chatbotError->getMessage());
        }
        redirect('index.php?page=chatbot-admin');
    }
    chatbot_render_admin($chatbotAdmin);
}

if ($page === 'holidays') {
    // فقط کاربر با holidays.manage می‌تواند تعطیلی ثبت/حذف کند؛ بقیه فهرست را فقط می‌بینند.
    $holidayInert = user_can($user, 'holidays.manage') ? '' : ' inert';
    $holidayAdmin = require_permission('holidays.view');
    $holidayQuery = db()->query('SELECT h.*, u.full_name FROM holidays h LEFT JOIN users u ON u.id = h.created_by ORDER BY h.holiday_date');
    render_header('تقویم SLA', $holidayAdmin);
     echo '<section class="page-heading"><div><span class="eyebrow">تقویم کاری</span><h1>تعطیلات رسمی و توقف SLA</h1><p>پنج‌شنبه و جمعه تعطیل ثابت هستند؛ سایر تعطیلات را با تقویم شمسی انتخاب کنید.</p></div></section><div class="holiday-guard"' . $holidayInert . '><section class="card form-card"><h2>افزودن تعطیلی</h2><form method="post"><input type="hidden" name="action" value="add_holiday">' . csrf_field() . '<div class="form-grid"><label class="date-picker-field">تاریخ جلالی<div class="jalali-picker" data-jalali-picker><input id="holiday-date" name="holiday_date" required autocomplete="off" placeholder="۱۴۰۵/۰۱/۰۱"><button type="button" class="calendar-trigger" aria-label="بازکردن تقویم">▦</button><div class="jalali-calendar" hidden></div></div><small class="field-help">پنج‌شنبه و جمعه در تقویم غیرفعال هستند و خودکار تعطیل محسوب می‌شوند.</small></label><label>عنوان تعطیلی<input name="holiday_title" required placeholder="نوروز"></label></div><button class="button" type="submit">افزودن به تقویم</button></form><div class="fixed-holidays"><span class="tag">تعطیلی ثابت</span><b>پنج‌شنبه و جمعه</b><small>در محاسبه SLA به‌صورت خودکار از روزهای کاری حذف می‌شوند.</small></div><hr><h2>تقویم سالانه — تیک روز تعطیل</h2>
<p class="muted">روی هر روز کلیک کنید تا به‌عنوان تعطیل ثبت یا حذف شود. رنگ <strong style="color:#dc2626">قرمز</strong> = تعطیل. پنج‌شنبه و جمعه ثابت‌اند.</p>
<div class="year-holiday-board" data-year-holiday-board>
<div class="board-head"><label>سال جلالی <select data-year-board-year">' .
implode('', array_map(static fn ($y) => '<option value="' . $y . '"' . ($y === (int) (gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'))[0]) ? ' selected' : '') . '>' . $y . '</option>', range(1400, 1415))) .
'</select></label></div>
<div class="year-grid" data-year-board-grid></div>
<form method="post" data-toggle-holiday class="hidden-toggle" style="position:absolute;left:-9999px">' . csrf_field() . '<input type="hidden" name="action" value="toggle_holiday"><input type="hidden" name="holiday_date" value=""><input type="hidden" name="holiday_title" value="تعطیل رسمی"></form>
</div>
<hr><h2>ورود تقویم سالانه</h2><p class="muted">فرمت‌های استاندارد CSV و ICS از منابع تقویم قابل استفاده‌اند. در CSV ستون‌های date و title یا ردیف‌های تاریخ و عنوان را وارد کنید؛ تاریخ جلالی ۱۴۰۵/۰۱/۰۱ و میلادی 2026-03-21 هر دو پذیرفته می‌شوند. پنج‌شنبه و جمعه از ورود فایل نیز حذف می‌شوند.</p><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="import_holidays">' . csrf_field() . '<label class="file-input">فایل تقویم<input type="file" name="calendar_file" accept=".csv,.ics,.txt,text/csv,text/calendar" required><small>حداکثر ۲ مگابایت؛ تاریخ‌های تکراری به‌روزرسانی می‌شوند.</small></label><button class="button secondary" type="submit">واردکردن تقویم</button></form></section><section class="card category-list holiday-list">';
    foreach ($holidayQuery->fetchAll() as $holiday) {
        echo '<div><span><strong>' . e(jalali_date($holiday['holiday_date'], false)) . ' • ' . e($holiday['title']) . '</strong><small>ثبت‌کننده: ' . e($holiday['full_name'] ?: 'سامانه') . '</small></span><form method="post" data-confirm="این روز تعطیل حذف شود؟">' . csrf_field() . '<input type="hidden" name="action" value="delete_holiday"><input type="hidden" name="holiday_id" value="' . (int) $holiday['id'] . '"><button class="mini-button danger" type="submit">حذف</button></form></div>';
    }
    echo '</div></section>';
    render_footer();
    exit;
}

if ($page === 'new-ticket' && $user && !user_can($user, 'ticket.create')) {
    http_response_code(403);
    render_header('عدم دسترسی', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">عدم دسترسی</span><h1>دسترسی به این بخش مجاز نیست</h1><p>برای دریافت دسترسی با ادمین اصلی سامانه تماس بگیرید.</p></div></section>';
    render_footer();
    exit;
}
if ($page === 'new-ticket') {
    $cdDvdRequestRecord = null;
    $subjectDefault = '';
    $descriptionDefault = '';
    if (($_GET['request'] ?? '') === 'cd_dvd_edit') {
        $candidate = cd_dvd_fetch_record((int) ($_GET['record_id'] ?? 0));
        if ($candidate && !cd_dvd_can_edit_records($user) && cd_dvd_can_view_record($candidate, $user)) {
            $cdDvdRequestRecord = $candidate;
            $subjectDefault = 'درخواست اصلاح رکورد CD/DVD ' . $candidate['media'] . ' ' . $candidate['serial'];
            $descriptionDefault = "لطفاً رکورد CD/DVD زیر را بررسی و در صورت تأیید اصلاح کنید.\n\n"
                . 'شناسه رکورد: ' . $candidate['id'] . "\n"
                . 'رسانه: ' . $candidate['media'] . "\n"
                . 'شماره: ' . $candidate['serial'] . "\n"
                . 'جهت: ' . ($candidate['direction'] === 'IN' ? 'ورود' : 'خروج') . "\n"
                . 'تاریخ: ' . $candidate['date_str'] . "\n"
                . 'شرح فعلی: ' . $candidate['info_desc'] . "\n\n"
                . 'شرح اصلاح موردنیاز: ';
        }
    }
    if ($cdDvdRequestRecord === null) {
        // پیش‌پرکردن متن از آریا؛ خدمت و سایر فیلدها را کاربر خودش در همین فرم انتخاب می‌کند.
        $subjectDefault = mb_substr(trim((string) ($_GET['subject'] ?? '')), 0, 255, 'UTF-8');
        $descriptionDefault = mb_substr(trim((string) ($_GET['description'] ?? '')), 0, 5000, 'UTF-8');
    }
    ticket_service_ensure_schema();
    $categories = db()->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY service_group, name')->fetchAll();
    $clientIp = inventory_client_ip();
    $limitedAssetScope = !is_staff_role((string) $user['role']) && !is_it_agent($user);
    if ($limitedAssetScope) {
        // برای کاربر عادی فقط سیستم‌های متعلق به خودش یا همین کلاینت را حتی در HTML هم بارگذاری کن.
        $assetQuery = db()->prepare('SELECT id, asset_tag, hostname, owner_user_id, ip_address FROM assets WHERE owner_user_id = ? OR (? <> "" AND ip_address = ?) ORDER BY hostname');
        $assetQuery->execute([(int) $user['id'], $clientIp, $clientIp]);
        $allAssets = $assetQuery->fetchAll();
    } else {
        [$assetWhere, $assetParams] = asset_scope($user, 'a');
        $assetQuery = db()->prepare('SELECT a.id, a.asset_tag, a.hostname, a.owner_user_id, a.ip_address FROM assets a ' . ($assetWhere !== '' ? $assetWhere : '') . ' ORDER BY a.hostname');
        $assetQuery->execute($assetParams);
        $allAssets = $assetQuery->fetchAll();
    }
    $clientAssetId = 0;
    $ownAssets = [];
    $otherAssets = [];
    foreach ($allAssets as $assetRow) {
        $isOwner = (int) $assetRow['owner_user_id'] === (int) $user['id'];
        $isHere = $clientIp !== '' && trim((string) $assetRow['ip_address']) === $clientIp;
        if ($isHere && !$clientAssetId) { $clientAssetId = (int) $assetRow['id']; }
        if ($isOwner || $isHere) { $ownAssets[] = $assetRow; } else { $otherAssets[] = $assetRow; }
    }
    $userAssets = array_merge($ownAssets, $otherAssets);
    $autoAssetId = $clientAssetId ?: (count($userAssets) === 1 ? (int) $userAssets[0]['id'] : 0);
    $departments = [];
    // معاونت‌های سازمانی از چارت سازمانی (org_units) خوانده می‌شوند و برای همهٔ نقش‌ها
    // نمایش داده می‌شوند تا درخواست به معاونت انتخاب‌شده مسیردهی شود.
    org_ensure_schema();
    org_ticket_schema_ensure();
    $departments = org_deputy_units();
    ticket_service_ensure_schema();
    $services = db()->query('SELECT id, name, code, service_group, requires_asset, default_priority, default_ticket_type, category_id FROM service_catalog WHERE is_active = 1 ORDER BY service_group, name')->fetchAll();
    $cdDvdServiceId = 0;
    if ($cdDvdRequestRecord) {
        $cdDvdServiceQuery = db()->prepare("SELECT id FROM service_catalog WHERE code = 'SUP-CDDVD' AND is_active = 1 LIMIT 1");
        $cdDvdServiceQuery->execute();
        $cdDvdServiceId = (int) ($cdDvdServiceQuery->fetchColumn() ?: 0);
    }
    $serviceFields = [];
    if ($services) {
        $serviceFieldQuery = db()->prepare('SELECT * FROM service_catalog_fields WHERE service_id = ? ORDER BY sort_order, id');
        foreach ($services as $service) {
            $serviceFieldQuery->execute([(int) $service['id']]);
            $serviceFields[(int) $service['id']] = $serviceFieldQuery->fetchAll();
        }
    }
    // ۱.۳۷.۲ (درخواست کاربر): فرم ثبت درخواست همیشه با حوزهٔ «خدمات کامپیوتری و IT» باز می‌شود.
    // تنها مسیر CD/DVD (که رسیدگی‌اش در پشتیبانی است) روی «پشتیبانی» می‌ماند.
    $ticketDefaultGroup = $cdDvdRequestRecord ? 'support' : 'it';
    render_header('ثبت درخواست', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">درخواست جدید</span><h1>ثبت درخواست</h1><p>ابتدا حوزه خدمت را انتخاب کنید تا فرم و مسیر رسیدگی درست نمایش داده شود.</p></div><a class="button secondary" href="index.php">بازگشت به داشبورد</a></section><section class="card form-card"><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="create_ticket">' . ($cdDvdRequestRecord ? '<input type="hidden" name="cd_dvd_record_id" value="' . (int) $cdDvdRequestRecord['id'] . '">' : '');
    echo '<div class="ticket-number-box" data-next-it="' . e(ticket_number_peek('IT')) . '" data-next-support="' . e(ticket_number_peek('SUP')) . '"><span>شماره درخواست:</span><strong id="ticket-number-preview">' . e($ticketDefaultGroup === 'support' ? ticket_number_peek('SUP') : ticket_number_peek('IT')) . '</strong></div>';
    echo '<div class="form-grid"><label class="full">موضوع درخواست<input name="subject" required maxlength="255" value="' . e($subjectDefault) . '" placeholder="مثلاً: اختلال در اتصال به پرینتر واحد مالی"></label><label>حوزه خدمت<div class="group-combo"><label class="group-option"><input type="radio" name="service_group" value="it"' . ($ticketDefaultGroup === 'it' ? ' checked' : '') . '><span>خدمات کامپیوتری و IT</span></label><label class="group-option"><input type="radio" name="service_group" value="support"' . ($ticketDefaultGroup === 'support' ? ' checked' : '') . '><span>خدمات پشتیبانی</span></label></div></label><input type="hidden" name="category_id" id="category-selector" value=""><label>خدمت از کاتالوگ<select name="service_id" id="service-selector" required><option value="">انتخاب کنید…</option>';
    foreach ($services as $service) {
        $selected = ($cdDvdServiceId > 0 && (int) $service['id'] === $cdDvdServiceId) ? ' selected' : '';
        echo '<option value="' . (int) $service['id'] . '" data-service-group="' . e($service['service_group']) . '" data-category-id="' . (int) ($service['category_id'] ?? 0) . '" data-requires-asset="' . (int) $service['requires_asset'] . '"' . $selected . '>' . e($service['name']) . '</option>';
    }
    echo '</select><small class="field-help">ابتدا حوزه و سپس خدمت را انتخاب کنید؛ دسته‌بندی، اولویت و مسیر رسیدگی خودکار تنظیم می‌شود.</small></label>';
    if ($departments) {
        $myUnitId = function_exists('user_org_unit_id') ? user_org_unit_id($user) : 0;
        $myUnitIsDeputy = false;
        foreach ($departments as $department) {
            if ((int) $department['id'] === $myUnitId) {
                $myUnitIsDeputy = true;
                break;
            }
        }
        echo '<label>معاونت سازمانی درخواست‌کننده<select name="requesting_unit_id" required><option value="">انتخاب کنید…</option>';
        foreach ($departments as $department) {
            echo '<option value="' . (int) $department['id'] . '" ' . ((int) $department['id'] === $myUnitId ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        echo '</select><small class="field-help">' . ($myUnitIsDeputy ? 'معاونت شما به‌صورت پیش‌فرض انتخاب شده است؛ ' : '') . 'درخواست به معاونت انتخاب‌شده ارجاع داده می‌شود.</small></label>';
    } else {
        echo '<label>معاونت سازمانی درخواست‌کننده<input value="هیچ معاونتی در چارت سازمانی تعریف نشده است" readonly></label>';
    }
     echo '<label>اولویت<select name="priority"><option value="">پیش‌فرض خدمت</option><option value="normal">عادی</option><option value="urgent">فوری</option><option value="critical">حیاتی</option></select></label><label class="ticket-asset-field" data-asset-combo data-asset-required-group="it">سیستم مرتبط <span class="auto-label" data-asset-label>پس از انتخاب خدمت مشخص می‌شود</span><input type="text" id="asset-search" class="asset-search" placeholder="جستجوی سیستم (نام رایانه، شماره اموال یا پلاک)" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="asset-results" aria-required="false"><select name="asset_id" id="asset-selector" class="asset-native" tabindex="-1" aria-hidden="true"><option value="">بدون سیستم خاص / انتخاب نشده</option>';
     foreach ($userAssets as $asset) {
          $isCurrent = (int) $asset['id'] === $clientAssetId;
          $tagText = asset_tag_label($asset['asset_tag'], $asset['hostname']);
          if (asset_is_auto_domain_tag($asset['asset_tag'])) { $tagText = ''; }
          echo '<option value="' . (int) $asset['id'] . '" ' . ((int) $asset['id'] === $autoAssetId ? 'selected' : '') . '>' . e(asset_display_name($asset['hostname']) . ($tagText !== '' ? ' - ' . $tagText : '') . ($isCurrent ? ' (سیستم فعلی)' : '')) . '</option>';
     }
       echo '</select><div class="asset-results" id="asset-results" role="listbox" hidden></div><small class="field-help">برای همهٔ خدمات IT، انتخاب سیستم مرتبط از شناسنامهٔ فنی الزامی است. نام رایانه، شمارهٔ اموال یا پلاک را جست‌وجو و از فهرست انتخاب کنید.</small><small class="field-error" data-asset-error hidden></small></label><label class="support-field">محل خدمت / اتاق<span class="auto-label">برای پشتیبانی الزامی</span><input name="support_location" placeholder="ساختمان، طبقه، اتاق یا محل دقیق"></label><label class="support-field">تجهیز یا مورد درخواست<textarea name="support_equipment" rows="2" placeholder="مثلاً صندلی، میز، پرینتر عمومی یا تجهیزات اتاق"></textarea></label>' . ((is_staff_role($user['role']) || is_it_agent($user)) ? '<label>ارجاع به درخواست قبلی (اختیاری، کارشناسان)<input type="number" name="parent_ticket_id" placeholder="شناسه داخلی درخواست"></label>' : '') . '<label class="full">شرح کامل مشکل<textarea name="description" required rows="8" placeholder="چه اتفاقی افتاده؟ از چه زمانی؟ چه پیامی می‌بینید؟">' . e($descriptionDefault) . '</textarea></label>';
      foreach ($serviceFields as $serviceId => $fields) {
          if (!$fields) { continue; }
          echo '<fieldset class="service-fields" data-service-fields="' . $serviceId . '"><legend>اطلاعات تکمیلی خدمت</legend><div class="form-grid">';
          foreach ($fields as $field) {
              $required = (int) $field['is_required'] === 1 ? ' required' : '';
              $name = 'custom[' . e($field['field_key']) . ']';
              $options = json_decode((string) $field['options_json'], true) ?: [];
              if ($field['field_type'] === 'textarea') {
                  echo '<label class="full">' . e($field['label']) . '<textarea name="' . $name . '" rows="3"' . $required . '></textarea></label>';
              } elseif ($field['field_type'] === 'select') {
                  echo '<label>' . e($field['label']) . '<select name="' . $name . '"' . $required . '><option value="">انتخاب کنید</option>';
                  foreach ($options as $option) { echo '<option value="' . e($option) . '">' . e($option) . '</option>'; }
                  echo '</select></label>';
              } else {
                   $inputType = $field['field_type'] === 'number' ? 'number' : 'text';
                   $placeholder = $field['field_type'] === 'date' ? '۱۴۰۵/۰۱/۰۱' : '';
                   echo '<label>' . e($field['label']) . '<input type="' . $inputType . '" name="' . $name . '" placeholder="' . $placeholder . '"' . $required . '></label>';
              }
          }
          echo '</div></fieldset>';
      }
      echo '<label class="full file-input">پیوست اختیاری<input type="file" name="attachment"><small>حداکثر ۸ مگابایت؛ PDF، تصویر، Word، Excel، TXT و ZIP</small></label></div><div class="actions"><button class="button" type="submit">ثبت درخواست</button></div></form></section>';
    render_footer();
    exit;
}

if ($page === 'ticket') {
    $ticket = fetch_ticket((int) ($_GET['id'] ?? 0));
    if (!$ticket || !can_view_ticket($ticket, $user)) {
        http_response_code(404);
        render_header('درخواست پیدا نشد', $user);
        echo '<section class="empty-state"><div class="empty-icon">؟</div><h2>درخواست پیدا نشد</h2><a class="button" href="index.php">بازگشت</a></section>';
        render_footer();
        exit;
    }
    $messagesQuery = db()->prepare('SELECT m.*, u.full_name, u.role FROM ticket_messages m JOIN users u ON u.id = m.user_id WHERE m.ticket_id = ? ORDER BY m.created_at');
    $messagesQuery->execute([$ticket['id']]);
    $messages = $messagesQuery->fetchAll();
    $attachments = [];
    if ($messages) {
        $ids = array_column($messages, 'id');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $attachmentQuery = db()->prepare('SELECT * FROM ticket_attachments WHERE message_id IN (' . $marks . ') ORDER BY id');
        $attachmentQuery->execute($ids);
        foreach ($attachmentQuery->fetchAll() as $attachment) {
            $attachments[(int) $attachment['message_id']][] = $attachment;
        }
    }
    $staff = is_staff_role($user['role']);
    $canManageTicket = $staff;
    $isRequester = (int) $ticket['requester_id'] === (int) $user['id'];
    $requesterView = $isRequester && !$staff;
    $canCancelOwnTicket = ticket_requester_can_cancel($ticket, $user);
    $handlerLevel = ticket_handler_level($ticket, $user);
    $cancelHasRequest = !empty($ticket['cancel_requested_at']);
    $cancelDecidable = $cancelHasRequest
        && in_array((string) $ticket['status'], ticket_requester_cancelable_statuses(), true);
    $cancelStaleClosed = $cancelHasRequest
        && in_array((string) $ticket['status'], ['closed', 'cancelled'], true);
    $cancelRequestPending = $cancelDecidable || $cancelStaleClosed;
    $canDecideCancellation = $cancelRequestPending
        && $handlerLevel !== ''
        && (int) ($ticket['cancel_requested_by'] ?? 0) !== (int) $user['id'];
    $canAssignTicket = $handlerLevel === 'manager';
    $requiresRating = $isRequester && in_array($ticket['status'], ['resolved', 'closed'], true) && !ticket_rating_is_current($ticket);
    render_header('جزئیات ' . ticket_number((int) $ticket['id']), $user);
     echo '<section class="page-heading"><div><a class="back-link" href="index.php">← بازگشت به فهرست</a><h1>' . e($ticket['subject']) . '</h1><div class="ticket-meta"><span>' . ticket_number((int) $ticket['id']) . '</span><span>' . persian_date($ticket['created_at']) . '</span><span class="tag">' . e(ticket_type_label((string) $ticket['ticket_type'])) . '</span>' . ($ticket['service_name'] ? '<span class="tag">' . e($ticket['service_name']) . '</span>' : '') . '<span class="status ' . e($ticket['status']) . '">' . e(status_label($ticket['status'])) . '</span><span class="priority ' . e($ticket['priority']) . '">' . e(priority_label($ticket['priority'])) . '</span></div></div></section><div class="ticket-layout"><section class="conversation">';
    foreach ($messages as $message) {
        if ((int) $message['is_internal'] === 1 && !$staff) {
            continue;
        }
         $messageRole = ((int) $message['is_internal'] === 1) ? 'internal' : (is_staff_role($message['role']) ? 'agent' : ($message['role'] === 'supervisor' ? 'supervisor' : 'user'));
         echo '<article class="message ' . $messageRole . ' ' . ((int) $message['user_id'] === (int) $user['id'] ? 'mine' : '') . '"><div class="message-head"><strong>' . e($message['full_name']) . '</strong><span>' . persian_date($message['created_at']) . '</span></div><div class="message-body">' . nl2br(e($message['body'])) . '</div>';
        if (!empty($attachments[(int) $message['id']])) {
            echo '<div class="attachments">';
            foreach ($attachments[(int) $message['id']] as $attachment) {
                 echo '<a class="attachment" href="index.php?action=download_attachment&id=' . (int) $attachment['id'] . '">📎 ' . e($attachment['original_name']) . '</a>';
            }
            echo '</div>';
        }
        echo '</article>';
    }
     if ($cancelRequestPending) {
        $pendingNote = $cancelStaleClosed
            ? 'این درخواست مربوط به پیش از بسته‌شدن است و باید پاک‌سازی شود.'
            : 'تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.';
        echo '<div class="card cancellation-pending"><h3>درخواست لغو در انتظار بررسی مسئول است</h3><p><strong>دلیل ثبت‌شده:</strong> ' . nl2br(e((string) ($ticket['cancel_request_reason'] ?? ''))) . '</p><small class="muted">درخواست در ' . e(persian_date((string) $ticket['cancel_requested_at'])) . ' ثبت شده است. ' . $pendingNote . '</small></div>';
    } elseif ($canCancelOwnTicket) {
        $cancelNeedsApproval = !ticket_requester_can_cancel_directly($ticket);
        $cancelButton = $cancelNeedsApproval ? 'ثبت درخواست لغو' : 'لغو درخواست پیش از ارجاع';
        $cancelConfirm = $cancelNeedsApproval
            ? 'درخواست لغو ثبت می‌شود؛ تا تصمیم مسئول، گفت‌وگو ادامه دارد اما تغییر وضعیت متوقف است. ادامه می‌دهید؟'
            : 'درخواست پیش از ارجاع به کارشناس فوراً لغو می‌شود؛ سابقه حفظ خواهد شد. ادامه می‌دهید؟';
        $cancelHelp = $cancelNeedsApproval
            ? 'چون درخواست ارجاع شده یا رسیدگی آغاز شده است، ابتدا درخواست شما ثبت می‌شود و مسئول همان حوزه باید آن را تأیید کند؛ تا آن زمان گفت‌وگو ادامه دارد اما تغییر وضعیت و ارجاع متوقف است.'
            : 'چون هنوز به کارشناس ارجاع نشده است، لغو فوراً انجام می‌شود؛ حذف فیزیکی نمی‌شود و تاریخچه می‌ماند.';
        echo '<form class="card cancel-ticket-card" method="post" data-confirm="' . e($cancelConfirm) . '">' . csrf_field() . '<input type="hidden" name="action" value="cancel_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>دیگر به این درخواست نیاز ندارید؟</h3><label>دلیل لغو<textarea name="cancel_reason" required maxlength="1000" rows="2" placeholder="مثلاً درخواست اشتباهی ثبت شده است…"></textarea></label><button class="button danger-button" type="submit">' . e($cancelButton) . '</button><small class="muted">' . e($cancelHelp) . '</small></form>';
    }
    if ($canDecideCancellation) {
        if ($cancelStaleClosed) {
            echo '<form class="card cancellation-decision" method="post">' . csrf_field() . '<input type="hidden" name="action" value="decide_ticket_cancellation"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>پاک‌سازی درخواست لغو قدیمی</h3><p class="muted">درخواست قبلاً ' . e(status_label((string) $ticket['status'])) . ' شده است؛ این درخواست قدیمی باید با ذکر دلیل پاک‌سازی شود.</p><label>یادداشت پاک‌سازی (الزامی)<textarea name="decision_note" required maxlength="1000" rows="2" placeholder="مثلاً: درخواست پیش از بسته‌شدن ثبت شده بود؛ بایگانی شد."></textarea></label><div class="actions"><button class="button secondary" type="submit" name="decision" value="reject">پاک‌سازی درخواست قدیمی</button></div></form>';
        } else {
            echo '<form class="card cancellation-decision" method="post">' . csrf_field() . '<input type="hidden" name="action" value="decide_ticket_cancellation"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>تصمیم مسئول رسیدگی دربارهٔ لغو</h3><label>یادداشت تصمیم (برای رد، نوشتن دلیل الزامی است)<textarea name="decision_note" maxlength="1000" rows="2" placeholder="در صورت رد، توضیح دهید رسیدگی چگونه ادامه می‌یابد."></textarea></label><div class="actions"><button class="button danger-button" type="submit" name="decision" value="approve" data-confirm="درخواست لغو را تأیید کنید؟ با این کار درخواست از صف فعال خارج می‌شود و سابقه حفظ می‌ماند.">تأیید لغو</button><button class="button secondary" type="submit" name="decision" value="reject">رد و ادامهٔ رسیدگی</button></div></form>';
        }
    }
    if ($requiresRating) {
         echo '<form class="card rating-card required-rating" method="post">' . csrf_field() . '<input type="hidden" name="action" value="rate_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>ثبت امتیاز رضایت الزامی است</h3><p class="muted">برای نهایی‌شدن این درخواست، میزان رضایت خود از واحد رسیدگی را ثبت کنید.</p><div class="rating-options">';
         for ($score = 1; $score <= 5; $score++) {
             echo '<label><input type="radio" name="score" value="' . $score . '" required><span>' . $score . '★</span></label>';
         }
         echo '</div><label>نظر اختیاری<textarea name="rating_comment" rows="3" placeholder="اگر توضیحی دارید بنویسید..."></textarea></label><button class="button" type="submit">ثبت امتیاز</button></form>';
     }
     if ($ticket['status'] === 'cancelled') {
         echo '<div class="card"><p class="muted">این درخواست به درخواست ثبت‌کننده لغو شده است. سابقه برای پیگیری حفظ می‌شود؛ برای نیاز جدید، درخواست تازه ثبت کنید.</p></div>';
     } elseif ($requesterView && $ticket['status'] === 'resolved') {
         if (!$requiresRating) {
             echo '<div class="card"><p class="muted">امتیاز شما ثبت شد. درخواست در انتظار تأیید نهایی سوپروایزر است.</p></div>';
         }
         if ($cancelHasRequest) {
             echo '<div class="card"><p class="muted">لغو این درخواست در انتظار تصمیم مسئول است؛ بازگشایی پس از تصمیم او امکان‌پذیر می‌شود.</p></div>';
         } elseif (user_can($user, 'ticket.reply')) {
             echo '<form class="card reply-box" method="post">' . csrf_field() . '<input type="hidden" name="action" value="reopen_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><h3>مشکل برطرف نشده است؟</h3><label>توضیح دهید چه چیزی هنوز برطرف نشده<textarea name="reopen_reason" required rows="3" placeholder="مثلاً: همچنان خطا می‌دهد..."></textarea></label><button class="button secondary" type="submit">مشکل حل نشده، بازگشایی درخواست</button></form>';
         }
     } elseif ($ticket['status'] === 'closed') {
         if (!$requiresRating) {
             echo '<div class="card"><p class="muted">این درخواست بسته شده است. برای ادامه، درخواست جدید ثبت کنید.</p></div>';
         }
     } elseif (!$requiresRating) {
         $afterOptions = ($handlerLevel !== '' && !empty($ticket['assigned_to']) && !$cancelHasRequest) ? array_values(array_intersect(['waiting_user', 'resolved'], ticket_allowed_transitions((string) $ticket['status']))) : [];
         $afterSelect = '';
         if ($afterOptions) {
             $afterSelect = '<label>پس از ارسال<select name="after_status"><option value="">بدون تغییر وضعیت</option>';
             foreach ($afterOptions as $afterOption) {
                 $afterSelect .= '<option value="' . $afterOption . '">' . ($afterOption === 'resolved' ? 'حل شد (منتظر امتیاز کاربر)' : e(status_label($afterOption))) . '</option>';
             }
             $afterSelect .= '</select></label>';
         } elseif ($cancelHasRequest && $handlerLevel !== '') {
             $afterSelect = '<small class="muted">تا تصمیم لغو، تغییر وضعیت از مسیر پاسخ متوقف است؛ پیام شما به‌عنوان گفت‌وگو ثبت می‌شود.</small>';
         }
         echo '<form class="card reply-box" method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="reply"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>پاسخ شما<textarea name="body" required rows="5" placeholder="پیام خود را بنویسید..."></textarea></label><div class="reply-actions"><label class="file-input compact">پیوست<input type="file" name="attachment"></label>' . ($staff ? '<label class="check-label"><input type="checkbox" name="is_internal" value="1"> یادداشت داخلی</label>' : '') . $afterSelect . '<button class="button" type="submit">ارسال پاسخ</button></div></form>';
     }
     echo '</section>';
        echo '<aside class="ticket-sidebar"><div class="card detail-card"><h3>اطلاعات درخواست</h3><dl><dt>ثبت‌کننده</dt><dd>' . e($ticket['requester_name']) . '</dd><dt>حوزه</dt><dd>' . (($ticket['service_group'] ?? 'it') === 'support' ? 'پشتیبانی' : 'IT') . '</dd><dt>نوع</dt><dd>' . e(ticket_type_label((string) $ticket['ticket_type'])) . '</dd><dt>خدمت</dt><dd>' . e($ticket['service_name'] ?: 'بدون خدمت') . '</dd><dt>واحد رسیدگی</dt><dd>' . e(($ticket['service_group'] ?? 'it') === 'support' ? 'واحد پشتیبانی' : 'واحد فناوری اطلاعات') . '</dd><dt>معاونت سازمانی</dt><dd>' . e($ticket['requesting_unit_name'] ?: ($ticket['department_name'] ?: 'بدون معاونت')) . '</dd><dt>دسته‌بندی</dt><dd>' . e($ticket['category_name'] ?: 'بدون دسته‌بندی') . '</dd><dt>کارشناس</dt><dd>' . e($ticket['assignee_name'] ?: 'تخصیص داده نشده') . '</dd>' . (($ticket['service_group'] ?? 'it') === 'support' ? '<dt>محل خدمت</dt><dd>' . e($ticket['support_location'] ?: 'ثبت نشده') . '</dd><dt>تجهیز / مورد</dt><dd>' . e($ticket['support_equipment'] ?: 'ثبت نشده') . '</dd>' : '<dt>سیستم</dt><dd>' . e($ticket['hostname'] ? asset_display_name($ticket['hostname']) . (asset_is_auto_domain_tag($ticket['asset_tag']) ? '' : ' (' . $ticket['asset_tag'] . ')') : 'بدون سیستم مرتبط') . '</dd>') . '<dt>مهلت SLA</dt><dd>' . e($ticket['due_at'] ? persian_date($ticket['due_at']) : 'ثبت نشده') . '</dd><dt>مهلت پاسخ OLA</dt><dd>' . e($ticket['ola_response_due_at'] ? persian_date($ticket['ola_response_due_at']) : 'ثبت نشده') . '</dd><dt>مهلت حل OLA</dt><dd>' . e($ticket['ola_due_at'] ? persian_date($ticket['ola_due_at']) : 'ثبت نشده') . '</dd>' . (ticket_rating_is_current($ticket) ? '<dt>رضایت کاربر</dt><dd>' . e((string) $ticket['rating_score']) . ' از ۵</dd>' : '') . '</dl></div>';
    if ($staff && empty($ticket['assigned_to']) && in_array($ticket['status'], ['new', 'manager_review'], true) && is_assignable_role((string) $user['role']) && !$cancelHasRequest) {
        echo '<div class="card detail-card"><h3>برداشتن درخواست</h3><p class="muted">این درخواست هنوز کارشناس مسئول ندارد.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="claim_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><button class="button wide" type="submit">برداشتن و شروع رسیدگی</button></form></div>';
    }
    if ($canManageTicket && $handlerLevel !== '' && $cancelHasRequest && !in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
        echo '<div class="card detail-card"><h3>مدیریت درخواست</h3><p class="muted">تا تصمیم دربارهٔ درخواست لغو، تغییر وضعیت، اولویت و ارجاع متوقف است. گفت‌وگو از ستون مکالمه ادامه دارد.</p></div>';
    }
    if ($canManageTicket && $handlerLevel !== '' && !$cancelHasRequest && !in_array((string) $ticket['status'], ['closed', 'cancelled'], true)) {
         if ($canAssignTicket) {
              $ticketGroupForList = (string) ($ticket['service_group'] ?? 'it');
              $agentSql = 'SELECT u.id, u.full_name, u.username FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND u.role IN ("agent", "manager", "support_manager", "primary_admin", "admin") AND (u.role = "admin" OR (u.role = "primary_admin" AND ? = "it") OR (u.role = "support_manager" AND ? = "support") OR hu.code = ?)';
              $agentParams = [$ticketGroupForList, $ticketGroupForList, $ticketGroupForList];
              if (($user['role'] ?? '') === 'manager' && $ticketGroupForList === 'support' && (int) ($user['department_id'] ?? 0) > 0) {
                  $agentSql .= ' AND u.department_id = ?';
                  $agentParams[] = (int) $user['department_id'];
              }
              $agentSql .= ' ORDER BY u.full_name';
              $agentQuery = db()->prepare($agentSql);
              $agentQuery->execute($agentParams);
              $agents = $agentQuery->fetchAll();
        }
        echo '<div class="card detail-card"><h3>مدیریت درخواست</h3><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="update_ticket"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><label>وضعیت<select name="status">';
        $statusChoices = array_values(array_unique(array_merge([(string) $ticket['status']], array_filter(ticket_allowed_transitions((string) $ticket['status']), fn ($allowedStatus) => $canAssignTicket || in_array($allowedStatus, ['in_progress', 'waiting_user', 'resolved'], true)))));
        foreach ($statusChoices as $status) {
            echo '<option value="' . $status . '" ' . ($ticket['status'] === $status ? 'selected' : '') . '>' . status_label($status) . '</option>';
        }
        echo '</select></label>';
        if ($canAssignTicket) {
            echo '<label>اولویت<select name="priority">';
            foreach (['normal','urgent','critical'] as $priority) {
                echo '<option value="' . $priority . '" ' . ($ticket['priority'] === $priority ? 'selected' : '') . '>' . priority_label($priority) . '</option>';
            }
            echo '</select></label>';
        } else {
            echo '<input type="hidden" name="priority" value="' . e((string) $ticket['priority']) . '">';
        }
        if ($canAssignTicket) {
            echo '<label>ارجاع به<select name="assigned_to"><option value="">بدون تخصیص</option>';
            if (!empty($ticket['assigned_to']) && !in_array((int) $ticket['assigned_to'], array_map('intval', array_column($agents, 'id')), true)) {
                echo '<option value="' . (int) $ticket['assigned_to'] . '" selected>' . e((string) ($ticket['assignee_name'] ?? '')) . '</option>';
            }
            foreach ($agents as $agent) {
                echo '<option value="' . (int) $agent['id'] . '" ' . ((int) $ticket['assigned_to'] === (int) $agent['id'] ? 'selected' : '') . '>' . e($agent['full_name']) . '</option>';
            }
            echo '</select></label>';
        } else {
            echo '<input type="hidden" name="assigned_to" value="' . (int) $ticket['assigned_to'] . '"><p class="muted">ارجاع درخواست فقط توسط مدیر واحد انجام می‌شود.</p>';
        }
        echo '<label>پیام همراه تغییر (برای «در انتظار پاسخ کاربر» و «حل‌شده» الزامی)<textarea name="update_note" rows="3" maxlength="5000" placeholder="برای انتظار: سؤال روشن؛ برای حل‌شده: شرح راه‌حل"></textarea><small class="field-help">این پیام برای ثبت‌کننده قابل مشاهده است و در مکالمه ذخیره می‌شود.</small></label>';
        echo '<button class="button wide" type="submit">ذخیره تغییرات</button></form></div>';
    }
    echo '</aside></div>';
    $eventsQuery = db()->prepare('SELECT e.*, u.full_name FROM ticket_events e LEFT JOIN users u ON u.id = e.user_id WHERE e.ticket_id = ? ORDER BY e.created_at DESC');
    $eventsQuery->execute([$ticket['id']]);
    echo '<section class="card event-log"><h3>ردگیری و سوابق انجام کار</h3><div class="event-list">';
    foreach ($eventsQuery->fetchAll() as $event) {
        $eventText = ['created' => 'درخواست ثبت شد', 'assigned' => 'درخواست به کارشناس ارجاع شد', 'status_changed' => 'وضعیت درخواست تغییر کرد', 'reply' => 'پاسخ ثبت شد', 'internal_note' => 'یادداشت داخلی ثبت شد', 'claimed' => 'درخواست توسط کارشناس برداشته شد', 'rated' => 'امتیاز رضایت کاربر ثبت شد', 'reopened_by_user' => 'کاربر اعلام کرد مشکل حل نشده است', 'supervisor_approved' => 'تأیید نهایی و بسته‌شدن', 'supervisor_rework' => 'برگشت برای اصلاح توسط سوپروایزر', 'supervisor_reopened' => 'بازگشایی رسمی توسط سوپروایزر', 'cancelled_by_requester' => 'لغو مستقیم توسط ثبت‌کننده پیش از ارجاع', 'cancellation_requested' => 'درخواست لغو در انتظار تصمیم مسئول', 'cancellation_approved' => 'تأیید لغو توسط مسئول رسیدگی', 'cancellation_rejected' => 'رد درخواست لغو و ادامهٔ رسیدگی', 'auto_assigned' => 'ارجاع خودکار به مسئول حوزه', 'auto_closed' => 'بسته‌شدن خودکار (بدون امتیاز کاربر)', 'bulk_status_changed' => 'تغییر وضعیت گروهی', 'priority_changed' => 'اولویت تغییر کرد'][$event['event_type']] ?? $event['event_type'];
        echo '<div class="event-item"><span class="event-dot"></span><div><strong>' . e($eventText) . '</strong><small>' . e($event['full_name'] ?: 'سامانه') . ' • ' . persian_date($event['created_at']) . '</small></div><em>' . e(status_label((string) $event['to_status'])) . '</em></div>';
    }
    echo '</div></section>';
    render_footer();
    exit;
}

if ($page === 'analytics') {
    $analyticsUser = require_permission('analytics.view');
    [$analyticsScope, $analyticsParams] = ticket_scope($analyticsUser);
    $statusQuery = db()->prepare('SELECT t.status, COUNT(*) AS total FROM tickets t ' . $analyticsScope . ' GROUP BY t.status ORDER BY total DESC');
    $statusQuery->execute($analyticsParams);
    $statusRows = $statusQuery->fetchAll();
    $priorityQuery = db()->prepare('SELECT t.priority, COUNT(*) AS total FROM tickets t ' . $analyticsScope . ' GROUP BY t.priority ORDER BY total DESC');
    $priorityQuery->execute($analyticsParams);
    $priorityRows = $priorityQuery->fetchAll();
    $departmentQuery = db()->prepare('SELECT COALESCE(d.name, "بدون معاونت") AS label, COUNT(*) AS total FROM tickets t LEFT JOIN departments d ON d.id = t.department_id ' . $analyticsScope . ' GROUP BY t.department_id, d.name ORDER BY total DESC LIMIT 10');
    $departmentQuery->execute($analyticsParams);
    $departmentRows = $departmentQuery->fetchAll();
    $ratingConditions = ['(u.is_it_agent = 1 OR u.role = "agent")', 'u.is_active = 1'];
    $ratingParams = [];
    if (!is_global_ticket_role($analyticsUser['role'])) {
        $ratingConditions[] = 'hu.code = ?';
        $ratingParams[] = ($analyticsUser['role'] ?? '') === 'support_manager' ? 'support' : user_service_group($analyticsUser);
        if (is_department_scoped_role($analyticsUser['role']) && (int) ($analyticsUser['department_id'] ?? 0) > 0) {
            $ratingConditions[] = 'rt.department_id = ?';
            $ratingParams[] = (int) $analyticsUser['department_id'];
        }
    }
    $agentQuery = db()->prepare('SELECT u.full_name AS label, COUNT(tr.id) AS ratings, COALESCE(AVG(tr.score), 0) AS average FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id LEFT JOIN ticket_ratings tr ON tr.agent_id = u.id LEFT JOIN tickets rt ON rt.id = tr.ticket_id WHERE ' . implode(' AND ', $ratingConditions) . ' GROUP BY u.id ORDER BY average DESC, ratings DESC LIMIT 10');
    $agentQuery->execute($ratingParams);
    $agentRows = $agentQuery->fetchAll();
    $chart = static function (string $title, array $rows, callable $label, callable $value, string $suffix = ''): string {
        $max = 1;
        foreach ($rows as $row) { $max = max($max, (float) $value($row)); }
        $html = '<section class="card chart-card"><div class="chart-title"><h2>' . e($title) . '</h2><span>نمای تحلیلی</span></div>';
        if (!$rows) { return $html . '<p class="muted">داده‌ای برای نمایش وجود ندارد.</p></section>'; }
        foreach ($rows as $row) { $amount = (float) $value($row); $width = max(3, (int) round(($amount / $max) * 100)); $html .= '<div class="chart-row"><div class="chart-label"><span>' . e((string) $label($row)) . '</span><b>' . e(rtrim(rtrim(number_format($amount, 1, '.', ''), '0'), '.') . $suffix) . '</b></div><div class="chart-track"><i style="width:' . $width . '%"></i></div></div>'; }
        return $html . '</section>';
    };
    render_header('تحلیل و چارت‌ها', $analyticsUser);
     echo '<section class="page-heading"><div><span class="eyebrow">نمای تحلیلی مدیریتی</span><h1>تحلیل عملکرد سامانه</h1><p>توزیع وضعیت، اولویت، معاونت و رضایت از کارشناسان هر واحد.</p></div><a class="button secondary" href="index.php?page=reports">گزارش فیلترپذیر</a></section><section class="charts-grid">';
    echo $chart('توزیع وضعیت درخواست‌ها', $statusRows, static fn (array $row): string => status_label((string) $row['status']), static fn (array $row): float => (float) $row['total']);
    echo $chart('توزیع اولویت‌ها', $priorityRows, static fn (array $row): string => priority_label((string) $row['priority']), static fn (array $row): float => (float) $row['total']);
    echo $chart('درخواست بر اساس معاونت', $departmentRows, static fn (array $row): string => (string) $row['label'], static fn (array $row): float => (float) $row['total']);
    echo $chart('میانگین رضایت کارشناسان', $agentRows, static fn (array $row): string => (string) $row['label'] . ' (' . (int) $row['ratings'] . ' نظر)', static fn (array $row): float => (float) $row['average'], ' از ۵');
    echo '</section><section class="card analytics-note"><strong>راهنمای تحلیل</strong><span>امتیاز رضایت فقط پس از حل درخواست و توسط ثبت‌کننده ثبت می‌شود. گزارش‌های این صفحه بر اساس سطح دسترسی شما (حوزه و معاونت) فیلتر می‌شوند.</span></section>';
    render_footer();
    exit;
}

if ($page === 'reports') {
    $reportUser = require_permission('reports.view');
    $reportMissingColumns = db_missing_columns('tickets', ['subject', 'description', 'status', 'priority', 'requester_id', 'department_id', 'service_group', 'created_at', 'updated_at', 'assigned_to', 'ticket_type', 'assigned_at', 'first_response_at', 'resolved_at', 'closed_at', 'due_at', 'sla_minutes']);
    if ($reportMissingColumns) {
        render_header('گزارش‌ها', $reportUser);
        echo '<section class="card empty-state"><h2>ساختار گزارش‌ها کامل نیست.</h2><p>این نسخه به ستون‌های جدید درخواست نیاز دارد. فایل‌های Migration را به‌ترتیب روی همان دیتابیس اجرا کنید، سپس صفحه را تازه‌سازی کنید.</p><p><code>upgrade-1.2-itsm.sql</code> و در صورت نیاز <code>upgrade-1.8-routing.sql</code></p><p class="muted">ستون‌های ناقص: ' . e(implode(', ', $reportMissingColumns)) . '</p><a class="button" href="index.php">بازگشت به داشبورد</a></section>';
        render_footer();
        exit;
    }
    [$where, $params] = report_query_filters($reportUser);
    $summaryQuery = db()->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(t.status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending")), 0) AS active, COALESCE(SUM(t.status = "resolved"), 0) AS resolved, COALESCE(SUM(t.status = "closed"), 0) AS closed, COALESCE(SUM(t.status = "cancelled"), 0) AS cancelled, COALESCE(SUM(t.status NOT IN ("resolved", "closed", "cancelled") AND t.sla_paused_at IS NULL AND t.due_at IS NOT NULL AND t.due_at < NOW()), 0) AS overdue, AVG(CASE WHEN t.resolved_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at) END) AS avg_resolution FROM tickets t LEFT JOIN users r ON r.id = t.requester_id ' . $where);
    $summaryQuery->execute($params);
    $summary = $summaryQuery->fetch() ?: ['total' => 0, 'active' => 0, 'resolved' => 0, 'closed' => 0, 'cancelled' => 0, 'overdue' => 0, 'avg_resolution' => 0];
    $reportPerPage = 100;
    $reportPage = max(1, (int) ($_GET['p'] ?? 1));
    $reportPages = max(1, (int) ceil(((int) $summary['total']) / $reportPerPage));
    $reportPage = min($reportPage, $reportPages);
    $reportOffset = ($reportPage - 1) * $reportPerPage;
     $rowsQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $where . ' ORDER BY t.created_at DESC LIMIT ' . $reportPerPage . ' OFFSET ' . $reportOffset);
    $rowsQuery->execute($params);
    $reportRows = $rowsQuery->fetchAll();
    $departments = is_global_ticket_role($reportUser['role']) ? db()->query('SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll() : [];
    $queryString = http_build_query(array_filter($_GET, static fn ($value, $key): bool => $key !== 'page' && $key !== 'action' && $value !== '', ARRAY_FILTER_USE_BOTH));
    render_header('گزارش‌ها', $reportUser);
     echo '<section class="page-heading"><div><span class="eyebrow">کنترل عملکرد پشتیبانی</span><h1>گزارش و پیگیری</h1><p>گزارش‌ها را فیلتر کنید و خروجی قابل استفاده در Excel بگیرید.</p></div><a class="button secondary" href="index.php?action=export_report' . ($queryString ? '&' . e($queryString) : '') . '">خروجی Excel</a></section><section class="card filter-card"><form method="get"><input type="hidden" name="page" value="reports"><div class="filter-grid"><label>از تاریخ جلالی<input type="text" name="from" placeholder="۱۴۰۵/۰۱/۰۱" value="' . e($_GET['from'] ?? '') . '"></label><label>تا تاریخ جلالی<input type="text" name="to" placeholder="۱۴۰۵/۰۱/۳۱" value="' . e($_GET['to'] ?? '') . '"></label><label>وضعیت<select name="status"><option value="">همه وضعیت‌ها</option>';
    // گزینه‌های گروهی هم اضافه شدند تا کارت‌های داشبورد (?status=active/resolved/all) در فرم دیده شوند
    foreach (['all' => 'همهٔ درخواست‌ها', 'active' => 'باز و در حال پیگیری', 'new' => 'جدید', 'manager_review' => null, 'assigned' => null, 'in_progress' => null, 'waiting_user' => null, 'pending' => null, 'resolved' => null, 'closed' => null, 'cancelled' => null] as $status => $customLabel) {
        echo '<option value="' . $status . '" ' . ((string) ($_GET['status'] ?? '') === $status ? 'selected' : '') . '>' . e($customLabel ?? status_label($status)) . '</option>';
    }
    echo '</select></label>';
    if ($departments) {
        echo '<label>واحد<select name="department_id"><option value="">همه واحدها</option>';
        foreach ($departments as $department) {
            echo '<option value="' . (int) $department['id'] . '" ' . ((int) ($_GET['department_id'] ?? 0) === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
        }
        echo '</select></label>';
    }
    echo '<label>اولویت<select name="priority"><option value="">همه اولویت‌ها</option>';
     foreach (['normal','urgent','critical'] as $priority) {
        echo '<option value="' . $priority . '" ' . ((string) ($_GET['priority'] ?? '') === $priority ? 'selected' : '') . '>' . priority_label($priority) . '</option>';
    }
     echo '</select></label><label>حوزه خدمت<select name="service_group"><option value="">همه حوزه‌ها</option><option value="it" ' . ((string) ($_GET['service_group'] ?? '') === 'it' ? 'selected' : '') . '>IT</option><option value="support" ' . ((string) ($_GET['service_group'] ?? '') === 'support' ? 'selected' : '') . '>پشتیبانی</option></select></label><label>نوع درخواست<select name="ticket_type"><option value="">همه انواع</option>';
     foreach (['incident','request','problem','change'] as $ticketType) { echo '<option value="' . $ticketType . '" ' . ((string) ($_GET['ticket_type'] ?? '') === $ticketType ? 'selected' : '') . '>' . ticket_type_label($ticketType) . '</option>'; }
     echo '</select></label><label class="filter-search">جست‌وجو<input name="q" value="' . e($_GET['q'] ?? '') . '" placeholder="عنوان، شرح یا ثبت‌کننده"></label><button class="button" type="submit">اعمال فیلتر</button></div></form></section><section class="stat-grid report-stats"><div class="stat-card accent"><span class="stat-icon">▣</span><div><strong>' . (int) $summary['total'] . '</strong><small>کل درخواست‌ها</small></div></div><div class="stat-card"><span class="stat-icon blue">◈</span><div><strong>' . (int) $summary['active'] . '</strong><small>باز و در حال انجام</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong>' . ((int) $summary['resolved'] + (int) $summary['closed']) . '</strong><small>حل‌شده یا بسته</small></div></div><div class="stat-card"><span class="stat-icon gray">×</span><div><strong>' . (int) $summary['cancelled'] . '</strong><small>لغوشده</small></div></div><div class="stat-card"><span class="stat-icon gray">◷</span><div><strong>' . ($summary['avg_resolution'] ? round((float) $summary['avg_resolution'] / 60, 1) . ' ساعت' : '-') . '</strong><small>میانگین زمان حل</small></div></div><div class="stat-card"><span class="stat-icon" style="background:#fef3f2;color:#b42318">!</span><div><strong>' . (int) $summary['overdue'] . '</strong><small>عبور از SLA</small></div></div></section><section class="card ticket-table report-table"><div class="table-head"><span>درخواست</span><span>واحد</span><span>ثبت‌کننده</span><span>وضعیت</span><span>زمان ثبت</span></div>';
    foreach ($reportRows as $row) {
         echo '<a class="table-row" href="index.php?page=ticket&id=' . (int) $row['id'] . '"><span class="ticket-title"><b>' . e(ticket_number((int) $row['id'])) . '</b><strong>' . e($row['subject']) . '</strong><small>' . e(ticket_type_label($row['ticket_type'])) . ' • ' . e($row['assignee_name'] ?: 'بدون کارشناس') . '</small></span><span>' . e($row['department_name'] ?: 'بدون واحد') . '</span><span>' . e($row['requester_name']) . '</span><span><em class="status ' . e($row['status']) . '">' . e(status_label($row['status'])) . '</em></span><span class="date-cell">' . persian_date($row['created_at']) . '</span></a>';
    }
    echo '</section>';
    if ($reportPages > 1) {
        $reportBaseArgs = array_filter($_GET, static fn ($value, $key): bool => $key !== 'p' && $key !== 'page' && $value !== '', ARRAY_FILTER_USE_BOTH);
        echo '<nav class="pagination" aria-label="صفحه‌بندی گزارش">';
        for ($reportNav = max(1, $reportPage - 3); $reportNav <= min($reportPages, $reportPage + 3); $reportNav++) {
            $reportArgs = array_merge(['page' => 'reports'], $reportBaseArgs, ['p' => $reportNav]);
            echo '<a class="' . ($reportNav === $reportPage ? 'active' : '') . '" href="index.php?' . e(http_build_query($reportArgs)) . '">' . number_format($reportNav) . '</a>';
        }
        echo '</nav>';
    }
    render_footer();
    exit;
}

if ($page === 'supervisor') {
    $supervisor = require_permission('supervisor.panel');
    [$where, $params] = ticket_scope($supervisor);
    $summaryQuery = db()->prepare('SELECT COUNT(*) AS total, SUM(status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending")) AS active, SUM(status = "resolved") AS awaiting_approval, SUM(status = "closed") AS closed, SUM(status = "cancelled") AS cancelled FROM tickets t ' . $where);
    $summaryQuery->execute($params);
    $summary = $summaryQuery->fetch() ?: ['total' => 0, 'active' => 0, 'awaiting_approval' => 0, 'closed' => 0, 'cancelled' => 0];
    $supervisorPerPage = 100;
    $supervisorPage = max(1, (int) ($_GET['p'] ?? 1));
    $supervisorPages = max(1, (int) ceil(((int) $summary['total']) / $supervisorPerPage));
    $supervisorPage = min($supervisorPage, $supervisorPages);
    $supervisorOffset = ($supervisorPage - 1) * $supervisorPerPage;
     $ticketsQuery = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name, tr.score AS rating_score, tr.created_at AS rating_at FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id ' . $where . ' ORDER BY CASE WHEN t.status = "resolved" THEN 0 WHEN t.status IN ("new", "manager_review", "assigned", "in_progress", "waiting_user", "pending") THEN 1 ELSE 2 END, t.updated_at DESC LIMIT ' . $supervisorPerPage . ' OFFSET ' . $supervisorOffset);
    $ticketsQuery->execute($params);
    $supervisorRows = $ticketsQuery->fetchAll();
    render_header('پنل سوپروایزر', $supervisor);
    echo '<section class="page-heading"><div><span class="eyebrow">نظارت سراسری</span><h1>پنل سوپروایزر</h1><p>تمام مراحل از ثبت تا انجام و تأیید نهایی را کنترل کنید.</p></div><a class="button secondary" href="index.php?page=reports">گزارش کامل</a></section><section class="stat-grid"><div class="stat-card accent"><span class="stat-icon">▣</span><div><strong>' . (int) $summary['total'] . '</strong><small>کل درخواست‌ها</small></div></div><div class="stat-card"><span class="stat-icon blue">◈</span><div><strong>' . (int) $summary['active'] . '</strong><small>در گردش</small></div></div><div class="stat-card"><span class="stat-icon" style="background:#fff8e7;color:#9a6700">✓</span><div><strong>' . (int) $summary['awaiting_approval'] . '</strong><small>منتظر تأیید شما</small></div></div><div class="stat-card"><span class="stat-icon green">✓</span><div><strong>' . (int) $summary['closed'] . '</strong><small>بسته‌شده</small></div></div><div class="stat-card"><span class="stat-icon gray">×</span><div><strong>' . (int) $summary['cancelled'] . '</strong><small>لغوشده</small></div></div></section><section class="card supervisor-list"><div class="table-head"><span>درخواست</span><span>واحد</span><span>کارشناس</span><span>وضعیت</span><span>عملیات</span></div>';
    foreach ($supervisorRows as $ticket) {
        echo '<div class="table-row supervisor-row"><span class="ticket-title"><b>' . e(ticket_number((int) $ticket['id'])) . '</b><a href="index.php?page=ticket&id=' . (int) $ticket['id'] . '"><strong>' . e($ticket['subject']) . '</strong></a><small>' . e($ticket['requester_name']) . '</small>' . (!empty($ticket['cancel_requested_at']) ? '<small class="warning-text">درخواست لغو در انتظار بررسی</small>' : '') . '</span><span>' . e($ticket['department_name'] ?: 'بدون واحد') . '</span><span>' . e($ticket['assignee_name'] ?: 'بدون کارشناس') . '</span><span><em class="status ' . e($ticket['status']) . '">' . e(status_label($ticket['status'])) . '</em></span><span class="supervisor-actions">';
        if ($ticket['status'] === 'resolved') {
            if (ticket_rating_is_current($ticket)) {
                echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="approve"><button class="button" type="submit">تأیید و بستن</button></form>';
            } else {
                echo '<small class="muted">در انتظار امتیاز کاربر</small>';
            }
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="supervisor_decision"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="decision" value="rework"><input type="text" name="supervisor_note" required maxlength="500" placeholder="دلیل برگشت برای اصلاح"><button class="button secondary" type="submit">برگشت برای اصلاح</button></form>';
         } elseif ($ticket['status'] === 'closed') {
             echo '<a class="button secondary" href="index.php?page=ticket&id=' . (int) $ticket['id'] . '">مشاهده</a><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="supervisor_reopen"><input type="hidden" name="ticket_id" value="' . (int) $ticket['id'] . '"><input type="hidden" name="supervisor_note" value="بازگشایی رسمی برای ادامه رسیدگی"><button class="button secondary" type="submit">بازگشایی رسمی</button></form>';
         } else {
             echo '<a class="button secondary" href="index.php?page=ticket&id=' . (int) $ticket['id'] . '">مشاهده</a>';
        }
        echo '</span></div>';
    }
    echo '</section>';
    if ($supervisorPages > 1) {
        echo '<nav class="pagination" aria-label="صفحه‌بندی سوپروایزر">';
        for ($supervisorNav = max(1, $supervisorPage - 3); $supervisorNav <= min($supervisorPages, $supervisorPage + 3); $supervisorNav++) {
            echo '<a class="' . ($supervisorNav === $supervisorPage ? 'active' : '') . '" href="index.php?page=supervisor&p=' . $supervisorNav . '">' . number_format($supervisorNav) . '</a>';
        }
        echo '</nav>';
    }
    render_footer();
    exit;
}

if ($page === 'inventory') {
    $inventoryUser = require_permission('inventory.view');
    if (!is_global_ticket_role($inventoryUser['role']) && user_service_group($inventoryUser) !== 'it') {
        http_response_code(403);
        exit('شناسنامه فنی سیستم‌ها فقط در حوزه IT قابل دسترسی است.');
    }
    [$inventoryScope, $inventoryParams] = asset_scope($inventoryUser);
    $inventoryId = (int) ($_GET['id'] ?? 0);
    if ($inventoryId <= 0 && user_can($inventoryUser, 'assets.view')) {
        redirect('index.php?page=assets');
    }
    if ($inventoryId <= 0) {
        asset_profile_ensure_schema();
        $inventoryQuery = db()->prepare('SELECT a.id, a.asset_tag, a.hostname, a.operating_system, a.last_inventory_at, d.name AS department_name, p.ad_online, p.ad_scanned_at FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN asset_profiles p ON p.asset_id = a.id ' . $inventoryScope . ' ORDER BY a.updated_at DESC LIMIT 200');
        $inventoryQuery->execute($inventoryParams);
        render_header('شناسنامه سیستم‌ها', $inventoryUser);
          echo '<section class="page-heading"><div><span class="eyebrow">جزئیات سیستم</span><h1>سیستم‌های شناسایی‌شده</h1><p>برای تکمیل اطلاعات دستی یا مشاهده تاریخچه، سیستم موردنظر را انتخاب کنید.</p></div><div class="actions">' . (user_can($inventoryUser, 'domain.import') ? '<form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="import_domain_assets"><button class="button secondary" type="submit">ورود کامپیوترهای دامنه</button></form>' : '') . '<a class="button secondary" href="index.php?page=assets">شناسنامه‌ها</a></div></section><section class="card ticket-table"><div class="table-head"><span>سیستم</span><span>معاونت</span><span>سیستم‌عامل</span><span>آخرین برداشت</span></div>';
         while ($asset = $inventoryQuery->fetch()) {
             $isOnline = (int) ($asset['ad_online'] ?? 0) === 1;
             $wasScanned = (string) ($asset['ad_scanned_at'] ?? '') !== '';
             if ($isOnline) {
                 $netBadge = '<small class="net-badge online">آنلاین</small>';
             } elseif ($wasScanned) {
                 $netBadge = '<small class="net-badge offline">آفلاین</small>';
             } else {
                 $netBadge = '<small class="net-badge unknown">پینگ نشده</small>';
             }
             echo '<a class="table-row" href="index.php?page=inventory&id=' . (int) $asset['id'] . '"><span class="ticket-title">' . (asset_is_auto_domain_tag($asset['asset_tag']) ? '' : '<b>' . e($asset['asset_tag']) . '</b>') . '<strong' . asset_name_attr($asset['hostname'], $asset['asset_tag']) . '>' . e(asset_display_name($asset['hostname'])) . '</strong>' . (asset_is_auto_domain_tag($asset['asset_tag']) ? '<small class="asset-tag-chip">دامنه</small>' : '') . $netBadge . '</span><span>' . e($asset['department_name'] ?: 'بدون معاونت') . '</span><span>' . e($asset['operating_system']) . '</span><span>' . e($asset['last_inventory_at'] ? persian_date($asset['last_inventory_at']) : 'ثبت نشده') . '</span></a>';
         }
        echo '</section>';
        render_footer();
        exit;
    }
    $inventoryQuery = db()->prepare('SELECT a.*, d.name AS department_name, u.full_name AS owner_name FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN users u ON u.id = a.owner_user_id ' . ($inventoryScope ? $inventoryScope . ' AND a.id = ?' : 'WHERE a.id = ?') . ' LIMIT 1');
    $inventoryQuery->execute(array_merge($inventoryParams, [$inventoryId]));
    $asset = $inventoryQuery->fetch();
    if (!$asset) {
        http_response_code(404);
        render_header('سیستم پیدا نشد', $inventoryUser);
        echo '<section class="empty-state"><h2>سیستم پیدا نشد</h2></section>';
        render_footer();
        exit;
    }
    $profile = asset_profile_fill_from_asset(asset_profile_fetch($inventoryId), $asset);
    $inventorySourceLabels = ['agent' => 'Agent کلاینت', 'domain' => 'اسکن شبکه‌ای', 'server_inventory' => 'استخراج محلی'];
    $lastInventoryLabel = !empty($asset['last_inventory_at'])
        ? persian_date((string) $asset['last_inventory_at']) . ' — ' . ($inventorySourceLabels[(string) ($asset['source'] ?? '')] ?? 'منبع ثبت‌شده')
        : 'هنوز برداشت دارای دادهٔ سخت‌افزاری ثبت نشده است؛ اطلاعات دامنه ممکن است فقط شامل مشخصات پایه باشد.';
    $collectionIssues = asset_profile_collection_status_summary($asset);
    $collectionFailures = array_values(array_filter($collectionIssues, static fn (array $item): bool => ($item['state'] ?? '') === 'failed'));
    $collectionEmpty = array_values(array_filter($collectionIssues, static fn (array $item): bool => ($item['state'] ?? '') === 'empty'));
    $collectionNotAttempted = array_values(array_filter($collectionIssues, static fn (array $item): bool => ($item['state'] ?? '') === 'not_attempted'));
    $collectionStatusHtml = '';
    if ($collectionFailures !== []) {
        $failureNames = array_map(static function (array $item): string {
            $name = (string) ($item['name'] ?? '');
            $lastSuccess = trim((string) ($item['last_success_at'] ?? ''));
            $sourceLabels = ['agent' => 'عامل کلاینت', 'domain' => 'اسکن دامنه', 'server_inventory' => 'استخراج محلی'];
            $source = $sourceLabels[(string) ($item['last_success_source'] ?? '')] ?? '';
            $detail = $lastSuccess !== '' ? ' (آخرین پاسخ موفق منبع: ' . persian_date($lastSuccess) . '، ساعت ' . date('H:i', strtotime($lastSuccess)) . ($source !== '' ? '، ' . $source : '') . ')' : ' (زمان دریافت موفق قبلی نامشخص است)';
            return $name . $detail;
        }, array_slice($collectionFailures, 0, 5));
        $extra = count($collectionFailures) > 5 ? ' و ' . (count($collectionFailures) - 5) . ' مورد دیگر' : '';
        $collectionStatusHtml .= '<small class="muted">در برداشت اخیر ' . count($collectionFailures) . ' منبع داده پاسخ نداد؛ مقادیر قبلی این بخش‌ها در صورت وجود حفظ شده‌اند: ' . e(implode('، ', $failureNames) . $extra) . '.</small>';
    }
    if ($collectionEmpty !== []) {
        $collectionStatusHtml .= '<small class="muted">در برداشت اخیر ' . count($collectionEmpty) . ' پرس‌وجوی موفق ردیفی برنگرداند؛ این با خطای دسترسی یکی نیست و دستهٔ متناظر به‌روز شده است.</small>';
    }
    if ($collectionNotAttempted !== []) {
        $collectionStatusHtml .= '<small class="muted">' . count($collectionNotAttempted) . ' دسته در روشِ برداشت اخیر اصلاً پرس‌وجو نشدند؛ آخرین دادهٔ شناخته‌شدهٔ آن‌ها جداگانه حفظ شده است.</small>';
    }
    render_header('شناسنامه سیستم', $inventoryUser);
    $canExtractThisAsset = user_can($inventoryUser, 'domain.scan');
    $extractPreflight = $canExtractThisAsset ? domain_scan_preflight() : null;
    $canExtractLocalThisAsset = PHP_OS_FAMILY === 'Windows' && user_can($inventoryUser, 'asset.extract') && (is_global_ticket_role($inventoryUser['role']) || user_service_group($inventoryUser) === 'it') && inventory_asset_is_local($asset);
    $assetActions = '';
    if ($canExtractLocalThisAsset) {
        $assetActions .= '<form method="post" class="inline-action inventory-local-extract-form">' . csrf_field() . '<input type="hidden" name="action" value="collect_inventory"><button class="button" type="submit">استخراج محلی همین سیستم</button><label class="muted"><input type="checkbox" name="overwrite" value="1"> بازنویسی فیلدهای خودکارِ پُر</label></form>';
    }
    if ($canExtractThisAsset && $extractPreflight === null) {
        $assetActions .= '<a class="button secondary" href="#extract-inventory">استخراج شبکه‌ای</a>';
        if (user_can($inventoryUser, 'settings.domain')) {
            $assetActions .= '<a class="button secondary" href="index.php?page=settings#domain-scan">تنظیمات اسکن شبکه</a>';
        }
    }
    echo '<section class="page-heading asset-heading"><div><a class="back-link" href="index.php?page=inventory">← بازگشت به سیستم‌ها</a><h1>شناسنامه رایانه ' . e(asset_display_name($asset['hostname'])) . '</h1><p>' . e(asset_name_title($asset['hostname'], $asset['asset_tag']) ?: $asset['asset_tag']) . ' • مالک: ' . e($asset['owner_name'] ?: 'بدون مالک') . '</p><p class="muted">آخرین برداشت سخت‌افزاری: ' . e($lastInventoryLabel) . '</p><small class="muted">ثبت این زمان کامل‌بودن همهٔ فیلدها را تضمین نمی‌کند؛ در برداشت ناقص ممکن است مقدار پیشین باقی بماند. استخراج محلی برای جایگزینی فیلدهای خودکارِ پُر به تأیید «بازنویسی» نیاز دارد.</small>' . $collectionStatusHtml . '</div><div class="actions">' . $assetActions . '<a class="button secondary" href="index.php?page=asset-history&id=' . $inventoryId . '">تاریخچه</a></div></section>';
    if ($canExtractThisAsset) {
        if ($extractPreflight !== null) {
            echo '<section id="extract-inventory" class="card form-card inventory-extract-card"><h2>استخراج شبکه‌ای</h2><p class="alert danger">' . e((string) $extractPreflight) . '</p></section>';
        } else {
            echo '<section id="extract-inventory" class="card form-card inventory-extract-card"><h2>استخراج شبکه‌ای با حساب مجاز</h2><p class="muted">این حساب باید روی همین کامپیوتر دسترسی Administrators محلی داشته باشد. اعتبارنامه فقط برای این نوبت، رمز‌شده در نشست موقت نگهداری و پس از پایان پاک می‌شود؛ در پایگاه‌داده ذخیره نمی‌شود. استفاده از HTTPS الزامی است. اسکن موفق فیلدهای ماشینی شناسنامه را تازه می‌کند و ممکن است مقدار دستی همان فیلدها را جایگزین کند؛ پلاک، پلمپ و اطلاعات اداری خارج از این نگاشت هستند.</p><form method="post" class="form-grid">' . csrf_field() . '<input type="hidden" name="asset_ids[]" value="' . $inventoryId . '"><input type="hidden" name="action" value="domain_scan_selected"><label>نام کاربری ویندوز (DOMAIN\\user)<input name="scan_username" required autocomplete="off" placeholder="COMPANY\\inventory-scan"></label><label>رمز حساب<input type="password" name="scan_password" required autocomplete="new-password"></label><div class="full"><button class="button" type="submit">استخراج اطلاعات این سیستم</button></div></form></section>';
        }
    }
    echo '<form class="inventory-form" method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_inventory_form"><input type="hidden" name="asset_id" value="' . $inventoryId . '">';
    echo asset_profile_render_sections($profile);
    echo '<div class="form-actions-bar"><button class="button" type="submit">ذخیره شناسنامه</button></div></form>';
    render_footer();
    exit;
}

if ($page === 'inventory-diagnostics') {
    $diagUser = require_permission('inventory.diagnostics');
    if (!is_global_ticket_role($diagUser['role']) && user_service_group($diagUser) !== 'it') {
        http_response_code(403);
        exit('این بخش فقط در حوزه IT قابل دسترسی است.');
    }
    $diagAssetId = (int) ($_GET['id'] ?? 0);
    $checks = inventory_diagnostics();
    render_header('تست جمع‌آوری اطلاعات', $diagUser);
    $selfTest = $_SESSION['asset_selftest'] ?? null;
    unset($_SESSION['asset_selftest']);
    echo '<section class="page-heading"><div><a class="back-link" href="' . ($diagAssetId > 0 ? 'index.php?page=inventory&id=' . $diagAssetId : 'index.php?page=assets') . '">← بازگشت</a><h1>تست جمع‌آوری اطلاعات سیستم</h1><p>نتیجه اجرای دستورهای سیستمی از دید همین سرور. اگر ردیفی «ناموفق/صفر» بود، دکمهٔ استخراج هم چیزی پر نمی‌کند.</p></div><div class="actions"><form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="asset_profile_selftest"><input type="hidden" name="asset_id" value="' . $diagAssetId . '"><label class="muted"><input type="checkbox" name="overwrite" value="1"> بازنویسی فیلدهای خودکار موجود</label><button class="button" type="submit">استخراج آزمایشی و نمایش فیلدها</button></form></div></section>';
    if (is_array($selfTest)) {
        echo '<section class="card ticket-table"><div class="table-head"><span>فیلد شناسنامه</span><span>مقدار به‌دست‌آمده</span></div>';
        if ((int) $selfTest['filled'] === 0) {
            echo '<div class="table-row"><span class="muted">هیچ فیلدی به‌دست نیامد. علت: ' . e((string) ($selfTest['error'] !== '' ? $selfTest['error'] : 'خروجی خالی از PowerShell/WMI')) . '</span></div>';
        }
        foreach ((array) $selfTest['keys'] as $row) {
            echo '<div class="table-row"><span><b dir="ltr">' . e((string) $row[0]) . '</b></span><span>' . e((string) $row[1]) . '</span></div>';
        }
        echo '</div><p class="muted" style="margin-top:8px">مجموع: ' . (int) $selfTest['filled'] . ' فیلد پرشدنی، ' . (int) $selfTest['empty'] . ' فیلد خالی (دستی)، امتیاز سخت‌افزار ' . (int) $selfTest['hardware_score'] . (($selfTest['asset_id'] ?? 0) > 0 ? '، شناسنامهٔ سیستم #' . (int) $selfTest['asset_id'] . ' (' . (int) $selfTest['applied'] . ' فیلد ذخیره شد)' : '') . '. زمان: ' . e((string) $selfTest['at']) . ' — سرور: ' . e((string) $selfTest['os']) . '</p></section>';
    }
    echo '<section class="card ticket-table"><div class="table-head"><span>بررسی</span><span>نتیجه</span></div>';
    foreach ($checks as $check) {
        echo '<div class="table-row"><span class="ticket-title"><b>' . e((string) $check[0]) . '</b></span><span style="word-break:break-all">' . e((string) $check[1]) . '</span></div>';
    }
    echo '</section>';
    render_footer();
    exit;
}

if ($page === 'asset-history') {
    $historyUser = require_permission('asset.history');
    [$historyScope, $historyParams] = asset_scope($historyUser);
    $historyId = (int) ($_GET['id'] ?? 0);
    $assetQuery = db()->prepare('SELECT a.*, d.name AS department_name, u.full_name AS owner_name FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN users u ON u.id = a.owner_user_id ' . ($historyScope ? $historyScope . ' AND a.id = ?' : 'WHERE a.id = ?') . ' LIMIT 1');
    $assetQuery->execute(array_merge($historyParams, [$historyId]));
    $asset = $assetQuery->fetch();
    if (!$asset) { http_response_code(404); exit('سیستم پیدا نشد.'); }
    $historyQuery = db()->prepare('SELECT h.*, u.full_name, t.subject, t.id AS ticket_id FROM asset_history_events h LEFT JOIN users u ON u.id = h.user_id LEFT JOIN tickets t ON t.id = h.ticket_id WHERE h.asset_id = ? ORDER BY h.created_at DESC');
    $historyQuery->execute([$historyId]);
    render_header('تاریخچه سیستم', $historyUser);
    echo '<section class="page-heading"><div><a class="back-link" href="index.php?page=inventory&id=' . $historyId . '">← بازگشت به شناسنامه</a><h1>تاریخچه کامل ' . e(asset_display_name($asset['hostname'])) . '</h1><p>' . e(asset_name_title($asset['hostname'], $asset['asset_tag']) ?: $asset['asset_tag']) . ' • ' . e($asset['owner_name'] ?: 'بدون مالک') . '</p></div></section><section class="card event-log system-history"><h3>تمام تغییرات، برداشت‌ها و حل مشکلات</h3><div class="event-list">';
    foreach ($historyQuery->fetchAll() as $event) { echo '<div class="event-item"><span class="event-dot"></span><div><strong>' . e($event['title']) . '</strong><small>' . e($event['full_name'] ?: 'سامانه') . ' • ' . e(persian_date($event['created_at'])) . ($event['subject'] ? ' • درخواست: ' . e(ticket_number((int) $event['ticket_id'])) : '') . '</small><p>' . e($event['details'] ?: '') . '</p></div></div>'; }
    echo '</div></section>';
    render_footer();
    exit;
}

if ($page === 'domain-scan') {
    $scanPageUser = require_permission('domain.scan');
    domain_scan_ensure_schema();
    $scanRunId = (int) ($_GET['run'] ?? 0);
    $scanRun = $scanRunId > 0 ? domain_scan_run($scanRunId) : domain_scan_latest_run();
    $scanEnabled = domain_scan_preflight() === null;
    render_header('اسکن دامنه', $scanPageUser);
    echo '<section class="page-heading"><div><span class="eyebrow">Active Directory</span><h1>اسکن کامپیوترهای دامنه</h1><p>فهرست کامل کامپیوترهای Active Directory وارد صف می‌شود؛ برای هر مقصدِ روشن و قابل‌دسترسی، جزئیات سخت‌افزار، نرم‌افزار، به‌روزرسانی‌ها، دستگاه‌ها، سرویس‌ها و حساب‌های کاربری با WMI/CIM خوانده می‌شود. سیستم خاموش یا غیرقابل‌دسترسی در فهرست می‌ماند اما تا زمان دسترسی، جزئیات زنده ندارد.</p></div><div class="actions"><a class="button secondary" href="index.php?page=settings">تنظیمات</a><a class="button secondary" href="index.php?page=assets">شناسنامه‌ها</a></div></section>';
    if (!$scanEnabled) {
        echo '<section class="card form-card"><p class="alert danger">' . e((string) domain_scan_preflight()) . '</p></section>';
    } else {
        echo '<section class="card form-card"><div class="form-section-head"><h2>شروع اسکن</h2><span class="muted">حساب اتصال: ' . e(domain_scan_account_label()) . '</span></div><form method="post" class="form-grid">' . csrf_field() . '<input type="hidden" name="action" value="domain_scan_start"><label>حساب مجاز ویندوز (DOMAIN\\user)<input name="scan_username" required autocomplete="off" placeholder="COMPANY\\inventory-scan"></label><label>رمز همان حساب<input type="password" name="scan_password" required autocomplete="new-password"></label><div class="full"><button class="button" type="submit">شروع اسکن دامنه</button><small>اعتبارنامه فقط برای همین نوبت استفاده می‌شود؛ در پایگاه‌داده ذخیره نمی‌شود و پس از پایان اسکن از نشست پاک می‌شود. ارسال آن فقط با HTTPS مجاز است.</small></div></form><p class="muted">هر اسکن همهٔ کامپیوترهای دامنه را در صف می‌گذارد؛ کامپیوترهای خاموش در اسکن بعدی دوباره بررسی می‌شوند. حساب باید روی کلاینت‌ها دسترسی Administrators محلی داشته باشد. این مسیر همهٔ رایانه‌های پیدا‌شده در AD را صف می‌کند و محدودیت انتخاب گروهی ۹۰۰تایی ندارد؛ نتیجهٔ واقعی برای هر میزبان در جدول همین صفحه ثبت می‌شود.</p></section>';
    }
    if ($scanRun) {
        $scanTotal = (int) $scanRun['total'];
        $scanDone = (int) $scanRun['done'];
        $scanPercent = $scanTotal > 0 ? min(100, (int) floor($scanDone * 100 / $scanTotal)) : 0;
        echo '<section class="card form-card"><div class="form-section-head"><h2>پیشرفت اسکن #' . (int) $scanRun['id'] . '</h2><span class="muted">وضعیت: ' . e((string) $scanRun['status']) . '</span></div>';
        echo '<div style="background:#1b2740;border-radius:10px;overflow:hidden;height:14px;margin:10px 0"><div id="scan-bar" style="height:14px;width:' . $scanPercent . '%;background:#14b8a6;transition:width .3s"></div></div>';
        echo '<div class="form-grid"><label>کل<input value="' . $scanTotal . '" readonly></label><label>انجام‌شده<input value="' . $scanDone . '" readonly></label><label>آنلاین<input value="' . (int) $scanRun['online'] . '" readonly></label><label>آفلاین<input value="' . (int) $scanRun['offline'] . '" readonly></label><label>خطا<input value="' . (int) $scanRun['failed'] . '" readonly></label></div>';
        echo '<p id="scan-status" class="muted"></p>';
        if ((string) $scanRun['status'] !== 'finished') {
            echo '<form id="scan-next" method="post" hidden>' . csrf_field() . '<input type="hidden" name="action" value="domain_scan_batch"><input type="hidden" name="run_id" value="' . (int) $scanRun['id'] . '"></form>';
        }
        echo '</section>';
        $scanRows = domain_scan_queue_rows((int) $scanRun['id'], 1000, (string) ($_GET['sort_d'] ?? ''), (string) ($_GET['dir_d'] ?? ''));
        // ۱.۳۷ — شکست‌ها بر اساس علت گروه‌بندی و راه‌حل گام‌به‌گام نشان داده می‌شوند تا
        // «RPC/WMI در دسترس نیست» و «دسترسی رد شد» بدون گشتن در لاگ‌ها قابل رفع باشند.
        $scanFailureGroups = domain_scan_failure_summary($scanRows);
        $scanErrorCount = 0;
        foreach ($scanRows as $scanRowForCount) {
            if ((string) ($scanRowForCount['status'] ?? '') === 'error') {
                $scanErrorCount++;
            }
        }
        if ($scanFailureGroups !== []) {
            echo '<section class="card form-card scan-failures"><div class="form-section-head"><h2>دلایل شکست استخراج و راه‌حل</h2><span class="muted">' . count($scanFailureGroups) . ' علت در این نوبت</span></div>';
            if ($scanErrorCount > 0) {
                $retryPreflight = domain_scan_preflight();
                if ($retryPreflight === null) {
                    echo '<form method="post" class="form-grid" style="margin:0 0 14px">' . csrf_field() . '<input type="hidden" name="action" value="domain_scan_retry"><input type="hidden" name="run_id" value="' . (int) $scanRun['id'] . '"><label>حساب مجاز ویندوز (DOMAIN\\user)<input name="scan_username" required autocomplete="off" placeholder="COMPANY\\inventory-scan"></label><label>رمز همان حساب<input type="password" name="scan_password" required autocomplete="new-password"></label><div class="full"><button class="button" type="submit">تلاش دوباره برای ناموفق‌ها (' . $scanErrorCount . ' سیستم)</button></div></form>';
                } else {
                    echo '<p class="alert danger">' . e((string) $retryPreflight) . '</p>';
                }
            }
            echo '<p class="muted">بعد از انجام هر اصلاح روی سرور یا کلاینت‌ها، دکمهٔ «تلاش دوباره» فقط سیستم‌های خطادار را در نوبت تازه‌ای صف می‌کند؛ سیستم‌های موفق دوباره استخراج نمی‌شوند.</p><div class="scan-failure-grid">';
            foreach ($scanFailureGroups as $scanGroup) {
                echo '<article class="scan-failure"><h3>' . e((string) $scanGroup['title']) . ' <span class="scan-failure-count">' . (int) $scanGroup['count'] . ' سیستم</span></h3>';
                if ($scanGroup['hosts'] !== []) {
                    echo '<p class="scan-failure-hosts">' . e(implode('، ', (array) $scanGroup['hosts'])) . (count($scanGroup['hosts']) < (int) $scanGroup['count'] ? ' و …' : '') . '</p>';
                }
                if ($scanGroup['messages'] !== []) {
                    echo '<p class="muted" style="font-size:11px">پیام سیستم: ' . e(implode(' | ', (array) $scanGroup['messages'])) . '</p>';
                }
                echo '<ol>';
                foreach ((array) $scanGroup['remedy'] as $scanStep) {
                    echo '<li>' . e((string) $scanStep) . '</li>';
                }
                echo '</ol></article>';
            }
            echo '</div><p class="muted">ابزارهای کمک: <code>php tools\diag_connection.php --client=IP</code> برای آزمون دسترسی یک سیستم و <code>php tools\diag_connection.php --json</code> برای خلاصهٔ وضعیت. تنظیم حساب و ترتیب پروتکل اتصال: <a href="index.php?page=settings#domain-scan">تنظیمات ← اسکن دامنه</a>.</p></section>';
        }
        if ($scanRows !== []) {
            echo '<section class="card ticket-table" id="scan-rows">' . log_sort_header_row([['name', 'کامپیوتر'], ['status', 'وضعیت'], ['online', 'آنلاین'], ['message', 'پیام']], 'sort_d', 'dir_d', $_GET, (string) ($_GET['sort_d'] ?? ''), (string) ($_GET['dir_d'] ?? '')) . '';
            foreach ($scanRows as $scanRow) {
                $statusLabel = ['pending' => 'در انتظار', 'running' => 'در حال اجرا', 'done' => 'انجام شد', 'offline' => 'آفلاین', 'error' => 'خطا'][(string) $scanRow['status']] ?? (string) $scanRow['status'];
                echo '<div class="table-row"><span class="ticket-title"><b' . asset_name_attr((string) $scanRow['hostname']) . '>' . e(asset_display_name((string) $scanRow['hostname'])) . '</b></span><span>' . e($statusLabel) . '</span><span>' . ((int) $scanRow['online'] === 1 ? 'بله' : 'خیر') . '</span><span style="font-size:11px">' . e((string) ($scanRow['message'] ?? '')) . '</span></div>';
            }
            echo '</section>';
        }
        if ((string) $scanRun['status'] !== 'finished') {
            echo '<script nonce="' . e(csp_nonce()) . '">(function(){var f=document.getElementById("scan-next");if(!f){return;}var s=document.getElementById("scan-status");var busy=false;var stalled=0;function step(){if(busy){return;}busy=true;var d=new FormData(f);d.set("limit","4");fetch("index.php",{method:"POST",body:d,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){busy=false;if(!j||!j.ok){if(s){s.textContent=j&&j.error?j.error:"خطا در ادامهٔ اسکن.";}return;}if(j.run&&s){s.textContent="پردازش‌شده: "+j.run.done+" از "+j.run.total;}var rc=document.getElementById("scan-rows");if(rc&&j.rows){var lb={pending:"در انتظار",running:"در حال اجرا",done:"انجام شد",offline:"آفلاین",error:"خطا"};while(rc.children.length>1){rc.removeChild(rc.lastChild);}j.rows.forEach(function(x){var r=document.createElement("div");r.className="table-row";var h=document.createElement("span");h.className="ticket-title";var hb=document.createElement("b");hb.textContent=x.name||x.hostname;h.appendChild(hb);r.appendChild(h);[lb[x.status]||x.status,x.online?"بله":"خیر",x.message||""].forEach(function(t,i){var c=document.createElement("span");if(i===2){c.style.fontSize="11px";}c.textContent=t;r.appendChild(c);});rc.appendChild(r);});}var b=document.getElementById("scan-bar");if(b&&j.run&&j.run.total>0){b.style.width=Math.floor(j.run.done*100/j.run.total)+"%";}if(j.pending>0){if(j.processed>0){stalled=0;}else{stalled++;}if(stalled<4){setTimeout(step,350);}else if(s){s.textContent="اسکن متوقف شد؛ برای ادامه دکمهٔ شروع اسکن را دوباره بزنید.";}}else if(s){s.textContent="اسکن کامل شد؛ صفحه را تازه کنید تا نتایج به‌روز شود.";}}).catch(function(){busy=false;if(s){s.textContent="خطای ارتباط با سرور.";}});}setTimeout(step,500);})();</script>';
        }
    }
    render_footer();
    exit;
}

if ($page === 'assets') {
    $assetUser = require_permission('assets.view');
    if (!is_global_ticket_role($assetUser['role']) && user_service_group($assetUser) !== 'it') {
        http_response_code(403);
        exit('شناسنامه فنی سیستم‌ها فقط در حوزه IT قابل دسترسی است.');
    }
    [$where, $params] = asset_scope($assetUser);
    asset_profile_ensure_schema();
    $assetSearch = trim((string) ($_GET['q'] ?? ''));
    $networkFilter = valid_choice((string) ($_GET['network'] ?? 'all'), ['all', 'online', 'offline', 'unknown'], 'all');
    if ($assetSearch !== '') {
        $where .= ($where !== '' ? ' AND ' : 'WHERE ') . '(a.asset_tag LIKE ? OR a.hostname LIKE ? OR a.serial_number LIKE ? OR a.ip_address LIKE ?)';
        $like = '%' . $assetSearch . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if ($networkFilter === 'online') {
        $where .= ($where !== '' ? ' AND ' : 'WHERE ') . 'p.ad_online = 1';
    } elseif ($networkFilter === 'offline') {
        $where .= ($where !== '' ? ' AND ' : 'WHERE ') . 'p.ad_scanned_at IS NOT NULL AND p.ad_online = 0';
    } elseif ($networkFilter === 'unknown') {
        $where .= ($where !== '' ? ' AND ' : 'WHERE ') . 'p.ad_scanned_at IS NULL';
    }
    $assetsQuery = db()->prepare('SELECT a.*, d.name AS department_name, p.ad_online, p.ad_last_seen, p.ad_scanned_at FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN asset_profiles p ON p.asset_id = a.id ' . $where . ' ORDER BY a.hostname, a.asset_tag');
    $assetsQuery->execute($params);
    $assetRows = $assetsQuery->fetchAll();
    $canImportAssets = user_can($assetUser, 'domain.import');
    $canPingAssets = user_can($assetUser, 'asset.ping') && (is_global_ticket_role($assetUser['role']) || user_service_group($assetUser) === 'it');
    $canExtractAssets = user_can($assetUser, 'domain.scan');
    $canExtractLocalServer = PHP_OS_FAMILY === 'Windows' && user_can($assetUser, 'asset.extract') && (is_global_ticket_role($assetUser['role']) || user_service_group($assetUser) === 'it');
    $canConfigureNetworkScan = user_can($assetUser, 'settings.domain');
    $networkScanEnabled = domain_scan_preflight() === null;
    $canSelectAssets = $canPingAssets || $canExtractAssets;
    $onlineCount = 0;
    $collectedCount = 0;
    foreach ($assetRows as $assetRow) {
        $onlineCount += (int) ($assetRow['ad_online'] ?? 0) === 1 ? 1 : 0;
        $collectedCount += !empty($assetRow['last_inventory_at']) ? 1 : 0;
    }
    render_header('دارایی‌ها', $assetUser);
    inventory_render_assets_content($assetRows, $assetSearch, $networkFilter, $canImportAssets, $canPingAssets, $canExtractAssets, $canExtractLocalServer, $canConfigureNetworkScan, $networkScanEnabled, $canSelectAssets, $onlineCount, $collectedCount);
    render_footer();
    exit;
}

if ($page === 'asset') {
    $assetUser = require_permission('assets.view');
    [$scope, $scopeParams] = asset_scope($assetUser);
    $assetId = (int) ($_GET['id'] ?? 0);
    redirect('index.php?page=inventory&id=' . $assetId);
    $assetQuery = db()->prepare('SELECT a.*, d.name AS department_name, u.full_name AS owner_name FROM assets a LEFT JOIN departments d ON d.id = a.department_id LEFT JOIN users u ON u.id = a.owner_user_id ' . ($scope ? $scope . ' AND a.id = ?' : 'WHERE a.id = ?') . ' LIMIT 1');
    $assetQuery->execute(array_merge($scopeParams, [$assetId]));
    $asset = $assetQuery->fetch();
    if (!$asset) {
        http_response_code(404);
        render_header('دارایی پیدا نشد', $assetUser);
        echo '<section class="empty-state"><h2>دارایی پیدا نشد</h2></section>';
        render_footer();
        exit;
    }
    $hardware = json_decode((string) $asset['hardware_json'], true) ?: [];
    $software = json_decode((string) $asset['software_json'], true) ?: [];
    $disks = json_decode((string) $asset['disks_json'], true) ?: [];
    $peripherals = json_decode((string) $asset['peripherals_json'], true) ?: [];
    render_header('شناسنامه دارایی', $assetUser);
    $manualPeripherals = is_array($peripherals['manual_entries'] ?? null) ? $peripherals['manual_entries'] : [];
    $canExtractLocalAsset = PHP_OS_FAMILY === 'Windows' && user_can($assetUser, 'asset.extract') && (is_global_ticket_role($assetUser['role']) || user_service_group($assetUser) === 'it') && inventory_asset_is_local($asset);
    $localExtractButton = $canExtractLocalAsset
        ? '<form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="collect_inventory"><button class="button" type="submit">استخراج محلی همین سیستم</button></form>'
        : '';
     echo '<section class="page-heading"><div><a class="back-link" href="index.php?page=assets">← بازگشت به دارایی‌ها</a><h1>' . e(asset_display_name($asset['hostname'])) . '</h1><p>' . e(asset_name_title($asset['hostname'], $asset['asset_tag']) ?: $asset['asset_tag']) . ' • آخرین جمع‌آوری: ' . e($asset['last_inventory_at'] ? persian_date($asset['last_inventory_at']) : 'ثبت نشده') . '</p></div><div class="actions">' . $localExtractButton . '<a class="button secondary" href="index.php?page=inventory&id=' . $assetId . '">ویرایش شناسنامه</a><a class="button secondary" href="index.php?page=asset-history&id=' . $assetId . '">تاریخچه سیستم</a></div></section>';
    if ($canExtractLocalAsset) {
        echo '<p class="muted">این استخراج روی میزبانی اجرا می‌شود که PHP سامانه روی آن نصب است؛ اگر سامانه را روی همین کامپیوتر اجرا کرده‌اید، همین دستگاه خوانده می‌شود.</p>';
    }
    echo '<section class="asset-facts"><div class="card detail-card"><h3>مشخصات اصلی</h3><dl><dt>شناسه</dt><dd>' . e($asset['asset_tag']) . '</dd><dt>نام کامپیوتر</dt><dd>' . e($asset['hostname']) . '</dd><dt>سریال</dt><dd>' . e($asset['serial_number']) . '</dd><dt>واحد</dt><dd>' . e($asset['department_name'] ?: 'بدون واحد') . '</dd><dt>کاربر دامنه</dt><dd>' . e($asset['domain_username']) . '</dd><dt>سیستم‌عامل</dt><dd>' . e($asset['operating_system']) . '</dd><dt>آنتی‌ویروس</dt><dd>' . e($asset['antivirus']) . '</dd><dt>IP</dt><dd>' . e($asset['ip_address']) . '</dd><dt>MAC</dt><dd>' . e($asset['mac_address']) . '</dd><dt>پردازنده</dt><dd>' . e($asset['cpu']) . '</dd><dt>حافظه</dt><dd>' . e((string) $asset['memory_mb']) . ' MB</dd></dl></div><div class="card detail-card"><h3>سخت‌افزار تکمیلی</h3><pre class="json-view">' . e(json_encode(array_merge($hardware, ['disks' => $disks]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre></div><div class="card detail-card"><h3>تجهیزات و اقلام دستی</h3><pre class="json-view">' . e(json_encode(array_merge($peripherals, ['printer_scanner_monitor_case' => $peripherals['case'] ?? ($manualPeripherals['case'] ?? [])]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre></div><div class="card detail-card"><h3>نرم‌افزارهای نصب‌شده</h3><pre class="json-view">' . e(json_encode($software, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre></div></section>';
    render_footer();
    exit;
}

if ($page === 'organization') {
    $organizationUser = require_permission('org.view');
    org_ensure_schema();
    $canManageOrg = user_can_manage_organization($organizationUser);
    $units = org_units_all(false);
    $users = db()->query("SELECT u.id, u.full_name, u.auth_source, u.is_active, u.org_unit_id, u.manager_user_id FROM users u WHERE COALESCE(u.auth_source, '') <> 'food' ORDER BY u.full_name")->fetchAll();
    $activeUsers = array_values(array_filter($users, static fn (array $u): bool => (int) $u['is_active'] === 1));
    $ceoUnit = null;
    $deputies = [];
    $departmentsByParent = [];
    $membersByUnit = [];
    foreach ($units as $unit) {
        if ((int) $unit['is_active'] !== 1) {
            continue;
        }
        if ((string) $unit['unit_type'] === 'ceo') {
            $ceoUnit = $unit;
        } elseif ((string) $unit['unit_type'] === 'deputy') {
            $deputies[] = $unit;
        } elseif ((string) $unit['unit_type'] === 'liaison') {
            // واحدهای مستقل (نمایندگی) کنار مدیرعامل؛ زیرمجموعهٔ هیچ واحدی نیستند.
        } else {
            $departmentsByParent[(int) ($unit['parent_id'] ?? 0)][] = $unit;
        }
    }
    foreach ($users as $member) {
        $membersByUnit[(int) ($member['org_unit_id'] ?? 0)][] = $member;
    }
    $orgChartUnits = [];
    foreach ($units as $unit) {
        if ((int) ($unit['is_active'] ?? 0) !== 1) {
            continue;
        }
        $orgChartUnits[] = [
            'id' => (int) $unit['id'],
            'name' => (string) $unit['name'],
            'code' => (string) ($unit['code'] ?? ''),
            'parentId' => (int) ($unit['parent_id'] ?? 0),
            'type' => (string) $unit['unit_type'],
            'chartSide' => (string) ($unit['chart_side'] ?? ''),
            'managerId' => (int) ($unit['manager_user_id'] ?? 0),
            'managerName' => (string) ($unit['manager_name'] ?? ''),
            'primaryAdmin' => (int) ($unit['is_primary_admin'] ?? 0) === 1,
        ];
    }
    $orgChartUsers = [];
    foreach ($users as $chartUser) {
        $orgChartUsers[] = [
            'id' => (int) $chartUser['id'],
            'name' => (string) $chartUser['full_name'],
            'active' => (int) $chartUser['is_active'] === 1,
            'unitId' => (int) ($chartUser['org_unit_id'] ?? 0),
            'managerId' => (int) ($chartUser['manager_user_id'] ?? 0),
            'source' => ((string) ($chartUser['auth_source'] ?? '') === 'ldap') ? 'ldap' : 'local',
        ];
    }
    $orgUndo = org_undo_state();
    $orgChartData = json_encode([
        'canManage' => $canManageOrg,
        'undo' => $orgUndo !== null,
        'undoLabel' => (string) ($orgUndo['label'] ?? ''),
        'currentUser' => ['id' => (int) $organizationUser['id'], 'name' => (string) $organizationUser['full_name']],
        'units' => $orgChartUnits,
        'users' => $orgChartUsers,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    render_header('مدیریت سازمان', $organizationUser);
    $canRepairOrgAdminRoles = user_is_primary_admin($organizationUser);
    $repairRoleLink = $canRepairOrgAdminRoles
        ? '<a class="org-icon-button org-repair-role-link" href="#repair-org-admin-roles" title="رفتن به اصلاح نقش مدیران سازمانی">ترمیم نقش‌ها</a>'
        : '';
    $orgTemplate = <<<'HTML'
<div class="organization-workspace" dir="rtl">
  <header class="org-header">
    <div class="org-brand"><div class="org-brand-icon" aria-hidden="true">▥</div><div><div class="org-brand-title">سامانه ساختار سازمانی</div><div class="org-brand-subtitle">راه‌اندازی و مدیریت چارت</div></div></div>
     <div class="org-divider"></div>
     <div class="org-header-actions"><a class="org-icon-button" href="index.php" title="بازگشت به داشبورد">↩</a>__REPAIR_ROLE_LINK__<button class="org-icon-button" data-org-tool="undo" disabled title="بازگشت آخرین عملیات">↶ <span>بازگشت</span></button><button class="org-icon-button" data-org-tool="export" title="خروجی JSON">↓ <span>خروجی</span></button><button class="org-icon-button danger" data-org-tool="reset" title="شروع مجدد">↺ <span>ریست</span></button></div>
  </header>
  <div class="org-main-layout">
    <aside class="org-sidebar" id="org-sidebar"></aside>
    <main class="org-canvas" id="org-canvas">
      <div class="org-canvas-toolbar"><input class="org-search" id="org-chart-search" type="search" placeholder="جستجوی واحد یا کاربر..." aria-label="جستجو در چارت سازمانی"><div class="org-zoom-controls"><button type="button" data-org-zoom="out" aria-label="کوچک‌نمایی">−</button><span id="org-zoom-level">100٪</span><button type="button" data-org-zoom="in" aria-label="بزرگ‌نمایی">+</button><button type="button" data-org-zoom="reset" aria-label="نمایش اندازهٔ پیش‌فرض">↺</button><button type="button" data-org-zoom="fit" aria-label="جا دادن کل چارت در صفحه" title="جا دادن کل چارت در صفحه">⛶</button></div><button class="org-focus-toggle" type="button" data-org-view="focus" aria-pressed="false">◫ <span>تمرکز روی نمودار</span></button></div>
      <div class="org-chart-wrap" id="org-chart-wrap"></div>
    </main>
    <aside class="org-pool-sidebar" id="org-pool-sidebar"></aside>
  </div>
   <div class="org-modal-backdrop" id="org-modal-backdrop"><section class="org-modal" role="dialog" aria-modal="true" aria-labelledby="org-modal-title"><header><div class="org-modal-icon" id="org-modal-icon"></div><div><h2 id="org-modal-title">انتخاب کاربر</h2><p id="org-modal-subtitle">از فهرست زیر انتخاب کنید</p></div><button class="org-modal-close" type="button" data-org-modal="close">×</button></header><div class="org-modal-preview" id="org-modal-preview"></div><div class="org-modal-hint" id="org-modal-hint">روی کاربر موردنظر کلیک کنید تا منصوب شود.</div><div class="org-modal-search"><input id="org-modal-search" type="search" placeholder="جستجو بر اساس نام..."></div><div class="org-modal-body" id="org-modal-body"></div><footer><span id="org-modal-info">موردی انتخاب نشده</span><button class="org-btn org-btn-ghost" type="button" data-org-modal="close">انصراف</button><button class="org-btn org-btn-success" type="button" id="org-modal-confirm" disabled>تأیید</button></footer></section></div>
   <div class="org-confirm-backdrop" id="org-confirm-backdrop"><section class="org-confirm-modal" role="dialog" aria-modal="true"><div class="org-confirm-icon" id="org-confirm-icon">!</div><h2 id="org-confirm-title">تأیید عملیات</h2><p id="org-confirm-message">آیا مطمئن هستید؟</p><div class="org-confirm-details" id="org-confirm-details"></div><footer><button class="org-btn org-btn-ghost" type="button" data-org-confirm="close">انصراف</button><button class="org-btn org-btn-danger" type="button" id="org-confirm-ok">تأیید</button></footer></section></div>
   <div class="org-undo-bar" id="org-undo-bar"><span>↶</span><b id="org-undo-text"></b><button type="button" data-org-tool="undo">بازگشت</button><button type="button" data-org-undo="close">×</button></div>
  <div class="org-toast" id="org-toast" role="status" aria-live="polite"></div>
  <form method="post" id="org-action-form" class="org-hidden-form">__CSRF__<input type="hidden" name="action" value=""><input type="hidden" name="unit_id" value=""><input type="hidden" name="unit_name" value=""><input type="hidden" name="unit_code" value=""><input type="hidden" name="node_type" value=""><input type="hidden" name="parent_id" value=""><input type="hidden" name="unit_manager_id" value=""><input type="hidden" name="is_primary_admin" value="0"><input type="hidden" name="ceo_user_id" value=""><input type="hidden" name="member_unit_id" value=""><input type="hidden" name="user_id" value=""><input type="hidden" name="manager_user_id" value=""></form>
</div>
HTML;
    echo str_replace(['__CSRF__', '__REPAIR_ROLE_LINK__'], [csrf_field(), $repairRoleLink], $orgTemplate);
    if ($canRepairOrgAdminRoles) {
        $repairRows = db()->query('SELECT DISTINCT u.id, u.username, u.full_name, u.role, ou.name AS unit_name FROM users u JOIN org_units ou ON (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id)) WHERE u.is_active = 1 AND (u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1)) AND ou.is_active = 1 ORDER BY u.full_name, ou.name')->fetchAll();
        $repairCandidates = [];
        foreach ($repairRows as $repairRow) {
            $repairId = (int) $repairRow['id'];
            if (!isset($repairCandidates[$repairId])) {
                $repairCandidates[$repairId] = $repairRow;
                $repairCandidates[$repairId]['unit_names'] = [];
            }
            $repairCandidates[$repairId]['unit_names'][] = (string) $repairRow['unit_name'];
        }
        echo '<section class="card form-card" id="repair-org-admin-roles"><h2>اصلاح مدیران سازمانی که ادمین اصلی شده‌اند</h2><p class="muted">این فهرست، ادمین‌های اصلیِ منصوب‌شده به معاونت/اداره را نشان می‌دهد. فقط افرادی را تیک بزنید که ادمین اصلی سامانه نیستند و اشتباهاً با انتساب سازمانی ادمین شده‌اند؛ نقش آن‌ها به «مدیر» برمی‌گردد و چارت و ارجاع‌های سازمانی حفظ می‌شود. دست‌کم یک ادمین اصلی واقعی باید باقی بماند.</p>';
        if ($repairCandidates === []) {
            echo '<p class="muted">در حال حاضر مدیر سازمانیِ ادمین اصلی پیدا نشد.</p>';
        } else {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="repair_org_manager_admin_roles"><div class="table-wrap"><table class="data-table"><thead><tr><th>اصلاح</th><th>کاربر</th><th>واحدهای مدیریتی</th></tr></thead><tbody>';
            foreach ($repairCandidates as $repairId => $repairCandidate) {
                $isCurrent = $repairId === (int) $organizationUser['id'];
                echo '<tr><td><input type="checkbox" name="user_ids[]" value="' . $repairId . '"' . ($isCurrent ? ' disabled' : '') . ' aria-label="اصلاح ' . e((string) $repairCandidate['full_name']) . '"></td><td>' . e((string) $repairCandidate['full_name']) . ' <small>#' . $repairId . ' · ' . e((string) $repairCandidate['username']) . '</small>' . ($isCurrent ? '<br><small>حساب جاری قابل تنزل نیست</small>' : '') . '</td><td>' . e(implode('، ', array_unique($repairCandidate['unit_names']))) . '</td></tr>';
            }
            echo '</tbody></table></div><button class="button" type="submit">بازگرداندن نقش انتخاب‌شده‌ها به مدیر</button></form>';
        }
        echo '</section>';

        // ۱.۶۲.۶ — ترمیم مدیران سازمانی که نقش «مدیر پشتیبانی» دارند (مقصد اشتباه ارجاع خودکار پشتیبانی).
        $supportRepairRows = db()->query('SELECT DISTINCT u.id, u.username, u.full_name, ou.name AS unit_name FROM users u JOIN org_units ou ON (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id)) WHERE u.is_active = 1 AND u.role = "support_manager" AND ou.is_active = 1 ORDER BY u.full_name, ou.name')->fetchAll();
        $supportRepairCandidates = [];
        foreach ($supportRepairRows as $supportRepairRow) {
            $supportRepairId = (int) $supportRepairRow['id'];
            if (!isset($supportRepairCandidates[$supportRepairId])) {
                $supportRepairCandidates[$supportRepairId] = $supportRepairRow;
                $supportRepairCandidates[$supportRepairId]['unit_names'] = [];
            }
            $supportRepairCandidates[$supportRepairId]['unit_names'][] = (string) $supportRepairRow['unit_name'];
        }
        echo '<section class="card form-card" id="repair-org-support-roles"><h2>اصلاح مدیران سازمانی که «مدیر پشتیبانی» شده‌اند</h2><p class="muted">درخواست‌های پشتیبانی به‌صورت خودکار به «مدیر پشتیبانی» ارجاع می‌شوند. اگر معاون یا مدیر اداره‌ای به‌اشتباه این نقش را داشته باشد، مسئول درخواست‌های پشتیبانی می‌شود. فقط افرادی را تیک بزنید که واقعاً مدیر واحد پشتیبانی نیستند؛ نقش آن‌ها به «مدیر» برمی‌گردد و چارت سازمانی حفظ می‌شود.</p>';
        if ($supportRepairCandidates === []) {
            echo '<p class="muted">در حال حاضر مدیر سازمانیِ دارای نقش «مدیر پشتیبانی» پیدا نشد.</p>';
        } else {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="repair_org_manager_support_roles"><div class="table-wrap"><table class="data-table"><thead><tr><th>اصلاح</th><th>کاربر</th><th>واحدهای مدیریتی</th></tr></thead><tbody>';
            foreach ($supportRepairCandidates as $supportRepairId => $supportRepairCandidate) {
                echo '<tr><td><input type="checkbox" name="user_ids[]" value="' . $supportRepairId . '" aria-label="اصلاح ' . e((string) $supportRepairCandidate['full_name']) . '"></td><td>' . e((string) $supportRepairCandidate['full_name']) . ' <small>#' . $supportRepairId . ' · ' . e((string) $supportRepairCandidate['username']) . '</small></td><td>' . e(implode('، ', array_unique($supportRepairCandidate['unit_names']))) . '</td></tr>';
            }
            echo '</tbody></table></div><button class="button" type="submit">بازگرداندن نقش انتخاب‌شده‌ها به مدیر</button></form>';
        }
        echo '</section>';

        // ۱.۶۲.۶ — تشخیص ارجاع خودکار: چه کسی الان مسئول درخواست جدید می‌شود و چرا.
        $diagOpenLoadSql = '(SELECT COUNT(*) FROM tickets t WHERE t.assigned_to = u.id AND t.status NOT IN ("resolved", "closed", "cancelled"))';
        $diagOrgManagerSql = 'EXISTS (SELECT 1 FROM org_units ou WHERE ou.is_active = 1 AND (ou.manager_user_id = u.id OR EXISTS (SELECT 1 FROM org_unit_managers oum WHERE oum.unit_id = ou.id AND oum.user_id = u.id)))';
        $diagRows = db()->query('SELECT u.id, u.full_name, u.username, u.role, u.is_primary_admin, hu.code AS hu_code, ' . $diagOpenLoadSql . ' AS open_load, ' . $diagOrgManagerSql . ' AS is_org_manager FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id WHERE u.is_active = 1 AND (u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1) OR u.role = "support_manager" OR (u.role IN ("agent", "manager") AND hu.code IN ("it", "support"))) ORDER BY u.full_name')->fetchAll();
        $diagPick = ['it' => choose_handling_agent('it'), 'support' => choose_handling_agent('support')];
        $diagRoleLabel = static fn (array $diagRow): string => ([
            'primary_admin' => 'ادمین اصلی',
            'admin' => ((int) $diagRow['is_primary_admin'] === 1 ? 'ادمین (پرچم ادمین اصلی)' : 'ادمین'),
            'support_manager' => 'مدیر پشتیبانی',
            'manager' => 'مدیر',
            'agent' => 'کارشناس',
        ][(string) $diagRow['role']] ?? (string) $diagRow['role']);
        echo '<section class="card form-card" id="ticket-auto-assign-diagnostics"><h2>تشخیص ارجاع خودکار درخواست‌ها</h2><p class="muted">مسئول درخواست جدید به‌صورت خودکار تعیین می‌شود: درخواست IT به «ادمین اصلی» و درخواست پشتیبانی به «مدیر پشتیبانی» می‌رسد (در صورت تعدد، کمترین درخواست باز؛ مدیران سازمانی آخرِ صف). اگر نام بی‌ربطی در این فهرست است، نقش او را از «مدیریت کاربران» یا فرم‌های ترمیم بالا اصلاح کنید.</p>';
        foreach (['it' => 'حوزهٔ IT', 'support' => 'حوزهٔ پشتیبانی'] as $diagGroup => $diagGroupLabel) {
            $diagCandidates = array_values(array_filter($diagRows, static fn (array $diagRow): bool => $diagGroup === 'it'
                ? ((string) $diagRow['role'] === 'primary_admin' || ((string) $diagRow['role'] === 'admin' && (int) $diagRow['is_primary_admin'] === 1) || ((string) ($diagRow['hu_code'] ?? '') === 'it' && in_array((string) $diagRow['role'], ['agent', 'manager'], true)))
                : ((string) $diagRow['role'] === 'support_manager' || ((string) ($diagRow['hu_code'] ?? '') === 'support' && in_array((string) $diagRow['role'], ['agent', 'manager'], true)))));
            $diagPickId = (int) ($diagPick[$diagGroup] ?? 0);
            $diagPickName = 'هیچ‌کس (درخواست بدون مسئول در «بررسی مدیر» می‌ماند)';
            foreach ($diagCandidates as $diagRow) {
                if ((int) $diagRow['id'] === $diagPickId) {
                    $diagPickName = (string) $diagRow['full_name'];
                }
            }
            echo '<h3 style="margin:14px 0 6px">' . $diagGroupLabel . '</h3><p class="muted">مقصد فعلی ارجاع خودکار: <b>' . e($diagPickName) . '</b></p>';
            if ($diagCandidates === []) {
                echo '<p class="muted">هیچ کاندیدایی برای این حوزه پیدا نشد.</p>';
                continue;
            }
            echo '<div class="table-wrap"><table class="data-table"><thead><tr><th>کاربر</th><th>نقش</th><th>واحد رسیدگی</th><th>درخواست‌های باز</th><th>مدیر سازمانی؟</th></tr></thead><tbody>';
            foreach ($diagCandidates as $diagRow) {
                $diagIsPick = (int) $diagRow['id'] === $diagPickId;
                echo '<tr' . ($diagIsPick ? ' style="font-weight:700"' : '') . '><td>' . e((string) $diagRow['full_name']) . ' <small>#' . (int) $diagRow['id'] . ' · ' . e((string) $diagRow['username']) . '</small>' . ($diagIsPick ? ' <span class="status ok">مقصد فعلی</span>' : '') . '</td><td>' . e($diagRoleLabel($diagRow)) . '</td><td>' . e((string) ($diagRow['hu_code'] === 'it' ? 'IT' : ($diagRow['hu_code'] === 'support' ? 'پشتیبانی' : '—'))) . '</td><td>' . (int) $diagRow['open_load'] . '</td><td>' . ((int) $diagRow['is_org_manager'] === 1 ? '<span class="status warn">بله — آخرِ صف ارجاع خودکار</span>' : 'خیر') . '</td></tr>';
            }
            echo '</tbody></table></div>';
        }
        echo '</section>';
    }
    echo '<script nonce="' . e(csp_nonce()) . '">window.PERSIAN_ORG_DATA = ' . $orgChartData . ';</script><script src="assets/organization.js?v=7"></script>';
    render_footer();
    exit;
}

if ($page === 'settings') {
    $user = require_login();
    $canSettingsGeneral = user_can($user, 'settings.general');
    $canSettingsDomain = user_can($user, 'settings.domain');
    $canSettingsUsers = user_can($user, 'settings.users');
    $canSettingsKeyRoles = user_can($user, 'settings.key_roles');
    // ویرایش هر بخش جدا از دیدن آن است؛ بدون کد ویرایش، فرم‌ها غیرفعال (inert) نمایش داده می‌شوند.
    $canSettingsGeneralEdit = user_can($user, 'settings.general_edit');
    $canSettingsDomainEdit = user_can($user, 'settings.domain_edit');
    $canSettingsUsersEdit = user_can($user, 'settings.users_edit');
    $canSettingsKeyRolesEdit = user_can($user, 'settings.key_roles_edit');
    $appName = setting('app_name', (string) cfg('app.name', 'سامانه درخواست پشتیبانی'));
    $logo = setting('app_logo', (string) cfg('app.logo', ''));
    $foodBrandName = setting('food_ticket_brand_name', $appName);
    $foodBrandLogo = setting('food_ticket_brand_logo', $logo);
    $departments = db()->query('SELECT id, name, code FROM departments WHERE is_active = 1 ORDER BY name')->fetchAll();
    $users = db()->query('SELECT u.id, u.username, u.full_name, u.employee_number, u.national_code, u.role, u.auth_source, u.is_active, u.is_it_agent, u.is_primary_admin, u.department_id, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.auth_source <> \'food\' AND NOT (u.auth_source = \'local\' AND u.role IN (\'local_user\',\'guard\')) ORDER BY u.full_name')->fetchAll();
    $canManageSettingsUsers = user_is_primary_admin($user);
    $roleSelectableUsers = array_values(array_filter($users, static fn (array $roleUser): bool => (int) ($roleUser['is_active'] ?? 0) === 1));
    $roleCards = '';
    foreach ([
        'supervisor' => ['label' => 'سوپروایز', 'note' => 'دسترسی سوپروایزر: مشاهده و مدیریت درخواست‌ها، تأیید و بستن.'],
        'inspection' => ['label' => 'بازرسی', 'note' => 'مجوز ثبت ورود رسانه در کنترل CD/DVD.'],
        'it_expert' => ['label' => 'کارشناس IT', 'note' => 'دسترسی ادمین اصلی سامانه.'],
    ] as $roleKey => $roleMeta) {
        $roleIds = key_role_user_ids($roleKey);
        $roleCards .= '<div class="key-role-block"><h3>' . e($roleMeta['label']) . '</h3><p class="muted">' . e($roleMeta['note']) . '</p><div class="form-grid">';
        for ($slot = 0; $slot < 2; $slot++) {
            $selectedId = (int) ($roleIds[$slot] ?? 0);
            $roleCards .= '<label>کاربر ' . ($slot + 1) . '<select name="' . $roleKey . '_ids[]"><option value="0">— انتخاب نشده —</option>';
            foreach ($roleSelectableUsers as $roleUser) {
                $roleCards .= '<option value="' . (int) $roleUser['id'] . '" ' . ((int) $roleUser['id'] === $selectedId ? 'selected' : '') . '>' . e($roleUser['full_name'] . ' - ' . $roleUser['username']) . '</option>';
            }
            $roleCards .= '</select></label>';
        }
        $roleCards .= '</div></div>';
    }
    render_header('تنظیمات سامانه', $user);
    echo '<section class="page-heading"><div><span class="eyebrow">مدیریت سامانه</span><h1>تنظیمات</h1><p>مشخصات ظاهری، اتصال Active Directory و نقش کاربران را مدیریت کنید.</p></div></section>';
    $tabLinks = '';
    if ($canSettingsGeneral) {
        $tabLinks .= '<a class="' . ($canSettingsGeneral ? 'active' : '') . '" href="#general">عمومی</a>';
    }
    if ($canSettingsDomain) {
        $tabLinks .= '<a class="' . (!$canSettingsGeneral && $canSettingsDomain ? 'active' : '') . '" href="#domain">اتصال دامین</a>';
    }
    if ($canSettingsGeneral) {
        $tabLinks .= '<a href="#triangle">افکت نمایشی</a>';
    }
    if ($canSettingsUsers) {
        $tabLinks .= '<a class="' . (!$canSettingsGeneral && !$canSettingsDomain && $canSettingsUsers ? 'active' : '') . '" href="#users">کاربران</a>';
    }
    if ($canManageSettingsUsers) {
        $tabLinks .= '<a href="#roles">نقش‌ها و دسترسی‌ها</a>';
    }
    echo '<div class="settings-tabs">' . $tabLinks . '</div>';
    echo '<section class="settings-stack">';

    if ($canSettingsGeneral) {
    echo '<div class="settings-panel settings-panel-grid" id="general"' . ($canSettingsGeneralEdit ? '' : ' inert') . '>';
    echo '<div class="card form-card" id="ticket-auto-close"><h2>بستن خودکار درخواست‌های حل‌شده</h2><p class="muted">درخواست «حل‌شده‌ای» که کاربر پس از این مدت امتیاز ثبت نکند، به‌صورت خودکار بسته می‌شود؛ کاربر پس از آن هم می‌تواند امتیاز بدهد. مقدار صفر یعنی غیرفعال.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="ticket"><input type="hidden" name="app_name" value="' . e($appName) . '"><label>مهلت بستن خودکار (روز)<input type="number" name="ticket_auto_close_days" min="0" max="30" value="' . (int) setting('ticket_auto_close_days', '3') . '"></label><small>پیش‌فرض ۳ روز. درخواست دارای درخواست لغو در انتظار، به‌صورت خودکار بسته نمی‌شود.</small><button class="button" type="submit">ذخیره مهلت بستن خودکار</button></form></div>';
    if (user_is_primary_admin($user)) {
        $inspectionAlarmEnabled = setting('inspection_food_absence_alarm_enabled', '1') !== '0';
        echo '<div class="card form-card inspection-alarm-settings" id="inspection-alarm-settings"><h2>یادآور روزانهٔ بازرسی</h2><p class="muted">هر روز ساعت ۱۱، پیام یادآوری غایبین گروه‌های غذایی فقط برای کاربران بازرسی نمایش داده می‌شود. تغییر این گزینه برای همهٔ کاربران بازرسی اعمال می‌شود.</p><p>وضعیت فعلی: <strong class="' . ($inspectionAlarmEnabled ? 'inspection-alarm-status-on' : 'inspection-alarm-status-off') . '">' . ($inspectionAlarmEnabled ? 'فعال' : 'غیرفعال') . '</strong></p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="inspection_alarm"><input type="hidden" name="inspection_food_absence_alarm_enabled" value="' . ($inspectionAlarmEnabled ? '0' : '1') . '"><button class="button ' . ($inspectionAlarmEnabled ? 'danger' : '') . '" type="submit">' . ($inspectionAlarmEnabled ? 'غیرفعال کردن یادآور' : 'فعال کردن یادآور') . '</button></form></div>';
    }
    echo '<div class="card form-card"><h2>اطلاعات سامانه</h2><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="general"><label>نام سامانه<input name="app_name" required value="' . e($appName) . '"></label><label>لوگو<input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml"><small>لوگوی جدید اختیاری است؛ PNG، JPG یا SVG تا ۲ مگابایت.</small></label><label>ظاهر نام کامپیوترها<select name="asset_hostname_style"><option value="short_lower"' . (setting('asset_hostname_style', 'short_lower') === 'short_lower' ? ' selected' : '') . '>فقط نام، با حروف کوچک (adminit2) — پیش‌فرض</option><option value="short"' . (setting('asset_hostname_style', 'short_lower') === 'short' ? ' selected' : '') . '>فقط نام، با حروف اصلی (ADMINIT2)</option><option value="full"' . (setting('asset_hostname_style', 'short_lower') === 'full' ? ' selected' : '') . '>نام کامل دامنه (ADMINIT2.DOMAIN.LOCAL)</option></select><small>روی فهرست شناسنامه‌ها، فهرست اسکن دامنه، فهرست سیستم‌ها و کمبوی «سیستم مرتبط» اثر دارد. نام کامل همیشه به‌صورت راهنما (tooltip) روی نام می‌ماند.</small></label>' . ($logo ? '<div class="current-logo"><img src="' . e($logo) . '" alt="لوگوی فعلی"><span>لوگوی فعلی</span></div>' : '') . '<button class="button" type="submit">ذخیره اطلاعات عمومی</button></form></div>';
    echo '<div class="card form-card login-branding-settings" id="login-branding"><h2>مدیریت صفحه ورود</h2><p class="muted">عنوان‌ها، پیام متحرک و لوگوی مخصوص صفحه ورود را تنظیم کنید. لوگو PNG یا JPG و حداکثر ۲ مگابایت باشد.</p><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="login_branding"><input type="hidden" name="app_name" value="' . e($appName) . '"><label>عنوان اصلی<input name="login_title" maxlength="120" value="' . e(setting('login_title', 'به سامانه درخواست پشتیبانی خوش آمدید')) . '"></label><label>متن معرفی<textarea name="login_intro" rows="2" maxlength="500">' . e(setting('login_intro', 'برای ثبت و پیگیری درخواست‌های خود وارد شوید.')) . '</textarea></label><label>پیام‌های متحرک ابتدای صفحه (اختیاری؛ هر پیام در یک خط، حداکثر ۵ پیام)<textarea name="login_announcements" rows="3" maxlength="1300" placeholder="مثلاً: اطلاعیه نگهداری سامانه در روز ...">' . e(setting('login_announcements', '') !== '' ? setting('login_announcements', '') : setting('login_announcement', '')) . '</textarea><small class="field-help">پیام‌ها به‌ترتیب و پشت سر هم نمایش داده می‌شوند؛ هر پیام حداکثر ۲۴۰ نویسه. اگر خالی باشد، نوار پیام نمایش داده نمی‌شود.</small></label><div class="form-grid"><label>سرعت حرکت پیام (ثانیه برای هر دور؛ کمتر = سریع‌تر)<input type="number" name="login_announcement_speed" min="5" max="60" value="' . (int) setting('login_announcement_speed', '18') . '"></label><label>اندازه قلم پیام (پیکسل)<input type="number" name="login_announcement_font_size" min="11" max="22" value="' . (int) setting('login_announcement_font_size', '12') . '"></label></div><div class="form-grid"><label>عنوان کارت معرفی<input name="login_art_title" maxlength="100" value="' . e(setting('login_art_title', 'پشتیبانی، ساده و شفاف')) . '"></label><label>متن کارت معرفی<input name="login_art_text" maxlength="300" value="' . e(setting('login_art_text', 'هر درخواست یک شماره پیگیری دارد و مسیر رسیدگی آن برای شما روشن است.')) . '"></label><label>عنوان راهنمای ورود<input name="login_note_title" maxlength="100" value="' . e(setting('login_note_title', 'ورود یکپارچه سازمانی')) . '"></label><label>متن راهنمای ورود<input name="login_note_text" maxlength="300" value="' . e(setting('login_note_text', 'کاربران شبکه با حساب Active Directory خود وارد می‌شوند.')) . '"></label></div><label>لوگوی صفحه ورود<input type="file" name="login_logo" accept="image/png,image/jpeg"></label>' . (setting('app_login_logo', '') ? '<div class="current-logo"><img src="' . e((string) setting('app_login_logo', '')) . '" alt="لوگوی صفحه ورود"><span>لوگوی اختصاصی صفحه ورود</span></div>' : '') . '<button class="button" type="submit">ذخیره تنظیمات صفحه ورود</button></form></div>';
    echo '<div class="card form-card food-ticket-brand-settings" id="food-ticket-brand"><h2>هویت پنل سامانه مدیریت غذا</h2><p class="muted">این نام و لوگو هنگام ورود از سامانه اصلی به پنل چاپ فیش منتقل می‌شود و با تغییرات بعدی نیز همگام خواهد ماند.</p><form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save_food_ticket_brand"><label>نام سامانه چاپ فیش<input name="food_ticket_brand_name" required value="' . e($foodBrandName) . '"></label><label>لوگوی پنل چاپ فیش<input type="file" name="food_ticket_logo" accept="image/png,image/jpeg"><small>PNG یا JPG، حداکثر ۲ مگابایت.</small></label>' . ($foodBrandLogo ? '<div class="current-logo"><img src="' . e($foodBrandLogo) . '" alt="لوگوی پنل چاپ فیش"><span>لوگوی فعلی پنل فیش</span></div>' : '') . '<button class="button" type="submit">ذخیره هویت پنل چاپ فیش</button></form></div>';
    if (user_is_primary_admin($user)) {
        $activityRetention = activity_log_retention_days();
        $activityCount = 0;
        try {
            activity_logs_ensure();
            $activityCount = (int) db()->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();
        } catch (Throwable) {
            $activityCount = 0;
        }
        echo '<div class="card form-card" id="activity-log-settings"><h2>گزارش فعالیت کاربران</h2><p class="muted">تمام عملیات کاربران ثبت می‌شود. رکوردهای قدیمی‌تر از بازهٔ نگهداری به‌صورت خودکار با کران پاک می‌شوند. رکوردهای فعلی: ' . number_format($activityCount) . '</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="prune_activity_logs"><label>بازهٔ نگهداری (روز)<input type="number" name="retention_days" min="7" max="3650" value="' . (int) $activityRetention . '"></label><small>پیش‌فرض ۶۰ روز (حدود دو ماه).</small><button class="button secondary" type="submit">ذخیره و پاک‌سازی لاگ‌های قدیمی</button></form></div>';
    }
    echo '</div>';
    }

    if ($canSettingsGeneral) {
        $tmConfig = triangle_effect_config();
        $tmPages = triangle_effect_enabled_pages();
        $particleConfig = particle_effect_config();
        $activeEffect = display_effect_type();
        $tmRange = static function (string $key, string $label, string $hint, string $step) use ($tmConfig): string {
            $spec = triangle_effect_config_spec()[$key];
            return '<label class="tm-field"><span class="tm-field-head"><span>' . e($label) . '</span><output data-tm-out="' . e($key) . '">' . e((string) $tmConfig[$key]) . '</output></span><input type="range" name="tm[' . e($key) . ']" data-tm-key="' . e($key) . '" min="' . e((string) $spec[0]) . '" max="' . e((string) $spec[1]) . '" step="' . e($step) . '" value="' . e((string) $tmConfig[$key]) . '"><small>' . e($hint) . '</small></label>';
        };
        $particleRange = static function (string $key, string $label, string $hint, string $step) use ($particleConfig): string {
            $spec = particle_effect_config_spec()[$key];
            return '<label class="tm-field"><span class="tm-field-head"><span>' . e($label) . '</span><output data-particle-out="' . e($key) . '">' . e((string) $particleConfig[$key]) . '</output></span><input type="range" name="particle[' . e($key) . ']" data-particle-key="' . e($key) . '" min="' . e((string) $spec[0]) . '" max="' . e((string) $spec[1]) . '" step="' . e($step) . '" value="' . e((string) $particleConfig[$key]) . '"><small>' . e($hint) . '</small></label>';
        };
        $tmPagesHtml = '';
        foreach (triangle_effect_page_catalog() as $tmGroupLabel => $tmGroupPages) {
            $tmPagesHtml .= '<fieldset class="tm-group"><legend>' . e((string) $tmGroupLabel) . '</legend>';
            foreach ($tmGroupPages as $tmPageKey => $tmPageLabel) {
                $tmPagesHtml .= '<label class="tm-page"><input type="checkbox" name="triangle_pages[]" value="' . e((string) $tmPageKey) . '"' . (in_array((string) $tmPageKey, $tmPages, true) ? ' checked' : '') . '><span>' . e((string) $tmPageLabel) . '</span></label>';
            }
            $tmPagesHtml .= '</fieldset>';
        }
        $triangleActive = $activeEffect === 'triangle';
        $particleActive = $activeEffect === 'particles';
        echo '<div class="settings-panel" id="triangle"' . ($canSettingsGeneralEdit ? '' : ' inert') . '>';
        echo '<form method="post" class="tm-form" id="triangle-form">' . csrf_field() . '<input type="hidden" name="action" value="save_triangle_effect"><input type="hidden" name="effect_type" id="display-effect-type" value="' . e($activeEffect) . '"><div class="tm-layout">';
        echo '<div class="card form-card tm-pages-card"><h2>صفحه‌ها و فرم‌ها</h2><p class="muted">افکت انتخاب‌شده فقط روی صفحه‌هایی نمایش داده می‌شود که انتخاب و ذخیره کنید. صفحه‌های چاپ مستقل برای حفظ چیدمان چاپ در این فهرست نیستند.</p><div class="tm-bulk"><button type="button" class="button secondary" data-tm-all="1">انتخاب همه</button><button type="button" class="button secondary" data-tm-all="0">پاک‌کردن انتخاب‌ها</button><span class="muted" data-tm-count></span></div><label class="tm-search"><span class="sr-only">جست‌وجوی صفحه</span><input type="search" data-tm-search placeholder="جست‌وجوی صفحه یا فرم…"></label><div class="tm-pages-list">' . $tmPagesHtml . '<p class="tm-no-results" data-tm-no-results hidden>صفحه‌ای با این نام پیدا نشد.</p></div></div>';
        echo '<div class="card form-card tm-settings-card"><h2>پیش‌نمایش و تنظیمات افکت</h2><div class="tm-effect-tabs" role="tablist" aria-label="نوع افکت نمایشی">';
        echo '<button type="button" class="tm-effect-tab' . ($triangleActive ? ' active' : '') . '" id="tab-triangle-effect" role="tab" aria-selected="' . ($triangleActive ? 'true' : 'false') . '" aria-controls="triangle-effect-panel" data-effect-tab="triangle"' . ($triangleActive ? '' : ' tabindex="-1"') . '><span class="tm-effect-icon" aria-hidden="true">△</span><span>مثلث واکنش‌گرا</span></button>';
        echo '<button type="button" class="tm-effect-tab' . ($particleActive ? ' active' : '') . '" id="tab-particle-effect" role="tab" aria-selected="' . ($particleActive ? 'true' : 'false') . '" aria-controls="particle-effect-panel" data-effect-tab="particles"' . ($particleActive ? '' : ' tabindex="-1"') . '><span class="tm-effect-icon" aria-hidden="true">✦</span><span>ذرات معلق</span></button></div>';
        echo '<p class="tm-effect-note">فهرست صفحه‌ها برای هر دو افکت مشترک است. تب فعال پس از ذخیره، نوع افکتی را که روی صفحه‌های انتخاب‌شده نمایش داده می‌شود مشخص می‌کند.</p>';
        echo '<section class="tm-effect-panel" id="triangle-effect-panel" data-effect-panel="triangle" role="tabpanel" aria-labelledby="tab-triangle-effect"' . ($triangleActive ? '' : ' hidden') . '><p class="muted">پیش‌نمایش زنده است؛ ماوس را روی آن حرکت دهید یا کلیک کنید. تنظیمات برای همهٔ صفحه‌های انتخاب‌شده یکسان است.</p><div class="tm-preview" id="triangle-preview" data-tm-preview></div><div class="tm-bulk"><button type="button" class="button secondary" data-tm-ripple>ایجاد موج آزمایشی</button><button type="button" class="button secondary" data-tm-preset="default">پیش‌فرض</button><button type="button" class="button secondary" data-tm-preset="ocean">امواج آرام</button><button type="button" class="button secondary" data-tm-preset="magnet">آهنربای قوی</button><button type="button" class="button secondary" data-tm-preset="soft">محو و ملایم</button></div><div class="tm-fields">';
        echo $tmRange('cellSize', 'اندازهٔ مثلث‌ها', 'کوچک‌تر = مثلث‌های بیشتر و سنگین‌تر', '1');
        echo $tmRange('opacity', 'شفافیت خطوط', 'کمتر = محوتر و کم‌مزاحمت‌تر', '0.05');
        echo $tmRange('mouseRadiusMult', 'شعاع اثر ماوس', 'ضریبی از اندازهٔ مثلث', '0.1');
        echo $tmRange('repulsionMult', 'قدرت دفع ماوس', 'شدت کنار رفتن نقطه‌ها از مسیر ماوس', '0.05');
        echo $tmRange('springK', 'سفتی فنر', 'سرعت برگشت نقطه‌ها به جای اصلی', '0.005');
        echo $tmRange('damping', 'میرایی حرکت', 'بیشتر = نرم‌تر و ماندگارتر', '0.01');
        echo $tmRange('idleAmp', 'لرزش آرام (حالت بیکار)', 'صفر = ثابت و بدون حرکت خودکار', '0.1');
        echo $tmRange('waveSpeed', 'سرعت موج کلیک', 'پیکسل بر ثانیه', '10');
        echo $tmRange('waveDuration', 'مدت موج کلیک', 'ثانیه', '0.1');
        echo $tmRange('waveWidth', 'ضخامت موج کلیک', 'پیکسل', '5');
        echo $tmRange('waveAmpMult', 'قدرت موج کلیک', 'شدت جابه‌جایی نقطه‌ها', '0.01');
        echo '</div><div class="tm-colors"><label>رنگ خطوط<input type="color" name="tm[meshColor]" data-tm-key="meshColor" value="' . e($tmConfig['meshColor']) . '"></label><label>رنگ نقطه‌ها<input type="color" name="tm[dotColor]" data-tm-key="dotColor" value="' . e($tmConfig['dotColor']) . '"></label><label class="check-label"><input type="checkbox" name="tm[showDots]" value="1" data-tm-key="showDots"' . ($tmConfig['showDots'] ? ' checked' : '') . '> نمایش نقطه‌ها</label><label class="check-label"><input type="checkbox" name="tm[maskVignette]" value="1" data-tm-key="maskVignette"' . ($tmConfig['maskVignette'] ? ' checked' : '') . '> محو شدن لبه‌ها (وینیت)</label></div><div class="actions"><button class="button" type="submit">ذخیره تنظیمات افکت</button><span class="muted">این گزینه نوع افکت فعال را مشخص می‌کند؛ صفحه‌های انتخاب‌نشده تغییری نمی‌کنند.</span></div></section>';
        echo '<section class="tm-effect-panel" id="particle-effect-panel" data-effect-panel="particles" role="tabpanel" aria-labelledby="tab-particle-effect"' . ($particleActive ? '' : ' hidden') . '><p class="muted">پیش‌نمایش زندهٔ ذرات معلق است؛ حرکت ماوس و تنظیم‌ها را همین‌جا امتحان کنید.</p><div class="tm-preview particle-preview" id="particle-preview" data-particle-preview><span class="particle-preview-label">ذرات معلق · حرکت ماوس</span><span class="particle-preview-help">شبکهٔ ذرات واکنش‌گرا</span></div><div class="tm-bulk"><button type="button" class="button secondary" data-particle-preset="default">پیش‌فرض</button><button type="button" class="button secondary" data-particle-preset="calm">آرام و خلوت</button><button type="button" class="button secondary" data-particle-preset="dense">شبکهٔ متراکم</button><button type="button" class="button secondary" data-particle-preset="reactive">واکنش قوی</button></div><div class="tm-fields">';
        echo $particleRange('density', 'تراکم ذرات', 'عدد کمتر = ذرات بیشتر', '100');
        echo $particleRange('maxParticles', 'سقف تعداد ذرات', 'کنترل جزئیات و بار پردازشی', '10');
        echo $particleRange('speed', 'سرعت حرکت', 'صفر = ذرات ثابت', '0.05');
        echo $particleRange('linkDistance', 'فاصلهٔ اتصال ذرات', 'بیشتر = خطوط شبکهٔ بیشتر', '5');
        echo $particleRange('mouseRadius', 'شعاع واکنش ماوس', 'فاصلهٔ تأثیر نشانگر', '10');
        echo $particleRange('mouseStrength', 'قدرت واکنش ماوس', 'شدت روشن‌شدن خطوط نزدیک ماوس', '0.1');
        echo $particleRange('opacity', 'شفافیت شبکه', 'کمتر = محوتر', '0.05');
        echo $particleRange('dotScale', 'اندازهٔ نقطه‌ها', 'تنظیم اندازهٔ ذرات', '0.1');
        echo '</div><div class="tm-colors"><label>رنگ شبکه<input type="color" name="particle[color]" data-particle-key="color" value="' . e($particleConfig['color']) . '"></label><label class="check-label"><input type="checkbox" name="particle[showDots]" value="1" data-particle-key="showDots"' . ($particleConfig['showDots'] ? ' checked' : '') . '> نمایش نقطه‌ها</label><label class="check-label"><input type="checkbox" name="particle[showLinks]" value="1" data-particle-key="showLinks"' . ($particleConfig['showLinks'] ? ' checked' : '') . '> اتصال ذرات به یکدیگر</label><label class="check-label"><input type="checkbox" name="particle[connectCursor]" value="1" data-particle-key="connectCursor"' . ($particleConfig['connectCursor'] ? ' checked' : '') . '> اتصال به ماوس</label><label class="check-label"><input type="checkbox" name="particle[autoMove]" value="1" data-particle-key="autoMove"' . ($particleConfig['autoMove'] ? ' checked' : '') . '> حرکت خودکار</label></div><div class="actions"><button class="button" type="submit">ذخیره تنظیمات ذرات</button><span class="muted">با ذخیرهٔ این تب، ذرات روی صفحه‌های انتخاب‌شده فعال می‌شوند؛ ورود و داشبورد بدون انتخاب صریح افکت نخواهند داشت.</span></div></section></div></div></form>';
        echo '</div>';
    }

    if ($canSettingsDomain) {
    echo '<div class="settings-panel settings-panel-grid" id="domain"' . ($canSettingsDomainEdit ? '' : ' inert') . '>';
    echo '<div class="card form-card settings-panel-tall"><h2>اتصال Active Directory</h2><p class="muted">رمز حساب سرویس در صورت خالی‌گذاشتن تغییر نمی‌کند.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="ldap"><input type="hidden" name="app_name" value="' . e($appName) . '"><label class="switch-row"><input type="checkbox" name="ldap_enabled" value="1" ' . (setting('ldap_enabled', cfg('ldap.enabled') ? '1' : '0') === '1' ? 'checked' : '') . '> ورود کاربران شبکه فعال باشد</label><div class="form-grid"><label>آدرس کنترلر<input name="ldap_host" value="' . e(setting('ldap_host', (string) cfg('ldap.host', ''))) . '"></label><label>پورت<input type="number" name="ldap_port" value="' . e(setting('ldap_port', (string) cfg('ldap.port', '389'))) . '"></label><label class="full">Base DN<input name="ldap_base_dn" value="' . e(setting('ldap_base_dn', (string) cfg('ldap.base_dn', ''))) . '"></label><label class="full">حساب سرویس<input name="ldap_bind_dn" value="' . e(setting('ldap_bind_dn', (string) cfg('ldap.bind_dn', ''))) . '"></label><label class="full">رمز حساب سرویس<input type="password" name="ldap_bind_password" placeholder="بدون تغییر"></label><label>Domain suffix<input name="ldap_domain_suffix" value="' . e(setting('ldap_domain_suffix', (string) cfg('ldap.domain_suffix', ''))) . '"></label></div><label class="switch-row"><input type="checkbox" name="ldap_ssl" value="1" ' . (setting('ldap_ssl', cfg('ldap.ssl') ? '1' : '0') === '1' ? 'checked' : '') . '> استفاده از LDAPS</label><button class="button" type="submit">ذخیره تنظیمات دامین</button></form></div>';
    echo '<div class="card form-card" id="domain-scan"' . (user_can($user, 'asset.remote_extract') ? '' : ' inert') . '><h2>اسکن دامنه و استخراج از شبکه</h2><p class="muted">همهٔ کامپیوترهای دامنه از Active Directory خوانده می‌شوند، سپس در صورت آنلاین بودن، اطلاعات سخت‌افزاری آن‌ها از راه دور دریافت و در شناسنامه ثبت می‌شود. برای دریافت سخت‌افزار، حساب وارد‌شده باید روی کلاینت‌ها دسترسی ادمین محلی داشته باشد.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="domain_scan"><input type="hidden" name="app_name" value="' . e($appName) . '"><p class="muted">نام کاربری و رمز ادمین در فرم استخراج هر نوبت وارد می‌شوند؛ رمز در تنظیمات پایگاه‌داده ذخیره نمی‌شود و برای ارسال آن HTTPS لازم است. حساب باید روی کلاینت‌ها عضو Administrators محلی باشد.</p><div class="form-grid"><label>دامنه (NetBIOS)<input name="domain_scan_domain" value="' . e(setting('domain_scan_domain', '')) . '" placeholder="COMPANY"></label><label>زمان انتظار پینگ (میلی‌ثانیه)<input type="number" name="domain_scan_timeout" min="500" max="30000" value="' . e(setting('domain_scan_timeout', '2500')) . '"></label><label>ترتیب پروتکل اتصال (۱.۳۷)<select name="domain_scan_protocols"><option value=""' . (setting('domain_scan_protocols', '') === '' ? ' selected' : '') . '>خودکار (اول DCOM، بعد WinRM)</option><option value="Wsman,Dcom"' . (setting('domain_scan_protocols', '') === 'Wsman,Dcom' ? ' selected' : '') . '>اول WinRM، بعد DCOM (اگر WinRM روی مقصدها فعال است)</option><option value="Dcom"' . (setting('domain_scan_protocols', '') === 'Dcom' ? ' selected' : '') . '>فقط DCOM</option><option value="Wsman"' . (setting('domain_scan_protocols', '') === 'Wsman' ? ' selected' : '') . '>فقط WinRM (نیازمند فعال‌بودن WinRM روی مقصدها)</option></select></label></div><p class="muted">خطاهای رایج استخراج («RPC/WMI در دسترس نیست» و «دسترسی رد شد») در صفحهٔ «اسکن دامنه» با راه‌حل گام‌به‌گام نمایش داده می‌شوند؛ پس از هر اصلاح، از همان صفحه «تلاش دوباره برای ناموفق‌ها» را بزنید.</p><button class="button" type="submit">ذخیره تنظیمات اسکن دامنه</button></form></div>';
    echo '<div class="card form-card" id="ldap-sync"><h2>معرفی کاربران دامین</h2><p class="muted">همه کاربران قابل مشاهده در Base DN را بدون نیاز به اولین ورود به سامانه معرفی می‌کند. حساب‌هایی که دیگر در دامین پیدا نشوند یا غیرفعال باشند، غیرفعال می‌شوند.</p><div class="actions" style="margin-top:0"><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="sync_ldap_users"><button class="button" type="submit">همگام‌سازی همه کاربران دامین</button></form><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="diagnose_ldap"><button class="button secondary" type="submit">تشخیص اتصال دامنه</button></form></div></div>';
    echo '<div class="card form-card settings-panel-full" id="ldap-role-map"><h2>نقش‌دهی گروه‌های AD</h2><p class="muted">هر خط یک JSON معتبر برای نقش باشد؛ مثال: {"agent":["CN=IT-Helpdesk,OU=Groups,DC=example,DC=local"]}. کاربران جدید دامینی بر اساس عضویت گروه نقش می‌گیرند و نقش کاربران فعلی دستی حفظ می‌شود.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_settings"><input type="hidden" name="settings_section" value="ldap"><input type="hidden" name="app_name" value="' . e($appName) . '"><label>تنظیم JSON نگاشت گروه به نقش<textarea name="ldap_group_map" rows="2" class="compact-textarea" placeholder="{&quot;agent&quot;:[&quot;CN=IT-Helpdesk,...&quot;]}">' . e(setting('ldap_group_map', '{}')) . '</textarea></label><button class="button secondary" type="submit">ذخیره نگاشت گروه‌ها</button></form></div>';
    echo '</div>';
    }

    if ($canSettingsUsers) {
    echo '<div class="card form-card settings-panel" id="users"' . ($canSettingsUsersEdit ? '' : ' inert') . '><h2>کاربران</h2>' . (user_is_primary_admin($user) ? '<p><a class="button secondary" href="index.php?page=local-accounts">کاربران محلی (پرسنل غذا) و انتخاب بازرسی</a></p>' : '') . '<p class="muted">کاربران دامینی با همگام‌سازی دامین یا در اولین ورود معرفی می‌شوند. ادمین اصلی می‌تواند از همین بخش نقش و وضعیت کاربران را تغییر دهد.</p><div class="category-list">';
    foreach ($users as $managedUser) {
        echo '<div><span><strong>' . e($managedUser['full_name']) . '</strong><small>' . e($managedUser['username']) . ' • ' . ($managedUser['auth_source'] === 'ldap' ? 'دامینی' : 'محلی') . ' • ' . ((int) $managedUser['is_active'] === 1 ? 'فعال' : 'غیرفعال') . '</small></span>';
        if ($canManageSettingsUsers) {
            $canLinkDomainCode = user_is_primary_admin($user) && (string) $managedUser['auth_source'] === 'ldap';
            $domainCodeDialogId = 'domain-code-link-dialog-' . (int) $managedUser['id'];
            $roleOptionsHtml = '';
            foreach (permission_roles() as $roleCode => $roleLabel) {
                $roleOptionsHtml .= '<option value="' . e($roleCode) . '" ' . ($managedUser['role'] === $roleCode ? 'selected' : '') . '>' . e($roleLabel) . '</option>';
            }
            echo '<form method="post" class="role-form">' . csrf_field() . '<input type="hidden" name="action" value="update_user_role"><input type="hidden" name="return_page" value="settings"><input type="hidden" name="user_id" value="' . (int) $managedUser['id'] . '"><input type="hidden" name="is_it_agent" value="' . ((int) $managedUser['is_it_agent'] === 1 ? '1' : '0') . '"><label>کد پرسنلی<input name="employee_number" value="' . e($managedUser['employee_number'] ?? '') . '" placeholder="کد دستگاه دستگاه حضور و غیاب"></label><label>کد ملی<input name="national_code" inputmode="numeric" value="' . e($managedUser['national_code'] ?? '') . '" placeholder="برای چاپ غذا"></label><select name="role">' . $roleOptionsHtml . '</select><select name="department_id"><option value="">بدون واحد</option>';
            foreach ($departments as $department) {
                echo '<option value="' . (int) $department['id'] . '" ' . ((int) $managedUser['department_id'] === (int) $department['id'] ? 'selected' : '') . '>' . e($department['name']) . '</option>';
            }
            echo '</select><label class="check-label"><input type="checkbox" name="is_active" value="1" ' . ((int) ($managedUser['is_active'] ?? 0) === 1 ? 'checked' : '') . '> فعال</label><label class="check-label"><input type="checkbox" name="is_primary_admin" value="1" ' . ((int) ($managedUser['is_primary_admin'] ?? 0) === 1 ? 'checked' : '') . '> ادمین اصلی</label><div class="role-form-actions"><button class="button secondary" type="submit">ذخیره</button>' . ($canLinkDomainCode ? '<button class="button domain-code-dialog-open" type="button" data-domain-code-dialog-open="' . e($domainCodeDialogId) . '">بررسی تغییر کد</button>' : '') . '</div></form>';
            if ($canLinkDomainCode) {
                echo '<dialog class="domain-code-link-dialog" id="' . e($domainCodeDialogId) . '" aria-labelledby="' . e($domainCodeDialogId) . '-title"><header><div><h3 id="' . e($domainCodeDialogId) . '-title">جستجو و بررسی کد پرسنلی</h3><p>این مرحله فقط رکوردهای واقعی را پیدا و برای بررسی نمایش می‌دهد؛ ادغام جداگانه تأیید می‌شود.</p></div><button class="button secondary" type="button" data-domain-code-dialog-close aria-label="بستن">بستن</button></header><p class="muted">حساب مقصد: ' . e((string) ($managedUser['full_name'] ?? '')) . ' · ' . e((string) ($managedUser['username'] ?? '')) . ' (#' . (int) $managedUser['id'] . ')<br>کد فعلی روی حساب دامین: ' . e((string) ($managedUser['employee_number'] ?? 'ثبت‌نشده')) . '</p><form method="post" class="domain-code-link-dialog-form">' . csrf_field() . '<input type="hidden" name="action" value="link_domain_food_by_code"><input type="hidden" name="user_id" value="' . (int) $managedUser['id'] . '"><label>کد قطعی پرسنل غذا<input name="employee_number" value="" placeholder="کد صحیح؛ مثلاً 335" required></label><label class="check-label"><input type="checkbox" name="confirm_code_link" value="1" required> تأیید می‌کنم رکوردهای محلی/غذاییِ همین کد متعلق به همین کارمند هستند.</label><div class="domain-code-dialog-actions"><button class="button" type="submit">جستجو و نمایش رکوردها</button><button class="button secondary" type="button" data-domain-code-dialog-close>انصراف</button></div><small class="muted">نام دخیل نیست؛ نتیجه‌ها قابل بررسی/انتخاب‌اند. پیش از ادغام پشتیبان ساخته می‌شود و ارجاع‌ها در یک تراکنش منتقل می‌شوند.</small></form></dialog>';
            }
        } else {
            echo '<span class="status admin-readonly">' . (user_is_primary_admin($managedUser) ? 'ادمین اصلی' : e(permission_roles()[$managedUser['role']] ?? $managedUser['role'])) . '</span>';
        }
        echo '</div>';
    }
    echo '</div>';
    echo '<div id="key-roles" class="settings-subform"' . ($canSettingsKeyRolesEdit ? '' : ' inert') . '><h3>نقش‌های کلیدی</h3><p class="muted">برای هر نقش حداکثر دو کاربر انتخاب کنید. «کارشناس IT» دسترسی ادمین اصلی می‌گیرد، «سوپروایز» نقش سوپروایزر را می‌گیرد و «بازرسی» مجوز ثبت ورود رسانه در CD/DVD را دارد.</p><form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save_key_roles">' . $roleCards . '<button class="button" type="submit">ذخیره نقش‌های کلیدی</button></form></div>';
    echo '</div>';
    }

    if ($canManageSettingsUsers) {
        $roleMeta = [
            'primary_admin' => ['icon' => 'owner', 'desc' => 'دسترسی کامل و غیرقابل‌تغییر', 'locked' => true],
            'supervisor' => ['icon' => 'supervisor', 'desc' => 'نظارت و مدیریت درخواست‌ها', 'locked' => false],
            'support_manager' => ['icon' => 'manager', 'desc' => 'مدیریت تیم پشتیبانی', 'locked' => false],
            'inspector' => ['icon' => 'inspector', 'desc' => 'بازرسی و کنترل CD/DVD', 'locked' => false],
            'manager' => ['icon' => 'manager', 'desc' => 'مدیریت واحد و اداره', 'locked' => false],
            'agent' => ['icon' => 'expert', 'desc' => 'رسیدگی به درخواست‌ها', 'locked' => false],
            'user' => ['icon' => 'user', 'desc' => 'کاربر عادی سامانه', 'locked' => false],
            'local_user' => ['icon' => 'user', 'desc' => 'کاربر محلی (پرسنل غذا)', 'locked' => false],
            'guard' => ['icon' => 'inspector', 'desc' => 'بازرسی', 'locked' => false],
        ];
        $allCodes = permission_all_codes();
        $catalog = permission_catalog();
        $totalAll = count($allCodes);
        echo '<div class="card form-card settings-panel roles-shell" id="roles">';
        echo '<div class="roles-shell-head"><div><h2>نقش‌ها و سطوح دسترسی</h2><p class="muted">یک نقش را انتخاب کنید و منوها/بخش‌هایی که اجازه دارد را تیک بزنید. هر چیزی تیک نخورد، برای آن نقش پنهان می‌ماند. دسترسی‌های «سامانه مدیریت غذا» برای همهٔ نقش‌ها قابل تنظیم است و کاربران دامینی و محلیِ دارای یک نقش، همین مجوزها را می‌گیرند.</p><p class="muted">تا وقتی دسترسی‌های یک نقش را <strong>ذخیره نکنید</strong>، پیش‌فرض‌های داخل کد اعمال می‌شود؛ به‌محض ذخیره، همان مقادیر شما اولویت می‌گیرد (حتی اگر چیزی تیک نخورده باشد) و هر وقت خواستید با «بازگردانی به پیش‌فرض کد» نقش دوباره به پیش‌فرض برمی‌گردد.</p></div></div>';
        echo '<div class="roles-layout"><aside class="roles-sidebar"><div class="roles-sidebar-head"><strong>نقش‌های سامانه</strong><small>برای ویرایش انتخاب کنید</small></div><div class="roles-list">';
        foreach (permission_roles() as $roleCode => $roleLabel) {
            $meta = $roleMeta[$roleCode] ?? ['icon' => 'user', 'desc' => '', 'locked' => false];
            $roleCount = $roleCode === 'primary_admin' ? $totalAll : count(array_intersect(role_permission_codes($roleCode), $allCodes));
            $pct = $totalAll > 0 ? (int) round($roleCount / $totalAll * 100) : 0;
            echo '<button type="button" class="role-item' . ($roleCode === 'primary_admin' ? ' locked' : '') . ($roleCode === 'supervisor' ? ' active' : '') . '" data-role-tab="' . e($roleCode) . '"><span class="role-avatar">' . permission_icon($meta['icon']) . '</span><span class="role-info"><span class="role-name">' . e($roleLabel) . ($meta['locked'] ? '<span class="lock-badge">قفل</span>' : '') . '</span><span class="role-stats"><span class="role-count">' . $roleCount . ' از ' . $totalAll . '</span><span class="mini-bar"><span class="mini-bar-fill" style="width:' . $pct . '%"></span></span></span></span></button>';
        }
        echo '</div></aside><section class="roles-content">';
        $firstRole = true;
        foreach (permission_roles() as $roleCode => $roleLabel) {
            $isPrimary = $roleCode === 'primary_admin';
            $active = $isPrimary ? $allCodes : array_values(array_intersect(role_permission_codes($roleCode), $allCodes));
            // ۱.۳۷.۷ — وضعیت این نقش: «پیش‌فرض کد» (دست‌نخورده) یا «ویرایش‌شده» (از فرم ذخیره شده).
            $roleIsCustom = !$isPrimary && role_permissions_custom($roleCode);
            $roleStateBadge = $isPrimary
                ? ''
                : '<span class="status ' . ($roleIsCustom ? 'warning' : 'done') . '" title="' . ($roleIsCustom
                    ? 'دسترسی‌های این نقش از فرم ذخیره شده و همین‌ها اعمال می‌شود؛ پیش‌فرض‌های کد روی آن اثر ندارند.'
                    : 'این نقش دست‌نخورده است؛ پیش‌فرض‌های داخل کد اعمال می‌شود و تغییرهای آیندهٔ کد خودکار می‌رسد.') . '">'
                . ($roleIsCustom ? 'ویرایش‌شده' : 'پیش‌فرض کد') . '</span>';
            echo '<form method="post" class="role-panel' . ($firstRole ? ' active' : '') . '" data-role-panel="' . e($roleCode) . '">' . csrf_field() . '<input type="hidden" name="action" value="save_role_permissions"><input type="hidden" name="role" value="' . e($roleCode) . '">';
            echo '<div class="role-panel-head"><div class="role-panel-title"><span class="role-icon-lg">' . permission_icon($roleMeta[$roleCode]['icon'] ?? 'user') . '</span><div><h3>' . e($roleLabel) . '</h3><p class="muted">' . ($isPrimary ? 'این نقش به همهٔ بخش‌ها دسترسی کامل دارد و قابل محدودکردن نیست.' : e($roleMeta[$roleCode]['desc'] ?? '')) . '</p></div>' . $roleStateBadge . '</div><div class="role-active-count"><small>دسترسی‌های فعال</small><strong data-role-count="' . e($roleCode) . '">' . count($active) . '<span>/' . $totalAll . '</span></strong></div></div>';
            if ($isPrimary) {
                echo '<div class="perm-locked-note"><span>قفل</span> ادمین اصلی همیشه به همهٔ بخش‌ها دسترسی دارد.</div>';
            } else {
                echo '<div class="perm-toolbar"><button type="button" class="toolbar-btn" data-perm-all="1">انتخاب همه</button><button type="button" class="toolbar-btn" data-perm-none="1">حذف همه</button><div class="perm-toolbar-spacer"></div><span class="perm-changes" data-role-changes hidden></span><button type="button" class="toolbar-btn warn" data-perm-reset hidden>بازگشت به حالت اولیه</button></div>';
                echo '<div class="perm-search"><input type="search" class="perm-search-input" data-perm-search placeholder="جست‌وجوی دسترسی یا گروه..."></div>';
                echo '<div class="groups-container">';
                foreach ($catalog as $group) {
                    // مجوزهای مدیریت غذا برای همهٔ نقش‌ها نمایش داده می‌شوند؛ این نقش مستقل از منبع ورود (دامینی/محلی) است.
                    $groupCodes = array_map(static fn (array $item): string => $item['code'], $group['items']);
                    $checkedCount = count(array_intersect($groupCodes, $active));
                    $isEmpty = $checkedCount === 0;
                    $isFull = $checkedCount === count($groupCodes);
                    $countClass = $isFull ? 'full' : ($isEmpty ? 'empty' : 'partial');
                    $pct = count($groupCodes) > 0 ? (int) round($checkedCount / count($groupCodes) * 100) : 0;
                    echo '<div class="group-card" data-perm-group-card="' . e($group['key']) . '" data-group-label="' . e($group['label']) . '"><div class="group-header" data-group-toggle="' . e($group['key']) . '"><span class="group-toggle">◀</span><span class="group-icon">' . permission_icon('g-' . $group['key']) . '</span><span class="group-info"><span class="group-name">' . e($group['label']) . '</span></span><span class="group-progress"><span class="group-count ' . $countClass . '" data-group-count>' . $checkedCount . ' از ' . count($groupCodes) . '</span><span class="group-bar"><span class="group-bar-fill" style="width:' . $pct . '%"></span></span></span><button type="button" class="group-select-all" data-group-all="' . e($group['key']) . '">' . ($isFull ? 'حذف همه' : 'انتخاب همه') . '</button></div><div class="group-body">';
                    // ویرایش (فرزند بخش) زیر والدش با تورفتگی نمایش داده می‌شود و خودش جدا از فهرست اصلی نیست.
                    $editMap = permission_edit_map();
                    $editChildCodes = array_values($editMap);
                    $itemsByCode = [];
                    foreach ($group['items'] as $gItem) {
                        $itemsByCode[$gItem['code']] = $gItem;
                    }
                    $renderPerm = static function (array $pItem, bool $isChild) use ($active, $group): void {
                        $pChecked = in_array($pItem['code'], $active, true);
                        echo '<label class="perm-item' . ($isChild ? ' perm-child' : '') . ($pChecked ? ' checked' : '') . '" data-perm-item' . ($isChild ? ' data-perm-edit="1"' : '') . ' data-perm-label="' . e($pItem['label']) . '" data-perm-group="' . e($group['key']) . '"><input type="checkbox" name="permissions[]" value="' . e($pItem['code']) . '"' . ($pChecked ? ' checked' : '') . '><span class="perm-check">✓</span><span class="perm-label">' . e($pItem['label']) . '</span></label>';
                    };
                    foreach ($group['items'] as $item) {
                        if (in_array($item['code'], $editChildCodes, true)) {
                            continue;
                        }
                        $renderPerm($item, false);
                        if (isset($editMap[$item['code']], $itemsByCode[$editMap[$item['code']]])) {
                            $renderPerm($itemsByCode[$editMap[$item['code']]], true);
                        }
                    }
                    echo '</div></div>';
                }
                echo '<div class="perm-empty" data-perm-empty hidden>دسترسی‌ای با این عبارت یافت نشد.</div>';
                echo '</div>';
                echo '<div class="role-actions"><button class="button" type="submit">ذخیره دسترسی‌های ' . e($roleLabel) . '</button>'
                    . '<button class="button secondary" type="submit" name="action" value="reset_role_defaults" data-confirm="دسترسی‌های این نقش به پیش‌فرض داخل کد برگردد؟ تنظیم دستی این نقش پاک می‌شود.">بازگردانی به پیش‌فرض کد</button>'
                    . '</div>';
            }
            echo '</form>';
            $firstRole = false;
        }
        echo '</section></div>';
        echo '<div class="roles-shell-foot"><form method="post" class="inline-action">' . csrf_field() . '<input type="hidden" name="action" value="reset_role_permissions"><button class="button secondary" type="submit">بازگرداندن همهٔ دسترسی‌ها به پیش‌فرض</button></form><button type="button" class="button secondary" data-copy-role>کپی از نقش دیگر</button></div>';
        echo '</div>';

        echo '<div class="copy-modal" data-copy-modal hidden><div class="copy-modal-card"><div class="copy-modal-head"><h3>کپی دسترسی‌ها</h3><p class="muted">دسترسی‌های کدام نقش روی نقش فعلی کپی شود؟</p></div><div class="copy-modal-body">';
        foreach (permission_roles() as $srcCode => $srcLabel) {
            echo '<button type="button" class="copy-option" data-copy-source="' . e($srcCode) . '"><span class="copy-avatar">' . permission_icon($roleMeta[$srcCode]['icon'] ?? 'user') . '</span><span class="copy-info"><strong>' . e($srcLabel) . '</strong><small>' . ($srcCode === 'primary_admin' ? $totalAll : count(array_intersect(role_permission_codes($srcCode), $allCodes))) . ' از ' . $totalAll . ' دسترسی فعال</small></span></button>';
        }
        echo '</div><div class="copy-modal-foot"><button type="button" class="button secondary" data-copy-cancel>انصراف</button><button type="button" class="button" data-copy-apply disabled>کپی کن</button></div></div></div>';
    }

    echo '</section>';
    render_footer();
    exit;
}

if ($page === 'logs') {
    $logUser = require_permission('logs.view');
    $levelFilter = valid_choice((string) ($_GET['level'] ?? ''), ['', 'error', 'warning', 'info', 'debug'], '');
    $logRows = system_logs_recent(200, $levelFilter);
    render_header('گزارش خطا', $logUser);
    echo '<section class="page-heading"><div><span class="eyebrow">System Logs</span><h1>گزارش خطا و رویدادهای سامانه</h1><p>خطاهای برنامه، استخراج شناسنامه و رویدادهای مهم اینجا ثبت می‌شوند. فایل خام: storage/logs/app.log</p></div><div class="actions"><a class="button secondary" href="index.php?page=logs">همه</a><a class="button secondary" href="index.php?page=logs&level=error">فقط خطاها</a><a class="button secondary" href="index.php?page=logs&level=info">اطلاعات</a></div></section>';
    echo '<section class="card ticket-table audit-table"><div class="table-head"><span>زمان</span><span>سطح</span><span>بخش</span><span>پیام</span><span>آدرس</span></div>';
    if ($logRows) {
        foreach ($logRows as $logRow) {
            $levelClass = $logRow['level'] === 'error' ? 'critical' : ($logRow['level'] === 'warning' ? 'urgent' : 'normal');
            echo '<div class="table-row"><span class="date-cell">' . e(persian_date($logRow['created_at'])) . '</span><span><em class="status ' . $levelClass . '">' . e($logRow['level']) . '</em></span><span>' . e($logRow['context']) . '</span><span class="muted">' . e($logRow['message']) . system_log_meta_html((string) ($logRow['meta_json'] ?? '')) . '</span><span class="muted">' . e($logRow['request_uri']) . '</span></div>';
        }
    } else {
        echo '<div class="table-row"><span class="muted">گزارشی ثبت نشده است.</span></div>';
    }
    echo '</section>';
    render_footer();
    exit;
}

if ($page !== 'dashboard') {
    redirect('index.php');
}

    $isStaff = is_staff_role($user['role']);
[$ticketWhere, $ticketParams] = ticket_scope($user);
$countQuery = db()->prepare('SELECT status, COUNT(*) AS total FROM tickets t ' . $ticketWhere . ' GROUP BY status');
$countQuery->execute($ticketParams);
$counts = ['new' => 0, 'manager_review' => 0, 'assigned' => 0, 'in_progress' => 0, 'waiting_user' => 0, 'open' => 0, 'pending' => 0, 'resolved' => 0, 'closed' => 0, 'cancelled' => 0];
foreach ($countQuery->fetchAll() as $count) {
    $counts[$count['status']] = (int) $count['total'];
}
$listQuery = db()->prepare('SELECT t.*, c.name AS category_name, d.name AS department_name, r.full_name AS requester_name, a.full_name AS assignee_name FROM tickets t LEFT JOIN categories c ON c.id = t.category_id LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to ' . $ticketWhere . ' ORDER BY t.updated_at DESC LIMIT 50');
$listQuery->execute($ticketParams);
$tickets = $listQuery->fetchAll();
render_header('داشبورد', $user);
$activeCount = $counts['new'] + $counts['manager_review'] + $counts['assigned'] + $counts['in_progress'] + $counts['waiting_user'] + $counts['pending'];
$cdDvdReady = cd_dvd_available();
echo '<section class="dashboard-hero"><div><span class="eyebrow">' . ($isStaff ? 'مرکز کنترل پشتیبانی' : 'فضای درخواست‌های شما') . '</span><h1>سلام ' . e($user['full_name']) . '،<br><strong>چه کمکی از ما می‌خواهید؟</strong></h1><p>درخواست جدید ثبت کنید یا وضعیت درخواست‌های قبلی را دنبال کنید.</p></div><a class="button hero-button" href="index.php?page=new-ticket"><span>＋</span> ثبت درخواست جدید</a></section><section class="stat-grid"><a class="stat-card accent" href="index.php?page=reports&amp;status=active" title="نمایش درخواست‌های در حال پیگیری"><span class="stat-icon">◈</span><div><strong>' . $activeCount . '</strong><small>در حال پیگیری</small></div></a><a class="stat-card" href="index.php?page=reports&amp;status=waiting_user" title="نمایش درخواست‌های در انتظار پاسخ کاربر"><span class="stat-icon blue">◌</span><div><strong>' . $counts['waiting_user'] . '</strong><small>در انتظار پاسخ کاربر</small></div></a><a class="stat-card" href="index.php?page=reports&amp;status=resolved" title="نمایش درخواست‌های حل‌شده و بسته‌شده"><span class="stat-icon green">✓</span><div><strong>' . ($counts['resolved'] + $counts['closed']) . '</strong><small>حل‌شده</small></div></a><a class="stat-card" href="index.php?page=reports&amp;status=cancelled" title="نمایش درخواست‌های لغوشده"><span class="stat-icon" style="background:#fef3f2;color:#b42318">×</span><div><strong>' . $counts['cancelled'] . '</strong><small>لغوشده</small></div></a><a class="stat-card" href="index.php?page=reports&amp;status=all" title="نمایش همهٔ درخواست‌های من در گزارش‌ها"><span class="stat-icon gray">▣</span><div><strong>' . count($tickets) . '</strong><small>نمایش داده‌شده</small></div></a></section>';
if ($cdDvdReady) {
    echo cd_dvd_render_surface($user, 'index.php');
     echo '<script src="assets/cd-dvd.js?v=3.4.9"></script>';
}
if (function_exists('food_order_render_pending_cancels_card')) {
    echo food_order_render_pending_cancels_card($user);
}
echo '<section class="list-section"><div class="section-title"><div><h2>' . ($isStaff ? 'آخرین درخواست‌ها' : 'درخواست‌های من') . '</h2><p>آخرین تغییرات در ابتدا نمایش داده می‌شود.</p></div><a href="index.php?page=new-ticket">ثبت درخواست ←</a></div>';
if (!$tickets) {
    echo '<div class="card empty-state"><div class="empty-icon">✦</div><h3>هنوز درخواستی ثبت نشده است</h3><p>برای شروع، اولین درخواست خود را ثبت کنید.</p><a class="button" href="index.php?page=new-ticket">ثبت اولین درخواست</a></div>';
} else {
    echo '<div class="card ticket-table"><div class="table-head"><span>عنوان درخواست</span><span>ثبت‌کننده</span><span>اولویت</span><span>وضعیت</span><span>آخرین تغییر</span></div>';
    foreach ($tickets as $ticket) {
        echo '<a class="table-row" href="index.php?page=ticket&id=' . (int) $ticket['id'] . '"><span class="ticket-title"><b>' . e(ticket_number((int) $ticket['id'])) . '</b><strong>' . e($ticket['subject']) . '</strong><small>' . e($ticket['category_name'] ?: 'بدون دسته‌بندی') . '</small></span><span>' . e($isStaff ? $ticket['requester_name'] : ($ticket['assignee_name'] ?: 'در انتظار تخصیص')) . '</span><span><i class="priority-dot ' . e($ticket['priority']) . '"></i>' . e(priority_label($ticket['priority'])) . '</span><span><em class="status ' . e($ticket['status']) . '">' . e(status_label($ticket['status'])) . '</em></span><span class="date-cell">' . persian_date($ticket['updated_at']) . ' ←</span></a>';
    }
    echo '</div>';
}
echo '</section>';
render_footer();
