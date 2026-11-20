/* ۱٫۶۳٫۳ — تب «غایبین ناهار»، نسخهٔ نهایی تأییدشده (۳ تب + چارت + چاپ خالص + انتخاب چندمعاونتی).
   داخل «سامانه مدیریت غذا ← گزارشها»؛ نه نمای مستقل، نه تغییر route/مجوز/بکاند. */
(function(){
'use strict';
var FIELDS=[{key:'row',label:'ردیف'},{key:'pc',label:'کد پرسنلی'},{key:'first',label:'نام'},{key:'last',label:'نام خانوادگی'},
 {key:'dep',label:'معاونت'},{key:'count',label:'تعداد غیبت'},{key:'food',label:'نوع غذا'},{key:'jal',label:'تاریخ غیبت'},{key:'status',label:'وضعیت غیبت'}];
var TAB_FIELDS={flat:['row','pc','first','last','dep','count','jal','status'],dept:['row','pc','first','last','dep','count','status'],detail:['row','pc','first','last','dep','food','jal','status']};
var FKEY='food-ticket-absent-fields-v2',BKEY='food-ticket-absent-blocks-v1';
var PAL=['var(--teal)','var(--navy-2)','var(--gold)','var(--danger)','#4f91c6','#8a63c9','#d9812b'];
var S={rows:[],key:'',seq:0,loading:false,error:'',tab:'detail',blocks:1,metric:'people',chart:true,query:'',deps:new Set(),
  fieldsByTab:{flat:new Set(TAB_FIELDS.flat),dept:new Set(TAB_FIELDS.dept),detail:new Set(TAB_FIELDS.detail)},
  sort:{key:'iso',dir:'asc'}};
try{var raw=localStorage.getItem(FKEY);if(raw){var saved=JSON.parse(raw);Object.keys(TAB_FIELDS).forEach(function(t){if(Array.isArray(saved[t])){var v=saved[t].filter(function(k){return TAB_FIELDS[t].indexOf(k)>=0});if(v.length)S.fieldsByTab[t]=new Set(v)}})}var bb=Number(localStorage.getItem(BKEY));if(bb===2||bb===3)S.blocks=bb;}catch(e){}
function saveFields(){try{var o={};Object.keys(TAB_FIELDS).forEach(function(t){o[t]=Array.from(S.fieldsByTab[t])});localStorage.setItem(FKEY,JSON.stringify(o))}catch(e){}}
function saveBlocks(){try{localStorage.setItem(BKEY,String(S.blocks))}catch(e){}}
function active(){return typeof view!=='undefined'&&view==='reports'&&(state.reportTab||'classic')==='absent-lunch';}
function syncIndeterminate(){var all=document.querySelector('[data-alr-all]');if(!all)return;var n=allDeps().length;all.checked=S.deps.size===n&&n>0;all.indeterminate=S.deps.size>0&&S.deps.size<n}
function rerender(){if(typeof render==='function')render();setTimeout(syncIndeterminate,0)}
function fa(n){return typeof toFaDigits==='function'?toFaDigits(n):String(n);}
function esc2(s){return typeof esc==='function'?esc(s):String(s==null?'':s).replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]});}
function jal(iso){try{return typeof faDate==='function'?faDate(iso):iso}catch(e){return iso}}
function depColor(name){return PAL[Math.max(0,allDeps().indexOf(name))%PAL.length]}
function allDeps(){var m={},o=[];S.rows.forEach(function(r){if(!m[r.dep]){m[r.dep]=1;o.push(r.dep)}});return o.sort(function(a,b){return a.localeCompare(b,'fa')})}
function selectedDeps(){return Array.from(S.deps)}
function filtered(){var q=String(S.query||'').trim(),d=selectedDeps();return S.rows.filter(function(r){
  if(d.indexOf(r.dep)<0)return false;
  if(q&&(r.first+r.last+r.pc+r.dep+r.food+r.jal).indexOf(q)<0)return false;
  return true;})}
function sorted(rows){var s=S.sort;return rows.slice().sort(function(a,b){var x=a[s.key],y=b[s.key];var c=String(x==null?'':x).localeCompare(String(y==null?'':y),'en',{numeric:true});return c?(s.dir==='desc'?-c:c):0})}
function activeFields(){return FIELDS.filter(function(f){return TAB_FIELDS[S.tab].indexOf(f.key)>=0&&S.fieldsByTab[S.tab].has(f.key)})}
function tabTitle(){return S.tab==='detail'?'تفکیک و جزئیات':(S.tab==='dept'?'بر اساس معاونت':'فهرست یکجا')}

