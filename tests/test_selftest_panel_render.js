#!/usr/bin/env node
/**
 * تست رندر «آزمون جامع Worker/چاپ/مدیریت» در پنل، بدون مرورگر.
 *
 * تابع‌های واقعیِ پنل (selftestBox و وابستگی‌هایش) از فایل index.html
 * استخراج و اجرا می‌شوند؛ سپس خروجی HTML با پاسخ واقعیِ سرور سنجیده می‌شود.
 *
 * اجرا: node tests/test_selftest_panel_render.js
 */
'use strict';

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const html = fs.readFileSync(path.join(root, 'food-ticket-web/index.html'), 'utf8');
const source = [...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].map((m) => m[1]).join('\n');

let pass = 0;
let fail = 0;
const check = (label, ok, detail = '') => {
  if (ok) {
    pass++;
    console.log('[PASS] ' + label);
  } else {
    fail++;
    console.log('[FAIL] ' + label + (detail ? ' :: ' + detail : ''));
  }
};

/** استخراج یک تابع/ثابت با شمارش آکولاد. */
const extract = (name) => {
  const patterns = [
    new RegExp('function\\s+' + name + '\\s*\\(', 'm'),
    new RegExp('(?:const|let|var)\\s+' + name + '\\s*=', 'm'),
  ];
  let start = -1;
  for (const p of patterns) {
    const m = source.match(p);
    if (m) {
      start = m.index;
      break;
    }
  }
  if (start < 0) {
    throw new Error('تابع «' + name + '» پیدا نشد');
  }
  const isArrowConst = patterns[1].test(source.slice(start, start + 200)) && source[start] !== '{';
  if (isArrowConst) {
    // const X = ... ;  — تا نقطه‌ویرگول در عمق صفر (توابع پیکانی بدون آکولاد)
    let depth = 0;
    for (let j = start; j < source.length; j++) {
      const ch = source[j];
      if (ch === '{' || ch === '(' || ch === '[') depth++;
      else if (ch === '}' || ch === ')' || ch === ']') depth--;
      else if (ch === ';' && depth === 0) return source.slice(start, j + 1);
    }
    throw new Error('پایان «' + name + '» پیدا نشد');
  }
  let i = source.indexOf('{', start);
  if (i < 0) {
    throw new Error('بدنهٔ «' + name + '» پیدا نشد');
  }
  let depth = 0;
  for (let j = i; j < source.length; j++) {
    const ch = source[j];
    if (ch === '{') depth++;
    else if (ch === '}') {
      depth--;
      if (depth === 0) return source.slice(start, j + 1);
    }
  }
  throw new Error('پایان «' + name + '» پیدا نشد');
};

const parts = ['esc', 'ST_LABELS', 'ST_CLASS', 'selftestBox'].map(extract).join('\n;\n');

const sandbox = {
  console,
  Intl,
  Number,
  String,
  Array,
  Math,
  JSON,
  window: {},
  document: { getElementById: () => null },
  render: () => {},
  toast: () => {},
  api: () => Promise.resolve({}),
  state: {},
};
vm.createContext(sandbox);
vm.runInContext(parts, sandbox);
vm.runInContext('globalThis.__selftestBox = selftestBox;', sandbox);
const selftestBox = sandbox.__selftestBox;

/* ── ۱) حالت اولیه: دکمهٔ اجرا ─────────────────────────────────────────── */
let out = selftestBox();
check('حالت اولیه دکمهٔ «اجرای آزمون جامع» دارد', out.includes('data-action="run-worker-selftest"'));
check('حالت اولیه فقط‌خواندنی بودن را توضیح می‌دهد', out.includes('فقط‌خواندنی'));
check('حالت اولیه بخش id دارد', out.includes('id="worker-selftest"'));

/* ── ۲) حالت بارگذاری ─────────────────────────────────────────────────── */
sandbox.window.__ftSelfTest = { __loading: true };
out = selftestBox();
check('حالت بارگذاری پیام انتظار نشان می‌دهد', out.includes('در حال اجرای بررسی'));

/* ── ۳) حالت خطا ──────────────────────────────────────────────────────── */
sandbox.window.__ftSelfTest = { __error: 'HTTP 403' };
out = selftestBox();
check('حالت خطا پیام خطا را نشان می‌دهد', out.includes('HTTP 403'));
check('حالت خطا دکمهٔ تلاش دوباره دارد', out.includes('تلاش دوباره'));

