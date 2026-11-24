/* مرکز درخواست‌ها — رفتارهای پیش‌روندهٔ UI (اختیاری؛ بدون آن هم صفحه کار می‌کند).
 *
 * ۱) وضعیت «در حال ارسال» برای فرم‌های POST: دکمه‌های ثبت غیرفعال می‌شوند و
 *    فرم aria-busy می‌گیرد تا هم دوبار-ثبت جلوگیری شود و هم خوانش صفحه‌خوان درست باشد.
 * ۲) فرم‌های GET دست‌نخورده می‌مانند.
 * ۳) اگر مرورگر قبل از submit پاسخ سرور را بگیرد (ناوبری کامل صفحه) مشکلی نیست؛
 *    صفحه تازه رندر می‌شود. برای ارسال‌های درون‌صفحه‌ای (fetch) این کلاس فقط بصری است.
 *
 * ۱.۶۳.۳ — دو نکتهٔ ریشه‌ای در همان شنوندهٔ submit:
 *  الف) «لغو توسط شنوندهٔ دیگر»: اگر پیش از این شنونده، ارسال با preventDefault() لغو
 *      شده باشد (مثلاً کاربر در window.confirm دکمهٔ «انصراف» را زده)، فرم باید
 *      دقیقاً همان‌طور که هست بماند؛ دکمه‌ای نباید غیرفعال و فرم aria-busy شود.
 *  ب) «دکمهٔ نام‌دارِ ارسال، بخشی از دادهٔ فرم است»: اگر دکمه‌ای مثل
 *      <button name="decision" value="approve"> غیرفعال شود، مرورگر مقدارش را
 *      در فرم نمی‌فرستد (کنترل غیرفعال، کنترل موفق نیست) و سرور دکمهٔ فشرده‌شده را
 *      نمی‌بیند؛ دقیقاً خطای «تصمیم دربارهٔ درخواست لغو معتبر نیست» در کارت
 *      «تصمیم مسئول رسیدگی دربارهٔ لغو». پس پیش از غیرفعال‌سازی، فقط مقدارِ
 *      همین دکمهٔ فشرده‌شده در یک input[type=hidden] کپی می‌شود.
 */
(function () {
  'use strict';

  if (window.__rcEnhancementsLoaded) {
    return;
  }
  window.__rcEnhancementsLoaded = true;

  function injectSubmitterCarrier(form, button) {
    // فقط نام/مقدارِ دکمهٔ واقعاً فشرده‌شده کپی می‌شود، نه همهٔ دکمه‌های نام‌دارِ
    // فرم؛ وگرنه هر دو مقدار (مثل approve و reject) ارسال می‌شد و سرور آخرین
    // مقدار را می‌دید — یعنی «تأیید لغو» می‌توانست به «رد» تبدیل شود.
    var carrier = document.createElement('input');
    carrier.type = 'hidden';
    carrier.name = button.name;
    carrier.value = button.value;
    carrier.setAttribute('data-rc-submit-carrier', '1');
    form.appendChild(carrier);
  }

  document.addEventListener('submit', function (event) {
    if (event.defaultPrevented) {
      // ارسال توسط شنوندهٔ مقدم لغو شده است؛ فرم را دست‌نخورده بگذار.
      return;
    }
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
    // حاملِ تزریق‌شدهٔ قبلی را پاک کن تا در هر ارسال دقیقاً یک نسخه از نام/مقدار
    // دکمهٔ فشرده‌شده وجود داشته باشد.
    var staleCarriers = form.querySelectorAll('input[data-rc-submit-carrier]');
    for (var s = 0; s < staleCarriers.length; s += 1) {
      if (staleCarriers[s].parentNode) {
        staleCarriers[s].parentNode.removeChild(staleCarriers[s]);
      }
    }
    // event.submitter در همهٔ مرورگرهای مدرن در دسترس است (دکمهٔ واقعاً فشرده‌شده).
    var submitter = event.submitter || null;
    if (submitter && submitter.form === form && submitter.name) {
      injectSubmitterCarrier(form, submitter);
    }
    var buttons = form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
    for (var i = 0; i < buttons.length; i += 1) {
      var button = buttons[i];
      if (button.disabled) {
        continue; // قبلاً غیرفعال بوده (مثلاً لغو pending) — دست نزن
      }
      if (!submitter && button.name) {
        // مرورگر قدیمی event.submitter ندارد؛ دکمهٔ نام‌دار را غیرفعال نکن تا مقدارش گم نشود.
        continue;
      }
      button.disabled = true;
      button.classList.add('rc-busy');
      button.setAttribute('aria-busy', 'true');
    }
  }, false);

  // نشانهٔ بصری وضعیت فوکوس صفحه‌کلید (دسترس‌پذیری)
  document.addEventListener('DOMContentLoaded', function () {
    document.documentElement.classList.add('rc-js-ready');
  });
})();
