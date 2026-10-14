// اجرا: NODE_PATH=/tmp/jsd/node_modules node tests/js/food-menu-by-menu.test.js
// بخش «نمایش بر اساس منو» را از فایل واقعی برمی‌دارد و خروجی را بررسی می‌کند.
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const src = fs.readFileSync(path.join(__dirname, '../../assets/food-menu.js'), 'utf8');
const start = src.indexOf('  var ORDER_COLUMNS');
const end = src.indexOf('  function renderOrders()');
assert(start > 0 && end > start, 'بخش سفارش‌ها در فایل پیدا نشد');
const segment = src.slice(start, end);

// وابستگی‌ها: esc و faDigits و state مثل فایل اصلی
const esc = (v) => String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const faDigits = (v) => String(v).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
const state = { ordersSort: { key: 'food', dir: 'asc' } };
const factory = new Function('esc', 'faDigits', 'state', 'jalaliFromIso', segment + '\nreturn { orderByMenuHtml, orderTableHtml };');
const { orderByMenuHtml } = factory(esc, faDigits, state, () => '۱۴۰۵/۰۷/۲۱');

const rows = [
  { menu_no: 2, food_name: 'قرمه‌سبزی', full_name: 'علی', employee_number: '1', national_code: '1' },
  { menu_no: 1, food_name: 'چلوکباب', full_name: 'زهرا', employee_number: '2', national_code: '2' },
  { menu_no: 1, food_name: 'چلوکباب', full_name: 'حسن', employee_number: '3', national_code: '3' },
  { menu_no: 0, food_name: 'ساندویچ', full_name: 'رضا', employee_number: '4', national_code: '4' },
];
const html = orderByMenuHtml(rows);
const titles = [...html.matchAll(/<h4>([^<]*?)\s—/g)].map((m) => m[1]);
assert.deepStrictEqual(titles, ['منوی ۱', 'منوی ۲', 'بدون منو'], 'ترتیب بخش‌ها باید منو ۱، ۲ و سپس بدون منو باشد');
assert.strictEqual((html.match(/fm-menu-group/g) || []).length, 3, 'فقط برای منوهای موجود بخش ساخته شود');
assert(!html.includes('منوی ۳'), 'منوی ۳ که سفارشی ندارد نباید نمایش داده شود');

// فقط منوی ۱: فقط یک بخش
const onlyOne = orderByMenuHtml([rows[1]]);
assert.strictEqual((onlyOne.match(/fm-menu-group/g) || []).length, 1);

// بدون سفارش: جدول خالی
assert(orderByMenuHtml([]).includes('سفارشی پیدا نشد'));

console.log('food-menu-by-menu: all assertions passed');
