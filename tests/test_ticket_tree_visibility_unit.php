<?php
declare(strict_types=1);
const APP_ROOT = __DIR__ . '/..';
function user_is_primary_admin(array $u): bool { return ($u['role']??'') === 'primary_admin'; }
function is_global_ticket_role(?string $r): bool { return in_array($r,['supervisor','inspector','primary_admin'],true); }
function is_it_agent(array $u): bool { return false; }
function user_can(array $u,string $p): bool { return in_array($p,$u['permissions']??[],true); }
class ScopeStmt {
    public array $params=[];
    public function __construct(public string $sql) {}
    public function execute(array $p): void {$this->params=$p;}
    public function fetchAll(?int $mode=null): array {return $GLOBALS['roots'][$this->params[0]]??[];}
    public function fetchColumn(): mixed {
        $target=$this->params[0]; $scope=array_slice($this->params,1); $unit=$GLOBALS['requesters'][$target]??0;
        return in_array($unit,$scope,true) ? 1 : 0;
    }
}
class ScopeDB {
    public function prepare(string $sql): ScopeStmt { return new ScopeStmt($sql); }
    public function query(string $sql): object {return new class {public function fetchAll(?int $mode=null):array{return $GLOBALS['units'];}};}
}
function db(): ScopeDB { static $db; return $db??=new ScopeDB(); }
require __DIR__.'/../lib/auth-scope.php';
function ok(bool $v,string $m):void {if (!$v) throw new RuntimeException($m);echo "PASS $m\n";}
$GLOBALS['units']=[['id'=>1,'parent_id'=>0,'company_id'=>1],['id'=>2,'parent_id'=>1,'company_id'=>1],['id'=>3,'parent_id'=>2,'company_id'=>1],['id'=>4,'parent_id'=>1,'company_id'=>1],['id'=>5,'parent_id'=>2,'company_id'=>2]];
$GLOBALS['roots']=[11=>[2],12=>[1],13=>[],14=>[4]];
$GLOBALS['requesters']=[30=>3,31=>4,32=>5];
$manager=['id'=>11,'role'=>'manager','handling_unit_code'=>'support','permissions'=>['ticket.view_own','ticket.view_unit']];
$ticket=['requester_id'=>30,'service_group'=>'support'];
ok(ticket_user_can_view($ticket,$manager),'manager sees descendant');
$ticket['requester_id']=31;ok(!ticket_user_can_view($ticket,$manager),'manager cannot see unrelated branch');
$ticket['requester_id']=32;ok(!ticket_user_can_view($ticket,$manager),'corrupt cross-company child excluded');
$ticket['requester_id']=11;ok(ticket_user_can_view($ticket,$manager),'manager sees own request');
$ticket['requester_id']=30;$ticket['service_group']='it';ok(!ticket_user_can_view($ticket,$manager),'service group respected');
[$sql,$args]=ticket_scope($manager);ok(str_contains($sql,'scope_user.org_unit_id IN') && $args===['support',11,2,3,2,3,2,3],'list uses same subtree and group as detail');
$missing=$manager;$missing['id']=13;[$sql,$args]=ticket_scope($missing);ok(str_contains($sql,'1 = 0') && $args===['support',13], 'manager without chart cannot see entire support pool');
$ticket=['requester_id'=>31,'service_group'=>'support'];ok(!ticket_user_can_view($ticket,$missing),'missing manager chart fails closed on detail');
$ordinary=['id'=>20,'role'=>'user','permissions'=>['ticket.view_own']];ok(!ticket_user_can_view($ticket,$ordinary),'ordinary user cannot see another requester');[$sql,$args]=ticket_scope($ordinary);ok($args===[20] && str_contains($sql,'requester_id'), 'ordinary user list own only');
$operator=['id'=>21,'role'=>'agent','handling_unit_code'=>'support','permissions'=>['ticket.view_unit']];ok(ticket_user_can_view($ticket,$operator),'support handler retains group visibility');[$sql,$args]=ticket_scope($operator);ok($args===['support'],'handler list limited to support');
echo "ALL PASSED\n";
