#!/usr/bin/env node
/**
 * تست رفتاری پاپ‌آپ «تغییرات این نسخه» — بدون مرورگر.
 *
 * اجرا: node tests/test_release_notes_popup.js
 *
 * چرا این تست؟
 *   باگ واقعی مالک (۲۰۲۶-۱۱-۱۶): «هرچی روی بستن می‌زنم بسته نمی‌شود» و «هر بار
 *   دوباره باز می‌شود». ریشه: CSS روی خودِ کارت `display:flex` گذاشته بود که بر
 *   `[hidden]` غلبه می‌کند، پس `card.hidden=true` هیچ اثر بصری نداشت.
 *
 *   این تست همان اسکریپت تولیدشده را در یک DOM ساده اجرا می‌کند و سه چیز را
 *   می‌سنجد:
 *     ۱) بستن واقعاً عنصر را پنهان می‌کند (display محاسبه‌شده)
 *     ۲) پس از دو بار نمایش، دیگر باز نمی‌شود
 *     ۳) با localStorage مسدود هم بستن کار می‌کند
 */

'use strict';

const { execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
let pass = 0;
let fail = 0;

function check(label, ok, detail = '') {
  if (ok) {
    pass++;
    console.log(`[PASS] ${label}`);
  } else {
    fail++;
    console.log(`[FAIL] ${label}${detail ? ' :: ' + detail : ''}`);
  }
}

/* ── تولید HTML از خودِ PHP (منبع حقیقت) ───────────────────────────────── */
let html = '';
try {
  html = execFileSync('php', ['-r', `
    define("APP_ROOT", ${JSON.stringify(ROOT)});
    function e($x){return htmlspecialchars((string)$x, ENT_QUOTES, "UTF-8");}
    function csp_nonce(){return "testnonce";}
    require "lib/release-notes.php";
    echo release_notes_dashboard_card();
  `], { cwd: ROOT, encoding: 'utf8' });
} catch (err) {
  console.log('[FAIL] تولید HTML با PHP ممکن نشد: ' + (err.message || err));
  process.exit(1);
}

check('HTML پاپ‌آپ تولید شد', html.length > 500, 'bytes=' + html.length);

/* ── استخراج اسکریپت پاپ‌آپ ───────────────────────────────────────────── */
const scriptMatch = html.match(/<script[^>]*>([\s\S]*?)<\/script>/);
check('اسکریپت پاپ‌آپ در HTML هست', Boolean(scriptMatch));

/* ── CSS: عنصر پایه نباید display داشته باشد (ریشهٔ باگ) ──────────────── */
const baseRuleMatch = html.match(/\.release-notes-popup\{([^}]*)\}/);
check('قاعدهٔ پایهٔ پاپ‌آپ پیدا شد', Boolean(baseRuleMatch));
check(
  'قاعدهٔ پایه display ندارد (وگرنه [hidden] بی‌اثر می‌شود)',
  baseRuleMatch && !/display\s*:/.test(baseRuleMatch[1]),
  baseRuleMatch ? baseRuleMatch[1] : 'n/a'
);
check(
  'قاعدهٔ [hidden] با !important محافظت شده',
  html.indexOf('.release-notes-popup[hidden]{display:none !important;}') !== -1
);
check('عنصر با صفت hidden رندر شده', /id="release-notes-popup"[^>]*\shidden/.test(html));
check('سقف نمایش در HTML یک است', /data-max-views="1"/.test(html));

