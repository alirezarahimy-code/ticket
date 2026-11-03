<?php
declare(strict_types=1);
class DeliveryStmt {
 public function __construct(public string $sql){}
 public function execute(?array $args=null):bool{return true;}
 public function fetchAll(int $mode=0):array {
  if(str_contains($this->sql,'information_schema.TABLES')) return ['food_catalog','food_calendar','food_calendar_items','food_orders','food_order_logs'];
  if(str_contains($this->sql,'information_schema.COLUMNS')) return ['food_calendar','food_orders'];
  if(str_contains($this->sql,'FROM food_calendar cal')) return [['food_date'=>'2026-11-02','food_name'=>'ساندویچ','sort_order'=>1]];
  if(str_contains($this->sql,'FROM food_ticket_events e')) return $GLOBALS['rows'];
  throw new RuntimeException($this->sql);
 }
}
class DeliveryDB {public function prepare(string $s):DeliveryStmt{return new DeliveryStmt($s);}}
function db():DeliveryDB {static $db;return $db??=new DeliveryDB();}
function app_error_log(string $m):void{}
require __DIR__.'/../food-order.php';
function chk(bool $v,string $msg):void {if(!$v)throw new RuntimeException($msg);echo "PASS $msg\n";}
$base=['id'=>1,'user_id'=>5,'food_date'=>'2026-11-02','food_name'=>'ساندویچ','event_type'=>'printed','current_order_id'=>7,'current_order_status'=>'active','current_order_food'=>'جوجه کباب'];
$GLOBALS['rows']=[$base];$r=food_order_printed_delivery_report('2026-11-02','2026-11-02')['items'][0];
chk($r['food_name']==='ساندویچ' && $r['current_order_food']==='جوجه کباب' && $r['current_order_state']==='different','receipt preserved and differing order exposed');
$GLOBALS['rows'][0]['current_order_status']='cancelled';$r=food_order_printed_delivery_report('2026-11-02','2026-11-02')['items'][0];chk($r['current_order_state']==='cancelled' && $r['current_order_food']==='', 'cancelled choice not reported as active');
$GLOBALS['rows'][0]['event_type']='guest';$r=food_order_printed_delivery_report('2026-11-02','2026-11-02')['items'][0];chk($r['current_order_state']==='not_applicable','guest is not compared');
echo "ALL PASSED\n";
