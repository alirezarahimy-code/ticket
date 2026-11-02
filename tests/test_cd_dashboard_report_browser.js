'use strict';
const fs=require('fs'), assert=require('assert'),{chromium}=require('playwright-core'),binary=require('@sparticuz/chromium');
(async()=>{
 const browser=await chromium.launch({executablePath:await binary.executablePath(),headless:true,args:binary.args});
 try {
  const page=await browser.newPage({viewport:{width:1440,height:900}});
  const css=['assets/style.css','assets/traffic-records-1.62.68.css','assets/traffic-records-1.62.69.css'].map(p=>fs.readFileSync(p,'utf8')).join('\n');
  await page.setContent(fs.readFileSync('/tmp/cd-dashboard.html','utf8'));await page.addStyleTag({content:css});
  const dashboard=await page.evaluate(()=>({heads:[...document.querySelector('.cd-dvd-surface-head').children].map(e=>e.textContent), values:[...document.querySelectorAll('.cd-dvd-surface-row')].map(r=>r.children[1].textContent)}));
  assert.equal(dashboard.heads[1],'شماره برگه خروج');assert.deepEqual(dashboard.values,['999','—','—']);
  await page.setContent(fs.readFileSync('/tmp/traffic69-report.html','utf8'));await page.addStyleTag({content:css});
  await page.evaluate(()=>{window.fetch=async()=>({ok:true,json:async()=>({items:[{serial:'123',name:'نام تست',meeting:'الف',approver:'اول'}],count:1,more:false})});});
  await page.addScriptTag({content:fs.readFileSync('assets/traffic-control.js','utf8')});
  await page.waitForSelector('#traffic-report-rows tr');
  const layout=await page.evaluate(()=>{let shell=document.querySelector('.content-wrap'),table=document.querySelector('.traffic-report-table'),wrap=table.parentElement;return {width:shell.clientWidth,viewport:innerWidth,font:getComputedStyle(table.querySelector('td')).fontSize,scroll:wrap.scrollWidth,client:wrap.clientWidth,details:document.querySelector('.traffic-destination-dropdown')!=null};});
  assert(layout.width > layout.viewport-40,'report content takes full page width');assert.equal(layout.font,'11px');assert(layout.scroll<=layout.client,'report fits desktop');assert(layout.details);
  const fields=page.locator('input[name="fields[]"]');let n=await fields.count();for(let i=0;i<n;i++){let f=fields.nth(i),v=await f.getAttribute('value');if(v!=='name'&&v!=='meeting')await f.uncheck();}
  assert.equal(await page.locator('#traffic-report-rows td').count(),2);
  assert.equal(await page.locator('.traffic-report-table th:visible').count(),2);
  assert.deepEqual(await page.locator('#traffic-report-rows td').allTextContents(),['نام تست','الف']);
  console.log('PASS Chromium: dashboard column, full-width report, compact font, no desktop scroll and live field selection');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
