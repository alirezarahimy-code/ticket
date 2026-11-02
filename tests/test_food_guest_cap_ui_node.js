'use strict';
// Actual view markup and handlers. Both production views, without injected form fields.
const assert=require('assert'),fs=require('fs'),path=require('path'),{JSDOM}=require('jsdom');
const html=fs.readFileSync(path.join(__dirname,'../food-ticket-web/index.html'),'utf8');const lines=html.split('\n');
const get=p=>{const l=lines.find(s=>s.trim().startsWith(p));assert(l,'missing '+p);return l.trim()};
const list=get('function guestList()'),loader=get('function loadGuestCards()'),cap=get('function loadGuestCap()'),control=get('function capModeControl(g)'),guests=get('function guests()'),cards=get('function guestCardForm()'),save=get("document.addEventListener('click',e=>{const a=e.target.closest('[data-action=\"save-guests\"]')"),change=get("document.addEventListener('change',function(e){if(!e.target||e.target.name!=='guest-cap-mode')");
const wait=()=>new Promise(r=>setTimeout(r,100));
async function run(view){
 const dom=new JSDOM('<!doctype html><html><body><main></main></body></html>',{runScripts:'outside-only',url:'https://fixture.test/'}),w=dom.window;
 let saved='variable',postCount=0,configPosts=0,fail=false,failRead=false;w.messages=[];
 w.mockApi=(route,opts={})=>{if(route==='/api/guest-cap'&&opts.method==='POST'){postCount++;if(fail)return Promise.reject(new Error('خطای ذخیره'));saved=JSON.parse(opts.body).mode;return Promise.resolve({mode:saved})}if(route==='/api/config'){configPosts++;return Promise.resolve({ok:true})}if(failRead)return Promise.reject(new Error('unavailable'));return Promise.resolve({mode:saved,cap:saved==='requested'?12:3,requested:12,inside:3,time:'12:30',fallback:20})};
 w.eval(`var serviceMode=true;let guestCapModeDirty=false,guestCapSaving=false;var state={guests:{cards:[],capMode:'variable',capStart:'11:00',capCutoff:'12:01',capEnd:'15:00',requestSameDayCutoff:'09:30',maxDaily:20,food:'مهمان',cooldown:2}};var toast=x=>window.messages.push(x);var persist=()=>{};var guestList=()=>[];var heading=(a,b,c)=>'<header>'+c+'</header>';var normHm=x=>/^\\d{2}:\\d{2}$/.test(String(x))?x:null;var esc=x=>String(x);var api=window.mockApi;${cap}\n${control}\n${guests}\n${cards}\n${save}\n${change}\ndocument.querySelector('main').innerHTML=${view}();`);
 await wait(); const radio=w.document.querySelector('input[value="requested"]'); radio.checked=true;radio.dispatchEvent(new w.Event('change',{bubbles:true}));await wait();
 assert.equal(saved,'requested',view+': choice saved automatically');assert.equal(postCount,1);assert(w.document.getElementById('guest-cap-live').textContent.includes('۱۲'));assert(w.messages.some(x=>x.includes('ذخیره شد')));
 w.document.querySelector('[data-action="save-guests"]').click();await wait();assert.equal(configPosts,1,view+': other guest settings can be saved without nonexistent cutoff field');
 fail=true;let variable=w.document.querySelector('input[value="variable"]');variable.checked=true;variable.dispatchEvent(new w.Event('change',{bubbles:true}));await wait();assert.equal(saved,'requested');assert(!w.document.getElementById('guest-cap-save-status').hidden);assert(w.messages.some(x=>x.includes('خطای ذخیره')));
 fail=false;variable=w.document.querySelector('input[value="variable"]');variable.checked=true;variable.dispatchEvent(new w.Event('change',{bubbles:true}));await wait();assert.equal(saved,'variable');
 failRead=true;radio.checked=true;radio.dispatchEvent(new w.Event('change',{bubbles:true}));await wait();assert.equal(saved,'requested');assert(!w.document.getElementById('guest-cap-save-status').hidden,'do not falsely confirm when post succeeded but readback failed');
 w.close();console.log('PASS actual '+view+' view: auto save, failure, readback and full form');
}
async function testCardLimit(){const dom=new JSDOM('<!doctype html><body><main></main>',{runScripts:'outside-only',url:'https://fixture.test/'}),w=dom.window;
w.mockApi=()=>Promise.resolve({items:[{id:1,cardNumber:'ABC123',dailyLimit:3,active:true}],guestCapMode:'requested'});
w.eval(`var serviceMode=true,view='none',guestCapModeDirty=false;var state={guests:{},settingsSection:''};var panelStartupApi=window.mockApi,persist=()=>{},render=()=>{},heading=(a,b,c)=>c,capModeControl=()=>'',esc=x=>String(x);${list}\n${cards}\n${loader}\nloadGuestCards();`);await wait();w.eval('document.querySelector("main").innerHTML=guestCardForm()');assert(w.document.querySelector('main').textContent.includes('3'),'card limit preserved through API mapping and rendered');assert(w.document.querySelector('input[name=dailyLimit]'));w.close();console.log('PASS independent card limit displayed and editable');}
(async()=>{await run('guests');await run('guestCardForm');await testCardLimit()})().catch(e=>{console.error(e);process.exitCode=1});
