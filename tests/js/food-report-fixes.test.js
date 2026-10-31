// تست رفع سه مشکل: عرض ستون ردیف، دکمههای view، دسترسی ثبت دستی فیش.
const fs=require('fs'),path=require('path'),assert=require('assert');
const root=path.resolve(__dirname,'../..');
const R=f=>fs.readFileSync(path.join(root,f),'utf8');
const panel=R('food-ticket-web/index.html');
const fm=R('food-ticket-web/assets/food-menu.js');
const fm2=R('assets/food-menu.js');
const css=R('assets/food-menu.css');
const css2=R('food-ticket-web/assets/food-menu.css');
const php=R('food-ticket.php');

// ۱) عرض ستون ردیف
for(const c of [css,css2]){
  assert(/is-compact[^{]*td:first-child[^{]*\{[^}]*white-space:nowrap/.test(c) || /td:first-child[^{]*\{[^}]*nowrap/.test(c),
    'ستون ردیف nowrap/min-width ندارد');
  assert(c.includes('min-width:52px')||c.includes('min-width:54px')||c.includes('min-width:56px'),'حداقل عرض ستون ردیف کم است');
}
// ۲) دکمههای view دیگه data-view نداشته باشن (با هندلر پنل تداخل نکنن)
assert(!fm.includes('data-fm-action="delivered-report-view" data-view='),'دکمه view هنوز data-view دارد');
assert(fm.includes('data-delivery-view='),'دکمه view از attribute اختصاصی استفاده نمیکند');
// و هندلر پنل یه محافظ داشته باشه
assert(panel.includes("b.hasAttribute('data-fm-action')"),'هندلر پنل محافظ ماژول ندارد');
// ۳) دسترسی ثبت دستی فیش
assert(php.includes("'manual-attendance' => $fpCan('food.manual_ticket')"),'نمایش منو به مجوز food.manual_ticket وصل نیست');
assert(panel.includes('FOOD_TICKET_CAN_MANUAL_TICKET'),'پنل مجوز سرور را چک نمیکند');
// هردو کپی یکسان
assert.strictEqual(fm,fm2,'دوتا کپی food-menu.js فرق دارند');
assert.strictEqual(css,css2,'دوتا کپی food-menu.css فرق دارند');
new Function(fm);
for(const s of [...panel.matchAll(/<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/g)])new Function(s[1]);
console.log('✅ تست رفع سه مشکل: PASS');
