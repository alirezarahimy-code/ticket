'use strict';
const assert = require('assert');
const fs = require('fs');
const { JSDOM } = require('jsdom');
const html = `<!doctype html><html><body>
<form id="traffic-history-form"><input name="page" value="traffic-control"><input name="tab" value="history"><input id="traffic-history-search" name="search"><select name="range"><option value="all">all</option><option value="today">today</option></select><input type="checkbox" name="open_only" value="1"></form>
<div id="traffic-history-table"><div id="traffic-history-rows"></div><p id="traffic-history-empty" hidden></p></div><p id="traffic-history-status"></p><button id="traffic-history-prev"></button><button id="traffic-history-next"></button>
<form id="traffic-report-form"><input name="action" value="export_traffic_report"><input name="from_date" type="text"><input name="to_date" type="text"><input id="traffic-report-all" type="checkbox" name="all_destinations" value="1" checked><input type="checkbox" name="destinations[]" value="الف" checked><input type="checkbox" name="destinations[]" value="ب" checked><input name="search" type="search"></form>
<p id="traffic-report-status"></p><table><tbody id="traffic-report-rows"></tbody></table><button id="traffic-report-prev"></button><button id="traffic-report-next"></button>
</body></html>`;
const dom = new JSDOM(html, {runScripts:'outside-only', url:'https://example.test/index.php?page=traffic-control&tab=history'});
const w=dom.window; const requests=[];
w.fetch = url => new Promise(resolve => requests.push({url:new URL(url, w.location.href), resolve}));
const reply=(request, data) => request.resolve({ok:true,json:async()=>data});
const sleep=ms => new Promise(r=>setTimeout(r,ms));
(async()=>{
 w.eval(fs.readFileSync('assets/traffic-control.js','utf8')); await sleep(20);
 assert.equal(requests.length,1); assert.equal(requests[0].url.searchParams.get('traffic_api'),'history');
 reply(requests[0],{html:'<div>first</div>',count:100,more:true}); await sleep(20);
 assert.equal(w.document.querySelector('#traffic-history-next').disabled,false);
 w.document.querySelector('#traffic-history-next').click();await sleep(20);
 assert.equal(requests[1].url.searchParams.get('offset'),'100');
 const input=w.document.querySelector('#traffic-history-search');input.value='نام';input.dispatchEvent(new w.Event('input',{bubbles:true}));
 // Old page response arrives after a new filter has been typed; must be ignored.
 reply(requests[1],{html:'<div>stale</div>',count:1,more:false});await sleep(330);
 assert.equal(requests[2].url.searchParams.get('search'),'نام');assert.equal(requests[2].url.searchParams.get('offset'),'0');
 reply(requests[2],{html:'<div>fresh</div>',count:1,more:false});await sleep(20);
 assert.match(w.document.querySelector('#traffic-history-rows').textContent,/fresh/);
 const open=w.document.querySelector('[name=open_only]');open.checked=true;open.dispatchEvent(new w.Event('change',{bubbles:true}));await sleep(20);
 assert.equal(requests[3].url.searchParams.get('open_only'),'1');reply(requests[3],{html:'',count:0,more:false});await sleep(20);
 assert.equal(w.document.querySelector('#traffic-history-empty').hidden,false);
 const form=w.document.querySelector('#traffic-report-form');form.elements.from_date.value='1405/07/20';form.elements.to_date.value='1405/07/20';
 form.elements.to_date.dispatchEvent(new w.Event('change',{bubbles:true}));await sleep(20);
 const req=requests[4]; assert.equal(req.url.searchParams.get('traffic_api'),'report');assert.equal(req.url.searchParams.get('action'),null);assert.equal(req.url.searchParams.get('from_date'),'1405/07/20');
 reply(req,{items:[{serial:'1',name:'<img src=x onerror=alert(1)>',meeting:'الف',approver:'ب'}],count:1,more:false});await sleep(20);
 assert.equal(w.document.querySelector('#traffic-report-rows img'),null);assert.match(w.document.querySelector('#traffic-report-rows').textContent,/<img/);
 const target=form.querySelector('input[value="الف"]');target.checked=false;target.dispatchEvent(new w.Event('change',{bubbles:true}));await sleep(20);
 assert.equal(form.querySelector('#traffic-report-all').checked,false);assert.deepEqual(requests[5].url.searchParams.getAll('destinations[]'),['ب']);
 console.log('PASS live server search, filters, pagination, stale response, report criteria and escaping');dom.window.close();
})().catch(e=>{console.error(e);process.exitCode=1;dom.window.close();});
