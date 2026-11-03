'use strict';
const fs = require('fs'), assert = require('assert');
const {chromium} = require('playwright-core'), binary = require('@sparticuz/chromium');
(async () => {
  const browser = await chromium.launch({executablePath: await binary.executablePath(), headless: true, args: binary.args});
  try {
    const page = await browser.newPage();
    for (const width of [1440, 1024, 375, 320]) {
      await page.setViewportSize({width, height: 900});
      await page.setContent(fs.readFileSync('/tmp/traffic69-report.html', 'utf8'));
      await page.addStyleTag({content: ['assets/style.css', 'assets/traffic-records-1.62.68.css', 'assets/traffic-records-1.62.69.css', 'assets/traffic-report-1.62.70.css'].map(f=>fs.readFileSync(f,'utf8')).join('\n')});
      await page.evaluate(() => {
        const options = document.querySelector('.traffic-destination-options');
        for (let i = 2; i <= 35; i++) {
          const label = document.createElement('label');
          const input = document.createElement('input'); input.type='checkbox'; input.name='destinations[]'; input.value=i; input.checked=true;
          const span = document.createElement('span'); span.textContent='مقصد آزمایشی بسیار طولانی شماره ' + i;
          label.append(input, span); options.append(label);
        }
        window.fetch=async()=>({ok:true,json:async()=>({items:[],count:0,more:false})});
      });
      await page.addScriptTag({content:fs.readFileSync('assets/jalali-calendar.js','utf8')});
      await page.evaluate(()=>window.ItsmJalali.enhanceAll(document));
      await page.addScriptTag({content:fs.readFileSync('assets/traffic-control.js','utf8')});
      await page.waitForFunction(() => document.querySelector('#traffic-report-status').textContent.includes('یافت نشد'));
      const summary=page.locator('.traffic-destination-dropdown summary');
      const dropdown=page.locator('.traffic-destination-dropdown');
      await summary.scrollIntoViewIfNeeded();
      const geometry = () => page.evaluate(() => {
        const rect=s=>{let r=document.querySelector(s).getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:r.height};};
        return {date:rect('[name="from_date"]'), dest:rect('.traffic-destination-dropdown'), fields:rect('.traffic-report-fields'), card:rect('.traffic-report-card'), pageHeight:document.documentElement.scrollHeight,scrollY:window.scrollY};
      });
      const before=await geometry();
      if(width>760) assert(Math.abs(before.date.y-before.dest.y)<12,'desktop destination is beside dates');
      else assert(before.dest.y>before.date.y,'mobile stacks destination after dates');
      await summary.click();
      assert(await dropdown.evaluate(e=>e.open));
      const opened=await geometry();
      for (const key of ['fields','card']) for (const axis of ['x','y','width','height']) assert(Math.abs(opened[key][axis]-before[key][axis])<0.1,`${width}: ${key}.${axis} moved`);
      assert.equal(opened.pageHeight,before.pageHeight,`${width}: page height changed`);
      assert.equal(opened.scrollY,before.scrollY,`${width}: page scrolled`);
      const panel=await page.locator('.traffic-destination-options').evaluate(e=>{const r=e.getBoundingClientRect();return {left:r.left,right:r.right,top:r.top,height:r.height,scroll:e.scrollHeight,style:getComputedStyle(e).position};});
      assert.equal(panel.style,'absolute');assert(panel.scroll>panel.height,'long destination list scrolls');
      assert(panel.left>=0 && panel.right<=width, `${width}: popup outside viewport`);
      assert(panel.top>=opened.dest.y+opened.dest.height,'popup below control');
      await page.locator('#traffic-report-all').uncheck();
      assert(await dropdown.evaluate(e=>e.open),'checking does not close multi-select');
      await page.locator('input[name="destinations[]"]').first().check();
      assert(await dropdown.evaluate(e=>e.open));
      assert.equal(await page.locator('#traffic-report-all').isChecked(),false);
      assert.equal(await page.locator('input[name="destinations[]"]').first().isChecked(),true);
      await page.keyboard.press('Escape');
      assert(!(await dropdown.evaluate(e=>e.open)),'Escape closes');
      assert(await summary.evaluate(e=>document.activeElement===e),'Escape restores focus');
      await summary.click(); await page.locator('#traffic-report-title').click();
      assert(!(await dropdown.evaluate(e=>e.open)),'outside click closes');
      await summary.click(); await summary.click();
      assert(!(await dropdown.evaluate(e=>e.open)),'summary toggles closed');
      await summary.click(); await page.locator('.calendar-trigger').first().click();
      assert(!(await dropdown.evaluate(e=>e.open)),'opening calendar closes destinations');
      assert(await page.locator('.jalali-calendar').first().isVisible(),'calendar opens');
      console.log(`PASS report popup Chromium ${width}px: geometry, viewport, scrolling, checkbox, escape, outside, calendar`);
    }
  } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exitCode=1});
