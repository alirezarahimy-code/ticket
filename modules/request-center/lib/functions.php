<?php
declare(strict_types=1);

/**
 * قالب‌بندی نشده — توابع مالکیت مالک ماژول Request Center است.
 * انتقال بایت‌به‌بایت از index.php (برش فاز استقلال)؛ هیچ تغییری در بدنه مجاز نیست.
 * تعیین مالکیت: این توابع صرفاً دامنهٔ درخواست را پیاده می‌کنند؛ توابع مشترک
 * (layout/HTTP نرمال‌ساز) جای جدا دارند.
 */

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

    // Remove the physical upload if the caller later rolls back its lifecycle transaction.
    $GLOBALS['request_center_pending_attachment_cleanup'][] = [
        'path' => $directory . '/' . $stored,
        'stored_name' => $stored,
    ];
    if (empty($GLOBALS['request_center_attachment_cleanup_registered'])) {
        $GLOBALS['request_center_attachment_cleanup_registered'] = true;
        register_shutdown_function(static function (): void {
            $pending = $GLOBALS['request_center_pending_attachment_cleanup'] ?? [];
            if ($pending === []) {
                return;
            }
            try {
                $connection = db();
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                $exists = $connection->prepare(
                    'SELECT 1 FROM ticket_attachments WHERE stored_name = ? LIMIT 1'
                );
                foreach ($pending as $attachment) {
                    $exists->execute([(string) $attachment['stored_name']]);
                    if (!$exists->fetchColumn()) {
                        @unlink((string) $attachment['path']);
                    }
                }
            } catch (Throwable $exception) {
                // Preserve the file if the database cannot be checked safely.
                if (function_exists('app_error_log')) {
                    try {
                        app_error_log('Request Center attachment cleanup deferred: ' . $exception->getMessage());
                    } catch (Throwable) {
                    }
                }
            }
        });
    }
}

