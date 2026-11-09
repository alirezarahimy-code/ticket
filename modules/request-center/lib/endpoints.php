<?php
declare(strict_types=1);

/**
 * برش فاز استقلال فاز ۲ — اندپوینت‌های GET متعلق به Request Center.
 * بدنه‌ها بایت‌به‌بایت از index.php برداشته و فقط در پوستهٔ تابع پیچده شده‌اند تا
 * کنترل مجوز (require_permission / require_login) همچنان توسط front controller
 * قبل از فراخوانی اعمال شود. هیچ منطق ردیف‌ها، گزارش، مجوز پیوست یا هدر HTTP تغییر نکرده است.
 */

function request_center_excel_download(string $filename, string $title, array $headers, array $rows, array $dateRange = []): never
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

function request_center_export_report(array $user): never
{
    $reportUser = $user;
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
    request_center_excel_download('ticket-report-' . date('Y-m-d') . '.xlsx', $exportTruncated ? 'گزارش درخواست‌ها (۵۰۰۰ ردیف اول؛ فیلتر را محدودتر کنید)' : 'گزارش کامل درخواست‌ها', ['شماره', 'عنوان', 'نوع', 'واحد', 'ثبت‌کننده', 'کارشناس', 'وضعیت', 'اولویت', 'ثبت', 'آخرین تغییر', 'ارجاع', 'اولین پاسخ', 'حل', 'بسته‌شدن', 'مهلت SLA', 'دقیقه SLA'], $rows, ['from' => $excelFrom !== null ? substr($excelFrom, 0, 10) : '', 'to' => $excelTo !== null ? substr($excelTo, 0, 10) : '']);
}


/**
 * خروجی Excel تصمیمات سوپروایزر (تأیید نهایی / برگشت برای اصلاح / بازگشایی رسمی / رد)
 * در محدودهٔ دسترسی همان کاربر. سقف ۵۰۰۰ ردیف؛ از/تا اختیاری (جلالی).
 */
function request_center_export_decisions(array $user): never
{
    [$where, $params] = ticket_scope($user, 't');
    $wherePart = $where !== '' ? $where . ' AND ' : 'WHERE ';
    $wherePart .= "e.event_type IN ('supervisor_approved', 'supervisor_rework', 'supervisor_reopened', 'ticket_rejected')";

    $from = jalali_input_to_gregorian((string) ($_GET['from'] ?? ''));
    if ($from !== null && $from !== '') {
        $wherePart .= ' AND e.created_at >= ?';
        $params[] = $from;
    }
    $to = jalali_input_to_gregorian((string) ($_GET['to'] ?? ''), true);
    if ($to !== null && $to !== '') {
        $wherePart .= ' AND e.created_at <= ?';
        $params[] = $to;
    }

    $query = db()->prepare(
        'SELECT e.event_type, e.from_status, e.to_status, e.created_at, e.details, '
        . 't.id AS ticket_id, t.ticket_no, t.subject, u.full_name AS actor_name '
        . 'FROM ticket_events e JOIN tickets t ON t.id = e.ticket_id '
        . 'LEFT JOIN users u ON u.id = e.user_id ' . $wherePart
        . ' ORDER BY e.created_at DESC, e.id DESC LIMIT 5001'
    );
    $query->execute($params);
    $decisionLabels = [
        'supervisor_approved' => 'تأیید نهایی (بسته شد)',
        'supervisor_rework' => 'برگشت برای اصلاح',
        'supervisor_reopened' => 'بازگشایی رسمی',
        'ticket_rejected' => 'رد درخواست',
    ];
    $rows = [];
    while ($row = $query->fetch()) {
        $details = json_decode((string) ($row['details'] ?? ''), true);
        $note = '';
        if (is_array($details)) {
            $note = (string) ($details['reason'] ?? $details['note'] ?? '');
        }
        $rows[] = [
            $row['ticket_no'] ?: ('#' . (int) $row['ticket_id']),
            $row['subject'],
            $decisionLabels[(string) $row['event_type']] ?? (string) $row['event_type'],
            $row['actor_name'] ?: 'سامانه',
            request_center_status_label((string) ($row['from_status'] ?? '')) ?: '—',
            request_center_status_label((string) ($row['to_status'] ?? '')) ?: '—',
            persian_date((string) $row['created_at']),
            $note,
        ];
    }
    $exportTruncated = count($rows) > 5000;
    if ($exportTruncated) {
        $rows = array_slice($rows, 0, 5000);
    }
    $excelFrom = jalali_input_to_gregorian((string) ($_GET['from'] ?? ''));
    $excelTo = jalali_input_to_gregorian((string) ($_GET['to'] ?? ''), true);
    request_center_excel_download(
        'supervisor-decisions-' . date('Y-m-d') . '.xlsx',
        $exportTruncated ? 'خروجی تصمیمات سوپروایزر (۵۰۰۰ ردیف اول؛ فیلتر را محدودتر کنید)' : 'خروجی تصمیمات سوپروایزر',
        ['شماره', 'عنوان درخواست', 'تصمیم', 'تصمیم‌گیرنده', 'وضعیت قبلی', 'وضعیت بعدی', 'زمان', 'دلیل/یادداشت'],
        $rows,
        ['from' => $excelFrom !== null ? substr($excelFrom, 0, 10) : '', 'to' => $excelTo !== null ? substr($excelTo, 0, 10) : '']
    );
}


