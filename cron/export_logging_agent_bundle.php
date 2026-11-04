<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit("Not found\n"); }
require dirname(__DIR__) . '/bootstrap.php';

$fingerprint = (string)($argv[1] ?? '');
if ($fingerprint !== '' && !preg_match('/^[a-f0-9]{64}$/i', $fingerprint)) { fwrite(STDERR,"Fingerprint must be 64 hex characters.\n"); exit(2); }
try {
    if ($fingerprint === '') {
        $issue = db()->query("SELECT * FROM log_issues WHERE status <> 'resolved' ORDER BY FIELD(severity,'critical','error','warning'), last_seen DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt=db()->prepare('SELECT * FROM log_issues WHERE fingerprint=? LIMIT 1'); $stmt->execute([$fingerprint]); $issue=$stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$issue) { fwrite(STDERR,"Issue not found.\n"); exit(3); }
    $stmt=db()->prepare("SELECT s.event_id,s.request_id,s.trace_id,s.operation_id,s.parent_event_id,s.code,s.category,s.level,s.context,s.message,s.meta_json,s.created_at
        FROM log_issue_events e JOIN system_logs s ON s.event_id=e.event_id WHERE e.fingerprint=? ORDER BY s.created_at DESC LIMIT 100");
    $stmt->execute([$issue['fingerprint']]); $rawEvents=$stmt->fetchAll(PDO::FETCH_ASSOC);
    $events=array_map('logging_diagnostic_redact_event',$rawEvents);
    $analysis= logging_diagnostic_analyze($rawEvents[0] ?? ['code'=>$issue['code'],'category'=>$issue['category'],'level'=>$issue['severity'],'context'=>$issue['context'],'created_at'=>$issue['last_seen'],'request_id'=>$issue['last_request_id']]);
    $similarStmt=db()->prepare("SELECT fingerprint,code,category,context,title,occurrences,status FROM log_issues WHERE code=? AND fingerprint<>? ORDER BY occurrences DESC,last_seen DESC LIMIT 30");
    $similarStmt->execute([$issue['code'],$issue['fingerprint']]); $similar=logging_diagnostic_similarity($issue,$similarStmt->fetchAll(PDO::FETCH_ASSOC),10);
    $spool=logging_spool_stats(); $counters=logging_health_counters();
    $workerPath=APP_ROOT.'/storage/logs/logging-worker-status.json'; $worker=is_file($workerPath)?json_decode((string)@file_get_contents($workerPath),true):[];
    $dbState='connected'; try { db()->query('SELECT 1'); } catch(Throwable) { $dbState='unknown'; }
    $report=[
      'schema'=>'samaneh-m-logging-agent-bundle-v1','generated_at'=>date(DATE_ATOM),'offline_engine'=>true,
      'application'=>['version'=>trim((string)@file_get_contents(APP_ROOT.'/VERSION')),'php_version'=>PHP_VERSION,'sapi'=>PHP_SAPI],
      'issue'=>['fingerprint'=>(string)$issue['fingerprint'],'code'=>(string)$issue['code'],'category'=>(string)$issue['category'],'context'=>logging_diagnostic_safe_value((string)$issue['context']),'severity'=>(string)$issue['severity'],'title'=>logging_diagnostic_safe_value((string)$issue['title']),'first_seen'=>(string)$issue['first_seen'],'last_seen'=>(string)$issue['last_seen'],'occurrences'=>(int)$issue['occurrences'],'status'=>(string)$issue['status'],'last_request_id'=>(string)($issue['last_request_id']??'')],
      'diagnosis'=>$analysis,'similar_issues'=>$similar,'events'=>$events,
      'runtime'=>['database'=>$dbState,'spool'=>array_intersect_key($spool,array_flip(['state','files','bytes','age_seconds','dead_letter_files','dead_letter_bytes','rewrite_temp_files'])),'counters'=>array_intersect_key($counters,array_flip(['state','written','dropped','dropped_last_at'])),'worker'=>is_array($worker)?array_intersect_key($worker,array_flip(['last_attempt_at','last_success_at','last_status','processed','failed'])):[]],
      'privacy'=>['redacted'=>true,'excluded'=>['passwords','tokens','cookies','authorization headers','email addresses','phone numbers','user names and IDs','raw SQL and request/response bodies','absolute server paths']]
    ];
    $outDir=APP_ROOT.'/storage/logs/agent-bundles'; if (!is_dir($outDir) && !@mkdir($outDir,0700,true) && !is_dir($outDir)) throw new RuntimeException('Cannot create private bundle directory');
    @chmod($outDir,0700);
    $stamp=date('Ymd-His'); $base='logging-agent-'.$stamp.'-'.substr((string)$issue['fingerprint'],0,10); $temp=$outDir.'/'.$base.'.tmp';
    if (!is_dir($temp) && !mkdir($temp,0700,true)) throw new RuntimeException('Cannot create temporary bundle directory');
    $json=json_encode($report,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
    $md="# بسته تشخیصی لاگ سامانه\n\n- زمان تولید: {$report['generated_at']}\n- نسخه: {$report['application']['version']}\n- کد رخداد: {$report['issue']['code']}\n- عنوان: {$report['issue']['title']}\n- تعداد تکرار: {$report['issue']['occurrences']}\n- اطمینان تشخیص: {$analysis['confidence_label']}\n\n## علت احتمالی\n\n{$analysis['probable_cause']}\n\n## شواهد\n\n";
    foreach($analysis['evidence'] as $ev) $md.='- '.$ev['label'].': '.$ev['value']."\n";
    $md.="\n## بررسی‌های پیشنهادی\n\n"; foreach($analysis['checks'] as $check) $md.='- '.$check."\n";
    $md.="\n## اقدام پیشنهادی\n\n{$analysis['recommended_fix']}\n\n## حریم خصوصی\n\nمقادیر حساس، هویت کاربران و بدنه درخواست‌ها در این بسته حذف یا پالایش شده‌اند.\n";
    $html='<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>بسته تشخیصی</title><style>body{font:15px/2 Tahoma,Arial;max-width:980px;margin:32px auto;padding:0 20px;color:#202938}section{border:1px solid #ddd;border-radius:12px;padding:18px;margin:16px 0}code{direction:ltr;unicode-bidi:embed}</style><h1>بسته تشخیصی لاگ سامانه</h1><section><b>'.htmlspecialchars($report['issue']['code'],ENT_QUOTES,'UTF-8').'</b><p>'.htmlspecialchars($report['issue']['title'],ENT_QUOTES,'UTF-8').'</p><p>سطح اطمینان: '.htmlspecialchars($analysis['confidence_label'],ENT_QUOTES,'UTF-8').'</p></section><section><h2>علت احتمالی</h2><p>'.htmlspecialchars($analysis['probable_cause'],ENT_QUOTES,'UTF-8').'</p><h2>اقدام پیشنهادی</h2><p>'.htmlspecialchars($analysis['recommended_fix'],ENT_QUOTES,'UTF-8').'</p></section><section><h2>شواهد</h2><ul>';
    foreach($analysis['evidence'] as $ev) $html.='<li>'.htmlspecialchars($ev['label'].': '.$ev['value'],ENT_QUOTES,'UTF-8').'</li>';
    $html.='</ul></section><section><h2>رخدادهای پالایش‌شده</h2><pre>'.htmlspecialchars(json_encode($events,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE),ENT_QUOTES,'UTF-8').'</pre></section></html>';
    file_put_contents($temp.'/report.json',$json,LOCK_EX); file_put_contents($temp.'/report.md',$md,LOCK_EX); file_put_contents($temp.'/report.html',$html,LOCK_EX);
    foreach(glob($temp.'/*')?:[] as $file) @chmod($file,0600);
    if (!class_exists('ZipArchive')) throw new RuntimeException('PHP ZipArchive extension is required to create the ZIP bundle; JSON/MD/HTML were retained in the private temporary folder.');
    $zipPath=$outDir.'/'.$base.'.zip'; $zip=new ZipArchive();
    if($zip->open($zipPath,ZipArchive::CREATE|ZipArchive::EXCL)!==true) throw new RuntimeException('Cannot create ZIP bundle');
    foreach(['report.json','report.md','report.html'] as $name) $zip->addFile($temp.'/'.$name,$name);
    $zip->close(); @chmod($zipPath,0600);
    foreach(glob($temp.'/*')?:[] as $file) @unlink($file); @rmdir($temp);
    fwrite(STDOUT,"Bundle created: storage/logs/agent-bundles/".$base.".zip\nSHA-256: ".hash_file('sha256',$zipPath)."\n");
} catch(Throwable $e) { logging_emergency_failure('logging_agent_bundle_failed',get_class($e)); fwrite(STDERR,'Agent bundle failed: '.get_class($e)."\n"); exit(4); }