function mapped(items,orgMap){
 var map=orgMap||state.__orgMap||null;
 return (items||[]).map(function(it){
  var declared=!!it.declared_absent;
  var r={pc:String(it.pc||it.nat||'—'),nat:String(it.nat||''),first:String(it.first||''),last:String(it.last||''),
   food:String(it.food||''),iso:String(it.foodDate||''),jal:jal(String(it.foodDate||'')),
   dep:'بدون معاونت سازمانی',deputy:'بدون معاونت سازمانی',declared:declared,
   status:declared?'غایب اعلامشده — فیش چاپ نشده':'غایب — سفارش دارد، فیش چاپ نشده',short:declared?'غایب اعلامشده':'غایب'};
  try{if(map&&window.FoodOrgGroups&&typeof window.FoodOrgGroups.groupOf==='function'){
   var g=window.FoodOrgGroups.groupOf(map,r);if(g&&g.label){r.dep=g.label;r.deputy=g.label}}}catch(e){}
  return r;});
}
function load(force){
 var r=reportRange(),key=r.from+'|'+r.to;
 if(!force&&S.key===key)return Promise.resolve();
 S.key=key;if(!serviceMode)return Promise.resolve();
 S.loading=true;S.error='';var seq=++S.seq;
 var mapP=Promise.resolve(null);
 try{if(window.FoodOrgGroups&&typeof loadOrgMap==='function')mapP=Promise.resolve(loadOrgMap());}catch(e){}
 return Promise.all([api('/api/absent?from='+encodeURIComponent(r.from)+'&to='+encodeURIComponent(r.to)),mapP.catch(function(){return null})])
  .then(function(res){
   if(seq!==S.seq||S.key!==key)return;
   S.rows=mapped(res[0]&&Array.isArray(res[0].items)?res[0].items:[],res[1]||state.__orgMap||null);
   S.loading=false;S.deps=new Set(allDeps());
   if(active())rerender();
  }).catch(function(e){if(seq!==S.seq)return;S.rows=[];S.deps=new Set();S.loading=false;S.error=e.message||'خطا در دریافت گزارش غایبین ناهار';if(active())rerender();});
}

/* ---------- چارت جمعوجور + تیک چندمعاونتی + «همه» ---------- */
function chartGroups(){
 var q=String(S.query||'').trim();
 var rows=S.rows.filter(function(r){return !q||(r.first+r.last+r.pc+r.dep+r.food+r.jal).indexOf(q)>=0});
 var m={},order=[];
 rows.forEach(function(r){if(!m[r.dep]){m[r.dep]={name:r.dep,people:{},portions:0};order.push(r.dep)}
  var g=m[r.dep];g.people[r.pc]=1;g.portions++});
 return order.map(function(n){var g=m[n];return{name:n,people:Object.keys(g.people).length,portions:g.portions}});
}
function rangeLabel(){var r=reportRange();return 'شروع بازه: '+jal(r.from)+' ← پایان بازه: '+jal(r.to)}
function daysForChart(){var out=[];try{var r=reportRange(),a=new Date(r.from+'T00:00:00'),b=new Date(r.to+'T00:00:00');for(var d=new Date(a),n=0;d<=b&&n<12;d.setDate(d.getDate()+1),n++)out.push(jal(d.toISOString().slice(0,10)).slice(-2))}catch(e){}return out}
function chartHtml(){
 var gs=chartGroups().map(function(g){return{name:g.name,v:S.metric==='people'?g.people:g.portions,people:g.people,portions:g.portions}})
  .filter(function(g){return g.v>0}).sort(function(a,b){return b.v-a.v});
 if(!gs.length)return '<div class="alr-empty">موردی یافت نشد.</div>';
 var max=Math.max.apply(null,gs.map(function(g){return g.v}))||1,total=gs.reduce(function(s,g){return s+g.v},0),mean=total/gs.length,meanPct=(mean/max*100).toFixed(2)+'%';
 var all=allDeps(),sel=selectedDeps(),allOn=sel.length===all.length&&all.length>0;
 var h='<label class="alr-all"><input type="checkbox" data-alr-all'+(allOn?' checked':'')+'> همه معاونتها</label>';
 h+='<div class="alr-plot">';
 gs.forEach(function(g,i){
  var on=sel.indexOf(g.name)>=0,w=(g.v/max*100).toFixed(2)+'%';
  h+='<div class="alr-row'+(i===0?' max':'')+(i===gs.length-1?' min':'')+(on?'':' off')+'" data-alr-row="'+esc2(g.name)+'">'+
   '<label class="alr-check"><input type="checkbox" data-alr-dep-check="'+esc2(g.name)+'"'+(on?' checked':'')+'></label>'+
   '<span class="alr-name" data-alr-dep-toggle="'+esc2(g.name)+'" title="'+esc2(g.name)+'">'+esc2(g.name)+'</span>'+
   '<span class="alr-track"><i class="alr-grid" style="right:25%"></i><i class="alr-grid" style="right:50%"></i><i class="alr-grid" style="right:75%"></i>'+
    '<i class="alr-mean" style="right:'+meanPct+'"></i><i class="alr-fill" style="width:'+w+';background:'+depColor(g.name)+'"></i></span>'+
   '<span class="alr-val">'+fa(g.v)+' '+(S.metric==='people'?'نفر':'وعده')+'</span></div>';
 });
 h+='</div>';
 var days=daysForChart();
 h+='<div class="alr-axis"><span>'+esc2(rangeLabel())+'</span>'+days.map(function(x){return '<span class="alr-tick">'+x+'</span>'}).join('')+'</div>';
 h+='<div class="alr-legend">'+gs.map(function(g,i){return '<span data-alr-dep-toggle="'+esc2(g.name)+'"><i style="background:'+depColor(g.name)+'"></i>'+esc2(g.name)+
   ' <b>'+fa(g.people)+' نفر · '+fa(g.portions)+' وعده</b>'+(i===0?' ▼ بیشترین':'')+(i===gs.length-1?' ▲ کمترین':'')+'</span>'}).join('')+
   '<span class="alr-mean-key"><i></i>میانگین '+fa(mean.toFixed(1))+'</span></div>';
 return h;
}

