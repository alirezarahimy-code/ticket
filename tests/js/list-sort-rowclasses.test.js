// اجرا: NODE_PATH=/tmp/jsd/node_modules node tests/js/list-sort-rowclasses.test.js
// فهرست‌های cd-dvd (cd-dvd-record-row) و فهرست خدمات (service-row) باید مرتب شوند؛ ستون عملیات نه.
const { JSDOM } = require('jsdom');
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const html = `<!doctype html><html><head></head><body>
<section class="card ticket-table cd-dvd-table" id="cd">
  <div class="table-head"><span>رسانه</span><span>تاریخ</span></div>
  <div class="cd-dvd-record-row"><span>ب</span><span>۱۴۰۵/۰۱/۰۲</span></div>
  <div class="cd-dvd-record-row"><span>الف</span><span>۱۴۰۵/۰۷/۰۱</span></div>
</section>
<div class="service-list" id="svc"><div class="service-row service-row-head"><span>خدمت</span><span>اولویت</span><span data-no-sort>عملیات</span></div>
  <div class="service-row"><span>ب</span><span>3</span><span><button>x</button></span></div>
  <div class="service-row"><span>الف</span><span>1</span><span><button>y</button></span></div>
</div>
</body></html>`;

const dom = new JSDOM(html, { runScripts: 'outside-only' });
const { window } = dom;
window.eval(fs.readFileSync(path.join(__dirname, '../../assets/list-sort.js'), 'utf8'));
window.ITSM_LIST_SORT.enhance();
const doc = window.document;

const cd = doc.getElementById('cd');
const cdHeaderBtn = cd.querySelectorAll('.table-head .ls-sort-btn');
assert.strictEqual(cdHeaderBtn.length, 2, 'cd-dvd header should have sort buttons');
cdHeaderBtn[0].click();
const cdFirst = cd.querySelector('.cd-dvd-record-row').firstElementChild.textContent;
assert.strictEqual(cdFirst, 'الف', 'cd-dvd rows should sort ascending by media');

const svc = doc.getElementById('svc');
const svcBtns = svc.querySelectorAll('.service-row-head .ls-sort-btn');
assert.strictEqual(svcBtns.length, 2, 'operations column must not get a sort button');
svcBtns[0].click();
assert.strictEqual(svc.querySelector('.service-row:not(.service-row-head)').firstElementChild.textContent, 'الف');
console.log('list-sort row classes: all assertions passed');
