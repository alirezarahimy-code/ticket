<?php
declare(strict_types=1);
define('APP_ROOT', sys_get_temp_dir());
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function db(): never { throw new RuntimeException('simulated database outage'); }
function logging_emergency_failure(string $key, string $detail): void {}
function logging_spool_stats(): array { return ['files'=>3,'processing_files'=>1,'rewrite_temp_files'=>0,'bytes'=>4096,'dead_letter_files'=>2,'dead_letter_bytes'=>1024,'state'=>'known']; }
function system_health_checks(): array { return [['اتصال دیتابیس','fail','قطع آزمایشی']]; }
require dirname(__DIR__) . '/logging-dashboard.php';
$points=[];
for($i=0;$i<24;$i++)$points[]=['label'=>sprintf('%02d',$i),'errors'=>$i===20?4:0,'warnings'=>$i===21?2:0];
$svg=logging_dashboard_svg_trend($points);
$data=logging_dashboard_fetch(7);
$checks=[
    'SVG chart is generated locally'=>str_contains($svg,'<svg') && str_contains($svg,'viewBox="0 0 760 220"'),
    'error and warning series are distinguished'=>str_contains($svg,'ld-bar-error') && str_contains($svg,'ld-bar-warning'),
    'chart has accessible description'=>str_contains($svg,'aria-label="روند ساعتی'),
    'hour labels are rendered'=>str_contains($svg,'>00</text>') && str_contains($svg,'>23</text>'),
    'local emergency spool remains visible during DB outage'=>($data['database']==='unknown' && ($data['spool']['files']??0)===3 && ($data['spool']['dead_letter_files']??0)===2),
    'DB-dependent metrics remain unknown during outage'=>($data['issues']===null && $data['notifications']===null && !isset($data['summary']['total_24h'])),
];
$failed=0; foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if(!$ok)$failed++;}
echo (count($checks)-$failed).'/'.count($checks).' dashboard offline tests passed.'.PHP_EOL; exit($failed?1:0);
