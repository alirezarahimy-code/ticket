// هارنس رفتاری نسخهٔ نهایی ۱٫۶۳٫۳ — نیاز: jsdom. اجرا: node tests/js/food-absent-lunch-runtime.test.js
'use strict';
const fs = require('fs'), path = require('path'), assert = require('assert');
let JSDOM;
try { JSDOM = require('jsdom').JSDOM; }
catch (e) { console.log('SKIP: jsdom not installed'); process.exit(0); }
const root = path.resolve(__dirname, '../..');
const html = fs.readFileSync(path.join(root, 'food-ticket-web/index.html'), 'utf8');
const scripts = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(m => m[1]);
const moduleCode = scripts.find(s => s.includes('window.AbsentLunchReport='));
assert(moduleCode, 'ماژول inline پیدا نشد');

const dom = new JSDOM('<!doctype html><html lang="fa" dir="rtl"><head></head><body><div id="app"></div></body></html>',
  { url: 'http://localhost/', runScripts: 'outside-only', pretendToBeVisual: true });
const w = dom.window, d = w.document;
const JAL = { '2026-11-14': '۱۴۰۵/۰۸/۲۴', '2026-11-15': '۱۴۰۵/۰۸/۲۵', '2026-11-16': '۱۴۰۵/۰۸/۲۶', '2026-11-17': '۱۴۰۵/۰۸/۲۷', '2026-11-18': '۱۴۰۵/۰۸/۲۸' };
const DEPS = ['معاونت اداری', 'معاونت فناوری اطلاعات', 'معاونت مالی و اداری'];
const items = [];
const add = (pc, first, last, rows) => rows.forEach(([iso, food, dec]) => items.push({ pc, first, last, food, foodDate: iso, declared_absent: !!dec }));
add('۱۰۰۱', 'علی', 'احمدی', [['2026-11-15','چلوکباب'],['2026-11-16','قرمهسبزی'],['2026-11-17','چلوکباب']]);
add('۱۰۰۲', 'مریم', 'رضایی', [['2026-11-15','جوجهکباب',true],['2026-11-17','قرمهسبزی']]);
add('۱۰۰۳', 'حسن', 'کریمی', [['2026-11-14','چلوکباب'],['2026-11-15','چلوکباب'],['2026-11-16','عدسپلو'],['2026-11-18','جوجهکباب']]);
add('۱۰۰۴', 'زهرا', 'محمدی', [['2026-11-16','قرمهسبزی'],['2026-11-18','چلوکباب']]);
add('۲۰۰۱', 'رضا', 'حسینی', [['2026-11-15','چلوکباب'],['2026-11-17','قرمهسبزی']]);
add('۲۰۰۲', 'سارا', 'نوری', [['2026-11-16','جوجهکباب',true],['2026-11-18','عدسپلو']]);
add('۳۰۰۱', 'محمد', 'صادقی', [['2026-11-15','چلوکباب'],['2026-11-17','قرمهسبزی']]);

w.view = 'reports';
w.state = { reportTab: 'absent-lunch', report: { from: '۱۴۰۵/۰۸/۲۴', to: '۱۴۰۵/۰۸/۲۸' }, __orgMap: {} };
w.serviceMode = true;
w.PANEL_VERSION = '1.37.57';
w.FOOD_TICKET_CSRF = 'x';
w.reportRange = () => ({ from: '2026-11-14', to: '2026-11-18' });
w.faDate = iso => JAL[iso] || iso;
w.isoDateFor = x => String(x || '');
w.todayJalali = () => '۱۴۰۵/۰۸/۲۸';
w.esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
w.heading = t => '<h2>' + t + '</h2>';
w.toast = () => {};
w.persist = () => {};
w.toFaDigits = n => String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
w.api = () => Promise.resolve({ items });
w.FoodOrgGroups = { groupOf: (map, r) => ({ label: String(r.pc || '')[0] === '۱' ? DEPS[0] : String(r.pc || '')[0] === '۲' ? DEPS[1] : DEPS[2] }), load: () => Promise.resolve({}) };
w.loadOrgMap = () => Promise.resolve({});
w.render = () => { d.getElementById('app').innerHTML = w.AbsentLunchReport.html(); };
w.print = () => {};
w.URL.createObjectURL = () => 'blob:x';
w.URL.revokeObjectURL = () => {};
w.HTMLAnchorElement.prototype.click = function () {};
let fetchBody = null;
w.fetch = (url, opts) => { fetchBody = JSON.parse(opts.body); return Promise.resolve({ ok: true, blob: () => Promise.resolve(new w.Blob(['x'])) }); };

