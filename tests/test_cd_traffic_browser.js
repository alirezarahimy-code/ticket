'use strict';
const fs=require('fs'), assert=require('assert'),{chromium}=require('playwright-core'),binary=require('@sparticuz/chromium');
(async()=>{
 const browser=await chromium.launch({executablePath:await binary.executablePath(),headless:true,args:binary.args});
 try {
  const page=await browser.newPage({viewport:{width:1440,height:800}});
  for (const width of [1440,1024,375]) {
   await page.setViewportSize({width,height:800});
   await page.setContent(fs.readFileSync('/tmp/cd-history.html','utf8'));
   await page.addStyleTag({content:fs.readFileSync('assets/style.css','utf8')+'\n'+fs.readFileSync('assets/traffic-records-1.62.68.css','utf8')+'\n'+fs.readFileSync('assets/traffic-records-1.62.69.css','utf8')});
   const results=await page.evaluate(()=>{
     const table=document.querySelector('.cd-dvd-record-table'),rows=[...table.querySelectorAll('.cd-dvd-record-row')];
     return {scroll:table.scrollWidth,client:table.clientWidth,header:table.querySelector('.cd-dvd-record-head').children[1].textContent,
       cells:rows.map(r=>r.children[1].textContent.trim()),y:rows.map(r=>[...r.children].map(c=>({top:c.getBoundingClientRect().top, height:c.getBoundingClientRect().height}))),
       wrap:rows.map(r=>[...r.children].map(c=>getComputedStyle(c).whiteSpace)), buttons:rows.map(r=>[...r.querySelectorAll('.actions .mini-button')].map(c=>({y:c.getBoundingClientRect().top,h:c.getBoundingClientRect().height}))) };
   });
   assert.equal(results.header,'شماره برگه خروج');assert.deepEqual(results.cells,['شماره 123','—','—']);
   for (const row of results.wrap) assert(row.slice(0, -1).every(value=>value==='normal') && row.at(-1)==='nowrap', 'long content wraps instead of being truncated');
   if(width>=1024) assert(results.scroll<=results.client, 'desktop uses full width without horizontal scrolling');
   for (const buttons of results.buttons) assert(Math.max(...buttons.map(b=>b.y))-Math.min(...buttons.map(b=>b.y))<2,'buttons should share one line');
   if(width===375) assert(results.scroll>results.client,'mobile horizontal scrolling');
  }
 await page.addScriptTag({content:fs.readFileSync('assets/cd-dvd.js','utf8')});
 const printResult=await page.evaluate(()=>{
   let html='';window.open=()=>({document:{write:s=>{html=s},close:()=>{}},print:()=>{},close:()=>{}});
   printCdDvdHistory();const parsed=new DOMParser().parseFromString(html,'text/html');
   return {heads:parsed.querySelectorAll('thead th').length,cells:parsed.querySelectorAll('tbody tr:first-child td').length,text:parsed.querySelector('thead').textContent};
 });
 assert.equal(printResult.heads,9);assert.equal(printResult.cells,9);assert(!printResult.text.includes('عملیات'));
 await page.setContent(fs.readFileSync('/tmp/traffic-new.html','utf8'));await page.addScriptTag({content:fs.readFileSync('assets/traffic-control.js','utf8')});
  await page.check('#traffic-notice-only');assert.equal(await page.locator('#traffic-national-code').isDisabled(),true);assert.equal(await page.locator('#traffic-meeting-with').isDisabled(),false);
  await page.selectOption('#traffic-meeting-with','الف');await page.selectOption('#traffic-approved-by','دوم');
  assert.equal(await page.locator('#traffic-approved-by').inputValue(),'دوم');await page.close();
  console.log('PASS Chromium: actual CD table content, one-line actions, mobile overflow; actual traffic special form');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
