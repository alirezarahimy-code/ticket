/* مرکز درخواست‌ها — رفتارهای پیش‌روندهٔ UI (اختیاری؛ بدون آن هم صفحه کار می‌کند).
 *
 * ۱) وضعیت «در حال ارسال» برای فرم‌های POST: دکمه‌های ثبت غیرفعال می‌شوند و
 *    فرم aria-busy می‌گیرد تا هم دوبار-ثبت جلوگیری شود و هم خوانش صفحه‌خوان درست باشد.
 * ۲) فرم‌های GET دست‌نخورده می‌مانند.
 * ۳) اگر مرورگر قبل از submit پاسخ سرور را بگیرد (ناوبری کامل صفحه) مشکلی نیست؛
 *    صفحه تازه رندر می‌شود. برای ارسال‌های درون‌صفحه‌ای (fetch) این کلاس فقط بصری است.
 */
(function () {
  'use strict';

  if (window.__rcEnhancementsLoaded) {
    return;
  }
  window.__rcEnhancementsLoaded = true;

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || form.tagName !== 'FORM') {
      return;
    }
    var method = String(form.getAttribute('method') || 'get').toLowerCase();
    if (method !== 'post') {
      return; // فقط جلوگیری از دوبار-ثبت POST مهم است
    }
    if (form.hasAttribute('data-rc-no-busy')) {
      return;
    }
    form.setAttribute('aria-busy', 'true');
    var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    for (var i = 0; i < buttons.length; i += 1) {
      var button = buttons[i];
      if (button.disabled) {
        continue; // قبلاً غیرفعال بوده (مثلاً لغو pending) — دست نزن
      }
      button.disabled = true;
      button.classList.add('rc-busy');
      button.setAttribute('aria-busy', 'true');
    }
  }, true);

  // نشانهٔ بصری وضعیت فوکوس صفحه‌کلید (دسترس‌پذیری)
  document.addEventListener('DOMContentLoaded', function () {
    document.documentElement.classList.add('rc-js-ready');
  });
})();
