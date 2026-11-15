/** ثبت دستی فیش — زیرمنوی سلامت سیستم در تنظیمات پنل غذا. */
(function () {
  'use strict';
  if (typeof window === 'undefined') return;

  const S = {
    manualEmployees: [], manualItems: [], manualFilter: '', manualSelected: [],
    manualLoading: false, manualSaving: false, manualError: '', manualLoaded: false,
    manualResults: [], manualWithTicket: true, manualPhysicalPrint: false, manualReason: '',
    manualDate: '',
  };
  const esc = (x) => String(x == null ? '' : x).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  const say = (m) => { try { if (typeof toast === 'function') toast(m); } catch (e) { /* ignore */ } };
  const callApi = (path, opts) => (typeof api === 'function' ? api(path, opts) : Promise.reject(new Error('اتصال به سرور در دسترس نیست.')));
  const isoToday = () => {
    try {
      return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Tehran', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());
    } catch (e) { return new Date().toISOString().slice(0, 10); }
  };
  // ۱.۶۲.۹: ردیف «مهمان» با شناسهٔ صفر همیشه اول فهرست است و همیشه قابل انتخاب می‌ماند.
  const isGuestRow = (u) => Number(u && u.id) === 0;
  const canIssueManualTicket = () => window.FOOD_TICKET_CAN_MANUAL_TICKET === true;
  const isReplaceableNoTicket = (u) => !!u && !!u.today_record
    && u.today_record.event_type === 'no_food'
    && u.today_record.print_status !== 'printed'
    && S.manualWithTicket && canIssueManualTicket();
  // «فیش یتیم»: در صف چاپ ماند و هرگز چاپ نشد (پرینتر خراب بود).
  // چنین ردیفی حق دارد دستی ثبت شود؛ وگرنه غذا نه تحویل ثبت می‌شود و نه راه اصلاح دارد.
  // هم‌تراز با food_ticket_event_is_orphan_ticket() در food-ticket-groups.php.
  const isOrphanTicket = (u) => {
    const r = u && u.today_record;
    if (!r) return false;
    // «بدون فیش» ردیف عمدی است، نه فیشِ گم‌شده. فیشی ساخته نشده که در صف
    // چاپ مانده باشد. این ردیف‌ها مسیر جایگزینی جدا دارند (no_food).
    if (String(r.event_type || '').toLowerCase() === 'no_food') return false;
    const ps = String(r.print_status || '').toLowerCase();
    const ds = String(r.delivery_status || '').toLowerCase();
    if (ps === 'printed' || ds === 'delivered') return false;
    // not_printed = مقدار پیش‌فرض ستون؛ ردیفی که هرگز به صف چاپ نرسید.
    // هم‌تراز با food_ticket_event_is_orphan_ticket() در سرور.
    return ['not_printed', 'pending', 'printing', 'print_error', 'failed', 'held_absent', ''].indexOf(ps) !== -1;
  };
  // «قابل لغو»: هر ردیفی که برای این روز چیزی ثبت کرده و فیشش
  // چاپ‌نشده/یتیم نیست. یعنی: فیش چاپ‌شده، تردد بدون فیش، یا هر ردیف
  // صف/تکرار. کاربرها باید بتوانند خودشان انتخاب کنند کدام را لغو کنند
  // تا به وضعیت «غذا سفارش داده ولی فیش چاپ نشده» بروند.
  // فیش یتیم (در صف چاپ مانده) لازم نیست لغو شود؛ همان با «ثبت گروهی»
  // به فیش تبدیل می‌شود.
  const isCancelableTicket = (u) => {
    const r = u && u.today_record;
    if (!r) return false;
    if (isOrphanTicket(u)) return false;
    if (r.event_type === 'guest') return false;
    return true;
  };
  const isReplaceableOrphan = (u) => isOrphanTicket(u) && S.manualWithTicket && canIssueManualTicket();
  // شرط «کد پرسنلی لازم است» برای فیش یتیم اعمال نمی‌شود: آن ردیف قبلاً
  // ساخته شده و فقط منتظر تحویل است؛ کد پرسنلی برای ساختن فیش تازه لازم بود.
  // بدون این استثنا، کاربرانی که کد پرسنلی ندارند از انتخاب حذف می‌شدند و
  // دکمهٔ «ثبت گروهی» برایشان هرگز ظاهر نمی‌شد.
  const selectableRow = (u) => !!u && (isGuestRow(u) || (!!u.active
    && (!u.today_record || isReplaceableNoTicket(u) || isReplaceableOrphan(u) || isCancelableTicket(u))
    && (!S.manualWithTicket || !!u.pc || isReplaceableOrphan(u) || isCancelableTicket(u))));
  const manualDateIso = () => S.manualDate || isoToday();
  const jalaliFromIso = (iso) => {
    try {
      const d = new Date(String(iso) + 'T12:00:00Z');
      return new Intl.DateTimeFormat('en-US-u-ca-persian', { timeZone: 'Asia/Tehran', year: 'numeric', month: '2-digit', day: '2-digit' })
        .format(d).replace(/[^0-9/]/g, '').replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3/$1/$2');
    } catch (e) { return String(iso || ''); }
  };
  // ورودی این فیلد «شمسی» است (۱۴۰۵/۰۸/۰۱)، ولی API میلادی می‌خواهد.
  // پیش‌تر اینجا فقط میلادی تجزیه می‌شد و سال شمسی ۱۴۰۵ زیر ۱۷۰۰ رد می‌شد؛
  // نتیجه: تاریخ بی‌صدا عوض نمی‌شد و فهرست همیشه روی «امروز» می‌ماند.
  // یعنی فیش‌های یتیم دیروز هیچ‌وقت دیده نمی‌شدند.
  const isoFromDateInput = (value) => {
    const raw = String(value == null ? '' : value).trim();
    if (!raw) return '';
    // ۱) میلادی از قبل آماده (YYYY-MM-DD)
    const isoDirect = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (isoDirect) {
      const y = Number(isoDirect[1]);
      return y >= 1700 && y <= 2200 ? raw : '';
    }
    // ۲) شمسی (۱۴۰۵/۰۸/۰۱ یا 1405-8-1) → میلادی
    const J = typeof window !== 'undefined' ? window.ItsmJalali : null;
    if (J && typeof J.parse === 'function' && typeof J.j2g === 'function') {
      const parts = J.parse(raw);
      if (parts) {
        const g = J.j2g(parts[0], parts[1], parts[2]);
        if (g && g[0]) {
          const pad = (n) => String(n).padStart(2, '0');
          return g[0] + '-' + pad(g[1]) + '-' + pad(g[2]);
        }
      }
      return '';
    }
    // ۳) بدون کتابخانهٔ تقویم: فقط میلادیِ بدون جداکننده
    const digits = raw.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d))).replace(/\D/g, '');
    if (digits.length !== 8 || Number(digits.slice(0, 4)) < 1700) return '';
    return digits.slice(0, 4) + '-' + digits.slice(4, 6) + '-' + digits.slice(6, 8);
  };

  function loadManualAttendance() {
    if (S.manualLoading) return;
    if (!S.manualDate) S.manualDate = isoToday();
    S.manualLoading = true;
    S.manualError = '';
    drawManualAttendance();
    callApi('/api/food-manual-attendance?date=' + encodeURIComponent(manualDateIso())).then((x) => {
      S.manualEmployees = (x && x.employees) || [];
      S.manualItems = (x && x.items) || [];
      if (x && x.date) S.manualDate = x.date;
      S.manualSelected = S.manualSelected.filter((id) => {
        const u = S.manualEmployees.find((person) => Number(person.id) === Number(id));
        return selectableRow(u);
      });
      S.manualError = '';
      S.manualLoaded = true;
    }).catch((e) => {
      S.manualError = (e && e.message) || 'دریافت فهرست کارکنان ناموفق بود.';
    }).then(() => {
      S.manualLoading = false;
      drawManualAttendance();
    });
  }

  const MANUAL_PRINT_STATUS = {
    not_printed: 'صادر شد؛ چاپ فیزیکی انجام نشد',
    pending: 'در صف چاپ فیزیکی', printing: 'در حال چاپ فیزیکی', printed: 'چاپ فیزیکی انجام شد',
    print_error: 'خطای چاپ فیزیکی', failed: 'چاپ فیزیکی ناموفق', held_absent: 'چاپ متوقف‌شده',
  };

  function manualStatus(u) {
    if (isGuestRow(u)) return '<span class="status ok">مهمان — چند بار قابل ثبت</span>';
    if (!u.active) return '<span class="status bad">حساب غیرفعال</span>';
    const record = u.today_record;
    if (!record && !u.pc) return '<span class="status warn">بدون کد پرسنلی</span>';
    if (!record) return '<span class="status ok">تردد این روز ثبت نشده</span>';
    if (record.event_type === 'printed') {
      if (isOrphanTicket(u)) {
        const ps = String(record.print_status || '').toLowerCase();
        const why = ps === 'print_error' || ps === 'failed' ? 'خطای چاپ' : 'در صف چاپ ماند';
        return '<span class="status warn">' + why + ' — قابل ثبت دستی</span>';
      }
      // غایب ولی فیش چاپ‌شده: این ردیف «حاضر» نیست. چاپ فیش یعنی غذا
      // سهمیه گرفت (شاید کسی دیگر برداشته)، نه اینکه شخص آمده باشد.
      // پیش‌تر فقط «دارای فیش» نوشته می‌شد و کاربر فکر می‌کرد حاضر است.
      if (u.is_absent) {
        return '<span class="status bad">غایب است — فیشش چاپ شده</span>';
      }
      return '<span class="status ok">دارای فیش این روز</span>';
    }
    if (record.source_uid === 'manual-attendance') return '<span class="status warn">ثبت دستی این روز</span>';
    if (record.event_type === 'no_food') return '<span class="status warn">تردد/بدون فیش این روز</span>';
    return '<span class="status warn">تردد این روز ثبت شده</span>';
  }

  function manualAttendancePanel() {
    const canEdit = window.FOOD_TICKET_CAN_MANUAL_ATTENDANCE !== false;
    const canIssueTicket = canIssueManualTicket();
    if (!canIssueTicket) { S.manualWithTicket = false; S.manualPhysicalPrint = false; }
    const query = String(S.manualFilter || '').trim().toLocaleLowerCase();
    const filtered = S.manualEmployees.filter((u) => !query
      || (String(u.name || '') + ' ' + String(u.pc || '')).toLocaleLowerCase().includes(query));
    const selected = new Set(S.manualSelected.map(Number));
    const canSelect = (u) => canEdit && selectableRow(u);
    const rows = filtered.map((u) => {
      const id = Number(u.id);
      const disabled = !canSelect(u);
      const checkbox = canEdit
        ? '<td><input type="checkbox" data-manual-person value="' + id + '"' + (selected.has(id) ? ' checked' : '') + (disabled ? ' disabled' : '') + ' aria-label="انتخاب ' + esc(u.name || 'کارمند') + '"></td>'
        : '';
      return '<tr class="' + (disabled ? 'manual-row-disabled' : '') + '">' + checkbox
        + '<td>' + esc(u.name || '—') + '</td><td>' + (u.pc ? esc(u.pc) : '<span class="muted">—</span>') + '</td>'
        + '<td>' + manualStatus(u) + '</td></tr>';
    }).join('');
    const emptyMessage = S.manualEmployees.length === 0
      ? 'در این روز هیچ کارمند فعالی با سفارش ناهار واجد شرایط پیدا نشد.'
      : 'کاربری مطابق جستجو پیدا نشد.';
    const eligibleVisible = filtered.filter(canSelect);
    // فیش‌های یتیم همین روز — برای دکمهٔ انتخاب گروهی.
    // چرا: رخداد پرینتر خراب یعنی ده‌ها نفر یک‌جا در صف ماندند؛ کلیک نفر‌به‌نفر
    // عملاً یعنی همان ثبت دستی که کاربر نمی‌خواهد.
    // دکمهٔ گروهی از کل فهرست ساخته می‌شود، نه از نتیجهٔ جستجو.
    // پیش‌تر اگر کاربر چیزی در «جستجوی زنده» تایپ کرده بود، ردیف‌های یتیم
    // بیرون از نتیجهٔ جستجو می‌ماندند و دکمه ناپدید می‌شد — با اینکه ردیف
    // یتیم در فهرست بود. (پیام «ردیف یتیم هست ولی نمایش داده نمی‌شود»
    // دقیقاً همین حالت را نشان می‌داد.)
    // ردیف‌هایی که «فیش دارند» و می‌توان لغوشان کرد تا به «تردد ثبت نشده»
    // برگردند و خودکار در فهرست غایبان بیایند.
    const cancelableRows = S.manualEmployees.filter((u) => isCancelableTicket(u) && canEdit);
    const cancelChosenCount = S.manualSelected.filter((id) => {
      const u = S.manualEmployees.find((x) => Number(x.id) === Number(id));
      return isCancelableTicket(u);
    }).length;
    const orphanRows = S.manualEmployees.filter((u) => isOrphanTicket(u) && canIssueManualTicket());
    const orphanCount = orphanRows.length;
    // تشخیص: اگر دکمه نیامد، کاربر باید بتواند بگوید کدام شرط شکسته است.
    // بدون این، سه حالت (بدون داده / بدون مجوز / روز بدون یتیم) از هم
    // قابل تفکیک نبودند و هر بار حدس می‌زدیم.
    const orphanDiag = (function () {
      if (orphanCount > 0) return '';
      if (!S.manualLoaded) return '';
      if (!canIssueManualTicket()) {
        return 'مجوز «ویرایش ثبت دستی» برای این کاربر فعال نیست.';
      }
      const anyOrphan = S.manualEmployees.some((u) => isOrphanTicket(u));
      if (anyOrphan) return 'ردیف یتیم هست ولی نمایش داده نمی‌شود؛ صفحه را دوباره بارگذاری کنید.';
      const withRecord = S.manualEmployees.filter((u) => u && u.today_record).length;
      if (withRecord === 0) {
        return 'برای این روز هیچ ردیف فیشی ثبت نشده است؛ چیزی برای ثبت دستی نیست.';
      }
      return 'این ' + withRecord + ' ردیفِ این روز، همه چاپ‌شده یا تحویل‌شده‌اند؛ یتیمی باقی نمانده.';
    })();
    const orphanSelected = orphanRows.filter((u) => selected.has(Number(u.id))).length;
    // تیک کنار «انتخاب»: با زدنش همهٔ افراد قابل‌انتخاب تیک می‌خورند و با برداشتنش همه بدون تیک می‌شوند.
    const allEligibleSelected = eligibleVisible.length > 0 && eligibleVisible.every((u) => selected.has(Number(u.id)));
    const checkboxHeader = canEdit
      ? '<th class="manual-select-all-head"><label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;white-space:nowrap"><input type="checkbox" data-manual-select-all' + (allEligibleSelected ? ' checked' : '') + (eligibleVisible.length ? '' : ' disabled') + ' aria-label="انتخاب یا لغو انتخاب همهٔ افراد قابل‌انتخاب"> انتخاب</label></th>'
      : '';
    const chosenCount = S.manualSelected.filter((id) => {
      const u = S.manualEmployees.find((x) => Number(x.id) === Number(id));
      return u && canSelect(u);
    }).length;
    const results = S.manualResults.length
      ? '<div class="table-wrap section-gap"><table class="table"><thead><tr><th>کارمند</th><th>نتیجه</th></tr></thead><tbody>'
        + S.manualResults.map((item) => '<tr><td>' + esc(item.name || (item.pc ? 'کد پرسنلی ' + item.pc : 'کاربر #' + (item.user_id || '—')))
          + (item.pc ? ' <small>(' + esc(item.pc) + ')</small>' : '') + '</td><td><span class="status ' + (item.ok ? 'ok' : 'bad') + '">'
          + esc(item.message || (item.ok ? 'ثبت شد.' : 'ثبت ناموفق بود.')) + '</span></td></tr>').join('')
        + '</tbody></table></div>' : '';
    const history = S.manualItems.length
      ? '<div class="table-wrap"><table class="table"><thead><tr><th>کارمند</th><th>وعده</th><th>ساعت</th><th>صدور فیش</th><th>چاپ فیزیکی</th><th>وضعیت چاپ</th></tr></thead><tbody>'
        + S.manualItems.map((item) => {
          const statusText = item.ticket_issued && item.print_status === 'printed' && !item.physical_print
            ? 'در سامانه چاپ‌شده؛ چاپ فیزیکی انجام نشد'
            : (item.ticket_issued ? (MANUAL_PRINT_STATUS[item.print_status] || item.print_status || '—') : 'تردد دستی؛ بدون فیش');
          return '<tr><td>' + esc(item.name || '—') + (item.pc ? ' <small>(' + esc(item.pc) + ')</small>' : '') + '</td>'
            + '<td>' + esc(item.food || (item.ticket_issued ? '—' : 'بدون فیش')) + '</td><td dir="ltr">' + esc(item.time || '—') + '</td>'
            + '<td>' + (item.ticket_issued ? 'صادر شد' : 'صادر نشد') + '</td><td>' + (item.physical_print ? 'بله' : 'خیر') + '</td>'
            + '<td><span class="status ' + (item.print_status === 'printed' ? 'ok' : (['failed', 'print_error'].includes(item.print_status) ? 'bad' : 'warn')) + '">'
            + esc(statusText) + '</span></td></tr>';
        }).join('')
        + '</tbody></table></div>'
      : '<p class="muted">در این روز هنوز ثبت دستی انجام نشده است.</p>';

    const isPastDay = manualDateIso() !== isoToday();
    const dateBadge = isPastDay
      ? '<span class="status warn">ثبت برای روز گذشته: ' + esc(jalaliFromIso(manualDateIso())) + '</span>'
      : '<span class="status ok">امروز</span>';
    return '<div class="card section-gap" id="manual-attendance-page"><div class="form-section-head"><h2>ثبت دستی فیش</h2>'
      + dateBadge + '</div>'
      + '<p class="muted">فهرست فقط کارکنان فعالِ دارای سفارش ناهارِ روز انتخاب‌شده را نشان می‌دهد؛ وضعیت سفارش هنگام ثبت هم دوباره بررسی می‌شود. ردیف «مهمان» همیشه اول فهرست است و بدون نیاز به سفارش، چند بار در روز قابل ثبت است. ثبت فیش از چاپ فیزیکی جداست. اگر ادمین اصلی فردی را انتخاب کند که رویداد «تردد/بدون فیش» دارد، همان رویداد به فیش صادرشده تبدیل می‌شود؛ حالت «بدون فیش» فقط تردد جدید ثبت می‌کند. تاریخ آینده مجاز نیست.</p>'
      + (S.manualError ? '<p class="manual-error" role="alert">' + esc(S.manualError) + '</p>' : '')
      + (S.manualLoading ? '<p class="muted">در حال بارگذاری فهرست کارکنان و ثبت‌های این روز…</p>' : '')
      + '<div class="manual-attendance-controls">'
      + '<label class="field">تاریخ ثبت (شمسی)<input id="fg-manual-date" type="text" data-jalali inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(jalaliFromIso(manualDateIso())) + '"' + (S.manualLoading || S.manualSaving ? ' disabled' : '') + '></label>'
      + '<label class="field">جستجوی زنده<input id="fg-manual-filter" type="search" value="' + esc(S.manualFilter) + '" placeholder="نام یا کد پرسنلی" autocomplete="off"></label>'
      + (canEdit ? '<div class="manual-select-actions">'
        + (orphanCount ? '<button type="button" class="btn primary" data-manual-action="manual-select-orphans">ثبت گروهی ' + orphanCount + ' فیشِ چاپ‌نشده</button>' : '')
        + (orphanDiag ? '<span class="muted" style="font-size:12px;align-self:center">' + esc(orphanDiag) + '</span>' : '')
        + (cancelableRows.length ? '<button type="button" class="btn danger" data-manual-action="manual-cancel-selected"' + (cancelChosenCount ? '' : ' disabled') + '>لغو فیش انتخاب‌شده‌ها (' + cancelChosenCount + ' نفر)</button>' : '')
        + (cancelableRows.length ? '<button type="button" class="btn ghost" data-manual-action="manual-select-cancelable">انتخاب همهٔ قابل‌لغوها (' + cancelableRows.length + ')</button>' : '')
        + '<button type="button" class="btn ghost" data-manual-action="manual-select-visible"' + (eligibleVisible.length ? '' : ' disabled') + '>انتخاب افرادِ قابل‌انتخابِ نتیجه</button>'
        + '<button type="button" class="btn ghost" data-manual-action="manual-clear-selection"' + (chosenCount ? '' : ' disabled') + '>پاک‌کردن انتخاب</button><span class="muted">انتخاب‌شده: ' + chosenCount + ' نفر</span></div>'
        + (cancelableRows.length ? '<p class="muted" style="font-size:12px;margin:0">ابتدا افراد را با چک‌باکس انتخاب کنید، بعد «لغو فیش انتخاب‌شده‌ها» را بزنید. ردیف به «غذا سفارش داده ولی فیش چاپ نشده» برمی‌گردد و شخص غایب ثبت می‌شود.</p>' : '')
        + '<fieldset class="manual-ticket-mode"><legend>نوع ثبت</legend>'
        + (canIssueTicket ? '<label><input type="radio" name="fg-manual-with-ticket" value="yes"' + (S.manualWithTicket ? ' checked' : '') + '> ثبت تردد با صدور فیش</label>' : '')
        + '<label><input type="radio" name="fg-manual-with-ticket" value="no"' + (!S.manualWithTicket ? ' checked' : '') + '> ثبت تردد بدون فیش</label>'
        + (canIssueTicket && S.manualWithTicket ? '<label class="manual-physical-print"><input id="fg-manual-print" type="checkbox"' + (S.manualPhysicalPrint ? ' checked' : '') + '> چاپ فیزیکی فیش از چاپگر</label>' : '')
        + (!canIssueTicket ? '<p class="muted">صدور دستی فیش فقط با مجوز مستقل و دسترسی ادمین اصلی ممکن است.</p>' : '')
        + '</fieldset><label class="field">علت ثبت دستی<input id="fg-manual-reason" maxlength="300" value="' + esc(S.manualReason) + '" placeholder="علت ثبت را وارد کنید؛ برای همهٔ انتخاب‌شده‌ها ثبت می‌شود."></label>'
        + '<div class="actions"><button type="button" class="btn primary" data-manual-action="manual-save"' + (S.manualSaving || S.manualLoading || !chosenCount ? ' disabled' : '') + '>'
        + (S.manualSaving ? 'در حال ثبت…' : 'ثبت تردد برای ' + chosenCount + ' نفر') + '</button><button type="button" class="btn ghost" data-manual-action="manual-reload"' + (S.manualLoading || S.manualSaving ? ' disabled' : '') + '>به‌روزرسانی فهرست</button></div>'
        : '<p class="manual-readonly-note">این فهرست فقط‌خواندنی است؛ برای ثبت دستی، مجوز ویرایش گروه‌های غذا لازم است.</p>') + '</div>'
      + '<div class="manual-attendance-list"><h3>فهرست کارکنان</h3><div class="table-wrap"><table class="table"><thead><tr>' + checkboxHeader + '<th>نام</th><th>کد پرسنلی</th><th>وضعیت امروز</th></tr></thead><tbody>'
      + (rows || '<tr><td colspan="' + (canEdit ? '4' : '3') + '" class="empty">' + esc(emptyMessage) + '</td></tr>')
      + '</tbody></table></div></div>'
      + (S.manualResults.length ? '<div class="manual-results"><h3>نتیجهٔ هر نفر</h3>' + results + '</div>' : '')
      + '<div class="form-section-head" style="margin-top:18px"><h3>ثبت‌های دستی این روز</h3></div>' + history + '</div>';
  }

  function manualAttendanceHtml() {
    return '<section><div id="manual-attendance-root">' + manualAttendancePanel() + '</div></section>';
  }

  function drawManualAttendance() {
    const root = document.getElementById('manual-attendance-root');
    if (!root) return;
    root.innerHTML = manualAttendancePanel();
    // تقویم شمسی مشترک سامانه روی فیلد تاریخ سوار می‌شود (مثل برنامهٔ غذایی).
    try { if (window.ItsmJalali && typeof window.ItsmJalali.enhanceAll === 'function') window.ItsmJalali.enhanceAll(root); } catch (e) { /* ignore */ }
  }

  function applyManualDateInput(value) {
    const iso = isoFromDateInput(value);
    if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return;
    if (iso > isoToday()) { say('ثبت دستی فیش برای تاریخ آینده مجاز نیست.'); return; }
    if (iso === manualDateIso()) return;
    S.manualDate = iso;
    S.manualSelected = [];
    S.manualResults = [];
    S.manualLoaded = false;
    loadManualAttendance();
  }


  document.addEventListener('input', (e) => {
    if (e.target && e.target.id === 'fg-manual-date') {
      applyManualDateInput(e.target.value || '');
      return;
    }
    if (e.target && e.target.id === 'fg-manual-filter') {
      S.manualFilter = e.target.value || '';
      drawManualAttendance();
      const f = document.getElementById('fg-manual-filter');
      if (f) { f.focus(); f.setSelectionRange(f.value.length, f.value.length); }
      return;
    }
    if (e.target && e.target.id === 'fg-manual-reason') {
      S.manualReason = e.target.value || '';
    }
  });

  document.addEventListener('change', (e) => {
    if (e.target && e.target.id === 'fg-manual-date') {
      applyManualDateInput(e.target.value || '');
      return;
    }
    if (e.target && e.target.name === 'fg-manual-with-ticket') {
      S.manualWithTicket = e.target.value === 'yes';
      if (!S.manualWithTicket) S.manualPhysicalPrint = false;
      S.manualSelected = S.manualSelected.filter((id) => {
        const u = S.manualEmployees.find((x) => Number(x.id) === Number(id));
        return selectableRow(u);
      });
      drawManualAttendance();
      return;
    }
    if (e.target && e.target.id === 'fg-manual-print') {
      S.manualPhysicalPrint = !!e.target.checked;
      return;
    }
    if (e.target && e.target.matches && e.target.matches('[data-manual-select-all]')) {
      if (e.target.checked) {
        const q = String(S.manualFilter || '').trim().toLocaleLowerCase();
        const visible = S.manualEmployees.filter((u) => {
          const matches = !q || (String(u.name || '') + ' ' + String(u.pc || '')).toLocaleLowerCase().includes(q);
          return matches && selectableRow(u);
        });
        S.manualSelected = Array.from(new Set(S.manualSelected.map(Number).concat(visible.map((u) => Number(u.id)))));
      } else {
        S.manualSelected = [];
      }
      drawManualAttendance();
      return;
    }
    if (e.target && e.target.matches && e.target.matches('[data-manual-person]')) {
      const id = Number(e.target.value || 0);
      const chosen = new Set(S.manualSelected.map(Number));
      if (e.target.checked) chosen.add(id); else chosen.delete(id);
      S.manualSelected = Array.from(chosen);
      drawManualAttendance();
    }
  });

  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-manual-action]');
    if (!b) return;
    e.preventDefault();
    const act = b.dataset.manualAction;
    if (act === 'manual-reload') { S.manualLoaded = false; loadManualAttendance(); return; }
    if (act === 'manual-select-orphans') {
      // فقط ردیف‌هایی که فیش‌شان در صف مانده و هرگز چاپ نشده؛ فیش واقعی دست نمی‌خورد.
      const ids = S.manualEmployees
        .filter((u) => isOrphanTicket(u) && canIssueManualTicket())
        .map((u) => Number(u.id));
      S.manualSelected = Array.from(new Set(S.manualSelected.map(Number).concat(ids)));
      // حالت «با فیش» — وگرنه تبدیل به فیش انجام نمی‌شود و غذا در گزارش نمی‌آید.
      S.manualWithTicket = true;
      if (!String(S.manualReason || '').trim()) {
        S.manualReason = 'فیش‌های چاپ‌نشدهٔ صف چاپ به‌صورت گروهی ثبت شدند.';
      }
      drawManualAttendance();
      return;
    }
    if (act === 'manual-select-cancelable') {
      // همهٔ ردیف‌های قابل‌لغوِ همین روز را تیک بزن.
      const ids = S.manualEmployees
        .filter((u) => isCancelableTicket(u) && canEdit)
        .map((u) => Number(u.id));
      S.manualSelected = Array.from(new Set(S.manualSelected.map(Number).concat(ids)));
      drawManualAttendance();
      return;
    }
    if (act === 'manual-cancel-selected') {
      // فقط کسانی که کاربر خودش تیک زده — نه همهٔ آن روز.
      const targets = S.manualSelected.map(Number)
        .map((id) => S.manualEmployees.find((u) => Number(u.id) === id))
        .filter((u) => isCancelableTicket(u))
        .map((u) => ({ id: Number(u.id), name: String(u.name || '') }));
      if (!targets.length) { say('ابتدا افراد را با چک‌باکس انتخاب کنید.'); return; }
      const reason = 'لغو فیش — از صفحهٔ ثبت دستی';
      if (!window.confirm('فیش ' + targets.length + ' نفر لغو شود؟\n\n'
        + targets.slice(0, 12).map((t) => '• ' + t.name).join('\n')
        + (targets.length > 12 ? '\n… و ' + (targets.length - 12) + ' نفر دیگر' : '')
        + '\n\nفیششان لغو می‌شود و به «غذا سفارش داده ولی فیش چاپ نشده» می‌روند.')) return;
      S.manualSaving = true;
      S.manualError = '';
      drawManualAttendance();
      let done = 0; const failed = [];
      const chain = targets.reduce((promise, t) => promise.then(() => callApi('/api/cancel-ticket', {
        method: 'POST',
        body: JSON.stringify({ user_id: t.id, date: manualDateIso(), reason, mark_absent: false }),
      }).then(() => { done++; }).catch((err) => { failed.push((t.name || t.id) + ': ' + ((err && err.message) || 'خطا')); })),
        Promise.resolve());
      chain.then(() => {
        S.manualSaving = false;
        S.manualLoaded = false;
        S.manualSelected = [];
        if (failed.length) say('لغو شد: ' + done + ' | ناموفق: ' + failed.length);
        else say(done + ' فیش لغو شد.');
        loadManualAttendance();
      });
      return;
    }
    if (act === 'manual-select-visible') {
      const visible = S.manualEmployees.filter((u) => {
        const q = String(S.manualFilter || '').trim().toLocaleLowerCase();
        const matches = !q || (String(u.name || '') + ' ' + String(u.pc || '')).toLocaleLowerCase().includes(q);
        return matches && selectableRow(u);
      });
      S.manualSelected = Array.from(new Set(S.manualSelected.map(Number).concat(visible.map((u) => Number(u.id)))));
      drawManualAttendance();
      return;
    }
    if (act === 'manual-clear-selection') { S.manualSelected = []; drawManualAttendance(); return; }
    if (act === 'manual-save') {
      // شناسهٔ صفر «مهمان» است و باید ارسال شود.
      const userIds = S.manualSelected.map(Number).filter((id) => Number.isInteger(id) && id >= 0);
      const reason = (document.getElementById('fg-manual-reason') || {}).value || S.manualReason || '';
      const physicalPrint = S.manualWithTicket && !!S.manualPhysicalPrint;
      if (!userIds.length) { say('حداقل یک کارمندِ قابل‌انتخاب را انتخاب کنید.'); return; }
      if (!reason.trim()) { say('علت ثبت دستی را وارد کنید.'); return; }
      S.manualReason = reason;
      S.manualSaving = true;
      S.manualResults = [];
      drawManualAttendance();
      (async () => {
        const results = [];
        let latest = null;
        for (let offset = 0; offset < userIds.length; offset += 100) {
          const batch = userIds.slice(offset, offset + 100);
          try {
            latest = await callApi('/api/food-manual-attendance', { method: 'POST', body: JSON.stringify({ user_ids: batch, date: manualDateIso(), with_ticket: S.manualWithTicket, physical_print: physicalPrint, reason }) });
            results.push(...((latest && latest.results) || []));
          } catch (err) {
            const message = (err && err.message) || 'ارتباط با سرور برقرار نشد.';
            batch.forEach((id) => {
              const person = S.manualEmployees.find((u) => Number(u.id) === Number(id)) || {};
              results.push({ ok: false, user_id: id, name: person.name || '', pc: person.pc || '', message: 'نتیجهٔ ثبت مشخص نیست؛ فهرست را به‌روزرسانی کنید. ' + message });
            });
            userIds.slice(offset + batch.length).forEach((id) => {
              const person = S.manualEmployees.find((u) => Number(u.id) === Number(id)) || {};
              results.push({ ok: false, user_id: id, name: person.name || '', pc: person.pc || '', message: 'به دلیل خطای ارتباطی ارسال نشد.' });
            });
            break;
          }
        }
        if (latest) {
          S.manualEmployees = latest.employees || S.manualEmployees;
          S.manualItems = latest.items || S.manualItems;
          S.manualLoaded = true;
        }
        S.manualResults = results;
        S.manualError = '';
        S.manualSelected = [];
        S.manualReason = '';
        const successCount = results.filter((item) => item.ok).length;
        const failureCount = results.length - successCount;
        drawManualAttendance();
        say(successCount + ' نفر موفق' + (failureCount ? '، ' + failureCount + ' نفر ناموفق' : '') + ' ثبت شدند.');
      })()
        .catch((err) => { S.manualError = (err && err.message) || 'ثبت دستی انجام نشد.'; say(S.manualError); })
        .then(() => { S.manualSaving = false; drawManualAttendance(); });
      return;
    }

  });

  window.FoodManualAttendanceUI = {
    html: manualAttendanceHtml,
    mount() {
      // پیش‌تر اگر FOOD_TICKET_API_BASE نبود بی‌صدا برمی‌گشت و کاربر
      // فهرستی خالی می‌دید بدون هیچ توضیحی. حالا خطا را نشان می‌دهیم.
      if (!window.FOOD_TICKET_API_BASE && typeof api !== 'function') {
        S.manualError = 'اتصال به سرور برقرار نشد؛ صفحه را از پنل اصلی باز کنید.';
        drawManualAttendance();
        return;
      }
      if (!S.manualLoaded && !S.manualLoading) loadManualAttendance();
      else drawManualAttendance();
    },
  };
})();
