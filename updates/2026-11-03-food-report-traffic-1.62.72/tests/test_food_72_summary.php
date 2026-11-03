<?php
declare(strict_types=1);
function db(): PDO { return $GLOBALS['pdo']; }
function app_error_log(string $msg): void { $GLOBALS['errors'][] = $msg; }
require __DIR__ . '/../../../food-order.php';
function check(bool $ok, string $name): void { if (!$ok) { echo 'FAIL: '.$name."\n"; exit(1); } echo 'PASS: '.$name."\n"; }
$GLOBALS['pdo'] = new PDO('sqlite::memory:');
$db = db(); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE food_orders (id INTEGER PRIMARY KEY, employee_id INTEGER, food_date TEXT, meal_type TEXT, status TEXT)');
$stmt=$db->prepare('INSERT INTO food_orders(employee_id,food_date,meal_type,status) VALUES (?,?,?,?)');
for($i=1;$i<=193;$i++) $stmt->execute([$i,'2026-11-03','lunch','active']);
for($i=194;$i<=198;$i++) $stmt->execute([$i,'2026-11-03','lunch','cancelled']);
$stmt->execute([199,'2026-11-03','dinner','active']);
$stmt->execute([200,'2026-11-03','dinner','active']);
$stmt->execute([201,'2026-11-04','lunch','active']);
$a=food_order_report_summary('2026-11-03','2026-11-03','lunch');
check($a['active']===193 && $a['cancelled']===5 && $a['total']===198, '193 active, 5 cancelled, 198 current lunch rows');
$b=food_order_report_summary('2026-11-03','2026-11-03','dinner');
check($b['active']===2 && $b['cancelled']===0, 'different meal remains separate');
$c=food_order_report_summary('2026-11-03','2026-11-04','lunch');
check($c['active']===194 && $c['by_date']['2026-11-04']['active']===1, 'multi-day grouping');
$d=food_order_report_summary('2026-11-03','2026-11-03','lunch',[1,194]);
check($d['active']===1 && $d['cancelled']===1 && $d['total']===2, 'selected employees limited correctly');
$db->exec("UPDATE food_orders SET status='active' WHERE employee_id=194");
$e=food_order_report_summary('2026-11-03','2026-11-03','lunch');
check($e['active']===194 && $e['cancelled']===4 && $e['total']===198, 'reactivation reflects current state, not historical events');
$db->exec('CREATE TABLE food_order_logs (id INTEGER PRIMARY KEY,order_id INTEGER, action TEXT, old_value TEXT,new_value TEXT,reason TEXT,created_at TEXT)');
$db->exec('CREATE TABLE food_ticket_day_visits (user_id INTEGER,visit_date TEXT,group_id INTEGER)');
$db->exec('CREATE TABLE food_ticket_groups (id INTEGER,title TEXT)');
$db->exec('CREATE TABLE food_ticket_group_members (user_id INTEGER,group_id INTEGER)');
$db->exec("INSERT INTO food_order_logs VALUES (1,194,'order_cancel_request_approved',NULL,NULL,'درخواست شخص', '2026-11-03 09:30:00')");
$db->exec("INSERT INTO food_ticket_groups VALUES (1,'سلف اصلی'),(2,'سلف مقصد')");
$db->exec("INSERT INTO food_ticket_group_members VALUES (194,1)");
$db->exec("INSERT INTO food_ticket_day_visits VALUES (194,'2026-11-03',2)");
$rows=food_order_report_descriptions([['id'=>194,'employee_id'=>194,'status'=>'cancelled']], '2026-11-03');
check(str_contains($rows[0]['report_note'],'درخواست شخص') && str_contains($rows[0]['report_note'],'سلف مقصد') && str_contains($rows[0]['report_note'],'سلف اصلی'),'cancellation and dated transfer descriptions');
$db->exec('DROP TABLE food_order_logs');
$rows=food_order_report_descriptions([['id'=>194,'employee_id'=>194,'status'=>'cancelled']], '2026-11-03');
check(str_contains($rows[0]['report_note'],'در دسترس نیست'), 'history source failure explicit');
echo "ALL PASSED\n";
