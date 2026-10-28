/* مدیر برنامهٔ غذایی در پنل چاپ فیش. */
(function (global) {
  'use strict';

  var Cal = global.FoodOrderCalendar;
  var canMenu = global.FOOD_TICKET_CAN_MENU_EDIT === true;
  var canClose = global.FOOD_TICKET_CAN_ORDER_CLOSE === true;
  var isPrimary = global.FOOD_TICKET_IS_PRIMARY_ADMIN === true;
  var apiBase = String(global.FOOD_TICKET_API_BASE || '');
  var csrf = String(global.FOOD_TICKET_CSRF || '');
  var root = null;
  var state = {
    mounted: false,
    embeddedMode: '',
    tab: canMenu ? 'calendar' : 'orders',
    year: 1405,
    month: 1,
    mealType: 'lunch',
    selectedDate: '',
    ordersDate: '',
    statisticsFrom: '',
    statisticsTo: '',
    statisticsMode: 'food',
    reportSelectedEmployees: [],
    reportEmployeeResults: [],
    reportEmployeeQuery: '',
    reportEmployeeSearchTimer: null,
    reportEmployeeSearchSeq: 0,
    reportEmployeeResultsLoaded: false,
    reportEmployeeSearchLoading: false,
    statistics: [],
    statisticsLoaded: false,
    statisticsRequestId: 0,
    deliveredReport: [],
    deliveredReportLoaded: false,
    deliveredReportRange: '',
    deliveredReportRequestId: 0,
    deliveredReportLoading: false,
    deliveredReportEmployeeCount: 0,
    deliveredReportGuestCount: 0,
    deliveredReportView: 'list',
    deliveredReportDate: '',
    deliveredReportSort: { key: 'row', dir: 'asc' },
    deliveredReportFields: ['row', 'national_code', 'employee_number', 'full_name', 'food_name', 'food_date', 'punch_time', 'record_type', 'menu_no', 'delivered_at'],
    monthData: null,
    catalog: [],
    orders: [],
    ordersLoaded: false,
    orderReportFields: ['row', 'national_code', 'employee_number', 'full_name', 'food_name', 'food_date', 'reserve_date', 'reserve_time', 'status_label', 'record_type', 'menu_no', 'meal_type'],
    foodGroups: [],
    foodGroupsLoaded: false,
    groupOrders: [],
    groupOrdersGroups: [],
    groupOrdersView: 'list',
    groupOrdersLoaded: false,
    loadingGroupOrders: false,
    groupOrdersDate: '',
    groupOrdersGroupId: '',
    groupOrdersTitle: '',
    ordersSort: { key: 'row', dir: 'asc' },
    ordersView: 'list',
    status: null,
    loading: false,
    loadingOrders: false,
    loadingCancelRequests: false,
    cancelRequests: [],
    guestRequests: [],
    guestRequestsLoaded: false,
    loadingGuestRequests: false,
    guestRequestSearch: '',
    guestEdit: null,
    guestSaving: false,
    guestActionBusyId: 0,
    loadingStatistics: false,
    error: '',
    notice: '',
    pendingImpact: null,
    pendingImpactExpected: 0,
    pendingImpactMessage: '',
    activeRequest: 0,
    bulk: { operation: 'canteen', date: '', mealType: 'lunch', people: [], groups: [], items: [], selectedIds: [], loading: false, previewOpen: false, results: null },
  };

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (m) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
  }
  function faDigits(value) { return String(value).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[Number(d)]; }); }
  function asciiDigits(value) {
    return String(value == null ? '' : value).replace(/[۰-۹٠-٩]/g, function (d) {
      var p = '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); return p >= 0 ? String(p) : String('٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    });
  }
  function isoFromJalali(value) {
    var normalized = asciiDigits(value).trim().replace(/[.-]/g, '/');
    var match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
    if (!match || !Cal) return null;
    try {
      var year = Number(match[1]), month = Number(match[2]), day = Number(match[3]);
      if (month < 1 || month > 12 || day < 1 || day > Cal.monthLength(year, month)) return null;
      return Cal.isoDate(year, month, day);
    } catch (e) { return null; }
  }
  function jalaliFromIso(iso) {
    if (!Cal || !iso) return '';
    try {
      var d = Cal.jalaliDateFromIso(iso);
      return d ? String(d[0]).padStart(4, '0') + '/' + String(d[1]).padStart(2, '0') + '/' + String(d[2]).padStart(2, '0') : '';
    } catch (e) { return ''; }
  }
  function normalizeIsoDate(value) {
    var normalized = asciiDigits(value).trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
      try { return Cal && Cal.jalaliDateFromIso(normalized) ? normalized : null; } catch (e) { return null; }
    }
    return isoFromJalali(normalized);
  }
  function jalaliMonthFromIso(iso) {
    if (!Cal || !iso) return null;
    try { var d = Cal.jalaliDateFromIso(iso); return d ? [Number(d[0]), Number(d[1])] : null; } catch (e) { return null; }
  }
  function localTodayIso() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  function monthKey() { return String(state.year).padStart(4, '0') + '-' + String(state.month).padStart(2, '0'); }
  function monthTitle() { return Cal.months[state.month - 1] + ' ' + faDigits(state.year); }
  function getDay(iso) { return state.monthData && state.monthData.days ? state.monthData.days[iso] || null : null; }
  function activeOrderCount(day) {
    if (!day || !Array.isArray(day.items)) return 0;
    return day.items.reduce(function (sum, item) { return sum + Number(item.order_count || 0); }, 0);
  }
  function holiday(iso, day) {
    if (day && day.holiday_title) return day.holiday_title;
    var list = global.ITSM_HOLIDAYS || [];
    var jalali = jalaliFromIso(iso);
    for (var i = 0; i < list.length; i++) {
      var item = list[i] || {};
      if (String(item.date || '').slice(0, 10) === iso || String(item.jalali || '').replace(/-/g, '/') === jalali) return item.title || 'تعطیل رسمی';
    }
    return '';
  }
  function buildUrl(route, params) {
    var query = params ? new URLSearchParams(params).toString() : '';
    var base = apiBase;
    if (!base) base = 'index.php?page=food-ticket&food_api=';
    return base + encodeURIComponent(String(route).replace(/^\/+/, '').replace(/^api\//, '')) + (query ? '&' + query : '');
  }
  async function request(route, params, options) {
    var opts = Object.assign({ method: 'GET', credentials: 'same-origin', headers: {} }, options || {});
    opts.headers = Object.assign({ 'Content-Type': 'application/json' }, opts.headers || {});
    if (opts.method !== 'GET' && csrf) opts.headers['X-CSRF-Token'] = csrf;
    var response = await fetch(buildUrl(route, params), opts);
    var text = await response.text();
    var payload = {};
    try { payload = text ? JSON.parse(text) : {}; } catch (e) {
      var parseError = new Error(response.ok ? 'پاسخ نامعتبر از سرور دریافت شد.' : ('خطای سرور ' + response.status));
      parseError.status = response.status; throw parseError;
    }
    if (!response.ok) {
      var error = new Error(payload.error || payload.message || ('خطای سرویس ' + response.status));
      error.status = response.status; error.payload = payload; throw error;
    }
    return payload || {};
  }
  function notify(message, bad) {
    state.notice = String(message || '');
    state.error = bad ? state.notice : '';
    if (!bad) state.notice = String(message || '');
    render();
  }

  function navTabs() {
    var tabs = [];
    if (canMenu || canClose) tabs.push(['calendar', 'برنامهٔ روزانه']);
    if (canMenu) tabs.push(['catalog', 'بانک غذا']);
    if (canEmergency || canManageGuestRequests) tabs.push(['bulk', 'عملیات گروهی']);
    if (canClose) {
      var cancelCount = (state.cancelRequests || []).length;
      var cancelLabel = 'درخواست‌های لغو' + (cancelCount ? (' (' + faDigits(cancelCount) + ')') : '');
      // «سفارش‌های روز» و «آمار سفارش‌ها» به منوی «گزارش‌ها» منتقل شدند.
      tabs.push(['cancel-requests', cancelLabel]);
    }
    return '<div class="fm-tabs" role="tablist">' + tabs.map(function (tab) {
      return '<button class="fm-tab ' + (state.tab === tab[0] ? 'active' : '') + '" type="button" role="tab" aria-selected="' + (state.tab === tab[0] ? 'true' : 'false') + '" data-fm-tab="' + tab[0] + '">' + tab[1] + '</button>';
    }).join('') + '</div>';
  }

  function renderCalendar() {
    if (!state.monthData && state.loading) return '<div class="fm-card fm-spinner">در حال خواندن برنامهٔ ماه…</div>';
    var days = state.monthData && state.monthData.days ? state.monthData.days : {};
    var cells = Cal.monthCells(state.year, state.month);
    var weekdayHeaders = Cal.weekdays.map(function (name) { return '<div class="fo-weekday" role="columnheader">' + esc(name) + '</div>'; }).join('');
    var cellsHtml = cells.map(function (cell) {
      var day = days[cell.iso] || null;
      var holidayName = holiday(cell.iso, day);
      var meals = day && Array.isArray(day.items) ? day.items : [];
      var orderState = day && day.order_status ? day.order_status : '';
      var count = activeOrderCount(day);
      var status = holidayName ? 'تعطیل رسمی' : (orderState === 'closed' ? 'سفارش بسته' : orderState === 'open' && meals.some(function (x) { return x.food_active !== false; }) ? 'سفارش باز' : (meals.length ? 'غذا غیرفعال' : 'بدون برنامه'));
      var classes = ['fo-day'];
      var adjacentAttrs = cell.isCurrentMonth ? '' : ' disabled aria-disabled="true" tabindex="-1"';
      if (!cell.isCurrentMonth) classes.push('is-adjacent');
      if (cell.isWeekend) classes.push('is-weekend');
      if (holidayName) classes.push('is-holiday');
      if (cell.iso === (state.status && state.status.today ? state.status.today : '')) classes.push('is-today');
      if (orderState === 'closed') classes.push('is-closed');
      if (count > 0) classes.push('has-order');
      if (state.selectedDate === cell.iso) classes.push('fm-day is-selected');
      var tip = [cell.jalali, status, holidayName, meals.map(function (x) { return x.food_name + (x.food_active === false ? ' (غیرفعال)' : ''); }).join('، '), count ? faDigits(count) + ' سفارش فعال' : ''].filter(Boolean).join(' • ');
      var mealText = meals.map(function (item) { return '<span class="fo-day-meal">' + esc(item.food_name) + (item.food_active === false ? ' · غیرفعال' : '') + '</span>'; }).join('');
      if (count) mealText += '<span class="fo-day-count">' + faDigits(count) + ' سفارش فعال</span>';
      var orderMarker = count ? '<span class="fo-day-order-marker" title="' + faDigits(count) + ' سفارش فعال">✓ ' + faDigits(count) + ' سفارش</span>' : '';
      return '<button class="' + classes.join(' ') + '" type="button" data-fm-day="' + esc(cell.iso) + '" data-fm-current="' + (cell.isCurrentMonth ? '1' : '0') + '" data-has-food="' + (meals.length ? '1' : '0') + '" data-has-order="' + (count ? '1' : '0') + '" data-tooltip="' + esc(tip) + '" aria-label="' + esc(cell.jalali + '، ' + status + (holidayName ? '، ' + holidayName : '') + (meals.length ? '، ' + meals.map(function (x) { return x.food_name; }).join('، ') : '') + (count ? '، ' + faDigits(count) + ' سفارش فعال' : '')) + '"' + adjacentAttrs + '><span class="fo-day-number-row"><span class="fo-day-number">' + faDigits(cell.day) + '</span>' + (holidayName ? '<span class="fo-day-status is-holiday">تعطیل</span>' : '') + '</span><span class="fo-day-status ' + (orderState === 'open' ? 'is-open' : orderState === 'closed' ? 'is-closed' : '') + '">' + esc(status) + '</span>' + orderMarker + '<span class="fo-day-meals">' + mealText + '</span></button>';
    }).join('');
    return '<div class="fm-grid"><div class="fm-card fm-calendar-card"><div class="fm-toolbar"><div><h3>برنامهٔ غذایی — ' + esc(monthTitle()) + '</h3></div><div class="fm-actions"><button class="btn secondary" type="button" data-fm-action="month-prev">ماه قبل</button><button class="btn secondary" type="button" data-fm-action="month-today">امروز</button><button class="btn secondary" type="button" data-fm-action="month-next">ماه بعد</button></div></div><div class="fo-calendar-grid" role="grid" aria-label="تقویم برنامه غذایی">' + weekdayHeaders + cellsHtml + '</div><div class="fo-legend"><span><i class="legend-open"></i> روز باز</span><span><i class="legend-closed"></i> روز بسته</span><span><i class="legend-holiday"></i> تعطیل رسمی / پنجشنبه و جمعه</span><span><i class="legend-order"></i> سفارش ثبت‌شده</span><span><i class="legend-today"></i> امروز</span></div></div>' + renderDayEditor() + '</div>';
  }

  function renderDayEditor() {
    if (!state.selectedDate) return '<div class="fm-card"><div class="fm-empty">یک روز از تقویم انتخاب کنید.</div></div>';
    var iso = state.selectedDate;
    var day = getDay(iso);
    if (!canMenu) {
      var readOnlyFoods = day && Array.isArray(day.items) ? day.items.map(function (item) { return esc(item.food_name || ''); }).join('، ') : '';
      var readOnlyStatus = day && day.order_status === 'closed' ? 'بسته' : (day && day.order_status === 'open' ? 'باز' : 'بدون برنامه');
      var readOnlyCount = activeOrderCount(day);
      var readOnlyCountHtml = readOnlyCount ? '<p><span class="fo-day-order-marker">' + faDigits(readOnlyCount) + ' سفارش فعال</span></p>' : '';
      return '<div class="fm-card"><div class="fm-toolbar"><div><h3>برنامهٔ روز ' + esc(faDigits(jalaliFromIso(iso))) + '</h3><span class="fm-small">وضعیت سفارش: ' + readOnlyStatus + '</span></div></div>' + (readOnlyFoods ? '<p>' + readOnlyFoods + '</p>' : '<div class="fm-empty">برای این روز برنامهٔ غذایی ثبت نشده است.</div>') + readOnlyCountHtml + '</div>';
    }
    var scheduled = day && Array.isArray(day.items) ? day.items : [];
    var scheduledIds = scheduled.map(function (item) { return Number(item.food_id); });
    var title = jalaliFromIso(iso);
    var inactiveSchedule = scheduled.filter(function (item) { return item.food_active === false; });
    var dayReadOnly = state.status && state.status.today && iso < state.status.today;
    var impactPending = Boolean(state.pendingImpact && state.pendingImpact.date === iso);
    var editorLocked = Boolean(state.daySaving || impactPending || (day && day.order_status === 'closed' && !canClose) || (dayReadOnly && !isPrimary));
    var lockedMessage = day && day.order_status === 'closed' && !canClose ? 'این روز بسته است؛ برای تغییر برنامهٔ روز بسته مجوز food.order_close لازم است.' : (dayReadOnly && !isPrimary ? 'روز گذشته فقط‌خواندنی است؛ فقط ادمین اصلی می‌تواند برنامه‌اش را اصلاح کند.' : '');
    var choiceHtml = function (food) {
      var included = scheduledIds.indexOf(Number(food.id)) >= 0;
      var inactive = !food.active;
      var label = food.food_name + (inactive ? ' · غیرفعال' : '');
      var disabled = editorLocked || (inactive && !included);
      return '<label class="fm-food-check"><input type="checkbox" name="food_ids" value="' + Number(food.id) + '" ' + (included ? 'checked' : '') + (disabled ? ' disabled' : '') + '><span>' + esc(label) + (inactive && included ? ' (فقط حفظ/حذف از برنامه)' : '') + '</span></label>';
    };
    // دسته‌ها ریشه‌اند: «+» غذاهای دسته را دو ستونه باز می‌کند و «-» جمع می‌کند.
    // بدنه همیشه در فرم می‌ماند (فقط hidden)، پس تیک‌های انتخاب‌شده هنگام جمع/باز شدن گم نمی‌شوند.
    var choices = catalogCategoryNames().map(function (name) {
      var foods = (state.catalog || []).filter(function (food) { return (food.category || 'سایر') === name; });
      if (!foods.length) return '';
      var open = !!dayCatalogOpen[name];
      var picked = foods.filter(function (food) { return scheduledIds.indexOf(Number(food.id)) >= 0; }).length;
      return '<div class="fm-cat-group' + (open ? ' open' : '') + '">'
        + '<button type="button" class="fm-cat-toggle" data-fm-daycat="' + esc(name) + '" aria-expanded="' + (open ? 'true' : 'false') + '">'
        + '<span class="fm-cat-sign" aria-hidden="true">' + (open ? '-' : '+') + '</span>'
        + '<strong>' + esc(name) + '</strong>'
        + '<span class="fm-small">' + faDigits(String(foods.length)) + ' غذا' + (picked ? ' · ' + faDigits(String(picked)) + ' انتخاب‌شده' : '') + '</span></button>'
        + '<div class="fm-cat-items fm-food-checks"' + (open ? '' : ' hidden') + '>' + foods.map(choiceHtml).join('') + '</div></div>';
    }).join('');
    var selectedStatus = day && day.order_status ? day.order_status : 'open';
    var statusControl = canClose
      ? '<fieldset class="fm-field fm-status-choice"><legend>وضعیت سفارش‌گیری</legend><label class="fm-radio"><input type="radio" name="order_status" value="open" ' + (selectedStatus === 'open' ? 'checked' : '') + (editorLocked ? ' disabled' : '') + '>باز</label><label class="fm-radio"><input type="radio" name="order_status" value="closed" ' + (selectedStatus === 'closed' ? 'checked' : '') + (editorLocked ? ' disabled' : '') + '>بسته</label></fieldset>'
      : '<div class="fm-alert">وضعیت روز: ' + esc(selectedStatus === 'closed' ? 'بسته' : 'باز') + ' — برای تغییر وضعیت دسترسی food.order_close لازم است.</div>';
    var impactButtonsDisabled = state.daySaving ? ' disabled' : '';
    var pending = state.pendingImpact && state.pendingImpact.date === iso ? '<div class="fm-confirm-box"><strong>' + esc(state.pendingImpactMessage || 'این تغییر سفارش فعال را لغو می‌کند.') + '</strong><span>برای انجام تغییر و ثبت لغوها، تأیید کنید. حذف فیزیکی سفارش انجام نمی‌شود.</span><button class="btn danger" type="button" data-fm-action="confirm-impact"' + impactButtonsDisabled + '>تأیید و ادامه</button><button class="btn secondary" type="button" data-fm-action="cancel-impact"' + impactButtonsDisabled + '>بازگشت</button></div>' : '';
    var message = state.dayError ? '<div class="fm-alert error">' + esc(state.dayError) + '</div>' : '';
    var itemsLabel = state.catalog.length ? '<div class="fm-cat-list">' + choices + '</div>' : '<div class="fm-empty">ابتدا از تب «بانک غذا» حداقل یک غذا ثبت کنید.</div>';
    return '<div class="fm-card"><div class="fm-toolbar"><div><h3>ویرایش برنامهٔ ' + esc(faDigits(title)) + '</h3><span class="fm-small">برای هر روز حداکثر سه گزینهٔ غذا می‌توانید ثبت کنید.</span></div>' + '' + '</div>' + (day && day.holiday_title ? '<div class="fm-alert warn">تعطیل رسمی: ' + esc(day.holiday_title) + ' — در صورت تصمیم سازمان، سفارش باز می‌تواند برقرار باشد.</div>' : '') + (lockedMessage ? '<div class="fm-alert warn">' + esc(lockedMessage) + '</div>' : '') + (inactiveSchedule.length ? '<div class="fm-alert warn">این برنامه شامل غذای غیرفعال است. آن را می‌توان برای حفظ سوابق نگه داشت یا پس از تأیید حذف کرد؛ سفارش جدید برای آن غذا ثبت نمی‌شود.</div>' : '') + message + pending + '<form id="fm-day-form" class="fm-day-editor"><div class="fm-field fm-food-selection"><div class="fm-food-selection-head"><strong>غذاهای این روز</strong><span class="fm-small">برای آینده؛ بدون تغییر سفارش‌های گذشته</span></div>' + '<div class="fm-food-search"><input type="search" data-fm-day-search placeholder="جستجوی غذا..." autocomplete="off" aria-label="جستجوی غذا"></div>' + itemsLabel + '</div>' + (canClose ? statusControl : '') + '<label class="fm-field">یادداشت کوتاه<input name="note" maxlength="255" value="' + esc(day && day.note ? day.note : '') + '" placeholder="اختیاری" ' + (editorLocked ? 'disabled' : '') + '></label><div class="fm-actions"><button class="btn primary" type="submit" ' + (editorLocked ? 'disabled' : '') + '>ذخیره برنامه</button><button class="btn secondary" type="button" data-fm-action="reload-month">بازخوانی</button></div></form>' + '</div>';
  }

  var FOOD_CATEGORY_ORDER = ['کبابی', 'خورشتی', 'ساندویچ', 'پلو', 'خوراک', 'رژیمی', 'سایر'];
  var catalogOpen = {};
  var dayCatalogOpen = {};

  function categoryOptions(selected) {
    return FOOD_CATEGORY_ORDER.map(function (name) {
      return '<option value="' + esc(name) + '"' + (name === (selected || 'سایر') ? ' selected' : '') + '>' + esc(name) + '</option>';
    }).join('');
  }

  function catalogCategoryNames() {
    var names = FOOD_CATEGORY_ORDER.slice();
    (state.catalog || []).forEach(function (food) {
      var name = food.category || 'سایر';
      if (names.indexOf(name) < 0) names.push(name);
    });
    return names;
  }

  function foodCardHtml(food) {
    return '<form class="fm-bank-item" data-fm-catalog-edit>'
      + '<input type="hidden" name="id" value="' + Number(food.id) + '">'
      + '<label class="fm-bank-field">نام غذا<input type="text" name="food_name" maxlength="190" value="' + esc(food.food_name) + '" aria-label="نام غذا"></label>'
      + '<label class="fm-bank-field">دسته‌بندی<select name="category" aria-label="دسته‌بندی">' + categoryOptions(food.category) + '</select></label>'
      + '<label class="fm-bank-field">وضعیت<select name="active" aria-label="وضعیت"><option value="1" ' + (food.active ? 'selected' : '') + '>فعال</option><option value="0" ' + (!food.active ? 'selected' : '') + '>غیرفعال</option></select></label>'
      + '<button class="btn secondary" type="submit">ذخیره</button>'
      + '</form>';
  }

  function renderCatalog() {
    var catalog = state.catalog || [];
    var groups = catalogCategoryNames().map(function (name) {
      var foods = catalog.filter(function (food) { return (food.category || 'سایر') === name; });
      var open = !!catalogOpen[name];
      var body = '';
      if (open) {
        body = foods.length
          ? '<div class="fm-bank-grid fm-cat-grid">' + foods.map(foodCardHtml).join('') + '</div>'
          : '<div class="fm-empty">در این دسته غذایی ثبت نشده است.</div>';
      }
      return '<div class="fm-cat-group' + (open ? ' open' : '') + '">'
        + '<button type="button" class="fm-cat-toggle" data-fm-cat="' + esc(name) + '" aria-expanded="' + (open ? 'true' : 'false') + '">'
        + '<span class="fm-cat-sign" aria-hidden="true">' + (open ? '-' : '+') + '</span>'
        + '<strong>' + esc(name) + '</strong>'
        + '<span class="fm-small">' + faDigits(String(foods.length)) + ' غذا</span></button>'
        + body + '</div>';
    }).join('');
    var list = catalog.length ? '<div class="fm-cat-list">' + groups + '</div>' : '<div class="fm-empty">غذایی ثبت نشده است.</div>' + groups;
    return '<div class="fm-card"><div class="fm-toolbar"><div><h3>بانک غذا</h3><span class="fm-small">هر غذا یک بار و با یک نام ثبت می‌شود؛ نام تکراری پذیرفته نمی‌شود. دسته‌بندی‌ها ریشه‌اند: با «+» غذاهای آن دسته (دو ستونه) باز می‌شود و با «-» جمع می‌شود. غیرفعال‌سازی به‌جای حذف فیزیکی انجام می‌شود. غذای استفاده‌شده در تقویم قابل تغییر نام نیست تا سابقهٔ سفارش حفظ شود.</span></div></div>'
      + '<form id="fm-catalog-add" class="fm-catalog-form"><label class="fm-field">نام غذای جدید<input type="text" name="food_name" maxlength="190" required placeholder="مثلاً چلو مرغ"></label><label class="fm-field">دسته‌بندی<select name="category" aria-label="دسته‌بندی غذای جدید">' + categoryOptions('سایر') + '</select></label><button class="btn primary" type="submit">افزودن غذا</button></form>'
      + list + '</div>';
  }

  var ORDER_COLUMNS = [
    { key: 'row', label: 'ردیف' },
    { key: 'national_code', label: 'کد ملی' },
    { key: 'employee_number', label: 'کد پرسنلی' },
    { key: 'full_name', label: 'نام و نام خانوادگی' },
    { key: 'food_name', label: 'نوع غذا' },
    { key: 'food_date', label: 'تاریخ غذا' },
    { key: 'reserve_date', label: 'تاریخ رزرو' },
    { key: 'reserve_time', label: 'ساعت رزرو' },
    { key: 'status_label', label: 'وضعیت سفارش' },
    { key: 'record_type', label: 'نوع ثبت' },
    { key: 'menu_no', label: 'شماره منو' },
    { key: 'meal_type', label: 'وعده' },
  ];

  var orderFieldsStorageKey = 'itsm-food-order-report-fields-v1:' + String(global.FOOD_TICKET_USER_ID || 'shared');
  function readSavedOrderReportFields() {
    try {
      var saved = global.localStorage && global.localStorage.getItem(orderFieldsStorageKey);
      var parsed = saved ? JSON.parse(saved) : null;
      if (!Array.isArray(parsed)) return null;
      var allowed = ORDER_COLUMNS.map(function (column) { return column.key; });
      var fields = parsed.filter(function (key, index) { return allowed.indexOf(key) >= 0 && parsed.indexOf(key) === index; });
      return fields.length ? fields : null;
    } catch (error) { return null; }
  }
  function saveOrderReportFields(fields) {
    try { if (global.localStorage) global.localStorage.setItem(orderFieldsStorageKey, JSON.stringify(fields)); } catch (error) { /* storage may be disabled */ }
  }
  state.orderReportFields = readSavedOrderReportFields() || state.orderReportFields;

  function selectedOrderColumns() {
    var selected = Array.isArray(state.orderReportFields) ? state.orderReportFields : [];
    return ORDER_COLUMNS.filter(function (column) { return selected.indexOf(column.key) >= 0; });
  }

  function orderFieldValue(row, key, position) {
    if (key === 'row') return faDigits(position + 1);
    if (key === 'national_code') return faDigits(row.national_code || '—');
    if (key === 'employee_number') return faDigits(row.employee_number || '—');
    if (key === 'full_name') return row.full_name || '—';
    if (key === 'food_name') return row.food_name || '—';
    if (key === 'food_date') return faDigits(jalaliFromIso(row.food_date) || '—');
    if (key === 'reserve_date') return faDigits(jalaliFromIso(row.reserve_date) || '—');
    if (key === 'reserve_time') return faDigits((row.reserve_time || '').slice(0, 5) || '—');
    if (key === 'status_label') return row.status === 'active' ? 'فعال' : 'لغوشده';
    if (key === 'record_type') return row.is_proxy ? 'نیابتی' : 'شخصی';
    if (key === 'menu_no') return Number(row.menu_no || 0) > 0 ? 'منوی ' + faDigits(row.menu_no) : 'بدون منو';
    if (key === 'meal_type') return row.meal_type === 'dinner' ? 'شام' : 'ناهار';
    return '—';
  }

  function orderSortValue(row, key, index) {
    if (key === 'row') return index;
    if (key === 'reserve_date' || key === 'reserve_time') return row[key] || '';
    if (key === 'status_label') return row.status === 'active' ? 0 : 1;
    if (key === 'record_type') return row.is_proxy ? 1 : 0;
    if (key === 'full_name') {
      var fullName = String(row.full_name || '').trim();
      var nameParts = fullName.split(/\s+/).filter(Boolean);
      var firstName = String(row.first_name || nameParts.shift() || '').trim();
      var lastName = String(row.last_name || nameParts.join(' ') || firstName).trim();
      return lastName + '\u0000' + firstName + '\u0000' + fullName;
    }
    return row[key] == null ? '' : row[key];
  }

  function sortOrderRows(rows) {
    var key = state.ordersSort.key;
    var dir = state.ordersSort.dir === 'desc' ? -1 : 1;
    var decorated = rows.map(function (row, index) {
      return { row: row, index: index, value: orderSortValue(row, key, index) };
    });
    decorated.sort(function (a, b) {
      var c;
      if (typeof a.value === 'number' && typeof b.value === 'number') c = a.value - b.value;
      else c = String(a.value).localeCompare(String(b.value), 'fa', { numeric: true });
      if (c === 0) c = a.index - b.index;
      return c * dir;
    });
    return decorated.map(function (d) { return d.row; });
  }

  function orderTableHtml(rows) {
    var sorted = sortOrderRows(rows);
    var columns = selectedOrderColumns();
    if (!columns.length) return '<div class="fm-empty">حداقل یک فیلد گزارش را انتخاب کنید.</div>';
    var blocks = columns.length <= 3 ? 3 : (columns.length <= 5 ? 2 : 1);
    var total = sorted.length;
    var head = '';
    for (var b = 0; b < blocks; b++) {
      columns.forEach(function (col, colIndex) {
        var mark = b === 0 && state.ordersSort.key === col.key ? (state.ordersSort.dir === 'desc' ? ' ▼' : ' ▲') : '';
        var blockStart = b > 0 && colIndex === 0 ? ' class="fm-order-block-start"' : '';
        head += '<th' + blockStart + '><button type="button" class="fm-sort-btn" data-fm-sort="' + col.key + '">' + esc(col.label + mark) + '</button></th>';
      });
    }
    var body = [];
    for (var line = 0; line < Math.ceil(total / blocks); line++) {
      var cells = '';
      for (var group = 0; group < blocks; group++) {
        var position = line * blocks + group;
        var row = sorted[position];
        columns.forEach(function (col, colIndex) {
          var blockStart = group > 0 && colIndex === 0 ? ' class="fm-order-block-start"' : '';
          if (!row) { cells += '<td class="fm-order-empty-cell' + (blockStart ? ' fm-order-block-start' : '') + '"></td>'; return; }
          var value = orderFieldValue(row, col.key, position);
          if (col.key === 'status_label') {
            cells += '<td' + blockStart + '><span class="fm-order-state ' + (row.status === 'active' ? '' : 'cancelled') + '">' + esc(value) + '</span></td>';
          } else {
            cells += '<td' + blockStart + '>' + esc(value) + '</td>';
          }
        });
      }
      body.push('<tr>' + cells + '</tr>');
    }
    return '<table class="fm-rows fm-order-report-table' + (blocks > 1 ? ' is-compact agg-blocks-' + blocks : '') + '" data-no-sort><thead><tr>' + head + '</tr></thead><tbody>'
      + (body.join('') || '<tr><td colspan="' + (columns.length * blocks) + '" class="fm-empty">برای این روز سفارشی پیدا نشد.</td></tr>')
      + '</tbody></table>';
  }

  function orderByMenuHtml(rows) {
    var groups = {};
    rows.forEach(function (row) {
      var key = Number(row.menu_no || 0);
      (groups[key] = groups[key] || []).push(row);
    });
    var keys = Object.keys(groups).map(Number).sort(function (a, b) {
      if (a === 0) return 1;
      if (b === 0) return -1;
      return a - b;
    });
    if (!keys.length) return orderTableHtml([]);
    return keys.map(function (key) {
      var list = groups[key];
      var title = key > 0 ? 'منوی ' + faDigits(key) : 'بدون منو';
      var foods = [];
      list.forEach(function (row) { if (row.food_name && foods.indexOf(row.food_name) < 0) foods.push(row.food_name); });
      return '<div class="fm-menu-group"><h4>' + esc(title) + ' — ' + esc(foods.join('، ') || '—')
        + ' <span class="fm-small">(' + faDigits(list.length) + ' سفارش)</span></h4>' + orderTableHtml(list) + '</div>';
    }).join('');
  }

  var DELIVERED_REPORT_COLUMNS = [
    { key: 'row', label: 'ردیف' },
    { key: 'national_code', label: 'کد ملی' },
    { key: 'employee_number', label: 'کد پرسنلی' },
    { key: 'full_name', label: 'نام و نام خانوادگی' },
    { key: 'food_name', label: 'نوع غذا' },
    { key: 'food_date', label: 'تاریخ غذا' },
    { key: 'punch_time', label: 'ساعت تردد' },
    { key: 'record_type', label: 'نوع فیش' },
    { key: 'menu_no', label: 'شماره منو' },
    { key: 'delivered_at', label: 'زمان تحویل' },
  ];
  var deliveredFieldsStorageKey = 'itsm-food-printed-delivery-report-fields-v1';
  function readDeliveredReportFields() {
    try {
      var saved = global.localStorage && global.localStorage.getItem(deliveredFieldsStorageKey);
      var parsed = saved ? JSON.parse(saved) : null;
      if (!Array.isArray(parsed)) return null;
      var allowed = DELIVERED_REPORT_COLUMNS.map(function (column) { return column.key; });
      var fields = parsed.filter(function (key, index) { return allowed.indexOf(key) >= 0 && parsed.indexOf(key) === index; });
      return fields.length ? fields : null;
    } catch (error) { return null; }
  }
  function saveDeliveredReportFields(fields) {
    try { if (global.localStorage) global.localStorage.setItem(deliveredFieldsStorageKey, JSON.stringify(fields)); } catch (error) { /* storage may be disabled */ }
  }
  state.deliveredReportFields = readDeliveredReportFields() || state.deliveredReportFields;

  function deliveredReportFieldValue(row, key, position) {
    if (key === 'row') return faDigits(position + 1);
    if (key === 'national_code') return faDigits(row.national_code || '—');
    if (key === 'employee_number') return faDigits(row.employee_number || '—');
    if (key === 'full_name') return row.full_name || '—';
    if (key === 'food_name') return row.food_name || '—';
    if (key === 'food_date') return faDigits(jalaliFromIso(row.food_date) || '—');
    if (key === 'punch_time') return faDigits((row.punch_time || '').slice(0, 5) || '—');
    if (key === 'record_type') return row.event_type === 'guest' ? 'مهمان' : 'پرسنل';
    if (key === 'menu_no') return Number(row.menu_no || 0) > 0 ? 'منوی ' + faDigits(row.menu_no) : (row.event_type === 'guest' ? 'مهمان' : 'بدون منو');
    if (key === 'delivered_at') return faDigits(String(row.delivered_at || '—').replace(/-/g, '/').slice(0, 16));
    return '—';
  }
  function selectedDeliveredReportColumns() {
    var selected = Array.isArray(state.deliveredReportFields) ? state.deliveredReportFields : [];
    return DELIVERED_REPORT_COLUMNS.filter(function (column) { return selected.indexOf(column.key) >= 0; });
  }
  function sortDeliveredReportRows(rows) {
    var key = state.deliveredReportSort.key;
    var direction = state.deliveredReportSort.dir === 'desc' ? -1 : 1;
    return rows.map(function (row, index) {
      var value = key === 'row' ? index : key === 'menu_no' ? Number(row.menu_no || 0) : (row[key] || '');
      if (key === 'record_type') value = row.event_type === 'guest' ? 1 : 0;
      return { row: row, index: index, value: value };
    }).sort(function (a, b) {
      var cmp = typeof a.value === 'number' && typeof b.value === 'number'
        ? a.value - b.value : String(a.value).localeCompare(String(b.value), 'fa', { numeric: true });
      return (cmp || a.index - b.index) * direction;
    }).map(function (item) { return item.row; });
  }
  function deliveredReportTableHtml(rows) {
    var sorted = sortDeliveredReportRows(rows);
    var columns = selectedDeliveredReportColumns();
    if (!columns.length) return '<div class="fm-empty">حداقل یک فیلد گزارش را انتخاب کنید.</div>';
    var blocks = columns.length <= 3 ? 3 : (columns.length <= 5 ? 2 : 1);
    var head = '';
    for (var block = 0; block < blocks; block++) {
      columns.forEach(function (column, colIndex) {
        var mark = block === 0 && state.deliveredReportSort.key === column.key ? (state.deliveredReportSort.dir === 'desc' ? ' ▼' : ' ▲') : '';
        var divider = block > 0 && colIndex === 0 ? ' class="fm-order-block-start"' : '';
        head += '<th' + divider + '><button type="button" class="fm-sort-btn" data-fm-delivery-sort="' + column.key + '">' + esc(column.label + mark) + '</button></th>';
      });
    }
    var body = [];
    for (var line = 0; line < Math.ceil(sorted.length / blocks); line++) {
      var cells = '';
      for (var group = 0; group < blocks; group++) {
        var position = line * blocks + group;
        var row = sorted[position];
        columns.forEach(function (column, colIndex) {
          var divider = group > 0 && colIndex === 0 ? ' class="fm-order-block-start"' : '';
          if (!row) { cells += '<td class="fm-order-empty-cell' + (divider ? ' fm-order-block-start' : '') + '"></td>'; return; }
          cells += '<td' + divider + '>' + esc(deliveredReportFieldValue(row, column.key, position)) + '</td>';
        });
      }
      body.push('<tr>' + cells + '</tr>');
    }
    return '<table class="fm-rows fm-order-report-table' + (blocks > 1 ? ' is-compact agg-blocks-' + blocks : '') + '" data-no-sort><thead><tr>' + head + '</tr></thead><tbody>'
      + (body.join('') || '<tr><td colspan="' + (columns.length * blocks) + '" class="fm-empty">در این بازه فیش چاپ‌شده و تحویل‌شده‌ای ثبت نشده است.</td></tr>')
      + '</tbody></table>';
  }
  function deliveredReportByMenuHtml(rows) {
    var groups = {};
    rows.forEach(function (row) {
      var key = Number(row.menu_no || 0);
      (groups[key] = groups[key] || []).push(row);
    });
    var keys = Object.keys(groups).map(Number).sort(function (a, b) {
      if (a === 0) return 1;
      if (b === 0) return -1;
      return a - b;
    });
    if (!keys.length) return deliveredReportTableHtml([]);
    return keys.map(function (key) {
      var list = groups[key];
      var title = key > 0 ? 'منوی ' + faDigits(key) : (list.every(function (row) { return row.event_type === 'guest'; }) ? 'فیش مهمان' : 'بدون منو');
      var foods = [];
      list.forEach(function (row) { if (row.food_name && foods.indexOf(row.food_name) < 0) foods.push(row.food_name); });
      return '<div class="fm-menu-group"><h4>' + esc(title) + ' — ' + esc(foods.join('، ') || '—')
        + ' <span class="fm-small">(' + faDigits(list.length) + ' فیش)</span></h4>' + deliveredReportTableHtml(list) + '</div>';
    }).join('');
  }
  function deliveredReportOrderedRows() {
    var rows = state.deliveredReport || [];
    if (state.deliveredReportView !== 'menu') return sortDeliveredReportRows(rows);
    var groups = {};
    rows.forEach(function (row) {
      var key = Number(row.menu_no || 0);
      (groups[key] = groups[key] || []).push(row);
    });
    return Object.keys(groups).map(Number).sort(function (a, b) {
      if (a === 0) return 1;
      if (b === 0) return -1;
      return a - b;
    }).reduce(function (all, key) { return all.concat(sortDeliveredReportRows(groups[key])); }, []);
  }
  /** تاریخ تکروز گزارش غذای تحویلی — مستقل از بازهٔ آمار سفارشها. */
  function deliveryDateForReport() {
    return state.deliveredReportDate || state.statisticsFrom || state.selectedDate || (state.status && state.status.today) || localTodayIso();
  }

  function renderPrintedDeliveryReport() {
    var fields = DELIVERED_REPORT_COLUMNS.map(function (column) {
      var checked = state.deliveredReportFields.indexOf(column.key) >= 0 ? ' checked' : '';
      return '<label class="fm-report-field"><input type="checkbox" data-fm-delivery-field="' + column.key + '"' + checked + '><span>' + esc(column.label) + '</span></label>';
    }).join('');
    var viewBar = '<div class="fm-view-switch"><button class="btn ' + (state.deliveredReportView === 'list' ? 'primary' : 'secondary') + '" type="button" data-fm-action="delivered-report-view" data-delivery-view="list">فهرست یکجا</button><button class="btn ' + (state.deliveredReportView === 'menu' ? 'primary' : 'secondary') + '" type="button" data-fm-action="delivered-report-view" data-delivery-view="menu">نمایش بر اساس منو</button></div>';
    var singleDate = state.deliveredReportDate || state.statisticsFrom || state.selectedDate || (state.status && state.status.today) || localTodayIso();
    var singleDateLabel = jalaliFromIso(singleDate) || '';
    var dateForm = '<form id="fm-delivery-date" class="fm-order-date"><label class="fm-field">تاریخ گزارش<input type="text" name="date" data-jalali inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(faDigits(singleDateLabel)) + '" required></label><button class="btn primary" type="submit">نمایش گزارش</button><button class="btn secondary" type="button" data-fm-action="delivery-today">امروز</button></form>';
    var results = state.deliveredReportLoading
      ? '<div class="fm-spinner">در حال دریافت فهرست فیش‌های تحویل‌شده…</div>'
      : '<div class="fm-delivery-counts"><div><span>پرسنل</span><strong>' + faDigits(state.deliveredReportEmployeeCount) + '</strong></div><div><span>فیش مهمان چاپ‌شده</span><strong>' + faDigits(state.deliveredReportGuestCount) + '</strong></div></div>'
        + viewBar + '<div class="fm-table-wrap fm-delivery-report-results">' + (state.deliveredReportView === 'menu' ? deliveredReportByMenuHtml(state.deliveredReport) : deliveredReportTableHtml(state.deliveredReport)) + '</div>';
    var actionsEnabled = state.deliveredReportLoaded && !state.deliveredReportLoading;
    return '<section class="fm-card fm-daily-orders fm-printed-delivery-report"><div class="fm-toolbar"><div><h3>فیش‌های چاپ‌شده و غذای تحویل‌شده</h3><span class="fm-small">تاریخ: ' + esc(faDigits(singleDateLabel || '—')) + ' — پرسنل فقط با فیش چاپ‌شده و وضعیت تحویل‌شده؛ مهمان با فیش چاپ‌شده. وضعیت تحویل در سامانه از ثبت چاپ می‌آید و تأیید مستقل دریافت غذا نیست.</span></div><div class="fm-actions"><button class="btn secondary" type="button" data-fm-action="delivered-report-export"' + (actionsEnabled ? '' : ' disabled') + '>استخراج Excel</button><button class="btn secondary" type="button" data-fm-action="delivered-report-print"' + (actionsEnabled ? '' : ' disabled') + '>چاپ</button></div></div>'
      + dateForm
      + '<div class="fm-report-fields"><strong>فیلدهای گزارش</strong>' + fields + '</div>'
      + (state.deliveredReportLoaded || state.deliveredReportLoading ? results : '<div class="fm-empty">تاریخ موردنظر را انتخاب کنید و «نمایش گزارش» بزنید.</div>')
      + '</section>';
  }
  async function loadPrintedDeliveryReport(fromDate, toDate) {
    var from = normalizeIsoDate(fromDate || '');
    var to = normalizeIsoDate(toDate || '');
    if (!from || !to || from > to) return;
    var key = from + '|' + to;
    if (state.deliveredReportRange === key && (state.deliveredReportLoaded || state.deliveredReportLoading)) return;
    state.deliveredReportRange = key;
    state.deliveredReportLoaded = false;
    state.deliveredReportLoading = true;
    state.deliveredReport = [];
    var requestId = ++state.deliveredReportRequestId;
    render();
    try {
      var response = await request('food-menu/printed-delivery', { from: from, to: to });
      if (requestId !== state.deliveredReportRequestId) return;
      state.deliveredReport = Array.isArray(response.items) ? response.items : [];
      state.deliveredReportEmployeeCount = Number(response.employee_count || 0);
      state.deliveredReportGuestCount = Number(response.guest_count || 0);
      state.deliveredReportLoaded = true;
      state.error = '';
    } catch (error) {
      if (requestId !== state.deliveredReportRequestId) return;
      state.deliveredReport = [];
      state.deliveredReportRange = '';
      state.error = error.message || 'گزارش فیش‌های تحویل‌شده بارگذاری نشد.';
    } finally {
      if (requestId === state.deliveredReportRequestId) {
        state.deliveredReportLoading = false;
        render();
      }
    }
  }
  function exportPrintedDeliveryReport() {
    if (!state.deliveredReportLoaded || state.deliveredReportLoading) return;
    var columns = selectedDeliveredReportColumns();
    if (!columns.length) return;
    var rows = deliveredReportOrderedRows().map(function (row, index) {
      return columns.map(function (column) { return deliveredReportFieldValue(row, column.key, index); });
    });
    var exportApi = global.ITSM_LIST_SORT;
    if (exportApi && typeof exportApi.exportXlsx === 'function') {
      exportApi.exportXlsx('فیش‌های چاپ‌شده و تحویل‌شده ' + faDigits(jalaliFromIso(deliveryDateForReport()) || ''), columns.map(function (column) { return column.label; }), rows, 'food-delivered-' + deliveryDateForReport(), null);
      return;
    }
    global.location.href = buildUrl('food-menu/printed-delivery-export', {
      from: deliveryDateForReport(), to: deliveryDateForReport(),
      fields: columns.map(function (column) { return column.key; }).join(','), view: state.deliveredReportView,
    });
  }
  function printPrintedDeliveryReport() {
    if (!state.deliveredReportLoaded || state.deliveredReportLoading) return;
    printScopedReport('.fm-printed-delivery-report .fm-delivery-report-results', 'fm-print-delivered-report');
  }

  function renderOrders() {
    var fallbackDate = state.ordersDate || state.selectedDate || (state.status && state.status.today) || localTodayIso();
    var inputDate = jalaliFromIso(fallbackDate) || '';
    var dateLabel = faDigits(inputDate || '—');
    var actionsEnabled = state.ordersLoaded && !state.loadingOrders;
    var rows = state.orders || [];
    var viewButton = function (view, label) {
      return '<button class="btn ' + (state.ordersView === view ? 'primary' : 'secondary') + '" type="button" data-fm-action="orders-view" data-orders-view="' + view + '">' + label + '</button>';
    };
    var viewBar = '<div class="fm-view-switch">' + viewButton('list', 'فهرست یکجا') + viewButton('menu', 'نمایش بر اساس منو') + '</div>';
    var body = state.ordersView === 'menu' ? orderByMenuHtml(rows) : orderTableHtml(rows);
    var fieldChecks = ORDER_COLUMNS.map(function (col) {
      var checked = state.orderReportFields.indexOf(col.key) >= 0 ? ' checked' : '';
      return '<label class="fm-report-field"><input type="checkbox" data-fm-order-field="' + col.key + '"' + checked + '><span>' + esc(col.label) + '</span></label>';
    }).join('');
    var daily = '<section class="fm-card fm-daily-orders"><div class="fm-toolbar"><div><h3>سفارش‌های ' + (state.mealType === 'dinner' ? 'شام' : 'ناهار') + ' — روز انتخاب‌شده</h3><span class="fm-small">تاریخ: ' + esc(dateLabel) + ' — فیلدهای انتخاب‌شده در خروجی و چاپ نمایش داده می‌شوند.</span></div></div><form id="fm-orders-date" class="fm-order-date"><label class="fm-field">تاریخ روز شمسی<input type="text" name="date" data-jalali inputmode="numeric" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(faDigits(inputDate)) + '" required></label><button class="btn primary" type="submit">مشاهده</button><button class="btn secondary" type="button" data-fm-action="orders-today">امروز</button><button class="btn secondary" type="button" data-fm-action="orders-export"' + (actionsEnabled ? '' : ' disabled') + '>استخراج Excel</button>' + (state.mealType === 'lunch' ? '<button class="btn secondary" type="button" data-fm-action="orders-print"' + (actionsEnabled ? '' : ' disabled') + '>چاپ</button>' : '') + '</form>'
      + '<div class="fm-report-fields"><strong>فیلدهای گزارش</strong>' + fieldChecks + '</div>'
      + (state.loadingOrders ? '<div class="fm-spinner">در حال بارگذاری سفارش‌ها…</div>' : viewBar + '<div class="fm-table-wrap fm-manager-data-warning">' + body + '</div>')
      + '</section>';
    return daily + renderGroupOrders();
  }

  function renderGroupOrders() {
    var selectedDate = state.groupOrdersDate || state.ordersDate || state.selectedDate || (state.status && state.status.today) || localTodayIso();
    var dateLabel = jalaliFromIso(selectedDate) || '';
    var groupOptions = '<option value="all"' + (state.groupOrdersGroupId === 'all' ? ' selected' : '') + '>همهٔ گروه‌ها</option>'
      + '<option value=""' + (!state.groupOrdersGroupId ? ' selected' : '') + '>انتخاب گروه غذا</option>'
      + (state.foodGroups || []).map(function (group) {
        return '<option value="' + Number(group.id) + '"' + (String(group.id) === String(state.groupOrdersGroupId) ? ' selected' : '') + '>' + esc(group.title) + '</option>';
      }).join('');
    var title = state.groupOrdersTitle || ((state.foodGroups || []).filter(function (group) { return String(group.id) === String(state.groupOrdersGroupId); })[0] || {}).title || '';
    var tableFor = function (rows) { return state.groupOrdersView === 'menu' ? orderByMenuHtml(rows || []) : orderTableHtml(rows || []); };
    var report;
    if (state.loadingGroupOrders) {
      report = '<div class="fm-spinner">در حال دریافت سفارش‌های گروه…</div>';
    } else if (!state.groupOrdersLoaded) {
      report = '<div class="fm-empty">گروه و روز را انتخاب کنید و «مشاهده» را بزنید.</div>';
    } else if (state.groupOrdersGroupId === 'all') {
      var allGroups = state.groupOrdersGroups || [];
      report = '<div class="fm-group-report-title"><strong>همهٔ گروه‌ها</strong><span>' + esc(faDigits(dateLabel)) + ' — ' + (state.mealType === 'dinner' ? 'شام' : 'ناهار') + '</span></div>' + (allGroups.length ? allGroups.map(function (group) {
        var groupRows = group.items || [];
        return '<section class="fm-group-result"><h4>' + esc(group.title || 'گروه غذا') + ' <span class="fm-small">(' + faDigits(groupRows.length) + ' سفارش)</span></h4>'
          + (groupRows.length ? '<div class="fm-table-wrap fm-manager-data-warning">' + tableFor(groupRows) + '</div>' : '<div class="fm-small fm-group-empty">برای این گروه سفارشی ثبت نشده است.</div>')
          + '</section>';
      }).join('') : '<div class="fm-empty">گروه فعالی برای نمایش وجود ندارد.</div>');
    } else {
      report = '<div class="fm-group-report-title"><strong>' + esc(title) + '</strong><span>' + esc(faDigits(dateLabel)) + ' — ' + (state.mealType === 'dinner' ? 'شام' : 'ناهار') + '</span></div>'
        + '<div class="fm-table-wrap fm-manager-data-warning">' + tableFor(state.groupOrders || []) + '</div>';
    }
    var viewBar = state.groupOrdersLoaded
      ? '<div class="fm-view-switch" aria-label="نحوهٔ نمایش سفارش‌های گروه">'
        + '<button class="btn ' + (state.groupOrdersView === 'list' ? 'primary' : 'secondary') + '" type="button" data-fm-action="group-orders-view" data-orders-view="list">فهرست یکجا</button>'
        + '<button class="btn ' + (state.groupOrdersView === 'menu' ? 'primary' : 'secondary') + '" type="button" data-fm-action="group-orders-view" data-orders-view="menu">نمایش بر اساس منو</button></div>' : '';
    return '<section class="fm-card fm-group-orders"><div class="fm-toolbar"><div><h3>لیست سفارش گروه‌های غذا</h3><span class="fm-small">سفارش اعضای گروه در تاریخ انتخاب‌شده، با جابه‌جایی روزانهٔ اعضا لحاظ‌شده.</span></div>' + (state.groupOrdersLoaded ? '<button class="btn secondary" type="button" data-fm-action="group-orders-print">چاپ</button>' : '') + '</div><form id="fm-group-orders-form" class="fm-order-date"><label class="fm-field">گروه غذا<select name="group_id" required' + (!state.foodGroupsLoaded || !state.foodGroups.length ? ' disabled' : '') + '>' + groupOptions + '</select></label><label class="fm-field">تاریخ روز شمسی<input type="text" name="date" data-jalali inputmode="numeric" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(faDigits(dateLabel)) + '" required></label><button class="btn primary" type="submit"' + (!state.foodGroupsLoaded || !state.foodGroups.length ? ' disabled' : '') + '>مشاهده</button></form>' + (!state.foodGroupsLoaded ? '<div class="fm-small">در حال بارگذاری فهرست گروه‌ها…</div>' : (!state.foodGroups.length ? '<div class="fm-empty">گروه فعالی برای انتخاب وجود ندارد.</div>' : '')) + viewBar + '<div class="fm-group-report-content">' + report + '</div></section>';
  }

  function renderCancelRequests() {
    var rows = state.cancelRequests || [];
    var body;
    if (state.loadingCancelRequests) {
      body = '<div class="fm-spinner">در حال بارگذاری درخواست‌های لغو…</div>';
    } else if (!rows.length) {
      body = '<div class="fm-empty">درخواست لغو بازی برای روزهای بسته‌شده نیست.</div>';
    } else {
      body = '<div class="fm-table-wrap"><table class="fm-rows"><thead><tr><th>تاریخ غذا</th><th>وعده</th><th>نام پرسنل</th><th>غذا</th><th>درخواست‌دهنده</th><th>زمان ثبت درخواست</th><th>عملیات</th></tr></thead><tbody>'
        + rows.map(function (row) {
          return '<tr><td>' + esc(faDigits(row.food_date_jalali || jalaliFromIso(row.food_date) || row.food_date || '')) + '</td><td>' + (row.meal_type === 'dinner' ? 'شام' : 'ناهار') + '</td><td>' + esc(row.employee_name || '—') + '</td><td>' + esc(row.food_name || '—') + '</td><td>' + esc(row.requester_name || '—') + '</td><td>' + esc(faDigits(row.requested_at_label || row.requested_at || row.created_at || '—')) + '</td><td>'
            + '<button class="btn danger" type="button" data-fm-action="cancel-req-approve" data-id="' + Number(row.id) + '">لغو سفارش</button> '
            + '<button class="btn secondary" type="button" data-fm-action="cancel-req-reject" data-id="' + Number(row.id) + '">رد درخواست</button></td></tr>';
        }).join('') + '</tbody></table></div>';
    }
    return '<section class="fm-card"><div class="fm-toolbar"><div><h3>درخواست لغو روزهای بسته‌شده</h3><span class="fm-small">کاربر وقتی روز سفارش بسته است درخواست می‌فرستد. همین فهرست در داشبورد مدیر پشتیبانی و مدیر اصلی هم هست؛ اعلان در زنگولهٔ سامانه ثبت می‌شود.</span></div><button class="btn secondary" type="button" data-fm-action="cancel-req-reload">تازه‌سازی</button></div>' + body + '</section>';
  }

  function renderStatistics() {
    var fallbackDate = state.selectedDate || (state.status && state.status.today) || localTodayIso();
    var from = jalaliFromIso(state.statisticsFrom || fallbackDate) || '';
    var to = jalaliFromIso(state.statisticsTo || fallbackDate) || '';
    var mode = ['employee', 'selected'].includes(state.statisticsMode) ? state.statisticsMode : 'food';
    var modeTitle = mode === 'selected' ? 'سفارش پرسنل انتخاب‌شده' : (mode === 'employee' ? 'آمار سفارش نفرات' : 'آمار نوع غذا');
    var rangeText = from === to ? faDigits(from) : faDigits(from) + ' تا ' + faDigits(to);
    var actionsEnabled = state.statisticsLoaded && !state.loadingStatistics;
    var result = '';
    if (state.loadingStatistics) {
      result = '<div class="fm-spinner">در حال دریافت گزارش سفارش‌ها…</div>';
    } else if (!state.statisticsLoaded) {
      result = '<div class="fm-empty">برای مشاهده، بازهٔ موردنظر را انتخاب کنید.</div>';
    } else if (mode === 'selected') {
      var selectedRows = state.statistics || [];
      result = selectedRows.length
        ? '<div class="fm-statistics-results fm-selected-orders"><div class="fm-table-wrap"><table class="fm-rows"><thead><tr><th>ردیف</th><th>نام پرسنل</th><th>شمارهٔ پرسنلی</th><th>تاریخ غذا</th><th>وعده</th><th>غذا</th><th>تاریخ ثبت</th><th>ساعت ثبت</th></tr></thead><tbody>' + selectedRows.map(function (order, index) {
          return '<tr><td>' + faDigits(index + 1) + '</td><td>' + esc(order.full_name || '—') + '</td><td>' + esc(faDigits(order.employee_number || '—')) + '</td><td>' + esc(faDigits(jalaliFromIso(order.food_date) || order.food_date || '—')) + '</td><td>' + (order.meal_type === 'dinner' ? 'شام' : 'ناهار') + '</td><td>' + esc(order.food_name || '—') + '</td><td>' + esc(faDigits(jalaliFromIso(order.reserve_date) || order.reserve_date || '—')) + '</td><td>' + esc(faDigits(String(order.reserve_time || '').slice(0, 5))) + '</td></tr>';
        }).join('') + '</tbody></table></div><div class="fm-small">' + faDigits(selectedRows.length) + ' سفارش فعال در بازه</div></div>'
        : '<div class="fm-statistics-results fm-empty">برای پرسنل انتخاب‌شده در این بازه سفارش فعال ثبت نشده است.</div>';
    } else if (mode === 'employee') {
      var people = state.statistics || [];
      var peopleColumns = [];
      var peopleColumnCount = people.length ? Math.min(3, Math.max(1, Math.floor(people.length / 3))) : 0;
      var peoplePerColumn = peopleColumnCount ? Math.floor(people.length / peopleColumnCount) : 0;
      var extraPeopleRows = peopleColumnCount ? people.length % peopleColumnCount : 0;
      var peopleOffset = 0;
      for (var group = 0; group < peopleColumnCount; group++) {
        var start = peopleOffset;
        var columnSize = peoplePerColumn + (group < extraPeopleRows ? 1 : 0);
        var columnRows = people.slice(start, start + columnSize).map(function (person, offset) {
          return '<tr><td>' + faDigits(start + offset + 1) + '</td><td>' + esc(person.full_name || '—') + '</td><td>' + faDigits(Number(person.count || 0)) + '</td></tr>';
        }).join('');
        peopleOffset += columnSize;
        peopleColumns.push('<section class="fm-statistics-people-column"><table class="fm-rows"><thead><tr><th>ردیف</th><th>نام و نام خانوادگی</th><th>تعداد</th></tr></thead><tbody>' + columnRows + '</tbody></table></section>');
      }
      result = people.length
        ? '<div class="fm-statistics-results fm-statistics-results--people"><div class="fm-statistics-people-columns">' + peopleColumns.join('') + '</div></div>'
        : '<div class="fm-statistics-results fm-statistics-results--people fm-empty">در بازهٔ انتخاب‌شده سفارش فعالی ثبت نشده است.</div>';
    } else {
      var days = state.statistics || [];
      var singleDayRange = !!state.statisticsFrom && state.statisticsFrom === state.statisticsTo;
      var dayLayoutClass = singleDayRange ? 'fm-statistics-days--single' : 'fm-statistics-days--multi';
      result = days.length ? '<div class="fm-statistics-results fm-statistics-results--food fm-statistics-days ' + dayLayoutClass + '">' + days.map(function (day) {
        var foods = (day.foods || []).map(function (food) {
          return '<li><span>' + esc(food.food_name || '—') + '</span><strong>' + faDigits(Number(food.count || 0)) + ' عدد</strong></li>';
        }).join('');
        return '<section class="fm-stat-day"><h4>' + esc(day.weekday || '') + ' ' + esc(faDigits(day.food_date_jalali || jalaliFromIso(day.food_date) || '')) + '</h4>'
          + (foods ? '<ul>' + foods + '</ul>' : '<div class="fm-empty">برای این روز سفارشی ثبت نشده است.</div>') + '</section>';
      }).join('') + '</div>' : '<div class="fm-statistics-results fm-statistics-results--food fm-empty">در بازهٔ انتخاب‌شده سفارشی ثبت نشده است.</div>';
    }
    var selected = state.reportSelectedEmployees || [];
    var selectedIds = selected.map(function (person) { return Number(person.id); });
    var picker = '';
    if (mode === 'selected') {
      var matches = state.reportEmployeeResults || [];
      var matchList = matches.length ? '<div class="fm-selected-employee-matches" role="group" aria-label="فهرست پرسنل">' + matches.map(function (person) {
        var id = Number(person.id);
        var checked = selectedIds.indexOf(id) >= 0;
        return '<label class="fm-selected-employee-option"><input type="checkbox" data-stat-employee-checkbox data-employee-id="' + id + '"' + (checked ? ' checked' : '') + '><span>' + esc(person.name || '') + '</span><small>' + esc(faDigits(person.employee_number || '')) + '</small></label>';
      }).join('') + '</div>' : (state.reportEmployeeSearchLoading ? '<div class="fm-small">در حال بارگذاری فهرست پرسنل…</div>' : (state.reportEmployeeQuery.length === 1 ? '<small class="fm-small">برای جست‌وجو دست‌کم دو نویسه وارد کنید.</small>' : (state.reportEmployeeResultsLoaded ? '<small class="fm-small">پرسنلی با این جست‌وجو پیدا نشد.</small>' : '<small class="fm-small">در حال آماده‌سازی فهرست پرسنل…</small>')));
      var tags = selected.map(function (person) {
        return '<span class="fm-selected-employee-tag">' + esc(person.name || '') + (person.employee_number ? ' · ' + esc(faDigits(person.employee_number)) : '') + '<button type="button" aria-label="حذف ' + esc(person.name || '') + '" data-fm-action="statistics-remove-employee" data-employee-id="' + Number(person.id) + '">×</button></span>';
      }).join('');
      picker = '<div class="fm-selected-employee-picker"><label class="fm-field">جست‌وجوی زندهٔ نام یا شمارهٔ پرسنلی<input type="search" data-stat-employee-search autocomplete="off" placeholder="نام یا شمارهٔ پرسنلی را وارد کنید" value="' + esc(state.reportEmployeeQuery || '') + '"></label>' + matchList
        + '<div class="fm-selected-employee-tags">' + (tags || '<span class="fm-small">هنوز پرسنلی انتخاب نشده است.</span>') + '</div><small class="fm-small">برای انتخاب یا برداشتن هر نفر، تیک کنار نام را بزنید؛ فهرست اولیه تا ۵۰۰ نفر است؛ از جست‌وجو برای یافتن سریع استفاده کنید. حداکثر ۱۰۰ نفر قابل انتخاب است.</small></div>';
    }
    return '<section class="fm-card fm-statistics"><div class="fm-toolbar"><div><h3>' + modeTitle + '</h3><span class="fm-small">بازه: ' + esc(rangeText) + '</span></div></div><form id="fm-statistics-date" class="fm-statistics-form"><div class="fm-stats-modes" role="radiogroup" aria-label="نوع گزارش"><label class="fm-stats-mode"><input type="radio" name="statistics_mode" value="food"' + (mode === 'food' ? ' checked' : '') + ' required><span>آمار نوع غذا</span></label><label class="fm-stats-mode"><input type="radio" name="statistics_mode" value="employee"' + (mode === 'employee' ? ' checked' : '') + ' required><span>آمار سفارش نفرات</span></label><label class="fm-stats-mode"><input type="radio" name="statistics_mode" value="selected"' + (mode === 'selected' ? ' checked' : '') + ' required><span>جزئیات سفارش پرسنل انتخابی</span></label></div><div class="fm-order-date"><label class="fm-field">از تاریخ شمسی<input type="text" name="from" data-jalali inputmode="numeric" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(faDigits(from)) + '" required></label><label class="fm-field">تا تاریخ شمسی<input type="text" name="to" data-jalali inputmode="numeric" placeholder="۱۴۰۵/۰۷/۳۰" value="' + esc(faDigits(to)) + '" required></label><button class="btn primary" type="submit">مشاهده</button><button class="btn secondary" type="button" data-fm-action="statistics-export"' + (actionsEnabled ? '' : ' disabled') + '>خروجی اکسل</button><button class="btn secondary" type="button" data-fm-action="statistics-print"' + (actionsEnabled ? '' : ' disabled') + '>پرینت</button></div>' + picker + '</form>' + result + '</section>';
  }

  function bulkOperationTitle(operation) {
    return operation === 'order' ? 'ثبت یا تغییر سفارش' : operation === 'cancel' ? 'لغو سفارش' : 'تغییر سلف';
  }

  function bulkSelectedPeople() {
    var selected = new Set((state.bulk.selectedIds || []).map(Number));
    return (state.bulk.people || []).filter(function (person) { return selected.has(Number(person.id)); });
  }

  function bulkTargetValue() {
    return String(state.bulk.target || '');
  }

  function bulkCanSelect(person) {
    var bulk = state.bulk;
    if (!bulk.snapshot) return false;
    var pastWindowAllowed = Boolean(bulk.snapshot.is_past && bulk.snapshot.late_order_window_open && ['order', 'cancel'].includes(bulk.operation));
    if (bulk.snapshot.is_past && !pastWindowAllowed) return false;
    if (bulk.operation === 'order') return Boolean(bulk.snapshot.has_calendar && (bulk.items || []).length && Number(bulk.target || 0) > 0);
    if (bulk.operation === 'cancel') return Boolean(person.order && person.order.status === 'active');
    var group = (bulk.groups || []).filter(function (item) { return Number(item.id) === Number(bulk.target || 0); })[0];
    if (!group || group.locked || (person.visit && person.visit.locked)) return false;
    var targetId = Number(group.id);
    var homeId = Number((person.home_group && person.home_group.group_id) || 0);
    var visitId = Number((person.visit && person.visit.group_id) || 0);
    if (person.visit && visitId === targetId) return false;
    if (homeId && targetId === homeId) return Boolean(person.visit && visitId !== homeId);
    if (homeId && person.home_group.locked) return false;
    return true;
  }

  function loadBulkSnapshot(date, mealType) {
    if (!canEmergency || state.bulk.operation === 'guest') return;
    var bulk = state.bulk;
    var iso = normalizeIsoDate(date || bulk.date || state.selectedDate || (state.status && state.status.today) || localTodayIso());
    if (!iso) { state.error = 'تاریخ را از تقویم شمسی به‌درستی انتخاب کنید.'; render(); return; }
    bulk.date = iso;
    bulk.mealType = mealType === 'dinner' ? 'dinner' : 'lunch';
    bulk.loading = true;
    bulk.selectedIds = [];
    state.error = ''; state.notice = '';
    render();
    request('food-menu/bulk-snapshot', { date: iso, meal_type: bulk.mealType })
      .then(function (snapshot) {
        bulk.snapshot = snapshot;
        bulk.people = Array.isArray(snapshot.people) ? snapshot.people : [];
        bulk.groups = Array.isArray(snapshot.groups) ? snapshot.groups : [];
        bulk.items = Array.isArray(snapshot.items) ? snapshot.items : [];
        var targetExists = bulk.operation === 'order'
          ? bulk.items.some(function (item) { return String(item.item_id) === String(bulk.target); })
          : bulk.groups.some(function (group) { return String(group.id) === String(bulk.target); });
        if (!bulk.target || !targetExists) {
          bulk.target = bulk.operation === 'order' ? String((bulk.items[0] || {}).item_id || '') : String((bulk.groups[0] || {}).id || '');
        }
      })
      .catch(function (error) { bulk.snapshot = null; bulk.people = []; bulk.groups = []; bulk.items = []; state.error = error.message || 'فهرست عملیات گروهی بارگذاری نشد.'; })
      .then(function () { bulk.loading = false; render(); });
  }

  function bulkTargetOptions() {
    var bulk = state.bulk;
    if (bulk.operation === 'order') {
      return (bulk.items || []).map(function (item) {
        return '<option value="' + Number(item.item_id) + '"' + (String(bulk.target) === String(item.item_id) ? ' selected' : '') + '>' + esc(item.food_name) + '</option>';
      }).join('') || '<option value="">برای این تاریخ غذایی ثبت نشده</option>';
    }
    if (bulk.operation === 'canteen') {
      return (bulk.groups || []).map(function (group) {
        return '<option value="' + Number(group.id) + '"' + (String(bulk.target) === String(group.id) ? ' selected' : '') + '>' + esc(group.title) + (group.locked ? ' — فیش چاپ شده/قفل' : '') + '</option>';
      }).join('') || '<option value="">سلف فعالی پیدا نشد</option>';
    }
    return '';
  }

  function bulkPersonStatus(person) {
    var bulk = state.bulk;
    var pastWindowAllowed = Boolean(bulk.snapshot && bulk.snapshot.is_past && bulk.snapshot.late_order_window_open && ['order', 'cancel'].includes(bulk.operation));
    if (bulk.snapshot && bulk.snapshot.is_past && !pastWindowAllowed) return '<span class="fm-bulk-state blocked">تاریخ گذشته؛ مهلت اضطراری فعال نیست</span>';
    if (bulk.operation === 'order') {
      if (!bulk.snapshot || !bulk.snapshot.has_calendar) return '<span class="fm-bulk-state blocked">برنامهٔ این روز ثبت نشده</span>';
      if (!bulk.items.length) return '<span class="fm-bulk-state blocked">غذای فعالی ندارد</span>';
      if (bulk.snapshot.is_past && pastWindowAllowed) return '<span class="fm-bulk-state override">مهلت اضطراری فعال · ثبت مجاز</span>';
      if (bulk.snapshot.order_status === 'closed') return '<span class="fm-bulk-state override">سفارش بسته · اقدام مدیر مجاز</span>';
      return '<span class="fm-bulk-state ok">' + (person.order && person.order.status === 'active' ? 'قابل تغییر' : person.order ? 'قابل ثبت مجدد' : 'آمادهٔ ثبت') + '</span>';
    }
    if (bulk.operation === 'cancel') {
      if (!person.order || person.order.status !== 'active') return '<span class="fm-bulk-state blocked">سفارش فعالی ندارد</span>';
      return '<span class="fm-bulk-state ' + (pastWindowAllowed ? 'override' : 'ok') + '">' + (pastWindowAllowed ? 'مهلت اضطراری فعال · لغو مجاز' : (bulk.snapshot && bulk.snapshot.order_status === 'closed' ? 'بسته · لغو مدیر مجاز' : 'قابل لغو')) + '</span>';
    }
    if (person.visit && person.visit.locked) return '<span class="fm-bulk-state blocked">فیش سلف فعلی چاپ شده</span>';
    var group = (bulk.groups || []).filter(function (item) { return Number(item.id) === Number(bulk.target || 0); })[0];
    if (!group) return '<span class="fm-bulk-state blocked">سلف مقصد را انتخاب کنید</span>';
    if (group.locked) return '<span class="fm-bulk-state blocked">فیش گروه مقصد چاپ شده</span>';
    var targetId = Number(group.id);
    var homeId = Number((person.home_group && person.home_group.group_id) || 0);
    var visitId = Number((person.visit && person.visit.group_id) || 0);
    if (person.visit && visitId === targetId) return '<span class="fm-bulk-state blocked">این سلف برای همان روز از قبل ثبت شده است</span>';
    if (person.home_group && homeId === targetId) {
      if (person.home_group.locked) return '<span class="fm-bulk-state blocked">فیش گروه دائمی چاپ شده</span>';
      return person.visit && visitId !== homeId
        ? '<span class="fm-bulk-state ok">بازگشت موقت به گروه دائمی «' + esc(person.home_group.title) + '»</span>'
        : '<span class="fm-bulk-state blocked">همین گروه دائمی؛ انتقالی لازم نیست</span>';
    }
    if (person.home_group && person.home_group.locked) return '<span class="fm-bulk-state blocked">فیش گروه دائمی مبدأ چاپ شده</span>';
    if (person.home_group) {
      var from = person.visit ? person.visit.title : person.home_group.title + ' (دائمی)';
      return '<span class="fm-bulk-state ok">انتقال موقت از «' + esc(from) + '»؛ عضویت دائمی حفظ می‌شود</span>';
    }
    if (person.visit) return '<span class="fm-bulk-state ok">قابل تغییر روزانه از «' + esc(person.visit.title) + '»</span>';
    return '<span class="fm-bulk-state ok">قابل ثبت برای این روز</span>';
  }

  function renderBulkResults() {
    var results = state.bulk.results;
    if (!results || !Array.isArray(results.items)) return '';
    return '<section class="fm-card fm-bulk-results"><div class="fm-toolbar"><div><h3>نتیجهٔ عملیات</h3><span class="fm-small">' + faDigits(results.succeeded || 0) + ' موفق · ' + faDigits(results.failed || 0) + ' ناموفق/ردشده</span></div><button class="btn secondary" type="button" data-fm-action="bulk-clear-results">بستن نتایج</button></div><div class="fm-table-wrap"><table class="fm-rows"><thead><tr><th>پرسنل</th><th>وضعیت</th><th>توضیح</th></tr></thead><tbody>' + results.items.map(function (item) { return '<tr><td>' + esc(item.name || '—') + '</td><td><span class="fm-bulk-state ' + (item.ok ? 'ok' : 'blocked') + '">' + (item.ok ? 'انجام شد' : 'انجام نشد') + '</span></td><td>' + esc(item.message || '') + '</td></tr>'; }).join('') + '</tbody></table></div></section>';
  }

  function renderBulkModal() {
    var bulk = state.bulk;
    if (!bulk.previewOpen) return '';
    var selected = bulkSelectedPeople();
    var targetLabel = bulk.operation === 'cancel' ? (bulk.reason || '—') : ((bulk.operation === 'order' ? bulk.items : bulk.groups).filter(function (item) { return String(item.item_id || item.id) === String(bulk.target); })[0] || {}).food_name || ((bulk.groups || []).filter(function (item) { return String(item.id) === String(bulk.target); })[0] || {}).title || '—';
    return '<div class="fm-bulk-backdrop" data-fm-action="bulk-backdrop"><section class="fm-bulk-modal" role="dialog" aria-modal="true" aria-labelledby="fm-bulk-modal-title"><header><h3 id="fm-bulk-modal-title">بازبینی عملیات گروهی</h3><p>' + esc(bulkOperationTitle(bulk.operation)) + ' · ' + esc(faDigits(jalaliFromIso(bulk.date) || bulk.date)) + ' · ' + esc(bulk.mealType === 'dinner' ? 'شام' : 'ناهار') + '</p></header><div class="fm-bulk-modal-body"><div class="fm-bulk-summary"><div><b>' + faDigits(selected.length) + '</b><small>پرسنل انتخاب‌شده</small></div><div><b>' + esc(targetLabel) + '</b><small>' + (bulk.operation === 'cancel' ? 'دلیل لغو' : 'مقصد عملیات') + '</small></div></div><p>سفارش روزِ بسته‌شده در این مسیر مجاز است. تاریخ گذشته فقط در مهلت اضطراری همان روز و وعده برای ثبت/لغو سفارش قابل اقدام است؛ تغییر سلف و مورد قفل‌شده قابل تغییر نیست. پس از تأیید، نتیجهٔ هر نفر جداگانه نمایش داده می‌شود.</p></div><footer><button type="button" class="btn secondary" data-fm-action="bulk-back">بازگشت</button><button type="button" class="btn primary" data-fm-action="bulk-confirm">تأیید و اجرا</button></footer></section></div>';
  }

  function renderBulkOperations() {
    var bulk = state.bulk;
    var availableModes = canEmergency ? [['canteen', 'تغییر سلف'], ['order', 'ثبت یا تغییر سفارش'], ['cancel', 'لغو سفارش']] : [];
    if (canManageGuestRequests) availableModes.push(['guest', 'مدیریت درخواست مهمان']);
    var operationTabs = availableModes.map(function (item) {
      return '<button type="button" class="fm-bulk-mode ' + (bulk.operation === item[0] ? 'active' : '') + '" role="tab" aria-selected="' + (bulk.operation === item[0] ? 'true' : 'false') + '" data-fm-action="bulk-mode" data-mode="' + item[0] + '">' + item[1] + '</button>';
    }).join('');
    var sectionHead = '<div class="fm-toolbar"><div><h3>عملیات گروهی غذا</h3><span class="fm-small">مدیریت گروهی سفارش‌ها و درخواست‌های غذای مهمان.</span></div><span class="fm-bulk-admin-tag">دسترسی مدیر پشتیبانی / ادمین اصلی</span></div><div class="fm-bulk-operation-tabs" role="tablist">' + operationTabs + '</div>';
    if (bulk.operation === 'guest' && canManageGuestRequests) return '<section class="fm-card fm-bulk-page">' + sectionHead + renderGuestRequestManager() + '</section>';
    if (!bulk.snapshot && bulk.loading) return '<section class="fm-card fm-bulk-page">' + sectionHead + '<div class="fm-spinner">در حال بارگذاری فهرست پرسنل و سفارش‌های روز…</div></section>';
    var snapshot = bulk.snapshot;
    if (!snapshot) return '<section class="fm-card fm-bulk-page">' + sectionHead + '<div class="fm-empty">برای مشاهدهٔ عملیات گروهی، تاریخ و وعده را انتخاب کنید.</div></section>';
    var targetBlock = bulk.operation === 'cancel'
      ? '<label class="fm-field">دلیل لغو<textarea name="reason" data-bulk-reason rows="2" maxlength="300" placeholder="مثلاً: درخواست مدیر واحد یا اصلاح ثبت اشتباه">' + esc(bulk.reason || '') + '</textarea><small>دلیل برای سابقهٔ لغو ذخیره می‌شود.</small></label>'
      : '<label class="fm-field">' + (bulk.operation === 'order' ? 'غذای انتخابی' : 'سلف مقصد') + '<select name="target" data-bulk-target>' + bulkTargetOptions() + '</select><small>' + (bulk.operation === 'order' ? 'برای افراد انتخاب‌شده ثبت می‌شود؛ اگر سفارش قبلی داشته باشند، تغییر می‌کند.' : 'برای عضو دائم، انتخاب سلف دیگر انتقال موقت همان روز است و عضویت ثابت را تغییر نمی‌دهد؛ انتخاب گروه دائمی، انتقال را برمی‌گرداند.') + '</small></label>';
    var rows = (bulk.people || []).filter(function (person) {
      var query = String(bulk.search || '').trim().toLocaleLowerCase();
      return !query || [person.name, person.employee_number, person.department, person.username].join(' ').toLocaleLowerCase().indexOf(query) >= 0;
    });
    var peopleRows = rows.map(function (person) {
      var eligible = bulkCanSelect(person);
      var selected = (bulk.selectedIds || []).indexOf(Number(person.id)) >= 0;
      var current = bulk.operation === 'canteen' ? (person.visit ? person.visit.title + (person.home_group ? ' (انتقال موقت)' : ' (روزانه)') : person.home_group ? person.home_group.title + ' (دائم)' : '—') : (person.order ? person.order.food_name + (person.order.status === 'active' ? '' : ' (لغوشده)') : 'بدون سفارش');
      return '<tr' + (eligible ? '' : ' class="fm-bulk-row-blocked"') + '><td><input type="checkbox" class="fm-bulk-check" data-bulk-person="' + Number(person.id) + '"' + (selected ? ' checked' : '') + (eligible ? '' : ' disabled') + ' aria-label="انتخاب ' + esc(person.name) + '"></td><td><strong>' + esc(person.name) + '</strong><small class="fm-bulk-secondary">' + esc(person.department || 'واحد ثبت نشده') + '</small></td><td>' + esc(faDigits(person.employee_number || '—')) + '</td><td>' + esc(current) + '</td><td>' + bulkPersonStatus(person) + '</td></tr>';
    }).join('') || '<tr><td colspan="5" class="fm-empty">پرسنلی با این جست‌وجو پیدا نشد.</td></tr>';
    var isPast = Boolean(snapshot.is_past);
    var pastOperationAllowed = Boolean(isPast && snapshot.late_order_window_open && ['order', 'cancel'].includes(bulk.operation));
    var isPastBlocked = isPast && !pastOperationAllowed;
    var isClosed = snapshot.order_status === 'closed';
    var topStatus = !snapshot.has_calendar
      ? '<div class="fm-alert warn">برای این تاریخ برنامهٔ غذایی ثبت نشده است؛ ثبت یا لغو سفارش ممکن نیست. تغییر سلف روزانه را می‌توان با انتخاب سلف انجام داد.</div>'
      : (isClosed ? '<div class="fm-alert good">سفارش این روز بسته شده است؛ در این تب مدیر مجاز است سفارش را ثبت/تغییر/لغو کند.</div>' : '');
    if (isPastBlocked) topStatus += '<div class="fm-alert warn">این تاریخ گذشته است و مهلت اضطراری فعال ندارد؛ عملیات سفارش/لغو ممکن نیست.</div>';
    else if (isPast && pastOperationAllowed) topStatus += '<div class="fm-alert good">مهلت اضطراری همین تاریخ و وعده فعال است؛ ثبت یا لغو گروهی سفارش تا پایان مهلت مجاز است. تغییر سلف برای تاریخ گذشته مجاز نیست.</div>';
    var previewDisabled = (!bulk.selectedIds.length || isPastBlocked || (bulk.operation === 'cancel' && !String(bulk.reason || '').trim())) ? ' disabled' : '';
    return '<section class="fm-card fm-bulk-page">' + sectionHead
      + topStatus
      + '<form id="fm-bulk-date-form" class="fm-bulk-controls"><label class="fm-field">تاریخ غذا<input type="text" name="date" data-jalali inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۱" value="' + esc(faDigits(jalaliFromIso(bulk.date) || bulk.date)) + '" required></label><label class="fm-field">وعدهٔ غذایی<select name="meal_type"><option value="lunch"' + (bulk.mealType === 'lunch' ? ' selected' : '') + '>ناهار</option><option value="dinner"' + (bulk.mealType === 'dinner' ? ' selected' : '') + '>شام</option></select></label>' + targetBlock + '<button class="btn secondary" type="submit"' + (bulk.loading ? ' disabled' : '') + '>بارگذاری فهرست روز</button></form>'
      + '<div class="fm-bulk-people-head"><div><strong>پرسنل</strong><small>' + faDigits(rows.length) + ' نفر در فهرست · ' + faDigits(bulk.selectedIds.length) + ' نفر انتخاب‌شده</small></div><div class="fm-bulk-tools"><input type="search" data-bulk-search placeholder="جست‌وجوی نام، شماره یا واحد" value="' + esc(bulk.search || '') + '"><button type="button" class="btn secondary" data-fm-action="bulk-select-all">انتخاب واجدان شرایط</button><button type="button" class="btn secondary" data-fm-action="bulk-clear-selection">پاک‌کردن انتخاب</button><button type="button" class="btn primary" data-fm-action="bulk-preview"' + previewDisabled + '>پیش‌نمایش و بررسی</button></div></div>'
      + '<div class="fm-table-wrap"><table class="fm-rows fm-bulk-table"><thead><tr><th></th><th>نام پرسنل / واحد</th><th>شمارهٔ پرسنلی</th><th>' + (bulk.operation === 'canteen' ? 'سلف فعلی' : 'سفارش فعلی') + '</th><th>وضعیت عملیات</th></tr></thead><tbody>' + peopleRows + '</tbody></table></div>'
      + '<div class="fm-bulk-footer"><span>انتخاب‌شده: <b>' + faDigits(bulk.selectedIds.length) + '</b> نفر</span></div>'
      + renderBulkResults() + renderBulkModal() + '</section>';
  }

  function bulkTogglePerson(id, checked) {
    var selected = new Set((state.bulk.selectedIds || []).map(Number));
    if (checked && selected.size >= 100 && !selected.has(Number(id))) { state.error = 'در هر نوبت حداکثر ۱۰۰ نفر قابل انتخاب است.'; render(); return; }
    if (checked) selected.add(Number(id)); else selected.delete(Number(id));
    state.error = '';
    state.bulk.selectedIds = Array.from(selected);
    state.bulk.previewOpen = false;
    render();
  }

  function bulkSelectEligible() {
    var eligible = (state.bulk.people || []).filter(bulkCanSelect).map(function (person) { return Number(person.id); });
    state.bulk.selectedIds = eligible.slice(0, 100);
    state.error = eligible.length > 100 ? 'برای هر نوبت فقط ۱۰۰ نفر انتخاب شد؛ برای بقیه، عملیات را در نوبت بعدی اجرا کنید.' : '';
    render();
  }

  async function submitBulkOperation() {
    var bulk = state.bulk;
    var ids = (bulk.selectedIds || []).map(Number).filter(function (id) { return id > 0; });
    if (!ids.length) { state.error = 'حداقل یک نفر را انتخاب کنید.'; render(); return; }
    var payload = { operation: bulk.operation, date: bulk.date, meal_type: bulk.mealType, employee_ids: ids };
    if (bulk.operation === 'order') payload.item_id = Number(bulk.target || 0);
    if (bulk.operation === 'canteen') payload.group_id = Number(bulk.target || 0);
    if (bulk.operation === 'cancel') payload.reason = String(bulk.reason || '').trim();
    bulk.submitting = true; bulk.previewOpen = false; state.error = ''; render();
    try {
      bulk.results = await request('food-menu/bulk-apply', null, { method: 'POST', body: JSON.stringify(payload) });
      state.notice = bulk.results.message || 'عملیات گروهی انجام شد.';
      await loadBulkSnapshot(bulk.date, bulk.mealType);
      bulk.selectedIds = [];
    } catch (error) {
      state.error = error.message || 'عملیات گروهی انجام نشد.';
    } finally {
      bulk.submitting = false;
      render();
    }
  }

  function mealTypeSwitch() {
    if (!['calendar', 'orders', 'statistics'].includes(state.tab)) return '';
    return '<div class="fm-meal-switch" role="group" aria-label="وعدهٔ غذایی" style="display:flex;gap:8px;margin:0 0 12px"><button class="btn ' + (state.mealType === 'lunch' ? 'primary' : 'secondary') + '" type="button" data-fm-action="meal-type" data-meal-type="lunch">ناهار</button><button class="btn ' + (state.mealType === 'dinner' ? 'primary' : 'secondary') + '" type="button" data-fm-action="meal-type" data-meal-type="dinner">شام</button></div>';
  }

  /** پنل مربوط به تبهای reportها را (بدون منوی برنامهٔ غذایی) برمیگرداند. */
  function embeddedPanel() {
    if (state.embeddedMode === 'delivered') return '<div class="fm-embedded-head"><h2>غذای تحویلی</h2><span class="fm-small">فیشهای چاپشده و غذای تحویلشده</span></div>' + renderPrintedDeliveryReport();
    if (state.embeddedMode === 'statistics') return '<div class="fm-embedded-head"><h2>آمار سفارشها</h2><span class="fm-small">گزارش نوع غذا و آمار سفارشات</span></div>' + renderStatistics();
    if (state.embeddedMode === 'todayorders') return '<div class="fm-embedded-head"><h2>سفارشهای امروز</h2><span class="fm-small">فهرست سفارشهای ثبتشده روز</span></div>' + renderOrders();
    return '';
  }
  /** لود دیتای مخصوص پنل جاسازیشده. */
  function loadEmbedded() {
    if (!state.embeddedMode) return Promise.resolve();
    var from = state.statisticsFrom || state.selectedDate;
    var to = state.statisticsTo || state.selectedDate;
    if (state.embeddedMode === 'todayorders') return loadOrders(state.ordersDate || state.selectedDate);
    if (state.embeddedMode === 'delivered') { var d = deliveryDateForReport(); return loadPrintedDeliveryReport(d, d); }
    return loadStatistics(from, to, state.statisticsMode || 'food');
  }

  function render() {
    if (!root || !root.isConnected) root = document.getElementById('food-menu-root');
    if (!root) return;
    if (!canMenu && !canClose && !canEmergency && !canManageGuestRequests) {
      root.innerHTML = '<div class="fm-alert error">مجوز برنامهٔ غذایی یا عملیات گروهی برای این حساب تعریف نشده است.</div>';
      return;
    }
    if (!Cal) {
      root.innerHTML = '<div class="fm-alert error">تقویم شمسی سامانه بارگذاری نشد. صفحه را تازه‌سازی کنید.</div>';
      return;
    }
    var panel = state.embeddedMode ? embeddedPanel() : (state.tab === 'calendar' && (canMenu || canClose) ? renderCalendar() : state.tab === 'catalog' && canMenu ? renderCatalog() : state.tab === 'orders' && canClose ? renderOrders() : state.tab === 'cancel-requests' && canClose ? renderCancelRequests() : state.tab === 'statistics' && canClose ? renderStatistics() : state.tab === 'bulk' && (canEmergency || canManageGuestRequests) ? renderBulkOperations() : '<div class="fm-card fm-empty">این بخش با مجوزهای فعلی در دسترس نیست.</div>');
    var notices = (state.error ? '<div class="fm-alert error">' + esc(state.error) + '</div>' : '') + (state.notice && !state.error ? '<div class="fm-alert good">' + esc(state.notice) + '</div>' : '');
    root.innerHTML = state.embeddedMode ? ('<div class="fm-embedded-report">' + notices + panel + '</div>') : ('<div class="fm-intro"><div><h2>برنامه غذایی</h2></div></div>' + notices + navTabs() + mealTypeSwitch() + panel);
    if (global.ItsmJalali && typeof global.ItsmJalali.enhanceAll === 'function') global.ItsmJalali.enhanceAll(root);
  }

  async function loadStatus() {
    if (!canMenu && !canClose) return null;
    try {
      state.status = await request('food-menu/status');
      if (!state.selectedDate && state.status.today) state.selectedDate = state.status.today;
      return state.status;
    } catch (e) {
      state.status = null;
      state.error = e.message || 'وضعیت سفارش بارگذاری نشد.';
      return null;
    }
  }
  async function loadCatalog() {
    if (!canMenu) return;
    try {
      var result = await request('food-menu/catalog');
      state.catalog = Array.isArray(result.items) ? result.items : [];
    } catch (e) { state.catalog = []; state.error = e.message || 'بانک غذا بارگذاری نشد.'; }
  }
  async function loadMonth() {
    if (!canMenu && !canClose) return;
    state.loading = true;
    try {
      state.monthData = await request('food-menu/month', { month: monthKey(), meal_type: state.mealType });
    } catch (e) { state.error = e.message || 'تقویم بارگذاری نشد.'; state.monthData = null; }
    finally { state.loading = false; render(); }
  }
  async function loadOrders(date) {
    if (!canClose) return;
    var selected = normalizeIsoDate(date || state.ordersDate || state.selectedDate);
    if (!selected) return;
    state.ordersDate = selected;
    state.selectedDate = selected;
    state.ordersLoaded = false;
    state.loadingOrders = true;
    render();
    try {
      var result = await request('food-menu/orders', { date: selected, meal_type: state.mealType });
      state.orders = Array.isArray(result.items) ? result.items : [];
      state.ordersLoaded = true;
      state.error = '';
    } catch (e) { state.orders = []; state.ordersLoaded = false; state.error = e.message || 'سفارش‌های روز بارگذاری نشد.'; }
    finally { state.loadingOrders = false; render(); }
  }

  async function loadFoodGroups() {
    if (!canClose) return;
    state.foodGroupsLoaded = false;
    try {
      var result = await request('food-menu/groups');
      state.foodGroups = Array.isArray(result.items) ? result.items : [];
      state.foodGroupsLoaded = true;
      if (!state.groupOrdersGroupId && state.foodGroups.length) state.groupOrdersGroupId = String(state.foodGroups[0].id);
    } catch (e) {
      state.foodGroups = [];
      state.foodGroupsLoaded = true;
      state.error = e.message || 'فهرست گروه‌های غذا بارگذاری نشد.';
    }
  }

  async function loadGroupOrders(groupId, date) {
    if (!canClose) return;
    var selectedGroup = String(groupId || state.groupOrdersGroupId || '');
    var selectedDate = normalizeIsoDate(date || state.groupOrdersDate || state.ordersDate || state.selectedDate);
    if (!selectedGroup || !selectedDate) return;
    state.groupOrdersGroupId = selectedGroup;
    state.groupOrdersDate = selectedDate;
    state.groupOrdersLoaded = false;
    state.loadingGroupOrders = true;
    render();
    try {
      var result = await request('food-menu/group-orders', { group_id: selectedGroup, date: selectedDate, meal_type: state.mealType });
      state.groupOrders = Array.isArray(result.items) ? result.items : [];
      state.groupOrdersTitle = result.group && result.group.title ? result.group.title : '';
      state.groupOrdersGroups = Array.isArray(result.groups) ? result.groups : [];
      state.groupOrdersLoaded = true;
      state.error = '';
    } catch (e) {
      state.groupOrders = [];
      state.groupOrdersGroups = [];
      state.groupOrdersLoaded = false;
      state.error = e.message || 'سفارش‌های گروه بارگذاری نشد.';
    } finally {
      state.loadingGroupOrders = false;
      render();
    }
  }

  async function loadCancelRequests() {
    if (!canClose) return;
    state.loadingCancelRequests = true;
    render();
    try {
      var result = await request('food-menu/cancel-requests');
      state.cancelRequests = Array.isArray(result.items) ? result.items : [];
      state.error = '';
    } catch (e) {
      state.cancelRequests = [];
      state.error = e.message || 'درخواست‌های لغو بارگذاری نشد.';
    } finally {
      state.loadingCancelRequests = false;
      render();
    }
  }

  async function loadGuestRequests() {
    if (!canManageGuestRequests) return;
    state.loadingGuestRequests = true;
    state.error = '';
    render();
    try {
      var result = await request('food-menu/guest-requests');
      state.guestRequests = Array.isArray(result.items) ? result.items : [];
      state.guestRequestsLoaded = true;
    } catch (e) {
      state.guestRequests = [];
      state.guestRequestsLoaded = false;
      state.error = e.message || 'درخواست‌های غذای مهمان بارگذاری نشد.';
    } finally {
      state.loadingGuestRequests = false;
      render();
    }
  }

  function beginGuestEdit(id) {
    var row = (state.guestRequests || []).find(function (item) { return Number(item.id) === Number(id); });
    if (!row || !row.can_edit) return;
    state.guestEdit = {
      id: Number(row.id),
      request_date: row.request_jalali || '',
      organization: row.organization || '',
      guest_count: String(row.guest_count || ''),
      requester_name: row.requester_name || '',
      note: row.note || ''
    };
    state.error = '';
    render();
    var form = root && root.querySelector('#fm-guest-edit-form');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function cancelGuestEdit() {
    state.guestEdit = null;
    state.error = '';
    render();
  }

  async function saveGuestEdit(form) {
    var data = new FormData(form);
    var body = {
      id: Number((state.guestEdit || {}).id || 0),
      request_date: normalizeIsoDate(data.get('request_date')) || '',
      organization: String(data.get('organization') || '').trim(),
      guest_count: String(data.get('guest_count') || '').trim(),
      requester_name: String(data.get('requester_name') || '').trim(),
      note: String(data.get('note') || '').trim()
    };
    if (!body.id || !body.request_date || body.organization.length < 2 || !body.guest_count || body.requester_name.length < 2) {
      state.error = 'تاریخ، سازمان، تعداد و نام درخواست‌دهنده را درست وارد کنید.';
      render();
      return;
    }
    state.guestEdit = Object.assign({}, state.guestEdit, body);
    state.guestSaving = true;
    state.error = '';
    render();
    try {
      var result = await request('food-menu/guest-request-update', null, { method: 'POST', body: JSON.stringify(body) });
      state.guestEdit = null;
      state.notice = result.message || 'درخواست مهمان ویرایش شد.';
      await loadGuestRequests();
    } catch (e) {
      state.error = e.message || 'ویرایش درخواست مهمان انجام نشد.';
    } finally {
      state.guestSaving = false;
      render();
    }
  }

  async function guestRequestAction(id, actionName) {
    var row = (state.guestRequests || []).find(function (item) { return Number(item.id) === Number(id); });
    if (!row) return;
    if (actionName === 'cancel' && !row.can_cancel) return;
    if (actionName === 'delete' && !row.can_delete) return;
    var prompt = actionName === 'cancel'
      ? 'درخواست فعال غذای مهمان لغو شود؟'
      : 'درخواست غذای مهمان به‌طور کامل حذف شود؟ این کار قابل بازگشت نیست.';
    if (!global.confirm(prompt)) return;
    state.guestActionBusyId = Number(id);
    state.error = '';
    render();
    try {
      var result = await request('food-menu/guest-request-' + actionName, null, { method: 'POST', body: JSON.stringify({ id: Number(id) }) });
      state.notice = result.message || (actionName === 'cancel' ? 'درخواست مهمان لغو شد.' : 'درخواست مهمان حذف شد.');
      await loadGuestRequests();
    } catch (e) {
      state.error = e.message || 'عملیات روی درخواست مهمان انجام نشد.';
    } finally {
      state.guestActionBusyId = 0;
      render();
    }
  }

  function renderGuestRequestManager() {
    if (state.loadingGuestRequests && !state.guestRequestsLoaded) return '<div class="fm-card fm-spinner">در حال بارگذاری همهٔ درخواست‌های مهمان…</div>';
    var query = String(state.guestRequestSearch || '').trim().toLocaleLowerCase('fa');
    var rows = (state.guestRequests || []).filter(function (item) {
      if (!query) return true;
      return [item.request_jalali, item.organization, item.deputy_name, item.requester_name, item.created_by_name, item.status, item.guest_count]
        .join(' ').toLocaleLowerCase('fa').replace(/[يى]/g, 'ی').replace(/ك/g, 'ک').indexOf(query.replace(/[يى]/g, 'ی').replace(/ك/g, 'ک')) >= 0;
    });
    var edit = state.guestEdit;
    var editForm = edit ? '<form id="fm-guest-edit-form" class="fm-bulk-controls"><label class="fm-field">تاریخ شمسی<input type="text" name="request_date" data-jalali inputmode="numeric" value="' + esc(faDigits(edit.request_date || '')) + '" required></label><label class="fm-field">سازمان / شرکت<input type="text" name="organization" maxlength="190" value="' + esc(edit.organization || '') + '" required></label><label class="fm-field">تعداد مهمان<input type="text" name="guest_count" inputmode="numeric" maxlength="3" value="' + esc(faDigits(edit.guest_count || '')) + '" required></label><label class="fm-field">نام درخواست‌دهنده<input type="text" name="requester_name" maxlength="150" value="' + esc(edit.requester_name || '') + '" required></label><label class="fm-field">یادداشت<input type="text" name="note" maxlength="500" value="' + esc(edit.note || '') + '"></label><button class="btn primary" type="submit"' + (state.guestSaving ? ' disabled' : '') + '>' + (state.guestSaving ? 'در حال ذخیره…' : 'ذخیرهٔ ویرایش') + '</button><button class="btn secondary" type="button" data-fm-action="guest-edit-cancel"' + (state.guestSaving ? ' disabled' : '') + '>انصراف</button></form>' : '';
    var tableRows = rows.map(function (item) {
      var actions = [];
      if (item.can_edit) actions.push('<button class="btn secondary" type="button" data-fm-action="guest-edit" data-id="' + Number(item.id) + '">ویرایش</button>');
      if (item.can_cancel) actions.push('<button class="btn secondary" type="button" data-fm-action="guest-cancel" data-id="' + Number(item.id) + '"' + (state.guestActionBusyId === Number(item.id) ? ' disabled' : '') + '>لغو</button>');
      if (item.can_delete) actions.push('<button class="btn danger" type="button" data-fm-action="guest-delete" data-id="' + Number(item.id) + '"' + (state.guestActionBusyId === Number(item.id) ? ' disabled' : '') + '>حذف</button>');
      var status = item.status === 'cancelled' ? 'لغوشده' : 'فعال';
      return '<tr><td>' + esc(faDigits(item.request_jalali || '—')) + '</td><td>' + esc(item.deputy_name || '—') + '</td><td>' + esc(item.organization || '—') + '</td><td>' + esc(faDigits(item.guest_count || 0)) + '</td><td>' + esc(item.requester_name || '—') + '</td><td>' + esc(item.created_by_name || '—') + '</td><td>' + status + '</td><td>' + (actions.join(' ') || '<span class="fm-small">—</span>') + '</td></tr>';
    }).join('') || '<tr><td colspan="8" class="fm-empty">' + (state.guestRequests.length ? 'با این جست‌وجو درخواستی پیدا نشد.' : 'درخواستی برای نمایش وجود ندارد.') + '</td></tr>';
    return '<section class="fm-card fm-guest-request-manager"><div class="fm-toolbar"><div><h3>مدیریت درخواست‌های غذای مهمان</h3><span class="fm-small">همهٔ درخواست‌ها، شامل سوابق گذشته؛ عملیات فقط در مهلت مجاز انجام می‌شود.</span></div><button class="btn secondary" type="button" data-fm-action="guest-reload"' + (state.loadingGuestRequests ? ' disabled' : '') + '>به‌روزرسانی</button></div>' + editForm + '<div class="fm-bulk-tools"><input type="search" data-guest-request-search placeholder="جست‌وجوی تاریخ، سازمان، مدیر یا درخواست‌دهنده" value="' + esc(state.guestRequestSearch || '') + '"><span class="fm-small">' + faDigits(rows.length) + ' از ' + faDigits((state.guestRequests || []).length) + ' درخواست</span></div><div class="fm-table-wrap"><table class="fm-rows"><thead><tr><th>تاریخ</th><th>معاونت</th><th>سازمان / شرکت</th><th>تعداد</th><th>درخواست‌دهنده</th><th>مدیر ثبت‌کننده</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>' + tableRows + '</tbody></table></div></section>';
  }

  async function decideCancelRequest(id, decision) {
    if (!id) return;
    var ok = decision === 'approve'
      ? window.confirm('سفارش این روز لغو شود؟ رکورد برای سوابق می‌ماند.')
      : window.confirm('این درخواست لغو رد شود؟');
    if (!ok) return;
    try {
      var result = await request('food-menu/cancel-request-decide', null, { method: 'POST', body: JSON.stringify({ id: Number(id), decision: decision }) });
      notify(result.message || (decision === 'approve' ? 'سفارش لغو شد.' : 'درخواست رد شد.'));
      await loadCancelRequests();
    } catch (e) {
      notify(e.message || 'عملیات انجام نشد.', true);
    }
  }

  async function loadStatistics(fromDate, toDate, mode) {
    if (!canClose) return;
    var from = normalizeIsoDate(fromDate || state.statisticsFrom || state.selectedDate);
    var to = normalizeIsoDate(toDate || state.statisticsTo || from);
    var selectedMode = String(mode || state.statisticsMode || 'food');
    if (!from || !to || from > to || !['food', 'employee', 'selected'].includes(selectedMode)) return;
    loadPrintedDeliveryReport(deliveryDateForReport(), deliveryDateForReport());
    var selectedIds = (state.reportSelectedEmployees || []).map(function (person) { return Number(person.id); }).filter(function (id) { return id > 0; });
    if (selectedMode === 'selected' && !selectedIds.length) {
      state.statisticsFrom = from; state.statisticsTo = to; state.statisticsMode = selectedMode;
      state.statistics = []; state.statisticsLoaded = false; state.loadingStatistics = false; state.statisticsRequestId++;
      render(); return;
    }
    state.statisticsFrom = from;
    state.statisticsTo = to;
    state.statisticsMode = selectedMode;
    state.statistics = [];
    state.statisticsLoaded = false;
    state.loadingStatistics = true;
    var requestId = ++state.statisticsRequestId;
    render();
    try {
      var query = { from: from, to: to, mode: selectedMode, meal_type: state.mealType };
      if (selectedMode === 'selected') query.employee_ids = selectedIds.join(',');
      var result = await request('food-menu/statistics', query);
      if (requestId !== state.statisticsRequestId) return;
      state.statistics = Array.isArray(result.items) ? result.items : [];
      state.statisticsLoaded = true;
      state.error = '';
    } catch (e) {
      if (requestId !== state.statisticsRequestId) return;
      state.statistics = [];
      state.error = e.message || 'آمار سفارش‌ها بارگذاری نشد.';
    } finally {
      if (requestId === state.statisticsRequestId) {
        state.loadingStatistics = false;
        render();
      }
    }
  }
  async function searchSelectedReportEmployees(query) {
    var q = String(query || '').trim();
    var requestId = ++state.reportEmployeeSearchSeq;
    state.reportEmployeeQuery = q;
    if (q.length === 1) {
      state.reportEmployeeResults = [];
      state.reportEmployeeResultsLoaded = true;
      state.reportEmployeeSearchLoading = false;
      render();
      return;
    }
    state.reportEmployeeSearchLoading = true;
    try {
      var result = await request('food-menu/employee-search', { q: q });
      if (requestId !== state.reportEmployeeSearchSeq) return;
      state.reportEmployeeResults = Array.isArray(result.items) ? result.items : [];
      state.reportEmployeeResultsLoaded = true;
      state.error = '';
    } catch (error) {
      if (requestId !== state.reportEmployeeSearchSeq) return;
      state.reportEmployeeResults = [];
      state.reportEmployeeResultsLoaded = true;
      state.error = error.message || 'فهرست پرسنل بارگذاری نشد.';
    } finally {
      if (requestId === state.reportEmployeeSearchSeq) state.reportEmployeeSearchLoading = false;
    }
    render();
  }
  async function refreshAll() {
    state.error = ''; state.notice = '';
    await loadStatus();
    if (!state.selectedDate) state.selectedDate = (state.status && state.status.today) || localTodayIso();
    if (!state.ordersDate) state.ordersDate = state.selectedDate;
    if (!state.statisticsFrom) state.statisticsFrom = state.selectedDate;
    if (!state.statisticsTo) state.statisticsTo = state.selectedDate;
    var initialMonth = jalaliMonthFromIso(state.selectedDate);
    if (initialMonth && !state.monthData) { state.year = initialMonth[0]; state.month = initialMonth[1]; }
    var jobs = [];
    if (canMenu) jobs.push(loadCatalog());
    if (canMenu || canClose) jobs.push(loadMonth());
    if (state.embeddedMode) jobs.push(loadEmbedded());
    else {
      if (canClose && state.tab === 'orders') jobs.push(loadOrders(state.ordersDate || state.selectedDate));
      if (canClose) { jobs.push(loadCancelRequests()); jobs.push(loadFoodGroups()); }
    }
    await Promise.all(jobs);
    render();
    if (canClose && state.tab === 'statistics' && state.statisticsMode === 'selected' && !state.reportEmployeeResultsLoaded && !state.reportEmployeeSearchLoading) searchSelectedReportEmployees('');
    if ((canEmergency || canManageGuestRequests) && state.tab === 'bulk') {
      if (state.bulk.operation === 'guest' && !state.guestRequestsLoaded && !state.loadingGuestRequests) loadGuestRequests();
      else if (state.bulk.operation !== 'guest' && !state.bulk.snapshot && !state.bulk.loading) loadBulkSnapshot(state.bulk.date || state.selectedDate, state.bulk.mealType || state.mealType);
    }
  }

  async function saveDay(confirmed, expectedImpact) {
    if (!canMenu || state.daySaving) return;
    var form = root.querySelector('#fm-day-form');
    if (!form) return;
    var body;
    if (confirmed && state.pendingImpact) {
      // Confirmation applies to the exact plan shown in the impact warning;
      // do not rebuild it from disabled/possibly edited form controls.
      body = Object.assign({}, state.pendingImpact);
      body.confirm_impact = true;
      body.expected_impact = Number(expectedImpact || 0);
    } else {
      var formData = new FormData(form);
      var foodIds = orderedDayFoodIds(form, state.selectedDate);
      var statusField = form.querySelector('[name="order_status"]:checked');
      body = {
        date: state.selectedDate,
        meal_type: state.mealType,
        food_ids: foodIds,
        order_status: statusField && !statusField.disabled ? String(statusField.value || '') : '',
        note: String(formData.get('note') || '').trim(),
      };
      if (confirmed) { body.confirm_impact = true; body.expected_impact = Number(expectedImpact || 0); }
    }
    state.daySaving = true;
    state.dayError = ''; state.pendingImpact = null; state.pendingImpactExpected = 0; state.pendingImpactMessage = '';
    var button = form.querySelector('[type="submit"]'); if (button) button.disabled = true;
    Array.from(form.querySelectorAll('input,select,textarea,button')).forEach(function (control) { control.disabled = true; });
    root.querySelectorAll('[data-fm-action="confirm-impact"],[data-fm-action="cancel-impact"]').forEach(function (control) { control.disabled = true; });
    try {
      var result = await request('food-menu/day', null, { method: 'POST', body: JSON.stringify(body) });
      state.notice = result.message || 'برنامهٔ روز ذخیره شد.'; state.error = ''; state.dayError = '';
      await loadMonth();
      if (canClose && state.tab === 'orders') await loadOrders(state.ordersDate || state.selectedDate);
    } catch (e) {
      if (e.payload && e.payload.confirmation_required) {
        state.pendingImpact = body;
        state.pendingImpactExpected = Number(e.payload.affected_orders || 0);
        state.pendingImpactMessage = e.payload.message || 'این تغییر سفارش‌های فعال را لغو می‌کند.';
      } else {
        state.dayError = e.message || 'ذخیرهٔ برنامه انجام نشد.';
      }
    } finally { state.daySaving = false; render(); }
  }

  async function saveCatalog(form) {
    if (!canMenu) return;
    var isNew = form.id === 'fm-catalog-add';
    var data = new FormData(form);
    var body = { id: Number(data.get('id') || 0), food_name: String(data.get('food_name') || '').trim(), category: String(data.get('category') || 'سایر'), active: String(data.get('active') || '1') === '1' };
    var saved = false;
    try {
      var result = await request('food-menu/catalog', null, { method: 'POST', body: JSON.stringify(body) });
      state.catalog = Array.isArray(result.items) ? result.items : [];
      state.notice = result.message || 'بانک غذا ذخیره شد.'; state.error = '';
      saved = true;
    } catch (e) { state.error = e.message || 'ذخیرهٔ غذا ناموفق بود.'; }
    render();
    if (isNew) {
      // Keep the new-food box active: cleared after a successful add, text kept on error.
      var input = global.document.querySelector('#fm-catalog-add input[name="food_name"]');
      if (input) {
        if (saved) input.value = '';
        else input.value = String(data.get('food_name') || '');
        input.focus();
      }
    }
  }


  // ترتیب خروجی همان ترتیب نمایش است: در نمای منو، گروه‌ها به ترتیب منو و در هر گروه طبق سرستون مرتب‌شده
  function orderExportRows() {
    var rows = state.orders || [];
    if (state.ordersView !== 'menu') return sortOrderRows(rows);
    var groups = {};
    rows.forEach(function (row) {
      var key = Number(row.menu_no || 0);
      (groups[key] = groups[key] || []).push(row);
    });
    var keys = Object.keys(groups).map(Number).sort(function (a, b) {
      if (a === 0) return 1;
      if (b === 0) return -1;
      return a - b;
    });
    var out = [];
    keys.forEach(function (key) {
      out = out.concat(sortOrderRows(groups[key]).map(function (row) { return { row: row, menu: key }; }));
    });
    return out;
  }

  function downloadOrdersCsv() {
    var columns = selectedOrderColumns();
    var items = orderExportRows();
    var headers = columns.map(function (col) { return col.label; });
    var rows = items.map(function (item, index) {
      var row = item.row !== undefined ? item.row : item;
      return columns.map(function (col) { return orderFieldValue(row, col.key, index); });
    });
    var filename = 'orders-' + (state.ordersDate || 'day');
    var api = global.ITSM_LIST_SORT;
    if (api && api.exportXlsx) {
      api.exportXlsx('فهرست سفارش‌های روز ' + faDigits(jalaliFromIso(state.ordersDate) || ''), headers, rows, filename, null);
    } else {
      global.location.href = buildUrl('food-menu/orders-export', { date: state.ordersDate, meal_type: state.mealType, fields: columns.map(function (col) { return col.key; }).join(',') });
    }
  }

  function downloadOrders() {
    if (!canClose || (state.tab !== 'orders' && state.embeddedMode !== 'todayorders') || !state.ordersLoaded || state.loadingOrders || !state.ordersDate) return;
    downloadOrdersCsv();
  }

  function printScopedReport(selector, bodyClass) {
    var doc = global.document;
    var target = (root && root.querySelector(selector)) || (doc && doc.querySelector(selector));
    if (!doc || !doc.body || !target || typeof global.print !== 'function') return;
    var printHost = doc.createElement('div');
    printHost.className = 'fm-print-host';
    var printList = target.cloneNode(true);
    printList.classList.add('fm-print-list');
    printHost.appendChild(printList);
    doc.body.appendChild(printHost);
    doc.body.classList.add(bodyClass);
    var cleaned = false;
    var cleanupTimer = null;
    function cleanup() {
      if (cleaned) return;
      cleaned = true;
      doc.body.classList.remove(bodyClass);
      if (printHost.parentNode) printHost.parentNode.removeChild(printHost);
      if (cleanupTimer !== null && typeof global.clearTimeout === 'function') global.clearTimeout(cleanupTimer);
      if (typeof global.removeEventListener === 'function') global.removeEventListener('afterprint', cleanup);
    }
    global.addEventListener('afterprint', cleanup, { once: true });
    try {
      global.print();
    } catch (error) {
      cleanup();
      throw error;
    }
    if (!cleaned) cleanupTimer = global.setTimeout(cleanup, 30000);
  }

  function printOrders() {
    if (!canClose || (state.tab !== 'orders' && state.embeddedMode !== 'todayorders') || state.mealType !== 'lunch' || !state.ordersLoaded || state.loadingOrders) return;
    printScopedReport('.fm-daily-orders .fm-table-wrap', 'fm-print-orders');
  }

  function downloadStatistics() {
    if (!canClose || !state.statisticsLoaded || !state.statisticsFrom || !state.statisticsTo) return;
    var params = { from: state.statisticsFrom, to: state.statisticsTo, mode: state.statisticsMode || 'food', meal_type: state.mealType };
    if (params.mode === 'selected') params.employee_ids = (state.reportSelectedEmployees || []).map(function (person) { return Number(person.id); }).filter(function (id) { return id > 0; }).join(',');
    global.location.href = buildUrl('food-menu/export', params);
  }

  function printStatistics() {
    if (!state.statisticsLoaded) return;
    printScopedReport('.fm-statistics .fm-statistics-results', 'fm-print-statistics');
  }

  function selectDay(iso, currentMonth) {
    if (!currentMonth) return;
    state.selectedDate = iso;
    state.dayError = '';
    state.pendingImpact = null; state.pendingImpactExpected = 0; state.pendingImpactMessage = '';
    state.error = '';
    render();
  }
  function changeMonth(year, month, selectedIso) {
    state.year = Number(year); state.month = Number(month);
    state.selectedDate = selectedIso || Cal.isoDate(state.year, state.month, 1);
    state.error = ''; state.pendingImpact = null; state.pendingImpactExpected = 0; state.pendingImpactMessage = '';
    loadMonth();
  }
  function switchTab(tab) {
    if ((tab === 'calendar' && !canMenu && !canClose) || (tab === 'catalog' && !canMenu)) return;
    if ((tab === 'orders' || tab === 'statistics' || tab === 'cancel-requests') && !canClose) return;
    if (tab === 'bulk' && !canEmergency && !canManageGuestRequests) return;
    state.tab = tab; state.error = ''; state.notice = ''; render();
    if (tab === 'cancel-requests') {
      loadCancelRequests();
    }
    if (tab === 'orders') {
      var defaultOrderDate = state.ordersDate || state.selectedDate || (state.status && state.status.today) || localTodayIso();
      loadOrders(defaultOrderDate);
    }
    if (tab === 'statistics' && !state.statisticsLoaded) {
      loadStatistics(state.statisticsFrom || state.selectedDate, state.statisticsTo || state.selectedDate, state.statisticsMode || 'food');
    }
    if (tab === 'statistics' && state.statisticsMode === 'selected' && !state.reportEmployeeResultsLoaded && !state.reportEmployeeSearchLoading) searchSelectedReportEmployees('');
    if (tab === 'calendar' && !state.monthData && (canMenu || canClose)) loadMonth();
    if (tab === 'catalog' && !state.catalog.length && canMenu) loadCatalog().then(render);
    if (tab === 'bulk') {
      if (state.bulk.operation === 'guest') loadGuestRequests();
      else {
        var bulkDate = state.bulk.date || state.selectedDate || (state.status && state.status.today) || localTodayIso();
        loadBulkSnapshot(bulkDate, state.bulk.mealType || state.mealType);
      }
    }
  }

  // ---- سفارش اضطراری (فقط food.emergency) ----
  // ترتیب: ۱) انتخاب کاربر ۲) وارد کردن کد ملی و تطبیق با همان کاربر ۳) ثبت / تغییر سفارش یا لغو سفارش.
  var canEmergency = global.FOOD_TICKET_CAN_EMERGENCY === true;
  var canManageGuestRequests = global.FOOD_TICKET_CAN_GUEST_REQUEST_MANAGE === true;
  if (canEmergency && !canMenu && !canClose) state.tab = 'bulk';
  if (canManageGuestRequests && !canEmergency && !canMenu && !canClose) {
    state.tab = 'bulk';
    state.bulk.operation = 'guest';
  }
  function emergencyEnsurePeople() {
    var em = state.emergency = state.emergency || {};
    if (em.people || em.peopleLoading) return;
    em.peopleLoading = true;
    request('food-menu/emergency/people', null).then(function (payload) {
      em.people = payload.items || [];
    }).catch(function (error) {
      em.people = [];
      em.error = error.message || 'فهرست کاربران بارگذاری نشد.';
    }).then(function () {
      em.peopleLoading = false;
      render();
    });
  }
  function emergencyPanel() {
    if (!canEmergency || state.tab !== 'calendar') return '';
    emergencyEnsurePeople();
    var iso = state.selectedDate;
    var day = getDay(iso);
    var em = state.emergency || {};
    var items = day && Array.isArray(day.items) ? day.items.filter(function (item) { return item.item_id && item.food_active !== false; }) : [];
    var status = day && day.order_status === 'closed' ? 'بسته' : 'باز';
    var head = '<div class="fm-card fm-emergency"><div class="fm-toolbar"><div><h3>سفارش اضطراری</h3><span class="fm-small">برای روز ' + esc(faDigits(jalaliFromIso(iso))) + ' (وضعیت: ' + esc(status) + ')؛ ابتدا کاربر را انتخاب کنید، سپس کد ملی او را برای تأیید وارد کنید.</span></div></div>';
    var msgs = (em.error ? '<div class="fm-alert error">' + esc(em.error) + '</div>' : '') + (em.notice && !em.error ? '<div class="fm-alert good">' + esc(em.notice) + '</div>' : '');
    if (!day) {
      return head + msgs + '<div class="fm-empty">برای این روز برنامهٔ غذایی ثبت نشده است.</div></div>';
    }
    if (!em.person) {
      var people = em.people || [];
      var options = '<option value="">— یک کاربر را انتخاب کنید —</option>' + people.map(function (p) {
        return '<option value="' + Number(p.id) + '"' + (Number(em.pickedId) === Number(p.id) ? ' selected' : '') + '>' + esc(p.name) + ' (' + esc(p.username) + ')</option>';
      }).join('');
      var pick = '<div class="fm-emergency-step"><label class="fm-field">۱. کاربر' + (em.peopleLoading ? ' (در حال بارگذاری…)' : '') + '<select name="emergency_person">' + options + '</select></label>'
        + '<button type="button" class="fm-btn" data-em-action="pick"' + (em.peopleLoading ? ' disabled' : '') + '>انتخاب کاربر</button></div>';
      var verify = em.pickedId
        ? '<div class="fm-emergency-step"><label class="fm-field">۲. کد ملی ' + esc(em.pickedName || '') + '<input type="text" name="emergency_nc" inputmode="numeric" maxlength="10" value="' + esc(em.nc || '') + '" autocomplete="off"></label>'
          + '<button type="button" class="fm-btn" data-em-action="verify">تأیید کد ملی</button></div>'
        : '';
      return head + msgs + pick + verify + '</div>';
    }
    var select = items.length
      ? '<select name="emergency_item">' + items.map(function (item) { return '<option value="' + Number(item.item_id) + '">' + esc(item.food_name || '') + '</option>'; }).join('') + '</select>'
      : '<div class="fm-empty">غذایی برای این روز تعریف نشده است.</div>';
    var orders = em.orders || [];
    var hasActive = orders.some(function (order) { return order.status === 'active'; });
    var orderList = orders.length
      ? '<ul class="fm-emergency-orders">' + orders.map(function (order) {
          return '<li>' + esc(order.food_name || '') + ' <span class="fm-small">(' + esc(order.status === 'active' ? 'فعال' : (order.status === 'cancelled' ? 'لغو شده' : order.status)) + ')</span> '
            + (order.status === 'active' ? '<button type="button" class="fm-btn secondary" data-em-action="cancel" data-order-id="' + Number(order.id) + '">لغو سفارش</button>' : '') + '</li>';
        }).join('') + '</ul>'
      : '<div class="fm-small">سفارشی برای این فرد در این روز ثبت نشده است.</div>';
    return head + msgs
      + '<div class="fm-alert good">فرد: ' + esc(em.person.name) + ' (' + esc(em.person.username) + ') — کد ملی تأیید شد.</div>'
      + '<div class="fm-emergency-step"><label class="fm-field">غذا' + select + '</label>'
      + (items.length ? '<button type="button" class="fm-btn" data-em-action="order">' + (hasActive ? 'تغییر سفارش' : 'ثبت سفارش') + '</button>' : '')
      + '<button type="button" class="fm-btn secondary" data-em-action="reset">تغییر کاربر</button></div>'
      + '<h4>سفارش‌های این فرد در این روز</h4>' + orderList + '</div>';
  }
  async function emergencyLoadOrders() {
    var em = state.emergency;
    if (!em || !em.person) return;
    var payload = await request('food-menu/emergency/orders', { date: state.selectedDate, employee_id: em.person.id, meal_type: state.mealType });
    em.orders = payload.items || [];
  }
  function daySearchInput(event) {
    var reportEmployeeSearch = event.target.closest && event.target.closest('[data-stat-employee-search]');
    if (reportEmployeeSearch) {
      state.reportEmployeeQuery = String(reportEmployeeSearch.value || '');
      if (state.reportEmployeeSearchTimer) global.clearTimeout(state.reportEmployeeSearchTimer);
      state.reportEmployeeSearchTimer = global.setTimeout(function () { searchSelectedReportEmployees(state.reportEmployeeQuery); }, 300);
      return;
    }
    var bulkSearch = event.target.closest && event.target.closest('[data-bulk-search]');
    if (bulkSearch) { var cursor = bulkSearch.selectionStart || 0; state.bulk.search = String(bulkSearch.value || ''); render(); var nextSearch = root && root.querySelector('[data-bulk-search]'); if (nextSearch) { nextSearch.focus(); try { nextSearch.setSelectionRange(cursor, cursor); } catch (e) {} } return; }
    var guestSearch = event.target.closest && event.target.closest('[data-guest-request-search]');
    if (guestSearch) { var guestCursor = guestSearch.selectionStart || 0; state.guestRequestSearch = String(guestSearch.value || ''); render(); var nextGuestSearch = root && root.querySelector('[data-guest-request-search]'); if (nextGuestSearch) { nextGuestSearch.focus(); try { nextGuestSearch.setSelectionRange(guestCursor, guestCursor); } catch (e) {} } return; }
    var bulkReason = event.target.closest && event.target.closest('[data-bulk-reason]');
    if (bulkReason) {
      state.bulk.reason = String(bulkReason.value || '').trim();
      var preview = root && root.querySelector('[data-fm-action="bulk-preview"]');
      var snapshot = state.bulk.snapshot;
      var pastWindowAllowed = Boolean(snapshot && snapshot.is_past && snapshot.late_order_window_open && ['order', 'cancel'].includes(state.bulk.operation));
      var pastBlocked = Boolean(snapshot && snapshot.is_past && !pastWindowAllowed);
      if (preview) preview.disabled = !state.bulk.selectedIds.length || pastBlocked || (state.bulk.operation === 'cancel' && !state.bulk.reason);
      return;
    }
    var input = event.target.closest && event.target.closest('[data-fm-day-search]');
    if (!input) return;
    var q = String(input.value || '').trim().replace(/ي/g, 'ی').replace(/ك/g, 'ک').toLowerCase();
    var editor = input.closest('.fm-food-selection') || root;
    editor.querySelectorAll('.fm-cat-group').forEach(function (group) {
      var visible = 0;
      group.querySelectorAll('.fm-food-check').forEach(function (label) {
        var text = String(label.textContent || '').replace(/ي/g, 'ی').replace(/ك/g, 'ک').toLowerCase();
        var match = !q || text.indexOf(q) !== -1;
        label.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      group.style.display = visible || !q ? '' : 'none';
      if (q && visible) {
        group.classList.add('open');
        var items = group.querySelector('.fm-cat-items');
        if (items) items.hidden = false;
        var sign = group.querySelector('.fm-cat-sign');
        if (sign) sign.textContent = '-';
      }
    });
  }

  async function emergencyClick(event) {
    var button = event.target.closest('[data-em-action]');
    if (!button || !canEmergency) return;
    event.preventDefault();
    var em = state.emergency = state.emergency || {};
    var action = button.dataset.emAction;
    em.error = ''; em.notice = '';
    try {
      if (action === 'pick') {
        var picked = String((root.querySelector('[name="emergency_person"]') || {}).value || '');
        if (!picked) throw new Error('ابتدا یک کاربر را انتخاب کنید.');
        var person = (em.people || []).filter(function (p) { return Number(p.id) === Number(picked); })[0];
        em.pickedId = Number(picked);
        em.pickedName = person ? person.name : '';
        em.nc = ''; em.person = null; em.orders = [];
      } else if (action === 'verify') {
        var nc = String((root.querySelector('[name="emergency_nc"]') || {}).value || '').trim();
        em.nc = nc;
        var found = await request('food-menu/emergency/lookup', null, { method: 'POST', body: JSON.stringify({ employee_id: em.pickedId, national_code: nc }) });
        em.person = found.person;
        await emergencyLoadOrders();
      } else if (action === 'reset') {
        state.emergency = { people: em.people };
      } else if (action === 'order') {
        var select = root.querySelector('[name="emergency_item"]');
        var result = await request('food-menu/emergency/order', null, { method: 'POST', body: JSON.stringify({ food_date: state.selectedDate, employee_id: em.person.id, national_code: em.nc, calendar_item_id: Number(select && select.value), meal_type: state.mealType, emergency: true }) });
        em.notice = result.message || 'سفارش اضطراری ثبت شد.';
        await emergencyLoadOrders();
      } else if (action === 'cancel') {
        var cancelled = await request('food-menu/emergency/cancel', null, { method: 'POST', body: JSON.stringify({ order_id: Number(button.dataset.orderId), employee_id: em.person.id, national_code: em.nc }) });
        em.notice = cancelled.message || 'سفارش لغو شد.';
        await emergencyLoadOrders();
      }
      if (typeof refreshAll === 'function') refreshAll();
    } catch (error) {
      em.error = error.message || 'عملیات انجام نشد.';
    }
    render();
  }

  function eventClick(event) {
    var dayCat = event.target.closest('[data-fm-daycat]');
    if (dayCat) {
      var group = dayCat.parentNode;
      var body = group.querySelector('.fm-cat-items');
      var willOpen = body.hidden;
      body.hidden = !willOpen;
      group.classList.toggle('open', willOpen);
      dayCat.querySelector('.fm-cat-sign').textContent = willOpen ? '-' : '+';
      dayCat.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      dayCatalogOpen[dayCat.dataset.fmDaycat] = willOpen;
      return;
    }
    var catToggle = event.target.closest('[data-fm-cat]');
    if (catToggle) { var catName = catToggle.dataset.fmCat; catalogOpen[catName] = !catalogOpen[catName]; render(); return; }
    var tab = event.target.closest('[data-fm-tab]');
    if (tab) { switchTab(tab.dataset.fmTab); return; }
    var deliveredSortBtn = event.target.closest('[data-fm-delivery-sort]');
    if (deliveredSortBtn) {
      var deliveredSortKey = deliveredSortBtn.dataset.fmDeliverySort;
      if (state.deliveredReportSort.key === deliveredSortKey) state.deliveredReportSort.dir = state.deliveredReportSort.dir === 'asc' ? 'desc' : 'asc';
      else state.deliveredReportSort = { key: deliveredSortKey, dir: 'asc' };
      render();
      return;
    }
    var sortBtn = event.target.closest('[data-fm-sort]');
    if (sortBtn) {
      var sortKey = sortBtn.dataset.fmSort;
      state.ordersSortTouched = true;
      if (state.ordersSort.key === sortKey) state.ordersSort.dir = state.ordersSort.dir === 'asc' ? 'desc' : 'asc';
      else state.ordersSort = { key: sortKey, dir: 'asc' };
      render();
      return;
    }
    var action = event.target.closest('[data-fm-action]');
    if (action) {
      var act = action.dataset.fmAction;
      if (act === 'statistics-add-employee') {
        var employeeId = Number(action.dataset.employeeId || 0);
        var person = (state.reportEmployeeResults || []).filter(function (item) { return Number(item.id) === employeeId; })[0];
        if (person && employeeId > 0 && !(state.reportSelectedEmployees || []).some(function (item) { return Number(item.id) === employeeId; })) {
          if (state.reportSelectedEmployees.length >= 100) { notify('حداکثر ۱۰۰ پرسنل قابل انتخاب است.', true); return; }
          state.reportSelectedEmployees.push(person);
          state.reportEmployeeResults = state.reportEmployeeResults.filter(function (item) { return Number(item.id) !== employeeId; });
          if (state.statisticsFrom && state.statisticsTo && state.statisticsMode === 'selected') loadStatistics(state.statisticsFrom, state.statisticsTo, 'selected');
          else render();
        }
        return;
      }
      if (act === 'statistics-remove-employee') {
        var removeId = Number(action.dataset.employeeId || 0);
        state.reportSelectedEmployees = state.reportSelectedEmployees.filter(function (item) { return Number(item.id) !== removeId; });
        if (state.statisticsFrom && state.statisticsTo && state.statisticsMode === 'selected' && state.reportSelectedEmployees.length) loadStatistics(state.statisticsFrom, state.statisticsTo, 'selected');
        else { state.statisticsLoaded = false; state.statistics = []; render(); }
        return;
      }
      if (act === 'bulk-mode') {
        var requestedBulkMode = String(action.dataset.mode || '');
        if (requestedBulkMode === 'guest' && !canManageGuestRequests) return;
        if (['order', 'cancel', 'canteen'].includes(requestedBulkMode) && !canEmergency) return;
        var nextBulkMode = ['order', 'cancel', 'canteen', 'guest'].includes(requestedBulkMode) ? requestedBulkMode : 'canteen';
        state.bulk.operation = nextBulkMode;
        state.bulk.selectedIds = [];
        state.bulk.previewOpen = false;
        state.bulk.results = null;
        state.error = '';
        if (nextBulkMode !== 'guest') state.bulk.target = nextBulkMode === 'order' ? String((state.bulk.items[0] || {}).item_id || '') : String((state.bulk.groups[0] || {}).id || '');
        render();
        if (nextBulkMode === 'guest') loadGuestRequests();
        else if (!state.bulk.snapshot || !state.bulk.people.length) loadBulkSnapshot(state.bulk.date || state.selectedDate, state.bulk.mealType || state.mealType);
        return;
      }
      if (act === 'guest-reload') { loadGuestRequests(); return; }
      if (act === 'guest-edit') { beginGuestEdit(Number(action.dataset.id || 0)); return; }
      if (act === 'guest-edit-cancel') { cancelGuestEdit(); return; }
      if (act === 'guest-cancel') { guestRequestAction(Number(action.dataset.id || 0), 'cancel'); return; }
      if (act === 'guest-delete') { guestRequestAction(Number(action.dataset.id || 0), 'delete'); return; }
      if (act === 'bulk-select-all') { bulkSelectEligible(); return; }
      if (act === 'bulk-clear-selection') { state.bulk.selectedIds = []; render(); return; }
      if (act === 'bulk-preview') { var reasonInput = root.querySelector('[data-bulk-reason]'); if (reasonInput) state.bulk.reason = String(reasonInput.value || '').trim(); if (state.bulk.selectedIds.length > 100) { state.error = 'در هر نوبت حداکثر ۱۰۰ نفر قابل انتخاب است.'; render(); return; } if (state.bulk.operation === 'cancel' && !state.bulk.reason) { state.error = 'برای لغو گروهی دلیل را وارد کنید.'; render(); return; } state.error = ''; state.bulk.previewOpen = true; render(); return; }
      if (act === 'bulk-back' || act === 'bulk-backdrop') { if (act === 'bulk-backdrop' && event.target !== action) return; state.bulk.previewOpen = false; render(); return; }
      if (act === 'bulk-confirm') { submitBulkOperation(); return; }
      if (act === 'bulk-clear-results') { state.bulk.results = null; state.notice = ''; render(); return; }
      if (act === 'meal-type') {
        var nextMeal = action.dataset.mealType === 'dinner' ? 'dinner' : 'lunch';
        if (nextMeal !== state.mealType) { state.mealType = nextMeal; state.pendingImpact = null; state.dayPick = null; state.monthData = state.tab === 'calendar' ? null : state.monthData; state.ordersLoaded = false; state.statisticsLoaded = false; if (state.tab === 'calendar') loadMonth(); else if (state.tab === 'orders') { loadOrders(state.ordersDate || state.selectedDate); if (state.groupOrdersLoaded && state.groupOrdersGroupId) loadGroupOrders(state.groupOrdersGroupId, state.groupOrdersDate || state.ordersDate); } else if (state.tab === 'statistics' && state.statisticsFrom && state.statisticsTo) loadStatistics(state.statisticsFrom, state.statisticsTo, state.statisticsMode); else render(); }
      }
      if (act === 'month-prev') { var p = Cal.shiftMonth(state.year, state.month, -1); changeMonth(p[0], p[1]); }
      if (act === 'month-next') { var n = Cal.shiftMonth(state.year, state.month, 1); changeMonth(n[0], n[1]); }
      if (act === 'month-today') {
        if (state.status && state.status.today) { var td = jalaliMonthFromIso(state.status.today); if (td) changeMonth(td[0], td[1], state.status.today); }
        else { var today = new Date(); var j = global.FoodOrderCalendar.jalaliDateFromIso(localTodayIso()); changeMonth(j[0], j[1], Cal.isoDate(j[0], j[1], j[2])); }
      }
      if (act === 'reload-month') loadMonth();
      if (act === 'show-orders') {
        state.ordersDate = state.selectedDate;
        if (global.document) global.document.dispatchEvent(new Event('food-open-report'));
      }
      if (act === 'confirm-impact') saveDay(true, state.pendingImpactExpected);
      if (act === 'cancel-impact') { state.pendingImpact = null; state.pendingImpactExpected = 0; state.pendingImpactMessage = ''; render(); }
      if (act === 'orders-view') { state.ordersView = action.dataset.ordersView === 'menu' ? 'menu' : 'list'; state.ordersSortTouched = state.ordersSortTouched || state.ordersView === 'menu'; render(); }
      if (act === 'group-orders-view') { state.groupOrdersView = action.dataset.ordersView === 'menu' ? 'menu' : 'list'; render(); }
      if (act === 'orders-export') downloadOrders();
      if (act === 'orders-print') printOrders();
      if (act === 'group-orders-print') printScopedReport('.fm-group-orders .fm-group-report-content', 'fm-print-group-orders');
      if (act === 'statistics-export') downloadStatistics();
      if (act === 'statistics-print') printStatistics();
      if (act === 'delivery-today') {
        state.deliveredReportDate = (state.status && state.status.today) || localTodayIso();
        state.deliveredReportRange = '';
        loadPrintedDeliveryReport(state.deliveredReportDate, state.deliveredReportDate);
      }
      if (act === 'delivered-report-view') { state.deliveredReportView = action.dataset.deliveryView === 'menu' ? 'menu' : 'list'; render(); }
      if (act === 'delivered-report-export') exportPrintedDeliveryReport();
      if (act === 'delivered-report-print') printPrintedDeliveryReport();
      if (act === 'cancel-req-reload') loadCancelRequests();
      if (act === 'cancel-req-approve') decideCancelRequest(Number(action.dataset.id || 0), 'approve');
      if (act === 'cancel-req-reject') decideCancelRequest(Number(action.dataset.id || 0), 'reject');
      if (act === 'orders-today') {
        var todayIso = state.status && state.status.today ? state.status.today : localTodayIso();
        state.selectedDate = todayIso; state.ordersDate = todayIso;
        loadOrders(todayIso);
      }
      return;
    }
    var day = event.target.closest('[data-fm-day]');
    if (day) selectDay(day.dataset.fmDay, day.dataset.fmCurrent === '1');
  }

  // ترتیب منو = ترتیب تیک زدن غذاها در روز. اولین غذای تیک‌خورده منوی ۱ است، و همین ترتیب ذخیره می‌شود.
  function dayPickFor(iso) {
    if (!state.dayPick || state.dayPick.date !== iso) {
      var day = getDay(iso);
      var saved = day && Array.isArray(day.items) ? day.items.map(function (item) { return Number(item.food_id); }) : [];
      state.dayPick = { date: iso, ids: saved };
    }
    return state.dayPick;
  }

  function trackDayPick(input) {
    var pick = dayPickFor(state.selectedDate);
    var id = Number(input.value);
    pick.ids = pick.ids.filter(function (x) { return x !== id; });
    if (input.checked) pick.ids.push(id);
  }

  function orderedDayFoodIds(form, iso) {
    var checked = Array.from(form.querySelectorAll('input[name="food_ids"]:checked')).map(function (input) { return Number(input.value); });
    var ordered = [];
    dayPickFor(iso).ids.forEach(function (id) { if (checked.indexOf(id) >= 0 && ordered.indexOf(id) < 0) ordered.push(id); });
    checked.forEach(function (id) { if (ordered.indexOf(id) < 0) ordered.push(id); });
    return ordered;
  }

  function eventChange(event) {
    var input = event.target;
    if (input && input.matches && input.matches('.fm-bulk-check')) { bulkTogglePerson(Number(input.dataset.bulkPerson || 0), input.checked); return; }
    if (input && input.matches && input.matches('[data-bulk-target]')) { state.bulk.target = String(input.value || ''); state.bulk.selectedIds = (state.bulk.people || []).filter(function (person) { return state.bulk.selectedIds.indexOf(Number(person.id)) >= 0 && bulkCanSelect(person); }).map(function (person) { return Number(person.id); }); render(); return; }
    if (input && input.matches && input.matches('[data-bulk-reason]')) { state.bulk.reason = String(input.value || '').trim(); return; }
    if (input && input.matches && input.matches('[data-fm-delivery-field]')) {
      var deliveredFields = Array.from(root.querySelectorAll('[data-fm-delivery-field]:checked')).map(function (item) { return item.dataset.fmDeliveryField; });
      if (!deliveredFields.length) { input.checked = true; notify('برای گزارش حداقل یک فیلد باید انتخاب شود.', true); return; }
      state.deliveredReportFields = deliveredFields;
      saveDeliveredReportFields(deliveredFields);
      render();
      return;
    }
    if (input && input.matches && input.matches('[data-fm-order-field]')) {
      var fields = Array.from(root.querySelectorAll('[data-fm-order-field]:checked')).map(function (item) { return item.dataset.fmOrderField; });
      if (!fields.length) { input.checked = true; notify('برای گزارش حداقل یک فیلد باید انتخاب شود.', true); return; }
      state.orderReportFields = fields;
      saveOrderReportFields(fields);
      render();
      return;
    }
    if (input && input.name === 'food_ids') { trackDayPick(input); return; }
    if (input && input.matches && input.matches('[data-stat-employee-checkbox]')) {
      var employeeId = Number(input.dataset.employeeId || 0);
      var person = (state.reportEmployeeResults || []).find(function (item) { return Number(item.id) === employeeId; });
      var selectedPeople = state.reportSelectedEmployees || [];
      var selectedIndex = selectedPeople.findIndex(function (item) { return Number(item.id) === employeeId; });
      if (input.checked && selectedIndex < 0) {
        if (selectedPeople.length >= 100) { input.checked = false; notify('حداکثر ۱۰۰ پرسنل قابل انتخاب است.', true); return; }
        if (person) selectedPeople.push(person);
      } else if (!input.checked && selectedIndex >= 0) selectedPeople.splice(selectedIndex, 1);
      state.reportSelectedEmployees = selectedPeople;
      state.statisticsLoaded = false; state.statistics = [];
      render();
      return;
    }
    if (!input || input.name !== 'statistics_mode') return;
    var selectedMode = ['employee', 'selected'].includes(input.value) ? input.value : 'food';
    var form = input.form;
    var formData = form ? new FormData(form) : null;
    var fromDate = formData ? normalizeIsoDate(formData.get('from')) : state.statisticsFrom;
    var toDate = formData ? normalizeIsoDate(formData.get('to')) : state.statisticsTo;
    state.statisticsMode = selectedMode;
    if (fromDate) state.statisticsFrom = fromDate;
    if (toDate) state.statisticsTo = toDate;
    state.error = '';
    if (fromDate && toDate && fromDate <= toDate && (selectedMode !== 'selected' || (state.reportSelectedEmployees || []).length)) {
      loadStatistics(fromDate, toDate, selectedMode);
    } else {
      state.statisticsRequestId++;
      state.statistics = [];
      state.statisticsLoaded = false;
      state.loadingStatistics = false;
      render();
    }
    if (selectedMode === 'selected' && !state.reportEmployeeResultsLoaded && !state.reportEmployeeSearchLoading) searchSelectedReportEmployees('');
  }

  function eventSubmit(event) {
    var form = event.target;
    if (form.id === 'fm-guest-edit-form') {
      event.preventDefault();
      saveGuestEdit(form);
      return;
    }
    if (form.id === 'fm-bulk-date-form') {
      event.preventDefault();
      var bulkData = new FormData(form);
      var bulkDate = normalizeIsoDate(bulkData.get('date'));
      var bulkMeal = String(bulkData.get('meal_type') || 'lunch') === 'dinner' ? 'dinner' : 'lunch';
      if (!bulkDate) { state.error = 'تاریخ غذا را از تقویم شمسی به‌درستی انتخاب کنید.'; render(); return; }
      if (state.bulk.operation === 'cancel') state.bulk.reason = String(bulkData.get('reason') || '').trim();
      else state.bulk.target = String(bulkData.get('target') || state.bulk.target || '');
      loadBulkSnapshot(bulkDate, bulkMeal); return;
    }
    if (form.id === 'fm-day-form') {
      event.preventDefault(); saveDay(false); return;
    }
    if (form.id === 'fm-catalog-add') {
      event.preventDefault(); saveCatalog(form); return;
    }
    if (form.matches('[data-fm-catalog-edit]')) {
      event.preventDefault(); saveCatalog(form); return;
    }
    if (form.id === 'fm-delivery-date') {
      event.preventDefault();
      var picked = normalizeIsoDate(new FormData(form).get('date'));
      if (!picked) { state.error = 'تاریخ معتبری انتخاب کنید.'; render(); return; }
      state.deliveredReportDate = picked;
      state.deliveredReportRange = '';
      state.error = '';
      loadPrintedDeliveryReport(picked, picked);
      return;
    }
    if (form.id === 'fm-orders-date') {
      event.preventDefault();
      var orderDate = normalizeIsoDate(new FormData(form).get('date'));
      if (!orderDate) { state.error = 'تاریخ روز را به‌درستی انتخاب کنید.'; render(); return; }
      state.error = ''; state.selectedDate = orderDate; state.ordersDate = orderDate; loadOrders(orderDate); return;
    }
    if (form.id === 'fm-group-orders-form') {
      event.preventDefault();
      var groupForm = new FormData(form);
      var groupDate = normalizeIsoDate(groupForm.get('date'));
      var groupId = String(groupForm.get('group_id') || '');
      if (!groupDate || !groupId) { state.error = 'گروه غذا و تاریخ روز را به‌درستی انتخاب کنید.'; render(); return; }
      state.error = '';
      loadGroupOrders(groupId, groupDate);
      return;
    }
    if (form.id === 'fm-statistics-date') {
      event.preventDefault();
      var statisticsForm = new FormData(form);
      var fromDate = normalizeIsoDate(statisticsForm.get('from'));
      var toDate = normalizeIsoDate(statisticsForm.get('to'));
      var statisticsMode = String(statisticsForm.get('statistics_mode') || state.statisticsMode || 'food');
      if (!['food', 'employee', 'selected'].includes(statisticsMode)) { state.error = 'نوع گزارش را انتخاب کنید.'; render(); return; }
      if (statisticsMode === 'selected' && !(state.reportSelectedEmployees || []).length) { state.error = 'برای گزارش، دست‌کم یک پرسنل را از فهرست انتخاب کنید.'; render(); return; }
      if (!fromDate || !toDate) { state.error = 'تاریخ شروع و پایان را به‌درستی انتخاب کنید.'; render(); return; }
      if (fromDate > toDate) { state.error = 'تاریخ پایان باید برابر یا پس از تاریخ شروع باشد.'; render(); return; }
      var spanDays = (Date.parse(toDate + 'T00:00:00Z') - Date.parse(fromDate + 'T00:00:00Z')) / 86400000;
      if (spanDays > 366) { state.error = 'بازهٔ آمار حداکثر یک سال است.'; render(); return; }
      state.error = ''; state.statisticsFrom = fromDate; state.statisticsTo = toDate; state.statisticsMode = statisticsMode; loadStatistics(fromDate, toDate, statisticsMode); return;
    }
  }

  function mount() {
    root = document.getElementById('food-menu-root');
    if (!root) return;
    state.embeddedMode = String(root.dataset.fmMode || '');
    if (root.dataset.fmMounted === '1') {
      // تب عوض شده؛ دیتای پنل جدید را لود کن، ولی لیسنرها را دوباره وصل نکن.
      if (state.embeddedMode) loadEmbedded();
      render();
      return;
    }
    root.dataset.fmMounted = '1';
    root.addEventListener('click', emergencyClick);
    root.addEventListener('click', eventClick);
    root.addEventListener('change', eventChange);
    root.addEventListener('input', daySearchInput);
    root.addEventListener('submit', eventSubmit);
    var current = state.selectedDate ? jalaliMonthFromIso(state.selectedDate) : null;
    if (current) { state.year = current[0]; state.month = current[1]; }
    render();
    refreshAll();
  }

  global.FoodMenuUI = {
    html: function (mode) {
      var attr = mode ? ' data-fm-mode="' + String(mode).replace(/[^a-z]/g, '') + '"' : '';
      return '<div id="food-menu-root"' + attr + '><div class="fm-card fm-spinner">در حال آمادهسازی…</div></div>';
    },
    mount: mount,
  };
  if (typeof document !== 'undefined') document.dispatchEvent(new Event('food-menu-ready'));
  if (typeof module !== 'undefined' && module.exports) module.exports = global.FoodMenuUI;
})(typeof window !== 'undefined' ? window : globalThis);
