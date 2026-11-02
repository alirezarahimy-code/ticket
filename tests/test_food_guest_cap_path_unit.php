<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__.'/..');
function db_table_exists(string $table): bool { return $table==='food_ticket_guest_cards'; }
function app_error_log(string $s): void {}
function user_can(array $user, string $permission): bool { return in_array($permission, $user['permissions'] ?? [], true); }
class CapStatement implements IteratorAggregate {
    public array $args=[];
    public function __construct(public string $sql) {}
    public function execute(?array $p=null): bool { $this->args=$p??[]; if(str_contains($this->sql,'INSERT INTO settings')) $GLOBALS['dbsettings'][$p[0]]=$p[1];return true; }
    public function getIterator(): Traversable { $rows=[];foreach($GLOBALS['dbsettings'] as $k=>$v)$rows[]=['key'=>$k,'value'=>$v];return new ArrayIterator($rows); }
    public function fetchColumn(int $n=0): mixed {
        if(str_contains($this->sql,'SUM(guest_count)')) { if(!empty($GLOBALS['unavailable']))throw new RuntimeException('db error');return $GLOBALS['requested']; }
        if(str_contains($this->sql,'FROM settings'))return $GLOBALS['dbsettings'][$this->args[0]]??false;
        if(str_contains($this->sql,'food_ticket_guest_cards'))return 1;
        if(str_contains($this->sql,'food_ticket_events'))return str_contains($this->sql,'source_card = ?')?$GLOBALS['card_issued']:$GLOBALS['issued'];
        return false;
    }
    public function fetch(int $mode=0): array|false { if(str_contains($this->sql,'FROM traffic_visits'))return ['entries'=>2,'early_exits'=>0];return false; }
    public function fetchAll(int $mode=0): array { if(str_contains($this->sql,'SELECT id, card_uid, guest_name, daily_limit'))return [['id'=>1,'card_uid'=>'CARDXYZ','guest_name'=>'مهمان','daily_limit'=>$GLOBALS['card_limit']]];return []; }
}
class CapDatabase { public function prepare(string $sql): CapStatement{return new CapStatement($sql);} public function query(string $sql): CapStatement{$s=new CapStatement($sql);$s->execute();return $s;} public function exec(string $sql): int{return 0;} }
function db(): CapDatabase { static $db;return $db??=new CapDatabase(); }
require __DIR__.'/../lib/settings-effects.php';
require __DIR__.'/../food-ticket.php';
function chk(bool $x,string $m): void {if(!$x){echo "FAIL $m\n";exit(1);}echo "PASS $m\n";}
$_SERVER['REQUEST_METHOD']='POST';
chk(!food_ticket_route_allowed(['permissions'=>['food.guest']], 'guest-cap') && food_ticket_route_allowed(['permissions'=>['food.guest_edit']], 'guest-cap'),'only guest editors can save cap mode');
$GLOBALS['dbsettings']=['food_guest_cap_mode'=>'variable'];$GLOBALS['requested']=12;$GLOBALS['issued']=3;$GLOBALS['card_issued']=3;$GLOBALS['card_limit']=0;
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Tehran')))->format('Y-m-d');$item=['date_raw'=>$today,'time_raw'=>'12:30:00','uid'=>'','card'=>'CARDXYZ','row'=>[]];$config=['guest_card_uids'=>'CARDXYZ','max_guest_daily'=>5];$map=[];
chk(food_ticket_decide_punch($item,$config,$map)['event_type']==='guest_limit','traffic mode blocks when 3 already issued and traffic cap 2');
chk(food_ticket_save_guest_cap_mode('requested')==='requested' && $GLOBALS['dbsettings']['food_guest_cap_mode']==='requested','mode is persisted and read back');
chk(food_ticket_decide_punch($item,$config,$map)['event_type']==='guest','request cap 12 overrides traffic cap 2 and fallback 5');
$GLOBALS['issued']=11;chk(food_ticket_decide_punch($item,$config,$map)['event_type']==='guest','12th ticket permitted');
$GLOBALS['issued']=12;chk(food_ticket_decide_punch($item,$config,$map)['event_type']==='guest_limit','13th ticket blocked');
$GLOBALS['issued']=3;$GLOBALS['card_limit']=3;$d=food_ticket_decide_punch($item,$config,$map);chk($d['event_type']==='guest_limit' && str_contains($d['payload']['last_error'],'سقف مستقل این کارت') && str_contains($d['payload']['last_error'],'جمع درخواست مهمان'),'configured card limit is separate and reason is visible');
$GLOBALS['card_limit']=0;$GLOBALS['unavailable']=true;try{food_ticket_decide_punch($item,$config,$map);chk(false,'request lookup failure');}catch(RuntimeException $e){chk(str_contains($e->getMessage(),'جمع درخواست'),'missing request total fails closed');}
try{food_ticket_save_guest_cap_mode('bad');chk(false,'invalid mode');}catch(RuntimeException $e){chk($GLOBALS['dbsettings']['food_guest_cap_mode']==='requested','invalid mode never saves');}
echo "ALL PASSED\n";
