// Regression coverage for the standalone lunch-absence report.
// Run: node tests/js/food-absent-report.test.js
'use strict';
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');

const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const panel = read('food-ticket-web/index.html');
const php = read('food-ticket.php');
const guide = read('help-guide.php');
const chatbot = read('chatbot.php');
const inline = [...panel.matchAll(/<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/g)]
  .map(match => match[1]).find(script => script.includes('const ABSENT_REPORT_FIELDS='));
assert(inline, 'the main panel inline script contains the absent-report module');

// The absent report is a separate authorized view, not a classic-report type.
assert(panel.includes("['absent-report','غایبین ناهار']"), 'navigation exposes the standalone view');
assert(panel.includes("'absent-report':absentReportView"), 'render routes the view to its own renderer');
assert(panel.includes("else if(view==='absent-report'){ensureAbsentReportRange();loadAbsent(false)}"),
  'entering the standalone view initializes and loads its range');
assert(php.includes("'absent-report' => $fpCan('food.reports')"), 'view access uses food.reports');
assert(!inline.includes('option value="absent"'), 'classic report forms no longer offer absent as a report type');
const currentRows = inline.slice(inline.indexOf('function currentReportRows()'), inline.indexOf('function reportMealLabel'));
assert(currentRows && !currentRows.includes("r.type==='absent'"), 'classic report row selection has no absent branch');
for (const hook of [
  "id=\"absent-report-filter\"", "data-action=\"absent-export\"", "data-action=\"absent-print\"",
  "data-action=\"absent-refresh\"", "data-action=\"absent-today\"", "data-absent-field=",
  "data-absent-sort=", "data-absent-org-view=", "'/api/absent?from='",
]) assert(inline.includes(hook), `standalone UI/event/API hook exists: ${hook}`);
assert(inline.includes('FoodOrgGroups.split(map,rows)'), 'deputy grouping uses the shared organization helper');
assert(inline.includes("document.addEventListener('click',e=>{const sort=e.target.closest('[data-absent-sort]')"),
  'sort, refresh, today, export, and print controls have a delegated click handler');
assert(inline.includes("name==='absent-refresh'){e.preventDefault();e.stopImmediatePropagation();refreshAbsentReport();return}")
  && inline.includes("name==='absent-today'){e.preventDefault();e.stopImmediatePropagation();setAbsentReportToday();return}"),
  'refresh applies a valid draft range, and today resets it explicitly');
assert(inline.includes('delete state.absentReportDraft;absentFilterDirty=false;absentRenderPending=false;'),
  'applying a range clears stale dirty/pending state');
assert(inline.includes("if(!box||!box.matches||!box.matches('[data-absent-field]'))return"),
  'the column selector has a delegated change handler');
assert(inline.includes("if(e.target.id!=='absent-report-filter')return"),
  'the range form has an independent submit handler');
assert(inline.includes("e.target.closest('[data-absent-org-view]')"),
  'the flat/grouped view switch is connected');
assert(inline.includes('function absentReportExportXlsx()'), 'standalone XLSX export exists');
assert(inline.includes('function absentReportPrint()'), 'standalone print output exists');
const printStart = inline.indexOf('function absentReportPrint()');
const printEnd = inline.indexOf('\nfunction reportClassicView()', printStart);
assert(printEnd > printStart, 'the print function boundary is intact before the classic report');
const printFunction = inline.slice(printStart, printEnd);
assert(!printFunction.includes('<script'), 'print HTML does not embed a script tag inside the main script block');
assert(printFunction.includes("printWindow.addEventListener('load',printWhenReady,{once:true})"),
  'printing starts from the parent window load handler, not embedded HTML script');
const mainScriptStart = panel.indexOf('<script>', panel.indexOf('food-brand.js'));
const mainScriptEnd = panel.indexOf('</script>', mainScriptStart);
assert(mainScriptStart >= 0 && panel.indexOf('function reportClassicView()', mainScriptStart) < mainScriptEnd,
  'the main panel script remains open past the print helper, so source cannot leak into page text');
assert(guide.includes('گزارش مستقل غایبین ناهار') && guide.includes('بر اساس معاونت'),
  'the Persian guide documents the standalone view and its grouping');
assert(chatbot.includes("'food_absent_lunch_report'") && chatbot.includes("'رفتن به گزارش غایبین ناهار'"),
  'chatbot provides a dedicated answer/link for the standalone report');
assert(panel.includes("PANEL_VERSION='1.37.57'") && php.includes("FOOD_TICKET_PANEL_VERSION = '1.37.57'"),
  'panel cache/version markers agree');

