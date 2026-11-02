'use strict';
// Real Chromium + real chart renderer, isolated fixture and mocked persistence (no live DB).
// NODE_PATH=/path/to/node_modules LD_LIBRARY_PATH=/path/to/nss node tests/test_org_chart_browser.js
const fs = require('fs'), assert = require('assert'), path = require('path');
const chromiumPkg = require('@sparticuz/chromium');
const browserPkg = chromiumPkg.default || chromiumPkg;
const { chromium } = require('playwright-core');
const root = path.resolve(__dirname, '..');
const index = fs.readFileSync(path.join(root,'index.php'),'utf8');
const template = index.split("$orgTemplate = <<<'HTML'\n")[1].split('\nHTML;')[0].replaceAll('__CSRF__','<input name="csrf" value="fixture">').replaceAll('__COMPANY_ID__','1').replaceAll('__CURRENT_COMPANY_NAME__','Fixture').replaceAll('__ORG_COMPANY_SELECTOR__','');
const css = fs.readFileSync(path.join(root,'assets/style.css'),'utf8');
const js = fs.readFileSync(path.join(root,'assets/organization.js'),'utf8');
const units=[
 {id:1,name:'root',parentId:0,type:'ceo',managerId:21,sortOrder:10},
 {id:2,name:'deputy A',parentId:1,type:'deputy',managerId:0,sortOrder:10},
 {id:3,name:'independent',parentId:1,type:'management',managerId:0,sortOrder:20},
 {id:4,name:'deputy B',parentId:1,type:'deputy',managerId:0,sortOrder:30},
 {id:5,name:'child',parentId:2,type:'department',managerId:0,sortOrder:10},
 {id:6,name:'office',parentId:1,type:'office',managerId:0,sortOrder:10}
];
const data={canManage:true,sortEnabled:true,companyId:1,companyName:'Fixture',companyType:'own',companyRootType:'ceo',companyRootTitle:'Root',currentUser:{id:21,name:'CEO'},units,users:[{id:21,name:'CEO',active:true,unitId:0},{id:22,name:'Expert',active:true,unitId:5}]};
const html='<!doctype html><html dir="rtl"><head><style>'+css+'</style></head><body class="page-organization">'+template+'<script>window.PERSIAN_ORG_DATA='+JSON.stringify(data)+';</script><script>'+js+'</script></body></html>';
(async()=>{
 const b=await chromium.launch({executablePath:await browserPkg.executablePath(),args:browserPkg.args,headless:true});
 const page=await b.newPage({viewport:{width:1600,height:900}});let errors=[];page.on('pageerror',e=>errors.push(e.message));let posted=null;
 await page.route('https://org.test/**',async route=>{
   if(route.request().method()==='POST') {posted=route.request().postData(); return route.fulfill({status:200,contentType:'text/html',body:html});}
   return route.fulfill({status:200,contentType:'text/html',body:html});
 });
 await page.goto('https://org.test/index.php?page=organization');await page.waitForSelector('.org-management [data-deputy-drag]');await page.waitForTimeout(100);
 assert.deepStrictEqual(await page.locator('.org-deputy-row > .org-branch').evaluateAll(ns=>ns.map(n=>n.getAttribute('data-branch-id'))),['2','3','4']);
 async function verifyTop(id){let result=await page.evaluate(id=>{
   let card=document.querySelector('[data-unit="'+id+'"].org-node'),r=card.getBoundingClientRect(),x=(r.left+r.right)/2,y=r.top;
   let paths=[...document.querySelectorAll('#org-wires path')];
   return paths.some(p=>{let len=p.getTotalLength(),pt=p.getPointAtLength(len),q=new DOMPoint(pt.x,pt.y).matrixTransform(p.getScreenCTM());return Math.abs(q.x-x)<2 && Math.abs(q.y-y)<2});
 },id);assert(result,'top endpoint is detached from unit '+id);}
 async function verifyBottom(id){let result=await page.evaluate(id=>{
   let card=document.querySelector('[data-unit="'+id+'"].org-node'),r=card.getBoundingClientRect(),x=(r.left+r.right)/2,y=r.bottom;
   return [...document.querySelectorAll('#org-wires path')].some(p=>{let pt=p.getPointAtLength(0),q=new DOMPoint(pt.x,pt.y).matrixTransform(p.getScreenCTM());return Math.abs(q.x-x)<2 && Math.abs(q.y-y)<2});
 },id);assert(result,'start endpoint is detached from unit '+id);}
 for (let id of [2,3,4,5]) await verifyTop(id);
 for (let id of [1,2,5]) await verifyBottom(id);
 // Actual Ctrl+drag + redraw for a root card and a child, followed by zoom.
 for (let id of [1,2,3,5]) {
   const card=page.locator('[data-unit="'+id+'"].org-node .org-node-name');await card.scrollIntoViewIfNeeded();const box=await card.boundingBox();
   await page.keyboard.down('Control');await page.mouse.move(box.x+box.width/2,box.y+box.height/2);await page.mouse.down();await page.mouse.move(box.x+box.width/2+45,box.y+box.height/2+65,{steps:8});await page.mouse.up();await page.keyboard.up('Control');await page.waitForTimeout(100);for(let child of [2,3,4,5])await verifyTop(child);for(let parent of [1,2,5])await verifyBottom(parent);
 }
 await page.locator('[data-org-zoom="in"]').click();await page.waitForTimeout(100);for(let id of [2,3,4,5])await verifyTop(id);for(let id of [1,2,5])await verifyBottom(id);
 // Office and expert boxes must also stay connected after independent movement.
 async function verifyEdge(selector, edge, start) {const found=await page.evaluate(({selector,edge,start})=>{const r=document.querySelector(selector).getBoundingClientRect(),x=edge==='right'?r.right:(r.left+r.right)/2,y=edge==='right'?(r.top+r.bottom)/2:edge==='bottom'?r.bottom:r.top;return [...document.querySelectorAll('#org-wires path')].some(p=>{const pt=p.getPointAtLength(start?0:p.getTotalLength()),q=new DOMPoint(pt.x,pt.y).matrixTransform(p.getScreenCTM());return Math.abs(q.x-x)<2&&Math.abs(q.y-y)<2})},{selector,edge,start});assert(found,selector+' wire endpoint detached');}
 for(const selector of ['[data-unit="6"].org-node .org-node-name','.org-branch[data-branch-id="5"] > .org-branch-card > .org-experts-group .org-expert-heading']){const card=page.locator(selector);await card.scrollIntoViewIfNeeded();const box=await card.boundingBox();await page.keyboard.down('Control');await page.mouse.move(box.x+box.width/2,box.y+box.height/2);await page.mouse.down();await page.mouse.move(box.x+box.width/2+55,box.y+box.height/2+55,{steps:8});await page.mouse.up();await page.keyboard.up('Control');await page.waitForTimeout(80);await verifyEdge('[data-unit="6"].org-node','right',true);await verifyEdge('.org-branch[data-branch-id="5"] > .org-branch-card > .org-experts-group','top',false);}
 // Reset local positions and zoom before hit-testing reorder.
 await page.evaluate(()=>localStorage.clear());await page.reload();await page.waitForSelector('.org-management [data-deputy-drag]');
 let from=page.locator('[data-deputy-drag="3"]'), to=page.locator('[data-unit="2"].org-node .org-node-name');await from.scrollIntoViewIfNeeded();let f=await from.boundingBox(),t=await to.boundingBox();
 await page.mouse.move(f.x+f.width/2,f.y+f.height/2);await page.mouse.down();await page.mouse.move(t.x+t.width/2,t.y+t.height/2,{steps:8});await page.mouse.up();await page.waitForTimeout(150);
 assert(posted && posted.includes('sort_org_root_units'),'reorder must submit mixed-root action');
 assert.deepStrictEqual(errors,[]);console.log('PASS Chromium: real chart movement, wire endpoints, zoom and mixed root reorder');await b.close();
})().catch(e=>{console.error(e);process.exit(1)});
