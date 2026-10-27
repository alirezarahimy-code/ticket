/*
 * مرتب‌ساز موقت لیست‌ها — فقط برای نمایش، چاپ و خروجی مرورگر.
 * - ترتیب ثبت‌شده در دیتابیس تغییر نمی‌کند؛ فقط ترتیب ردیف‌های DOM همین صفحه عوض می‌شود.
 * - وضعیت مرتب‌سازی فقط در حافظهٔ همین صفحه است و با بستن یا ترک صفحه از بین می‌رود.
 * - چرخهٔ کلیک روی سرستون: صعودی ← نزولی ← ترتیب اصلی.
 * - خالی‌ها همیشه آخر لیست می‌مانند.
 * - خروج یک لیست یا یک ستون از مرتب‌سازی: data-no-sort روی جدول/کانتینر یا روی سرستون.
 * - لیست‌هایی که در سمت سرور صفحه‌بندی می‌شوند باید پیش از فعال‌سازی به «کل نتایج فیلتر» بروند؛
 *   تا آن زمان data-no-sort دارند.
 */
(function () {
  'use strict';

  var STYLE_ID = 'ls-style';
  var MARK = 'ls-rows';
  var collator = (typeof Intl !== 'undefined' && Intl.Collator) ? new Intl.Collator('fa', { numeric: true, sensitivity: 'base' }) : null;
  var NUMBER_RE = /^[-+]?\d+(\.\d+)?%?$/;
  var DATE_RE = /^(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?/;

  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    var style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = [
      '.ls-sort-btn{background:none;border:0;padding:0;margin:0;font:inherit;color:inherit;cursor:pointer;text-align:inherit;white-space:nowrap;}',
      '.ls-sort-btn:hover{text-decoration:underline;}',
      '.ls-sort-btn:focus-visible{outline:2px solid #2b9568;outline-offset:2px;border-radius:4px;}',
      '.ls-sort-btn .ls-arrow{font-size:10px;opacity:.75;margin-inline-start:3px;}'
    ].join('');
    document.head.appendChild(style);
  }

  function normalize(text) {
    return String(text || '')
      .replace(/[\u06F0-\u06F9]/g, function (d) { return String(d.charCodeAt(0) - 0x06F0); })
      .replace(/[\u0660-\u0669]/g, function (d) { return String(d.charCodeAt(0) - 0x0660); })
      .replace(/[\u066C\u060C,٬]/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function pad(v) { return ('0' + String(v)).slice(-2); }

  // کلید مرتب‌سازی: عدد، تاریخ (yyyymmddhhmmss) یا متن فارسی
  function sortKey(raw) {
    var text = normalize(raw);
    if (text === '' || text === '—' || text === '-') return { empty: true };
    if (NUMBER_RE.test(text)) return { type: 'n', value: parseFloat(text) };
    var d = DATE_RE.exec(text);
    if (d) {
      return { type: 'd', value: d[1] + pad(d[2]) + pad(d[3]) + pad(d[4] || 0) + pad(d[5] || 0) + pad(d[6] || 0) };
    }
    return { type: 's', value: text };
  }

  function compare(a, b) {
    if (a.empty || b.empty) return 0;
    if (a.type === 'n' && b.type === 'n') return a.value - b.value;
    if (a.type === 'd' && b.type === 'd') return a.value < b.value ? -1 : (a.value > b.value ? 1 : 0);
    if (collator) return collator.compare(String(a.value), String(b.value));
    return String(a.value) < String(b.value) ? -1 : (String(a.value) > String(b.value) ? 1 : 0);
  }

  // ردیف‌های داده را از DOM فعلی می‌خواند؛ ردیف‌های placeholder و خالی کنار گذاشته می‌شوند
  function collect(container) {
    var table = container.tagName === 'TABLE' ? container : null;
    if (table) {
      var headRow = table.tHead && table.tHead.rows[0];
      // جدول بدون thead: اگر ردیف اول فقط سرستون (th) باشد، همان سرستون است.
      if (!headRow && table.rows.length > 1 && Array.prototype.every.call(table.rows[0].cells, function (c) { return c.tagName === 'TH'; })) {
        headRow = table.rows[0];
      }
      if (!headRow) return null;
      var body = table.tBodies[0] || table;
      var headers = Array.prototype.slice.call(headRow.cells);
      var rows = Array.prototype.slice.call(body.rows).filter(function (tr) {
        return tr !== headRow && tr.parentNode === body && tr.cells.length === headers.length && !tr.hasAttribute('data-ls-skip');
      });
      return { container: container, headers: headers, rows: rows, cells: function (tr) { return tr.cells; }, parent: body };
    }
    var head = null;
    // سطر سرستون: table-head (الگوی عمومی) یا service-row-head (فهرست خدمات)
    var HEAD_CLASSES = ['table-head', 'service-row-head'];
    var ROW_CLASSES = ['table-row', 'cd-dvd-record-row', 'service-row'];
    function hasAny(el, list) { for (var k = 0; k < list.length; k++) { if (el.classList.contains(list[k])) return true; } return false; }
    for (var i = 0; i < container.children.length; i++) {
      if (hasAny(container.children[i], HEAD_CLASSES)) { head = container.children[i]; break; }
    }
    if (!head) return null;
    var headerCells = Array.prototype.slice.call(head.children);
    if (headerCells.length < 1) return null;
    var dataRows = Array.prototype.slice.call(container.children).filter(function (el) {
      return hasAny(el, ROW_CLASSES) && el !== head && !hasAny(el, HEAD_CLASSES) && !el.hasAttribute('data-ls-skip') && el.children.length >= headerCells.length;
    });
    return { container: container, headers: headerCells, rows: dataRows, cells: function (row) { return row.children; }, parent: container };
  }

  // نشانگر ثابت بعد از آخرین ردیف؛ پیدا یا ساخته می‌شود تا با rerender گم نشود
  function anchorFor(cfg) {
    var node = null;
    for (var n = cfg.parent.firstChild; n; n = n.nextSibling) {
      if (n.nodeType === 8 && n.data === MARK) { node = n; break; }
    }
    if (!node) {
      node = document.createComment(MARK);
      var last = cfg.rows.length ? cfg.rows[cfg.rows.length - 1] : null;
      if (last && last.parentNode === cfg.parent) cfg.parent.insertBefore(node, last.nextSibling);
      else cfg.parent.appendChild(node);
    }
    return node;
  }

  function applySort(cfg, state, columnIndex, direction) {
    var rows = cfg.rows;
    // ترتیب اصلی: همان ترتیب ثبت‌شدهٔ DOM هنگام فعال‌شدن مرتب‌ساز (ردیف‌های حذف‌شده کنار گذاشته می‌شوند)
    if (!state.original) state.original = rows.slice();
    var base = state.original.filter(function (row) { return rows.indexOf(row) >= 0; });
    rows.forEach(function (row) { if (base.indexOf(row) < 0) base.push(row); });
    var ordered = base;
    if (direction !== 0) {
      var keyed = base.map(function (row, index) {
        return { row: row, index: index, key: sortKey(cfg.cells(row)[columnIndex] ? cfg.cells(row)[columnIndex].textContent : '') };
      });
      keyed.sort(function (x, y) {
        if (x.key.empty || y.key.empty) {
          if (x.key.empty && y.key.empty) return x.index - y.index;
          return x.key.empty ? 1 : -1;            // خالی‌ها همیشه آخر
        }
        var c = compare(x.key, y.key);
        if (c === 0) return x.index - y.index;
        return direction === 1 ? c : -c;
      });
      ordered = keyed.map(function (x) { return x.row; });
    }
    var anchor = anchorFor(cfg);
    ordered.forEach(function (row) { cfg.parent.insertBefore(row, anchor); });
    if (direction !== 0) cfg.container.setAttribute('data-ls-active', '1');
    else cfg.container.removeAttribute('data-ls-active');
  }

  function updateHeaders(cfg, activeIndex, direction) {
    cfg.headers.forEach(function (th, index) {
      var btn = th.querySelector(':scope > .ls-sort-btn');
      if (!btn) return;
      var old = btn.querySelector(':scope > .ls-arrow');
      if (old) old.parentNode.removeChild(old);
      if (index === activeIndex && direction !== 0) {
        var span = document.createElement('span');
        span.className = 'ls-arrow';
        span.textContent = direction === 1 ? '▲' : '▼';
        btn.appendChild(span);
      }
      th.setAttribute('aria-sort', index === activeIndex && direction !== 0 ? (direction === 1 ? 'ascending' : 'descending') : 'none');
    });
  }

  function attach(container) {
    var cfg = collect(container);
    if (!cfg) return;
    var state = { column: -1, direction: 0, original: null };
    var any = false;
    cfg.headers.forEach(function (th, index) {
      if (th.hasAttribute('data-no-sort') || th.querySelector(':scope > .ls-sort-btn')) return;
      var label = normalize(th.textContent);
      if (label === '') return;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ls-sort-btn';
      btn.setAttribute('title', 'مرتب‌سازی بر اساس ' + label + ' (فقط نمایش)');
      while (th.firstChild) btn.appendChild(th.firstChild);   // محتوای قبلی سرستون حفظ شود
      th.appendChild(btn);
      th.setAttribute('aria-sort', 'none');
      any = true;
      btn.addEventListener('click', function () {
        var fresh = collect(container);       // ردیف‌ها همیشه از DOM فعلی خوانده می‌شوند
        if (!fresh) return;
        if (state.column === index) {
          state.direction = state.direction === 1 ? -1 : (state.direction === -1 ? 0 : 1);
        } else {
          state.column = index;
          state.direction = 1;
        }
        if (state.direction === 0) state.column = -1;
        applySort(fresh, state, state.column, state.direction);
        updateHeaders(fresh, state.column, state.direction);
      });
    });
    if (any) container.setAttribute('data-ls-done', '1');
  }

  function enhance() {
    if (window.ITSM_LIST_SORT_OFF) return;
    injectStyle();
    Array.prototype.slice.call(document.querySelectorAll('table')).forEach(function (table) {
      if (table.hasAttribute('data-ls-done') || table.closest('[data-no-sort]')) return;
      attach(table);
    });
    Array.prototype.slice.call(document.querySelectorAll('.table-head, .service-row-head')).forEach(function (head) {
      var container = head.parentElement;
      if (!container || container.hasAttribute('data-ls-done') || container.closest('[data-no-sort]')) return;
      attach(container);
    });
  }

  var scheduled = false;
  function scheduleEnhance() {
    if (scheduled) return;
    scheduled = true;
    setTimeout(function () { scheduled = false; enhance(); }, 50);
  }

  // ---------- خروجی XLSX از نمای مرتب‌شده ----------
  // بدون مرتب‌سازی، خروجی سرور بدون تغییر اجرا می‌شود.

  // داده‌های نمایش فعلی (با ترتیب DOM و فقط ستون‌های قابل‌مشاهده)
  function buildView(container) {
    var cfg = collect(container);
    if (!cfg) return null;
    var cols = [];
    cfg.headers.forEach(function (th, index) {
      var label = normalize(th.textContent.replace(/[▲▼]/g, ''));
      if (th.hasAttribute('data-no-sort') || label === '') return;
      cols.push({ index: index, label: label });
    });
    var rows = cfg.rows.map(function (row) {
      var cells = cfg.cells(row);
      return cols.map(function (c) {
        var cell = cells[c.index];
        return cell ? cell.textContent.replace(/\s+/g, ' ').trim() : '';
      });
    });
    return { headers: cols.map(function (c) { return c.label; }), rows: rows };
  }

  function fileLabel(container) {
    var scope = container.closest('section, .card, .panel') || container.parentElement;
    var title = scope ? scope.querySelector('h1, h2, h3') : null;
    var label = title ? normalize(title.textContent) : 'list';
    return (label || 'list').replace(/[\\\/:*?"<>|]+/g, ' ').slice(0, 80) || 'list';
  }

  function saveBlob(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { a.parentNode && a.parentNode.removeChild(a); URL.revokeObjectURL(url); }, 1000);
  }

  // ارسال نمای مرتب‌شده به سرور و دریافت XLSX واقعی
  function exportXlsx(title, headers, rows, filename, fallbackHref) {
    var token = window.ITSM_CSRF || window.FOOD_TICKET_CSRF || '';
    return fetch('index.php?action=export_view_xlsx', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
      body: JSON.stringify({ title: title, filename: filename, headers: headers, rows: rows })
    }).then(function (response) {
      if (!response.ok) throw new Error('export failed');
      return response.blob();
    }).then(function (blob) {
      saveBlob(blob, filename + '.xlsx');
    }).catch(function () {
      if (fallbackHref) window.location.href = fallbackHref;
    });
  }

  function exportContainer(container, href) {
    var view = buildView(container);
    if (!view) return;
    var label = fileLabel(container);
    exportXlsx(label, view.headers, view.rows, label, href);
  }

  // نزدیک‌ترین لیست مرتب‌شده؛ اگر فقط یک لیست مرتب‌شده در صفحه باشد همان است
  function activeListFor(el) {
    var scope = el.closest ? el.closest('section, .card, .panel') : null;
    if (scope) {
      var inScope = Array.prototype.slice.call(scope.querySelectorAll('[data-ls-active="1"]'));
      if (scope.hasAttribute('data-ls-active')) inScope.unshift(scope);
      if (inScope.length === 1) return inScope[0];
      if (inScope.length > 1) return null;
    }
    var all = document.querySelectorAll('[data-ls-active="1"]');
    return all.length === 1 ? all[0] : null;
  }

  var EXPORT_HREF = /[?&]action=export_|[?&]export=|[?&]download=(csv|xlsx)/;

  function onExportClick(event) {
    if (window.ITSM_LIST_SORT_OFF || event.defaultPrevented) return;
    var link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
    if (!link || !EXPORT_HREF.test(link.getAttribute('href') || '')) return;
    var container = activeListFor(link);
    if (!container) return;                // بدون مرتب‌سازی: خروجی سرور
    event.preventDefault();
    event.stopPropagation();
    exportContainer(container, link.getAttribute('href'));
  }

  function start() {
    document.addEventListener('click', onExportClick, true);
    enhance();
    // صفحه‌هایی که لیست را با JS بازسازی می‌کنند هم پوشش داده شوند
    if (typeof MutationObserver !== 'undefined') {
      new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
          if (mutations[i].addedNodes && mutations[i].addedNodes.length) { scheduleEnhance(); return; }
        }
      }).observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
  window.ITSM_LIST_SORT = { enhance: enhance, buildView: buildView, exportXlsx: exportXlsx };
})();
