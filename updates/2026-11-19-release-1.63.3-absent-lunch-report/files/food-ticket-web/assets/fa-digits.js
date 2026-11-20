/*!
 * assets/fa-digits.js — خودکار: ارقام لاتین/عربی در فیلدهای متنی به فارسی (۱٫۶۳٫۳)
 *
 * چرا این‌جا: لایهٔ نمایشی سرور (lib/fa-digits.php) فقط خروجی HTML را فارسی می‌کند.
 * کاربر هنگام «تایپ» با کیبورد لاتین ارقام لاتین وارد می‌کرد و باید دوباره
 * فارسی‌سازی را می‌دید. این ماژول همان تبدیل را در لحظهٔ تایپ انجام می‌دهد.
 *
 * رفتار:
 *   • فقط رویداد input روی input[type=text|search|tel|(بدون type)] و textarea اعمال می‌شود.
 *   • password/email/url/number/date/… و هر چیزی با data-fa-digits="off" (خودِ عنصر یا یکی از
 *     نیای جدّی) مستثنا هستند.
 *   • چون هر دو نگاشت ۱↔۱ در UTF-16 هستند، موقعیت کارت (caret) بدون تغییر می‌ماند؛
 *     مقدار و selectionStart/End صریحاً حفظ و بازیابی می‌شوند.
 *   • مقادیر از پیش پرشده هنگام بارگذاری صفحه و هم هنگام تزریق نودهای جدید (SPA/ajax)
 *     تبدیل می‌شوند.
 *   • ذخیره‌سازی در پایگاه‌داده canonical لاتین می‌ماند: بوت‌استرپ ارقام ورودی
 *     ($_GET/$_POST و JSON) را دوباره لاتین می‌کند؛ نمایش دوباره فارسی می‌شود.
 */
(function () {
  'use strict';

  var TO_FA = { '0': '۰', '1': '۱', '2': '۲', '3': '۳', '4': '۴', '5': '۵', '6': '۶', '7': '۷', '8': '۸', '9': '۹' };
  var AR_TO_FA = { '٠': '۰', '١': '۱', '٢': '۲', '٣': '۳', '٤': '۴', '٥': '۵', '٦': '۶', '٧': '۷', '٨': '۸', '٩': '۹' };

  function toFaDigits(value) {
    return String(value)
      .replace(/[0-9]/g, function (c) { return TO_FA[c]; })
      .replace(/[٠-٩]/g, function (c) { return AR_TO_FA[c]; });
  }

  function isEligible(el) {
    if (!el || el.nodeType !== 1) { return false; }
    var tag = el.tagName ? el.tagName.toUpperCase() : '';
    if (tag === 'TEXTAREA') {
      return !optOut(el);
    }
    if (tag !== 'INPUT') { return false; }
    var type = (el.getAttribute('type') || 'text').toLowerCase().trim();
    if (type !== 'text' && type !== 'search' && type !== 'tel') { return false; }
    return !optOut(el);
  }

  function optOut(el) {
    if (el.getAttribute('data-fa-digits') === 'off') { return true; }
    return !!(el.closest && el.closest('[data-fa-digits="off"]'));
  }

  function convertInPlace(el) {
    var before = el.value;
    var after = toFaDigits(before);
    if (after === before) { return; }
    var start = null;
    var end = null;
    try { start = el.selectionStart; end = el.selectionEnd; } catch (ignored) { /* type=number و… */ }
    el.value = after; // نگاشت ۱:۱ در UTF-16 — موقعیت منطقی کارت عوض نمی‌شود
    if (start !== null && typeof el.setSelectionRange === 'function') {
      try { el.setSelectionRange(start, end); } catch (ignored) { /* غیرقابل تنظیم */ }
    }
  }

  function convertAll(root) {
    var scope = root || document;
    var els = scope.querySelectorAll ? scope.querySelectorAll('textarea, input') : [];
    for (var i = 0; i < els.length; i++) {
      if (isEligible(els[i])) { convertInPlace(els[i]); }
    }
  }

  document.addEventListener('input', function (event) {
    if (isEligible(event.target)) { convertInPlace(event.target); }
  }, true);

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(function () { convertAll(document); });

  // نودهای تازه تزریق‌شده (Ajax / فرزندان تازه) هم پوشش داده می‌شوند.
  if (typeof window.MutationObserver === 'function') {
    ready(function () {
      var observer = new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
          var added = mutations[i].addedNodes || [];
          for (var j = 0; j < added.length; j++) {
            var node = added[j];
            if (!node || node.nodeType !== 1) { continue; }
            if (isEligible(node)) { convertInPlace(node); } else { convertAll(node); }
          }
        }
      });
      observer.observe(document.body, { childList: true, subtree: true });
    });
  }

  // ابزار کمکی برای ماژول‌های دیگر (مثلاً وقتی مقدار را خودشان می‌سازند).
  window.faDigitsToPersian = toFaDigits;
  window.faDigitsToLatin = function (value) {
    return String(value)
      .replace(/[۰-۹]/g, function (d) { return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)); })
      .replace(/[٠-٩]/g, function (d) { return String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)); });
  };
})();
