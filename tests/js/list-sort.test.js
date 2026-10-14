// اجرا: NODE_PATH=/tmp/jsd/node_modules node tests/js/list-sort.test.js
const { JSDOM } = require('jsdom');
const fs = require('fs');
const path = require('path');
const assert = require('assert');

const html = `<!doctype html><html><head></head><body>
<section class="card ticket-table" id="a">
  <div class="table-head"><span>عنوان</span><span>تاریخ</span><span>مبلغ</span><span data-no-sort>عملیات</span></div>
  <a class="table-row" href="#1"><span>بیست</span><span>۱۴۰۵/۰۷/۰۱</span><span>۱۲٬۰۰۰</span><span>x</span></a>
  <a class="table-row" href="#2"><span>الف</span><span>۱۴۰۴/۱۲/۱۰</span><span>۹</span><span>x</span></a>
  <a class="table-row" href="#3"><span>ج</span><span>—</span><span>۱۰۰</span><span>x</span></a>
  <div class="table-row"><span>ب</span><span>۱۴۰۵/۰۱/۰۲</span><span>۲</span><span>x</span></div>
  <div class="muted">پایان</div>
</section>
<section class="card" id="b">
  <div class="table-head"><span>انتخاب</span><span>نام</span></div>
  <label class="table-row"><span><input type="checkbox" name="ids[]" value="5"></span><span>زیاد</span></label>
  <label class="table-row"><span><input type="checkbox" name="ids[]" value="6"></span><span>کم</span></label>
</section>
<table id="t"><thead><tr><th>نام</th><th>عدد</th></tr></thead><tbody>
  <tr><td>سیب</td><td>10</td></tr><tr><td>آلو</td><td>2</td></tr><tr><td>موز</td><td>33</td></tr>
</tbody></table>
<table id="x" data-no-sort><thead><tr><th>نام</th></tr></thead><tbody><tr><td>ب</td></tr><tr><td>الف</td></tr></tbody></table>
</body></html>`;

const dom = new JSDOM(html, { runScripts: 'outside-only' });
const { window } = dom;
window.eval(fs.readFileSync(path.join(__dirname, '../../assets/list-sort.js'), 'utf8'));
window.ITSM_LIST_SORT.enhance();
const doc = window.document;

function rowTexts(container, selector) {
  return Array.from(container.querySelectorAll(selector)).map(r => r.children[0].textContent.trim());
}
function clickHeader(container, index) {
  const head = container.querySelector('.table-head') || container.querySelector('thead tr');
  const btn = head.children[index].querySelector('.ls-sort-btn');
  assert.ok(btn, 'header ' + index + ' has a sort button');
  btn.click();
}
function headerBtns(container) {
  return Array.from(container.querySelectorAll('.table-head .ls-sort-btn'));
}

const a = doc.getElementById('a');
const original = rowTexts(a, '.table-row');
assert.deepStrictEqual(original, ['بیست', 'الف', 'ج', 'ب'], 'original order');

// no-sort header has no button
assert.strictEqual(a.querySelectorAll('.table-head span[data-no-sort] .ls-sort-btn').length, 0);
assert.strictEqual(headerBtns(a).length, 3, 'three sortable headers');

// sort by name asc (Persian collation)
clickHeader(a, 0);
assert.deepStrictEqual(rowTexts(a, '.table-row'), ['الف', 'ب', 'بیست', 'ج'], 'name asc (Persian order)');
// the muted placeholder stays after rows
assert.strictEqual(a.lastElementChild.className, 'muted', 'placeholder stays last');

// sort by date asc: empty (—) last
clickHeader(a, 1);
assert.deepStrictEqual(rowTexts(a, '.table-row'), ['الف', 'ب', 'بیست', 'ج'], 'date asc (1404 before 1405) with empty last');

// date desc
clickHeader(a, 1);
assert.deepStrictEqual(rowTexts(a, '.table-row'), ['بیست', 'ب', 'الف', 'ج'], 'date desc, empty still last');

// third click → original order
clickHeader(a, 1);
assert.deepStrictEqual(rowTexts(a, '.table-row'), original, 'restored original');

// numeric with Persian digits and separator: amount asc
clickHeader(a, 2);
assert.deepStrictEqual(rowTexts(a, '.table-row'), ['ب', 'الف', 'ج', 'بیست'], 'amount asc (2,9,100,12000)');

// checkbox rows keep their inputs
const b = doc.getElementById('b');
clickHeader(b, 1);
assert.deepStrictEqual(Array.from(b.querySelectorAll('label.table-row')).map(r => r.children[1].textContent.trim()), ['زیاد', 'کم'], 'name asc: ز before ک in Persian');
assert.strictEqual(b.querySelectorAll('input[name="ids[]"]').length, 2, 'inputs preserved');

// data-no-sort table untouched
const x = doc.getElementById('x');
assert.strictEqual(x.querySelectorAll('.ls-sort-btn').length, 0, 'no-sort table has no buttons');
assert.deepStrictEqual(Array.from(x.querySelectorAll('tbody td')).map(td => td.textContent), ['ب', 'الف']);

// standard table: sort by number desc
const t = doc.getElementById('t');
clickHeader(t, 1);
assert.deepStrictEqual(Array.from(t.querySelectorAll('tbody tr td:first-child')).map(td => td.textContent), ['آلو', 'سیب', 'موز']);
clickHeader(t, 1); // desc
assert.deepStrictEqual(Array.from(t.querySelectorAll('tbody tr td:first-child')).map(td => td.textContent), ['موز', 'سیب', 'آلو']);


// ردیف تازه بعد از مرتب‌سازی هم در ترتیب درست قرار می‌گیرد
const newRow = doc.createElement('div');
newRow.className = 'table-row';
newRow.innerHTML = '<span>آ</span><span>۱۴۰۵/۰۱/۰۱</span><span>۵</span><span>x</span>';
a.insertBefore(newRow, a.querySelector('.muted'));
clickHeader(a, 0);
assert.strictEqual(rowTexts(a, '.table-row')[0], 'آ', 'new row sorted into place');

// جدول ساخته‌شده با JS بعد از بارگذاری هم پوشش داده می‌شود
const late = doc.createElement('table');
late.innerHTML = '<thead><tr><th>نام</th></tr></thead><tbody><tr><td>ت</td></tr><tr><td>پ</td></tr></tbody>';
doc.body.appendChild(late);
window.ITSM_LIST_SORT.enhance();
assert.ok(late.querySelector('.ls-sort-btn'), 'late table enhanced');

console.log('list-sort: all assertions passed');