w.eval(moduleCode);
const A = w.AbsentLunchReport;
const tick = () => new Promise(r => setTimeout(r, 15));
const click = sel => d.querySelector(sel).dispatchEvent(new w.MouseEvent('click', { bubbles: true }));
const change = (el, checked) => { el.checked = checked; el.dispatchEvent(new w.Event('change', { bubbles: true })); return tick(); };
let P = 0, F = 0;
const t = async (name, fn) => { try { await fn(); P++; console.log('  PASS ' + name); } catch (e) { F++; console.log('  FAIL ' + name + ' → ' + e.message); } };

(async () => {
  await A.load(true); await tick();
  await t('بوت: ۱۷ غیبت، ۳ معاونت، تیک «همه» چک', () => {
    assert.strictEqual(A.state.rows.length, 17);
    assert.strictEqual(A.state.deps.size, 3);
    assert(d.querySelector('[data-alr-all]').checked);
    assert.strictEqual(d.querySelectorAll('[data-alr-dep-check]').length, 3);
  });
  await t('چارت جمعوجور: نام کامل، رنگ اختصاصی، محور بازه', () => {
    assert.strictEqual(d.querySelectorAll('.alr-row').length, 3);
    assert(!d.querySelector('.alr-chart svg'));
    const names = [...d.querySelectorAll('.alr-name')].map(x => x.textContent);
    DEPS.forEach(n => assert(names.includes(n), n));
    const fills = [...d.querySelectorAll('.alr-fill')].map(x => x.getAttribute('style'));
    assert.strictEqual(new Set(fills).size, 3);
    const axis = d.querySelector('.alr-axis').textContent;
    assert(axis.includes('شروع بازه: ۱۴۰۵/۰۸/۲۴') && axis.includes('پایان بازه: ۱۴۰۵/۰۸/۲۸'));
  });
  await t('سرگروه بولد چسبیده: ۴ نفر · ۱۱ وعده', () => {
    const h = [...d.querySelectorAll('.alr-group-head h4')].find(x => x.textContent.includes('معاونت اداری'));
    assert(h.querySelector('b').textContent.includes('۴ نفر · ۱۱ وعده'));
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 3);
  });
  await t('تیک تکی: حذف یک معاونت ⇒ لیست ۲ گروه، «همه» indeterminate', async () => {
    const box = [...d.querySelectorAll('[data-alr-dep-check]')].find(x => x.dataset.alrDepCheck === DEPS[2]);
    await change(box, false);
    assert.strictEqual(A.state.deps.size, 2);
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 2);
    const all = d.querySelector('[data-alr-all]');
    assert.strictEqual(all.indeterminate, true);
    assert.strictEqual(all.checked, false);
    assert(!d.getElementById('alrList').textContent.includes('صادقی'));
  });
  await t('تیک «همه معاونتها» ⇒ فیلتر کل معاونتها', async () => {
    await change(d.querySelector('[data-alr-all]'), true);
    assert.strictEqual(A.state.deps.size, 3);
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 3);
    assert.strictEqual(d.querySelector('[data-alr-all]').indeterminate, false);
    assert(d.getElementById('alrList').textContent.includes('صادقی'));
  });
  await t('چند معاونت بهصورت همزمان قابل انتخاباند', async () => {
    await change(d.querySelector('[data-alr-all]'), false);
    assert.strictEqual(A.state.deps.size, 0);
    assert(d.getElementById('alrList').textContent.includes('هیچ معاونتی انتخاب نشده'));
    const boxes = [...d.querySelectorAll('[data-alr-dep-check]')];
    await change(boxes[0], true);
    await change(d.querySelectorAll('[data-alr-dep-check]')[1], true);
    assert.strictEqual(A.state.deps.size, 2);
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 2);
    await change(d.querySelector('[data-alr-all]'), true);
  });
  await t('تب «فهرست یکجا»: تاریخ غیبت + تعداد غیبت', async () => {
    click('[data-alr-tab="flat"]'); await tick();
    const h = d.querySelector('#alrList table thead').textContent;
    assert(h.includes('تاریخ غیبت') && h.includes('تعداد غیبت'));
    assert(!h.includes('وعده'));
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 0);
  });
  await t('تب «بر اساس معاونت»: تعداد غیبت برگشته', async () => {
    click('[data-alr-tab="dept"]'); await tick();
    const h = d.querySelector('#alrList table thead').textContent;
    assert(h.includes('تعداد غیبت'));
    assert(!h.includes('تاریخ غیبت'));
    assert.strictEqual(d.querySelectorAll('.alr-group').length, 3);
    const g = [...d.querySelectorAll('.alr-group')].find(x => x.querySelector('h4').textContent.includes('معاونت اداری'));
    assert.strictEqual(g.querySelectorAll('tbody tr').length, 4);
  });
  await t('تب «تفکیک و جزئیات»: نوع غذا + تاریخ، بدون وعده، ۱۱ ردیف اداری', async () => {
    click('[data-alr-tab="detail"]'); await tick();
    assert.strictEqual(d.querySelector('[data-alr-tab="detail"]').textContent.trim(), 'تفکیک و جزئیات');
    const h = d.querySelector('#alrList table thead').textContent;
    assert(h.includes('نوع غذا') && h.includes('تاریخ غیبت') && !h.includes('وعده'));
    const g = [...d.querySelectorAll('.alr-group')].find(x => x.querySelector('h4').textContent.includes('معاونت اداری'));
    assert.strictEqual(g.querySelectorAll('tbody tr').length, 11);
  });
  await t('ابزارهای چارت در هر ۳ تب حاضراند', async () => {
    for (const tab of ['flat', 'dept', 'detail']) {
      click('[data-alr-tab="' + tab + '"]'); await tick();
      assert(d.querySelector('.alr-tools'), tab);
      assert(d.querySelector('#alrSearch') && d.querySelector('[data-alr-all]'), tab);
      assert.strictEqual(d.querySelectorAll('[data-alr-metric]').length, 2, tab);
    }
    click('[data-alr-tab="detail"]'); await tick();
  });
  await t('چاپ فقط لیست + عنوان؛ بعد از کنسل فرم سرجاست', () => {
    A.preparePrint();
    const area = d.getElementById('alrPrint');
    assert(area.querySelector('h1').textContent === 'گزارش غایبین ناهار');
    assert(area.querySelector('.alr-tab-title').textContent === 'تفکیک و جزئیات');
    assert(area.querySelector('table'));
    assert(!area.querySelector('.alr-chart') && !area.querySelector('.alr-tools') && !area.querySelector('.alr-chip'));
    assert(w.document.body.classList.contains('printing-absent-lunch'), 'کلاس چاپ غایبین بعد از preparePrint فعال است');
    assert(d.getElementById('app').querySelector('#alr-filter'));
  });
  await t('XLSX واقعی با فیلدهای تب فعال', async () => {
    w.AbsentLunchReport.html();
    click('[data-action="absent-lunch-export"]'); await tick();
    assert(fetchBody && fetchBody.headers.includes('نوع غذا') && fetchBody.headers.includes('تاریخ غیبت'));
    assert(fetchBody.rows.length === 17);
  });
  await t('بلوک ۲ ستونها را ضرب میکند', async () => {
    const one = d.querySelector('#alrList table thead tr').children.length;
    click('[data-alr-block="2"]'); await tick();
    assert.strictEqual(d.querySelector('#alrList table thead tr').children.length, one * 2);
    click('[data-alr-block="1"]'); await tick();
  });
  await t('جستجوی زنده', async () => {
    const box = d.getElementById('alrSearch'); box.value = 'مریم';
    box.dispatchEvent(new w.Event('input', { bubbles: true })); await tick();
    assert(d.getElementById('alrList').textContent.includes('مریم'));
    assert(!d.getElementById('alrList').textContent.includes('حسن'));
    const b2 = d.getElementById('alrSearch'); b2.value = '';
    b2.dispatchEvent(new w.Event('input', { bubbles: true })); await tick();
  });
  console.log('\nRESULT: ' + P + ' PASS / ' + F + ' FAIL');
  process.exit(F ? 1 : 0);
})().catch(e => { console.error(e); process.exit(1); });
