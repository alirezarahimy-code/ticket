#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""سازندهٔ بستهٔ بهروزرسانی ۱٫۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها.

خروجی:
  updates/2026-11-19-release-1.63.3-absent-lunch-report/{files,manifest.json,...}
  downloads/ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip(+.sha256)

هیچ فایل محصولی در شاخه دستکاری نمیشود؛ همهٔ نسخههای تغییریافته داخل files/ بستهاند.
"""
from __future__ import annotations
import hashlib, json, re, zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PKG = ROOT / 'updates/2026-11-19-release-1.63.3-absent-lunch-report'
FILES = PKG / 'files'
DOWNLOADS = ROOT / 'downloads'
ZIP_PATH = DOWNLOADS / 'ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip'
VERSION = '1.63.3'
REQUIRES = '1.63.2'
PANEL_VERSION = '1.37.57'
DATE = '2026-11-19'
SOURCE_ZIP = Path('/tmp/absent-pkg/source.zip')

# ---------------------------------------------------------------- helpers
def sha256(b: bytes) -> str:
    return hashlib.sha256(b).hexdigest()

def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')

def must_replace(text: str, old: str, new: str, label: str, count: int = 1) -> str:
    n = text.count(old)
    if n != count:
        raise SystemExit(f'{label}: انتظار {count} رخداد، پیدا شد {n}')
    return text.replace(old, new, count)

# ---------------------------------------------------------------- 1) backend
backend = read('food-ticket.php')
enhanced = SOURCE_ZIP and None
import zipfile as _z
with _z.ZipFile(SOURCE_ZIP) as z:
    enhanced = z.read('food-absent-report-only/backend/food_ticket_api_absent.php').decode('utf-8')
enhanced = '\n'.join(enhanced.splitlines()[2:]).strip() + '\n'  # حذف <?php و کامنت استخراج
fn_re = re.compile(r'function food_ticket_api_absent\(string \$fromDate.*?\n\}\n', re.S)
m = fn_re.search(backend)
if not m:
    raise SystemExit('تابع food_ticket_api_absent در food-ticket.php پیدا نشد')
backend = backend[:m.start()] + enhanced + backend[m.end():]
backend = must_replace(backend, "const FOOD_TICKET_PANEL_VERSION = '1.37.53';",
                        f"const FOOD_TICKET_PANEL_VERSION = '{PANEL_VERSION}';", 'panel version php')

# ---------------------------------------------------------------- 2) panel HTML
html = read('food-ticket-web/index.html')
html = must_replace(html, "PANEL_VERSION='1.37.53'", f"PANEL_VERSION='{PANEL_VERSION}'", 'panel version html')

tabs_old = "const REPORT_TABS=[['classic','گزارشگیری'],['aggregate','گزارش تجمیعی'],['delivered','غذای تحویلی'],['statistics','آمار سفارشها'],['todayorders','سفارشهای امروز'],['reconciliation','تطبیق خرید و توزیع']];"
tabs_new = tabs_old[:-2] + ",['absent-lunch','غایبین ناهار']];"
html = must_replace(html, tabs_old, tabs_new, 'REPORT_TABS')

html = must_replace(html, ' else body=reportClassicView();',
    " else if(tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html():'<section class=\"card\"><p>در حال بارگذاری تب غایبین ناهار…</p></section>';else body=reportClassicView();", 'reportView dispatch')

html = must_replace(html, 'function render(){const pages={',
    "function render(){if(state.report&&state.report.type==='absent'){state.report.type='attendance';state.reportTab='absent-lunch';persist()}const pages={", 'render normalization')

render_old = "if(view==='reports'){ensureReportRange();if((state.reportTab||'classic')==='aggregate')loadAggregate(false);else if(!FOOD_REPORT_TABS[state.reportTab]&&state.report&&state.report.type==='absent')loadAbsent(false)}"
render_new = "if(view==='reports'){ensureReportRange();if((state.reportTab||'classic')==='aggregate')loadAggregate(false);else if((state.reportTab||'classic')==='absent-lunch'&&window.AbsentLunchReport)window.AbsentLunchReport.load(false);else if(!FOOD_REPORT_TABS[state.reportTab]&&state.report&&state.report.type==='absent')loadAbsent(false)}"
html = must_replace(html, render_old, render_new, 'render loader hook')

# گزینهٔ قدیمی «نوع گزارش = غایبین» از هر دو فرم حذف میشود (گزارش به تب تازه رفت)
opt_re = re.compile(r'<option value="absent" \$\{r\.type===\'absent\'\?\'selected\':\'\'\}>غایبین[^<]*</option>')
html, n_opt = opt_re.subn('', html)
if n_opt != 2:
    raise SystemExit(f'حذف گزینهٔ absent: انتظار ۲، پیدا شد {n_opt}')

# استایل اختصاصی تب (از توکنهای تم اصلی سامانه استفاده میکند)
CSS = r"""
/* ۱.۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها */
.alr-card{background:var(--food-white);border:1px solid var(--food-line);border-radius:16px;padding:19px;box-shadow:var(--food-shadow)}
.alr-fields{display:flex;flex-wrap:wrap;align-items:center;gap:8px 14px}
.alr-chip{display:inline-flex;align-items:center;gap:5px;white-space:nowrap;font-size:13px;font-weight:600;cursor:pointer}
.alr-chip input{accent-color:var(--teal)}
.alr-blocks{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:13px;padding-top:12px;border-top:1px solid var(--food-line)}
.alr-blocks-label{font-weight:800;font-size:12px;color:var(--muted)}
.alr-block-btn{border:1px solid var(--food-line);border-radius:8px;padding:5px 12px;background:var(--food-white);color:var(--muted);font-size:12px;font-weight:800}
.alr-block-btn.active{border-color:var(--teal);background:var(--teal-light);color:var(--navy)}
.alr-hint{color:var(--muted);font-size:11px}
.alr-table-wrap{overflow:auto}
.alr-table{width:100%!important;max-width:100%!important;min-width:0!important;table-layout:fixed;border-collapse:separate;border-spacing:0;font-size:12px;white-space:nowrap;margin-inline:auto}
.alr-table th,.alr-table td{overflow:hidden;text-overflow:ellipsis;text-align:center;padding:8px 7px;border-bottom:1px solid var(--food-line)}
.alr-table thead th{position:sticky;top:0;z-index:1;background:var(--food-paper);color:var(--muted);font-size:11px;font-weight:800}
.alr-table tbody td{color:var(--ink)}
.alr-table tbody tr:hover td{background:var(--teal-light)}
.alr-table .alr-block-start{border-right:2px solid var(--food-line)}
.alr-sort{border:0;background:transparent;color:inherit;font:inherit;font-weight:inherit;padding:0}
.alr-sort:hover{color:var(--teal)}
.alr-pill{border-radius:99px;padding:2px 9px;font-size:10px;font-weight:800;background:var(--food-paper);color:var(--muted)}
.alr-pill.declared{background:var(--teal-light);color:var(--navy)}
.alr-group{margin-top:16px}
.alr-group-head{display:flex;justify-content:center;align-items:baseline;gap:6px;margin:0 0 7px}
.alr-group-head h3{margin:0;font-size:15px}
.alr-group-head span{color:var(--muted);font-size:13px;font-weight:600}
.alr-info{font-size:12px;color:var(--teal);font-weight:800}
#absentLunchPrint{display:none}
body.absent-lunch-printing > *:not(#absentLunchPrint){display:none!important}
body.absent-lunch-printing #absentLunchPrint{display:block!important;background:#fff;color:#111;padding:0;margin:0}
#absentLunchPrint h1{text-align:center;font-size:18px;margin:0 0 6px}
#absentLunchPrint .alr-tab-title{text-align:center;font-size:13px;font-weight:800;margin:0 0 12px}
#absentLunchPrint h2{font-size:12px;margin:12px 0 5px}
#absentLunchPrint .alr-group{break-inside:avoid}
@media print{
 #absentLunchPrint .alr-table{width:100%!important;table-layout:fixed;border-collapse:collapse}
 #absentLunchPrint .alr-table col{width:auto!important}
 #absentLunchPrint .alr-table thead{display:table-header-group}
 #absentLunchPrint .alr-table th,#absentLunchPrint .alr-table td{border:1px solid #666;padding:4px 3px;font-size:9px;color:#111!important;background:#fff!important}
 #absentLunchPrint tr{break-inside:avoid}
 @page{margin:10mm}
}
"""
html = must_replace(html, '</head>', '<style>\n' + CSS + '</style>\n</head>', 'inject css')

JS = r"""
/* ۱٫۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها (سامانه مدیریت غذا ← گزارشها).
   نمای مستقل/ناوبری جداگانهای اضافه نمیشود؛ مجوز همان reports/food.reports است. */
(function(){
'use strict';
var FIELDS=[{key:'row',label:'ردیف'},{key:'pc',label:'کد پرسنلی'},{key:'first',label:'نام'},{key:'last',label:'نام خانوادگی'},{key:'deputy',label:'معاونت'},{key:'count',label:'تعداد غیبت'},{key:'date',label:'آخرین تاریخ'},{key:'status',label:'وضعیت غیبت'}];
var FKEY='food-ticket-absent-fields-v1',BKEY='food-ticket-absent-blocks-v1';
var S={rows:[],key:'',seq:0,loading:false,error:'',sort:{key:'pc',dir:'asc'},org:false};
function active(){return typeof view!=='undefined'&&view==='reports'&&(state.reportTab||'classic')==='absent-lunch';}
function fa(n){return typeof toFaDigits==='function'?toFaDigits(n):String(n);}
function cols(){try{var raw=localStorage.getItem(FKEY);if(raw){var k=JSON.parse(raw);var c=FIELDS.filter(function(f){return k.indexOf(f.key)>=0;});if(c.length)return c;}}catch(e){}return FIELDS.slice(0,6);}
function saveCols(a){try{localStorage.setItem(FKEY,JSON.stringify(a.map(function(f){return f.key;})));}catch(e){}}
function blocks(){try{var b=Number(localStorage.getItem(BKEY));return b===2||b===3?b:1;}catch(e){return 1;}}
function setBlocks(b){try{localStorage.setItem(BKEY,String(b));}catch(e){}}
function plain(p,k,i){return k==='row'?fa(i+1):k==='status'?p.status:String(p[k]==null?'':p[k]);}
function cell(p,k,i){
 if(k==='row')return fa(i+1);
 if(k==='count')return '<b>'+fa(p.count)+'</b>';
 if(k==='status')return '<span class="alr-pill'+(p.declared?' declared':'')+'" title="'+esc(p.status)+'">'+esc(p.short)+'</span>';
 return esc(p[k]||'—');
}
function sorted(rows){
 var pairs=rows.map(function(r,i){return{r:r,i:i};}),s=S.sort;
 if(s.key)pairs.sort(function(a,b){
  var x=s.key==='row'?a.i:a.r[s.key],y=s.key==='row'?b.i:b.r[s.key];
  var cmp=(s.key==='row'||s.key==='count')?(Number(x)-Number(y)):String(x==null?'':x).localeCompare(String(y==null?'':y),'fa',{numeric:true,sensitivity:'base'});
  return cmp?(s.dir==='desc'?-cmp:cmp):a.i-b.i;
 });
 return pairs.map(function(o){return o.r;});
}
function percents(rows,c,b){
 var w=c.map(function(col){var mx=String(col.label).length;rows.forEach(function(p,i){mx=Math.max(mx,String(plain(p,col.key,i)).length);});return Math.max(5,Math.min(30,mx+2));});
 var all=[];for(var i=0;i<b;i++)all=all.concat(w);
 var total=all.reduce(function(s,x){return s+x;},0);
 return all.map(function(x){return (x/total*100).toFixed(3)+'%';});
}
function table(rows,b,sortable){
 var c=cols();if(!c.length)return '<div class="notice">حداقل یک فیلد را انتخاب کنید.</div>';
 b=Math.max(1,Math.min(3,Number(b)||1));
 var cg='<colgroup>'+percents(rows,c,b).map(function(w){return '<col style="width:'+w+'">';}).join('')+'</colgroup>';
 var head='<tr>';
 for(var k=0;k<b;k++)head+=c.map(function(f,j){
  var on=S.sort.key===f.key,mark=on?(S.sort.dir==='desc'?' ▼':' ▲'):'';
  var label=sortable?'<button type="button" class="alr-sort" data-absent-lunch-sort="'+f.key+'">'+esc(f.label+mark)+'</button>':esc(f.label);
  return '<th class="'+(j===0&&k>0?'alr-block-start':'')+'">'+label+'</th>';
 }).join('');
 head+='</tr>';
 var lines=Math.ceil(rows.length/b),body='';
 for(var line=0;line<lines;line++){
  var tds='';
  for(var k2=0;k2<b;k2++){
   var idx=line*b+k2,p=rows[idx];
   if(!p){tds+=c.map(function(_,j){return '<td class="'+(j===0&&k2>0?'alr-block-start':'')+'"></td>';}).join('');continue;}
   tds+=c.map(function(f,j){return '<td class="'+(j===0&&k2>0?'alr-block-start':'')+'" title="'+esc(plain(p,f.key,idx))+'">'+cell(p,f.key,idx)+'</td>';}).join('');
  }
  body+='<tr>'+tds+'</tr>';
 }
 var empty='<tr><td colspan="'+c.length*b+'" class="empty">در این بازه غایبی ثبت نشده است.</td></tr>';
 return '<div class="alr-card alr-table-wrap"><table class="alr-table">'+cg+'<thead>'+head+'</thead><tbody>'+(body||empty)+'</tbody></table></div>';
}
function groups(rows){
 var out={},order=[],map=state.__orgMap||null;
 rows.forEach(function(r){
  var g=r.deputy||'بدون معاونت سازمانی';
  if(map&&window.FoodOrgGroups&&typeof window.FoodOrgGroups.groupOf==='function'){
   var grp=window.FoodOrgGroups.groupOf(map,r);
   g=grp&&grp.label?grp.label:'بدون معاونت سازمانی';
  }
  if(!out[g]){out[g]=[];order.push(g);}out[g].push(r);
 });
 return order.map(function(g){return{name:g,rows:out[g]};});
}
function mapped(items,orgMap){
 var by={},order=[];
 (items||[]).forEach(function(it){
  var pc=String(it.pc||''),nat=String(it.nat||''),key=pc||nat||String(it.full_name||'');if(!key)return;
  if(!by[key]){by[key]={pc:pc||'—',nat:nat,first:String(it.first||''),last:String(it.last||''),deputy:'بدون معاونت سازمانی',count:0,date:'',status:'غایب — سفارش دارد، فیش چاپ نشده',short:'غایب',declared:false};order.push(by[key]);}
  var r=by[key];r.count+=1;
  var d=String(it.foodDate||'');if(d>r.date)r.date=d;
  if(it.declared_absent){r.declared=true;r.status='غایب اعلامشده — فیش چاپ نشده';r.short='غایب اعلامشده';}
 });
 var map=orgMap||state.__orgMap||null;
 if(map&&window.FoodOrgGroups&&typeof window.FoodOrgGroups.groupOf==='function')order.forEach(function(r){var g=window.FoodOrgGroups.groupOf(map,r);r.deputy=g&&g.label?g.label:'بدون معاونت سازمانی';});
 return order;
}
function rangeInputs(){var r=state.report||{},f=r.from||faDate(reportRange().from);return{from:f,to:r.to||f};}
function load(force){
 var r=reportRange(),key=r.from+'|'+r.to;
 if(!force&&S.key===key)return Promise.resolve();
 S.key=key;if(!serviceMode)return Promise.resolve();
 S.loading=true;S.error='';var seq=++S.seq;
 var apiP=api('/api/absent?from='+encodeURIComponent(r.from)+'&to='+encodeURIComponent(r.to));
 var mapP=Promise.resolve(null);
 try{if(window.FoodOrgGroups&&typeof loadOrgMap==='function')mapP=Promise.resolve(loadOrgMap());}catch(e){}
 return Promise.all([apiP,mapP.catch(function(){return null;})]).then(function(res){
  if(seq!==S.seq||S.key!==key)return;
  var x=res[0]||{};
  S.rows=mapped(Array.isArray(x.items)?x.items:[],res[1]||state.__orgMap||null);
  S.loading=false;if(active())render();
 }).catch(function(e){if(seq!==S.seq)return;S.rows=[];S.loading=false;S.error=e.message||'خطا در دریافت گزارش غایبین ناهار';if(active())render();});
}
function html(){
 var ri=rangeInputs(),b=blocks(),c=cols(),rows=sorted(S.rows),people=rows.length;
 var portions=rows.reduce(function(s,r){return s+r.count;},0);
 var chosen={};c.forEach(function(f){chosen[f.key]=true;});
 var range='<form class="card form-grid" id="absent-lunch-filter"><label class="field">از تاریخ (شمسی)<input name="from" value="'+esc(ri.from)+'" placeholder="۱۴۰۵/۰۸/۲۷"></label><label class="field">تا تاریخ (شمسی)<input name="to" value="'+esc(ri.to)+'" placeholder="۱۴۰۵/۰۸/۲۸"></label><div class="form-actions"><button class="btn primary">نمایش گزارش</button><button type="button" class="btn ghost" data-action="absent-lunch-today">امروز</button></div></form>';
 var stats='<div class="grid section-gap"><div class="card stat"><span class="label">مجموع پرس</span><strong>'+fa(portions)+' پرس</strong></div><div class="card stat"><span class="label">افراد یکتا</span><strong>'+fa(people)+' نفر</strong></div></div>';
 var chips='<div class="card section-gap"><div class="alr-fields"><strong class="alr-blocks-label">فیلدهای گزارش:</strong>'+FIELDS.map(function(f){return '<label class="alr-chip"><input type="checkbox" data-absent-lunch-field="'+f.key+'" '+(chosen[f.key]?'checked':'')+'><span>'+esc(f.label)+'</span></label>';}).join('')+'</div>'+
  '<div class="alr-blocks"><span class="alr-blocks-label">بلوک در هر ردیف:</span>'+[1,2,3].map(function(n){return '<button type="button" class="alr-block-btn '+(b===n?'active':'')+'" data-absent-lunch-block="'+n+'" aria-pressed="'+(b===n)+'">'+fa(n)+'</button>';}).join('')+'<span class="alr-hint">لیست همیشه تمامعرض صفحه است؛ فاصلهٔ فیلدها خودکار تنظیم میشود.</span></div></div>';
 var views='<div class="agg-tabs section-gap" role="group" aria-label="نحوهٔ نمایش غایبین ناهار"><button type="button" class="agg-tab '+(!S.org?'active':'')+'" data-absent-lunch-view="off">فهرست یکجا</button><button type="button" class="agg-tab '+(S.org?'active':'')+'" data-absent-lunch-view="on">بر اساس معاونت</button></div>';
 var notice=!serviceMode?'<div class="notice warn">برای گزارش واقعی، پنل باید متصل به سرویس باشد.</div>':S.error?'<div class="notice warn">خطا: '+esc(S.error)+'؛ بازخوانی کنید.</div>':S.loading?'<div class="notice">در حال دریافت گزارش…</div>':'';
 var list;
 if(S.org){
  var gs=groups(rows),map=state.__orgMap||null;
  if(!map&&window.FoodOrgGroups&&!window.__alrOrgTried){window.__alrOrgTried=true;loadOrgMap().then(function(){if(active())render();});}
  list=gs.map(function(g){return '<section class="alr-group"><div class="alr-group-head"><h3>'+esc(g.name)+'</h3><span>('+fa(g.rows.length)+' نفر · '+fa(g.rows.reduce(function(s,r){return s+r.count;},0))+' پرس)</span></div>'+table(g.rows,b,false)+'</section>';}).join('')||'<div class="notice">در این بازه غایبی ثبت نشده است.</div>';
 }else list=table(rows,b,true);
 var actions='<button class="btn primary" data-action="absent-lunch-export">خروجی XLSX</button><button class="btn ghost" data-action="absent-lunch-print">چاپ فهرست</button><button class="btn ghost" data-action="absent-lunch-refresh">بازخوانی</button>';
 return '<section>'+heading('گزارش غایبین ناهار','فهرست سفارشهای ناهاری که فیش دریافت نکردهاند؛ هر ردیف معادل یک پرس است.',actions)+range+stats+notice+chips+views+list+'<div class="alr-info section-gap">بلوک: '+fa(b)+' · فیلدها: '+fa(c.length)+' · ستونها: '+fa(c.length*b)+' · عرض لیست ۱۰۰٪ صفحه</div></section>';
}
function exportXlsx(){
 if(S.loading||S.error){toast('گزارش معتبر دریافت نشده است.');return;}
 var helper=window.ITSM_LIST_SORT;
 if(!helper||typeof helper.exportXlsx!=='function'){toast('ابزار ساخت XLSX بارگذاری نشده است.');return;}
 var c=cols(),rows=sorted(S.rows),r=reportRange();
 var name='گزارش-غایبین-ناهار-'+r.from+(r.from!==r.to?'_'+r.to:'');
 var title='گزارش غایبین ناهار — '+faDate(r.from)+(r.from!==r.to?' تا '+faDate(r.to):'');
 var token=window.ITSM_CSRF||window.FOOD_TICKET_CSRF||'';
 fetch('index.php?action=export_view_xlsx',{method:'POST',credentials:'same-origin',
  headers:{'Content-Type':'application/json','X-CSRF-Token':token},
  body:JSON.stringify({title:title,filename:name,headers:c.map(function(f){return f.label;}),
   rows:rows.map(function(p,i){return c.map(function(f){return plain(p,f.key,i);});})})})
  .then(function(res){if(!res.ok)throw new Error('export failed');return res.blob();})
  .then(function(blob){
   var url=URL.createObjectURL(blob),a=document.createElement('a');
   a.href=url;a.download=name+'.xlsx';document.body.appendChild(a);a.click();a.remove();
   setTimeout(function(){URL.revokeObjectURL(url);},1000);
   toast('فایل XLSX ساخته شد ('+fa(rows.length)+' ردیف).');
  }).catch(function(){toast('ساخت XLSX ناموفق بود؛ دوباره تلاش کنید.');});
}
function printSections(){
 var rows=sorted(S.rows);
 if(!S.org)return[{name:'',rows:rows}];
 return groups(rows).map(function(g){return{name:g.name,rows:g.rows};});
}
function preparePrint(){
 var b=blocks(),c=cols(),secs=printSections();
 var area=document.getElementById('absentLunchPrint');
 if(!area){area=document.createElement('div');area.id='absentLunchPrint';document.body.appendChild(area);}
 area.innerHTML='<h1>گزارش غایبین ناهار</h1><div class="alr-tab-title">'+(S.org?'بر اساس معاونت':'فهرست یکجا')+'</div>'+
  (secs.map(function(s){return '<div class="alr-group">'+(s.name?'<h2>'+esc(s.name)+'</h2>':'')+table(s.rows,b,false)+'</div>';}).join('')||'<p>موردی برای نمایش نیست.</p>');
 var total=c.length*b,lines=secs.reduce(function(a,s){return a+Math.ceil(s.rows.length/b);},0);
 var dyn=document.getElementById('alrPrintDyn');
 if(!dyn){dyn=document.createElement('style');dyn.id='alrPrintDyn';document.head.appendChild(dyn);}
 dyn.textContent='@page{size:A4 '+(total>8||lines>28?'landscape':'portrait')+';margin:10mm}#absentLunchPrint .alr-table th,#absentLunchPrint .alr-table td{font-size:'+(total>12?8:total>8?9:10)+'px}';
 document.body.classList.add('absent-lunch-printing');
}
document.addEventListener('click',function(e){
 var t=e.target;if(!t||!t.closest)return;
 var v=t.closest('[data-absent-lunch-view]');
 if(v){e.preventDefault();e.stopImmediatePropagation();S.org=v.dataset.absentLunchView==='on';if(S.org&&window.FoodOrgGroups)loadOrgMap().then(function(){if(active())render();});render();return;}
 var b=t.closest('[data-absent-lunch-block]');
 if(b){e.preventDefault();e.stopImmediatePropagation();setBlocks(Number(b.dataset.absentLunchBlock));render();return;}
 var s=t.closest('[data-absent-lunch-sort]');
 if(s){e.preventDefault();e.stopImmediatePropagation();var k=s.dataset.absentLunchSort;if(S.sort.key!==k)S.sort={key:k,dir:'asc'};else S.sort.dir=S.sort.dir==='asc'?'desc':'asc';render();return;}
 var a=t.closest('[data-action]');if(!a)return;var n=a.dataset.action;
 if(String(n).indexOf('absent-lunch-')!==0)return;
 e.preventDefault();e.stopImmediatePropagation();
 if(n==='absent-lunch-export')exportXlsx();
 else if(n==='absent-lunch-print'){preparePrint();window.print();}
 else if(n==='absent-lunch-refresh'){S.key='';load(true);}
 else if(n==='absent-lunch-today'){var d=todayJalali();state.report=Object.assign({},state.report||{},{from:d,to:d});persist();S.key='';load(true);render();}
},true);
document.addEventListener('change',function(e){
 var box=e.target;if(!box||!box.dataset||!box.dataset.absentLunchField)return;
 var boxes=Array.prototype.slice.call(document.querySelectorAll('[data-absent-lunch-field]')),chosen=boxes.filter(function(x){return x.checked;}).map(function(x){return x.dataset.absentLunchField;});
 if(!chosen.length){box.checked=true;toast('حداقل یک فیلد باید انتخاب شود.');return;}
 saveCols(FIELDS.filter(function(f){return chosen.indexOf(f.key)>=0;}));render();
},true);
document.addEventListener('submit',function(e){
 if(!e.target||e.target.id!=='absent-lunch-filter')return;
 e.preventDefault();e.stopImmediatePropagation();
 var d=new FormData(e.target),f=String(d.get('from')||'').trim(),t=String(d.get('to')||'').trim();
 if(!f||!t||isoDateFor(f)>isoDateFor(t)){toast('بازهٔ تاریخ معتبر نیست.');return;}
 state.report=Object.assign({},state.report||{},{from:f,to:t});persist();S.key='';load(true);render();
},true);
window.addEventListener('afterprint',function(){document.body.classList.remove('absent-lunch-printing');});
window.addEventListener('beforeprint',function(){if(active())preparePrint();});
window.AbsentLunchReport={html:html,load:load,preparePrint:preparePrint,active:active};
if(active())load(false);
})();
"""
html = must_replace(html, '</body></html>', '<script>\n' + JS + '\n</script>\n</body></html>', 'inject js')

# ---------------------------------------------------------------- 3) help guide
guide = read('help-guide.php')
guide_section = """
<h3 data-kw="گزارش مستقل غایبین ناهار غایبین ناهار تب غایبین فیش نگرفتند سفارش دارند بر اساس معاونت بلوک در هر ردیف">۶.۹-ب تب «غایبین ناهار» در صفحهٔ گزارشها</h3>
<p>گزارش <b>مستقل غایبین ناهار</b> از منوی «سامانه مدیریت غذا ← گزارشها» و از <b>تب تازهٔ «غایبین ناهار»</b> در همان صفحه باز میشود؛ نمای جداگانهای به ناوبری اضافه نشده و مجوز آن همان <code>food.reports</code> است. بازهٔ شمسی را انتخاب کنید، سپس فیلدها و «بلوک در هر ردیف» (۱/۲/۳) را تنظیم کنید. لیست همیشه تمامعرض صفحه است و فاصلهٔ فیلدها بهصورت خودکار تنظیم میشود. فیلد «نوع غذا» در گزارشهای دارای بازه حذف شده است.</p>
<p>دو نمای «فهرست یکجا» و «<b>بر اساس معاونت</b>» وجود دارد؛ در نمای معاونتی، افراد بر اساس چارت سازمانی گروهبندی میشوند و «غایب اعلامشده» با برچسب جدا مشخص است. خروجی همیشه <b>XLSX</b> واقعی است و «چاپ فهرست» فقط عنوان «گزارش غایبین ناهار»، عنوان تب و خود لیست را چاپ میکند (بدون هدر، منو و فرمها).</p>
"""
guide = must_replace(guide, '<h2 id="support">', guide_section + '\n<h2 id="support">', 'help guide')

# ---------------------------------------------------------------- 4) chatbot
chat = read('chatbot.php')
chat_intent = """
    // ۱.۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها (نه نمای مستقل).
    if (chatbot_has_any($norm, ['غایبین', 'غایب', 'فیش نگرفت', 'غذا نگرفت'])
        && chatbot_has_any($norm, ['گزارش', 'تب', 'کجاست', 'کجا', 'ببینم', 'نمایش', 'لیست'])) {
        $absentAnswer = 'گزارش غایبین ناهار دیگر یک «نوع گزارش» جداگانه نیست. در «سامانه مدیریت غذا ← گزارشها» '
            . 'تب تازهٔ «غایبین ناهار» را باز کنید: بازهٔ شمسی، انتخاب فیلدها، بلوک در هر ردیف، '
            . 'نمای یکجا یا بر اساس معاونت، خروجی XLSX و چاپ فقطِ لیست همانجاست. '
            . 'منطق گزارش: سفارش ناهار ثبت شده ولی فیش چاپشده برای همان روز وجود ندارد.';
        return chatbot_reply($user, $question, $absentAnswer, true, null, 'food_absent_lunch_report', 0.0, [
            'url' => 'index.php?page=food-ticket',
            'label' => 'رفتن به گزارش غایبین ناهار',
        ]);
    }

"""
chat = must_replace(chat, '    $flow = chatbot_flow_active();', chat_intent + '    $flow = chatbot_flow_active();', 'chatbot intent')

# ---------------------------------------------------------------- 5) docs / tests
CHANGE = """# گزارش تغییرات ۱٫۶۳٫۳ — تب «غایبین ناهار» در صفحهٔ گزارشها

- نسخه: **1.63.3** · پیشنیاز: **1.63.2** · تاریخ: **2026-11-19** · نسخهٔ پنل: **1.37.57**
- مهاجرت پایگاه داده: **ندارد** (فقط SELECT؛ هیچ جدول/ستون/دادهای حذف یا تغییر نمیکند)
- بسته: `ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip`

## محل استقرار (مهم)

گزارش **داخل منوی «سامانه مدیریت غذا ← گزارشها»** قرار میگیرد: به `REPORT_TABS` همان صفحه
یک تب تازه به نام **«غایبین ناهار»** اضافه شده و محتوای گزارش در همان تب گنجانده میشود.
**هیچ نمای مستقل / آیتم ناوبری تازهای** ساخته نشده است؛ مجوز همان `'reports' => food.reports`
موجود در `$fpViews` است و گزینهٔ قدیمی «نوع گزارش = غایبین» از فرم گزارشگیری حذف شد
(حالت ذخیرهشدهٔ قدیمی کاربران بهصورت خودکار به تب تازه هدایت میشود).

## چه چیزهایی اضافه/اصلاح شد

| بخش | تغییر |
|---|---|
| `food-ticket.php` | جایگزینی `food_ticket_api_absent()` با نسخهٔ بستهٔ سورس: هویت `pc:` کنار `nat:`، ردیفهای فقط کد پرسنلی، `users.employee_number` برای «غایب اعلامشده»، فیلدهای `full_name` / `status` / `declared_absent`. route و مجوز `GET /api/absent` بدون تغییر. |
| `food-ticket.php` / `index.html` | `FOOD_TICKET_PANEL_VERSION` و `PANEL_VERSION` به `1.37.57` (هماهنگ، بدون پین تازهٔasset). |
| `food-ticket-web/index.html` | تب تازه در `REPORT_TABS`، dispatch در `reportView()`، بارگذاری `AbsentLunchReport.load()` در `render()`، حذف ۲ گزینهٔ قدیمی `option value="absent"`، استایل و ماژول inline (بدون فایل asset تازه ⇒ نیازی به بازنویسی هاست/پین کش نیست). |
| ماژول تب | بازهٔ شمسی، ۸ فیلد قابل انتخاب (بدون «نوع غذا»)، «بلوک در هر ردیف» ۱/۲/۳، نمای یکجا / بر اساس معاونت، مرتبسازی، آمار پرس/نفر، خروجی **XLSX واقعی** از همان action رسمی `index.php?action=export_view_xlsx` (نه CSV)، چاپ فقطِ عنوان «گزارش غایبین ناهار» + عنوان تب + لیست. |
| `help-guide.php` | بخش «گزارش مستقل غایبین ناهار» + «بر اساس معاونت». |
| `chatbot.php` | نیت `food_absent_lunch_report` با لینک «رفتن به گزارش غایبین ناهار». |

## قواعد رعایتشده

- لیست **همیشه ۱۰۰٪ عرض صفحه** است (`table-layout:fixed` + درصد ستونها). با ۱ بلوک کل عرض پوشیده میشود؛
  با ۲/۳ بلوک فاصلهٔ فیلدها بر اساس حجم محتوای همان ستون بهصورت خودکار کم/زیاد میشود.
- خروجی همهٔ گزارشها **XLSX** است (نه CSV). چاپ فقط لیست + عنوانهاست و با کلاس
  `body.absent-lunch-printing` محدود شده، پس چاپ سایر گزارشها دستنخورده میماند.
- تم دقیقاً **تم اصلی سامانه**: ماژول فقط از توکنهای `food-ticket-theme.css` / استایل پایهٔ پنل
  (`--food-white`, `--food-paper`, `--food-line`, `--food-shadow`, `--teal`, `--teal-light`, `--navy`, `--ink`, `--muted`) استفاده میکند.
- بدون `DROP` / `DELETE` / مهاجرت؛ بدون تغییر شاخه، بدون استقرار production.

## تستها

- `tests/js/food-absent-lunch-tab.test.js` — قرارداد تب، حذف گزینهٔ قدیمی، نسخهها، بکاند، راهنما/چتبات، XLSX و چاپ.
- `tests/test_food_absent_lunch_tab_static.py` — همان قرارداد بهصورت static (بدون نیاز به Node).
- `tests/test_food_absent_lunch_backend_static.py` — هویت `pc:`، `employee_number`، `declared_absent`.

## نصب

1. پشتیبان کامل از فایلها و دیتابیس بگیرید.
2. در `system-update.php` بسته را بارگذاری کنید؛ `requires_version=1.63.2` بررسی میشود.
3. پس از نصب `VERSION` باید `1.63.3` و پنل `1.37.57` باشد (`Ctrl+F5` برای کش مرورگر).
4. منوی «سامانه مدیریت غذا ← گزارشها ← غایبین ناهار» را باز کنید و تست دود: بازه، بلوک ۱/۲/۳، XLSX، چاپ.

> این بسته بهصورت «کاندیدای مستقیم» ارائه شده و عمداً به `downloads/index.html` لینک نشده تا
> منبع حقیقت نسخهٔ مخزن (`VERSION`) بدون تأیید انتشار تغییر نکند.
"""

START = """بستهٔ بهروزرسانی ۱٫۶۳٫۳ — تب «غایبین ناهار»
================================================

پیشنیاز: نسخهٔ ۱٫۶۳٫۲ · مهاجرت دیتابیس: ندارد · نسخهٔ پنل: ۱٫۳۷٫۵۷

۱) پیش از هر کاری پشتیبان کامل بگیرید (فایلها + دیتابیس).
۲) ZIP را از طریق «سامانه ← بهروزرسانی» (system-update.php) بارگذاری کنید.
۳) بعد از نصب: VERSION = 1.63.3 و پنل = 1.37.57؛ مرورگر را Ctrl+F5 کنید.
۴) محل گزارش: سامانه مدیریت غذا ← گزارشها ← تب «غایبین ناهار».
   نمای مستقل/منوی جداگانه اضافه نشده؛ مجوز همان food.reports است.

