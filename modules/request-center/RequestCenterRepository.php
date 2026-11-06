<?php
declare(strict_types=1);

/**
 * Read-only Request Center repository.
 *
 * This is the first strangler slice: it owns scoped reads only. Existing write
 * workflows remain on the legacy POST handlers until each workflow has been
 * migrated and regression-tested independently.
 */
final class RequestCenterRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Read-only schema preflight. The independent entry point must never heal
     * or mutate schema at request time; incomplete installs fail closed.
     *
     * @return array<int,string>
     */
    public function schemaProblems(): array
    {
        try {
            $tableRows = $this->pdo->query(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
            )->fetchAll(PDO::FETCH_COLUMN);
            $tables = array_fill_keys(array_map('strtolower', $tableRows), true);
            $required = [
                'tickets' => ['id', 'ticket_no', 'subject', 'status', 'priority', 'service_group', 'ticket_type',
                    'created_at', 'updated_at', 'due_at', 'requester_id', 'assigned_to', 'department_id',
                    'category_id', 'service_id', 'asset_id', 'requesting_unit_id'],
                'users' => ['id', 'full_name', 'role'],
                'departments' => ['id', 'name'],
                'categories' => ['id', 'name'],
                'service_catalog' => ['id', 'name'],
                'assets' => ['id', 'hostname', 'asset_tag'],
                'org_units' => ['id', 'name'],
                'ticket_messages' => ['id', 'ticket_id', 'user_id', 'body', 'is_internal', 'created_at'],
                'ticket_attachments' => ['id', 'message_id', 'original_name', 'mime', 'size_bytes'],
            ];
            $columnRows = $this->pdo->query(
                'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()'
            )->fetchAll(PDO::FETCH_ASSOC);
            $columns = [];
            foreach ($columnRows as $row) {
                $table = strtolower((string) $row['TABLE_NAME']);
                $column = strtolower((string) $row['COLUMN_NAME']);
                $columns[$table][$column] = true;
            }

            $problems = [];
            foreach ($required as $table => $names) {
                if (!isset($tables[$table])) {
                    $problems[] = 'جدول ' . $table . ' وجود ندارد.';
                    continue;
                }
                foreach ($names as $name) {
                    if (!isset($columns[$table][$name])) {
                        $problems[] = 'ستون ' . $table . '.' . $name . ' وجود ندارد.';
                    }
                }
            }
            return $problems;
        } catch (Throwable $exception) {
            return ['بررسی ساختار پایگاه‌داده ممکن نشد: ' . get_class($exception)];
        }
    }

    /** @return array{total:int,rows:array<int,array<string,mixed>>} */
    public function listVisible(array $user, int $page = 1, int $perPage = 50, array $filters = []): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset = ($page - 1) * $perPage;
        [$scopeSql, $scopeParams] = ticket_scope($user, 't');
        $clauses = [];
        $params = $scopeParams;

        $status = (string) ($filters['status'] ?? '');
        if (in_array($status, ['new', 'manager_review', 'assigned', 'in_progress', 'waiting_user', 'pending', 'resolved', 'closed', 'cancelled'], true)) {
            $clauses[] = 't.status = ?';
            $params[] = $status;
        }
        $priority = (string) ($filters['priority'] ?? '');
        if (in_array($priority, ['normal', 'urgent', 'critical'], true)) {
            $clauses[] = 't.priority = ?';
            $params[] = $priority;
        }
        $requesterId = (int) ($filters['requester_id'] ?? 0);
        if ($requesterId > 0) {
            $clauses[] = 't.requester_id = ?';
            $params[] = $requesterId;
        }

        $assigneeId = (int) ($filters['assigned_to'] ?? 0);
        if ($assigneeId > 0) {
            $clauses[] = 't.assigned_to = ?';
            $params[] = $assigneeId;
        }
        $serviceGroup = (string) ($filters['service_group'] ?? '');
        if (in_array($serviceGroup, ['it', 'support'], true)) {
            $clauses[] = 't.service_group = ?';
            $params[] = $serviceGroup;
        }
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $search = function_exists('mb_substr') ? mb_substr($search, 0, 100, 'UTF-8') : substr($search, 0, 100);
            $clauses[] = '(t.subject LIKE ? OR t.ticket_no LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $whereParts = [];
        if ($scopeSql !== '') {
            $whereParts[] = preg_replace('/^\\s*WHERE\\s+/i', '', trim($scopeSql));
        }
        foreach ($clauses as $clause) {
            $whereParts[] = $clause;
        }
        $where = $whereParts !== [] ? ' WHERE ' . implode(' AND ', $whereParts) : '';

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM tickets t ' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $sql = 'SELECT t.id, t.ticket_no, t.subject, t.status, t.priority, t.service_group,
                       t.ticket_type, t.created_at, t.updated_at, t.due_at,
                       r.full_name AS requester_name, a.full_name AS assignee_name,
                       d.name AS department_name
                FROM tickets t
                JOIN users r ON r.id = t.requester_id
                LEFT JOIN users a ON a.id = t.assigned_to
                LEFT JOIN departments d ON d.id = t.department_id
                ' . $where . '
                ORDER BY t.updated_at DESC, t.id DESC
                LIMIT ' . $perPage . ' OFFSET ' . $offset;
        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return ['total' => $total, 'rows' => $query->fetchAll(PDO::FETCH_ASSOC)];
    }

    /** Returns null for both missing and out-of-scope requests. */
    public function findVisible(int $ticketId, array $user): ?array
    {
        if ($ticketId <= 0) {
            return null;
        }

        $query = $this->pdo->prepare(
            'SELECT t.*, r.full_name AS requester_name, a.full_name AS assignee_name,
                    d.name AS department_name, c.name AS category_name,
                    s.name AS service_name, asset.hostname, asset.asset_tag,
                    ru.name AS requesting_unit_name
             FROM tickets t
             JOIN users r ON r.id = t.requester_id
             LEFT JOIN users a ON a.id = t.assigned_to
             LEFT JOIN departments d ON d.id = t.department_id
             LEFT JOIN categories c ON c.id = t.category_id
             LEFT JOIN service_catalog s ON s.id = t.service_id
             LEFT JOIN assets asset ON asset.id = t.asset_id
             LEFT JOIN org_units ru ON ru.id = t.requesting_unit_id
             WHERE t.id = ? LIMIT 1'
        );
        $query->execute([$ticketId]);
        $ticket = $query->fetch(PDO::FETCH_ASSOC);
        if (!is_array($ticket) || !ticket_user_can_view($ticket, $user)) {
            return null;
        }

        return $ticket;
    }

    /** @return array<int,array<string,mixed>> */
    public function messages(int $ticketId, bool $includeInternal): array
    {
        $sql = 'SELECT m.id, m.ticket_id, m.user_id, m.body, m.is_internal, m.created_at,
                       u.full_name, u.role
                FROM ticket_messages m
                JOIN users u ON u.id = m.user_id
                WHERE m.ticket_id = ?';
        if (!$includeInternal) {
            $sql .= ' AND COALESCE(m.is_internal, 0) = 0';
        }
        $sql .= ' ORDER BY m.created_at ASC, m.id ASC';
        $query = $this->pdo->prepare($sql);
        $query->execute([$ticketId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param array<int,array<string,mixed>> $messages
     *  @return array<int,array<int,array<string,mixed>>>
     */
    public function attachmentsForMessages(array $messages): array
    {
        $messageIds = array_values(array_unique(array_filter(
            array_map(static fn (array $message): int => (int) ($message['id'] ?? 0), $messages),
            static fn (int $id): bool => $id > 0
        )));
        if ($messageIds === []) {
            return [];
        }

        $marks = implode(',', array_fill(0, count($messageIds), '?'));
        $query = $this->pdo->prepare(
            'SELECT id, message_id, original_name, mime, size_bytes
             FROM ticket_attachments
             WHERE message_id IN (' . $marks . ')
             ORDER BY id ASC'
        );
        $query->execute($messageIds);

        $result = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $attachment) {
            $result[(int) $attachment['message_id']][] = $attachment;
        }
        return $result;
    }
}