/* ---------- جدول ---------- */
function cell(r,f,i){if(f.key==='row')return fa(i+1);if(f.key==='count')return fa(r.count||1);
 if(f.key==='status')return '<span class="alr-pill'+(r.declared?' declared':'')+'" title="'+esc2(r.status)+'">'+esc2(r.short||'غایب')+'</span>';
 return esc2(r[f.key]==null?'—':r[f.key])}
function plain(r,f,i){return f.key==='row'?String(i+1):f.key==='count'?String(r.count||1):String(r[f.key]==null?'':r[f.key])}
function percents(rows,c,b){var w=c.map(function(f){var mx=String(f.label).length;rows.forEach(function(r,i){mx=Math.max(mx,plain(r,f,i).length)});return Math.max(5,Math.min(28,mx+2))});
 var all=[];for(var i=0;i<b;i++)all=all.concat(w);var t=all.reduce(function(s,x){return s+x},0);return all.map(function(x){return(x/t*100).toFixed(3)+'%'})}
function tableHtml(rows,b,sortable){
 var c=activeFields();if(!c.length)return '<div class="alr-empty">حداقل یک فیلد را انتخاب کنید.</div>';
 var cg='<colgroup>'+percents(rows,c,b).map(function(w){return '<col style="width:'+w+'">'}).join('')+'</colgroup>';
 var head='<tr>';for(var k=0;k<b;k++)head+=c.map(function(f,j){return '<th class="'+(j===0&&k>0?'alr-block-start':'')+'">'+
  (sortable?'<button type="button" class="alr-sort" data-alr-sort="'+f.key+'">'+esc2(f.label)+'</button>':esc2(f.label))+'</th>'}).join('');head+='</tr>';
 var lines=Math.ceil(rows.length/b),body='';
 for(var line=0;line<lines;line++){var tds='';
  for(var k2=0;k2<b;k2++){var idx=line*b+k2,r=rows[idx];
   tds+=!r?c.map(function(_,j){return '<td class="'+(j===0&&k2>0?'alr-block-start':'')+'"></td>'}).join(''):
    c.map(function(f,j){return '<td class="'+(j===0&&k2>0?'alr-block-start':'')+'" title="'+esc2(plain(r,f,idx))+'">'+cell(r,f,idx)+'</td>'}).join('')}
  body+='<tr>'+tds+'</tr>'}
 if(!body)body='<tr><td colspan="'+c.length*b+'" class="alr-empty">در این بازه غایبی ثبت نشده است.</td></tr>';
 return '<div class="alr-table-wrap"><table class="alr-table">'+cg+'<thead>'+head+'</thead><tbody>'+body+'</tbody></table></div>';
}
function rowsForMode(mode){
 var rows=sorted(filtered());if(mode==='detail')return rows;
 var m={},order=[];rows.forEach(function(r){var u=m[r.pc];
  if(!u){u=Object.assign({},r,{count:0});m[r.pc]=u;order.push(u)}
  u.count++;if(r.iso>=u.iso){u.iso=r.iso;u.jal=r.jal;u.food=r.food;u.status=r.status;u.short=r.short;u.declared=r.declared}});
 return order;
}
function groupStats(){var m={};filtered().forEach(function(r){if(!m[r.dep])m[r.dep]={people:{},portions:0};m[r.dep].people[r.pc]=1;m[r.dep].portions++});return m}
function groupedHtml(mode){
 var rows=mode==='detail'?rowsForMode('detail'):rowsForMode('dept'),stats=groupStats(),gs={},order=[];
 rows.forEach(function(r){if(!gs[r.dep]){gs[r.dep]=[];order.push(r.dep)}gs[r.dep].push(r)});
 if(!order.length)return '<div class="alr-empty">'+(S.deps.size?'موردی یافت نشد.':'هیچ معاونتی انتخاب نشده؛ تیک «همه معاونتها» را بزنید.')+'</div>';
 order.sort(function(a,b){return(stats[b]?stats[b].portions:0)-(stats[a]?stats[a].portions:0)||a.localeCompare(b,'fa')});
 return order.map(function(dep){var st=stats[dep]||{people:{},portions:0};
  return '<section class="alr-group"><div class="alr-group-head"><h4>'+esc2(dep)+' <b>'+fa(Object.keys(st.people).length)+' نفر · '+fa(st.portions)+' وعده</b></h4></div>'+
   tableHtml(gs[dep],S.blocks,false)+'</section>'}).join('');
}
function viewHtml(sortable){
 if(S.tab==='flat')return tableHtml(rowsForMode('flat'),S.blocks,sortable);
 if(S.tab==='dept')return groupedHtml('dept');
 return groupedHtml('detail');
}
function foodSummary(){var m={},o=[];filtered().forEach(function(r){if(!m[r.food]){m[r.food]=0;o.push(r.food)}m[r.food]++});
 return o.sort(function(a,b){return m[b]-m[a]}).map(function(f){return '<span>'+esc2(f||'—')+' '+fa(m[f])+'</span>'}).join('')}