فایلهای تغییریافته: VERSION, food-ticket.php, food-ticket-web/index.html,
help-guide.php, chatbot.php + گزارش تغییرات و تستها.
"""

JS_TEST = r"""// قرارداد استقرار تب «غایبین ناهار» — اجرا: node tests/js/food-absent-lunch-tab.test.js
'use strict';
const fs = require('fs'), path = require('path'), assert = require('assert');
const root = path.resolve(__dirname, '../..');
const read = f => fs.readFileSync(path.join(root, f), 'utf8');
const html = read('food-ticket-web/index.html');
const php = read('food-ticket.php');
const guide = read('help-guide.php');
const bot = read('chatbot.php');

assert(html.includes("['absent-lunch','غایبین ناهار']"), 'تب تازه به REPORT_TABS صفحهٔ گزارشها اضافه شده');
assert(html.includes("['reconciliation','تطبیق خرید و توزیع'],['absent-lunch','غایبین ناهار']]"), 'آرایهٔ REPORT_TABS معتبر است');
assert(html.includes("tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html()"), 'reportView محتوای تب را رندر میکند');
assert(html.includes("window.AbsentLunchReport.load(false)"), 'render لودر تب را صدا میزند');
assert(!html.includes('option value="absent"'), 'گزینهٔ قدیمی نوع گزارش حذف شده');
assert(!html.includes("['absent-report','غایبین ناهار']"), 'نمای مستقل/ناوبری جداگانه اضافه نشده');
assert(html.includes("state.reportTab='absent-lunch'"), 'حالت ذخیرهشدهٔ قدیمی به تب تازه هدایت میشود');
assert(html.includes("PANEL_VERSION='1.37.57'") && php.includes("FOOD_TICKET_PANEL_VERSION = '1.37.57'"), 'نسخههای پنل هماهنگاند');
assert(html.includes('window.AbsentLunchReport={html:html,load:load'), 'ماژول تب تعریف شده');
assert(html.includes('function mapped(items,orgMap)'), 'نگاشت افراد نقشهٔ سازمانی را همراه گزارش میگیرد');
assert(html.includes('map=state.__orgMap||null') && html.includes('window.FoodOrgGroups.groupOf(map,r)'), 'گروهبندی معاونت نقشهٔ تازه را دوباره میخواند');
assert(html.includes('window.AbsentLunchReport?window.AbsentLunchReport.html()'), 'استارتاپ قبل از تعریف ماژول امن است');
assert(html.includes('if(active())load(false)'), 'تب ذخیرهشده در استارتاپ بارگذاری میشود');
assert(html.includes('data-absent-lunch-block=') && html.includes('[1,2,3].map(function(n)'), 'کنترل بلوک ۱/۲/۳ برگشته');
assert(!html.includes('data-absent-lunch-field="food"'), 'فیلد نوع غذا حذف شده');
assert(html.includes('width:100%!important') && html.includes('table-layout:fixed'), 'لیست همیشه تمامعرض صفحه');
assert(html.includes('body.absent-lunch-printing'), 'چاپ محدود به همین تب است');
assert(html.includes("'food.reports'") || php.includes("'reports' => $fpCan('food.reports')"), 'مجوز همان reports/food.reports');
assert(guide.includes('گزارش مستقل غایبین ناهار') && guide.includes('بر اساس معاونت'), 'راهنما بهروز شده');
assert(bot.includes("'food_absent_lunch_report'") && bot.includes("'رفتن به گزارش غایبین ناهار'"), 'چتبات بهروز شده');
assert(php.includes("SELECT id FROM users WHERE employee_number = ?") && php.includes("'declared_absent'"), 'بکاند هویت کد پرسنلی و غیبت اعلامشده دارد');
console.log('food-absent-lunch-tab: OK');
"""

PY_TEST = '''#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان استاتیک ۱٫۶۳٫۳ — تب «غایبین ناهار» داخل صفحهٔ گزارشها."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HTML = (ROOT / 'food-ticket-web/index.html').read_text(encoding='utf-8')
PHP = (ROOT / 'food-ticket.php').read_text(encoding='utf-8')
GUIDE = (ROOT / 'help-guide.php').read_text(encoding='utf-8')
BOT = (ROOT / 'chatbot.php').read_text(encoding='utf-8')


def test_tab_is_inside_reports_page():
    assert "['absent-lunch','غایبین ناهار']" in HTML
    assert "tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html()" in HTML
    assert "window.AbsentLunchReport.load(false)" in HTML
    assert "['absent-report','غایبین ناهار']" not in HTML
    assert "state.reportTab='absent-lunch'" in HTML


def test_legacy_report_type_removed_and_redirected():
    assert 'option value="absent"' not in HTML
    assert "state.report.type==='absent'" in HTML


def test_panel_versions_match():
    assert "PANEL_VERSION='1.37.57'" in HTML
    assert "FOOD_TICKET_PANEL_VERSION = '1.37.57'" in PHP


def test_blocks_full_width_and_no_food_field():
    assert 'data-absent-lunch-block="' in HTML and '[1,2,3].map(function(n)' in HTML
    assert 'data-absent-lunch-field="food"' not in HTML
    assert 'width:100%!important' in HTML and 'table-layout:fixed' in HTML
    assert 'function percents(' in HTML and 'width:100%!important' in HTML
    assert 'function mapped(items,orgMap)' in HTML
    assert 'map=state.__orgMap||null' in HTML and 'window.FoodOrgGroups.groupOf(map,r)' in HTML


def test_print_is_scoped_to_this_tab():
    assert 'body.absent-lunch-printing' in HTML
    assert '#absentLunchPrint h1' in HTML
    assert 'گزارش غایبین ناهار' in HTML


def test_xlsx_and_permission():
    assert 'ITSM_LIST_SORT' in HTML
    assert "'reports' => $fpCan('food.reports')" in PHP
    assert 'export_view_xlsx' in (ROOT / 'index.php').read_text(encoding='utf-8')


def test_help_and_chatbot():
    assert 'گزارش مستقل غایبین ناهار' in GUIDE
    assert 'بر اساس معاونت' in GUIDE
    assert "'food_absent_lunch_report'" in BOT
    assert "'رفتن به گزارش غایبین ناهار'" in BOT
'''

PY_BACKEND = '''#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""نگهبان بکاند ۱٫۶۳٫۳ — منطق گزارش غایبین."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHP = (ROOT / 'food-ticket.php').read_text(encoding='utf-8')


def test_backend_identity_and_declared_absence():
    start = PHP.index('function food_ticket_api_absent(')
    end = PHP.index('\\nfunction ', start + 10)
    fn = PHP[start:end]
    assert "'nat:' . $nat" in fn and "'pc:' . $pc" in fn
    assert 'SELECT id FROM users WHERE employee_number = ?' in fn
    assert "'declared_absent'" in fn and "'status'" in fn
    assert "'full_name'" in fn
    assert "COALESCE(meal_type, 'lunch') = 'lunch'" in fn
    assert 'DROP' not in fn and 'DELETE' not in fn.upper()


def test_route_and_permission_unchanged():
    assert "$route === 'absent'" in PHP
    assert "'absent' => 'food.reports'" in PHP
    assert "FOOD_TICKET_PANEL_VERSION = '1.37.57'" in PHP
'''

# ---------------------------------------------------------------- write files
out = {
 'VERSION': VERSION + '\n',
 'food-ticket.php': backend,
 'food-ticket-web/index.html': html,
 'help-guide.php': guide,
 'chatbot.php': chat,
 'downloads/CHANGE-REPORT-FA-1.63.3.md': CHANGE,
 'tests/js/food-absent-lunch-tab.test.js': JS_TEST,
 'tests/test_food_absent_lunch_tab_static.py': PY_TEST,
 'tests/test_food_absent_lunch_backend_static.py': PY_BACKEND,
}
FILES.mkdir(parents=True, exist_ok=True)
for rel, content in out.items():
    p = FILES / rel
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(content, encoding='utf-8')

# source-folder docs (not inside the ZIP)
(PKG / 'migrations').mkdir(exist_ok=True)
(PKG / 'migrations' / 'README.md').write_text(
    '# مهاجرت\n\nاین بسته هیچ مهاجرتی ندارد؛ فقط SELECT اضافه شده و شِما تغییر نمیکند.\n', encoding='utf-8')
(PKG / 'README.md').write_text(
    '# بستهٔ ۱٫۶۳٫۳\n\nگزارش غایبین ناهار بهصورت **تب** داخل «سامانه مدیریت غذا ← گزارشها».\n'
    'ساخت دوباره: `python3 updates/2026-11-19-release-1.63.3-absent-lunch-report/build.py`\n', encoding='utf-8')
(PKG / 'CHANGE-REPORT-FA.md').write_text(CHANGE, encoding='utf-8')

manifest = {
 'name': 'food-absent-lunch-report-tab',
 'version': VERSION,
 'requires_version': REQUIRES,
 'date': DATE,
 'title': 'تب «غایبین ناهار» داخل صفحهٔ گزارشهای سامانه مدیریت غذا',
 'panel_version': PANEL_VERSION,
 'migrations': [],
 'files': sorted(out.keys()),
 'sha256': {rel: sha256(content.encode('utf-8')) for rel, content in out.items()},
 'description': 'Adds the absent-lunch report as a tab inside the food reports page (no standalone view).',
 'notes': 'پیشنیاز ۱٫۶۳٫۲؛ مهاجرت دیتابیس ندارد. پشتیبان و نصب آزمایشی الزامی. '
          'گزارش قدیمی «نوع گزارش=غایبین» حذف و حالت ذخیرهشده به تب تازه هدایت میشود. '
          'نسخهٔ پنل ۱٫۳۷٫۵۷؛ پس از نصب Ctrl+F5 کنید.',
}
(PKG / 'manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

# ---------------------------------------------------------------- build ZIP
DOWNLOADS.mkdir(exist_ok=True)
with zipfile.ZipFile(ZIP_PATH, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as z:
    z.writestr('manifest.json', json.dumps(manifest, ensure_ascii=False, indent=2) + '\n')
    for rel, content in sorted(out.items()):
        z.writestr('files/' + rel, content.encode('utf-8'))

# verify like the installer does
with zipfile.ZipFile(ZIP_PATH) as z:
    names = z.namelist()
    assert 'manifest.json' in names
    actual = sorted(n[6:] for n in names if n.startswith('files/'))
    assert actual == sorted(out.keys()), (actual, sorted(out.keys()))
    loaded = json.loads(z.read('manifest.json'))
    assert loaded['version'] == VERSION and loaded['requires_version'] == REQUIRES
    for rel, h in loaded['sha256'].items():
        assert sha256(z.read('files/' + rel)) == h, rel
    assert not any(n.startswith('migrations/') for n in names)

checksum = sha256(ZIP_PATH.read_bytes())
(ZIP_PATH.with_suffix(ZIP_PATH.suffix + '.sha256')).write_text(checksum + '  ' + ZIP_PATH.name + '\n', encoding='utf-8')
(DOWNLOADS / 'CHANGE-REPORT-FA-1.63.3.md').write_text(CHANGE, encoding='utf-8')

print('ZIP:', ZIP_PATH.name, ZIP_PATH.stat().st_size, 'bytes')
print('SHA256:', checksum)
print('files:', len(out))
