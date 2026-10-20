<?php
declare(strict_types=1);

require_once __DIR__ . '/traffic-import.php';

// کنترل تردد مراجعین و میهمانان — پورت‌شده از کنترل_تردد.xlsm
// همه‌ی توابع با پیشوند traffic_ برای جلوگیری از تداخل نام با بقیه ماژول‌ها.

function traffic_can_manage(array $user): bool
{
    return function_exists('user_can') ? user_can($user, 'traffic.manage') : ($user['role'] ?? '') === 'admin';
}

function traffic_can_manage_destinations(array $user): bool
{
    return function_exists('user_can') ? user_can($user, 'traffic.destinations') : ($user['role'] ?? '') === 'admin';
}

function traffic_destinations(bool $activeOnly = true): array
{
    $sql = 'SELECT id, title, approver_name, is_active FROM traffic_destinations' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY title';
    return db()->query($sql)->fetchAll();
}

function traffic_approvers(): array
{
    return db()->query("SELECT DISTINCT approved_by FROM traffic_visits WHERE approved_by IS NOT NULL AND approved_by <> '' ORDER BY approved_by")->fetchAll(PDO::FETCH_COLUMN);
}

/** تأییدکنندهٔ تردد از مقصد فعال خوانده می‌شود؛ ورودی POST منبع معتبر نیست. */
function traffic_destination_approver(string $title, ?array $existingRecord = null): ?string
{
    $title = trim($title);
    if ($title === '') {
        return null;
    }
    $query = db()->prepare('SELECT approver_name FROM traffic_destinations WHERE title = ? AND is_active = 1 LIMIT 1');
    $query->execute([$title]);
    $approver = $query->fetchColumn();
    if ($approver !== false) {
        return trim((string) $approver) !== '' ? trim((string) $approver) : null;
    }
    if ($existingRecord !== null && $title === (string) ($existingRecord['meeting_with'] ?? '')) {
        return trim((string) ($existingRecord['approved_by'] ?? '')) !== '' ? trim((string) $existingRecord['approved_by']) : null;
    }
    throw new TrafficInputException('مقصد انتخاب‌شده معتبر یا فعال نیست.', 'meeting_with');
}

// شماره برگ به‌صورت تاریخ‌شمسی+شماره‌ترتیبی روزانه، مثل ۱۴۰۴۰۷۱۵-۰۰۰۱
function traffic_next_serial(): string
{
    [$jy, $jm, $jd] = gregorian_to_jalali((int) date('Y'), (int) date('n'), (int) date('j'));
    $prefix = sprintf('%04d%02d%02d', $jy, $jm, $jd);
    $query = db()->prepare('SELECT COUNT(*) FROM traffic_visits WHERE serial_no LIKE ?');
    $query->execute([$prefix . '-%']);
    $count = (int) $query->fetchColumn() + 1;
    return $prefix . '-' . sprintf('%04d', $count);
}

function traffic_find_last_visit(string $nationalCode, int $excludeId = 0): ?array
{
    $query = db()->prepare('SELECT * FROM traffic_visits WHERE national_code = ? AND id <> ? ORDER BY visit_date DESC, id DESC LIMIT 1');
    $query->execute([$nationalCode, $excludeId]);
    $row = $query->fetch();
    return $row ?: null;
}

function traffic_fetch_record(int $id): ?array
{
    $query = db()->prepare('SELECT * FROM traffic_visits WHERE id = ? LIMIT 1');
    $query->execute([$id]);
    $row = $query->fetch();
    return $row ?: null;
}

function traffic_query_records(array $filters, int $limit = 300, string $sort = '', string $dir = ''): array
{
    $where = [];
    $params = [];
    if ($filters['search'] !== '') {
        $where[] = '(v.full_name LIKE ? OR v.national_code LIKE ? OR v.serial_no LIKE ? OR v.company LIKE ?)';
        $like = '%' . $filters['search'] . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if ($filters['range'] === 'today') {
        $where[] = 'visit_date = CURDATE()';
    } elseif ($filters['range'] === 'week') {
        $where[] = 'visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)';
    }
    if ($filters['open_only']) {
        $where[] = 'exit_time IS NULL';
    }
    $sql = 'SELECT v.*, u.full_name AS recorder_name FROM traffic_visits v LEFT JOIN users u ON u.id = v.created_by';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sortMap = ['serial' => 'v.serial_no', 'date' => 'v.visit_date', 'name' => 'v.full_name', 'national' => 'v.national_code', 'entry' => 'v.entry_time', 'exit' => 'v.exit_time', 'meeting' => 'v.meeting_with', 'recorder' => 'u.full_name', '__tie' => 'v.id'];
    $sql .= log_sort_order($sortMap, $sort, $dir, ' ORDER BY v.visit_date DESC, v.id DESC') . ' LIMIT ' . $limit;
    $query = db()->prepare($sql);
    $query->execute($params);
    return $query->fetchAll();
}

function traffic_stats(): array
{
    $stats = [];
    $stats['today'] = (int) db()->query("SELECT COUNT(*) FROM traffic_visits WHERE visit_date = CURDATE()")->fetchColumn();
    $stats['week'] = (int) db()->query("SELECT COUNT(*) FROM traffic_visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)")->fetchColumn();
    $stats['inside'] = (int) db()->query("SELECT COUNT(*) FROM traffic_visits WHERE visit_date = CURDATE() AND exit_time IS NULL")->fetchColumn();
    $stats['total'] = (int) db()->query('SELECT COUNT(*) FROM traffic_visits')->fetchColumn();

    $stats['by_destination'] = db()->query("SELECT CASE WHEN is_notice = 1 THEN 'تردد خاص' ELSE COALESCE(NULLIF(meeting_with, ''), 'نامشخص') END AS title, COUNT(*) AS cnt FROM traffic_visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY CASE WHEN is_notice = 1 THEN 'تردد خاص' ELSE COALESCE(NULLIF(meeting_with, ''), 'نامشخص') END ORDER BY cnt DESC LIMIT 6")->fetchAll();

    $daily = [];
    $rows = db()->query("SELECT visit_date, COUNT(*) AS cnt FROM traffic_visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY visit_date")->fetchAll(PDO::FETCH_KEY_PAIR);
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} day"));
        $daily[] = ['date' => $d, 'label' => jalali_date($d, false), 'count' => (int) ($rows[$d] ?? 0)];
    }
    $stats['daily'] = $daily;
    return $stats;
}

function traffic_empty_stats(): array
{
    $daily = [];
    for ($i = 13; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} day"));
        $daily[] = ['date' => $date, 'label' => jalali_date($date, false), 'count' => 0];
    }
    return ['today' => 0, 'week' => 0, 'inside' => 0, 'total' => 0, 'by_destination' => [], 'daily' => $daily];
}

function traffic_export_report(): never
{
    $fromInput = trim((string) ($_GET['from_date'] ?? ''));
    $toInput = trim((string) ($_GET['to_date'] ?? ''));
    $fromGregorian = $fromInput !== '' ? jalali_input_to_gregorian($fromInput) : null;
    $toGregorian = $toInput !== '' ? jalali_input_to_gregorian($toInput, true) : null;
    if ($fromGregorian === null || $toGregorian === null) {
        http_response_code(422);
        header('Content-Type: text/plain; charset=utf-8');
        exit('تاریخ شروع و پایان را با تقویم شمسی و به‌شکل معتبر انتخاب کنید.');
    }
    $fromDate = substr($fromGregorian, 0, 10);
    $toDate = substr($toGregorian, 0, 10);
    if ($fromDate > $toDate) {
        http_response_code(422);
        header('Content-Type: text/plain; charset=utf-8');
        exit('تاریخ «از» باید زودتر یا برابر با تاریخ «تا» باشد.');
    }

    $availableDestinations = array_values(array_unique(array_map(
        static fn (array $destination): string => (string) $destination['title'],
        traffic_destinations()
    )));
    $requestedDestinations = $_GET['destinations'] ?? [];
    if (!is_array($requestedDestinations)) {
        $requestedDestinations = [];
    }
    $requestedDestinations = array_values(array_unique(array_filter(array_map(
        static fn (mixed $value): string => is_string($value) ? trim($value) : '',
        $requestedDestinations
    ), static fn (string $value): bool => $value !== '')));
    $selectedDestinations = array_values(array_intersect($availableDestinations, $requestedDestinations));
    $allDestinations = !empty($_GET['all_destinations'])
        || ($availableDestinations !== [] && count($selectedDestinations) === count($availableDestinations));
    if (!$allDestinations && $selectedDestinations === []) {
        http_response_code(422);
        header('Content-Type: text/plain; charset=utf-8');
        exit('یک مقصد را انتخاب کنید یا گزینهٔ «همه» را علامت بزنید.');
    }

    $where = ['v.visit_date >= ?', 'v.visit_date <= ?'];
    $params = [$fromDate, $toDate];
    if (!$allDestinations) {
        $where[] = 'v.meeting_with IN (' . implode(',', array_fill(0, count($selectedDestinations), '?')) . ')';
        array_push($params, ...$selectedDestinations);
    }
    $query = db()->prepare(
        'SELECT v.*, u.full_name AS recorder_name FROM traffic_visits v LEFT JOIN users u ON u.id = v.created_by WHERE '
        . implode(' AND ', $where)
        . ' ORDER BY v.visit_date DESC, v.entry_time DESC, v.id DESC'
    );
    $query->execute($params);
    $rows = [];
    $sortKeys = [];
    foreach ($query->fetchAll() as $record) {
        $isNotice = (int) ($record['is_notice'] ?? 0) === 1;
        $sortKeys[] = [(string) $record['visit_date'], (string) $record['entry_time'], count($rows)];
        $rows[] = [
            (string) $record['serial_no'],
            jalali_date((string) $record['visit_date'], false),
            ($isNotice ? 'تردد خاص: ' : '') . (string) $record['full_name'],
            $isNotice || empty($record['national_code']) ? '—' : (string) $record['national_code'],
            (string) ($record['phone'] ?? ''),
            (string) ($record['company'] ?? ''),
            $isNotice ? 'تردد خاص' : (string) ($record['meeting_with'] ?? ''),
            (string) ($record['approved_by'] ?? ''),
            substr((string) $record['entry_time'], 0, 5),
            $record['exit_time'] ? substr((string) $record['exit_time'], 0, 5) : '',
            (int) $record['no_visit'] === 1 ? 'بله' : 'خیر',
            (int) $record['with_car'] === 1 ? 'بله' : 'خیر',
            (int) $record['with_mobile'] === 1 ? 'بله' : 'خیر',
            $isNotice ? 'اطلاع ورود برای افزایش سقف مهمان غذا' : (string) ($record['description'] ?? ''),
            (string) ($record['recorder_name'] ?? ''),
        ];
    }
    // مرتب‌سازی: جدیدترین تاریخ و ساعت اول (همان ترتیب قبلی برای ترددهای عادی).
    usort($sortKeys, static fn (array $a, array $b): int => [$b[0], $b[1], $b[2]] <=> [$a[0], $a[1], $a[2]]);
    $rows = array_map(static fn (array $key): array => $rows[$key[2]], $sortKeys);
    $destinationLabel = $allDestinations ? 'همه مقصدها' : implode('، ', $selectedDestinations);
    $title = 'گزارش تردد مراجعین | ' . jalali_date($fromDate, false) . ' تا ' . jalali_date($toDate, false)
        . ' | ' . $destinationLabel . ' | تعداد: ' . count($rows);
    excel_download(
        'traffic-report-' . $fromDate . '-to-' . $toDate . '.xlsx',
        $title,
        ['شماره برگ', 'تاریخ', 'نام و نام خانوادگی', 'کد ملی', 'تلفن همراه', 'موسسه / شرکت', 'مقصد ملاقات', 'تأییدکننده', 'ساعت ورود', 'ساعت خروج', 'بدون بازدید', 'با خودرو', 'با تلفن همراه', 'هدف از تردد', 'ثبت‌کننده'],
        $rows,
        ['from' => $fromDate, 'to' => $toDate]
    );
}

