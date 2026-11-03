<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/..');
function food_order_internal_food_for_employee(int $id, string $date): ?string { if (isset($GLOBALS['sequence'][$id]) && $GLOBALS['sequence'][$id]) return array_shift($GLOBALS['sequence'][$id]); return $GLOBALS['orders'][$id] ?? null; }
function food_ticket_group_event_ids_for_run(int $id): array { return array_keys($GLOBALS['events']); }
function food_ticket_group_print_mode(): string { return 'group'; }
function db_table_exists(string $name): bool { return false; }
function app_error_log(string $message): void { $GLOBALS['logs'][] = $message; }
function food_ticket_tpl_print_network(array $event, string $host, int $port): void { $GLOBALS['sent'][]=$event; }
class QueueStmt {
    public array $args=[]; public int $affected=0;
    public function __construct(public string $sql) {}
    public function execute(?array $args=null): bool {
        $this->args=$args??[]; $s=$this->sql; $p=$this->args;
        if (str_contains($s, 'UPDATE food_ticket_events SET food_type =')) {
            [$food,$id,$old]=$p; $row=&$GLOBALS['events'][$id];
            if ($row['print_status']==='printing' && $row['food_type']===$old) { $row['food_type']=$food; $this->affected=1; }
        } elseif (str_contains($s, 'SET print_status = "printing"')) {
            $ids=str_contains($s,'id IN (') ? array_slice($p,2) : [end($p)];
            foreach ($ids as $id) { if (in_array($GLOBALS['events'][$id]['print_status'],['pending','failed','print_error'],true)) { $GLOBALS['events'][$id]['print_status']='printing';$GLOBALS['events'][$id]['retry_count']++;$GLOBALS['events'][$id]['print_attempts']++;$this->affected++; } }
        } elseif (str_contains($s, 'SET print_status = "printed"')) {
            foreach ($p as $id) if (isset($GLOBALS['events'][$id])) $GLOBALS['events'][$id]['print_status']='printed';
        } elseif (str_contains($s, 'SET print_status = "failed"')) {
            $ids=str_contains($s,'id IN (') ? array_slice($p,2) : [end($p)];
            foreach ($ids as $id) if (isset($GLOBALS['events'][$id])) $GLOBALS['events'][$id]['print_status']='failed';
        }
        return true;
    }
    public function rowCount(): int { return $this->affected; }
    public function fetchColumn(int $n=0): mixed { return false; }
    public function fetch(): array|false { return str_contains($this->sql,'food_ticket_group_runs') ? ['status'=>'done','group_title'=>'گروه','punch_date'=>'2026-11-02','punch_time'=>'12:00'] : false; }
    public function fetchAll(int $mode=0): array { return array_values(array_filter($GLOBALS['events'], fn($e) => str_contains($this->sql, 'WHERE id IN') || in_array($e['print_status'], ['pending','failed','print_error'], true))); }
}
class QueueDB {
    public function prepare(string $sql): QueueStmt { return new QueueStmt($sql); }
    public function query(string $sql): QueueStmt { $s=new QueueStmt($sql);$s->execute();return $s; }
}
function db(): QueueDB { static $db; return $db??=new QueueDB(); }
require __DIR__ . '/../food-ticket-runtime.php';
food_ticket_delivery_supported(true);
function chk(bool $ok,string $label): void { if(!$ok) { echo "FAIL $label\n"; exit(1); } echo "PASS $label\n"; }
function event(int $id, string $food='ساندویچ', bool $group=false): array { return ['id'=>$id,'user_id'=>$id,'punch_date'=>'2026-11-02','punch_time'=>'12:00','full_name'=>'کارمند '.$id,'food_type'=>$food,'ticket_key'=>'food:2026-11-02:'.$id,'event_type'=>'printed','print_status'=>'pending','retry_count'=>0,'print_attempts'=>0,'source_key'=>'s'.$id,'source_payload'=>$group?' {"run_id":9}':'','print_job_id'=>'j'.$id]; }
function reset_test(array $rows,array $orders): void { $GLOBALS['events']=[]; foreach($rows as $r) $GLOBALS['events'][$r['id']]=$r; $GLOBALS['orders']=$orders;$GLOBALS['sequence']=[];$GLOBALS['sent']=[];$GLOBALS['logs']=[]; }
function process(): array { return food_ticket_process_print_queue(['printer_host'=>'fake','printer_mode'=>'tcp_raw']); }
reset_test([event(1)],[1=>'جوجه کباب']); process();
chk(count($GLOBALS['sent'])===1 && $GLOBALS['sent'][0]['food_type']==='جوجه کباب' && $GLOBALS['events'][1]['food_type']==='جوجه کباب','individual uses current choice, reconciles snapshot and sends it');
chk((bool)array_filter($GLOBALS['logs'],fn($s)=>str_contains($s,'food_reconciled')),'reconciliation recorded');
reset_test([event(1),event(2)],[2=>'خورش']); process();
chk(count($GLOBALS['sent'])===1 && $GLOBALS['sent'][0]['user_id']===2 && $GLOBALS['events'][1]['print_status']==='failed','cancelled individual not printed, other worker continues');
$r=event(1);$r['print_status']='failed';$r['retry_count']=1;$r['print_attempts']=1;
reset_test([$r],[1=>'جوجه کباب']);process();chk(!$GLOBALS['sent'] && $GLOBALS['events'][1]['food_type']==='ساندویچ','mismatch after previous attempt fails closed without rewriting');
reset_test([$r],[1=>'ساندویچ']);process();chk(count($GLOBALS['sent'])===1,'unchanged retry retains existing behavior');
$r=event(1,'مهمان');$r['event_type']='guest';$r['ticket_key']='guest:test';$r['user_id']=0;
reset_test([$r],[]);process();chk(count($GLOBALS['sent'])===1 && $GLOBALS['sent'][0]['food_type']==='مهمان','guest remains independent of employee orders');
$r=event(1);$r['user_id']=0;reset_test([$r],[1=>'جوجه کباب']);process();chk(!$GLOBALS['sent'],'missing identity fails closed');
reset_test([event(1,'ساندویچ',true),event(2,'خورش',true)],[1=>'جوجه کباب',2=>'خورش']);process();
chk(count($GLOBALS['sent'])===1 && $GLOBALS['sent'][0]['group_ticket_rows'][0]['food']==='جوجه کباب' && $GLOBALS['events'][1]['food_type']==='جوجه کباب','group sends one receipt with current member choices');
reset_test([event(1,'ساندویچ',true),event(2,'خورش',true)],[1=>'جوجه کباب']);process();
chk(!$GLOBALS['sent'] && $GLOBALS['events'][1]['food_type']==='ساندویچ' && $GLOBALS['events'][1]['print_status']==='failed','group validates all members before reconciling or sending');
reset_test([event(1)],[1=>'خورش']);$GLOBALS['sequence'][1]=['جوجه کباب','خورش'];process();chk(!$GLOBALS['sent'] && $GLOBALS['events'][1]['print_status']==='failed','change during preparation stops before sending');
$r=event(1);$r['print_status']='printed';reset_test([$r],[1=>'جوجه کباب']);process();chk(!$GLOBALS['sent'] && $GLOBALS['events'][1]['food_type']==='ساندویچ','queue never touches printed receipt');
try { food_ticket_prepare_queued_food($r);chk(false,'printed history never reconciled'); } catch(RuntimeException $e) { chk(true,'printed mismatch never reconciled'); }
echo "ALL PASSED\n";
