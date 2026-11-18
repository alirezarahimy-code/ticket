// Regression coverage for the current aggregate-report UI and reconciliation formula.
// Run: node tests/js/food-aggregate-report.test.js
'use strict';
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '../..');
const html = fs.readFileSync(path.join(root, 'food-ticket-web/index.html'), 'utf8');
const start = html.indexOf('const AGG_FIELDS_KEY=');
const end = html.indexOf('function reportClassicView(){', start);
assert(start >= 0 && end > start, 'the aggregate-report module is present');
const block = html.slice(start, end);
const handlerStart = html.indexOf("document.addEventListener('click',e=>{\n const sortBtn=", end);
const handlerEnd = html.indexOf("document.addEventListener('click',e=>{const a=e.target.closest('[data-action]');if(a?.dataset.action==='today-orders')", handlerStart);
assert(handlerStart > end && handlerEnd > handlerStart, 'aggregate click/change/submit handlers are present');
const interactionBlock = html.slice(handlerStart, handlerEnd);

const storage = new Map();
const listeners = { click: [], change: [], input: [], submit: [], focusout: [] };
const created = [];
const apiCalls = [];
const toasts = [];
let renderCount = 0;

const env = {
  localStorage: {
    getItem(key) { return storage.has(key) ? storage.get(key) : null; },
    setItem(key, value) { storage.set(key, String(value)); },
  },
  document: {
    addEventListener(type, handler) { if (listeners[type]) listeners[type].push(handler); },
    querySelector() { return null; },
    querySelectorAll() { return []; },
    createElement() {
      const element = { className: '', innerHTML: '', href: '', download: '', style: {}, isConnected: true,
        click() {}, remove() { this.isConnected = false; } };
      created.push(element);
      return element;
    },
    body: { appendChild(element) { if (!created.includes(element)) created.push(element); }, classList: { add() {}, remove() {} } },
  },
  window: { FoodOrgGroups: null },
  state: {
    report: { from: '۱۴۰۵/۰۸/۰۵', to: '۱۴۰۵/۰۸/۰۵' },
    reportTab: 'aggregate',
    aggSort: { key: 'last_name', dir: 'asc' },
  },
  view: 'reports',
  serviceMode: true,
  apiBase: '',
  absentFilterDirty: false,
  absentRenderPending: false,
  esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
  },
  faDate(value) { return String(value ?? ''); },
  toFaDigits(value) { return String(value ?? '').replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]); },
  isoDateFor(value) {
    const text = String(value ?? '').replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit));
    return /^\d{4}-\d{2}-\d{2}$/.test(text) ? text : null;
  },
  todayIso: () => '2026-10-27',
  todayJalali: () => '۱۴۰۵/۰۸/۰۵',
  formObject(form) { return form.values || {}; },
  heading(title, subtitle, actions = '') { return `<header><h2>${title}</h2><p>${subtitle}</p>${actions}</header>`; },
  api(url) { apiCalls.push(url); return Promise.resolve({ ok: true, counts: {}, printed_people: [], absent_people: [], columns: [] }); },
  loadAggregate(force) { apiCalls.push({ aggregate: !!force }); return Promise.resolve(); },
  persist() {},
  render() { renderCount++; },
  toast(message) { toasts.push(String(message)); },
  loadOrgMap: () => Promise.resolve(null),
  loadReportMonitoring() {},
  reportClassicView: () => '<div>گزارش کلاسیک</div>',
  Promise, JSON, Array, String, Number, Math, Object, Set, URLSearchParams,
};

const names = Object.keys(env);
const create = new Function(...names, `${block}; return {
  reportView, reportAggregateView, aggFields, aggSaveFields, aggRange, aggDateLabel,
  aggCell, aggTable, aggSelectedCols, aggSorted, aggDownloadUrl, showAggregatePreview,
  aggregateDiagTable, AGG_ALL_FIELDS, AGG_DEFAULT_FIELDS
};`);
const reportModule = create(...names.map(name => env[name]));
const handlerEnv = {
  ...env,
  REPORT_TABS: [['classic', 'گزارشگیری'], ['aggregate', 'گزارش تجمیعی']],
  FOOD_REPORT_TABS: {},
  aggDownloadUrl: reportModule.aggDownloadUrl,
  showAggregatePreview: reportModule.showAggregatePreview,
  aggSaveFields: reportModule.aggSaveFields,
};
new Function(...Object.keys(handlerEnv), interactionBlock)(...Object.values(handlerEnv));

function fire(type, target, extra = {}) {
  const event = { target, preventDefault() { this.defaultPrevented = true; }, stopImmediatePropagation() {}, ...extra };
  for (const handler of listeners[type] || []) handler(event);
}
function clickTarget({ action = null, tab = null, sort = null } = {}) {
  return { closest(selector) {
    if (selector === '[data-agg-sort]' && sort) return { dataset: { aggSort: sort } };
    if (selector === '[data-report-tab]' && tab) return { dataset: { reportTab: tab } };
    if (selector === '[data-action]' && action) return { dataset: { action } };
    return null;
  } };
}