// Evaluate only the report functions against small, synthetic fixtures. No DOM,
// database, live API, or printing device is used by this test.
const firstStart = inline.indexOf('const ABSENT_FIELDS_KEY=');
const firstEnd = inline.indexOf('\nfunction loadReportMonitoring()', firstStart);
const secondStart = inline.indexOf('function absentSortValue(');
const secondEnd = inline.indexOf('\nfunction reportClassicView()', secondStart);
assert(firstEnd > firstStart && secondEnd > secondStart, 'pure report function blocks were found');
const source = inline.slice(firstStart, firstEnd) + '\n' + inline.slice(secondStart, secondEnd) + `
 globalThis.absentTestApi={
   absentReportRows,absentPortionCount,absentUniquePeopleCount,absentSortedRows,
   absentReportTableMarkup,absentReportExportXlsx,absentReportOrgHtml,absentReportPrintHtml,absentReportPrint,absentReportView,
   absentRangeInputs,absentReportRange,markAbsentReportFilterDirty,commitAbsentReportRange,
   refreshAbsentReport,setAbsentReportToday,loadAbsent
 };
`;
const stored = new Map([['food-ticket-absent-fields-v1', JSON.stringify(['row', 'name', 'food'])]]);
const exported = [];
const apiCalls = [];
const messages = [];
let renderCount = 0;
let persistCount = 0;
const mockRows = [
  { foodDate: '2026-11-02', nat: '0012345678', pc: '101', first: 'علی', last: 'رضایی', food: 'برنج', declared_absent: false },
  { foodDate: '2026-11-01', nat: '0012345678', pc: '101', first: 'علی', last: 'رضایی', food: 'خورش' },
  { foodDate: '2026-11-02', nat: '0098765432', pc: '202', first: 'سارا', last: 'محمدی', food: '<خوراک>' },
  { foodDate: '2026-11-03', nat: '0011122233', pc: '303', first: 'خارج', last: 'بازه', food: '—' },
];
const orgGroups = {
  keysForRow(row) {
    const nat = String(row.national_code || row.nat || '').replace(/^0+/, '');
    return nat ? ['nat:' + nat] : (row.pc ? ['pc:' + row.pc] : []);
  },
  split(_map, rows) {
    const grouped = new Map();
    for (const row of rows) {
      const label = String(row.nat || '').endsWith('5678') ? 'معاونت الف' : 'معاونت ب';
      if (!grouped.has(label)) grouped.set(label, []);
      grouped.get(label).push(row);
    }
    return [...grouped].map(([label, items]) => ({ label, rows: items }));
  },
};
const context = {
  state: {
    absentReport: { from: '2026-11-01', to: '2026-11-02' },
    absentRows: mockRows,
    absentSort: { key: 'date', dir: 'asc' },
    absentReportLoading: false,
    absentReportError: '',
    __orgMap: { people: {} },
  },
  serviceMode: true,
  view: 'absent-report',
  localStorage: {
    getItem(key) { return stored.has(key) ? stored.get(key) : null; },
    setItem(key, value) { stored.set(key, value); },
  },
  window: {
    FoodOrgGroups: orgGroups,
    ITSM_LIST_SORT: { exportXlsx(...args) { exported.push(args); } },
  },
  document: { querySelector() { return null; } },
  todayJalali: () => '2026-11-06',
  todayIso: () => '2026-11-06',
  isoDateFor(value) {
    const text = String(value || '');
    return /^\d{4}-\d{2}-\d{2}$/.test(text) ? text : null;
  },
  faDate(value) { return String(value || ''); },
  toFaDigits(value) { return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]); },
  esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));
  },
  api() { throw new Error('this rendering test must not call a live API'); },
  persist() { persistCount += 1; },
  render() { renderCount += 1; },
  formObject(form) { return { ...form.values }; },
  safeAbsentRender() {},
  toast(message) { messages.push(message); },
  heading(title, subtitle, actions) { return `<header><h1>${title}</h1><p>${subtitle}</p>${actions}</header>`; },
  loadOrgMap() { return Promise.resolve(this.state.__orgMap); },
};
vm.runInNewContext(source, context, { filename: 'food-ticket-absent-report-test.js' });
const report = context.absentTestApi;
context.api = url => { apiCalls.push(url); return Promise.resolve({ items: mockRows }); };
const rows = report.absentReportRows();
assert.strictEqual(rows.length, 3, 'rows outside the selected date range are excluded');
assert(rows.every(row => row.portion_count === 1), 'each returned row contributes one lunch portion');
assert.strictEqual(report.absentPortionCount(rows), 3, 'portion total counts rows');
assert.strictEqual(report.absentUniquePeopleCount(rows), 2, 'unique people are deduplicated across days');
assert.strictEqual(rows[0].status, 'غایب — سفارش دارد، فیش چاپ نشده', 'missing status receives a clear label');

const sorted = report.absentSortedRows(rows);
assert.deepStrictEqual(Array.from(sorted, row => row.date), ['2026-11-01', '2026-11-02', '2026-11-02'],
  'sort direction is applied before row numbers are assigned');
assert.deepStrictEqual(Array.from(sorted, row => row.__absentRow), [1, 2, 3], 'row numbering follows sort order');
context.state.absentSort = { key: 'row', dir: 'desc' };
const reverseRows = report.absentSortedRows(rows);
assert.deepStrictEqual(Array.from(reverseRows, row => row.name), ['سارا محمدی', 'علی رضایی', 'علی رضایی'],
  'sorting by the row-number column reverses the source order');
