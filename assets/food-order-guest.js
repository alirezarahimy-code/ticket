/* بخش «سفارش غذا برای مهمان» (فقط مدیران و بالاتر): ثبت و لغو درخواست برای روزهای آینده. */
(function () {
  'use strict';

  var root = document.getElementById('food-guest-app');
  if (!root) return;

  var apiBase = (root.dataset.api || '').replace(/&amp;/g, '&');
  var csrf = root.dataset.csrf || '';
  var requesterDefault = root.dataset.selfName || '';
  var state = { items: [], foods: [], mode: 'fixed', loaded: false, error: '', saving: false, formError: '' };

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
  }
  function faDigits(value) { return String(value).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[Number(d)]; }); }
  function normDigits(value) {
    return String(value == null ? '' : value).replace(/[۰-۹]/g, function (d) { return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)); })
      .replace(/[٠-٩]/g, function (d) { return String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)); });
  }

  async function request(route, params, options) {
    var query = params ? new URLSearchParams(params).toString() : '';
    var url = apiBase + encodeURIComponent(route) + (query ? '&' + query : '');
    var opts = Object.assign({ method: 'GET', credentials: 'same-origin', headers: {} }, options || {});
    opts.headers = Object.assign({ 'Content-Type': 'application/json' }, opts.headers || {});
    if (opts.method !== 'GET' && csrf) opts.headers['X-CSRF-Token'] = csrf;
    var response = await fetch(url, opts);
    var text = await response.text();
    var payload;
    try { payload = text ? JSON.parse(text) : {}; } catch (e) { throw new Error('پاسخ معتبر از سرور دریافت نشد (کد ' + response.status + ').'); }
    if (!response.ok) throw new Error(payload.error || ('خطای سرویس ' + response.status));
    return payload || {};
  }

  function toast(message, bad) {
    var node = document.createElement('div');
    node.className = 'alert ' + (bad ? 'danger' : 'success');
    node.setAttribute('role', bad ? 'alert' : 'status');
    node.textContent = message;
    node.style.margin = '8px 0';
    root.insertBefore(node, root.firstChild);
    window.setTimeout(function () { node.remove(); }, 4800);
  }

  function render() {
    var foodOptions = '<option value="">بدون انتخاب نوع غذا</option>' + state.foods.map(function (f) {
      return '<option value="' + f.id + '">' + esc(f.name) + '</option>';
    }).join('');
    var modeNote = state.mode === 'requested'
      ? 'سقف مهمان‌های این روزها اکنون بر اساس مجموع درخواست‌های فعال همین بخش تعیین می‌شود.'
      : 'سقف مهمان‌های این روزها طبق تنظیم فعلی سامانه است؛ درخواست‌ها در سقف مهمان‌ها به حساب می‌آیند.';
    var rows = state.items.map(function (r) {
      var action = r.editable && r.status === 'active'
        ? '<button type="button" class="button secondary" data-fg-action="cancel" data-id="' + r.id + '">لغو</button>'
        : '<span class="muted">—</span>';
      var status = r.status === 'cancelled' ? '<span class="muted">لغو شده</span>' : 'فعال';
      return '<tr>'
        + '<td>' + esc(faDigits(r.request_jalali)) + '</td>'
        + '<td>' + esc(r.organization) + '</td>'
        + '<td>' + esc(faDigits(r.guest_count)) + '</td>'
        + '<td>' + esc(r.food_name || '—') + '</td>'
        + '<td>' + esc(r.requester_name) + '</td>'
        + '<td>' + esc(r.created_by_name) + '</td>'
        + '<td>' + status + '</td>'
        + '<td>' + action + '</td></tr>';
    }).join('');
    var table = state.items.length
      ? '<div class="table-wrap"><table class="ticket-table" style="width:100%;border-collapse:collapse"><thead><tr>'
        + '<th>تاریخ</th><th>سازمان / شرکت</th><th>تعداد</th><th>نوع غذا</th><th>درخواست‌دهنده</th><th>ثبت‌کننده</th><th>وضعیت</th><th></th>'
        + '</tr></thead><tbody>' + rows + '</tbody></table></div>'
      : '<p class="muted">درخواست فعالی برای روزهای آینده ثبت نشده است.</p>';
    root.innerHTML = ''
      + '<div class="card fo-guest-card" style="margin-top:16px">'
      + '<h2 style="margin-top:0">سفارش غذا برای مهمان</h2>'
      + '<p class="muted">برای مهمانان سازمان، برای هر روز آینده سفارش ثبت کنید. ' + esc(modeNote) + '</p>'
      + (state.formError ? '<div class="alert danger" role="alert">' + esc(state.formError) + '</div>' : '')
      + '<form id="fg-form" class="form-grid" novalidate>'
      + '<label>تاریخ مورد نیاز (شمسی)<input type="text" inputmode="numeric" autocomplete="off" data-jalali name="request_date" required></label>'
      + '<label>سازمان / شرکت<input type="text" name="organization" maxlength="190" required></label>'
      + '<label>تعداد مهمان<input type="text" inputmode="numeric" name="guest_count" maxlength="3" required></label>'
      + '<label>نوع غذا (از فهرست غذاها)<select name="food_id">' + foodOptions + '</select></label>'
      + '<label>نام درخواست‌دهنده<input type="text" name="requester_name" maxlength="150" value="' + esc(requesterDefault) + '" required></label>'
      + '<label>یادداشت (اختیاری)<input type="text" name="note" maxlength="500"></label>'
      + '<div><button type="submit" class="button primary" data-fg-action="save"' + (state.saving ? ' disabled' : '') + '>ثبت درخواست مهمان</button></div>'
      + '</form>'
      + '<h3 style="margin-top:20px">درخواست‌های مهمان (از امروز به بعد)</h3>'
      + table
      + '</div>';
    if (window.ItsmJalali && typeof window.ItsmJalali.enhanceAll === 'function') window.ItsmJalali.enhanceAll(root);
  }

  async function load() {
    try {
      var data = await request('guest-requests');
      state.items = data.items || [];
      state.foods = data.foods || [];
      state.mode = data.mode || 'fixed';
      state.loaded = true;
      state.error = '';
    } catch (e) {
      state.error = e.message;
    }
    if (state.error) {
      root.innerHTML = '<div class="alert danger" role="alert">' + esc(state.error) + '</div>';
      return;
    }
    render();
  }

  async function save(form) {
    if (state.saving) return;
    var fd = new FormData(form);
    var body = {
      request_date: normDigits(fd.get('request_date') || '').trim(),
      organization: String(fd.get('organization') || '').trim(),
      guest_count: normDigits(fd.get('guest_count') || '').trim(),
      food_id: Number(fd.get('food_id') || 0),
      requester_name: String(fd.get('requester_name') || '').trim(),
      note: String(fd.get('note') || '').trim()
    };
    state.formError = '';
    if (!body.request_date) { state.formError = 'تاریخ را وارد کنید.'; return render(); }
    if (!body.organization) { state.formError = 'نام سازمان یا شرکت را وارد کنید.'; return render(); }
    if (!/^\d{1,3}$/.test(body.guest_count) || Number(body.guest_count) < 1 || Number(body.guest_count) > 500) {
      state.formError = 'تعداد مهمان باید عددی بین ۱ تا ۵۰۰ باشد.'; return render();
    }
    if (!body.requester_name) { state.formError = 'نام درخواست‌دهنده را وارد کنید.'; return render(); }
    state.saving = true;
    render();
    try {
      var res = await request('guest-request', null, { method: 'POST', body: JSON.stringify(body) });
      state.saving = false;
      toast(res.message || 'درخواست ثبت شد.', false);
      await load();
    } catch (e) {
      state.saving = false;
      state.formError = e.message;
      render();
    }
  }

  async function cancel(id) {
    if (!window.confirm('این درخواست غذای مهمان لغو شود؟')) return;
    try {
      var res = await request('guest-request-cancel', null, { method: 'POST', body: JSON.stringify({ id: id }) });
      toast(res.message || 'لغو شد.', false);
      await load();
    } catch (e) {
      toast(e.message, true);
    }
  }

  root.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || form.id !== 'fg-form') return;
    event.preventDefault();
    save(form);
  });
  root.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-fg-action="cancel"]');
    if (btn) cancel(Number(btn.dataset.id || 0));
  });

  load();
})();