/* ---------- XLSX واقعی (همان action رسمی پنل) ---------- */
function exportXlsx(){
 if(S.loading||S.error){toast('گزارش معتبر دریافت نشده است.');return}
 var c=activeFields(),rows=S.tab==='detail'?rowsForMode('detail'):rowsForMode('flat'),r=reportRange();
 var name='گزارش-غایبین-ناهار-'+r.from+(r.from!==r.to?'_'+r.to:'');
 var title='گزارش غایبین ناهار — '+jal(r.from)+(r.from!==r.to?' تا '+jal(r.to):'');
 var token=window.ITSM_CSRF||window.FOOD_TICKET_CSRF||'';
 fetch('index.php?action=export_view_xlsx',{method:'POST',credentials:'same-origin',
  headers:{'Content-Type':'application/json','X-CSRF-Token':token},
  body:JSON.stringify({title:title,filename:name,headers:c.map(function(f){return f.label}),
   rows:rows.map(function(p,i){return c.map(function(f){return plain(p,f,i)})})})})
  .then(function(res){if(!res.ok)throw new Error('export failed');return res.blob()})
  .then(function(blob){var url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=name+'.xlsx';
   document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(url)},1000);
   toast('فایل XLSX ساخته شد ('+fa(rows.length)+' ردیف).')})
  .catch(function(){toast('ساخت XLSX ناموفق بود؛ دوباره تلاش کنید.')});
}
/* ---------- چاپ خالص: فقط عنوان گزارش + عنوان تب + لیست؛ بعد از کنسل هم فرم سرجاست ---------- */
function preparePrint(){
 var area=document.getElementById('alrPrint');
 if(!area){area=document.createElement('div');area.id='alrPrint';document.body.appendChild(area)}
 area.innerHTML='<h1>گزارش غایبین ناهار</h1><div class="alr-tab-title">'+esc2(tabTitle())+'</div>'+viewHtml(false);
 var dyn=document.getElementById('alrPrintDyn');
 if(!dyn){dyn=document.createElement('style');dyn.id='alrPrintDyn';document.head.appendChild(dyn)}
 var cols=activeFields().length*S.blocks;
 dyn.textContent='@page{size:A4 '+(cols>10?'landscape':'portrait')+';margin:10mm}#alrPrint th,#alrPrint td{font-size:'+(cols>14?8:9)+'px}';
}