let viewHtml = reportModule.reportView();
assert(viewHtml.includes('data-report-tab="classic"') && viewHtml.includes('data-report-tab="aggregate"'),
  'classic and aggregate tabs are rendered');
env.state.reportTab = 'classic';
assert(reportModule.reportView().includes('گزارش کلاسیک'), 'classic tab retains its original report view');
env.state.reportTab = 'aggregate';
viewHtml = reportModule.reportView();
assert(viewHtml.includes('id="aggregate-filter"'), 'aggregate tab renders its date filter');

// Active orders are row counts; the absent reconciliation uses unique, matchable people.
env.state.aggregate = {
  counts: {
    total_orders: 120, lunch_orders: 112, cancelled_orders: 3, order_rows: 115,
    dinner_orders: 8, dinner_cancelled_orders: 1, dinner_order_rows: 9,
    printed_final: 102, printed_dinner: 7, physical_printed_lunch: 98,
    physical_printed_dinner: 7, auto_printed_lunch: 4, auto_printed_dinner: 0,
    guest_printed_lunch: 2, guest_printed_dinner: 1,
    physical_guest_printed_lunch: 1, physical_guest_printed_dinner: 1,
    auto_guest_printed_lunch: 1, auto_guest_printed_dinner: 0,
    absent: 9, expected_absent: 9, has_mismatch: false, difference: 0,
  },
  diagnostics: {
    active_lunch_orders: 112, active_dinner_orders: 8, active_other_meal_orders: 0,
    reportable_lunch_rows: 110, unique_lunch_identities: 108,
    matched_printed_lunch_identities: 99, absent_count: 9, difference: 0,
    orders_without_national_code_count: 2, orders_without_identity_count: 1,
    legacy_printed_event_count: 1,
    legacy_printed_events: [{ date: '2026-10-27', first_name: 'علی', last_name: 'رضایی', pc: '101', event_id: 7 }],
    event_type_status_counts: [{ event_type: 'print', meal_type: 'lunch', print_status: 'printed', total: 1 }],
  },
  printed_people: [
    { pc: '101', first_name: 'علی', last_name: 'رضایی', name: 'علی رضایی', food: 'چلوکباب', kind: 'پرسنل', date: '2026-10-27', time: '12:01:00', meal_type: 'lunch' },
    { pc: '102', first_name: 'مریم', last_name: 'احمدی', name: 'مریم احمدی', food: 'خورش', kind: 'پرسنل', date: '2026-10-27', time: '12:03:00', meal_type: 'dinner' },
  ],
  absent_people: [
    { pc: '202', first_name: 'سارا', last_name: 'محمدی', name: 'سارا محمدی', food: 'قورمه‌سبزی', status: 'غایب', date: '2026-10-27', declared: true, meal_type: 'lunch' },
  ],
  source_notes: [],
};
env.state.aggregateUpdatedAt = '2026-10-27T09:30:00.000Z';

let out = reportModule.reportAggregateView();
for (const label of ['سفارش‌ها', 'فیش خودکار پرسنل', 'فیش خودکار مهمان', 'فهرست چاپ‌شده', 'فهرست غایبین']) {
  assert(out.includes(label), `current summary row appears: ${label}`);
}
assert(out.includes('۱۰۸ هویت یکتای سفارش‌دارِ قابل‌تطبیق − ۹۹ هویت دارای فیش ناهار'),
  'summary calculates expected absences from unique identities and matched staff receipts');
assert(out.includes('۹ غایبِ موردانتظار') && out.includes('غایب ثبت‌شده ۹'), 'expected and listed absences are visible');
assert(!out.includes('۱۱۲ سفارش فعال ناهار − ۱۰۲ ثبت فیش ناهار'), 'raw row/event subtraction is not presented as person reconciliation');
assert(out.includes('با همان کد هم انجام می‌شود'), 'missing national codes can fall back to personnel-code matching');
assert(out.includes('نه کد ملی دارد و نه کد پرسنلی'), 'unmatchable rows have a distinct warning');
assert(out.includes('هویت‌های یکتای قابل‌تطبیق') && !out.includes('هویت‌های یکتای دارای کد ملی'),
  'diagnostics describe both national-code and personnel-code fallback identities accurately');
assert(out.includes('عیب‌یابی و تفکیک منبع آمار') && out.includes('رویداد چاپ با نوع قدیمی print'), 'diagnostic details and legacy events remain visible');
assert(out.includes('data-action="aggregate-export"') && out.includes('data-action="aggregate-print"'), 'XLSX and print actions remain present');
assert(out.includes('id="aggregate-filter"'), 'date-range filter remains present');

