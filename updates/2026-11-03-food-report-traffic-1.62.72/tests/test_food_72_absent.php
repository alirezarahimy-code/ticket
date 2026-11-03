<?php
declare(strict_types=1);
function db(): PDO { return $GLOBALS['pdo']; }
function app_error_log(string $s): void {}
function food_ticket_parse_date($s): ?string { return $s; }
function food_ticket_digits($s): string { return preg_replace('/\D/','',(string)$s); }
function food_order_internal_items(...$args): array {return [];}
function food_order_mode(): string {return 'INTERNAL-DB';}
function food_ticket_api_orders(...$args): array {return [['nat'=>'123','pc'=>'55','first'=>'الف','last'=>'ب','food'=>'کباب','foodDate'=>'2026-11-03']];}
$s=file_get_contents(__DIR__.'/../../../food-ticket.php');$a=strpos($s,'function food_ticket_api_absent(');$b=strpos($s,'/**', $a);eval(substr($s,$a,$b-$a));
function check(bool $b,string $s): void {if(!$b){echo "FAIL $s\n";exit(1);}echo "PASS $s\n";}
$GLOBALS['pdo']=new PDO('sqlite::memory:');$db=db();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->exec('CREATE TABLE food_ticket_events(punch_date TEXT,national_code TEXT,personnel_code TEXT,print_status TEXT,event_type TEXT)');$db->exec('CREATE TABLE users(id INTEGER,national_code TEXT)');
$db->exec("INSERT INTO food_ticket_events VALUES('2026-11-03','123','55','printed','guest')");
check(count(food_ticket_api_absent('2026-11-03'))===1,'guest receipt cannot suppress staff absent');
$db->exec("INSERT INTO food_ticket_events VALUES('2026-11-03','123','55','printed','printed')");check(count(food_ticket_api_absent('2026-11-03'))===0,'staff print matches staff absent');
$db->exec('DROP TABLE food_ticket_events');try {food_ticket_api_absent('2026-11-03');check(false,'error');}catch(RuntimeException $e){check(true,'missing event source throws');}echo "ALL PASSED\n";