/* ---------- نمای تب ---------- */
function html(){
 var r=state.report||{},from=r.from||jal(reportRange().from),ri={from:from,to:r.to||from};
 var people=Object.keys(S.rows.reduce(function(m,x){m[x.pc]=1;return m},{})).length,portions=S.rows.length,deps=allDeps().length;
 var range='<form class="card alr-filter" id="alr-filter"><label class="field">از تاریخ (شمسی)<input name="from" value="'+esc2(ri.from)+'"></label><label class="field">تا تاریخ (شمسی)<input name="to" value="'+esc2(ri.to)+'"></label>'+
  '<div class="form-actions"><button class="btn primary">نمایش گزارش</button><button type="button" class="btn ghost" data-action="absent-lunch-today">امروز</button></div>'+
  '<div class="alr-chips alr-chips-row"><span class="alr-chip">مجموع پرس <b>'+fa(portions)+'</b></span><span class="alr-chip">افراد یکتا <b>'+fa(people)+'</b></span><span class="alr-chip">معاونتها <b>'+fa(deps)+'</b></span></div>'+
  '<button type="button" class="btn primary" data-action="absent-lunch-export">خروجی XLSX</button><button type="button" class="btn ghost" data-action="absent-lunch-print">چاپ فهرست</button><button type="button" class="btn ghost" data-action="absent-lunch-refresh">بازخوانی</button></form>';
 var fields='<div class="alr-fields"><span class="alr-fields-label">فیلدهای گزارش:</span>'+TAB_FIELDS[S.tab].map(function(k){
  var f=FIELDS.filter(function(x){return x.key===k})[0];
  return '<label class="alr-field-chip"><input type="checkbox" data-alr-field="'+k+'"'+(S.fieldsByTab[S.tab].has(k)?' checked':'')+'> '+esc2(f.label)+'</label>'}).join('')+
  '<span class="alr-fields-label alr-blocks-inline">بلوک در هر ردیف:</span>'+[1,2,3].map(function(n){return '<button type="button" class="alr-block-btn '+(S.blocks===n?'active':'')+'" data-alr-block="'+n+'" aria-pressed="'+(S.blocks===n)+'">'+fa(n)+'</button>'}).join('')+'</div>';
 var tabs='<div class="alr-tabs" role="tablist">'+[['flat','فهرست یکجا'],['dept','بر اساس معاونت'],['detail','تفکیک و جزئیات']].map(function(t){return '<button type="button" class="alr-tab '+(S.tab===t[0]?'active':'')+'" data-alr-tab="'+t[0]+'" role="tab" aria-selected="'+(S.tab===t[0])+'">'+t[1]+'</button>'}).join('')+'</div>';
 var tools='<div class="alr-tools"><input type="search" id="alrSearch" placeholder="جستجو: نام / کد پرسنلی / نوع غذا / معاونت" value="'+esc2(S.query)+'"><span class="alr-fields-label">نمودار:</span><span class="alr-seg"><button type="button" data-alr-metric="people" class="'+(S.metric==='people'?'active':'')+'">افراد</button><button type="button" data-alr-metric="portions" class="'+(S.metric==='portions'?'active':'')+'">وعدهها</button></span><button type="button" class="btn ghost" data-action="absent-lunch-chart">'+(S.chart?'پنهان کردن نمودار':'نمایش نمودار')+'</button></div>';
 var chart=S.chart?'<section class="alr-chart"><div class="alr-chart-head"><h3>نمودار معاونتها</h3><span class="alr-range">'+fa(daysForChart().length)+' روز</span></div><div id="alrChart">'+chartHtml()+'</div></section>':'';
 var notice=!serviceMode?'<div class="notice warn">برای گزارش واقعی، پنل باید متصل به سرویس باشد.</div>':S.error?'<div class="notice warn">خطا: '+esc2(S.error)+'؛ بازخوانی کنید.</div>':S.loading?'<div class="notice">در حال دریافت گزارش…</div>':'';
 setTimeout(syncIndeterminate,0);
 return '<section>'+heading('گزارش غایبین ناهار','','')+range+notice+fields+tabs+tools+chart+
  '<div class="alr-food">'+foodSummary()+'</div><div id="alrList">'+viewHtml(true)+'</div>'+
  '<div class="alr-info">بلوک: '+fa(S.blocks)+' · فیلدها: '+fa(activeFields().length)+' · معاونتهای انتخابشده: '+fa(selectedDeps().length)+' از '+fa(deps)+'</div></section>';
}

