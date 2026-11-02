<?php
declare(strict_types=1);
// Behavioural tests of the real engine/order source using a fake DB. No live records or printing.
define('APP_ROOT', __DIR__ . '/..');
function food_ticket_source_table(): string { return 'SOURCE_TABLE'; }
function food_order_schema_ensure(): void {}
function food_order_mode(bool $reload = false): string { return 'INTERNAL-DB'; }
function food_order_iso_date(string $s): ?string { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) ? $s : null; }
function food_order_digits(mixed $s): string { return preg_replace('/\D/', '', (string) $s); }
function food_ticket_digits(mixed $s): string { return food_order_digits($s); }
function food_ticket_code(mixed $s): string { return trim((string)$s); }
function food_ticket_card_key(string $s): string { return ""; }
function food_ticket_parse_date(mixed $s): ?string { return food_order_iso_date((string)$s); }
function food_ticket_parse_time(mixed $s): ?string { return (string)$s; }
function food_ticket_source_key(array $a): string { return 'source-'.($a['uid'] ?? ''); }
function food_ticket_existing_source(string $s): ?array { return null; }
function food_ticket_existing_ticket(string $s): ?array { return null; }
function food_ticket_card_equals(mixed $a, mixed $b): bool { return (string)$a === (string)$b; }
function food_ticket_guest_card(string $card, array $config): ?array { return ['guest_name'=>'مهمان','daily_limit'=>0]; }
function food_guest_requested_total(string $date): ?int { return $GLOBALS['requested']; }
function app_error_log(string $s): void {}
function food_ticket_config(bool $reload = false): array { return ['enabled'=>0]; }
function setting(string $key, ?string $default=null): ?string { return $GLOBALS['settings_cache'][$key] ??= ($GLOBALS['settings_db'][$key] ?? $default); }
function setting_forget_cache(): void { $GLOBALS['settings_cache']=[]; }
class ReceiptStmt {
    public array $params=[];
    public function __construct(public string $sql) {}
    public function execute(?array $p=null): bool { $this->params=$p??[]; return true; }
    public function fetchAll(int $mode=0): array {
        if (str_contains($this->sql, 'SELECT o.employee_id, u.national_code, c.food_name')) return $GLOBALS['order_rows'];
        if (str_contains($this->sql, 'SELECT c.food_name FROM food_orders')) return $GLOBALS['current_food_rows'][$this->params[0]] ?? [];
        return [];
    }
    public function fetchColumn(int $col=0): mixed { if (str_contains($this->sql, 'FROM settings')) return $GLOBALS['settings_db'][$this->params[0]] ?? false; return str_contains($this->sql, 'food_ticket_events') ? $GLOBALS['issued'] : false; }
    public function fetch(int $mode=0): array|false { return str_contains($this->sql, 'traffic_visits') ? ['entries'=>0,'early_exits'=>0] : false; }
}
class ReceiptDB {
    public function prepare(string $sql): ReceiptStmt { return new ReceiptStmt($sql); }
    public function query(string $sql): ReceiptStmt { $s=new ReceiptStmt($sql);$s->execute();return $s; }
}
function db(): ReceiptDB { static $db; return $db??=new ReceiptDB(); }
require __DIR__ . '/../food-ticket-order-source.php';
require __DIR__ . '/../food-ticket-engine.php';
function chk(bool $ok,string $label): void { if (!$ok) { fwrite(STDERR,"FAIL $label\n"); exit(1); } echo "PASS $label\n"; }
$GLOBALS['settings_db']=['food_guest_cap_mode'=>'variable'];$GLOBALS['settings_cache']=[];
chk(food_guest_cap_mode()==='variable','initial worker mode');
$GLOBALS['settings_db']['food_guest_cap_mode']='requested';
chk(food_guest_cap_mode()==='variable','old request cache illustrates stale loop');
food_ticket_process_batch_v2();
chk(food_guest_cap_mode()==='requested','next batch reloads changed mode');
$date=(new DateTimeImmutable('now',new DateTimeZone('Asia/Tehran')))->format('Y-m-d');
$GLOBALS['requested']=12;$GLOBALS['issued']=11;
$item=['date_raw'=>$date,'time_raw'=>'12:30:00','uid'=>'','card'=>'guest-1','row'=>[]];$map=[];
chk(food_ticket_decide_punch($item,['max_guest_daily'=>20],$map)['event_type']==='guest','request cap 12 allows 12th guest regardless of zero traffic');
$GLOBALS['issued']=12;
chk(food_ticket_decide_punch($item,['max_guest_daily'=>20],$map)['event_type']==='guest_limit','request cap 12 blocks 13th');
$GLOBALS['settings_db']['food_guest_cap_mode']='variable'; food_ticket_process_batch_v2(); $GLOBALS['issued']=0;
chk(food_ticket_decide_punch($item,['max_guest_daily'=>20],$map)['event_type']==='guest_limit','switching back uses traffic cap rather than the 12 requests');
$GLOBALS['settings_db']['food_guest_cap_mode']='requested'; food_ticket_process_batch_v2();
$GLOBALS['requested']=null;
try {food_ticket_decide_punch($item,['max_guest_daily'=>20],$map);chk(false,'missing requested total fails closed');} catch (RuntimeException $e) {chk(str_contains($e->getMessage(),'جمع درخواست'),'missing requested total fails closed without fixed fallback');}
$GLOBALS['order_rows']=[['employee_id'=>3,'national_code'=>'0123456789','food_name'=>'ساندویچ']];
$GLOBALS['current_food_rows']=[3=>[['food_name'=>'جوجه کباب']]];
$map=[$date=>food_order_internal_map($date)];$person=['id'=>3,'is_active'=>1,'national_code'=>'0123456789','full_name'=>'Test'];
$decision=food_ticket_decide_for_user($person,$date,[],[],$map);
chk($decision['print'] && $decision['payload']['food_type']==='جوجه کباب','current employee choice overrides stale batch map for new event');
$GLOBALS['current_food_rows'][3]=[];
$decision=food_ticket_decide_for_user($person,$date,[],[],$map);
chk($decision['event_type']==='no_food' && !$decision['print'],'cancelled current order never prints cached food');
$GLOBALS['current_food_rows'][3]=[['food_name'=>'الف'],['food_name'=>'ب']];
try {food_order_internal_food_for_employee(3,$date);chk(false,'duplicate active orders rejected');} catch (RuntimeException $e) {chk(true,'duplicate active orders rejected');}
echo "ALL PASSED\n";
