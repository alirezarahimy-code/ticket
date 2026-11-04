const {JSDOM,VirtualConsole}=require('jsdom');
const fs=require('fs'), path=require('path'), assert=require('assert');
let html=fs.readFileSync(path.join(__dirname,'../../../food-ticket-web/index.html'),'utf8');
let scripts=[...html.matchAll(/<script>([\s\S]*?)<\/script>/g)],big=scripts.reduce((a,b)=>a[1].length>b[1].length?a:b);
html=html.replace(big[0],'<script>'+big[1]+`;window.__t={state,dashboardEventKind,mapMonitoring,buildFoodDashboardPayload,loadAggregate,loadReportMonitoring,loadAbsent,loadGuestOrdersReport,render,setView(v){view=v},todayIso};</script>`);
for(const name of ['food-menu.js']) html=html.replace(new RegExp('<script src="assets/'+name.replace('.','\\.')+'\\?[^\"]*"><\\/script>'),'<script>'+fs.readFileSync(path.join(__dirname,'../../../assets/'+name),'utf8')+'</script>');
const errors=[];let aggregateFailure=false,reportFailure=false;
const vc=new VirtualConsole();vc.on('jsdomError',e=>errors.push(e.message));vc.on('error',e=>errors.push(String(e)));
const dom=new JSDOM(html,{url:'https://example.test/index.php?page=food-ticket',runScripts:'dangerously',pretendToBeVisual:true,virtualConsole:vc,beforeParse(w){
 w.FOOD_TICKET_HOST_BOOT=1;w.FOOD_TICKET_API_BASE='index.php?page=food-ticket&food_api=';w.FOOD_TICKET_AUTHENTICATED=true;w.FOOD_TICKET_ROLE='admin';w.FOOD_TICKET_IS_PRIMARY_ADMIN=true;w.FOOD_TICKET_CAN_MENU=true;w.FOOD_TICKET_CAN_MENU_EDIT=true;w.FOOD_TICKET_VIEWS={dashboard:true,reports:true};w.FOOD_TICKET_SECTIONS={};w.FOOD_TICKET_SECTION_EDITS={};w.performance.getEntriesByType=()=>[];
 w.fetch=async (url,opts={})=>{const u=decodeURIComponent(String(url));let obj={items:[]};let ok=true;
 if(u.includes('dashboard-data')){let today=new Date();let day=today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-'+String(today.getDate()).padStart(2,'0');obj={monitoring:[],orders:[],active_employee_count:200,order_summary:{by_date:{[day]:{active:193,cancelled:5,total:198}}}}}
 else if(u.includes('aggregate-report')){if(aggregateFailure){ok=false;obj={error:'آزمایش خطای منبع'}}else obj={columns:[],totals:{orders:193,orders_cancelled:5,orders_total:198},printed_people:[],absent_people:[]}}
 else if(u.includes('report-data')){if(reportFailure){ok=false;obj={error:'آزمایش خطای گزارش'}}else obj={orders:[],monitoring:[]}}
 else if(u.includes('food-menu/orders'))obj={items:[],summary:{active:193,cancelled:5,total:198}};
 else if(u.includes('food-menu/statistics'))obj={items:[],summary:{active:193,cancelled:5,total:198}};
 else if(u.includes('food-menu/status'))obj={today:new Date().toISOString().slice(0,10)};
 return {ok,status:ok?200:500,text:async()=>JSON.stringify(obj),headers:{get:()=>null}}};
}});
const wait=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{await wait(300);const w=dom.window,t=w.__t;assert(t,'main script boot');
 const mapped=t.mapMonitoring([{event_type:'guest',result:'تحویل شد',print_status:'printed'},{event_type:'guest_limit',result:'سقف مهمان',print_status:'failed'},{event_type:'no_food',result:'غذا ندارد',print_status:'ignored'},{event_type:'repeat',result:'تردد مجدد',print_status:'ignored'}]);
 assert.deepStrictEqual(mapped.map(t.dashboardEventKind),['guests','errors','noFood','repeat']);console.log('PASS event_type classification including guest printed vs cap');
 const p=t.buildFoodDashboardPayload();const cards=p.summaryCards||p.cards;assert(cards,'dashboard cards');assert(cards.find(c=>c.id==='risk-score').value.includes('۱۹۳'));assert(cards.find(c=>c.id==='cancelled-orders').value.includes('۵'));console.log('PASS dashboard active/cancelled summary 193/5');
 t.setView('reports');t.state.reportTab='aggregate';t.state.report={from:t.todayIso(),to:t.todayIso(),type:'attendance'};t.render();await wait(200);
 assert(w.document.querySelector('#aggregate-filter'),'aggregate form present');let f=w.document.querySelector('#aggregate-filter input[name=from]');f.value='draft';f.dispatchEvent(new w.Event('input',{bubbles:true}));await t.loadAggregate(true);assert.strictEqual(w.document.querySelector('#aggregate-filter input[name=from]').value,'draft');console.log('PASS in-flight aggregate response preserves unsent date');
 w.document.querySelector('[data-report-tab=classic]').click();await wait(100);reportFailure=true;await t.loadReportMonitoring();assert(w.document.querySelector('#screen').textContent.includes('خطا در دریافت اطلاعات'));assert(!w.document.querySelector('#screen .grid.section-gap .stat'));console.log('PASS classic source error visible without zero summary');
 w.document.querySelector('[data-report-tab=aggregate]').click();await wait(100);aggregateFailure=true;await t.loadAggregate(true);await wait(50);assert(w.document.querySelector('#screen').textContent.includes('آزمایش خطای منبع'));console.log('PASS aggregate source error visible');
 assert.deepStrictEqual(errors,[]);console.log('ALL PASSED');dom.window.close();
})().catch(e=>{console.error('FAIL',e.stack,'jsdom errors',errors);dom.window.close();process.exitCode=1});
