<?php
declare(strict_types=1);
const APP_ROOT=__DIR__.'/..';
class DoneRedirect extends Exception {}
class SortStmt {
 public function __construct(public string $sql){}
 public function execute(array $p):bool { if(str_starts_with($this->sql,'UPDATE org_units')) $GLOBALS['updates'][]=$p;return true; }
 public function fetchAll(int $mode=0):array {return [2,3,4];}
}
class SortDB {
 public bool $tx=false; public bool $rolled=false; public bool $committed=false;
 public function beginTransaction():bool{$this->tx=true;return true;}
 public function inTransaction():bool{return $this->tx;}
 public function commit():bool{$this->tx=false;$this->committed=true;return true;}
 public function rollBack():bool{$this->tx=false;$this->rolled=true;$GLOBALS['updates']=[];return true;}
 public function prepare(string $s):SortStmt{return new SortStmt($s);}
}
function db():SortDB{return $GLOBALS['db'];}
function org_has_company_schema():bool{return true;} function org_company_context_id($u,$i):int{return 1;}
function org_company_root_title($id):string{return 'root';} function post_value($k,$d=''){return $_POST[$k]??$d;}
function org_unit_by_id($id):array{return ['company_id'=>$id===9?2:1];}
function require_permission($p):array{return ['id'=>1];} function user_can_manage_organization($u):bool{return $GLOBALS['allowed'];}
function org_has_sort_order():bool{return true;} function org_ceo_unit_id($id):int{return 1;}
function org_snapshot($id):array{return [];} function org_record_undo(...$v):void{} function save_audit(...$v):void{} function save_audit_required(...$v):void{} function flash(...$v):void{}
function org_company_redirect_url($id):string{return '/';} function redirect($url):void{throw new DoneRedirect();}
function runSort(array $ids,bool $allowed=true):string {
 $GLOBALS['db']=new SortDB();$GLOBALS['updates']=[];$GLOBALS['allowed']=$allowed;$_SESSION=[];$_POST=['unit_ids'=>$ids];$action='sort_org_root_units';$user=['id'=>1];
 try {require __DIR__.'/../actions/org-actions.php';}catch(DoneRedirect $e){return 'ok';}catch(Throwable $e){return 'error';}return 'missing';
}
function chk(bool $v,string $m):void {if(!$v) throw new RuntimeException($m);echo "PASS $m\n";}
chk(runSort([3,2,4])==='ok' && $GLOBALS['updates']===[[10,3,1,1],[20,2,1,1],[30,4,1,1]] && $GLOBALS['db']->committed,'mixed root sorting persists order, not parentage');
chk(runSort([3,2])==='error' && $GLOBALS['db']->rolled && !$GLOBALS['updates'],'missing id rejected atomically');
chk(runSort([3,2,2])==='error' && $GLOBALS['db']->rolled && !$GLOBALS['updates'],'duplicate id rejected atomically');
chk(runSort([3,2,9])==='error' && !$GLOBALS['updates'],'cross-company id rejected');
chk(runSort([3,2,4],false)==='error' && !$GLOBALS['updates'],'permission enforced');
echo "ALL PASSED\n";
