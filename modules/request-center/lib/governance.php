<?php
declare(strict_types=1);

// محافظ: helperهای حاکمیت ITSM؛ فقط از ورودی‌های معتبر ماژول بارگذاری می‌شود.
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

/**
 * «تغییر و مشکل» (ITSM Governance) — توابع مشترک بین صفحهٔ ماژول
 * (modules/request-center/Pages/governance.php) و اکشن‌های POST
 * (modules/request-center/Actions/governance-actions.php).
 * این توابع پیش‌تر داخل governance.php بودند؛ با تصمیم مالک ۲۰۲۶-۱۱-۱۴ به ماژول منتقل شدند.
 */

function gov_post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function gov_choice(string $value, array $allowed, string $fallback): string
{
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function gov_change_number(int $id): string
{
    return 'CHG-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

function gov_problem_number(int $id): string
{
    return 'PRB-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

function gov_risk_label(string $risk): string
{
    return ['low' => 'کم', 'medium' => 'متوسط', 'high' => 'بالا', 'critical' => 'بحرانی'][$risk] ?? $risk;
}

function gov_change_status_label(string $status): string
{
    return ['draft' => 'پیش‌نویس', 'submitted' => 'ارسال‌شده', 'cab_review' => 'در انتظار CAB', 'approved' => 'تأییدشده', 'rejected' => 'ردشده', 'scheduled' => 'زمان‌بندی‌شده', 'implemented' => 'اجراشده', 'rolled_back' => 'بازگشت‌داده‌شده', 'closed' => 'بسته‌شده'][$status] ?? $status;
}

function gov_problem_status_label(string $status): string
{
    return ['open' => 'باز', 'investigation' => 'در حال بررسی', 'known_error' => 'خطای شناخته‌شده', 'resolved' => 'حل‌شده', 'closed' => 'بسته‌شده'][$status] ?? $status;
}

function gov_parse_datetime(string $value): ?string
{
    if ($value === '') {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $value, new DateTimeZone((string) cfg('app.timezone', 'Asia/Tehran')));
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d\\TH:i') !== $value) {
        return null;
    }
    return $date->format('Y-m-d H:i:s');
}

function gov_scope(array $user, string $alias = 'r'): array
{
    if (in_array($user['role'], ['primary_admin', 'supervisor', 'support_manager', 'inspector'], true)) {
        return ['', []];
    }
    $departmentId = (int) ($user['department_id'] ?? 0);
    if (in_array((string) $user['role'], ['manager', 'support_manager'], true)) {
        return $departmentId > 0 ? ["WHERE {$alias}.department_id = ?", [$departmentId]] : ['WHERE 1 = 0', []];
    }
    if ($user['role'] === 'agent') {
        return $departmentId > 0 ? ["WHERE ({$alias}.owner_id = ? OR {$alias}.department_id = ?)", [(int) $user['id'], $departmentId]] : ["WHERE {$alias}.owner_id = ?", [(int) $user['id']]];
    }
    return ['WHERE 1 = 0', []];
}

function gov_fetch_ticket(int $ticketId): ?array
{
    if ($ticketId <= 0) {
        return null;
    }
    $query = db()->prepare('SELECT t.*, d.name AS department_name, r.full_name AS requester_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id JOIN users r ON r.id = t.requester_id WHERE t.id = ? LIMIT 1');
    $query->execute([$ticketId]);
    return $query->fetch() ?: null;
}

function gov_can_view_ticket(array $ticket, array $user): bool
{
    if (in_array($user['role'], ['primary_admin', 'supervisor', 'support_manager', 'inspector'], true) || (int) $ticket['requester_id'] === (int) $user['id']) {
        return true;
    }
    if ($user['role'] === 'agent') {
        return (string) ($ticket['service_group'] ?? '') === user_service_group($user);
    }
    return in_array((string) $user['role'], ['manager', 'support_manager'], true) && (string) ($ticket['service_group'] ?? '') === user_service_group($user) && (int) ($user['department_id'] ?? 0) > 0 && (int) $ticket['department_id'] > 0 && (int) $ticket['department_id'] === (int) $user['department_id'];
}

function gov_fetch_change(int $changeId): ?array
{
    $query = db()->prepare('SELECT c.*, d.name AS department_name, r.full_name AS requester_name, o.full_name AS owner_name, cab.full_name AS cab_name FROM change_records c LEFT JOIN departments d ON d.id = c.department_id JOIN users r ON r.id = c.requester_id LEFT JOIN users o ON o.id = c.owner_id LEFT JOIN users cab ON cab.id = c.cab_decided_by WHERE c.id = ? LIMIT 1');
    $query->execute([$changeId]);
    return $query->fetch() ?: null;
}

function gov_fetch_problem(int $problemId): ?array
{
    $query = db()->prepare('SELECT p.*, d.name AS department_name, c.full_name AS creator_name, o.full_name AS owner_name FROM problem_records p LEFT JOIN departments d ON d.id = p.department_id JOIN users c ON c.id = p.created_by LEFT JOIN users o ON o.id = p.owner_id WHERE p.id = ? LIMIT 1');
    $query->execute([$problemId]);
    return $query->fetch() ?: null;
}

function gov_can_view_record(array $record, array $user): bool
{
    if (in_array($user['role'], ['primary_admin', 'supervisor', 'support_manager', 'inspector'], true)) {
        return true;
    }
    if (in_array((string) $user['role'], ['manager', 'support_manager'], true)) {
        return (int) ($user['department_id'] ?? 0) > 0 && (int) $record['department_id'] > 0 && (int) $record['department_id'] === (int) $user['department_id'];
    }
    return $user['role'] === 'agent' && ((int) $record['owner_id'] === (int) $user['id'] || ((int) ($user['department_id'] ?? 0) > 0 && (int) $record['department_id'] > 0 && (int) $record['department_id'] === (int) $user['department_id']));
}

function gov_datetime_input(?string $value): string
{
    return $value ? str_replace(' ', 'T', substr($value, 0, 16)) : '';
}

function gov_ticket_options(array $user, int $selected = 0): string
{
    [$where, $params] = ticket_scope($user);
    $query = db()->prepare('SELECT t.id, t.subject, t.ticket_type FROM tickets t ' . ($where ?: 'WHERE 1 = 1') . ' ORDER BY t.created_at DESC LIMIT 300');
    $query->execute($params);
    $html = '<option value="">بدون درخواست مرجع</option>';
    foreach ($query->fetchAll() as $ticket) {
        $label = ticket_number((int) $ticket['id']) . ' • ' . $ticket['subject'] . ' • ' . ticket_type_label((string) $ticket['ticket_type']);
        $html .= '<option value="' . (int) $ticket['id'] . '" ' . ((int) $ticket['id'] === $selected ? 'selected' : '') . '>' . e($label) . '</option>';
    }
    return $html;
}

function gov_notify_linked_tickets(string $table, string $column, int $recordId, string $type, string $title, string $body, int $excludeUserId): void
{
    if (!in_array($table, ['change_ticket_links', 'problem_ticket_links'], true) || !in_array($column, ['change_id', 'problem_id'], true)) {
        return;
    }
    $query = db()->prepare('SELECT ticket_id FROM ' . $table . ' WHERE ' . $column . ' = ?');
    $query->execute([$recordId]);
    foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $ticketId) {
        notify_ticket_parties((int) $ticketId, $type, $title, $body, $excludeUserId);
    }
}
