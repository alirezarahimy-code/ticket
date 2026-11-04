<?php
declare(strict_types=1);
function app_error_log(string $s): void {}
class MonitorStmt {
    public function __construct(public string $sql) {}
    public function execute(?array $p=null): bool { if (!empty($GLOBALS['fail_compare']) && str_contains($this->sql,'LEFT JOIN food_orders')) throw new RuntimeException('unavailable');return true; }
    public function fetchAll(int $m=0): array { return $GLOBALS['rows']; }
}
class MonitorDB { public function prepare(string $s): MonitorStmt { return new MonitorStmt($s); } }
function db(): MonitorDB { static $db; return $db??=new MonitorDB(); }
require __DIR__.'/../food-ticket.php';
food_ticket_delivery_supported(true);food_ticket_source_delete_supported(false);
function chk(bool $x,string $m): void { if(!$x) {echo "FAIL $m\n";exit(1);} echo "PASS $m\n"; }
$base=['id'=>1,'user_id'=>1,'attendance_date'=>'2026-11-02','food_type'=>'ساندویچ','result'=>'printed','print_status'=>'printed','ticket_key'=>'food:2026-11-02:123','current_order_id'=>7,'current_order_status'=>'active','current_order_food'=>'جوجه کباب'];
$GLOBALS['rows']=[$base];$out=food_ticket_api_monitoring('2026-11-02','2026-11-02')[0];
chk($out['food_type']==='ساندویچ' && $out['current_order_food']==='جوجه کباب' && $out['current_order_state']==='different','printed snapshot preserved and current order shown separately');
$GLOBALS['rows'][0]['current_order_status']='cancelled';$out=food_ticket_api_monitoring('2026-11-02','2026-11-02')[0];chk($out['current_order_state']==='cancelled' && $out['current_order_food']==='','cancelled order');
$GLOBALS['rows'][0]['ticket_key']='guest:test';$out=food_ticket_api_monitoring('2026-11-02','2026-11-02')[0];chk($out['current_order_state']==='not_applicable','guest not compared');
$GLOBALS['fail_compare']=true;$out=food_ticket_api_monitoring('2026-11-02','2026-11-02')[0];chk($out['food_type']==='ساندویچ' && $out['current_order_state']==='unavailable','DB comparison unavailable preserves monitoring');
echo "ALL PASSED\n";
