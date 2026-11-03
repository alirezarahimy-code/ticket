<?php
declare(strict_types=1);
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function valid_choice($v,$options,$default){return in_array($v,$options,true)?$v:$default;}
function cd_dvd_can_submit_out($u){return false;} function cd_dvd_can_register_in($u){return false;}
function cd_dvd_query_records($u,$f=[]){$row=['media'=>'CD','serial'=>'A','info_type'=>'سایر','date_str'=>'1405/07/01','recorder_display_name'=>'الف','recorder_name'=>'الف'];return [array_merge($row,['direction'=>'OUT','exit_sheet'=>'999']),array_merge($row,['direction'=>'IN','exit_sheet'=>'مخفی']),array_merge($row,['direction'=>'OUT','exit_sheet'=>''])];}
function cd_dvd_stats($u){return ['total'=>3,'cd'=>3,'dvd'=>0,'incoming'=>1,'outgoing'=>2,'outside'=>2];}
$source=file_get_contents(__DIR__.'/../cd-dvd.php');$a=strpos($source,'function cd_dvd_render_surface(');$b=strpos($source,'function cd_dvd_render_page_legacy(',$a);
eval(substr($source,$a,$b-$a));echo '<!doctype html><html dir="rtl"><body>'.cd_dvd_render_surface(['id'=>1],'index.php?page=cd-dvd').'</body></html>';
