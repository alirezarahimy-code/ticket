// تست: مدیر پشتیبانی (یا هر نقشی که تقویم برنامهٔ غذایی را میبیند) باید
// تعداد سفارشهای فعال هر روز را در خانههای تقویم ببیند.
// اجرا: node tests/js/food-calendar-order-count.test.js
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const root = path.resolve(__dirname, '../..');
const R = (f) => fs.readFileSync(path.join(root, f), 'utf8');

const fm = R('assets/food-menu.js');
const fm2 = R('food-ticket-web/assets/food-menu.js');
const php = R('food-order.php');

// ── ۱) سمت PHP: شمارش نباید منحصراً به مجوز نوشتنی «بستن روز» وصل باشد ──
assert(php.includes('function food_order_can_view_order_counts(array $user): bool'),
  'تابع صریح مجوز مشاهدهٔ تعداد سفارش تعریف نشده است');
assert(php.includes('$includeCounts = food_order_can_view_order_counts($user);'),
  'مسیر food-menu/month هنوز از تابع جدید استفاده نمیکند');
assert(!php.includes("$includeCounts = food_order_user_can($user, 'food.order_close');"),
  'شمارش همچنان فقط به food.order_close گره خورده است');
const countFn = php.slice(php.indexOf('function food_order_can_view_order_counts'), php.indexOf('function food_order_require_primary_admin'));
for (const perm of ['food.menu', 'food.order_close', 'food.reports', 'food.orders']) {
  assert(countFn.includes(`\"${perm}\"`) || countFn.includes(`'${perm}'`), `مجوز ${perm} در لیست مجوزهای خواندنی نیست`);
}

// ── ۲) شبیهسازی منطق تابع PHP: نقشی که فقط food.reports دارد هم باید عدد را ببیند ──
function canViewCounts(granted) {
  return ['food.menu', 'food.order_close', 'food.reports', 'food.orders'].some((p) => granted.includes(p));
}
assert.strictEqual(canViewCounts(['food.reports']), true, 'مدیر پشتیبانی با food.reports باید شمارش را ببیند');
assert.strictEqual(canViewCounts(['food.orders', 'food.menu']), true);
assert.strictEqual(canViewCounts(['foodorder.self']), false, 'کاربر عادی نباید شمارش کل را ببیند');

// ── ۳) هردو کپی ماژول یکسان و معتبر ──
assert.strictEqual(fm, fm2, 'دو کپی food-menu.js باهم فرق دارند');
for (const src of [fm, fm2]) new Function(src);
assert(fm.includes('return day.items.reduce(function (sum, item) { return sum + Number(item.order_count || 0); }, 0);'),
  'activeOrderCount از order_count سرور استفاده نمیکند');

// ── ۴) رفتار واقعی renderCalendar: عدد سفارش در خانهٔ روز چاپ شود ──
// برش توابع از activeOrderCount تا renderDayEditor (شامل holiday/navTabs/renderCalendar).
const start = fm.indexOf('  function activeOrderCount(day)');
const end = fm.indexOf('  function renderDayEditor()');
assert(start > 0 && end > start, 'بخش تقویم در ماژول پیدا نشد');
const segment = fm.slice(start, end);

const esc = (v) => String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const faDigits = (v) => String(v).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
const Cal = {
  months: ['فروردین'], weekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
  monthCells: () => [
    { iso: '2026-10-28', jalali: '۱۴۰۵/۰۸/۰۶', day: 6, isCurrentMonth: true, isWeekend: false },
    { iso: '2026-10-29', jalali: '۱۴۰۵/۰۸/۰۷', day: 7, isCurrentMonth: true, isWeekend: false },
  ],
};
const state = {
  year: 1405, month: 8, loading: false, selectedDate: null,
  status: { today: '2026-10-28' },
  monthData: {
    days: {
      '2026-10-28': {
        date: '2026-10-28', order_status: 'open',
        items: [{ food_id: 1, food_name: 'قرمهسبزی', food_active: true, order_count: 7 }],
      },
      '2026-10-29': {
        date: '2026-10-29', order_status: 'open',
        items: [{ food_id: 2, food_name: 'چلوکباب', food_active: true, order_count: 0 }],
      },
    },
  },
};
const globalStub = { ITSM_HOLIDAYS: [] };
const factory = new Function('esc', 'faDigits', 'Cal', 'state', 'global',
  'monthTitle', 'jalaliFromIso', 'canMenu', 'canClose', 'isPrimary', 'renderDayEditor',
  segment + '\nreturn { renderCalendar, activeOrderCount };');
const ui = factory(esc, faDigits, Cal, state, globalStub,
  () => 'مهر ۱۴۰۵', () => '۱۴۰۵/۰۸/۰۶', true, false, false, () => '');

const html = ui.renderCalendar();
assert(html.includes('fo-day-order-marker'), 'نشانگر تعداد سفارش در تقویم رندر نشد');
assert(html.includes('۷ سفارش'), 'عدد ۷ سفارش در خانهٔ روز دیده نمیشود');
assert(html.includes('has-order'), 'کلاس has-order به روز دارای سفارش اضافه نشد');
assert.strictEqual((html.match(/fo-day-order-marker/g) || []).length, 1,
  'روزی که سفارش ندارد نباید نشانگر داشته باشد');
assert.strictEqual(ui.activeOrderCount(state.monthData.days['2026-10-28']), 7);

// ── ۵) حالت فقطخواندنی ویرایشگر روز هم عدد را نشان دهد ──
const roStart = fm.indexOf('  function renderDayEditor()');
const roEnd = fm.indexOf('  function renderCatalog()');
const roSegment = roStart > 0 && roEnd > roStart ? fm.slice(roStart, roEnd) : '';
assert(roSegment.includes('readOnlyCountHtml') && roSegment.includes('activeOrderCount(day)'),
  'ویرایشگر فقطخواندنی تعداد سفارش را نشان نمیدهد');

console.log('✅ تست تعداد سفارشهای تقویم: PASS (مجوز + رفتار تقویم + حالت فقطخواندنی)');