// Mismatch status must use the set-based count, not raw printed event totals.
env.state.aggregate.counts.has_mismatch = true;
env.state.aggregate.counts.expected_absent = 10;
env.state.aggregate.counts.absent = 9;
env.state.aggregate.counts.difference = 1;
env.state.aggregate.diagnostics.matched_printed_lunch_identities = 98;
env.state.aggregate.diagnostics.difference = 1;
out = reportModule.reportAggregateView();
assert(out.includes('notice warn') && out.includes('مغایرت نشان می‌دهد'), 'mismatch warning/message is rendered');

// Column selection, escaping, and sorting use the real panel helpers.
storage.clear();
assert.deepStrictEqual(reportModule.aggFields(), reportModule.AGG_DEFAULT_FIELDS, 'default selected columns are restored');
reportModule.aggSaveFields(['pc', 'first_name', 'last_name']);
assert.deepStrictEqual(reportModule.aggFields(), ['pc', 'first_name', 'last_name'], 'selected columns persist');
assert.strictEqual(reportModule.aggSelectedCols().length, 3, 'only selected columns are returned');
const table = reportModule.aggTable([{ pc: '1', first_name: 'علی', last_name: '<رضایی>' }], 'خالی');
assert(table.includes('data-agg-sort="pc"') && table.includes('data-agg-sort="last_name"'), 'selected headers remain sortable');
assert(table.includes('&lt;رضایی&gt;'), 'table cells are escaped');
assert(!table.includes('data-agg-sort="food"'), 'unselected fields are omitted');
const sorted = reportModule.aggSorted([
  { first_name: 'علی', last_name: 'رضایی' },
  { first_name: 'مهدی', last_name: 'احمدی' },
]);
assert.deepStrictEqual(sorted.map(person => person.last_name), ['احمدی', 'رضایی'], 'family-name ordering works');

const url = reportModule.aggDownloadUrl();
assert(url.includes('/api/aggregate-report-export?') && url.includes('from=') && url.includes('to=') && url.includes('fields='),
  'XLSX download URL has the range and selected fields');
reportModule.showAggregatePreview();
const preview = created[created.length - 1];
assert(preview.innerHTML.includes('۱۰۸ هویت یکتای سفارش‌دارِ قابل‌تطبیق'), 'print preview uses the corrected set-based formula');
assert(preview.innerHTML.includes('<h3>غایبین ناهار</h3>'), 'print preview includes absent list');

// Delegated navigation, sorting, and action buttons use the production click handler.
let before = renderCount;
fire('click', clickTarget({ sort: 'pc' }));
assert(env.state.aggSort.key === 'pc' && renderCount > before, 'clicking a column header sorts and re-renders');
before = renderCount;
fire('click', clickTarget({ tab: 'classic' }));
assert(env.state.reportTab === 'classic' && renderCount > before, 'classic tab click changes view and renders');
fire('click', clickTarget({ tab: 'aggregate' }));
assert(env.state.reportTab === 'aggregate', 'aggregate tab click restores the summary');
assert(apiCalls.some(call => call && call.aggregate === false), 'opening aggregate tab requests its report');

// XLSX and print actions remain wired to the selected range and current report.
fire('click', clickTarget({ action: 'aggregate-export' }));
const downloadLink = created[created.length - 1];
assert(downloadLink.download.endsWith('.xlsx') && downloadLink.href.includes('aggregate-report-export'),
  'export action creates an XLSX download link');
fire('click', clickTarget({ action: 'aggregate-print' }));
const clickedPreview = created[created.length - 1];
assert(clickedPreview.innerHTML.includes('پیش‌نمایش گزارش تجمیعی'), 'print action opens the real preview layer');

// The production filter rejects invalid ranges without persisting them, then refreshes valid input.
const previousRange = { ...env.state.report };
fire('submit', { id: 'aggregate-filter', values: { from: '2026-10-29', to: '2026-10-27' }, matches() { return true; } });
assert(toasts.some(message => message.includes('معتبر نیست')), 'an inverted date range is rejected');
assert.deepStrictEqual(env.state.report, previousRange, 'invalid dates do not overwrite the current range');
before = renderCount;
fire('submit', { id: 'aggregate-filter', values: { from: '2026-10-27', to: '2026-10-28' }, matches() { return true; } });
assert(renderCount > before, 'valid range submission renders a new report');
assert(env.state.report.from === '2026-10-27' && env.state.report.to === '2026-10-28', 'submitted range becomes current');
assert(apiCalls.some(call => call && call.aggregate === true), 'valid range triggers a refreshed aggregate request');
fire('click', clickTarget({ action: 'aggregate-today' }));
assert(env.state.report.from === '۱۴۰۵/۰۸/۰۵' && env.state.report.to === '۱۴۰۵/۰۸/۰۵', 'today action resets both range endpoints');

console.log('PASS: aggregate-report UI regression checks');
