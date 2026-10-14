/*
 * مرتب‌سازی سمت سرور بدون رفرش صفحه.
 * لینک‌های سرستون (a.log-sort) فقط بخش همان لیست را از سرور می‌گیرند و جایگزین می‌کنند.
 * صفحه‌بندی و فیلترها همان رفتار قبلی را دارند. اگر مشکلی پیش آید، مرورگر به لینک عادی می‌رود.
 */
(function () {
  'use strict';

  function regionOf(el) {
    var node = el.closest('[id], section');
    return node || null;
  }

  // بخش‌های دارای سرستون، به ترتیب سند؛ برای پیدا کردن همان بخش در پاسخ سرور وقتی شناسه ندارد.
  function sortableSections(root) {
    return Array.prototype.slice.call(root.querySelectorAll('section')).filter(function (s) {
      return s.querySelector('a.log-sort');
    });
  }

  function findFresh(doc, region, index) {
    if (region.id) return doc.getElementById(region.id);
    return sortableSections(doc)[index] || null;
  }

  var busy = false;

  document.addEventListener('click', function (event) {
    var link = event.target && event.target.closest ? event.target.closest('a.log-sort') : null;
    if (!link) return;
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    if (busy) return;

    var region = regionOf(link);
    if (!region) { window.location.href = link.href; return; }
    var index = region.tagName === 'SECTION' ? sortableSections(document).indexOf(region) : -1;
    busy = true;
    region.setAttribute('aria-busy', 'true');
    region.style.opacity = '0.6';

    fetch(link.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
      .then(function (response) {
        if (!response.ok) throw new Error('http ' + response.status);
        return response.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = findFresh(doc, region, index);
        if (!fresh || !region.parentNode) throw new Error('region-missing');
        // اسکریپت‌های داخل بخش اجرا نمی‌شوند؛ فقط HTML جدول لیست جایگزین می‌شود.
        Array.prototype.slice.call(fresh.querySelectorAll('script')).forEach(function (s) { s.parentNode.removeChild(s); });
        region.parentNode.replaceChild(document.importNode(fresh, true), region);
        window.history.pushState({ serverSort: true }, '', link.href);
      })
      .catch(function () {
        window.location.href = link.href;
      })
      .then(function () {
        busy = false;
      });
  });

  // دکمهٔ «بازگشت» مرورگر: برای همگام ماندن با آدرس، صفحه یک‌بار بارگذاری می‌شود.
  window.addEventListener('popstate', function () { window.location.reload(); });
})();