function request_center_download_attachment(array $user): never
{
    $downloadUser = $user;
    $attachmentId = (int) ($_GET['id'] ?? 0);
    $attachmentQuery = db()->prepare('SELECT a.*, m.ticket_id, m.is_internal FROM ticket_attachments a JOIN ticket_messages m ON m.id = a.message_id WHERE a.id = ? LIMIT 1');
    $attachmentQuery->execute([$attachmentId]);
    $attachment = $attachmentQuery->fetch();
    $attachmentTicket = $attachment ? fetch_ticket((int) $attachment['ticket_id']) : null;
    // پیوست داخلی را بر پایهٔ نسبت کاربر با همین درخواست می‌سنجیم، نه نقش سراسری:
    // کارمندی که خودش ثبت‌کنندهٔ درخواست است نباید پیوست داخلی آن را بگیرد.
    $canDownloadInternal = $attachmentTicket ? ticket_can_see_internal($attachmentTicket, $downloadUser) : false;
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

/**
 * برش فاز استقلال فاز ۲ — دادهٔ داشبورد اصلی برای بخش Request Center.
 * بدنهٔ SQL بایت‌به‌بایت از index.php برداشته شده؛ هیچ‌جا رندر نمی‌شود.
 * خروجی: [counts, tickets]
 */
function request_center_dashboard_rows(array $user): array
{
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
    ticket_number_cache_prime($tickets); // ticket_no از همین SELECT آمده؛ جلوگیری از N+1
    return [$counts, $tickets];
}

/**
 * برش فاز استقلال فاز ۲ — slice جست‌وجوی Request Center برای صفحهٔ جست‌وجوی سراسری.
 * بدنهٔ SQL بایت‌به‌بایت از index.php برداشته شد.
 */
function request_center_global_search(array $user, array $needles): array
{
    [$searchScope, $searchParams] = ticket_scope($user);
    $ticketGroups=[]; $ticketTerms=[];
    foreach($needles as $needle){$like='%'.$needle.'%';$ticketGroups[]='(t.subject LIKE ? OR t.description LIKE ? OR r.full_name LIKE ?)';array_push($ticketTerms,$like,$like,$like);}
    $ticketSql = 'SELECT t.id, t.subject, t.status, t.ticket_type, d.name AS department_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id ' . ($searchScope ?: 'WHERE 1 = 1') . ' AND (' . implode(' OR ', $ticketGroups) . ') ORDER BY t.updated_at DESC LIMIT 30';
    $ticketQuery = db()->prepare($ticketSql); $ticketQuery->execute(array_merge($searchParams,$ticketTerms)); return $ticketQuery->fetchAll();
}
