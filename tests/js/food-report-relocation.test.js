// تست جابجایی reportها از «برنامه غذایی» به «گزارشها».
// اجرا: node tests/js/food-report-relocation.test.js
const fs=require('fs'),path=require('path'),assert=require('assert'),vm=require('vm');
const root=path.resolve(__dirname,'../..');
const html=fs.readFileSync(path.join(root,'food-ticket-web/index.html'),'utf8');
const fm=fs.readFileSync(path.join(root,'food-ticket-web/assets/food-menu.js'),'utf8');
const fm2=fs.readFileSync(path.join(root,'assets/food-menu.js'),'utf8');

// ۱) پنج تب تو «گزارشها»
for(const [k,label] of [['classic','گزارشگیری'],['aggregate','گزارش تجمیعی'],['delivered','غذای تحویلی'],['statistics','آمار سفارشها'],['todayorders','سفارشهای امروز']]){
  assert(html.includes(`['${k}','${label}']`),`تب «${label}» تو REPORT_TABS وجود ندارد`);
}
assert(html.includes('data-report-tab="${k}"'),'تبها بهصورت داینامیک ساخته نمیشوند');
// ۲) reportView به ماژول وصل شده
assert(/FOOD_REPORT_TABS/.test(html),'نگاشت تبها به ماژول تعریف نشده');
assert(/FoodMenuUI\.html\(/.test(html),'reportView از FoodMenuUI.html استفاده نمیکند');
// ۳) ماژول حالت جاسازیشده داره
for(const f of [fm,fm2]){
  assert(f.includes('state.embeddedMode'),'حالت جاسازیشده تو ماژول نیست');
  assert(f.includes('data-fm-mode'),'FoodMenuUI.html حالت رو نمیپذیرد');
  assert(f.includes('food-open-report') || f.includes('embeddedMode'),'لینک show-orders اصلاح نشده');
}
// ۴) تبهای قدیمی از منوی برنامه غذایی حذف شدن
const navBlock=fm.slice(fm.indexOf('function navTabs()'),fm.indexOf('function renderCalendar()'));
assert(!navBlock.includes("['orders', 'سفارش‌های روز']"),'تب سفارشهای روز هنوز تو برنامه غذایی هست');
assert(!navBlock.includes("['statistics', 'آمار سفارش‌ها']"),'تب آمار سفارشها هنوز تو برنامه غذایی هست');
// ۵) گزارش تحویلی دوبار رندر نشه
assert(!fm.includes("+ '</section>' + renderPrintedDeliveryReport();"),'گزارش تحویلی هنوز به آمار چسبیده');
// ۶) هردو کپی ماژول یکسان باشن
assert.strictEqual(fm,fm2,'دوتا کپی food-menu.js با هم فرق دارن');
// ۷) JS معتبر
for(const src of [fm]) new Function(src);
const scripts=[...html.matchAll(/<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/g)];
for(const s of scripts) new Function(s[1]);
console.log('✅ تست جابجایی reportها: PASS (همهٔ موارد)');
