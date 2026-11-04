<?php
declare(strict_types=1);
define('APP_ROOT', dirname(__DIR__));
require APP_ROOT.'/lib/logging-core.php';
require APP_ROOT.'/lib/logging-diagnostics.php';
$checks=[];
$run=static function(string $name,bool $ok) use (&$checks): void {$checks[]=$name.': '.($ok?'PASS':'FAIL');};
$db=logging_diagnostic_analyze(['code'=>'SYS-DB-001','category'=>'system','level'=>'error','request_id'=>'req-123','trace_id'=>'trace-123','context'=>'database','meta'=>['sql_state'=>'HY000','password'=>'bad-secret','table'=>'users']]);
$run('known DB rule is deterministic',$db['known_rule'] && str_contains($db['probable_cause'],'پایگاه داده'));
$run('sensitive metadata is excluded',!str_contains(json_encode($db,JSON_UNESCAPED_UNICODE),'bad-secret'));
$run('evidence is attached',count($db['evidence'])>=3);
$unknown=logging_diagnostic_analyze(['code'=>'SYS-UNKNOWN-999','category'=>'system','level'=>'error']);
$run('unknown rule remains low confidence',!$unknown['known_rule'] && $unknown['confidence']==='low');
$redacted=logging_diagnostic_redact_event(['event_id'=>'e1','message'=>'failed user@example.com password=abc','meta_json'=>'{"token":"secret","line":42,"file":"/srv/app/a.php","stack_trace":"#0 /srv/app/a.php(42): DemoRun()"}']);
$run('agent redaction removes email and secrets',!str_contains(json_encode($redacted),'user@example.com') && !str_contains(json_encode($redacted),'secret'));
$run('agent metadata strips absolute file paths',($redacted['meta']['file'] ?? '') === 'a.php');
$run('stack trace is reduced to basename and line',($redacted['stack_frames'][0]['file'] ?? '') === 'a.php' && ($redacted['stack_frames'][0]['line'] ?? 0) === 42);
$sqlSafe=logging_diagnostic_safe_value('Query failed: SELECT * FROM users WHERE password=secret; at /srv/app/private.php');
$run('SQL and absolute paths are redacted',!str_contains($sqlSafe,'SELECT * FROM') && !str_contains($sqlSafe,'/srv/app'));
$similar=logging_diagnostic_similarity(['fingerprint'=>'a','code'=>'SYS-DB-001','category'=>'system','context'=>'db','title'=>'DB error'],[
 ['fingerprint'=>'b','code'=>'SYS-DB-001','category'=>'system','context'=>'db','title'=>'DB error','occurrences'=>4,'status'=>'open'],
 ['fingerprint'=>'c','code'=>'SYS-PHP-001','category'=>'system','context'=>'php','title'=>'PHP error','occurrences'=>2,'status'=>'open']],5);
$run('similar issues rank exact stable code first',count($similar)===1 && $similar[0]['fingerprint']==='b' && $similar[0]['score']===100);
foreach($checks as $line) echo $line.PHP_EOL;
$failed=count(array_filter($checks,static fn($x)=>str_ends_with($x,'FAIL')));
echo sprintf("%d/%d passed\n",count($checks)-$failed,count($checks)); exit($failed?1:0);
