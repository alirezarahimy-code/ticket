// اجرا: NODE_PATH=/tmp/jsd/node_modules node tests/js/list-sort-export.test.js
const { JSDOM } = require('jsdom');
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const dom = new JSDOM('<!doctype html><html><body></body></html>', { runScripts: 'outside-only', url: 'http://localhost/index.php' });
const { window } = dom;
const doc = window.document;
window.ITSM_CSRF = 'tok123';
window.eval(fs.readFileSync(path.join(__dirname, '../../assets/list-sort.js'), 'utf8'));
doc.dispatchEvent(new window.Event('DOMContentLoaded'));

const exp = doc.createElement('section');
exp.className = 'card';
exp.innerHTML = '<h2>فهرست تست</h2><a id="exp" href="index.php?action=export_assets">Excel</a>' +
  '<div class="table-head"><span>نام</span><span>مبلغ</span><span data-no-sort>عملیات</span></div>' +
  '<div class="table-row"><span>ب</span><span>۲</span><span>x</span></div>' +
  '<div class="table-row"><span>الف</span><span>۱۰</span><span>x</span></div>' +
  '<div class="table-row"><span>ج</span><span>—</span><span>x</span></div>';
doc.body.appendChild(exp);
window.ITSM_LIST_SORT.enhance();

// fetch ساختگی: درخواست‌ها را ثبت می‌کند
const calls = [];
window.fetch = (url, opts) => { calls.push({ url, opts }); return Promise.resolve({ ok: true, blob: () => Promise.resolve(new window.Blob(['x'])) }); };
window.URL.createObjectURL = () => 'blob:test';
window.URL.revokeObjectURL = () => {};

const link = doc.getElementById('exp');
const click = () => { const e = new window.MouseEvent('click', { bubbles: true, cancelable: true }); link.dispatchEvent(e); return e; };
const sortBtn = (i) => exp.querySelectorAll('.table-head .ls-sort-btn')[i];

// ۱) بدون مرتب‌سازی: خروجی سرور دست نخورده
assert.strictEqual(click().defaultPrevented, false, 'unsorted → server export');
assert.strictEqual(calls.length, 0);

// ۲) مرتب‌سازی بر اساس مبلغ (صعودی)
sortBtn(1).click();
assert.ok(exp.hasAttribute('data-ls-active'));
const view = JSON.parse(JSON.stringify(window.ITSM_LIST_SORT.buildView(exp)));
assert.deepStrictEqual(view.headers, ['نام', 'مبلغ'], 'actions column skipped');
assert.deepStrictEqual(view.rows, [['ب', '۲'], ['الف', '۱۰'], ['ج', '—']], 'sorted order');

// ۳) لینک خروجی از مسیر XLSX مرتب‌شده می‌رود
const e2 = click();
assert.strictEqual(e2.defaultPrevented, true, 'sorted → local XLSX export');
assert.strictEqual(calls.length, 1);
assert.ok(calls[0].url.startsWith('index.php?action=export_view_xlsx'));
assert.strictEqual(calls[0].opts.method, 'POST');
assert.strictEqual(calls[0].opts.headers['X-CSRF-Token'], 'tok123', 'csrf token sent');
const body = JSON.parse(calls[0].opts.body);
assert.deepStrictEqual(body.headers, ['نام', 'مبلغ']);
assert.deepStrictEqual(body.rows[0], ['ب', '۲']);

// ۴) برگشت به ترتیب اصلی: دوباره خروجی سرور
sortBtn(1).click();
sortBtn(1).click();
assert.ok(!exp.hasAttribute('data-ls-active'));
assert.strictEqual(click().defaultPrevented, false, 'reset → server export');

console.log('list-sort-export: all assertions passed');
