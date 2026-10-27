// تست رفتاری «گزارش تجمیعی» — بلوک UI را از index.html جدا کرده و با mock اجرا میکند.
// اجرا: node tests/js/food-aggregate-report.test.js
const fs = require('fs');
const assert = require('assert');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const html = fs.readFileSync(path.join(root, 'food-ticket-web/index.html'), 'utf8');
const start = html.indexOf('const AGG_FIELDS_KEY=');
const fnEnd = html.indexOf('function reportClassicView(){', start);
assert(start > 0 && fnEnd > start, 'بلوک توابع گزارش تجمیعی در index.html پیدا نشد');
const hStart = html.indexOf("document.addEventListener('click',e=>{\n const tab=e.target.closest&&e.target.closest('[data-report-tab]')", fnEnd);
const hEndMarker = "document.addEventListener('click',e=>{const a=e.target.closest('[data-action]');if(a?.dataset.action==='today-orders'){e.preventDefault();";
const hEnd = html.indexOf(hEndMarker, hStart);
assert(hStart > 0 && hEnd > hStart, 'بلوک هندلرهای گزارش تجمیعی پیدا نشد');
const block = html.slice(start, fnEnd) + '\n' + html.slice(hStart, hEnd);

// ── mock محیط ────────────────────────────────────────────────────────────────
const storage = new Map();
const listeners = { click: [], change: [], submit: [] };
const created = [];
const toastCalls = [];
const apiCalls = [];
let renderCount = 0;

const env = {
  localStorage: {
    getItem: (k) => (storage.has(k) ? storage.get(k) : null),
    setItem: (k, v) => storage.set(k, String(v)),
  },
  document: {
    addEventListener: (type, fn) => { if (listeners[type]) listeners[type].push(fn); },
    querySelectorAll: () => [],
    createElement: () => ({ className: '', innerHTML: '', href: '', download: '', click() {}, remove() {}, style: {} }),
    body: { appendChild: (x) => created.push(x), classList: { add() {}, remove() {} } },
  },
  state: { report: { from: '۱۴۰۵/۰۸/۰۵', to: '۱۴۰۵/۰۸/۰۵' }, reportTab: 'aggregate' },
  view: 'reports',
  serviceMode: true,
  apiBase: '',
  esc: (x) => String(x ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;'),
  faDate: (x) => String(x ?? ''),
  toFaDigits: (v) => String(v).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]),
  isoDateFor: (x) => {
    const s = String(x ?? '').replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    const m = s.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
    if (m) return '2026-' + m[2].padStart(2, '0') + '-' + m[3].padStart(2, '0');
    return '2026-10-27';
  },
  todayIso: () => '2026-10-27',
  todayJalali: () => '۱۴۰۵/۰۸/۰۵',
  formObject: (f) => f._values || {},
  heading: (title, sub, actions = '') => `<div class="heading"><h2>${title}</h2><p>${sub}</p>${actions}</div>`,
  api: (url) => { apiCalls.push(url); return Promise.resolve({ ok: true, counts: {}, printed_people: [], absent_people: [], columns: [] }); },
  persist: () => {},
  render: () => { renderCount++; },
  toast: (m) => toastCalls.push(String(m)),
  loadReportMonitoring: () => {},
  loadAggregate: () => Promise.resolve(),
  reportClassicView: () => '<div id="classic-report">گزارشگیری قدیمی</div>',
  Promise, JSON, Array, String, Number, Math, Object, Set, URLSearchParams,
};

const names = Object.keys(env);
const fn = new Function(...names, `${block}; return {
  reportView, reportAggregateView, aggFields, aggSaveFields, aggRange, aggDateLabel,
  aggCell, aggTable, aggSelectedCols, loadAggregate, aggDownloadUrl, showAggregatePreview,
  AGG_ALL_FIELDS, AGG_DEFAULT_FIELDS
};`);
const m = fn(...names.map((n) => env[n]));

function fire(type, target, extra = {}) {
  const e = { target, preventDefault() {}, stopImmediatePropagation() {}, ...extra };
  for (const h of listeners[type]) h(e);
}

// ── تستها ───────────────────────────────────────────────────────────────────
// ۱) reportView باید دوتا تب بسازد
let out = m.reportView();
assert(out.includes('data-report-tab="classic"'), 'تب گزارشگیری وجود ندارد');
assert(out.includes('data-report-tab="aggregate"'), 'تب گزارش تجمیعی وجود ندارد');
assert(out.includes('گزارشگیری') && out.includes('گزارش تجمیعی'), 'عنوان تبها ناقص است');

// ۲) تب کلاسیک، محتوای قدیمی را نشان دهد (عقبگرد نکرده باشد)
env.state.reportTab = 'classic';
out = m.reportView();
assert(out.includes('id="classic-report"'), 'تب گزارشگیری محتوای قدیمی را نشان نمیدهد');
env.state.reportTab = 'aggregate';

