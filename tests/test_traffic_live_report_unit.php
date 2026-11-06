<?php
declare(strict_types=1);
const APP_ROOT = __DIR__.'/..';
$GLOBALS['db'] = new PDO('sqlite::memory:');
$GLOBALS['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$GLOBALS['db']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$GLOBALS['db']->sqliteCreateFunction('CURDATE', fn() => '2026-10-12', 0);
$GLOBALS['db']->sqliteCreateFunction('CURTIME', fn() => '14:15:00', 0);
function db(): PDO {return $GLOBALS['db'];}
function save_audit(...$args): void {}
function save_audit_required(...$args): void { save_audit(...$args); }
function log_sort_order(array $map, string $key, string $dir, string $default): string {return $default;}
function e($value): string {return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');}
function user_can($u,$perm): bool {return in_array($perm, $u['perms'] ?? [], true);}
function valid_iranian_national_code($v): bool {return $v === '1234567890';}
function csrf_field(): string {return '<input value="csrf">';}
class ExportDone extends Exception {}
function excel_download($file,$title,$headers,$rows,$options): never { $GLOBALS['exportHeaders']=$headers; $GLOBALS['exportRows']=$rows; throw new ExportDone(); }
require APP_ROOT.'/lib/tickets-sla.php';
require APP_ROOT.'/traffic-control.php';
function check(bool $condition, string $message): void {if (!$condition) throw new Exception($message); echo "PASS $message\n";}
$db=db();
$db->exec('CREATE TABLE users(id INTEGER PRIMARY KEY, full_name TEXT)');
$db->exec('CREATE TABLE traffic_destinations(id INTEGER PRIMARY KEY, title TEXT, approver_name TEXT, approver_name2 TEXT, approver_name3 TEXT, is_active INTEGER)');
$db->exec('CREATE TABLE traffic_visits(id INTEGER PRIMARY KEY, serial_no TEXT, visit_date TEXT, full_name TEXT, national_code TEXT, phone TEXT, company TEXT, entry_time TEXT, exit_time TEXT, no_visit INTEGER, with_car INTEGER, with_mobile INTEGER, is_notice INTEGER, meeting_with TEXT, destination_id INTEGER, approved_by TEXT, description TEXT, created_by INTEGER)');
$db->exec('CREATE TABLE traffic_visit_history (visit_id INTEGER, serial_no TEXT, visitor_name TEXT, action TEXT, changed_by INTEGER, before_json TEXT, after_json TEXT, changes_json TEXT)');
$db->exec("INSERT INTO users VALUES (7,'ثبت کننده')");
$db->exec("INSERT INTO traffic_destinations VALUES (1,'مقصد الف','تایید اول','تایید دوم',NULL,1),(2,'مقصد ب','تایید ب',NULL,NULL,1),(3,'غیرفعال','قدیم',NULL,NULL,0)");
$stmt=$db->prepare('INSERT INTO traffic_visits VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
for ($i=1;$i<=405;$i++) $stmt->execute([$i,'s'.$i,'2026-10-12',$i===1?'نام قدیمی':'نام '.$i,'1234567890','09120000000','شرکت','10:00:00',$i%2?'11:00:00':null,0,0,0,0,$i%2?'مقصد الف':'مقصد ب',$i%2?1:2,'تایید اول','شرح '.$i,7]);
$filter=['search'=>'نام قدیمی','range'=>'all','open_only'=>false];
$found=traffic_query_records($filter,101);
check(count($found)===1 && $found[0]['id']===1, 'history search reaches past first 300');
$filter=['search'=>'', 'range'=>'all','open_only'=>true];
check(count(traffic_query_records($filter,101))===101, 'open filter and next-page detection');
check(count(traffic_query_records($filter,101,'','',200))===2, 'offset retrieves remaining rows');
$filter['search']='تایید اول';check(count(traffic_query_records($filter,101))===101,'search includes approver');
$invalid=['full_name'=>'خاص','meeting_with'=>'مقصد الف','approved_by'=>'غیرمجاز']; $_POST=$invalid;
try {traffic_notice_create(['id'=>7]); throw new Exception('bad approver accepted');} catch (TrafficInputException $e) {check($e->field==='approved_by','special visit rejects unrelated approver');}
$_POST=['full_name'=>'خاص','meeting_with'=>'مقصد الف','approved_by'=>'تایید دوم'];
$id=traffic_notice_create(['id'=>7]); $record=traffic_fetch_record($id);
check($record['meeting_with']==='مقصد الف' && $record['approved_by']==='تایید دوم' && (int)$record['is_notice']===1 && (int)$record['destination_id']===1, 'special destination and chosen approver saved');
$input=['from_date'=>'1405/07/20','to_date'=>'1405/07/20','destinations'=>['مقصد الف'],'search'=>'خاص'];
$c=traffic_report_criteria($input);$preview=traffic_report_records($c,101);
check(count($preview)===1 && (int)$preview[0]['id']===$id, 'report date destination search returns special record');
$_GET=$input;try {traffic_export_report();} catch (ExportDone $e) {}
check(count($GLOBALS['exportRows'])===1 && $GLOBALS['exportRows'][0][6]==='مقصد الف' && $GLOBALS['exportRows'][0][7]==='تایید دوم','export shares criteria and preserves special destination');
$input['destinations']=['ناموجود'];try {traffic_report_criteria($input);throw new Exception('invalid destination accepted');} catch (InvalidArgumentException $e) {check(true,'unknown destination rejected');}
$inactive = traffic_report_criteria(['from_date'=>'1405/07/20','to_date'=>'1405/07/20','destinations'=>['3']]);check($inactive['destination_ids']===[3], 'inactive destination can be reported by id');
$db->exec("UPDATE traffic_destinations SET title='الف جدید' WHERE id=1");
$input=['from_date'=>'1405/07/20','to_date'=>'1405/07/20','destinations'=>['1'],'search'=>'خاص','fields_present'=>'1','fields'=>['name','meeting','approver']];
$renamed=traffic_report_records(traffic_report_criteria($input),101);
check(count($renamed)===1 && $renamed[0]['meeting_with']==='مقصد الف', 'destination id survives rename and preserves original historical text');
$_GET=$input;try {traffic_export_report();} catch (ExportDone $e) {}
check($GLOBALS['exportHeaders']===['نام و نام خانوادگی','مقصد ملاقات','تأییدکننده'] && $GLOBALS['exportRows'][0]===['تردد خاص: خاص','مقصد الف','تایید دوم'], 'selected fields and order shared by export');
try {traffic_report_selected_fields(['fields_present'=>'1']);throw new Exception('empty fields accepted');} catch (InvalidArgumentException $e) {check(true,'empty selection rejected');}
$input=['from_date'=>'1405/07/20','to_date'=>'1405/07/20','all_destinations'=>'1'];$c=traffic_report_criteria($input);
$p1=traffic_report_records($c,101,0);$p2=traffic_report_records($c,101,100);$_GET=$input;try {traffic_export_report();} catch (ExportDone $e) {}
check(count($GLOBALS['exportRows'])===406 && $p1[0]['serial_no']===$GLOBALS['exportRows'][0][0] && $p2[0]['serial_no']===$GLOBALS['exportRows'][100][0], 'preview pages and full export share ordering');
$html=traffic_history_rows_html([$record], ['perms'=>[]]);
check(str_contains($html,'مقصد الف') && !str_contains($html,'traffic-control-print') && !str_contains($html,'traffic_delete'), 'history escapes values and hides management actions for view-only');
$_POST=['record_id'=>$id,'full_name'=>'خاص ویرایش','national_code'=>'','phone'=>'','company'=>'','meeting_with'=>'مقصد الف','approved_by'=>'تایید دوم','visit_date'=>'2026-10-12','entry_time'=>'14:15','exit_time'=>''];
traffic_update_record(['id'=>7,'perms'=>['traffic.manage']]);$updated=traffic_fetch_record($id);
check((int)$updated['destination_id']===1 && $updated['meeting_with']==='مقصد الف', 'editing an old snapshot after rename keeps the linked id');
$_POST=['full_name'=>'عادی','national_code'=>'1234567890','phone'=>'09120000000','meeting_with'=>'الف جدید','approved_by'=>'تایید اول'];
$regular=traffic_create_record(['id'=>7]);
check((int) traffic_fetch_record($regular)['destination_id']===1, 'normal creation links the configured destination');
$db->beginTransaction();
$plan=['rows'=>[['action'=>'insert','serial_no'=>'import1','visit_date'=>'2026-10-12','full_name'=>'واردشده','national_code'=>'','phone'=>'','company'=>'','entry_time'=>'10:00:00','exit_time'=>null,'no_visit'=>0,'with_car'=>0,'with_mobile'=>0,'meeting_with'=>'الف جدید','approved_by'=>'تایید اول','description'=>'','is_notice'=>0]],'counts'=>['insert'=>1]];
traffic_import_apply_plan_in_transaction($plan,7,$db);$db->commit();
$imported=$db->query("SELECT * FROM traffic_visits WHERE serial_no='import1'")->fetch();
check((int)$imported['destination_id']===1, 'import links matching destination');
echo "ALL PASSED\n";