/* ---------- رویدادها (delegation؛ DOM بعد از render پنل عوض میشود) ---------- */
document.addEventListener('click',function(e){
 var t=e.target;if(!t||!t.closest)return;
 var tab=t.closest('[data-alr-tab]');if(tab){e.preventDefault();e.stopImmediatePropagation();S.tab=tab.dataset.alrTab;rerender();return}
 var b=t.closest('[data-alr-block]');if(b){e.preventDefault();e.stopImmediatePropagation();S.blocks=Number(b.dataset.alrBlock)||1;saveBlocks();rerender();return}
 var m=t.closest('[data-alr-metric]');if(m){e.preventDefault();e.stopImmediatePropagation();S.metric=m.dataset.alrMetric;rerender();return}
 var s=t.closest('[data-alr-sort]');if(s){e.preventDefault();e.stopImmediatePropagation();var k=s.dataset.alrSort;
  if(S.sort.key!==k)S.sort={key:k,dir:'asc'};else S.sort.dir=S.sort.dir==='asc'?'desc':'asc';rerender();return}
 var tg=t.closest('[data-alr-dep-toggle]');if(tg){e.preventDefault();e.stopImmediatePropagation();toggleDep(tg.dataset.alrDepToggle);return}
 var a=t.closest('[data-action]');if(!a)return;var n=a.dataset.action;
 if(String(n).indexOf('absent-lunch-')!==0)return;
 e.preventDefault();e.stopImmediatePropagation();
 if(n==='absent-lunch-export')exportXlsx();
 else if(n==='absent-lunch-print'){preparePrint();window.print()}
 else if(n==='absent-lunch-refresh'){S.key='';load(true)}
 else if(n==='absent-lunch-chart'){S.chart=!S.chart;rerender()}
 else if(n==='absent-lunch-today'){var d=todayJalali();state.report=Object.assign({},state.report||{},{from:d,to:d});persist();S.key='';load(true);rerender()}
},true);
function toggleDep(name){if(S.deps.has(name))S.deps.delete(name);else S.deps.add(name);rerender()}
document.addEventListener('change',function(e){
 var box=e.target;if(!box||!box.dataset)return;
 if(box.dataset.alrAll!==undefined){e.stopImmediatePropagation();
  S.deps=box.checked?new Set(allDeps()):new Set();rerender();return}
 if(box.dataset.alrDepCheck){e.stopImmediatePropagation();
  if(box.checked)S.deps.add(box.dataset.alrDepCheck);else S.deps.delete(box.dataset.alrDepCheck);rerender();return}
 if(box.dataset.alrField){e.stopImmediatePropagation();var k=box.dataset.alrField,set=S.fieldsByTab[S.tab];
  if(box.checked)set.add(k);else set.delete(k);
  if(!set.size){box.checked=true;set.add(k);toast('حداقل یک فیلد لازم است.');return}
  saveFields();rerender();return}
},true);
document.addEventListener('input',function(e){
 if(!e.target||e.target.id!=='alrSearch')return;e.stopImmediatePropagation();
 S.query=e.target.value;
 var box=document.getElementById('alrSearch'),pos=box?box.selectionStart:null;
 rerender();var n=document.getElementById('alrSearch');if(n){n.focus();try{n.setSelectionRange(pos,pos)}catch(x){}}
},true);
document.addEventListener('submit',function(e){
 if(!e.target||e.target.id!=='alr-filter')return;
 e.preventDefault();e.stopImmediatePropagation();
 var d=new FormData(e.target),f=String(d.get('from')||'').trim(),t=String(d.get('to')||'').trim();
 if(!f||!t||isoDateFor(f)>isoDateFor(t)){toast('بازهٔ تاریخ معتبر نیست.');return}
 state.report=Object.assign({},state.report||{},{from:f,to:t});persist();S.key='';load(true);rerender();
},true);
window.addEventListener('beforeprint',function(){if(active())preparePrint()});
window.AbsentLunchReport={html:html,load:load,preparePrint:preparePrint,active:active,state:S,chartHtml:chartHtml,viewHtml:viewHtml,rowsForMode:rowsForMode,tabTitle:tabTitle};
if(active())load(false);
})();