// ۳) پنج شمارنده + مغایرت
env.state.aggregate = {
  counts: { total_orders: 120, guest_printed: 8, printed_final: 102, absent: 10, has_mismatch: false, difference: 0 },
  printed_people: [{ pc: '1001', name: 'علی رضایی', food: 'چلوکباب', kind: 'پرسنل', date: '2026-10-27', time: '12:01:00' }],
  absent_people: [{ pc: '1002', name: 'مریم احمدی', food: 'قورمهسبزی', status: 'غایب', date: '2026-10-27', declared: true }],
  source_notes: [],
};
out = m.reportAggregateView();
for (const label of ['تعداد کل سفارشها', 'فیش مهمان چاپشده', 'فیش چاپشده قطعی', 'تعداد غایبین', 'مغایرت']) {
  assert(out.includes(label), `شمارنده «${label}» در خروجی نیست`);
}
assert(out.includes('۱۲۰') && out.includes('۱۰۲') && out.includes('۱۰'), 'اعداد فارسی نشان داده نشدند');
assert(out.includes('ندارد'), 'وضعیت «مغایرت ندارد» نشان داده نشد');
assert(out.includes('علی رضایی') && out.includes('مریم احمدی'), 'اسامی چاپشدهها/غایبین نشان داده نشد');
assert(out.includes('غیبت اعلامشده'), 'برچسب غیبت اعلامشده نشان داده نشد');
assert(out.includes('aggregate-export') && out.includes('aggregate-print'), 'دکمههای استخراج/چاپ وجود ندارند');
assert(out.includes('id="aggregate-filter"'), 'فرم انتخاب روز وجود ندارد');
assert(out.includes('data-agg-field="name"'), 'انتخاب فیلد وجود ندارد');

// ۴) حالت مغایرtdارد
env.state.aggregate.counts = { total_orders: 120, guest_printed: 8, printed_final: 100, absent: 10, has_mismatch: true, difference: 2 };
out = m.reportAggregateView();
assert(out.includes('دارد') && out.includes('اختلاف ۲ نفر'), 'مغایرت و اختلاف نشان داده نشد');
assert(out.includes('agg-mismatch'), 'استایل هشدار مغایرت اعمال نشد');

// ۵) انتخاب فیلدها — فقط ستونهای انتخابشده بیایند
storage.clear();
assert.deepStrictEqual(m.aggFields(), m.AGG_DEFAULT_FIELDS, 'فیلدهای پیشفرض نادرستاند');
m.aggSaveFields(['pc', 'name']);
assert.deepStrictEqual(m.aggFields(), ['pc', 'name'], 'ذخیره/بازیابی فیلدها کار نمیکند');
assert.strictEqual(m.aggSelectedCols().length, 2, 'تعداد ستونهای انتخابشده نادرست است');
out = m.aggTable([{ pc: '1', name: 'الف' }], 'خالی');
assert(out.includes('<th>کد پرسنلی</th>') && out.includes('<th>نام و نام خانوادگی</th>'), 'سرستونها ناقصاند');
assert(!out.includes('<th>نوع غذا</th>'), 'ستون انتخابنشده همچنان نمایش داده میشود');
storage.clear();

// ۶) کلیک تب → تغییر state و رندر
const before = renderCount;
fire('click', { closest: (sel) => (sel === '[data-report-tab]' ? { dataset: { reportTab: 'classic' } } : null) });
assert.strictEqual(env.state.reportTab, 'classic', 'کلیک تب، state را عوض نکرد');
assert(renderCount > before, 'کلیک تب، رندر نکرد');
fire('click', { closest: (sel) => (sel === '[data-report-tab]' ? { dataset: { reportTab: 'aggregate' } } : null) });
assert.strictEqual(env.state.reportTab, 'aggregate', 'برگشت به تب تجمیعی کار نکرد');

// ۷) استخراج → لینک دانلود XLSX
const beforeLinks = created.length;
fire('click', {
  closest: (sel) => (sel === '[data-report-tab]' ? null : (sel === '[data-action]' ? { dataset: { action: 'aggregate-export' } } : null)),
});
const link = created[created.length - 1];
assert(link && link.href && link.href.includes('aggregate-report-export'), 'لینک استخراج ساخته نشد');
assert(link.href.includes('from=') && link.href.includes('to=') && link.href.includes('fields='), 'پارامترهای استخراج ناقصاند');
assert(link.download.endsWith('.xlsx'), 'خروجی XLSX نیست');

// ۸) چاپ → لایه پیشنمایش
const beforeLayers = created.length;
fire('click', {
  closest: (sel) => (sel === '[data-report-tab]' ? null : (sel === '[data-action]' ? { dataset: { action: 'aggregate-print' } } : null)),
});
assert(created.length > beforeLayers, 'لایه پیشنمایش چاپ ساخته نشد');
assert(created[created.length - 1].innerHTML.includes('پیشنمایش گزارش تجمیعی'), 'محتوای پیشنمایش ناقص است');

// ۹) فرم تاریخ نامعتبر → خطا، بدون رندر
toastCalls.length = 0;
fire('submit', {
  id: 'aggregate-filter',
  _values: { from: '۱۴۰۵/۰۸/۱۰', to: '۱۴۰۵/۰۸/۰۱' },
});
assert(toastCalls.some((x) => x.includes('معتبر نیست')), 'بازه نامعتبر گرفته نشد');

// ۱۰) دکمه «امروز»
fire('click', {
  closest: (sel) => (sel === '[data-report-tab]' ? null : (sel === '[data-action]' ? { dataset: { action: 'aggregate-today' } } : null)),
});
assert.strictEqual(env.state.report.from, '۱۴۰۵/۰۸/۰۵', 'دکمه امروز تاریخ را تنظیم نکرد');

console.log('✅ تست رفتاری گزارش تجمیعی: PASS (۱۰ گروه تست)');