/** خطای ورودی تردد همراه با نام فیلدی که باید فوکوس شود. */
class TrafficInputException extends RuntimeException
{
    public string $field;

    public function __construct(string $message, string $field = '')
    {
        parent::__construct($message);
        $this->field = $field;
    }
}

function traffic_validate_payload(array $data, bool $allowBlankNationalCode = false): array
{
    $name = trim((string) ($data['full_name'] ?? ''));
    $nationalCode = preg_replace('/\D/', '', strtr((string) ($data['national_code'] ?? ''), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
    if ($name === '') {
        throw new TrafficInputException('نام و نام خانوادگی الزامی است.', 'full_name');
    }
    if (!(($allowBlankNationalCode && $nationalCode === '') || valid_iranian_national_code((string) $nationalCode))) {
        throw new TrafficInputException('کد ملی وارد شده معتبر نیست.', 'national_code');
    }
    $phone = preg_replace('/\D/', '', strtr((string) ($data['phone'] ?? ''), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
    return [
        'full_name' => mb_substr($name, 0, 190),
        'national_code' => (string) $nationalCode,
        'phone' => $phone !== '' ? mb_substr($phone, 0, 20) : null,
        'company' => mb_substr(trim((string) ($data['company'] ?? '')), 0, 190) ?: null,
        'meeting_with' => mb_substr(trim((string) ($data['meeting_with'] ?? '')), 0, 190) ?: null,
        'approved_by' => mb_substr(trim((string) ($data['approved_by'] ?? '')), 0, 190) ?: null,
        'description' => mb_substr(trim((string) ($data['description'] ?? '')), 0, 5000) ?: null,
        'no_visit' => !empty($data['no_visit']) ? 1 : 0,
        'with_car' => !empty($data['with_car']) ? 1 : 0,
        'with_mobile' => !empty($data['with_mobile']) ? 1 : 0,
    ];
}

function traffic_visit_snapshot(array $record): array
{
    $fields = ['serial_no', 'visit_date', 'full_name', 'national_code', 'phone', 'company', 'entry_time', 'exit_time', 'no_visit', 'with_car', 'with_mobile', 'meeting_with', 'approved_by', 'description', 'is_notice'];
    $snapshot = [];
    foreach ($fields as $field) {
        $snapshot[$field] = $record[$field] ?? null;
    }
    return $snapshot;
}

function traffic_record_history(array $before, ?array $after, array $user, string $action = 'updated'): void
{
    $beforeSnapshot = traffic_visit_snapshot($before);
    $afterSnapshot = $after === null ? null : traffic_visit_snapshot($after);
    $changes = [];
    foreach ($beforeSnapshot as $field => $oldValue) {
        $newValue = $afterSnapshot[$field] ?? null;
        if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
            $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
        }
    }
    if ($changes === []) {
        return;
    }
    $stmt = db()->prepare('INSERT INTO traffic_visit_history (visit_id, serial_no, visitor_name, action, changed_by, before_json, after_json, changes_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        (int) $before['id'], (string) ($before['serial_no'] ?? ''), (string) ($before['full_name'] ?? ''), $action,
        (int) ($user['id'] ?? 0) ?: null,
        json_encode($beforeSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
        $afterSnapshot === null ? null : json_encode($afterSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
        json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
    ]);
}

function traffic_history_records(int $limit = 50, int $offset = 0): array
{
    $limit = max(1, min(200, $limit));
    $offset = max(0, $offset);
    return db()->query('SELECT h.*, u.full_name AS editor_name FROM traffic_visit_history h LEFT JOIN users u ON u.id = h.changed_by ORDER BY h.changed_at DESC, h.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset)->fetchAll(PDO::FETCH_ASSOC);
}

function traffic_history_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM traffic_visit_history')->fetchColumn();
}

function traffic_history_field_label(string $field): string
{
    static $labels = [
        'visit_date' => 'تاریخ مراجعه', 'full_name' => 'نام و نام خانوادگی', 'national_code' => 'کد ملی',
        'phone' => 'تلفن همراه', 'company' => 'موسسه / شرکت', 'entry_time' => 'ساعت ورود', 'exit_time' => 'ساعت خروج',
        'no_visit' => 'بدون بازدید', 'with_car' => 'با خودرو', 'with_mobile' => 'با تلفن همراه',
        'meeting_with' => 'ملاقات با', 'approved_by' => 'تاییدکننده', 'description' => 'توضیحات', 'is_notice' => 'نوع تردد',
    ];
    return $labels[$field] ?? $field;
}

function traffic_normalize_time_input(mixed $value): string
{
    return trim(strtr((string) $value, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '：' => ':',
    ]));
}

function traffic_history_value(string $field, mixed $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    if (in_array($field, ['no_visit', 'with_car', 'with_mobile', 'is_notice'], true)) {
        if ($field === 'is_notice') {
            return (int) $value === 1 ? 'تردد خاص' : 'تردد عادی';
        }
        return (int) $value === 1 ? 'بله' : 'خیر';
    }
    if (in_array($field, ['entry_time', 'exit_time'], true)) {
        return substr((string) $value, 0, 5);
    }
    if ($field === 'visit_date') {
        return jalali_date((string) $value, false);
    }
    return (string) $value;
}

function traffic_create_record(array $user): int
{
    $payload = traffic_validate_payload($_POST);
    $payload['approved_by'] = traffic_destination_approver((string) $payload['meeting_with']);
    $serial = traffic_next_serial();
    $stmt = db()->prepare('INSERT INTO traffic_visits (serial_no, visit_date, full_name, national_code, phone, company, entry_time, exit_time, no_visit, with_car, with_mobile, meeting_with, approved_by, description, created_by) VALUES (?, CURDATE(), ?, ?, ?, ?, CURTIME(), NULL, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$serial, $payload['full_name'], $payload['national_code'], $payload['phone'], $payload['company'], $payload['no_visit'], $payload['with_car'], $payload['with_mobile'], $payload['meeting_with'], $payload['approved_by'], $payload['description'], (int) $user['id']]);
    $id = (int) db()->lastInsertId();
    save_audit((int) $user['id'], 'traffic_visit_created', null, ['visit_id' => $id, 'serial' => $serial]);
    return $id;
}

function traffic_update_record(array $user): void
{
    if (!traffic_can_manage($user)) {
        throw new RuntimeException('ویرایش تردد فقط برای مدیران کنترل تردد مجاز است.');
    }
    $id = (int) ($_POST['record_id'] ?? 0);
    $record = traffic_fetch_record($id);
    if (!$record) {
        throw new RuntimeException('رکورد تردد پیدا نشد.');
    }
    $payload = traffic_validate_payload($_POST, (int) ($record['is_notice'] ?? 0) === 1);
    $payload['approved_by'] = traffic_destination_approver((string) $payload['meeting_with'], $record);
    $visitDate = trim((string) ($_POST['visit_date'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
        throw new TrafficInputException('تاریخ مراجعه معتبر نیست.', 'visit_date');
    }
    [$year, $month, $day] = array_map('intval', explode('-', $visitDate));
    if (!checkdate($month, $day, $year)) {
        throw new TrafficInputException('تاریخ مراجعه معتبر نیست.', 'visit_date');
    }
    $parseTime = static function (mixed $value, bool $required, string $field): ?string {
        $raw = traffic_normalize_time_input($value);
        if ($raw === '' && !$required) {
            return null;
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $raw)) {
            throw new TrafficInputException($field === 'entry_time' ? 'ساعت ورود معتبر نیست.' : 'ساعت خروج معتبر نیست.', $field);
        }
        return $raw . ':00';
    };
    $entryTime = $parseTime($_POST['entry_time'] ?? '', true, 'entry_time');
    $exitTime = $parseTime($_POST['exit_time'] ?? '', false, 'exit_time');
    if ($exitTime !== null && $exitTime < (string) $entryTime) {
        throw new TrafficInputException('ساعت خروج نمی‌تواند قبل از ساعت ورود باشد.', 'exit_time');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE traffic_visits SET visit_date = ?, full_name = ?, national_code = ?, phone = ?, company = ?, entry_time = ?, exit_time = ?, no_visit = ?, with_car = ?, with_mobile = ?, meeting_with = ?, approved_by = ?, description = ? WHERE id = ?');
        $stmt->execute([$visitDate, $payload['full_name'], $payload['national_code'] !== '' ? $payload['national_code'] : null, $payload['phone'], $payload['company'], $entryTime, $exitTime, $payload['no_visit'], $payload['with_car'], $payload['with_mobile'], $payload['meeting_with'], $payload['approved_by'], $payload['description'], $id]);
        $updated = traffic_fetch_record($id);
        if (!$updated) {
            throw new RuntimeException('رکورد تردد پس از ذخیره پیدا نشد.');
        }
        traffic_record_history($record, $updated, $user, 'updated');
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    save_audit((int) $user['id'], 'traffic_visit_updated', null, ['visit_id' => $id]);
}

function traffic_delete_record(array $user): void
{
    if (!traffic_can_manage($user)) {
        throw new RuntimeException('حذف تردد فقط برای مدیران کنترل تردد مجاز است.');
    }
    $id = (int) ($_POST['record_id'] ?? 0);
    $record = traffic_fetch_record($id);
    if (!$record) {
        throw new RuntimeException('رکورد تردد پیدا نشد.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        traffic_record_history($record, null, $user, 'deleted');
        $pdo->prepare('DELETE FROM traffic_visits WHERE id = ?')->execute([$id]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    save_audit((int) $user['id'], 'traffic_visit_deleted', null, ['visit_id' => $id]);
}

function traffic_set_exit(array $user): void
{
    $id = (int) ($_POST['record_id'] ?? 0);
    $record = traffic_fetch_record($id);
    if (!$record) {
        throw new RuntimeException('رکورد تردد پیدا نشد.');
    }
    if (!traffic_can_manage($user)) {
        throw new RuntimeException('ثبت ساعت خروج فقط برای مدیران کنترل تردد مجاز است.');
    }
    if (!empty($record['exit_time'])) {
        throw new TrafficInputException('ساعت خروج این تردد قبلاً ثبت شده است.');
    }
    $isToday = (string) $record['visit_date'] === (string) db()->query('SELECT CURDATE()')->fetchColumn();
    $exitTime = null;
    if (!$isToday) {
        // تردد روز قبل: ساعت خروج واقعی باید دستی وارد شود، نه ساعت فعلی
        $raw = traffic_normalize_time_input($_POST['exit_time'] ?? '');
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $raw)) {
            throw new TrafficInputException('برای ترددهای روزهای قبل، ساعت خروج واقعی را وارد کنید.', 'exit_time');
        }
        $exitTime = $raw . ':00';
        if ($exitTime < (string) $record['entry_time']) {
            throw new TrafficInputException('ساعت خروج نمی‌تواند قبل از ساعت ورود باشد.', 'exit_time');
        }
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $update = $isToday
            ? $pdo->prepare('UPDATE traffic_visits SET exit_time = CURTIME() WHERE id = ? AND exit_time IS NULL')
            : $pdo->prepare('UPDATE traffic_visits SET exit_time = ? WHERE id = ? AND exit_time IS NULL');
        $update->execute($isToday ? [$id] : [$exitTime, $id]);
        if ($update->rowCount() !== 1) {
            throw new TrafficInputException('ثبت ساعت خروج انجام نشد؛ وضعیت تردد را دوباره بررسی کنید.');
        }
        $updated = traffic_fetch_record($id);
        if (!$updated) {
            throw new RuntimeException('رکورد تردد پس از ثبت خروج پیدا نشد.');
        }
        traffic_record_history($record, $updated, $user, 'exit_set');
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    save_audit((int) $user['id'], 'traffic_visit_exit_set', null, ['visit_id' => $id]);
}

/** خودترمیمی ستون‌های تردد خاص: اگر مهاجرت اجرا نشده باشد، همین‌جا اضافه/اصلاح می‌شود (یک‌بار در هر درخواست). */
function traffic_ensure_notice_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo = db();
        $cols = [];
        foreach ($pdo->query("SHOW COLUMNS FROM traffic_visits")->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string) $c['Field'])] = $c;
        }
        if (!isset($cols['is_notice'])) {
            $pdo->exec('ALTER TABLE traffic_visits ADD COLUMN is_notice TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (isset($cols['national_code']) && strtoupper((string) $cols['national_code']['Null']) === 'NO') {
            $pdo->exec('ALTER TABLE traffic_visits MODIFY national_code VARCHAR(10) NULL');
        }
    } catch (Throwable $e) {
        // اگر دسترسی DDL نباشد، خطا همان‌جا ثبت می‌شود و صفحه از کار نمی‌افتد.
        if (function_exists('app_error_log')) {
            app_error_log('traffic schema check: ' . $e->getMessage());
        }
    }
}

traffic_ensure_notice_schema();

/** تردد خاص: فقط نام؛ ورود همان لحظه ثبت می‌شود، برگه ندارد و مثل بقیهٔ ترددها در فهرست‌ها و سقف مهمان شمرده می‌شود. */
function traffic_notice_create(array $user): int
{
    traffic_ensure_notice_schema();
    $name = trim((string) ($_POST['full_name'] ?? ''));
    if ($name === '') {
        throw new TrafficInputException('نام و نام خانوادگی الزامی است.', 'full_name');
    }
    $name = mb_substr($name, 0, 190);
    $serial = traffic_next_serial();
    $stmt = db()->prepare('INSERT INTO traffic_visits (serial_no, visit_date, full_name, national_code, entry_time, exit_time, no_visit, with_car, with_mobile, is_notice, created_by) VALUES (?, CURDATE(), ?, NULL, CURTIME(), NULL, 0, 0, 0, 1, ?)');
    $stmt->execute([$serial, $name, (int) $user['id']]);
    $id = (int) db()->lastInsertId();
    save_audit((int) $user['id'], 'traffic_notice_created', null, ['visit_id' => $id, 'serial' => $serial]);
    return $id;
}

function traffic_save_destination(array $user): void
{
    if (!traffic_can_manage_destinations($user)) {
        throw new RuntimeException('مدیریت مقصدها فقط برای نقش دارای دسترسی مقاصد تردد مجاز است.');
    }
    $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 190);
    $approver = mb_substr(trim((string) ($_POST['approver_name'] ?? '')), 0, 190);
    if ($title === '' || $approver === '') {
        throw new RuntimeException('عنوان مقصد و نام تأییدکننده را وارد کنید.');
    }
    $exists = db()->prepare('SELECT COUNT(*) FROM traffic_destinations WHERE title = ?');
    $exists->execute([$title]);
    if ((int) $exists->fetchColumn() > 0) {
        throw new RuntimeException('این عنوان مقصد قبلاً ثبت شده است.');
    }
    db()->prepare('INSERT INTO traffic_destinations (title, approver_name) VALUES (?, ?)')->execute([$title, $approver !== '' ? $approver : null]);
}

function traffic_update_destination(array $user): void
{
    if (!traffic_can_manage_destinations($user)) {
        throw new RuntimeException('مدیریت مقصدها فقط برای نقش دارای دسترسی مقاصد تردد مجاز است.');
    }
    $id = (int) ($_POST['destination_id'] ?? 0);
    $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 190);
    $approver = mb_substr(trim((string) ($_POST['approver_name'] ?? '')), 0, 190);
    if ($id < 1 || $title === '' || $approver === '') {
        throw new RuntimeException('شناسه، عنوان مقصد و نام تأییدکننده را به‌درستی وارد کنید.');
    }
    $exists = db()->prepare('SELECT COUNT(*) FROM traffic_destinations WHERE title = ? AND id <> ?');
    $exists->execute([$title, $id]);
    if ((int) $exists->fetchColumn() > 0) {
        throw new RuntimeException('این عنوان مقصد قبلاً ثبت شده است.');
    }
    $update = db()->prepare('UPDATE traffic_destinations SET title = ?, approver_name = ? WHERE id = ?');
    $update->execute([$title, $approver !== '' ? $approver : null, $id]);
    if ($update->rowCount() < 1) {
        $check = db()->prepare('SELECT COUNT(*) FROM traffic_destinations WHERE id = ?');
        $check->execute([$id]);
        if ((int) $check->fetchColumn() < 1) {
            throw new RuntimeException('مقصد برای ویرایش پیدا نشد.');
        }
    }
}

function traffic_delete_destination(array $user): void
{
    if (!traffic_can_manage_destinations($user)) {
        throw new RuntimeException('مدیریت مقصدها فقط برای نقش دارای دسترسی مقاصد تردد مجاز است.');
    }
    $id = (int) ($_POST['destination_id'] ?? 0);
    $update = db()->prepare('UPDATE traffic_destinations SET is_active = 0 WHERE id = ? AND is_active = 1');
    $update->execute([$id]);
}

/** مقدارهای فرم ثبت تردد برای پر کردن دوباره پس از خطا. */
function traffic_form_old_values(): array
{
    $keys = ['national_code', 'full_name', 'phone', 'company', 'approved_by', 'description', 'meeting_with', 'visit_date', 'entry_time', 'exit_time', 'no_visit', 'with_car', 'with_mobile'];
    $old = [];
    foreach ($keys as $key) {
        $old[$key] = mb_substr(trim((string) ($_POST[$key] ?? '')), 0, 500);
    }
    return $old;
}

/** خطا را برای نمایش بالای فرم ذخیره می‌کند و کاربر را به همان زبانه برمی‌گرداند. */
function traffic_remember_form_error(RuntimeException $exception, string $tab): never
{
    $field = $exception instanceof TrafficInputException ? $exception->field : '';
    $_SESSION['traffic_form_error'] = [
        'message' => $exception->getMessage(),
        'field' => $field,
        'tab' => $tab,
        'old' => traffic_form_old_values(),
        'record_id' => (int) ($_POST['record_id'] ?? 0),
    ];
    $suffix = $tab === 'edit' ? '&id=' . (int) ($_POST['record_id'] ?? 0) : '';
    redirect('index.php?page=traffic-control&tab=' . $tab . $suffix);
}

/** همهٔ ترددهای بدون خروج (امروز و روزهای قبل). */
function traffic_open_visits(): array
{
    return db()->query('SELECT id, serial_no, visit_date, full_name, national_code, entry_time, meeting_with, is_notice FROM traffic_visits WHERE exit_time IS NULL ORDER BY visit_date ASC, entry_time ASC, id ASC LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
}

function traffic_handle_post(string $action, array $user): void
{
    if (in_array($action, ['traffic_import_preview', 'traffic_import_apply'], true)) {
        traffic_import_handle_post($action, $user);
    }
    if (!function_exists('user_can') || !user_can($user, 'traffic.manage')) {
        return;
    }
    if ($action === 'traffic_create' && !empty($_POST['notice_only'])) {
        try {
            traffic_notice_create($user);
        } catch (RuntimeException $exception) {
            traffic_remember_form_error($exception, 'new');
        }
        flash('success', 'تردد خاص ثبت شد.');
        redirect('index.php?page=traffic-control&tab=new');
    }
    if ($action === 'traffic_create') {
        try {
            $id = traffic_create_record($user);
        } catch (RuntimeException $exception) {
            traffic_remember_form_error($exception, 'new');
        }
        flash('success', 'تردد با شماره برگ ثبت شد.');
        redirect('index.php?page=traffic-control&tab=new&created=' . $id);
    }
    if ($action === 'traffic_update') {
        try {
            traffic_update_record($user);
        } catch (RuntimeException $exception) {
            traffic_remember_form_error($exception, 'edit');
        }
        flash('success', 'اطلاعات تردد به‌روزرسانی شد.');
        redirect('index.php?page=traffic-control&tab=history');
    }
    if ($action === 'traffic_delete') {
        traffic_delete_record($user);
        flash('success', 'رکورد تردد حذف شد.');
        redirect('index.php?page=traffic-control&tab=history');
    }
    if ($action === 'traffic_set_exit') {
        $returnTab = valid_choice((string) ($_POST['return_tab'] ?? 'history'), ['dashboard', 'history'], 'history');
        try {
            traffic_set_exit($user);
        } catch (RuntimeException $exception) {
            traffic_remember_form_error($exception, $returnTab);
        }
        flash('success', 'ساعت خروج ثبت شد.');
        redirect('index.php?page=traffic-control&tab=' . $returnTab);
    }
    if ($action === 'traffic_add_destination') {
        if (!traffic_can_manage_destinations($user)) {
            return;
        }
        traffic_save_destination($user);
        flash('success', 'مقصد جدید اضافه شد.');
        redirect('index.php?page=traffic-control&tab=destinations');
    }
    if ($action === 'traffic_update_destination') {
        traffic_update_destination($user);
        flash('success', 'عنوان مقصد و تأییدکننده به‌روزرسانی شد.');
        redirect('index.php?page=traffic-control&tab=destinations');
    }
    if ($action === 'traffic_delete_destination') {
        if (!traffic_can_manage_destinations($user)) {
            return;
        }
        traffic_delete_destination($user);
        flash('success', 'مقصد غیرفعال شد.');
        redirect('index.php?page=traffic-control&tab=destinations');
    }
}

// ------------------------------------------------------------
// جست‌وجوی زنده با کد ملی (AJAX) — معادل txtNationalCode_BeforeUpdate در اکسل
// ------------------------------------------------------------
function traffic_api_handle(string $mode, array $user): never
{
    header('Content-Type: application/json; charset=utf-8');
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'نیاز به ورود'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (function_exists('user_can') && !user_can($user, 'traffic.view')) {
        http_response_code(403);
        echo json_encode(['error' => 'دسترسی مجاز نیست'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($mode === 'lookup') {
        $code = preg_replace('/\D/', '', (string) ($_GET['code'] ?? ''));
        if (!valid_iranian_national_code((string) $code)) {
            echo json_encode(['valid' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $last = traffic_find_last_visit((string) $code);
        if (!$last) {
            echo json_encode(['valid' => true, 'found' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $isToday = $last['visit_date'] === date('Y-m-d');
        $needsExit = $isToday && $last['exit_time'] === null;
        echo json_encode([
            'valid' => true,
            'found' => true,
            'needs_exit_only' => $needsExit,
            'record_id' => (int) $last['id'],
            'full_name' => $last['full_name'],
            'phone' => $last['phone'],
            'company' => $last['company'],
            'meeting_with' => $last['meeting_with'],
            'approved_by' => $last['approved_by'],
            'last_visit_label' => jalali_date($last['visit_date'], false) . ' ساعت ' . substr((string) $last['entry_time'], 0, 5),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['error' => 'نامعتبر'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------
// صفحه‌ی اصلی: زیرفرم‌های داشبورد / ثبت تردد / سوابق / مقصدها
// ------------------------------------------------------------
function traffic_render_page(array $user): never
{
    if (function_exists('require_permission')) {
        $user = require_permission('traffic.view');
    }
    $tab = valid_choice((string) ($_GET['tab'] ?? 'dashboard'), ['dashboard', 'new', 'history', 'destinations', 'import', 'edit', 'changes'], 'dashboard');
    if ($tab === 'import') {
        $user = require_permission('traffic.manage');
        if (isset($_GET['template'])) {
            traffic_import_download_template(valid_choice((string) $_GET['template'], ['xlsx', 'csv'], 'xlsx'));
        }
    }
    if (in_array($tab, ['edit', 'changes'], true)) {
        $user = require_permission('traffic.manage');
    }
    $filters = [
        'search' => trim((string) ($_GET['search'] ?? '')),
        'range' => valid_choice((string) ($_GET['range'] ?? 'week'), ['today', 'week', 'all'], 'week'),
        'open_only' => !empty($_GET['open_only']),
    ];
    $formError = null;
    $formOld = [];
    if (isset($_SESSION['traffic_form_error']) && is_array($_SESSION['traffic_form_error']) && ($_SESSION['traffic_form_error']['tab'] ?? '') === $tab) {
        $formError = $_SESSION['traffic_form_error'];
        $formOld = is_array($formError['old'] ?? null) ? $formError['old'] : [];
        unset($_SESSION['traffic_form_error']);
    }
    $openVisits = [];
    $openError = '';
    $stats = traffic_empty_stats();
    $statsError = '';
    $dailyMax = 1;
    $destinationMax = 1;
    if ($tab === 'dashboard') {
        try {
            $openVisits = traffic_open_visits();
        } catch (Throwable $exception) {
            $openError = 'فهرست ترددهای بدون خروج دریافت نشد.';
        }
        try {
            $stats = array_replace($stats, traffic_stats());
            $dailyRows = [];
            foreach ((array) $stats['daily'] as $day) {
                if (!is_array($day)) {
                    continue;
                }
                $count = max(0, (int) ($day['count'] ?? 0));
                $dailyRows[] = [
                    'date' => (string) ($day['date'] ?? ''),
                    'label' => (string) ($day['label'] ?? ''),
                    'count' => $count,
                ];
                $dailyMax = max($dailyMax, $count);
            }
            $stats['daily'] = $dailyRows !== [] ? $dailyRows : traffic_empty_stats()['daily'];

            $destinationRows = [];
            foreach ((array) $stats['by_destination'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $count = max(0, (int) ($row['cnt'] ?? 0));
                $destinationRows[] = [
                    'title' => trim((string) ($row['title'] ?? '')) ?: 'نامشخص',
                    'cnt' => $count,
                ];
                $destinationMax = max($destinationMax, $count);
            }
            $stats['by_destination'] = $destinationRows;
            foreach (['today', 'week', 'inside', 'total'] as $key) {
                $stats[$key] = max(0, (int) ($stats[$key] ?? 0));
            }
        } catch (Throwable $exception) {
            $dashboardError = 'Traffic dashboard stats failed: ' . $exception->getMessage();
            if (function_exists('system_log')) {
                system_log('error', 'traffic-dashboard', $dashboardError, ['file' => basename($exception->getFile()), 'line' => $exception->getLine()]);
            } else {
                app_error_log($dashboardError);
            }
            $stats = traffic_empty_stats();
            $statsError = 'آمار داشبورد از پایگاه داده دریافت نشد؛ پیام خطا در گزارش خطای سرور ثبت شده است.';
        }
    }
    $destinations = in_array($tab, ['new', 'history', 'edit'], true) ? traffic_destinations() : [];
    $approvers = []; // تأییدکننده از تنظیم مقصد خوانده می‌شود؛ ورود دستی وجود ندارد.
    $editRecord = $tab === 'edit' ? traffic_fetch_record((int) ($_GET['id'] ?? 0)) : null;
    $changePage = max(1, (int) ($_GET['p'] ?? 1));
    $changeCount = $tab === 'changes' ? traffic_history_count() : 0;
    $changePerPage = 50;
    $changePageCount = max(1, (int) ceil($changeCount / $changePerPage));
    $changePage = min($changePage, $changePageCount);
    $changeRows = $tab === 'changes' ? traffic_history_records($changePerPage, ($changePage - 1) * $changePerPage) : [];
    $createdId = (int) ($_GET['created'] ?? 0);
    $lastCreated = $createdId ? traffic_fetch_record($createdId) : null;

    render_header('کنترل تردد', $user);
    ?>
    <section class="traffic-page">
        <header class="cd-dvd-banner">
            <div><span class="eyebrow">ثبت و پیگیری مراجعین و میهمانان</span><h1>کنترل تردد</h1></div>
            <a class="button" href="index.php">بازگشت به داشبورد</a>
        </header>
        <nav class="cd-dvd-tabs" aria-label="زیرفرم‌های کنترل تردد">
            <a class="<?= $tab === 'dashboard' ? 'active' : '' ?>" href="index.php?page=traffic-control&amp;tab=dashboard">📊 داشبورد</a>
            <a class="<?= $tab === 'new' ? 'active' : '' ?>" href="index.php?page=traffic-control&amp;tab=new">➕ ثبت تردد</a>
            <a class="<?= $tab === 'history' ? 'active' : '' ?>" href="index.php?page=traffic-control&amp;tab=history">📋 سوابق</a>
            <?php if (traffic_can_manage($user)): ?><a class="<?= $tab === 'changes' ? 'active' : '' ?>" href="index.php?page=traffic-control&amp;tab=changes">🕘 تاریخچهٔ ویرایش‌ها</a><?php endif; ?>
            <?php if (traffic_can_manage_destinations($user)): ?><a class="<?= $tab === 'destinations' ? 'active' : '' ?>" href="index.php?page=traffic-control&amp;tab=destinations">🏷️ مقاصد ملاقات و تأییدکنندگان</a><?php endif; ?>
        </nav>
        <?php if ($formError): ?>
        <div class="alert danger" role="alert" tabindex="-1" id="traffic-form-error" data-focus-form="<?= $tab === 'new' ? 'traffic-new-form' : '' ?>" data-focus-field="<?= e((string) $formError['field']) ?>"><?= e((string) $formError['message']) ?></div>
        <?php endif; ?>

        <?php if ($tab === 'dashboard'): ?>
        <section class="traffic-dashboard">
            <div class="traffic-dashboard-head">
                <div><span class="eyebrow">نمای کلی مراجعات</span><h2>امروز در ورودی سازمان چه می‌گذرد؟</h2></div>
                <a class="button traffic-dashboard-action" href="index.php?page=traffic-control&amp;tab=new">＋ ثبت تردد جدید</a>
            </div>
            <?php if ($statsError !== ''): ?><div class="alert danger traffic-dashboard-status" role="alert"><?= e($statsError) ?></div><?php elseif ($stats['total'] === 0): ?><div class="traffic-dashboard-empty"><span class="traffic-dashboard-empty-icon" aria-hidden="true">◌</span><div><strong>هنوز ترددی ثبت نشده است</strong></div><a class="button traffic-dashboard-empty-action" href="index.php?page=traffic-control&amp;tab=new">ثبت اولین تردد</a></div><?php endif; ?>
            <div class="traffic-stat-grid">
                <article class="traffic-stat-card is-today"><div class="traffic-stat-top"><span class="traffic-stat-icon">◷</span><span class="traffic-stat-period">امروز</span></div><strong><?= (int) $stats['today'] ?></strong><small>تردد ثبت‌شده</small></article>
                <article class="traffic-stat-card is-week"><div class="traffic-stat-top"><span class="traffic-stat-icon">↗</span><span class="traffic-stat-period">۷ روز</span></div><strong><?= (int) $stats['week'] ?></strong><small>تردد در هفتهٔ اخیر</small></article>
                <article class="traffic-stat-card is-inside"><div class="traffic-stat-top"><span class="traffic-stat-icon">●</span><span class="traffic-stat-period">اکنون</span></div><strong><?= (int) $stats['inside'] ?></strong><small>افرادِ بدون ثبت خروج</small></article>
                <article class="traffic-stat-card is-total"><div class="traffic-stat-top"><span class="traffic-stat-icon">▤</span><span class="traffic-stat-period">همهٔ سوابق</span></div><strong><?= (int) $stats['total'] ?></strong><small>مجموع ترددهای ثبت‌شده</small></article>
            </div>
            <div class="traffic-dashboard-grid">
                <section class="card traffic-dashboard-panel traffic-trend-panel">
                    <div class="traffic-panel-heading"><div><span class="traffic-panel-kicker">روند مراجعه</span><h3>ترددهای ۱۴ روز اخیر</h3></div><span class="traffic-period-pill">دو هفته</span></div>
                    <div class="traffic-bars" role="img" aria-label="نمودار تعداد ترددهای روزانه در ۱۴ روز اخیر">
                        <?php foreach ($stats['daily'] as $day): ?>
                            <div class="traffic-bar" title="<?= e($day['label'] . ': ' . $day['count']) ?>">
                                <b><?= (int) $day['count'] ?></b>
                                <span style="height: <?= max(4, (int) round((int) $day['count'] / $dailyMax * 100)) ?>%"></span>
                                <small><?= e($day['label']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="card traffic-dashboard-panel traffic-destination-panel">
                    <div class="traffic-panel-heading"><div><span class="traffic-panel-kicker">۳۰ روز گذشته</span><h3>مقصدهای پرتردد</h3></div><span class="traffic-period-pill">پُرمراجعه‌ها</span></div>
                    <div class="traffic-destination-list">
                        <?php foreach ($stats['by_destination'] as $index => $row): ?>
                            <div class="traffic-destination-row">
                                <div class="traffic-destination-info"><span class="traffic-destination-rank"><?= sprintf('%02d', $index + 1) ?></span><span class="traffic-destination-name"><b><?= e($row['title']) ?></b><small><?= (int) $row['cnt'] ?> مراجعه</small></span><strong><?= (int) $row['cnt'] ?></strong></div>
                                <div class="traffic-destination-track"><span style="width: <?= max(4, (int) round((int) $row['cnt'] / $destinationMax * 100)) ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$stats['by_destination']): ?><p class="muted traffic-destination-empty">هنوز داده‌ای ثبت نشده است.</p><?php endif; ?>
                    </div>
                </section>
            </div>
            <section class="card traffic-dashboard-panel" id="traffic-open-panel" data-focus-panel="1">
                <div class="traffic-panel-heading"><div><span class="traffic-panel-kicker">بدون خروج (همهٔ روزها)</span><h3>ترددهای بدون خروج (<?= (int) count($openVisits) ?>)</h3></div><span class="traffic-period-pill">ثبت سریع خروج</span></div>
                <?php if ($openError !== ''): ?><div class="alert danger"><?= e($openError) ?></div>
                <?php elseif (!$openVisits): ?><p class="muted">در حال حاضر فردی بدون خروج ثبت نشده است.</p>
                <?php else: ?>
                <div class="traffic-open-list">
                    <?php foreach ($openVisits as $visit): ?>
                    <div class="traffic-open-row" style="display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap;padding:8px 0;border-bottom:1px solid #e5e7eb">
                        <span><strong><?= e(((int) ($visit['is_notice'] ?? 0) === 1 ? 'تردد خاص: ' : '') . $visit['full_name']) ?></strong> <?php if (empty($visit['is_notice'])): ?><small>(<?= e($visit['national_code']) ?>)</small><?php endif; ?><br><small>تاریخ <?= e(jalali_date($visit['visit_date'], false)) ?> · شماره برگ <?= e($visit['serial_no']) ?> · ورود <?= e(substr((string) $visit['entry_time'], 0, 5)) ?> · ملاقات با <?= e($visit['meeting_with'] ?? '-') ?></small></span>
                        <?php if (user_can($user, 'traffic.manage')): ?>
                        <form method="post" class="traffic-open-exit-form"><?= csrf_field() ?><input type="hidden" name="action" value="traffic_set_exit"><input type="hidden" name="record_id" value="<?= (int) $visit['id'] ?>"><input type="hidden" name="return_tab" value="dashboard"><?php if ((string) $visit['visit_date'] !== (string) date('Y-m-d')): ?><label style="font-size:12px">ساعت خروج واقعی <input class="traffic-time-24h" type="text" name="exit_time" required inputmode="numeric" maxlength="5" placeholder="مثلاً 09:05" title="ساعت ۲۴ساعته، مانند 14:30" style="margin-inline-start:6px"></label><?php endif; ?><button class="mini-button" type="submit">ثبت خروج</button></form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'new'): ?>
        <section class="card form-card traffic-form-card">
            <div class="section-title"><div><span class="eyebrow">فرم ثبت</span><h2>ثبت تردد جدید</h2></div></div>
            <?php if ($lastCreated): ?><div class="alert success">ثبت با موفقیت انجام شد. شماره برگ: <b><?= e($lastCreated['serial_no']) ?></b> — برای چاپ از دکمهٔ فعال در پایین فرم استفاده کنید.</div><?php endif; ?>
            <div id="traffic-lookup-hint" class="alert info" hidden></div>
            <form method="post" id="traffic-quick-exit-form" hidden><?= csrf_field() ?><input type="hidden" name="action" value="traffic_set_exit"><input type="hidden" name="record_id" id="traffic-quick-exit-id"></form>
            <form method="post" class="traffic-form" id="traffic-new-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="traffic_create">
                <label class="check-label traffic-notice-toggle" style="margin-bottom:10px;justify-content:flex-start;width:max-content"><input type="checkbox" name="notice_only" value="1" id="traffic-notice-only" style="display:inline-block;width:auto;margin:0"> <span>تردد خاص</span></label>

                <div class="form-grid">
                    <div class="traffic-full-only" style="display:contents"><label>کد ملی<input name="national_code" id="traffic-national-code" required maxlength="10" inputmode="numeric" pattern="[0-9۰-۹]*" data-digits-only autocomplete="off" value="<?= e((string) ($formOld['national_code'] ?? '')) ?>"></label></div>
                    <label>نام و نام خانوادگی<input name="full_name" id="traffic-full-name" required value="<?= e((string) ($formOld['full_name'] ?? '')) ?>"></label>
                    <div class="traffic-full-only" style="display:contents">
                    <label>تلفن همراه<input name="phone" id="traffic-phone" inputmode="numeric" pattern="[0-9۰-۹]*" data-digits-only autocomplete="off" value="<?= e((string) ($formOld['phone'] ?? '')) ?>"></label>
                    <label>موسسه / شرکت<input name="company" id="traffic-company" value="<?= e((string) ($formOld['company'] ?? '')) ?>"></label>
                    <label>ملاقات با<select name="meeting_with" id="traffic-meeting-with"><option value="">— انتخاب کنید —</option><?php foreach ($destinations as $d): ?><option value="<?= e($d['title']) ?>" data-approver="<?= e((string) ($d['approver_name'] ?? '')) ?>"><?= e($d['title']) ?></option><?php endforeach; ?></select></label>
                    <label>تأییدکننده<input id="traffic-approved-by" readonly aria-readonly="true" placeholder="با انتخاب مقصد نمایش داده می‌شود"><small class="field-help">این مقدار از تنظیم همان مقصد خوانده می‌شود.</small></label>
                    <label class="full">توضیحات / هدف از تردد<textarea name="description" rows="2"><?= e((string) ($formOld['description'] ?? '')) ?></textarea></label>
                    <div class="traffic-checks">
                        <label><input type="checkbox" name="no_visit" value="1"> بدون بازدید</label>
                        <label><input type="checkbox" name="with_car" value="1"> با خودرو</label>
                        <label><input type="checkbox" name="with_mobile" value="1"> با تلفن همراه</label>
                    </div>
                    </div>
                </div>
                <div class="actions"><button class="button" type="submit">ثبت تردد</button><button class="button secondary" type="reset">پاک‌کردن فرم</button><?php if ($lastCreated): ?><a class="button secondary" data-traffic-print-saved href="index.php?page=traffic-control-print&amp;id=<?= (int) $lastCreated['id'] ?>" target="_blank" rel="noopener">چاپ برگه ملاقات</a><?php else: ?><button class="button secondary" type="button" disabled aria-disabled="true" title="پس از ثبت تردد فعال می‌شود">چاپ برگه ملاقات</button><?php endif; ?></div>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'history'): ?>
        <section class="card cd-dvd-history-panel">
            <div class="section-title"><div><span class="eyebrow">ردگیری</span><h2>سوابق تردد</h2></div><a class="button traffic-history-new-button" href="index.php?page=traffic-control&amp;tab=new">ثبت جدید</a></div>
            <section class="traffic-report-card" aria-labelledby="traffic-report-title">
                <div class="traffic-report-heading"><span class="traffic-report-mark" aria-hidden="true">▤</span><div><span class="traffic-panel-kicker">خروجی مدیریتی</span><h3 id="traffic-report-title">استخراج گزارش تردد</h3></div></div>
                <form method="get" action="index.php" class="traffic-report-form" id="traffic-report-form">
                    <input type="hidden" name="action" value="export_traffic_report">
                    <div class="traffic-report-date-grid">
                        <label>از تاریخ<input type="date" name="from_date" required></label>
                        <label>تا تاریخ<input type="date" name="to_date" required></label>
                    </div>
                    <fieldset class="traffic-report-destination-fieldset">
                        <legend>مقصد ملاقات</legend>
                        <div class="traffic-report-destinations">
                            <label class="traffic-report-destination traffic-report-all"><input type="checkbox" id="traffic-report-all" name="all_destinations" value="1" checked><span>همه</span></label>
                            <?php foreach ($destinations as $destination): ?><label class="traffic-report-destination"><input type="checkbox" name="destinations[]" value="<?= e($destination['title']) ?>" checked><span><?= e($destination['title']) ?></span></label><?php endforeach; ?>
                        </div>
                        <?php if (!$destinations): ?><?php endif; ?>
                    </fieldset>
                    <div class="traffic-report-actions"><button class="button traffic-export-button" type="submit"><span aria-hidden="true">⇩</span>استخراج Excel</button></div>
                </form>
            </section>
            <form method="get" class="cd-dvd-history-filters"><input type="hidden" name="page" value="traffic-control"><input type="hidden" name="tab" value="history">
                <label>جست‌وجو<input name="search" id="traffic-history-search" autocomplete="off" value="<?= e($filters['search']) ?>" placeholder="نام، کد ملی، شماره برگ، شرکت"></label>
                <label>بازه<select name="range" onchange="this.form.submit()"><option value="today" <?= $filters['range'] === 'today' ? 'selected' : '' ?>>امروز</option><option value="week" <?= $filters['range'] === 'week' ? 'selected' : '' ?>>۷ روز اخیر</option><option value="all" <?= $filters['range'] === 'all' ? 'selected' : '' ?>>همه</option></select></label>
                <label><input type="checkbox" name="open_only" value="1" onchange="this.form.submit()" <?= $filters['open_only'] ? 'checked' : '' ?>> فقط ترددهای بدون خروج</label>
            </form>
            <div class="cd-dvd-record-table traffic-record-table" id="traffic-history-table">
                <?= log_sort_header_row([['serial', 'شماره برگ'], ['date', 'تاریخ'], ['name', 'نام'], ['national', 'کد ملی'], ['entry', 'ورود'], ['exit', 'خروج'], ['meeting', 'ملاقات با'], ['recorder', 'ثبت‌کننده'], ['', 'عملیات']], 'sort_t', 'dir_t', $_GET, (string) ($_GET['sort_t'] ?? ''), (string) ($_GET['dir_t'] ?? ''), 'cd-dvd-record-head') ?>
                <?php foreach (traffic_query_records($filters, 300, (string) ($_GET['sort_t'] ?? ''), (string) ($_GET['dir_t'] ?? '')) as $r): ?>
                <div class="cd-dvd-record-row" data-traffic-row data-search="<?= e(implode(' ', [(string) $r['full_name'], (string) $r['national_code'], (string) $r['serial_no'], (string) ($r['company'] ?? '')])) ?>">
                    <span><strong><?= e($r['serial_no']) ?></strong></span>
                    <span><?= e(jalali_date($r['visit_date'], false)) ?></span>
                    <span><?= e((!empty($r['is_notice']) ? 'تردد خاص: ' : '') . $r['full_name']) ?></span>
                    <span><?= empty($r['is_notice']) ? e($r['national_code']) : '—' ?></span>
                    <span><?= e(substr((string) $r['entry_time'], 0, 5)) ?></span>
                    <span><?= $r['exit_time'] ? e(substr((string) $r['exit_time'], 0, 5)) : '<em class="media-status inside">داخل</em>' ?></span>
                    <span><?= e($r['meeting_with'] ?? '-') ?></span>
                    <span><?= e($r['recorder_name'] ?? '-') ?></span>
                    <span class="actions">
                        <?php if (empty($r['is_notice'])): ?><a class="mini-button" href="index.php?page=traffic-control-print&amp;id=<?= (int) $r['id'] ?>" target="_blank">چاپ</a><?php endif; ?>
                        <?php if (!$r['exit_time'] && traffic_can_manage($user)): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="traffic_set_exit"><input type="hidden" name="record_id" value="<?= (int) $r['id'] ?>"><?php if ((string) $r['visit_date'] !== (string) date('Y-m-d')): ?><input class="traffic-time-24h" type="text" name="exit_time" required inputmode="numeric" maxlength="5" placeholder="09:05" aria-label="ساعت خروج واقعی، قالب ۲۴ ساعته" title="ساعت ۲۴ساعته، مانند 14:30"><?php endif; ?><button class="mini-button" type="submit">ثبت خروج</button></form><?php endif; ?>
                        <?php if (traffic_can_manage($user)): ?><a class="mini-button" href="index.php?page=traffic-control&amp;tab=edit&amp;id=<?= (int) $r['id'] ?>">ویرایش</a><?php endif; ?>
                        <?php if (traffic_can_manage($user)): ?><form method="post" data-confirm="این رکورد حذف شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="traffic_delete"><input type="hidden" name="record_id" value="<?= (int) $r['id'] ?>"><button class="mini-button danger" type="submit">حذف</button></form><?php endif; ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <p class="traffic-history-empty" id="traffic-history-empty" hidden>موردی با این جست‌وجو یافت نشد.</p>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'edit'): ?>
        <section class="card form-card traffic-form-card traffic-edit-card">
            <?php if (!$editRecord): ?>
                <div class="alert danger">رکورد موردنظر پیدا نشد یا حذف شده است. <a href="index.php?page=traffic-control&amp;tab=history">بازگشت به سوابق</a></div>
            <?php else: ?>
                <div class="section-title"><div><span class="eyebrow">اصلاح اطلاعات ثبت‌شده</span><h2>ویرایش تردد <?= e((string) $editRecord['serial_no']) ?></h2></div><a class="button secondary" href="index.php?page=traffic-control&amp;tab=history">بازگشت به سوابق</a></div>
                <p class="traffic-edit-note">تاریخ مراجعه و ساعت‌های ورود و خروج با قالب ۲۴ساعته (مثلاً 09:05) قابل اصلاح هستند؛ تغییرات ذخیره‌شده در «تاریخچهٔ ویرایش‌ها» با مقدار قبلی و جدید ثبت می‌شود.</p>
                <form method="post" class="traffic-form traffic-edit-form">
                    <?= csrf_field() ?><input type="hidden" name="action" value="traffic_update"><input type="hidden" name="record_id" value="<?= (int) $editRecord['id'] ?>">
                    <div class="form-grid">
                        <?php if (empty($editRecord['is_notice'])): ?><label>کد ملی<input name="national_code" required maxlength="10" inputmode="numeric" pattern="[0-9۰-۹]*" data-digits-only value="<?= e((string) ($formOld['national_code'] ?? $editRecord['national_code'] ?? '')) ?>"></label><?php else: ?><input type="hidden" name="national_code" value="<?= e((string) ($formOld['national_code'] ?? $editRecord['national_code'] ?? '')) ?>"><?php endif; ?>
                        <label>نام و نام خانوادگی<input name="full_name" required maxlength="190" value="<?= e((string) ($formOld['full_name'] ?? $editRecord['full_name'])) ?>"></label>
                        <label>تاریخ مراجعه<input type="date" name="visit_date" required value="<?= e((string) ($formOld['visit_date'] ?? $editRecord['visit_date'])) ?>"></label>
                        <label>ساعت ورود<input class="traffic-time-24h" type="text" name="entry_time" required inputmode="numeric" maxlength="5" placeholder="09:05" value="<?= e(substr((string) ($formOld['entry_time'] ?? $editRecord['entry_time']), 0, 5)) ?>" title="ساعت ۲۴ساعته، مانند 14:30"></label>
                        <label>ساعت خروج <small>(خالی یعنی هنوز خارج نشده)</small><input class="traffic-time-24h" type="text" name="exit_time" inputmode="numeric" maxlength="5" placeholder="09:05" value="<?= e(substr((string) ($formOld['exit_time'] ?? $editRecord['exit_time'] ?? ''), 0, 5)) ?>" title="ساعت ۲۴ساعته؛ خالی یعنی هنوز خارج نشده"></label>
                        <label>تلفن همراه<input name="phone" inputmode="numeric" pattern="[0-9۰-۹]*" data-digits-only value="<?= e((string) ($formOld['phone'] ?? $editRecord['phone'] ?? '')) ?>"></label>
                        <label>موسسه / شرکت<input name="company" maxlength="190" value="<?= e((string) ($formOld['company'] ?? $editRecord['company'] ?? '')) ?>"></label>
                        <label>ملاقات با<?php $currentMeeting = (string) ($formOld['meeting_with'] ?? $editRecord['meeting_with'] ?? ''); $activeMeetings = array_map(static fn (array $destination): string => (string) $destination['title'], $destinations); ?><select name="meeting_with" id="traffic-edit-meeting-with"><option value="">— انتخاب کنید —</option><?php if ($currentMeeting !== '' && !in_array($currentMeeting, $activeMeetings, true)): ?><option value="<?= e($currentMeeting) ?>" data-approver="<?= e((string) ($editRecord['approved_by'] ?? '')) ?>" selected><?= e($currentMeeting) ?> (غیرفعال)</option><?php endif; ?><?php foreach ($destinations as $destination): $selectedMeeting = $currentMeeting === (string) $destination['title']; ?><option value="<?= e($destination['title']) ?>" data-approver="<?= e((string) ($destination['approver_name'] ?? '')) ?>" <?= $selectedMeeting ? 'selected' : '' ?>><?= e($destination['title']) ?></option><?php endforeach; ?></select></label>
                        <label>تأییدکننده<input id="traffic-edit-approved-by" readonly aria-readonly="true" placeholder="از تنظیم مقصد پر می‌شود" value="<?= e((string) ($formOld['approved_by'] ?? $editRecord['approved_by'] ?? '')) ?>"><small class="field-help">با تغییر مقصد، تأییدکنندهٔ متناظر نمایش داده می‌شود.</small></label>
                        <label class="full">توضیحات / هدف از تردد<textarea name="description" rows="3"><?= e((string) ($formOld['description'] ?? $editRecord['description'] ?? '')) ?></textarea></label>
                        <div class="traffic-checks">
                            <label><input type="checkbox" name="no_visit" value="1" <?= !empty($formOld) ? (!empty($formOld['no_visit']) ? 'checked' : '') : (!empty($editRecord['no_visit']) ? 'checked' : '') ?>> بدون بازدید</label>
                            <label><input type="checkbox" name="with_car" value="1" <?= !empty($formOld) ? (!empty($formOld['with_car']) ? 'checked' : '') : (!empty($editRecord['with_car']) ? 'checked' : '') ?>> با خودرو</label>
                            <label><input type="checkbox" name="with_mobile" value="1" <?= !empty($formOld) ? (!empty($formOld['with_mobile']) ? 'checked' : '') : (!empty($editRecord['with_mobile']) ? 'checked' : '') ?>> با تلفن همراه</label>
                        </div>
                    </div>
                    <div class="actions"><button class="button" type="submit">ذخیرهٔ اصلاحات</button><a class="button secondary" href="index.php?page=traffic-control&amp;tab=history">انصراف</a></div>
                </form>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'changes'): ?>
        <section class="card traffic-change-history-card">
            <div class="section-title"><div><span class="eyebrow">ردگیری مدیریتی</span><h2>تاریخچهٔ ویرایش‌های کنترل تردد</h2></div><a class="button secondary" href="index.php?page=traffic-control&amp;tab=history">بازگشت به سوابق</a></div>
            <p class="traffic-edit-note">ویرایش مشخصات، اصلاح تاریخ و ساعت‌ها، ثبت ساعت خروج و حذف رکوردها در این فهرست ثبت می‌شوند. برای دیدن همهٔ رویدادها از صفحه‌بندی پایین استفاده کنید.</p>
            <?php if (!$changeRows): ?><div class="traffic-change-empty">هنوز تغییری در سوابق تردد ثبت نشده است.</div><?php else: ?>
            <div class="traffic-change-list">
                <?php foreach ($changeRows as $change): $changeMap = json_decode((string) ($change['changes_json'] ?? '{}'), true); $changeMap = is_array($changeMap) ? $changeMap : []; $changeLabel = match ((string) $change['action']) { 'deleted' => 'حذف رکورد', 'exit_set' => 'ثبت ساعت خروج', default => 'ویرایش رکورد' }; ?>
                <article class="traffic-change-item">
                    <header><div><strong><?= e($changeLabel) ?> · <?= e((string) $change['visitor_name']) ?></strong><small>شماره برگ <?= e((string) $change['serial_no']) ?> · <?= e((string) ($change['editor_name'] ?? 'کاربر حذف‌شده')) ?></small></div><time><?= e(jalali_date((string) $change['changed_at'], true)) ?></time></header>
                    <details><summary>مشاهدهٔ <?= count($changeMap) ?> مورد تغییر</summary><dl><?php foreach ($changeMap as $field => $values): if (!is_array($values)) continue; ?><div><dt><?= e(traffic_history_field_label((string) $field)) ?></dt><dd><del><?= e(traffic_history_value((string) $field, $values['from'] ?? null)) ?></del><span aria-hidden="true"> ← </span><ins><?= e(traffic_history_value((string) $field, $values['to'] ?? null)) ?></ins></dd></div><?php endforeach; ?></dl></details>
                </article>
                <?php endforeach; ?>
            </div>
            <?php $changePages = (int) ceil($changeCount / $changePerPage); if ($changePages > 1): ?><nav class="traffic-change-pagination" aria-label="صفحه‌بندی تاریخچه"><?php if ($changePage > 1): ?><a class="button secondary" href="index.php?page=traffic-control&amp;tab=changes&amp;p=<?= $changePage - 1 ?>">صفحهٔ قبلی</a><?php endif; ?><span>صفحهٔ <?= $changePage ?> از <?= $changePages ?> · <?= $changeCount ?> تغییر</span><?php if ($changePage < $changePages): ?><a class="button secondary" href="index.php?page=traffic-control&amp;tab=changes&amp;p=<?= $changePage + 1 ?>">صفحهٔ بعدی</a><?php endif; ?></nav><?php endif; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'import'): $importPreview = traffic_import_current_preview((int) ($user['id'] ?? 0)); $importCounts = is_array($importPreview['counts'] ?? null) ? $importPreview['counts'] : []; $importReady = (int) ($importCounts['insert'] ?? 0); ?>
        <section class="card traffic-import-card">
            <div class="section-title"><div><span class="eyebrow">ابزار محدود مدیران تردد</span><h2>ورود سوابق قدیمی از Excel</h2></div><a class="button secondary" href="index.php?page=traffic-control&amp;tab=history">بازگشت به سوابق</a></div>
            <p class="traffic-import-intro">این فرم در منو نمایش داده نمی‌شود و فقط با دسترسی‌های «مشاهده» و «مدیریت کنترل تردد» باز می‌شود. فایل‌های <b>XLSX / XLSM / CSV / TXT</b> تا ۱۰ مگابایت، ۱۰٬۰۰۰ ردیف و ۶۴ ستون پذیرفته می‌شود؛ از Excel فقط اولین کاربرگ خوانده می‌شود. فایل قدیمی <b>XLS</b> را ابتدا با Excel به XLSX یا CSV ذخیره کنید.</p>
            <div class="traffic-import-template-actions">
                <a class="button secondary" href="index.php?page=traffic-control&amp;tab=import&amp;template=xlsx">دانلود قالب Excel</a>
                <a class="button secondary" href="index.php?page=traffic-control&amp;tab=import&amp;template=csv">دانلود قالب CSV</a>
            </div>
            <div class="traffic-import-columns"><strong>ستون‌های لازم:</strong> نام و نام خانوادگی، تاریخ تردد (شمسی، میلادی یا تاریخ Excel). ساعت ورود/خروج، کد ملی، تلفن، شرکت، شماره برگ، میزبان، تأییدکننده، توضیحات و پرچم‌های «بدون بازدید/خودرو/تلفن همراه/تردد خاص» اختیاری‌اند. اگر شماره برگ خالی باشد، شمارهٔ یکتا بر اساس تاریخ ساخته می‌شود.</div>
            <form method="post" action="index.php?page=traffic-control&amp;tab=import" enctype="multipart/form-data" class="traffic-import-upload-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="traffic_import_preview">
                <label>فایل سوابق تردد<input type="file" name="file" accept=".xlsx,.xlsm,.csv,.txt" required></label>
                <button class="button" type="submit">بررسی فایل (بدون ذخیره)</button>
            </form>
            <p class="traffic-import-safety">ابتدا فقط پیش‌نمایش انجام می‌شود؛ ردیف‌های تکراری دوباره ثبت نمی‌شوند. پیش‌نمایش تا ۳۰ دقیقه معتبر است و برای ثبت نهایی باید همان فایل را دوباره انتخاب و تأیید کنید. آپلود موقت است و فایل در پوشهٔ سامانه ذخیره نمی‌شود.</p>

            <?php if ($importPreview): ?>
            <section class="traffic-import-preview" aria-labelledby="traffic-import-preview-title">
                <h3 id="traffic-import-preview-title">پیش‌نمایش: <?= e((string) $importPreview['file_name']) ?></h3>
                <div class="traffic-import-counts">
                    <span><b><?= (int) ($importCounts['insert'] ?? 0) ?></b> قابل ثبت</span>
                    <span><b><?= (int) ($importCounts['exists'] ?? 0) ?></b> از قبل موجود</span>
                    <span><b><?= (int) ($importCounts['duplicate'] ?? 0) ?></b> تکراری در فایل</span>
                    <span><b><?= (int) ($importCounts['invalid'] ?? 0) ?></b> نامعتبر</span>
                    <span><b><?= (int) ($importCounts['warnings'] ?? 0) ?></b> دارای هشدار</span>
                </div>
                <?php if ($importReady > 0): ?>
                <form method="post" action="index.php?page=traffic-control&amp;tab=import" enctype="multipart/form-data" class="traffic-import-confirm-form" data-confirm="<?= e('فقط ردیف‌های قابل ثبت به سوابق تردد اضافه می‌شوند. ادامه می‌دهید؟') ?>">
                    <?= csrf_field() ?><input type="hidden" name="action" value="traffic_import_apply"><input type="hidden" name="preview_token" value="<?= e((string) $importPreview['token']) ?>">
                    <label>برای ثبت، همان فایل پیش‌نمایش‌شده را دوباره انتخاب کنید<input type="file" name="file" accept=".xlsx,.xlsm,.csv,.txt" required></label>
                    <button class="button" type="submit">ثبت <?= number_format($importReady) ?> تردد</button>
                </form>
                <?php else: ?><p class="traffic-import-no-ready">ردیف قابل ثبت پیدا نشد؛ فایل را اصلاح و دوباره بررسی کنید.</p><?php endif; ?>

                <div class="traffic-import-table-wrap"><table class="traffic-import-table"><thead><tr><th>ردیف</th><th>شماره برگ</th><th>تاریخ</th><th>نام</th><th>کد ملی</th><th>ورود</th><th>خروج</th><th>وضعیت / توضیح</th></tr></thead><tbody>
                <?php foreach ((array) ($importPreview['rows'] ?? []) as $previewRow): ?>
                    <?php $status = (string) ($previewRow['action'] ?? 'invalid'); $statusLabels = ['insert' => 'قابل ثبت', 'exists' => 'از قبل موجود', 'duplicate' => 'تکراری در فایل', 'invalid' => 'نیاز به اصلاح']; ?>
                    <tr class="traffic-import-<?= e($status) ?>"><td><?= (int) ($previewRow['line'] ?? 0) ?></td><td><?= e((string) ($previewRow['serial_no'] ?? '')) ?></td><td><?= !empty($previewRow['visit_date']) ? e(jalali_date((string) $previewRow['visit_date'], false)) : '—' ?></td><td><?= e((string) ($previewRow['full_name'] ?? '')) ?></td><td><?= e((string) ($previewRow['national_code'] ?? '')) ?></td><td><?= e((string) ($previewRow['entry_time'] ?? '')) ?></td><td><?= e((string) ($previewRow['exit_time'] ?? '')) ?></td><td><strong><?= e($statusLabels[$status] ?? $status) ?></strong><br><small><?= e((string) ($previewRow['reason'] ?? '')) ?></small></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
                <?php if (!empty($importPreview['truncated'])): ?><p class="muted">برای خوانایی، فقط ۳۰۰ ردیف نخست نشان داده شده است؛ شمارش بالا شامل همهٔ <?= number_format((int) ($importPreview['total'] ?? 0)) ?> ردیف است.</p><?php endif; ?>
                <p class="traffic-import-privacy">کد ملی در جدول پیش‌نمایش پوشانده شده و فقط چهار رقم پایانی دیده می‌شود.</p>
            </section>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($tab === 'destinations' && traffic_can_manage_destinations($user)): ?>
        <section class="card traffic-destinations-card">
            <div class="section-title"><div><span class="eyebrow">تنظیمات</span><h2>مقاصد ملاقات و تأییدکنندگان</h2><p class="muted">برای هر مقصد، نام تأییدکننده را ثبت کنید تا هنگام ثبت تردد خودکار نمایش داده شود.</p></div></div>
            <form method="post" class="traffic-destination-add-form"><?= csrf_field() ?><input type="hidden" name="action" value="traffic_add_destination"><label>عنوان مقصد<input name="title" placeholder="مثلاً ملاقات با مدیرعامل" maxlength="190" required></label><label>تأییدکننده<input name="approver_name" placeholder="نام تأییدکننده" maxlength="190" required></label><button class="button" type="submit">افزودن مقصد</button></form>
            <div class="traffic-destinations-list">
                <?php foreach (traffic_destinations(false) as $d): ?>
                <div class="traffic-destination-row <?= (int) ($d['is_active'] ?? 1) === 1 ? '' : 'is-inactive' ?>">
                    <form method="post" class="traffic-destination-edit-form"><?= csrf_field() ?><input type="hidden" name="action" value="traffic_update_destination"><input type="hidden" name="destination_id" value="<?= (int) $d['id'] ?>"><label>عنوان مقصد<input name="title" value="<?= e($d['title']) ?>" maxlength="190" required></label><label>تأییدکننده<input name="approver_name" value="<?= e((string) ($d['approver_name'] ?? '')) ?>" maxlength="190" required></label><button class="mini-button" type="submit">ذخیره</button></form>
                    <?php if ((int) ($d['is_active'] ?? 1) === 1): ?><form method="post" data-confirm="این مقصد از فهرست فعال غیرفعال شود؟ سوابق قبلی حفظ می‌شوند."><?= csrf_field() ?><input type="hidden" name="action" value="traffic_delete_destination"><input type="hidden" name="destination_id" value="<?= (int) $d['id'] ?>"><button class="mini-button danger" type="submit">غیرفعال‌کردن</button></form><?php else: ?><span class="traffic-destination-inactive-label">غیرفعال</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </section>
    <script nonce="<?= e(csp_nonce()) ?>" src="assets/traffic-control.js?v=9"></script>
    <?php
    render_footer();
    exit;
}

// ------------------------------------------------------------
// چاپ برگ ملاقات — معادل PrintTemplate + cmdPrintPass_Click
// ------------------------------------------------------------
function traffic_render_print(array $user, int $id): never
{
    if (function_exists('require_permission')) {
        $user = require_permission('traffic.view');
    }
    $record = traffic_fetch_record($id);
    if (!$record) {
        http_response_code(404);
        exit('برگ ملاقات پیدا نشد.');
    }
    ?><!doctype html>
    <html lang="fa" dir="rtl">
    <head>
    <meta charset="utf-8">
    <title>برگ ملاقات — <?= e($record['serial_no']) ?></title>
    <style>
        @font-face { font-family: 'ShabnamPrint'; src: url('assets/fonts/Shabnam-Regular.woff2') format('woff2'); font-weight: 400; font-style: normal; }
        @font-face { font-family: 'ShabnamPrint'; src: url('assets/fonts/Shabnam-Bold.woff2') format('woff2'); font-weight: 700; font-style: normal; }
        body { font-family: 'ShabnamPrint', 'VazirmatnLocal', Tahoma, sans-serif; padding: 30px; color: #263d38; background: #eef3ef; }
        .pass { max-width: 680px; margin: 0 auto; border: 1px solid #a7c7b4; border-top: 8px solid #19745c; border-radius: 14px; background: #fff; padding: 26px 30px; box-shadow: 0 12px 32px rgba(29, 74, 54, .12); }
        .pass h1 { text-align: center; color: #174e42; font-size: 23px; margin: 4px 0 12px; }
        .pass .bismillah { text-align: center; color: #258065; font-size: 13px; margin-bottom: 12px; }
        .pass table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .pass td { padding: 9px 11px; border: 1px solid #d3e0d8; font-size: 14px; line-height: 1.8; }
        .pass td.label { background: #f0f6f2; width: 35%; color: #35594b; font-weight: bold; }
        .pass .meta { display: flex; justify-content: space-between; margin-top: 8px; font-size: 13px; }
        .pass .meta span { padding: 5px 10px; border-radius: 7px; background: #f2f7f3; }
        .pass-signature { display: flex; align-items: flex-end; gap: 10px; width: min(310px, 72%); margin: 38px 0 0 auto; }
        .pass-signature span { white-space: nowrap; font-weight: bold; }
        .pass-signature i { display: block; flex: 1; min-width: 90px; height: 34px; border-bottom: 1px dotted #444; }
        @media print { .no-print { display: none; } body { padding: 0; background: #fff; } .pass { box-shadow: none; break-inside: avoid; } }
    </style>
    </head>
    <body>
    <div class="pass">
        <div class="bismillah">بسمه‌تعالی</div>
        <h1>برگ ملاقات مراجعین</h1>
        <div class="meta"><span>شماره: <?= e($record['serial_no']) ?></span><span>تاریخ: <?= e(jalali_date($record['visit_date'], false)) ?></span></div>
        <table>
            <tr><td class="label">نام و نام خانوادگی</td><td><?= e($record['full_name']) ?></td></tr>
            <tr><td class="label">کد ملی</td><td><?= e($record['national_code']) ?></td></tr>
            <tr><td class="label">موسسه / شرکت</td><td><?= e($record['company'] ?? '') ?></td></tr>
            <tr><td class="label">ملاقات با</td><td><?= e($record['meeting_with'] ?? '') ?></td></tr>
            <tr><td class="label">تایید کننده</td><td><?= e($record['approved_by'] ?? '') ?></td></tr>
            <tr><td class="label">ساعت ورود</td><td><?= e(substr((string) $record['entry_time'], 0, 5)) ?></td></tr>
            <tr><td class="label">هدف از تردد</td><td><?= e($record['description'] ?? '') ?></td></tr>
        </table>
        <div class="pass-signature"><span>امضای ملاقات‌شونده</span><i></i></div>
    </div>
    <p class="no-print" style="text-align:center"><button id="traffic-print-again">چاپ مجدد</button></p>
    <script nonce="<?= e(csp_nonce()) ?>">
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () { window.print(); });
        } else {
            window.print();
        }
        document.getElementById('traffic-print-again').addEventListener('click', function () { window.print(); });
    </script>
    </body>
    </html>
    <?php
    exit;
}
