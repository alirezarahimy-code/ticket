<?php
declare(strict_types=1);
function e($v): string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function jalali_date($d,$flag): string {return $d;}
function traffic_can_manage($user): bool{return false;}
$s=file_get_contents(__DIR__.'/../../../traffic-control.php');$a=strpos($s,'function traffic_history_rows_html(');$b=strpos($s,'function traffic_api_handle(', $a);eval(substr($s,$a,$b-$a));
$r=['serial_no'=>'3','visit_date'=>'2026-11-03','full_name'=>'نمونه','national_code'=>'111','company'=>'<شرکت آزمایش>','entry_time'=>'09:00','exit_time'=>null,'meeting_with'=>'الف','recorder_name'=>'ب'];
$html=traffic_history_rows_html([$r],[]);if(!str_contains($html,'&lt;شرکت آزمایش&gt;')||str_contains($html,'<شرکت آزمایش>')){echo "FAIL escaped company\n";exit(1);}echo "PASS escaped company and visible data\n";
$r['company']='';$html=traffic_history_rows_html([$r],[]);if(!str_contains($html,'<span>—</span>')){echo "FAIL blank company\n";exit(1);}echo "PASS empty company placeholder\nALL PASSED\n";
