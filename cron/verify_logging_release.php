<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit("Not found\n"); }
require dirname(__DIR__) . '/bootstrap.php';
$checks=[];
$add=static function(string $name,string $state,string $detail) use (&$checks): void {$checks[]=['name'=>$name,'state'=>$state,'detail'=>$detail];};
$add('php_version',version_compare(PHP_VERSION,'8.1.0','>=')?'ok':'fail',PHP_VERSION);
$add('pdo_mysql',extension_loaded('pdo_mysql')?'ok':'fail',extension_loaded('pdo_mysql')?'extension loaded':'pdo_mysql missing');
$add('zip_archive',class_exists('ZipArchive')?'ok':'warn',class_exists('ZipArchive')?'ZIP support available':'ZIP support missing; Agent ZIP export unavailable');
try { db()->query('SELECT 1'); $add('database','ok','SELECT 1 succeeded'); } catch(Throwable $e) { $add('database','fail','Database check failed: '.get_class($e)); }
$tables=['system_logs','activity_logs','ticket_events','log_issues','log_issue_history','log_issue_events','log_issue_notifications','logging_health_alerts','notifications'];
foreach($tables as $table) { try { $ok=db_table_exists($table); $add('table_'.$table,$ok?'ok':'fail',$ok?'present':'missing'); } catch(Throwable $e) {$add('table_'.$table,'fail','check failed: '.get_class($e));} }
foreach(['system_logs'=>['event_id','request_id','trace_id','operation_id','parent_event_id','code','category','meta_json'],'activity_logs'=>['event_id','request_id','trace_id','operation_id','parent_event_id'],'ticket_events'=>['event_id','request_id','trace_id','operation_id','parent_event_id'],'notifications'=>['idempotency_key']] as $table=>$columns) foreach($columns as $column) { try {$ok=db_column_present($table,$column);$add('column_'.$table.'_'.$column,$ok?'ok':'fail',$ok?'present':'missing');}catch(Throwable $e){$add('column_'.$table.'_'.$column,'fail','check failed: '.get_class($e));} }
$storage=APP_ROOT.'/storage/logs'; $add('storage_logs_dir',is_dir($storage)?'ok':'fail',is_dir($storage)?'present':'missing'); $add('storage_logs_writable',is_dir($storage)&&is_writable($storage)?'ok':'fail',is_dir($storage)&&is_writable($storage)?'writable':'not writable');
$pendingUpdateAudit=glob(APP_ROOT.'/storage/backups/*/UPDATE-AUDIT-PENDING.json')?:[]; $add('update_audit_reconciliation',$pendingUpdateAudit===[]?'ok':'fail',$pendingUpdateAudit===[]?'no pending update-audit marker':count($pendingUpdateAudit).' backup(s) require completion-audit reconciliation');
try { $deleteRule=(string)db()->query("SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='ticket_events' LIMIT 1")->fetchColumn(); $add('ticket_event_delete_policy',strtoupper($deleteRule)==='RESTRICT'?'ok':'fail','DELETE_RULE='.$deleteRule); } catch(Throwable $e) { $add('ticket_event_delete_policy','fail','could not verify RESTRICT foreign key'); }
$integrityKey=(string)getenv('LOGGING_INTEGRITY_KEY'); $add('integrity_key',strlen($integrityKey)>=32?'ok':'fail',strlen($integrityKey)>=32?'configured (value hidden)':'missing or shorter than 32 bytes');
$integrityBackup=(string)getenv('LOGGING_INTEGRITY_BACKUP_DIR'); $integrityBackupReal=$integrityBackup!==''?realpath($integrityBackup):false; $appReal=realpath(APP_ROOT); $backupOutside=is_string($integrityBackupReal)&&is_string($appReal)&&strpos($integrityBackupReal.DIRECTORY_SEPARATOR,$appReal.DIRECTORY_SEPARATOR)!==0; $add('integrity_backup_directory',$backupOutside&&is_writable($integrityBackupReal)?'ok':'fail',$backupOutside&&is_writable($integrityBackupReal)?'writable and outside application root':'missing, unwritable or inside application root');
$spool=logging_spool_stats(); $add('spool_state',in_array(($spool['state']??'unknown'),['ok','empty'],true)?'ok':'warn','state='.($spool['state']??'unknown').'; files='.(int)($spool['files']??0).'; bytes='.(int)($spool['bytes']??0).'; dead_letter='.(int)($spool['dead_letter_files']??0));
$workerPath=$storage.'/logging-worker-status.json'; $worker=is_file($workerPath)?json_decode((string)@file_get_contents($workerPath),true):null; $workerAt=is_array($worker)?strtotime((string)($worker['last_success_at']??'')):false; $add('worker_last_success',$workerAt!==false&&$workerAt>=time()-900?'ok':'warn',$workerAt!==false?date(DATE_ATOM,$workerAt):'no recent success timestamp');
$failed=count(array_filter($checks,static fn($c)=>$c['state']==='fail')); $warnings=count(array_filter($checks,static fn($c)=>$c['state']==='warn'));
$report=['schema'=>'logging-release-preflight-v1','generated_at'=>date(DATE_ATOM),'overall'=>$failed?'fail':($warnings?'warning':'ok'),'failed'=>$failed,'warnings'=>$warnings,'checks'=>$checks];
$out=APP_ROOT.'/storage/logs/logging-release-preflight.json'; @file_put_contents($out,json_encode($report,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE)); if(is_file($out)) @chmod($out,0600);
fwrite(STDOUT,json_encode($report,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE).PHP_EOL);
exit($failed?1:0);
