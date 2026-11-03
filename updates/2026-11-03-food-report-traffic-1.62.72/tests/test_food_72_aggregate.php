<?php
declare(strict_types=1);
function db(): PDO {return $GLOBALS['pdo'];}
function app_error_log(string $s): void {}
function food_ticket_parse_date(string $s): ?string {return preg_match('/^\d{4}-\d\d-\d\d$/',$s)?$s:null;}
function food_ticket_digits($s): string {return preg_replace('/\D/','',(string)$s);}
function food_ticket_api_orders(...$args): array {$GLOBALS['args']=$args;return [['foodDate'=>'2026-11-03','nat'=>'123','pc'=>'1','first'=>'الف','last'=>'ب']];}
function food_ticket_api_absent($a,$b): array {return [['foodDate'=>$a,'full_name'=>'نمونه']];}
require __DIR__.'/../../../food-order.php';
$source=file_get_contents(__DIR__.'/../../../food-ticket.php');$start=strpos($source,'function food_ticket_aggregate_last_name(');$end=strpos($source,'/** ستونهای قابلانتخاب گزارش تجمیعی', $start);if($start===false||$end===false)die("missing functions\n");eval(substr($source,$start,$end-$start));
function check(bool $b,string $s): void {if(!$b){echo "FAIL $s\n";exit(1);}echo "PASS $s\n";}
$GLOBALS['pdo']=new PDO('sqlite::memory:');$db=db();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE food_orders(id INTEGER PRIMARY KEY,employee_id INT,food_date TEXT,meal_type TEXT,status TEXT)');$q=$db->prepare('INSERT INTO food_orders(employee_id,food_date,meal_type,status) VALUES (?,?,?,?)');for($i=0;$i<193;$i++)$q->execute([$i,'2026-11-03','lunch','active']);for($i=0;$i<5;$i++)$q->execute([300+$i,'2026-11-03','lunch','cancelled']);$q->execute([500,'2026-11-03','dinner','active']);$q->execute([501,'2026-11-04','lunch','active']);
$db->exec('CREATE TABLE food_ticket_events(id INTEGER PRIMARY KEY,full_name TEXT,personnel_code TEXT,national_code TEXT,punch_date TEXT,punch_time TEXT,food_type TEXT,event_type TEXT,print_status TEXT)');
$db->exec("INSERT INTO food_ticket_events VALUES (1,'الف','1','123','2026-11-03','12:00','کباب','printed','printed'),(2,'مهمان','','','2026-11-03','12:01','مهمان','guest','printed'),(3,'مهمان','','','2026-11-03','12:02','مهمان','guest','print_error'),(4,'قدیمی','2','456','2026-11-04','12:01','خورشت','print','printed')");
$r=food_ticket_api_aggregate_report('2026-11-03','2026-11-04');$c=$r['counts'];
check($c['total_orders']===194 && $c['cancelled_orders']===5 && $c['order_rows']===199 && $c['all_meal_orders']===195 && $c['dinner_orders']===1,'aggregate lunch vs all meals 2-day counts');
check($c['guest_printed']===1 && $c['printed_final']===2 && $c['legacy_printed']===1,'guest successful, staff and legacy prints separated');
check($GLOBALS['args']===['2026-11-03','','2026-11-04'],'diagnostic uses end date as third argument');
check(!isset($r['printed_people'][0]['nat']) && !isset($r['printed_people'][0]['event_id']),'diagnostic identifiers not leaked in public list');
check(count($r['source_notes'])>0,'noncritical diagnostics failure visible');
$db->exec('DROP TABLE food_ticket_events');try {food_ticket_api_aggregate_report('2026-11-03','2026-11-04');check(false,'event source failure');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'آمار قابل محاسبه نیست'),'critical source failure fails closed');}
echo "ALL PASSED\n";
