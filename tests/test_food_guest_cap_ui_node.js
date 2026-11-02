'use strict';
// Real panel functions and event handlers, with jsdom + mocked API. No production server.
const assert=require('assert'),fs=require('fs'),path=require('path'),{JSDOM}=require('jsdom');
const html=fs.readFileSync(path.join(__dirname,'../food-ticket-web/index.html'),'utf8');
const lines=html.split('\n');
const get=prefix=>{const line=lines.find(s=>s.trim().startsWith(prefix));assert(line,'missing '+prefix);return line.trim()};
const cap=get('function loadGuestCap()'),control=get('function capModeControl(g)'),save=get("document.addEventListener('click',e=>{const a=e.target.closest('[data-action=\"save-guests\"]')"),change=get("document.addEventListener('change',function(e){if(!e.target||e.target.name!=='guest-cap-mode')");
assert(html.includes("if(act==='save-guests'){if(serviceMode)return;"),'no false local success on service save');
const dom=new JSDOM('<!doctype html><html><body><main></main></body></html>',{runScripts:'outside-only',url:'https://fixture.test/'});const w=dom.window;
w.eval(`var serviceMode=true;var guestCapModeDirty=false;var state={guests:{cards:[],capMode:'variable',capStart:'11:00',capCutoff:'12:01',capEnd:'15:00',requestSameDayCutoff:'09:30',maxDaily:20,food:'مهمان'}};var toast=x=>window.messages.push(x);var persist=()=>{};var guestList=()=>[];var normHm=x=>/^\\d{2}:\\d{2}$/.test(String(x))?x:null;var esc=x=>String(x);var api=window.mockApi;${cap}\n${control}\n${save}\n${change}\ndocument.querySelector('main').innerHTML=capModeControl(state.guests)+'<input id="guest-request-cutoff" value="09:30"><button data-action="save-guests">save</button>';`);
w.messages=[];let saved='variable',fail=true,postCount=0;
w.mockApi=(route,opts)=>{if(route==='/api/config'){postCount++;if(fail)return Promise.reject(new Error('خطای ذخیره'));saved=JSON.parse(opts.body).guestCapMode;return Promise.resolve({ok:true})}return Promise.resolve({mode:saved,cap:saved==='requested'?12:3,requested:12,inside:3,time:'12:30',fallback:20})};
// api was bound from mockApi at evaluation time; set it before use.
w.eval('api=window.mockApi');
const wait=()=>new Promise(r=>setTimeout(r,35));
(async()=>{await wait();const radio=w.document.querySelector('input[value="requested"]');radio.checked=true;radio.dispatchEvent(new w.Event('change',{bubbles:true}));const status=w.document.getElementById('guest-cap-save-status');assert(!status.hidden);assert(w.document.getElementById('guest-cap-live').textContent.includes('کنترل تردد'));
w.document.querySelector('[data-action="save-guests"]').click();await wait();assert.equal(postCount,1);assert.equal(saved,'variable');assert(!status.hidden);assert(w.messages.some(x=>x.includes('خطای ذخیره')));assert(!w.messages.some(x=>x.includes('تأیید شد')));
fail=false;w.document.querySelector('[data-action="save-guests"]').click();await wait();assert.equal(saved,'requested');assert(status.hidden);assert(w.messages.some(x=>x.includes('تأیید شد')));assert(w.document.getElementById('guest-cap-live').textContent.includes('۱۲'));console.log('PASS guest cap UI: unsaved state, failed save, server-confirmed request cap 12');w.close()})().catch(e=>{console.error(e);process.exitCode=1;w.close()});