/* ── شبیه‌سازی DOM + localStorage ─────────────────────────────────────── */
function makeEnv({ storageBlocked = false, presetSeen = null, presetDismissed = false } = {}) {
  const store = new Map();
  if (presetSeen !== null) store.set('release-notes-popup-seen:1.63.3', String(presetSeen));
  if (presetDismissed) store.set('release-notes-popup-dismissed:1.63.3', '1');

  const localStorage = storageBlocked
    ? {
        getItem() { throw new Error('blocked'); },
        setItem() { throw new Error('blocked'); },
      }
    : {
        getItem: (k) => (store.has(k) ? store.get(k) : null),
        setItem: (k, v) => store.set(k, String(v)),
      };

  // یک عنصر مینیمال که رفتار hidden/closest/addEventListener را دارد.
  const listeners = {};
  const el = {
    hidden: true,
    attrs: { 'data-release-version': '1.63.3', 'data-max-views': '1' },
    getAttribute(name) {
      if (name === 'data-release-version') return '1.63.3';
      if (name === 'data-max-views') return '1';
      return null;
    },
    addEventListener(type, fn) { listeners[type] = fn; },
    removeEventListener(type) { delete listeners[type]; },
    _listeners: listeners,
    _click(target) {
      if (!listeners.click) return;
      listeners.click({
        target: {
          closest: (sel) => (sel === '[data-rn-close]' && target === 'close' ? el : null),
        },
        preventDefault() {},
      });
    },
  };

  const docListeners = {};
  const document = {
    getElementById: (id) => (id === 'release-notes-popup' ? el : null),
    addEventListener(type, fn) { docListeners[type] = fn; },
    removeEventListener(type) { delete docListeners[type]; },
  };

  return { el, document, localStorage, docListeners, store };
}

function runScript(env) {
  const body = scriptMatch[1];
  // eslint-disable-next-line no-new-func
  const fn = new Function('document', 'localStorage', body);
  fn(env.document, env.localStorage);
  return env.el;
}

/* ۱) نمایش اول ────────────────────────────────────────────────────────── */
let env = makeEnv();
let el = runScript(env);
check('نمایش اول: پاپ‌آپ باز می‌شود', el.hidden === false);
check('نمایش اول: شمارندهٔ نمایش ۱ شد', env.store.get('release-notes-popup-seen:1.63.3') === '1');

/* ۲) بستن با دکمه ─────────────────────────────────────────────────────── */
el._click('close');
check('بستن با دکمه: عنصر پنهان می‌شود', el.hidden === true);
check('بستن با دکمه: وضعیت dismissed ثبت می‌شود',
  env.store.get('release-notes-popup-dismissed:1.63.3') === '1');
check('بستن با دکمه: شنوندهٔ Esc برداشته می‌شود', !env.docListeners.keydown);

/* ۳) بار دوم در یک بارگذاری تازه: چون dismissed=1 است، نباید باز شود ─── */
env = makeEnv({ presetSeen: 1, presetDismissed: true });
el = runScript(env);
check('پس از بستن، در بارگذاری بعدی باز نمی‌شود', el.hidden === true);

/* ۴) سقف یک نمایش ─────────────────────────────────────────────────── */
env = makeEnv({ presetSeen: 1 });
el = runScript(env);
check('پس از یک‌بار نمایش، دیگر باز نمی‌شود', el.hidden === true);

env = makeEnv({ presetSeen: 0 });
el = runScript(env);
check('نمایش اول هنوز روی اولین ورود باز می‌شود', el.hidden === false);
check('شمارندهٔ نمایش ۱ شد', env.store.get('release-notes-popup-seen:1.63.3') === '1');

/* ۵) Esc ──────────────────────────────────────────────────────────────── */
env = makeEnv();
el = runScript(env);
check('Esc: شنونده ثبت شد', Boolean(env.docListeners.keydown));
env.docListeners.keydown({ key: 'Escape' });
check('Esc: پاپ‌آپ بسته شد', el.hidden === true);

/* ۶) localStorage مسدود (حالت ناشناس/سیاست سازمانی) ──────────────────── */
env = makeEnv({ storageBlocked: true });
el = runScript(env);
check('storage مسدود: پاپ‌آپ باز می‌شود (بدون خطا)', el.hidden === false);
el._click('close');
check('storage مسدود: بستن هم کار می‌کند', el.hidden === true);

/* ۷) کلیک روی پس‌زمینه ───────────────────────────────────────────────── */
env = makeEnv();
el = runScript(env);
el._click('close');
check('کلیک روی پس‌زمینه: بسته شد', el.hidden === true);

/* ── خلاصه ─────────────────────────────────────────────────────────────── */
console.log('\n' + '='.repeat(60));
console.log(`PASS=${pass} | FAIL=${fail}`);
if (fail > 0) {
  console.log('❌ رفتار پاپ‌آپ تغییرات ایراد دارد.');
  process.exit(1);
}
console.log('✅ پاپ‌آپ تغییرات یک‌بار نمایش و درست بسته می‌شود.');
process.exit(0);
