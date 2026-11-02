<?php
declare(strict_types=1);
// Behavioural test of the real functions with an in-memory DB stub; never touches a live database.
function user_can(array $user, string $permission): bool { return ($user['role'] ?? '') === 'support_manager'; }
function app_error_log(string $message): void {}

final class GuestStatement {
    public array $params = [];
    public function __construct(public string $sql, private GuestDatabase $db) {}
    public function execute(?array $params = null): bool { $this->params = $params ?? []; $this->db->queries[] = [$this->sql, $this->params]; return true; }
    public function fetchColumn(): mixed {
        if (str_contains($this->sql, 'SELECT DATABASE()')) return 'fake';
        if (str_contains($this->sql, 'information_schema.TABLES')) return 1;
        return false;
    }
    public function fetch(int $mode = 0): array|false {
        if (str_contains($this->sql, 'FROM food_guest_requests WHERE id')) return $this->db->rows[(int) ($this->params[0] ?? 0)] ?? false;
        return false;
    }
    public function fetchAll(int $mode = 0): array {
        if (str_contains($this->sql, "TABLE_NAME = 'food_guest_requests'")) return ['request_date', 'organization', 'guest_count', 'food_id', 'requester_name', 'deputy_unit_id', 'status'];
        if (str_contains($this->sql, 'SELECT TABLE_NAME FROM information_schema.TABLES')) return ['food_catalog','food_calendar','food_calendar_items','food_orders','food_order_logs'];
        if (str_contains($this->sql, "TABLE_NAME IN ('food_calendar', 'food_orders')")) return ['food_calendar','food_orders'];
        if (str_contains($this->sql, 'FROM food_catalog')) return [];
        if (str_contains($this->sql, 'FROM food_guest_requests r')) {
            $rows = array_values($this->db->rows); $offset = 0;
            if (str_contains($this->sql, 'r.created_by = ?')) {
                $owner = (int) $this->params[$offset++];
                $rows = array_values(array_filter($rows, fn($r) => (int) ($r['created_by'] ?? 0) === $owner));
            }
            if (str_contains($this->sql, 'r.request_date >= ?')) {
                $today = $this->params[$offset++];
                $rows = array_values(array_filter($rows, fn($r) => $r['request_date'] >= $today));
            }
            if (str_contains($this->sql, 'LIMIT 300')) $rows = array_slice($rows, 0, 300);
            return $rows;
        }
        return [];
    }
}
final class GuestDatabase {
    public array $rows = []; public array $queries = []; private bool $inTransaction = false;
    public function prepare(string $sql): GuestStatement { return new GuestStatement($sql, $this); }
    public function query(string $sql): GuestStatement { $s = $this->prepare($sql); $s->execute(); return $s; }
    public function beginTransaction(): void { $this->inTransaction = true; }
    public function inTransaction(): bool { return $this->inTransaction; }
    public function commit(): void { $this->inTransaction = false; }
    public function rollBack(): void { $this->inTransaction = false; }
}
function db(): GuestDatabase { static $db; return $db ??= new GuestDatabase(); }
require __DIR__ . '/../food-order.php';
function check_guest(bool $condition, string $label): void { if (!$condition) { fwrite(STDERR, "FAIL $label\n"); exit(1); } echo "PASS $label\n"; }
function denied(callable $fn): bool { try { $fn(); return false; } catch (RuntimeException $e) { return str_contains($e->getMessage(), 'فقط مدیر'); } }

$future = (new DateTimeImmutable('+7 days'))->format('Y-m-d');
$past = (new DateTimeImmutable('-7 days'))->format('Y-m-d');
$row = ['request_date'=>$future,'organization'=>'Company','guest_count'=>2,'requester_name'=>'Name','note'=>'', 'status'=>'active', 'created_at'=>'', 'food_id'=>null, 'food_name'=>null, 'deputy_unit_id'=>null, 'deputy_name'=>'', 'created_by_name'=>'Owner'];
// Many other people's rows before our own prove that filtering happens before the limit.
for ($i = 1; $i <= 310; $i++) db()->rows[$i] = $row + ['id'=>$i, 'created_by'=>2];
db()->rows[311] = $row + ['id'=>311, 'created_by'=>1];
db()->rows[312] = array_merge($row, ['id'=>312, 'created_by'=>1, 'request_date'=>$past]);
db()->rows[313] = $row + ['id'=>313, 'created_by'=>null];
$manager = ['id'=>1,'role'=>'manager'];
$reviewer = ['id'=>2,'role'=>'support_manager'];
check_guest(array_column(food_guest_request_list($manager)['items'], 'id') === [311], 'manager receives only own current and future records before LIMIT');
check_guest(array_column(food_guest_request_list($manager, true)['items'], 'id') === [311,312], 'personal history remains owner scoped');
check_guest(count(food_guest_request_list($reviewer)['items']) === 300, 'reviewer personal view is also owner scoped and capped');
check_guest(count(food_guest_request_list($reviewer, true, true)['items']) === 313, 'authorized management view retains history');
try { food_guest_request_list($manager, true, true); check_guest(false, 'manager cannot request management scope'); } catch (RuntimeException $e) { check_guest(true, 'manager cannot request management scope'); }
try { food_guest_request_list(['id'=>0,'role'=>'manager']); check_guest(false, 'missing user id rejected'); } catch (RuntimeException $e) { check_guest(true, 'missing user id rejected'); }
check_guest(denied(fn() => food_guest_request_cancel($reviewer,311)), 'reviewer cannot cancel another user through personal route');
check_guest(denied(fn() => food_guest_request_delete($reviewer,311)), 'reviewer cannot delete another user through personal route');
check_guest(denied(fn() => food_guest_request_update($reviewer,311,['request_date'=>$future,'organization'=>'Company','guest_count'=>'2','requester_name'=>'Name'])), 'reviewer cannot edit another user through personal route');
check_guest(db()->inTransaction() === false, 'transactions roll back on denied mutations');
echo "ALL PASSED\n";