context.state.absentSort = { key: 'date', dir: 'asc' };
const table = report.absentReportTableMarkup(sorted, true, 'empty');
assert(table.includes('data-absent-sort="name"') && table.includes('data-absent-sort="food"'),
  'the table renders sortable selected columns');
assert(!table.includes('وضعیت غیبت'), 'unselected columns are omitted');
assert(table.includes('&lt;خوراک&gt;'), 'row values are HTML escaped');

report.absentReportExportXlsx();
assert.strictEqual(exported.length, 1, 'XLSX export invokes the shared export helper once');
assert.deepStrictEqual(Array.from(exported[0][1]), ['ردیف', 'نام و نام خانوادگی', 'نوع غذا'],
  'XLSX headers respect the selected columns');
assert.strictEqual(exported[0][2].length, 3, 'XLSX contains the filtered report rows');
assert(messages.some(message => message.includes('در حال ساخت')), 'successful export reports progress');

const grouped = report.absentReportOrgHtml(report.absentSortedRows(rows));
assert(grouped.includes('معاونت الف') && grouped.includes('معاونت ب'), 'screen output groups rows by organization');
assert(grouped.includes('پرس'), 'each group shows its portion total');
const printFlat = report.absentReportPrintHtml(sorted, false, 'فهرست کامل غایبین');
assert(printFlat.includes('<h1>فهرست کامل غایبین</h1>') && printFlat.includes('نام و نام خانوادگی'),
  'flat print contains the requested title and selected list only');
assert(!printFlat.includes('زمان چاپ') && !printFlat.includes('آمار پایش'),
  'print output does not add timestamps or monitoring statistics');
const printGrouped = report.absentReportPrintHtml(sorted, true, 'لیست غایبین بر اساس معاونت');
assert(printGrouped.includes('لیست غایبین بر اساس معاونت') && printGrouped.includes('معاونت الف'),
  'grouped print uses the grouped title and organization headers');

report.loadAbsent(true).then(() => {
  assert.deepStrictEqual(apiCalls, ['/api/absent?from=2026-11-01&to=2026-11-02'],
    'the standalone view requests the selected date range from the absent API');
  assert.strictEqual(context.state.absentRows.length, mockRows.length, 'API rows are kept in transient state');
  assert.strictEqual(context.state.absentReportLoading, false, 'loading state clears after success');
  assert.strictEqual(context.state.absentReportError, '', 'successful load clears the report error');

  report.markAbsentReportFilterDirty({ values: { from: '2026-11-04', to: '2026-11-05' } });
  assert.strictEqual(context.state.absentReport.from, '2026-11-01');
  assert.strictEqual(context.state.absentReport.to, '2026-11-02', 'editing a draft does not silently commit it');
  const visibleDraft = report.absentRangeInputs();
  assert.strictEqual(visibleDraft.from, '2026-11-04');
  assert.strictEqual(visibleDraft.to, '2026-11-05', 'rerendered filter inputs retain the unsubmitted draft');
  assert(report.absentReportView().includes('بازهٔ تغییرکرده هنوز اعمال نشده است'),
    'the view labels an unapplied range');
  const exportCount = exported.length;
  report.absentReportExportXlsx();
  report.absentReportPrint();
  assert.strictEqual(exported.length, exportCount, 'exports/printing are blocked while the visible range is unapplied');

  assert.strictEqual(report.refreshAbsentReport(), true, 'refresh applies a valid dirty date range');
  assert.strictEqual(context.state.absentReport.from, '2026-11-04');
  assert.strictEqual(context.state.absentReport.to, '2026-11-05', 'refresh commits the edited range instead of discarding it');
  assert.strictEqual(context.state.absentReportDraft, undefined, 'committing removes the draft');
  assert.strictEqual(context.state.absentReportLoading, true, 'committing a range enters loading state');
  assert(renderCount > 0 && persistCount > 0, 'committing refresh persists and renders');

  return report.loadAbsent(true).then(() => {
    assert.strictEqual(apiCalls[1], '/api/absent?from=2026-11-04&to=2026-11-05',
      'refresh fetches the newly applied date interval');
    report.markAbsentReportFilterDirty({ values: { from: '2026-11-07', to: '2026-11-06' } });
    assert.strictEqual(report.refreshAbsentReport(), false, 'an invalid dirty range cannot be refreshed');
    assert.strictEqual(report.absentRangeInputs().from, '2026-11-07', 'invalid draft remains visible for correction');
    assert.strictEqual(report.setAbsentReportToday(), true, 'today overrides an invalid dirty draft');
    assert.strictEqual(context.state.absentReport.from, '2026-11-06');
    assert.strictEqual(context.state.absentReport.to, '2026-11-06', 'today applies one day and resets the range');
    assert.strictEqual(context.state.absentReportDraft, undefined, 'today clears the stale draft');
    assert.strictEqual(context.state.absentReportLoading, true, 'today starts a new report load');
    console.log('✅ تست گزارش مستقل غایبین ناهار: PASS');
  });
}).catch(error => {
  console.error(error);
  process.exitCode = 1;
});