/* ── ۴) پاسخ سالم کامل (شبیه خروجی واقعی سرور) ───────────────────────── */
const good = {
  generated_at: '2026-11-16 10:30:00',
  elapsed_ms: 412,
  source: 'panel',
  window: { enabled: true, open: false },
  verdict: { level: 'ok', headline: 'همهٔ بررسی‌ها سالم است.', counts: { ok: 12, warn: 0, fail: 0, info: 4, skip: 1 } },
  sections: [
    { key: 'config', title: 'تنظیمات پایه و نصب', checks: [{ name: 'پردازشگر فیش غذا', status: 'ok', message: 'فعال است.', fix: '' }] },
    {
      key: 'worker',
      title: 'Worker — زنده‌بودن و درستی بیکاری',
      checks: [
        { name: 'ضربان Worker (فایل)', status: 'ok', message: 'Worker زنده است.', fix: '' },
        { name: 'وضعیت بیکاری', status: 'info', message: 'خارج از پنجره عمداً بیکار است.', fix: '' },
      ],
    },
  ],
  text: 'گزارش',
};
sandbox.window.__ftSelfTest = good;
out = selftestBox();
check('پاسخ سالم: حکم نمایش داده می‌شود', out.includes('همهٔ بررسی‌ها سالم است.'));
check('پاسخ سالم: آیکن ✅ دارد', out.includes('✅'));
check('پاسخ سالم: شمارش‌ها نمایش داده می‌شوند', out.includes('سالم: ۱۲') && out.includes('ایراد: ۰'));
check('پاسخ سالم: عنوان بخش‌ها می‌آید', out.includes('تنظیمات پایه و نصب') && out.includes('درستی بیکاری'));
check('پاسخ سالم: نام بررسی‌ها می‌آید', out.includes('ضربان Worker (فایل)'));
check('پاسخ سالم: دکمهٔ کپی گزارش هست', out.includes('data-action="copy-worker-selftest"'));
check('پاسخ سالم: دکمهٔ اجرای دوباره هست', out.includes('data-action="run-worker-selftest"'));
check('پاسخ سالم: زمان اجرا نمایش داده می‌شود', out.includes('2026-11-16 10:30:00'));
check('پاسخ سالم: مدت اجرا نمایش داده می‌شود', out.includes('۴۱۲'));
check('پاسخ سالم: بیکاری درست توضیح داده می‌شود', out.includes('طبیعی است'));
check('پاسخ سالم: هیچ ایراد قرمزی ندارد', !out.includes('class="status bad"'), 'bad badge found');

/* ── ۵) پاسخ با ایراد: کلاس رنگی و راه‌حل ─────────────────────────────── */
sandbox.window.__ftSelfTest = {
  generated_at: '2026-11-16 12:00:00',
  elapsed_ms: 900,
  window: { enabled: true, open: true },
  verdict: { level: 'fail', headline: '۲ ایراد مسدودکننده — چاپ کار نمی‌کند.', counts: { ok: 8, warn: 1, fail: 2, info: 2, skip: 0 } },
  sections: [
    {
      key: 'queue',
      title: 'صف چاپ',
      checks: [
        { name: 'ردیف‌های واجد شرایط چاپ', status: 'fail', message: '۵ ردیف مانده است.', fix: 'چاپگر را بررسی کنید.' },
        { name: 'فیش‌های ناموفق', status: 'warn', message: '۳ فیش ناموفق.', fix: '' },
      ],
    },
  ],
  text: 'گزارش',
};
out = selftestBox();
check('پاسخ خراب: آیکن ❌ دارد', out.includes('❌'));
check('پاسخ خراب: نشان «ایراد» دارد', out.includes('ایراد'));
check('پاسخ خراب: راه‌حل نمایش داده می‌شود', out.includes('راه‌حل: چاپگر را بررسی کنید.'));
check('پاسخ خراب: وضعیت bad رنگ می‌گیرد', out.includes('class="status bad"'));
check('پاسخ خراب: وضعیت warn رنگ می‌گیرد', out.includes('class="status warn"'));
check('پاسخ خراب: بخش دارای ایراد باز است', out.includes('<details open'));
check('پاسخ خراب: وضعیت پنجرهٔ باز ذکر می‌شود', out.includes('داخل پنجرهٔ کاری'));

/* ── ۶) پنجرهٔ غیرفعال ────────────────────────────────────────────────── */
sandbox.window.__ftSelfTest = {
  generated_at: '2026-11-16 03:00:00',
  elapsed_ms: 100,
  window: { enabled: false, open: true },
  verdict: { level: 'warn', headline: 'کار می‌کند ولی ۱ هشدار دارد.', counts: { ok: 5, warn: 1, fail: 0, info: 1, skip: 0 } },
  sections: [],
  text: 'گزارش',
};
out = selftestBox();
check('پنجرهٔ غیرفعال: پیام «شبانه‌روز» نمایش داده می‌شود', out.includes('شبانه‌روز'));
check('پنجرهٔ غیرفعال: آیکن ⚠️ دارد', out.includes('⚠️'));

/* ── ۷) امنیت: HTML تزریقی باید escape شود ───────────────────────────── */
sandbox.window.__ftSelfTest = {
  generated_at: 'x',
  elapsed_ms: 1,
  window: { enabled: true, open: true },
  verdict: { level: 'ok', headline: '<img src=x onerror=alert(1)>', counts: { ok: 1, warn: 0, fail: 0, info: 0, skip: 0 } },
  sections: [{ key: 'x', title: '<script>bad()</script>', checks: [{ name: '<b>x</b>', status: 'ok', message: '<iframe>', fix: '<svg>' }] }],
  text: 'گزارش',
};
out = selftestBox();
check('امنیت: تگ img در حکم escape شده', !out.includes('<img src=x'));
check('امنیت: تگ script در عنوان escape شده', !out.includes('<script>bad()'));
check('امنیت: تگ iframe در پیام escape شده', !out.includes('<iframe>'));
check('امنیت: متن escape شده دیده می‌شود', out.includes('&lt;img'));

/* ── ۸) خود صفحه: جای درست قرارگیری ──────────────────────────────────── */
check('تابع connections آزمون را بعد از فرم نشان می‌دهد', /\$\{selftestBox\(\)\}<\/section>/.test(html));
check('آزمون بعد از فرم تنظیمات تردد است', html.indexOf('</form>${selftestBox()}') > html.indexOf('id="connection-form"'));

console.log('\n' + '='.repeat(60));
console.log('PASS=' + pass + ' | FAIL=' + fail);
if (fail > 0) {
  console.log('❌ رندر پنل ناموفق بود.');
  process.exit(1);
}
console.log('✅ رندر بخش آزمون در پنل سالم است.');
process.exit(0);
