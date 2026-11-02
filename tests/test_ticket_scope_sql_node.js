'use strict';
// Execute the emitted production predicate against a real relational engine via Python sqlite.
const {execFileSync}=require('child_process'),assert=require('assert');
const sqlFixture=`<?php
const APP_ROOT='/';
function user_is_primary_admin(array $u):bool{return false;}
function is_global_ticket_role(?string $r):bool{return false;}
function user_can(array $u,string $p):bool{return in_array($p,$u['permissions']);}
function is_it_agent(array $u):bool{return false;}
class S {public function execute($p){} public function fetchAll($m=null){return [2];}}
class D {public function prepare($s){return new S;} public function query($s){return new class{public function fetchAll($m=null){return [['id'=>2,'parent_id'=>1,'company_id'=>1],['id'=>3,'parent_id'=>2,'company_id'=>1],['id'=>4,'parent_id'=>1,'company_id'=>1],['id'=>5,'parent_id'=>2,'company_id'=>2]];}};}}
function db(){return new D;}
require '${process.cwd()}/lib/auth-scope.php';
require '${process.cwd()}/lib/notify-users.php';
$u=['id'=>11,'role'=>'manager','handling_unit_code'=>'support','permissions'=>['ticket.view_own','ticket.view_unit']];
echo json_encode([ticket_scope($u),notification_ticket_visibility($u,'n')]);`;
const fs=require('fs'),os=require('os'),path=require('path'),tmp=fs.mkdtempSync(path.join(os.tmpdir(),'scope-'));fs.writeFileSync(path.join(tmp,'scope.php'),sqlFixture);
const result=JSON.parse(execFileSync('/home/user/.cache/food-debug/node_modules/.bin/php-wasm-cli',[path.join(tmp,'scope.php')],{encoding:'utf8'}));
const py=`import sqlite3,json,sys\n(scope,params),(notif,nparams)=json.loads(sys.stdin.read())\nc=sqlite3.connect(':memory:')\nc.executescript("CREATE TABLE users(id INTEGER,org_unit_id INTEGER); CREATE TABLE org_units(id INTEGER,is_active INTEGER,manager_user_id INTEGER); CREATE TABLE org_unit_managers(user_id INTEGER,unit_id INTEGER); CREATE TABLE tickets(id INTEGER,requester_id INTEGER,service_group TEXT); CREATE TABLE notifications(id INTEGER,ticket_id INTEGER,user_id INTEGER); INSERT INTO users VALUES(11,NULL),(20,3),(21,4),(22,5),(23,NULL),(24,NULL); INSERT INTO org_units VALUES(2,1,23),(3,1,NULL),(4,1,NULL); INSERT INTO org_unit_managers VALUES(24,3); INSERT INTO tickets VALUES(1,20,'support'),(2,21,'support'),(3,22,'support'),(4,11,'support'),(5,20,'it'),(6,23,'support'),(7,24,'support'); INSERT INTO notifications VALUES(1,1,11),(2,2,11),(3,NULL,11),(4,5,11)")\nprint(json.dumps([c.execute('SELECT t.id FROM tickets t '+scope+' ORDER BY id',params).fetchall(),c.execute('SELECT n.id FROM notifications n WHERE n.user_id = ? '+notif+' ORDER BY id',[11]+nparams).fetchall()]))`;
const rows=JSON.parse(execFileSync('python3',['-c',py],{input:JSON.stringify(result),encoding:'utf8'}));
assert.deepStrictEqual(rows,[[[1],[4],[6],[7]],[[1],[3]]]);console.log('PASS real SQL: list + stale notifications respect subtree, company and service group');
