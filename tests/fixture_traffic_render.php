<?php
declare(strict_types=1);
const APP_ROOT=__DIR__.'/..';
$GLOBALS['pdo']=new PDO('sqlite::memory:'); $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $GLOBALS['pdo']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$GLOBALS['pdo']->exec('CREATE TABLE traffic_destinations (id INTEGER PRIMARY KEY, title TEXT, approver_name TEXT, approver_name2 TEXT, approver_name3 TEXT, is_active INTEGER)');
$GLOBALS['pdo']->exec("INSERT INTO traffic_destinations VALUES(1,'الف','اول','دوم',NULL,1)");
function db():PDO{return $GLOBALS['pdo'];}
function user_can($u,$p):bool{return true;}
function valid_choice($v,$values,$fallback){return in_array($v,$values,true)?$v:$fallback;}
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf_field():string{return '<input type="hidden" name="csrf" value="x">';}
function csp_nonce():string{return 'test';}
function render_header($title,$user):void{echo '<!doctype html><html lang="fa"><body>';}
function render_footer():void{echo '</body></html>';}
function flash(...$v):void{}
require APP_ROOT.'/lib/tickets-sla.php';require APP_ROOT.'/traffic-control.php';
$_GET=['tab'=>'new'];$_SESSION=[];
traffic_render_page(['id'=>1,'role'=>'admin']);
