<?php
declare(strict_types=1);
const APP_ROOT = __DIR__ . '/..';
function user_is_primary_admin(array $u): bool { return ($u['role']??'') === 'primary_admin' || (($u['role']??'') === 'admin' && (int) ($u['is_primary_admin'] ?? 0) === 1); }
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
// ───── مدیر واحدِ صرف (بدون handling_unit_code): مرزِ اختیار فقط چارت، هر دو حوزه ─────
$sole=['id'=>14,'role'=>'manager','permissions'=>['ticket.view_own','ticket.view_unit','ticket.edit','ticket.assign']];
$GLOBALS['roots'][14]=[2];
$itTicket=['requester_id'=>30,'service_group'=>'it'];
ok(ticket_user_can_view($itTicket,$sole),'sole unit head sees I.T. subtree request');
$supportTicket=['requester_id'=>30,'service_group'=>'support'];
ok(ticket_user_can_view($supportTicket,$sole),'sole unit head sees support subtree request too (no service_group filter)');
$outside=['requester_id'=>31,'service_group'=>'support'];
ok(!ticket_user_can_view($outside,$sole),'sole unit head cannot see unrelated branch');
$cross=['requester_id'=>32,'service_group'=>'it'];
ok(!ticket_user_can_view($cross,$sole),'sole unit head cannot see other-company subtree');
$assignedToHead=['requester_id'=>7,'service_group'=>'it','assigned_to'=>14];
ok(!ticket_user_can_view($assignedToHead,$sole),'ticket assigned to head but requested outside chart stays invisible');
[$headSql,$headArgs]=ticket_scope($sole);
ok(!str_contains($headSql,'service_group') && str_contains($headSql,'scope_user.org_unit_id IN') && $headArgs===[14,2,3,2,3,2,3],'sole unit head list spans both domains without service_group filter');
$headless=$sole;$headless['id']=15;$GLOBALS['roots'][15]=[];[$hSql,$hArgs]=ticket_scope($headless);
ok($hArgs===['support',15] && str_contains($hSql,'1 = 0'),'sole unit head without chart fails closed to legacy behaviour');
ok(ticket_handler_level($itTicket,$sole)==='assigner','sole unit head gets assigner level on subtree ticket');
ok(ticket_handler_level($outside,$sole)==='','sole unit head has no handler level outside chart');
$ownByHead=['requester_id'=>14,'service_group'=>'it','assigned_to'=>14];
ok(ticket_handler_level($ownByHead,$sole)==='','sole unit head never gets handler authority over own ticket');
// ───── مورد ۳: نقش جدید اولویت دارد؛ چارت دیگر مرز او نیست ─────
// مدیر تولید در چارت است ولی بعداً «ادمین اصلی» (admin + flag) شده است.
$promoted=['id'=>16,'role'=>'admin','is_primary_admin'=>1,'permissions'=>['ticket.view_own','ticket.view_unit','ticket.assign']];
$GLOBALS['roots'][16]=[2];
ok(!ticket_sole_unit_head($promoted),'promoted chart manager is not a sole unit head');
ok(ticket_user_can_view($outside,$promoted),'promoted chart manager sees the whole system like a primary admin');
[$promotedSql,$promotedArgs]=ticket_scope($promoted);
ok($promotedSql==='' && $promotedArgs===[],'promoted chart manager scope is unrestricted');
ok(ticket_handler_level($itTicket,$promoted)==='manager','promoted chart manager keeps full manager authority, not assigner');
// همان مدیر، ولی با نقش اصلی primary_admin به‌جای پرچم.
$promotedRole=['id'=>17,'role'=>'primary_admin','permissions'=>['ticket.view_own','ticket.view_unit']];
ok(!ticket_sole_unit_head($promotedRole),'primary_admin role is never a sole unit head');
[$prSql,$prArgs]=ticket_scope($promotedRole);
ok($prSql==='' && $prArgs===[],'primary_admin role scope is unrestricted');
// رئیس چارت که فقط دسترسی موردی (مثلا از گروه‌ها) گرفته: نقش عوض نشده، پس مدیر صرف می‌ماند.
// اولویت با خودِ نقش است، نه پرمیشن‌های گرنت‌شده.
$granted=['id'=>18,'role'=>'manager','permissions'=>['ticket.view_own','ticket.view_unit','ticket.assign','supervisor.panel','ticket.view_all','supervisor.decide']];
$GLOBALS['roots'][18]=[2];
ok(ticket_sole_unit_head($granted),'chart head with case-granted permissions stays a sole unit head');
// اگر نقش واقعاً supervisor باشد، اولویت با نقش است و مدیر صرف نیست.
$roleSupervisor=['id'=>19,'role'=>'supervisor','permissions'=>['ticket.view_own','ticket.view_unit']];
ok(!ticket_sole_unit_head($roleSupervisor),'supervisor role is not a sole unit head');
ok(!ticket_can_see_internal($itTicket,$sole),'sole unit head cannot read internal notes');
ok(!ticket_reply_starts_work(['requester_id'=>30,'service_group'=>'it','status'=>'assigned'],$sole),'sole unit head reply never silently starts work');
echo "ALL PASSED\n";