function fetch_ticket(int $id): ?array
{
    $query = db()->prepare('SELECT t.*, c.name AS category_name, sc.name AS service_name, sc.code AS service_code, d.name AS department_name, ou.name AS requesting_unit_name, r.full_name AS requester_name, r.username AS requester_username, a.full_name AS assignee_name, ass.asset_tag, ass.hostname, tr.score AS rating_score, tr.comment AS rating_comment, tr.created_at AS rating_at FROM tickets t LEFT JOIN categories c ON c.id = t.category_id LEFT JOIN service_catalog sc ON sc.id = t.service_id LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN org_units ou ON ou.id = t.requesting_unit_id JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assigned_to LEFT JOIN assets ass ON ass.id = t.asset_id LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id WHERE t.id = ? LIMIT 1');
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

function can_view_ticket(array $ticket, array $user): bool
{
    return ticket_user_can_view($ticket, $user);
}

/**
 * ۱.۶۲.۶ — آیا زیرساخت چارت سازمانی برای تشخیص «مدیر سازمانی» در دسترس است؟
 * نتیجه کش می‌شود تا ثبت درخواست روی نصب‌های قدیمیِ بدون این جدول‌ها خطا نگیرد.
 */
/**
 * آیا ستون‌های نقش خودکار چارت (users.org_auto_role / users.org_prev_role) موجودند؟
 * در شِمای تازهٔ ۱٫۶۲٫۹۵ این ستون‌ها را صفحهٔ سازمان به‌صورت تنبل می‌سازد؛ مسیر ثبت درخواست
 * هرگز نباید برای ترمیم شِما DDL اجرا کند. اگر ستون نباشد، «مدیر خودکار-چارتی» هم وجود ندارد
 * و شرط حذف او معنا ندارد. نتیجه کش می‌شود.
 */
function ticket_org_auto_role_sql(): string
{
    static $ready = null;
    if ($ready === null) {
        try {
            db()->query('SELECT org_auto_role, org_prev_role FROM users LIMIT 1');
            $ready = true;
        } catch (Throwable) {
            $ready = false;
        }
    }
    return $ready ? ' AND COALESCE(u.org_auto_role, 0) = 0' : '';
}

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

function choose_handling_agent(string $serviceGroup, ?int $departmentId = null, int $requesterId = 0): ?int
{
    $serviceGroup = in_array($serviceGroup, ['it', 'support'], true) ? $serviceGroup : 'it';
    $departmentId = (int) ($departmentId ?? 0);
    $loadJoin = ' LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status NOT IN ("resolved", "closed", "cancelled", "rejected")';
    // ۱.۶۲.۶ — مدیران چارت سازمانی (معاون/مدیر اداره) آخرِ صف ارجاع خودکار می‌رفتند
    // تا اگر نقش/پرچم اشتباهی (مثل «ادمین اصلی» شدن ناخواسته) داشته باشند، تا وقتی کاندیدای
    // واقعی فعال هست انتخاب نشوند.
    // ۲۰۲۶-۱۱-۱۱ — تصمیم مالک: مدیران چارت (org_auto_role=1) از کاندیدای ارجاع خودکار
    // کاملاً حذف می‌شوند (نه فقط آخر صف). مدیرِ منصوبِ دستی (org_auto_role=0) همچنان ممکن است
    // با همان شرطِ department_id انتخاب شود. برای اصلاح ریشه‌ای، نقش‌ها از صفحهٔ سازمان ترمیم شوند.
    $orgManagerRank = ticket_org_manager_rank_sql();

    // مالک اصلی هر حوزه اولویت دارد: IT ← ادمین اصلی؛ پشتیبانی ← مدیر پشتیبانی.
    // اگر برای یک حوزه چند مالک فعال تعریف شده باشد، کمترین بارِ باز ملاک می‌شود.
    $ownerCondition = $serviceGroup === 'it'
        ? '(u.role = "primary_admin" OR (u.role = "admin" AND u.is_primary_admin = 1))'
        : 'u.role = "support_manager"';
    $candidateFields = 'u.id, u.role, u.is_active, u.is_primary_admin, u.department_id, u.is_it_agent, hu.code AS handling_unit_code';
    $routingTicket = ['requester_id' => $requesterId, 'service_group' => $serviceGroup, 'department_id' => $departmentId];
    $ownerQuery = db()->query('SELECT ' . $candidateFields . ' FROM users u LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id' . $loadJoin . ' WHERE u.is_active = 1 AND ' . $ownerCondition . ' GROUP BY u.id, u.role, u.is_active, u.is_primary_admin, u.department_id, u.is_it_agent, hu.code ORDER BY ' . $orgManagerRank . 'COUNT(t.id), u.id');
    foreach ($ownerQuery->fetchAll() as $candidate) {
        if (ticket_assignee_is_eligible($routingTicket, $candidate, 'ticket_notification_user_can')) {
            return (int) $candidate['id'];
        }
    }

    // اگر مسئول اصلی غیرفعال/تعریف‌نشده باشد، فقط کارشناس همان حوزه جایگزین می‌شود.
    // عمداً دیگر به کارشناس حوزهٔ دیگر ارجاع نمی‌دهیم؛ در نبود جایگزین، درخواست در manager_review می‌ماند.
    $fallback = db()->prepare(
        'SELECT ' . $candidateFields . ' FROM users u '
        . 'LEFT JOIN handling_units hu ON hu.id = u.handling_unit_id'
        . $loadJoin
        . ' WHERE u.is_active = 1 AND u.role IN ("agent", "manager") AND hu.code = ?'
        // مدیر عادی فقط مسئول همان معاونت/واحدِ ثبت‌شده روی درخواست است؛ از معاونت دیگری انتخاب نشود.
        . ' AND (u.role <> "manager" OR (? > 0 AND u.department_id = ?))'
        // تصمیم مالک (۲۰۲۶-۱۱-۱۱): مدیران چارت سازمانی از ارجاع خودکار حذف‌اند؛ فقط انتصاب دستی روی یک درخواست ممکن است.
        // شرط فقط وقتی ستون‌های org_* موجود باشند اعمال می‌شود (نصب تازهٔ ۱٫۶۲٫۹۵ ستون‌ها را تنبل می‌سازد)؛ وگرنه کل کوئری با خطای ستون ناموجود می‌شکند.
        . ticket_org_auto_role_sql()
        . ' GROUP BY u.id, u.role, u.is_active, u.is_primary_admin, u.department_id, u.is_it_agent, hu.code ORDER BY ' . $orgManagerRank . 'COUNT(t.id), u.id'
    );
    $fallback->execute([$serviceGroup, $departmentId, $departmentId]);
    foreach ($fallback->fetchAll() as $candidate) {
        if (ticket_assignee_is_eligible($routingTicket, $candidate, 'ticket_notification_user_can')) {
            return (int) $candidate['id'];
        }
    }
    return null;
}

/**
 * مسیر مجاز تغییر وضعیت توسط کارکنان (مدل رسمی گردش درخواست).
 * new/manager_review -> assigned -> in_progress <-> waiting_user -> resolved -> (امتیاز کاربر) -> closed توسط سوپروایزر
 */
function ticket_allowed_transitions(string $from): array
{
    // pending یک وضعیت میراثی است؛ سیاست متمرکز در WorkflowService نگهداری می‌شود.
    return RequestCenterWorkflowService::allowedTransitions($from);
}
function ticket_requester_cancelable_statuses(): array
{
    // UI and WorkflowService share one canonical cancellation status list.
    return RequestCenterWorkflowService::REQUESTER_CANCELLABLE_STATUSES;
}

function ticket_cancellation_decision_statuses(): array
{
    return RequestCenterWorkflowService::CANCELLATION_DECISION_STATUSES;
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
    $status = valid_choice((string) ($_GET['status'] ?? ''), ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled', 'rejected', 'active', 'all'], '');
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

/**
 * Read-only preflight for the independent create-request path.
 *
 * Legacy index.php historically repaired these fields and seeded categories
 * during a web request. The independent Request Center must never run DDL or
 * data backfills at request time; migration 035 (merged lifecycle schema) owns that preparation.
 */
function request_center_assert_create_schema(): void
{
    $required = [
        'tickets' => ['requesting_unit_id', 'rejected_at', 'ticket_no'],
        'ticket_number_seq' => ['prefix', 'seq'],
        'notifications' => ['id', 'user_id', 'ticket_id', 'notification_type', 'title', 'body', 'idempotency_key'],
        'ticket_events' => ['id', 'event_id', 'ticket_id', 'user_id', 'event_type', 'from_status', 'to_status', 'details', 'created_at'],
        'activity_logs' => ['id', 'event_id', 'action_code', 'module', 'target_type', 'target_id', 'result', 'created_at'],
        'categories' => ['id', 'name', 'code', 'service_group', 'is_active'],
        'service_catalog' => ['id', 'name', 'code', 'service_group', 'default_priority', 'default_ticket_type', 'department_id', 'category_id', 'handling_unit_id', 'requires_asset', 'is_active'],
        'service_catalog_fields' => ['id', 'service_id', 'field_key', 'label', 'field_type', 'options_json', 'is_required', 'sort_order'],
        'org_units' => ['id', 'name', 'code', 'parent_id', 'is_active', 'unit_type', 'manager_user_id'],
        'users' => ['id', 'full_name', 'role', 'is_active', 'is_primary_admin', 'department_id', 'handling_unit_id', 'is_it_agent'],
        'assets' => ['id', 'asset_tag', 'hostname', 'owner_user_id', 'ip_address', 'department_id'],
        'departments' => ['id', 'name', 'manager_user_id'],
        'handling_units' => ['id', 'code', 'is_active', 'manager_user_id'],
    ];
    if (function_exists('org_has_company_schema') && org_has_company_schema()) {
        $required['org_companies'] = ['id', 'code', 'name', 'company_type', 'is_active'];
        $required['org_unit_managers'] = ['unit_id', 'user_id'];
        $required['org_units'][] = 'company_id';
        $required['users'] = ['id', 'full_name', 'company_id', 'org_unit_id'];
    }
    $statement = db()->query(
        'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()'
    );
    $available = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $table = strtolower((string) ($row['TABLE_NAME'] ?? ''));
        $column = strtolower((string) ($row['COLUMN_NAME'] ?? ''));
        if ($table !== '' && $column !== '') {
            $available[$table][$column] = true;
        }
    }
    $missing = [];
    foreach ($required as $table => $columns) {
        foreach ($columns as $column) {
            if (!isset($available[$table][$column])) {
                $missing[] = $table . '.' . $column;
            }
        }
    }
    if ($missing !== []) {
        throw new RuntimeException(
            'ساختار پایگاه‌دادهٔ مرکز درخواست‌ها آماده نیست. Migrationهای RC را اجرا کنید؛ ستون‌های ناقص: '
            . implode(', ', $missing)
        );
    }
}

/**
 * Request Center-only ticket number preview. This intentionally never invokes
 * ticket_number_ensure_schema(); migration 035 (merged lifecycle schema) owns the sequence table/column.
 */
function request_center_ticket_number_peek(string $prefix): string
{
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    $query = db()->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? LIMIT 1');
    $query->execute([$prefix]);
    $current = (int) ($query->fetchColumn() ?: 0);
    $pattern = '^' . preg_quote($prefix, '/') . '-[0-9]+$';
    $maxQuery = db()->prepare(
        'SELECT MAX(CAST(SUBSTRING_INDEX(ticket_no, "-", -1) AS UNSIGNED))
         FROM tickets WHERE ticket_no LIKE ? AND ticket_no REGEXP ?'
    );
    $maxQuery->execute([$prefix . '-%', $pattern]);
    $existing = (int) ($maxQuery->fetchColumn() ?: 0);
    return $prefix . '-' . str_pad((string) (max($current, $existing) + 1), 5, '0', STR_PAD_LEFT);
}

/**
 * Read-only equivalent of org_deputy_units() for the independent Request
 * Center entry. The provider helper calls org_ensure_schema() and therefore
 * is intentionally not used on this path.
 *
 * @return array<int,array<string,mixed>>
 */
function request_center_org_deputy_units(array $user): array
{
    $companyAware = function_exists('org_has_company_schema') && org_has_company_schema();
    $companyId = function_exists('org_current_company_id') ? org_current_company_id() : 0;
    if ($companyAware && $companyId <= 0) {
        return [];
    }
    $companyFilter = $companyAware ? ' AND ou.company_id = ?' : '';
    $query = db()->prepare(
        'SELECT ou.id, ou.name, ou.code, ou.parent_id, m.full_name AS manager_name
         FROM org_units ou
         LEFT JOIN users m ON m.id = ou.manager_user_id
         WHERE ou.is_active = 1 AND ou.unit_type = "deputy"' . $companyFilter . '
         ORDER BY ou.name'
    );
    $query->execute($companyAware ? [$companyId] : []);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/** Request Center status labels, including the module-owned rejected terminal state. */
function request_center_status_label(string $status): string
{
    $labels = [
        'new' => 'جدید',
        'manager_review' => 'در انتظار ارزیابی و ارجاع',
        'assigned' => 'ارجاع‌شده',
        'in_progress' => 'در حال رسیدگی',
        'waiting_user' => 'در انتظار پاسخ کاربر',
        'pending' => 'در انتظار پاسخ کاربر',
        'resolved' => 'حل‌شده',
        'closed' => 'بسته‌شده',
        'cancelled' => 'لغوشده',
        'rejected' => 'ردشده',
    ];
    return $labels[$status] ?? (function_exists('status_label') ? status_label($status) : $status);
}

/**
 * Transactional sequence allocation for the independent Request Center.
 * It mirrors the established global sequence contract but performs no schema
 * repair or DDL in the request path.
 */
function request_center_ticket_number_next(string $prefix): string
{
    $prefix = strtoupper(substr(trim($prefix) !== '' ? trim($prefix) : 'TK', 0, 12));
    $ownsTransaction = !db()->inTransaction();
    if ($ownsTransaction) {
        db()->beginTransaction();
    }
    try {
        db()->prepare(
            'INSERT INTO ticket_number_seq (prefix, seq) VALUES (?, 0)
             ON DUPLICATE KEY UPDATE prefix = VALUES(prefix)'
        )->execute([$prefix]);
        $read = db()->prepare('SELECT seq FROM ticket_number_seq WHERE prefix = ? FOR UPDATE');
        $read->execute([$prefix]);
        $current = (int) ($read->fetchColumn() ?: 0);
        $pattern = '^' . preg_quote($prefix, '/') . '-[0-9]+$';
        $maxQuery = db()->prepare(
            'SELECT MAX(CAST(SUBSTRING_INDEX(ticket_no, "-", -1) AS UNSIGNED))
             FROM tickets WHERE ticket_no LIKE ? AND ticket_no REGEXP ?'
        );
        $maxQuery->execute([$prefix . '-%', $pattern]);
        $existing = (int) ($maxQuery->fetchColumn() ?: 0);
        $sequence = max($current, $existing) + 1;
        db()->prepare('UPDATE ticket_number_seq SET seq = ? WHERE prefix = ?')
            ->execute([$sequence, $prefix]);
        if ($ownsTransaction) {
            db()->commit();
        }
        return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    } catch (Throwable $exception) {
        if ($ownsTransaction && db()->inTransaction()) {
            db()->rollBack();
        }
        throw $exception;
    }
}

/**
 * Idempotent, failure-isolated notification for Request Center lifecycle work.
 * A unique POST operation ID makes repeated delivery of the same event safe.
 */
function request_center_notify_user(int $userId, string $type, string $title, string $body, int $ticketId, bool $required = false): void
{
    if ($userId <= 0) {
        if ($required) {
            throw new RuntimeException('گیرندهٔ اعلان درخواست معتبر نیست.');
        }
        return;
    }
    try {
        $operationId = function_exists('logging_operation_id') ? (string) logging_operation_id() : '';
        if ($operationId === '') {
            $operationId = (string) ($GLOBALS['__logging_parent_event_id'] ?? '');
        }
        if ($operationId === '') {
            $query = db()->prepare('SELECT updated_at FROM tickets WHERE id = ? LIMIT 1');
            $query->execute([$ticketId]);
            $operationId = (string) ($query->fetchColumn() ?: 'ticket-' . $ticketId);
        }
        $key = hash('sha256', implode('|', ['request-center', $ticketId, $type, $operationId, $userId]));
        notify_user($userId, $type, $title, $body, $ticketId, $key);
    } catch (Throwable $exception) {
        try {
            app_error_log('Request Center notification skipped: ticket=' . $ticketId . '; type=' . $type . '; recipient=' . $userId . '; error=' . get_class($exception));
        } catch (Throwable) {
        }
        if ($required) {
            throw $exception;
        }
    }
}

/** Notify permitted parties; failures are best-effort by default and propagate when the caller requires transactional delivery. */
function request_center_notify_ticket_parties(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null, bool $required = false): void
{
    try {
        $query = db()->prepare('SELECT t.requester_id, t.assigned_to, t.department_id, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
        $query->execute([$ticketId]);
        $ticket = $query->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            if ($required) {
                throw new RuntimeException('درخواست برای ثبت اعلان پیدا نشد.');
            }
            return;
        }
        $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
        $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
        $unassignedAgentIds = [];
        if ($type === 'ticket_created' && empty($ticket['assigned_to'])) {
            $unassignedAgentIds = ticket_unassigned_notify_candidates();
        }
        $recipients = ticket_notification_recipients(
            $ticket,
            array_merge(
                [(int) $ticket['requester_id'], (int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']],
                $supervisorIds,
                $unassignedAgentIds
            )
        );
        if ($required && $recipients === []) {
            throw new RuntimeException('هیچ گیرندهٔ مجاز و فعالی برای اعلان درخواست پیدا نشد.');
        }
        foreach ($recipients as $recipientId) {
            if ($excludeUserId === null || $recipientId !== $excludeUserId) {
                request_center_notify_user((int) $recipientId, $type, $title, $body, $ticketId, $required);
            }
        }
    } catch (Throwable $exception) {
        try {
            app_error_log('Request Center party notification lookup failed: ticket=' . $ticketId . '; type=' . $type . '; error=' . get_class($exception));
        } catch (Throwable) {
        }
        if ($required) {
            throw $exception;
        }
    }
}

/** Notify only permitted staff; the requester is deliberately excluded. */
function request_center_notify_ticket_staff(int $ticketId, string $type, string $title, string $body, ?int $excludeUserId = null, bool $required = false): void
{
    try {
        $query = db()->prepare('SELECT t.requester_id, t.assigned_to, t.department_id, t.service_group, d.manager_user_id, hu.manager_user_id AS handling_manager_id FROM tickets t LEFT JOIN departments d ON d.id = t.department_id LEFT JOIN handling_units hu ON hu.code = t.service_group WHERE t.id = ? LIMIT 1');
        $query->execute([$ticketId]);
        $ticket = $query->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            if ($required) {
                throw new RuntimeException('درخواست برای ثبت اعلان پیدا نشد.');
            }
            return;
        }
        $supervisorQuery = db()->query('SELECT id FROM users WHERE role = "supervisor" AND is_active = 1');
        $supervisorIds = array_map('intval', $supervisorQuery->fetchAll(PDO::FETCH_COLUMN));
        $unassignedAgentIds = empty($ticket['assigned_to']) ? ticket_unassigned_notify_candidates() : [];
        $recipients = ticket_notification_recipients(
            $ticket,
            array_merge(
                [(int) $ticket['assigned_to'], (int) $ticket['manager_user_id'], (int) $ticket['handling_manager_id']],
                $supervisorIds,
                $unassignedAgentIds
            ),
            null,
            true
        );
        if ($required && $recipients === []) {
            throw new RuntimeException('هیچ گیرندهٔ مجاز و فعالی از کارکنان برای اعلان درخواست پیدا نشد.');
        }
        foreach ($recipients as $recipientId) {
            if ($excludeUserId === null || $recipientId !== $excludeUserId) {
                request_center_notify_user((int) $recipientId, $type, $title, $body, $ticketId, $required);
            }
        }
    } catch (Throwable $exception) {
        try {
            app_error_log('Request Center staff notification lookup failed: ticket=' . $ticketId . '; type=' . $type . '; error=' . get_class($exception));
        } catch (Throwable) {
        }
        if ($required) {
            throw $exception;
        }
    }
}

/** Resolution notice for requester and staff; required mode propagates failures to the lifecycle transaction. */
function request_center_notify_ticket_resolved(int $ticketId, int $actorId, bool $required = false): void
{
    try {
        $ticket = fetch_ticket($ticketId);
        if (!$ticket) {
            if ($required) {
                throw new RuntimeException('درخواست برای ثبت اعلان حل‌شدن پیدا نشد.');
            }
            return;
        }
        $autoCloseDays = max(0, (int) (setting('ticket_auto_close_days', '3') ?? '3'));
        $autoCloseNotice = $autoCloseDays > 0
            ? ' طبق سیاست سامانه، اگر ظرف ' . $autoCloseDays . ' روز امتیاز رضایت ثبت نکنید، درخواست به‌صورت خودکار بسته می‌شود.'
            : '';
        request_center_notify_user(
            (int) $ticket['requester_id'],
            'ticket_resolved',
            'درخواست شما انجام شد',
            'درخواست ' . ticket_number($ticketId) . ' حل شد. لطفاً نتیجه را بررسی و امتیاز رضایت خود را ثبت کنید؛ اگر مشکل برطرف نشده است درخواست را بازگشایی کنید.' . $autoCloseNotice,
            $ticketId,
            $required
        );
        request_center_notify_ticket_staff(
            $ticketId,
            'ticket_resolved',
            'درخواست حل شد و منتظر امتیاز کاربر است',
            'درخواست ' . ticket_number($ticketId) . ' حل‌شده است و پس از ثبت امتیاز کاربر قابل تأیید نهایی است.',
            $actorId,
            $required
        );
    } catch (Throwable $exception) {
        try {
            app_error_log('Request Center resolved notification failed: ticket=' . $ticketId . '; error=' . get_class($exception));
        } catch (Throwable) {
        }
        if ($required) {
            throw $exception;
        }
    }
}

/**
 * Request Center supervisor authority for lifecycle operations outside the
 * ordinary handler hierarchy (e.g. queue assignment and cancellation review).
 * Reuses existing permissions and preserves separation of duties.
 */
function request_center_can_supervise_ticket(array $ticket, array $user): bool
{
    return user_can($user, 'supervisor.panel')
        && user_can($user, 'ticket.edit')
        && ticket_user_can_view($ticket, $user)
        && !ticket_user_is_requester($ticket, $user);
}

function request_center_ticket_return_url(int $ticketId): string
{
    $user = $GLOBALS['user'] ?? [];
    $workspace = (string) ($_POST['rc_return_workspace'] ?? '');
    $allowed = [
        'requester' => user_can($user, 'ticket.create') || user_can($user, 'ticket.view_own'),
        'agent' => user_can($user, 'ticket.edit') || user_can($user, 'queue.view')
            || user_can($user, 'ticket.view_unit') || user_can($user, 'ticket.view_all'),
        'supervisor' => user_can($user, 'supervisor.panel'),
        'admin' => user_is_primary_admin($user),
    ];
    if (!isset($allowed[$workspace]) || !$allowed[$workspace]) {
        $action = (string) ($_POST['action'] ?? '');
        $workspace = in_array($action, ['supervisor_decision', 'supervisor_reopen', 'supervisor_assign_ticket'], true)
            && user_can($user, 'supervisor.panel') ? 'supervisor'
            : ((user_can($user, 'ticket.edit') || user_can($user, 'queue.view')) ? 'agent' : 'requester');
    }
    $params = ['page' => 'ticket', 'id' => max(0, $ticketId), 'workspace' => $workspace];
    $originPage = (string) ($_POST['rc_return_origin_page'] ?? '');
    if (($workspace === 'agent' && $originPage === 'queue' && user_can($user, 'queue.view'))
        || ($workspace === 'supervisor' && $originPage === 'supervisor' && user_can($user, 'supervisor.panel'))) {
        $params['return_page'] = $originPage;
    }
    if ($workspace === 'agent') {
        $params['agent_view'] = (string) ($_POST['rc_return_agent_view'] ?? '') === 'team'
            && user_can($user, 'queue.view') ? 'team' : 'mine';
        $queue = (string) ($_POST['rc_return_queue'] ?? 'all');
        $params['queue'] = in_array($queue, ['all','unassigned','overdue','mine','waiting_user','resolved','cancellation_requested','cancelled','rejected'], true) ? $queue : 'all';
    }
    foreach (['q' => 100, 'status' => 32, 'priority' => 16, 'service_group' => 16, 'from' => 24, 'to' => 24] as $key => $maxLength) {
        $value = trim((string) ($_POST['rc_return_' . $key] ?? ''));
        if ($value !== '' && strlen($value) <= $maxLength) {
            $params[$key] = $value;
        }
    }
    $requestedTicketType = valid_choice((string) ($_POST['rc_return_ticket_type'] ?? ''), ['incident', 'request', 'problem', 'change'], '');
    if ($requestedTicketType !== '') {
        $params['ticket_type'] = $requestedTicketType;
    }
    foreach (['department_id', 'assigned_to'] as $returnIdKey) {
        $returnId = (int) ($_POST['rc_return_' . $returnIdKey] ?? 0);
        if ($returnId > 0) {
            $params[$returnIdKey] = $returnId;
        }
    }
    if ((string) ($_POST['rc_return_calendar'] ?? '') === '1'
        && in_array($workspace, ['agent', 'supervisor', 'admin'], true)) {
        $params['calendar'] = 1;
        $calendarMonth = (string) ($_POST['rc_return_jmonth'] ?? '');
        if (preg_match('/^\\d{4}-(0[1-9]|1[0-2])$/', $calendarMonth)) {
            $params['jmonth'] = $calendarMonth;
        }
        $params['view'] = (string) ($_POST['rc_return_view'] ?? '') === 'agenda' ? 'agenda' : 'month';
        $calendarKind = (string) ($_POST['rc_return_kind'] ?? '');
        if (in_array($calendarKind, ['sla', 'work', 'visit', 'await', 'followup'], true)) {
            $params['kind'] = $calendarKind;
        }
    }
    $params['p'] = max(1, min(100000, (int) ($_POST['rc_return_p'] ?? 1)));
    return 'request-center.php?' . http_build_query($params);
}


/** شناسه‌های واحدهایی که این کاربر در چارت سازمانی، رئیس‌شان است (خالی = مدیر واحد نیست). */
function request_center_unit_head_scope_ids(array $user): array
{
    if (!function_exists('ticket_manager_scope_unit_ids')) {
        return [];
    }
    try {
        return ticket_manager_scope_unit_ids($user);
    } catch (Throwable $exception) {
        if (function_exists('app_error_log')) {
            app_error_log('[rc-unit-head] ' . $exception->getMessage());
        }
        return [];
    }
}

/** آیا کاربر «مدیر واحد» است؟ (رئیسِ فعالِ دست‌کم یک واحد چارت، بدون نقش نظارتی سراسری) */
function request_center_is_unit_head(array $user): bool
{
    if (function_exists('user_is_primary_admin') && user_is_primary_admin($user)) {
        return false;
    }
    // اولویت با خودِ نقش است؛ دسترسی‌های موردیِ گرنت‌شده از گروه‌ها، نقش را تغییر نمی‌دهند.
    $role = (string) ($user['role'] ?? '');
    if (function_exists('is_global_ticket_role') && is_global_ticket_role($role)) {
        return false;
    }
    return request_center_unit_head_scope_ids($user) !== [];
}

/**
 * آیا کاربر «مدیر واحدِ صرف» است؟ (مدیر واحد، بدون هیچ نشانۀ کارشناسی/پرسنل پشتیبانی)
 * این‌گونه افراد در صف/تقویم/گزارش‌های عملیاتی شرکت نمی‌کنند؛ جای آن‌ها «فضای مدیر واحد» است.
 */
function request_center_is_org_unit_head(array $user): bool
{
    if (function_exists('ticket_sole_unit_head')) {
        // یک منبع حقیقت: lib/auth-scope.php::ticket_sole_unit_head (همان مرزِ چارت).
        return ticket_sole_unit_head($user);
    }
    if (!request_center_is_unit_head($user)) {
        return false;
    }
    $role = (string) ($user['role'] ?? '');
    if (in_array($role, ['agent', 'support_manager'], true)) {
        return false;
    }
    if (function_exists('is_it_agent') && is_it_agent($user)) {
        return false;
    }
    if (trim((string) ($user['handling_unit_code'] ?? '')) !== '') {
        return false;
    }
    return true;
}


/** آیا ثبت‌کنندهٔ این درخواست، داخل زیردرخت واحدهایی است که این کاربر رئیس فعال آن‌هاست؟ */
function request_center_manager_ticket_in_subtree(array $ticket, array $user): bool
{
    if (!function_exists('ticket_manager_scope_unit_ids') || !function_exists('ticket_requester_tree_predicate')) {
        return false;
    }
    $unitIds = ticket_manager_scope_unit_ids($user);
    if (!$unitIds) {
        return false;
    }
    [$predicate, $params] = ticket_requester_tree_predicate('?', $unitIds);
    try {
        $query = db()->prepare('SELECT ' . $predicate);
        $query->execute(array_merge([(int) ($ticket['requester_id'] ?? 0)], $params));
        return (bool) $query->fetchColumn();
    } catch (Throwable $error) {
        if (function_exists('app_error_log')) {
            app_error_log('[unit-head-cancel] ' . $error->getMessage());
        }
        return false;
    }
}

/**
 * «مدیر واحدِ صرف» می‌تواند برای درخواستِ بازِ بدون درخواست لغوِ درانتظارِ ثبت‌شده توسط
 * عضوی از زیرمجموعه‌اش، فقط «درخواست لغو» ثبت کند (هرگز لغو مستقیم یا تصمیم نهایی نیست).
 * برای درخواست‌های خودش، مسیر عادی ثبت‌کننده اعمال می‌شود.
 */
function request_center_manager_can_request_cancel(array $ticket, array $user): bool
{
    if (!function_exists('ticket_sole_unit_head') || !ticket_sole_unit_head($user)) {
        return false;
    }
    if (function_exists('ticket_user_is_requester') && ticket_user_is_requester($ticket, $user)) {
        return false;
    }
    if (!function_exists('ticket_requester_cancelable_statuses')
        || !in_array((string) ($ticket['status'] ?? ''), ticket_requester_cancelable_statuses(), true)) {
        return false;
    }
    if (!empty($ticket['cancel_requested_at'])) {
        return false;
    }
    return request_center_manager_ticket_in_subtree($ticket, $user);
}
