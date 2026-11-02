<?php
declare(strict_types=1);
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf_field():string{return '<input type="hidden" name="csrf" value="x">';}
function cd_dvd_can_manage_record($record,$user):bool{return true;}
$tabUrl=fn($tab,$args=[])=>'#';$user=['id'=>1];
$base=['media'=>'CD','serial'=>'P86-0001','id'=>1,'info_type'=>'سایر','info_desc'=>str_repeat('اطلاعات بسیار طولانی ',30),'date_str'=>'1405/07/01','recorder_display_name'=>'نام طولانی ثبت کننده','recorder_name'=>'نام'];
$records=[array_merge($base,['direction'=>'OUT','exit_sheet'=>'شماره 123']),array_merge($base,['id'=>2,'direction'=>'IN','exit_sheet'=>'قدیمی نباید دیده شود']),array_merge($base,['id'=>3,'direction'=>'OUT','exit_sheet'=>''])];
$source=file_get_contents(__DIR__.'/../cd-dvd.php');$start=strpos($source,'<div class="cd-dvd-record-table"><div class="cd-dvd-record-head">',strpos($source,'function cd_dvd_render_page(array'));
$end=strpos($source,'<?php endif; ?></div>',$start)+strlen('<?php endif; ?></div>');
if (!$start || !$end) throw new Exception('CD table not found');
echo '<!doctype html><html dir="rtl"><body><section id="cd-dvd-history">';eval('?>'.substr($source,$start,$end-$start));echo '</section></body></html>';
