/**
 * طراح قالب فیش غذا (Designer واقعی)
 * - همهٔ اندازه‌ها «میلی‌متر» هستند و همان عددها در دیتابیس (food_ticket_templates.template_json) ذخیره می‌شوند.
 * - بوم طراحی با مقیاس px/mm رسم می‌شود؛ فونت‌ها بر حسب pt همان اندازهٔ واقعی چاپ را دارند.
 * - «پیش‌نمایش واقعی چاپ» تصویری است که خود سرور با همان رندرر چاپ شبکه می‌سازد (۸ نقطه بر میلی‌متر).
 * - هر قالب هم در چاپ شبکه (ESC/POS) و هم در صف ویندوز (GDI+) با همین مختصات چاپ می‌شود.
 */
(function () {
  'use strict';
  if (typeof window === 'undefined') return;

  const MAX_TEMPLATES = 4;
  const S = {
    loaded: false, loading: false, error: '', busy: false,
    templates: [], catalog: [], fonts: ['Tahoma'], brand: {}, net: {},
    groupPrintMode: 'group', previewKind: 'individual', designTab: 'individual',
    groupTemplate: null, individualWork: null, groupWork: null,
    individualDirty: false, groupDirty: false,
    currentId: null,          // شناسهٔ قالب در حال ویرایش (0 = قالب جدید ذخیره‌نشده)
    work: null,               // نسخهٔ فعلی؛ با تب‌ها بین طراحی انفرادی و گروهی عوض می‌شود
    dirty: false,
    sel: '',                  // شناسهٔ المان انتخاب‌شده
    scale: 4,                 // px per mm
    preview: null, previewBusy: false, previewSeq: 0, previewTimer: null,
    drag: null,
  };

  const esc = (x) => String(x == null ? '' : x).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  const say = (m) => { try { if (typeof toast === 'function') toast(m); } catch (e) { /* ignore */ } };
  const callApi = (path, opts) => (typeof api === 'function' ? api(path, opts) : Promise.reject(new Error('اتصال به سرور در دسترس نیست.')));
  const root = () => document.getElementById('food-designer-root');
  const clone = (o) => JSON.parse(JSON.stringify(o));
  const r1 = (n) => Math.round(Number(n) * 10) / 10;
  const num = (v, d) => { const n = Number(v); return Number.isFinite(n) ? n : d; };
  const faDigits = (s) => String(s).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);

  const FIELD_BY_KEY = () => Object.fromEntries(S.catalog.map((c) => [c.key, c]));
  const MULTI = ['free_text', 'line', 'title'];

  // ───────────────────────── قالب‌های آماده ─────────────────────────
  function el(field, x, y, w, h, pt, bold, align, extra) {
    return Object.assign({
      id: field + '_' + Math.random().toString(36).slice(2, 6), field, enabled: true, x, y, width: w, height: h,
      font_family: 'Tahoma', font_size: pt, bold: !!bold, align: align || 'right', line_height: 1.2,
      show_label: false, label: '', text: '', border: false,
    }, extra || {});
  }
  const PRESETS = {
    small50: {
      label: 'فیش کوچک ۵۰×۵۰', w: 50, h: 50,
      build: () => ({
        cut_paper: true, border: true, border_inset: 2, logo_source: 'system', name_source: 'system', custom_name: '', margin_bottom: 2,
        elements: [
          el('logo', 'logo', 17, 2.2, 16, 9, 8, false, 'center'),
          el('system_name', 'system_name', 3, 11.6, 44, 4, 7, true, 'center'),
          el('line', 'line', 3, 16.4, 44, 0.5, 8, false, 'center'),
          el('food_name', 'food_name', 3, 18, 44, 8, 11, true, 'center', { border: true }),
          el('employee_name', 'employee_name', 3, 28, 44, 5, 9, true, 'center'),
          el('date', 'date', 3, 34.5, 44, 4, 7, false, 'right', { show_label: true, label: 'تاریخ' }),
          el('punch_time', 'punch_time', 3, 39.5, 44, 4, 7, false, 'right', { show_label: true, label: 'ساعت' }),
          el('national_code', 'national_code', 3, 44, 44, 4, 7, false, 'right', { enabled: false, show_label: true, label: 'کد ملی' }),
        ],
      }),
    },
    office80: {
      label: 'فیش اداری ۸۰ میلی‌متر', w: 80, h: 0,
      build: () => ({
        cut_paper: true, border: true, border_inset: 3, logo_source: 'system', name_source: 'system', custom_name: '', margin_bottom: 4,
        elements: [
          el('logo', 'logo', 28, 5, 24, 14, 8, false, 'center', { enabled: false }),
          el('system_name', 'system_name', 6, 6, 68, 8, 15, true, 'center'),
          el('title', 'title', 6, 14.5, 68, 6, 11, false, 'center', { text: 'فیش غذای پرسنل' }),
          el('line', 'line', 6, 22, 68, 0.5, 8, false, 'center'),
          el('food_name', 'food_name', 6, 25, 68, 11, 17, true, 'center', { border: true }),
          el('employee_name', 'employee_name', 6, 39, 68, 6, 11, false, 'right', { show_label: true, label: 'نام و نام خانوادگی' }),
          el('personnel_code', 'personnel_code', 6, 46, 68, 6, 11, false, 'right', { show_label: true, label: 'کد پرسنلی' }),
          el('national_code', 'national_code', 6, 53, 68, 6, 11, false, 'right', { show_label: true, label: 'کد ملی' }),
          el('department', 'department', 6, 60, 68, 6, 11, false, 'right', { show_label: true, label: 'واحد' }),
          el('date', 'date', 6, 67, 68, 6, 11, false, 'right', { show_label: true, label: 'تاریخ' }),
          el('punch_time', 'punch_time', 6, 74, 68, 6, 11, false, 'right', { show_label: true, label: 'ساعت تردد' }),
          el('free_text', 'free_text', 6, 83, 68, 6, 10, false, 'center', { text: 'نوش جان' }),
        ],
      }),
    },
    bigLogo80: {
      label: 'فیش با لوگوی بزرگ ۸۰ میلی‌متر', w: 80, h: 0,
      build: () => ({
        cut_paper: true, border: true, border_inset: 3, logo_source: 'system', name_source: 'system', custom_name: '', margin_bottom: 4,
        elements: [
          el('logo', 'logo', 15, 5, 50, 28, 8, false, 'center'),
          el('system_name', 'system_name', 6, 35, 68, 8, 14, true, 'center'),
          el('line', 'line', 6, 44.5, 68, 0.5, 8, false, 'center'),
          el('food_name', 'food_name', 6, 47.5, 68, 11, 17, true, 'center', { border: true }),
          el('employee_name', 'employee_name', 6, 61.5, 68, 6, 12, true, 'center'),
          el('date', 'date', 6, 69, 68, 5, 10, false, 'center'),
          el('punch_time', 'punch_time', 6, 75, 68, 5, 10, false, 'center'),
        ],
      }),
    },
    simple80: {
      label: 'قالب ساده ۸۰ میلی‌متر', w: 80, h: 0,
      build: () => ({
        cut_paper: true, border: false, border_inset: 3, logo_source: 'system', name_source: 'system', custom_name: '', margin_bottom: 4,
        elements: [
          el('food_name', 'food_name', 6, 4, 68, 10, 16, true, 'center'),
          el('employee_name', 'employee_name', 6, 16, 68, 6, 12, false, 'center'),
          el('date', 'date', 6, 23, 68, 5, 10, false, 'center'),
          el('punch_time', 'punch_time', 6, 29, 68, 5, 10, false, 'center'),
        ],
      }),
    },
    blank: { label: 'قالب خالی ۸۰ میلی‌متر', w: 80, h: 0, build: () => ({ cut_paper: true, border: false, border_inset: 3, logo_source: 'system', name_source: 'system', custom_name: '', margin_bottom: 4, elements: [] }) },
  };

  // ───────────────────────── دسترسی به داده ─────────────────────────
  function current() { return S.work; }
  function selected() { return S.work ? S.work.template.elements.find((e) => e.id === S.sel) : null; }
  function paperH() {
    const w = S.work;
    if (!w) return 50;
    if (Number(w.paper_height) > 0) return Number(w.paper_height);
    let b = 0;
    w.template.elements.forEach((e) => { if (e.enabled) b = Math.max(b, e.y + e.height); });
    return Math.max(20, r1(b + num(w.template.margin_bottom, 3)));
  }
  function workFrom(row) {
    if (!row) return null;
    return clone({ id: row.id || 0, name: row.name || '', paper_width: row.paper_width, paper_height: row.paper_height, template: row.template });
  }
  function setWork(row) {
    S.work = workFrom(row);
    if (!S.work) return;
    S.currentId = S.work.id;
    S.dirty = S.designTab === 'group' ? S.groupDirty : S.individualDirty;
    S.sel = (S.work.template.elements.find((e) => e.enabled) || S.work.template.elements[0] || {}).id || '';
    S.preview = null;
    if (S.designTab === 'group') S.groupWork = workFrom(S.work);
    else S.individualWork = workFrom(S.work);
  }
  function activeRow() { return S.templates.find((t) => t.is_active) || null; }

  // ───────────────────────── بارگذاری ─────────────────────────
  function applyList(x, keepSelection) {
    S.templates = x.templates || [];
    S.catalog = x.catalog || S.catalog;
    S.fonts = x.fonts || S.fonts;
    S.brand = x.brand || S.brand;
    S.net = x.net || S.net;
    S.groupPrintMode = x.group_print_mode === 'individual' ? 'individual' : 'group';
    const individual = activeRow() || S.templates[0];
    if (individual && (!S.individualWork || !keepSelection)) S.individualWork = workFrom(individual);
    if (x.group_template) S.groupTemplate = x.group_template;
    if (S.groupTemplate && (!S.groupWork || !keepSelection)) S.groupWork = workFrom(S.groupTemplate);
    if (!S.groupWork && individual) {
      S.groupWork = workFrom(individual);
      S.groupWork.id = 0;
      S.groupWork.name = 'قالب گروهی مستقل';
    }
    if (!keepSelection || !S.work) {
      const next = S.designTab === 'group' ? (S.groupWork || S.groupTemplate) : S.individualWork;
      if (next) setWork(next);
    }
  }
  function load() {
    if (S.loading) return;
    S.loading = true;
    callApi('/api/templates').then((x) => {
      applyList(x, false);
      S.loaded = true;
      S.error = '';
    }).catch((e) => { S.error = (e && e.message) || 'خطا در دریافت قالب‌ها'; })
      .then(() => { S.loading = false; draw(); schedulePreview(0); });
  }

  // ───────────────────────── رسم رابط ─────────────────────────
  function html() {
    return '<section id="food-designer-root" class="fd-root">' + body() + '</section>';
  }
  function mount() {
    const r = root();
    if (!r) return;
    if (!S.loaded && !S.loading) { r.innerHTML = body(); load(); return; }
    r.innerHTML = body();
    if (S.loaded && !S.preview && !S.previewBusy) schedulePreview(0);
  }
  function draw() {
    const r = root();
    if (r) r.innerHTML = body();
  }

  function sampleText(e) {
    const cat = FIELD_BY_KEY()[e.field] || {};
    let v;
    if (e.field === 'system_name') {
      v = S.work.template.name_source === 'custom' && S.work.template.custom_name ? S.work.template.custom_name : (S.brand.system_name || cat.sample);
    } else if (e.field === 'title' || e.field === 'free_text') {
      v = e.text;
    } else {
      v = cat.sample || '';
      if (e.show_label && e.label) v = e.label + ': ' + v;
    }
    return v;
  }
  function logoUrl() {
    const t = S.work.template;
    if (t.logo_source === 'ticket') return S.brand.has_ticket_logo ? S.brand.ticket_logo_url : '';
    return S.brand.has_system_logo ? S.brand.system_logo_url : '';
  }
  function elementHtml(e) {
    const sc = S.scale;
    const pxPt = (num(e.font_size, 11) * 25.4 / 72) * sc;
    const common = 'left:' + e.x * sc + 'px;top:' + e.y * sc + 'px;width:' + e.width * sc + 'px;height:' + e.height * sc + 'px;';
    const selCls = e.id === S.sel ? ' fd-sel' : '';
    const offCls = e.enabled ? '' : ' fd-off';
    let inner = '';
    let style = common;
    if (e.field === 'line') {
      inner = '<div class="fd-line"></div>';
      style = 'left:' + e.x * sc + 'px;top:' + (e.y * sc - 3) + 'px;width:' + e.width * sc + 'px;height:7px;';
    } else if (e.field === 'logo') {
      const u = logoUrl();
      inner = u ? '<img src="' + esc(u) + '" alt="" draggable="false">' : '<span class="fd-ph">لوگو (تعریف نشده)</span>';
    } else if (e.field === 'qrcode') {
      const side = Math.min(e.width, e.height) * sc;
      inner = '<div class="fd-qr" style="width:' + side + 'px;height:' + side + 'px">QR</div>';
    } else {
      const ta = e.align === 'center' ? 'center' : (e.align === 'left' ? 'left' : 'right');
      inner = '<div class="fd-text" style="font-family:\'' + esc(e.font_family) + '\',Tahoma,sans-serif;font-size:' + pxPt + 'px;font-weight:' + (e.bold ? 700 : 400) + ';line-height:' + (pxPt * num(e.line_height, 1.2)) + 'px;text-align:' + ta + ';' + (e.border ? 'outline:1px solid #000;' : '') + '">' + esc(sampleText(e)).replace(/\n/g, '<br>') + '</div>';
    }
    const imgAlign = (e.field === 'logo' || e.field === 'qrcode') ? 'display:flex;align-items:center;justify-content:' + (e.align === 'left' ? 'flex-start' : e.align === 'right' ? 'flex-end' : 'center') + ';' : '';
    return '<div class="fd-el' + selCls + offCls + '" data-fd-el="' + esc(e.id) + '" style="' + style + imgAlign + '">' + inner + (e.id === S.sel && e.field !== 'line' ? '<span class="fd-h" data-fd-resize="1"></span>' : '') + '</div>';
  }

  function groupPreviewLines() {
    const elements = S.work.template.elements.filter((e) => e.enabled);
    const hasGroupTitle = elements.some((e) => e.field === 'group_title');
    const hasDate = elements.some((e) => ['date', 'food_date'].includes(e.field));
    const hasTime = elements.some((e) => ['punch_time', 'attendance_time'].includes(e.field));
    const lines = [];
    if (!hasGroupTitle) lines.push({ kind: 'meta', text: 'گروه: گروه نمونهٔ کارکنان' });
    if (!hasDate) lines.push({ kind: 'meta', text: 'تاریخ: ۱۴۰۵/۰۷/۱۳' });
    if (!hasTime) lines.push({ kind: 'meta', text: 'ساعت: ۱۲:۴۱' });
    if (lines.length) lines.push({ kind: 'meta', text: '------------------------------' });
    const sampleRows = [
      ['منو۱', 'چلوکباب', ['علی رضایی', 'سارا محمدی', 'رضا کریمی', 'مریم احمدی', 'حسین رضایی', 'نرگس موسوی']],
      ['منو۲', 'زرشک‌پلو با مرغ', ['فاطمه حسینی', 'محمد کاظمی', 'ناهید جعفری', 'امیر رحیمی', 'لیلا صادقی', 'پیمان مرادی']],
      ['منو۳', 'قیمه', ['آزاده کریمی', 'جواد احمدی', 'مهدی نوری', 'الهام رستمی', 'فرهاد یوسفی', 'سمانه حیدری']],
    ];
    sampleRows.forEach(([label, food, names]) => {
      lines.push({ kind: 'menu', text: label + ' — ' + food + ' — ' + names.length + ' عدد' });
      names.forEach((name) => lines.push({ kind: 'member', text: 'نام و نام خانوادگی: ' + name }));
    });
    return lines;
  }

  function groupTextHeight(text, style, width) {
    const pt = num(style && style.font_size, 11);
    const lineHeight = pt * 25.4 / 72 * num(style && style.line_height, 1.2);
    const perLine = Math.max(1, Math.floor(Math.max(1, width) / Math.max(0.5, pt * 25.4 / 72 * 0.52)));
    let count = 0;
    String(text).split('\n').forEach((line) => { count += Math.max(1, Math.ceil(Array.from(line).length / perLine)); });
    return Math.max(num(style && style.height, 0.5), count * lineHeight);
  }

  function groupCanvasHtml() {
    const w = S.work, t = w.template, sc = S.scale, paperW = Number(w.paper_width) || 80;
    const ignored = ['food_name', 'employee_name', 'personnel_code', 'national_code', 'department', 'meal', 'ticket_number', 'qrcode', 'free_text'];
    const staticElements = t.elements.filter((e) => e.enabled && !ignored.includes(e.field));
    const footers = t.elements.filter((e) => e.enabled && e.field === 'free_text');
    const anchor = t.elements.find((e) => e.enabled && e.field === 'food_name') || t.elements.find((e) => e.enabled && e.field === 'employee_name') || null;
    const menuStyle = t.elements.find((e) => e.enabled && e.field === 'food_name') || anchor || {};
    const memberStyle = t.elements.find((e) => e.enabled && e.field === 'employee_name') || menuStyle;
    const lines = groupPreviewLines();
    let staticBottom = 0;
    staticElements.forEach((e) => {
      const h = e.field === 'line' ? 0 : (e.field === 'logo' || e.field === 'qrcode' ? Number(e.height) : groupTextHeight(sampleText(e), e, Number(e.width)));
      staticBottom = Math.max(staticBottom, Number(e.y) + h);
    });
    const bodyX = anchor ? Number(anchor.x) : 6;
    const bodyY = Math.max(staticBottom + 1.5, anchor ? Number(anchor.y) : 0);
    const bodyW = Math.max(10, Math.min(paperW - Math.max(0, bodyX), anchor ? Number(anchor.width) : paperW - 12));
    let lineTop = 0;
    const linesHtml = lines.map((line) => {
      const style = line.kind === 'menu' ? menuStyle : memberStyle;
      const styleElement = line.kind === 'menu' ? menuStyle : memberStyle;
      const lineHeight = num(style.font_size, 11) * 25.4 / 72 * sc * num(style.line_height, 1.2);
      const lineBoxH = groupTextHeight(line.text, Object.assign({}, style, { height: 0.5 }), bodyW);
      const gapAfter = line.kind === 'menu' ? 1.5 : 0;
      const out = '<div data-fd-group-style-pick="1" data-fd-pick="' + esc(styleElement.id || '') + '" style="height:' + lineBoxH * sc + 'px;margin-bottom:' + gapAfter * sc + 'px;box-sizing:border-box;font-family:\'' + esc(style.font_family || 'Tahoma') + '\',Tahoma,sans-serif;font-size:' + (num(style.font_size, 11) * 25.4 / 72 * sc) + 'px;font-weight:' + (style.bold ? 700 : 400) + ';line-height:' + lineHeight + 'px;text-align:' + (anchor ? anchor.align : 'right') + ';white-space:pre-wrap;overflow-wrap:anywhere">' + esc(line.text) + '</div>';
      lineTop += lineBoxH + gapAfter;
      return out;
    }).join('');
    const bodyH = Math.max(0.5, lineTop + 1.0);
    const bodySelected = anchor && (anchor.id === S.sel || memberStyle.id === S.sel);
    const body = anchor ? '<div class="fd-el' + (bodySelected ? ' fd-sel' : '') + '" data-fd-el="' + esc(anchor.id) + '" style="left:' + bodyX * sc + 'px;top:' + bodyY * sc + 'px;width:' + bodyW * sc + 'px;height:' + bodyH * sc + 'px;' + (anchor.border ? 'outline:1px solid #000;' : '') + '">' + linesHtml + '</div>' : '<div class="fd-help">برای طراحی بخش منوها و اعضا، فیلد «نام غذا» یا «نام کارمند» را فعال کنید.</div>';
    let bottom = Math.max(staticBottom, bodyY + bodyH);
    const footerHtml = footers.map((e) => {
      const moved = Object.assign({}, e, { y: bottom + 1 });
      bottom = moved.y + groupTextHeight(moved.text, moved, moved.width) + 1;
      return elementHtml(moved);
    }).join('');
    const H = Math.max(20, Math.ceil((bottom + num(t.margin_bottom, 3)) * 10) / 10);
    const W = paperW * sc;
    const inset = t.border ? '<div class="fd-border" style="inset:' + t.border_inset * sc + 'px"></div>' : '';
    const ruler = [];
    for (let m = 0; m <= paperW; m += 10) ruler.push('<i style="left:' + m * sc + 'px">' + m + '</i>');
    return '<div class="fd-canvas-wrap"><h3 class="fd-group-canvas-title">چیدمان قابل ویرایش فیش گروهی</h3><p class="fd-help">نمونهٔ بوم شامل ۳ منو و ۱۸ عضو است. فیلد «نام غذا» جایگاه، عرض، تراز و فونت عنوان منوها را کنترل می‌کند؛ فیلد «نام کارمند» را از لایه‌ها یا با کلیک روی یکی از نام‌ها انتخاب کنید تا فونت و فاصلهٔ خطوط اسامی را تنظیم کنید. پیش‌نمایش چاپ پس از هر تغییر به‌روزرسانی می‌شود.</p>' +
      '<div class="fd-ruler-x" style="width:' + W + 'px">' + ruler.join('') + '</div>' +
      '<div class="fd-canvas" id="fd-canvas" style="width:' + W + 'px;height:' + H * sc + 'px">' + inset + staticElements.map(elementHtml).join('') + body + footerHtml + '</div>' +
      '<div class="fd-dim">' + paperW + ' × متغیر (بر اساس تعداد اعضا) میلی‌متر — ارتفاع بلوک با محتوا و فونت‌ها محاسبه می‌شود.</div></div>';
  }

  function canvasHtml() {
    const w = S.work;
    const t = w.template;
    const sc = S.scale;
    const W = w.paper_width * sc;
    const H = paperH() * sc;
    const inset = t.border ? '<div class="fd-border" style="inset:' + t.border_inset * sc + 'px"></div>' : '';
    const rulerX = [];
    for (let m = 0; m <= w.paper_width; m += 10) rulerX.push('<i style="left:' + m * sc + 'px">' + m + '</i>');
    return '<div class="fd-canvas-wrap"><div class="fd-ruler-x" style="width:' + W + 'px">' + rulerX.join('') + '</div>' +
      '<div class="fd-canvas" id="fd-canvas" style="width:' + W + 'px;height:' + H + 'px">' + inset + t.elements.map(elementHtml).join('') + '</div>' +
      '<div class="fd-dim">' + r1(w.paper_width) + ' × ' + (Number(w.paper_height) > 0 ? r1(w.paper_height) : 'متغیر (≈' + r1(paperH()) + ')') + ' میلی‌متر — مقیاس ' + sc + ' پیکسل بر میلی‌متر</div></div>';
  }

  function designTabsHtml() {
    return '<div class="fd-design-tabs"><button type="button" class="fd-design-tab ' + (S.designTab === 'individual' ? 'on' : '') + '" data-fd-design-tab="individual">۱ ـ چاپ انفرادی</button><button type="button" class="fd-design-tab ' + (S.designTab === 'group' ? 'on' : '') + '" data-fd-design-tab="group">۲ ـ چاپ گروهی</button></div>';
  }

  function tabsHtml() {
    if (S.designTab === 'group') return '<div class="fd-tabs"><span class="fd-tab on">قالب مستقل چاپ گروهی</span></div>';
    const items = S.templates.map((t) => '<button type="button" class="fd-tab' + (t.id === S.currentId ? ' on' : '') + '" data-fd-open="' + t.id + '">' + esc(t.name) + (t.is_active ? ' <b class="fd-act">فعال</b>' : '') + '</button>').join('');
    const unsaved = S.currentId === 0 ? '<button type="button" class="fd-tab on">' + esc(S.work.name || 'قالب جدید') + ' <em>(ذخیره‌نشده)</em></button>' : '';
    const canAdd = S.templates.length < MAX_TEMPLATES && S.currentId !== 0;
    return '<div class="fd-tabs">' + items + unsaved +
      '<button type="button" class="fd-tab add" data-fd-new="1" ' + (canAdd ? '' : 'disabled') + ' title="' + (S.templates.length >= MAX_TEMPLATES ? 'حداکثر ' + MAX_TEMPLATES + ' قالب' : 'قالب جدید') + '">＋ قالب جدید (' + S.templates.length + '/' + MAX_TEMPLATES + ')</button></div>';
  }

  function fieldListHtml() {
    const t = S.work.template;
    const rows = S.catalog.map((c) => {
      const mine = t.elements.filter((e) => e.field === c.key);
      const on = mine.some((e) => e.enabled);
      const multi = MULTI.includes(c.key);
      const add = multi ? '<button type="button" class="fd-mini" data-fd-addfield="' + c.key + '">＋ افزودن</button>' : '';
      const cnt = mine.length > 1 ? ' <small>(' + mine.length + ')</small>' : '';
      return '<label class="fd-fr"><input type="checkbox" data-fd-toggle="' + c.key + '" ' + (on ? 'checked' : '') + '><span>' + esc(c.label) + cnt + '</span></label>' + add;
    }).join('');
    return '<div class="fd-card"><h3>فیلدهای قابل چاپ</h3><div class="fd-fields">' + rows + '</div><p class="fd-help">تیک = فعال/غیرفعال در چاپ. فیلد غیرفعال روی فیش چاپ نمی‌شود ولی جایگاهش حفظ می‌شود.</p></div>';
  }

  function layersHtml() {
    const t = S.work.template;
    const cat = FIELD_BY_KEY();
    const rows = t.elements.map((e, i) => '<div class="fd-layer' + (e.id === S.sel ? ' on' : '') + (e.enabled ? '' : ' off') + '" data-fd-pick="' + esc(e.id) + '"><span>' + (i + 1) + '. ' + esc((cat[e.field] || {}).label || e.field) + '</span>' +
      '<span class="fd-lbtn"><button type="button" data-fd-up="' + esc(e.id) + '" title="بالاتر در ترتیب چاپ">▲</button><button type="button" data-fd-down="' + esc(e.id) + '" title="پایین‌تر در ترتیب چاپ">▼</button></span></div>').join('');
    return '<div class="fd-card"><h3>ترتیب چاپ (لایه‌ها)</h3><div class="fd-layers">' + (rows || '<p class="fd-help">فیلدی روی فیش نیست.</p>') + '</div></div>';
  }

  function groupTypographyPropsHtml(e) {
    const isMenu = e.field === 'food_name';
    const fonts = S.fonts.map((f) => '<option ' + (f === e.font_family ? 'selected' : '') + '>' + esc(f) + '</option>').join('');
    const inp = (k, label, step, extra) => '<label class="fd-f">' + label + '<input type="number" step="' + (step || 0.5) + '" data-fd-prop="' + k + '" value="' + esc(e[k]) + '" ' + (extra || '') + '></label>';
    const layout = isMenu ? inp('x', 'فاصله از چپ (mm)', 0.5, 'min="0"') + inp('y', 'فاصله از بالا (mm)', 0.5, 'min="0"') + inp('width', 'عرض بلوک (mm)', 0.5, 'min="10"') : '';
    const align = isMenu ? '<label class="fd-f">تراز کل بلوک<select data-fd-prop="align"><option value="right" ' + (e.align === 'right' ? 'selected' : '') + '>راست</option><option value="center" ' + (e.align === 'center' ? 'selected' : '') + '>وسط</option><option value="left" ' + (e.align === 'left' ? 'selected' : '') + '>چپ</option></select></label>' : '';
    const border = isMenu ? '<label class="fd-f chk"><input type="checkbox" data-fd-prop="border" ' + (e.border ? 'checked' : '') + '> کادر دور بلوک منوها و اعضا</label>' : '';
    return '<div class="fd-card"><h3>مشخصات: ' + (isMenu ? 'عنوان منوها' : 'اسامی اعضا') + '</h3><div class="fd-grid">' +
      '<label class="fd-f chk full"><input type="checkbox" data-fd-prop="enabled" ' + (e.enabled ? 'checked' : '') + '> فعال به‌عنوان سبک فیش گروهی</label>' + layout +
      '<label class="fd-f">فونت<select data-fd-prop="font_family">' + fonts + '</select></label>' +
      inp('font_size', 'اندازهٔ فونت (pt)', 0.5, 'min="4" max="72"') +
      inp('line_height', 'فاصلهٔ خطوط (ضریب)', 0.05, 'min="0.8" max="3"') + align +
      '<label class="fd-f chk"><input type="checkbox" data-fd-prop="bold" ' + (e.bold ? 'checked' : '') + '> ضخیم (Bold)</label>' + border +
      (isMenu ? '<p class="fd-help" style="grid-column:1/-1">این فونت برای سطرهای «منو۱ — نام غذا» است. جایگاه و تراز این فیلد روی همهٔ سطرهای پویای گروه اعمال می‌شود.</p>' : '<p class="fd-help" style="grid-column:1/-1">این فونت فقط برای سطرهای اسامی اعضا است؛ جایگاه و تراز کل بلوک از فیلد «نام غذا» گرفته می‌شود.</p>') +
      '</div></div>';
  }

  function propsHtml() {
    const e = selected();
    if (!e) return '<div class="fd-card"><h3>مشخصات فیلد</h3><p class="fd-help">یک فیلد را روی بوم انتخاب کنید.</p></div>';
    if (S.designTab === 'group' && (e.field === 'food_name' || e.field === 'employee_name')) return groupTypographyPropsHtml(e);
    const cat = FIELD_BY_KEY()[e.field] || {};
    const isText = !['line', 'logo', 'qrcode'].includes(e.field);
    const hasText = e.field === 'title' || e.field === 'free_text';
    const inp = (k, label, step, extra) => '<label class="fd-f">' + label + '<input type="number" step="' + (step || 0.5) + '" data-fd-prop="' + k + '" value="' + esc(e[k]) + '" ' + (extra || '') + '></label>';
    const fonts = S.fonts.map((f) => '<option ' + (f === e.font_family ? 'selected' : '') + '>' + esc(f) + '</option>').join('');
    const textBlock = isText ? (
      '<label class="fd-f">نوع فونت<select data-fd-prop="font_family">' + fonts + '</select></label>' +
      inp('font_size', 'اندازه فونت (pt)', 0.5, 'min="4" max="72"') +
      inp('line_height', 'فاصله خطوط (ضریب)', 0.05, 'min="0.8" max="3"') +
      '<label class="fd-f">تراز<select data-fd-prop="align"><option value="right" ' + (e.align === 'right' ? 'selected' : '') + '>راست</option><option value="center" ' + (e.align === 'center' ? 'selected' : '') + '>وسط</option><option value="left" ' + (e.align === 'left' ? 'selected' : '') + '>چپ</option></select></label>' +
      '<label class="fd-f chk"><input type="checkbox" data-fd-prop="bold" ' + (e.bold ? 'checked' : '') + '> ضخیم (Bold)</label>' +
      '<label class="fd-f chk"><input type="checkbox" data-fd-prop="border" ' + (e.border ? 'checked' : '') + '> کادر دور فیلد</label>'
    ) : ((e.field === 'logo' || e.field === 'qrcode') ? '<label class="fd-f">تراز<select data-fd-prop="align"><option value="right" ' + (e.align === 'right' ? 'selected' : '') + '>راست</option><option value="center" ' + (e.align === 'center' ? 'selected' : '') + '>وسط</option><option value="left" ' + (e.align === 'left' ? 'selected' : '') + '>چپ</option></select></label>' : '');
    const labelBlock = (cat.type === 'text' && isText && e.field !== 'system_name' && e.field !== 'food_name') ?
      '<label class="fd-f chk"><input type="checkbox" data-fd-prop="show_label" ' + (e.show_label ? 'checked' : '') + '> چاپ عنوان قبل از مقدار</label>' +
      '<label class="fd-f full">عنوان فیلد<input type="text" data-fd-prop="label" value="' + esc(e.label) + '" maxlength="60"></label>' : '';
    const textArea = hasText ? '<label class="fd-f full">متن<textarea data-fd-prop="text" rows="2" maxlength="500">' + esc(e.text) + '</textarea></label>' : '';
    return '<div class="fd-card"><h3>مشخصات: ' + esc(cat.label || e.field) + '</h3><div class="fd-grid">' +
      '<label class="fd-f chk full"><input type="checkbox" data-fd-prop="enabled" ' + (e.enabled ? 'checked' : '') + '> فعال (چاپ شود)</label>' +
      inp('x', 'فاصله از چپ (mm)', 0.5, 'min="0"') + inp('y', 'فاصله از بالا (mm)', 0.5, 'min="0"') +
      inp('width', 'عرض (mm)', 0.5, 'min="1"') + inp('height', 'ارتفاع (mm)', 0.5, 'min="0.5"') +
      textBlock + labelBlock + textArea +
      '</div><div class="fd-actions"><button type="button" class="fd-btn danger" data-fd-delete="' + esc(e.id) + '">حذف فیلد</button></div></div>';
  }

  function paperHtml() {
    const w = S.work;
    const t = w.template;
    const variable = !(Number(w.paper_height) > 0);
    const logoThumb = (u, ok) => ok ? '<img class="fd-thumb" src="' + esc(u) + '" alt="">' : '<span class="fd-help">ندارد</span>';
    const warn58 = (w.paper_width > 60 && w.paper_width < 72) ? '<p class="fd-warn">عرض بین ۶۰ تا ۷۲ میلی‌متر معمولاً با سرِ چاپ ۵۸ یا ۸۰ هم‌خوان نیست؛ در چاپ شبکه از لبه‌ها برش می‌خورد.</p>' : '';
    return '<div class="fd-card"><h3>کاغذ و قالب</h3><div class="fd-grid">' +
      '<label class="fd-f full">نام قالب<input type="text" data-fd-meta="name" value="' + esc(w.name) + '" maxlength="120"></label>' +
      '<label class="fd-f">عرض کاغذ (mm)<input type="number" step="0.5" min="20" max="120" data-fd-meta="paper_width" value="' + esc(w.paper_width) + '"></label>' +
      '<label class="fd-f">طول کاغذ (mm)<input type="number" step="0.5" min="15" max="600" data-fd-meta="paper_height" value="' + esc(variable ? 50 : w.paper_height) + '" ' + (variable ? 'disabled' : '') + '></label>' +
      '<label class="fd-f chk full"><input type="checkbox" data-fd-meta="variable" ' + (variable ? 'checked' : '') + '> طول متغیر (بر اساس محتوا)</label>' +
      '<label class="fd-f chk"><input type="checkbox" data-fd-meta="cut_paper" ' + (t.cut_paper ? 'checked' : '') + '> برش خودکار</label>' +
      '<label class="fd-f chk"><input type="checkbox" data-fd-meta="border" ' + (t.border ? 'checked' : '') + '> کادر دور فیش</label>' +
      (t.border ? '<label class="fd-f">فاصلهٔ کادر از لبه (mm)<input type="number" step="0.5" min="0" max="20" data-fd-meta="border_inset" value="' + esc(t.border_inset) + '"></label>' : '') +
      '</div>' + warn58 + '</div>' +
      '<div class="fd-card"><h3>لوگو و نام سامانه</h3><div class="fd-grid">' +
      '<div class="fd-f full"><b>منبع لوگو</b><label class="fd-radio"><input type="radio" name="fd-logo" data-fd-meta="logo_source" value="system" ' + (t.logo_source !== 'ticket' ? 'checked' : '') + '> استفاده از لوگوی سیستم ' + logoThumb(S.brand.system_logo_url, S.brand.has_system_logo) + '</label>' +
      '<label class="fd-radio"><input type="radio" name="fd-logo" data-fd-meta="logo_source" value="ticket" ' + (t.logo_source === 'ticket' ? 'checked' : '') + '> لوگوی اختصاصی فیش ' + logoThumb(S.brand.ticket_logo_url, S.brand.has_ticket_logo) + '</label>' +
      '<div class="fd-row"><label class="fd-btn">آپلود لوگوی فیش<input type="file" id="fd-logo-file" accept="image/png,image/jpeg" hidden></label>' + (S.brand.has_ticket_logo ? '<button type="button" class="fd-btn danger" data-fd-logo-remove="1">حذف لوگوی فیش</button>' : '') + '</div>' +
      '<p class="fd-help">لوگوی سیستم از «تنظیمات عمومی ← اطلاعات سامانه» خوانده می‌شود (PNG/JPG، حداکثر ۲ مگابایت).</p></div>' +
      '<div class="fd-f full"><b>نام سامانه</b><label class="fd-radio"><input type="radio" name="fd-name" data-fd-meta="name_source" value="system" ' + (t.name_source !== 'custom' ? 'checked' : '') + '> نام سامانه: «' + esc(S.brand.system_name || '') + '»</label>' +
      '<label class="fd-radio"><input type="radio" name="fd-name" data-fd-meta="name_source" value="custom" ' + (t.name_source === 'custom' ? 'checked' : '') + '> نام اختصاصی فیش</label>' +
      '<input type="text" data-fd-meta="custom_name" value="' + esc(t.custom_name) + '" placeholder="نام اختصاصی" maxlength="120" ' + (t.name_source === 'custom' ? '' : 'disabled') + '></div>' +
      '</div></div>';
  }

  function groupModeHtml() {
    const isGroup = S.groupPrintMode !== 'individual';
    return '<div class="fd-card fd-group-mode"><h3>حالت چاپ گروه‌های غذایی</h3>' +
      '<p class="fd-help">این گزینه فقط برای گروه‌های غذایی است؛ مسیرهای چاپ فیش انفرادی و سایر چاپ‌ها بدون تغییر می‌مانند.</p>' +
      '<div class="fd-actions"><button type="button" class="fd-btn ' + (isGroup ? 'primary' : '') + '" data-fd-group-mode="group">چاپ یک فیش مشترک برای گروه</button>' +
      '<button type="button" class="fd-btn ' + (!isGroup ? 'primary' : '') + '" data-fd-group-mode="individual">بازگشت به چاپ انفرادی اعضا</button></div>' +
      '<p class="fd-help">وضعیت فعلی: <b>' + (isGroup ? 'فیش مشترک برای هر گروه' : 'فیش جداگانه برای هر عضو') + '</b></p></div>';
  }

  function groupLivePreviewHtml() {
    const w = S.work, t = w.template, scale = 3, paperW = Number(w.paper_width) || 80;
    const ignored = ['food_name', 'employee_name', 'personnel_code', 'national_code', 'department', 'meal', 'ticket_number', 'qrcode', 'free_text'];
    const staticElements = t.elements.filter((e) => e.enabled && !ignored.includes(e.field));
    const footers = t.elements.filter((e) => e.enabled && e.field === 'free_text');
    const anchor = t.elements.find((e) => e.enabled && e.field === 'food_name') || t.elements.find((e) => e.enabled && e.field === 'employee_name') || null;
    const menuStyle = t.elements.find((e) => e.enabled && e.field === 'food_name') || anchor || {};
    const memberStyle = t.elements.find((e) => e.enabled && e.field === 'employee_name') || menuStyle;
    const lines = groupPreviewLines();
    let staticBottom = 0;
    const staticHtml = staticElements.map((e) => {
      const h = e.field === 'line' ? 0 : (e.field === 'logo' || e.field === 'qrcode' ? Number(e.height) : groupTextHeight(sampleText(e), e, Number(e.width)));
      staticBottom = Math.max(staticBottom, Number(e.y) + h);
      const style = 'position:absolute;left:' + e.x * scale + 'px;top:' + e.y * scale + 'px;width:' + e.width * scale + 'px;height:' + Math.max(e.height, h) * scale + 'px;box-sizing:border-box;';
      if (e.field === 'line') return '<div style="' + style + 'border-top:1px dashed #333"></div>';
      if (e.field === 'logo') {
        const logo = logoUrl();
        return logo ? '<img alt="" src="' + esc(logo) + '" style="' + style + 'object-fit:contain">' : '';
      }
      if (e.field === 'qrcode') return '<div style="' + style + 'background:repeating-conic-gradient(#000 0 25%,#fff 0 50%) 0 0/20% 20%"></div>';
      const pt = num(e.font_size, 11) * 25.4 / 72 * scale;
      return '<div style="' + style + 'font-family:\'' + esc(e.font_family || 'Tahoma') + '\',Tahoma,sans-serif;font-size:' + pt + 'px;font-weight:' + (e.bold ? 700 : 400) + ';line-height:' + pt * num(e.line_height, 1.2) + 'px;text-align:' + (e.align || 'right') + ';white-space:pre-wrap;' + (e.border ? 'outline:1px solid #000;' : '') + '">' + esc(sampleText(e)).replace(/\n/g, '<br>') + '</div>';
    }).join('');
    const bodyX = anchor ? Number(anchor.x) : 6;
    const bodyY = Math.max(staticBottom + 1.5, anchor ? Number(anchor.y) : 0);
    const bodyW = Math.max(10, Math.min(paperW - Math.max(0, bodyX), anchor ? Number(anchor.width) : paperW - 12));
    let usedH = 0;
    const linesHtml = lines.map((line) => {
      const style = line.kind === 'menu' ? menuStyle : memberStyle;
      const lineBoxH = groupTextHeight(line.text, Object.assign({}, style, { height: 0.5 }), bodyW);
      const gapAfter = line.kind === 'menu' ? 1.5 : 0;
      usedH += lineBoxH + gapAfter;
      const pt = num(style.font_size, 11) * 25.4 / 72 * scale;
      return '<div style="height:' + lineBoxH * scale + 'px;margin-bottom:' + gapAfter * scale + 'px;box-sizing:border-box;font-family:\'' + esc(style.font_family || 'Tahoma') + '\',Tahoma,sans-serif;font-size:' + pt + 'px;font-weight:' + (style.bold ? 700 : 400) + ';line-height:' + pt * num(style.line_height, 1.2) + 'px;text-align:' + (anchor ? anchor.align : 'right') + ';white-space:pre-wrap;overflow-wrap:anywhere">' + esc(line.text) + '</div>';
    }).join('');
    const bodyH = Math.max(0.5, usedH + 1.0);
    let bottom = Math.max(staticBottom, bodyY + bodyH);
    const footerHtml = footers.map((e) => {
      const y = bottom + 1;
      const h = groupTextHeight(e.text, e, e.width);
      bottom = y + h + 1;
      const pt = num(e.font_size, 11) * 25.4 / 72 * scale;
      return '<div style="position:absolute;left:' + e.x * scale + 'px;top:' + y * scale + 'px;width:' + e.width * scale + 'px;height:' + h * scale + 'px;font-family:\'' + esc(e.font_family || 'Tahoma') + '\',Tahoma,sans-serif;font-size:' + pt + 'px;font-weight:' + (e.bold ? 700 : 400) + ';line-height:' + pt * num(e.line_height, 1.2) + 'px;text-align:' + (e.align || 'right') + ';white-space:pre-wrap">' + esc(e.text).replace(/\n/g, '<br>') + '</div>';
    }).join('');
    const height = Math.max(20, bottom + num(t.margin_bottom, 3));
    const paperBorder = t.border ? '<div style="position:absolute;inset:' + num(t.border_inset, 3) * scale + 'px;border:1px solid #000;pointer-events:none"></div>' : '';
    const bodyHtml = '<div style="position:absolute;left:' + bodyX * scale + 'px;top:' + bodyY * scale + 'px;width:' + bodyW * scale + 'px;height:' + bodyH * scale + 'px;box-sizing:border-box;' + (anchor && anchor.border ? 'outline:1px solid #000;' : '') + '">' + linesHtml + '</div>';
    return '<div class="fd-group-live-preview"><div style="position:relative;background:#fff;margin:0 auto;width:' + paperW * scale + 'px;height:' + height * scale + 'px;direction:rtl;overflow:hidden;box-shadow:0 2px 10px #0002">' + paperBorder + staticHtml + bodyHtml + footerHtml + '</div></div>';
  }

  function previewHtml() {
    const p = S.preview;
    const isGroup = S.designTab === 'group';
    let inner;
    if (isGroup) {
      let actual;
      if (S.previewBusy && !p) actual = '<p class="fd-help">در حال ساخت تصویر دقیق چاپ از سرور…</p>';
      else if (!p) actual = '<p class="fd-help">تصویر دقیق چاپ پس از بارگذاری ساخته می‌شود.</p>';
      else if (p.error) actual = '<p class="fd-warn">' + esc(p.error) + '</p>';
      else {
        const w = p.paper_mm.w * S.scale;
        const fonts = Object.keys(p.fonts || {}).map((k) => esc(k) + (p.fonts[k] !== k ? ' ← ' + esc(p.fonts[k]) : '')).join('، ');
        actual = '<div class="fd-pv-paper"><img src="' + p.image + '" alt="خروجی دقیق چاپ" style="width:' + w + 'px;image-rendering:pixelated"></div>' +
          '<p class="fd-help">تصویر واقعی رندرشده در سرور؛ ' + p.width_px + '×' + p.height_px + ' نقطه.' + (fonts ? ' فونت‌های استفاده‌شده: ' + fonts : '') + '</p>' +
          (p.warnings || []).map((x) => '<p class="fd-warn">' + esc(x) + '</p>').join('');
      }
      inner = '<h4 class="fd-live-title">پیش‌نمایش زندهٔ طراحی</h4>' + groupLivePreviewHtml() +
        '<div class="fd-group-server-preview"><h4>خروجی دقیق چاپ</h4>' + actual + '</div>';
    } else if (S.previewBusy && !p) inner = '<p class="fd-help">در حال ساخت پیش‌نمایش…</p>';
    else if (!p) inner = '<p class="fd-help">برای دیدن خروجی دقیق، «به‌روزرسانی پیش‌نمایش» را بزنید.</p>';
    else if (p.error) inner = '<p class="fd-warn">' + esc(p.error) + '</p>';
    else {
      const w = p.paper_mm.w * S.scale;
      const fonts = Object.keys(p.fonts || {}).map((k) => esc(k) + (p.fonts[k] !== k ? ' ← ' + esc(p.fonts[k]) : '')).join('، ');
      inner = '<div class="fd-pv-paper"><img src="' + p.image + '" alt="پیش‌نمایش واقعی" style="width:' + w + 'px;image-rendering:pixelated"></div>' +
        '<p class="fd-help">اندازهٔ تصویر ' + p.width_px + '×' + p.height_px + ' نقطه (چاپگر ' + p.dots_per_mm + ' نقطه بر میلی‌متر). این همان تصویر ارسالی به چاپگر شبکه است.' + (fonts ? ' فونت: ' + fonts : '') + '</p>' +
        (p.warnings || []).map((x) => '<p class="fd-warn">' + esc(x) + '</p>').join('');
    }
    const previewTitle = isGroup ? 'پیش‌نمایش چاپ گروهی' : 'پیش‌نمایش واقعی چاپ انفرادی';
    return '<div class="fd-card fd-preview"><h3>' + previewTitle + '</h3>' + inner + '<div class="fd-actions"><button type="button" class="fd-btn" data-fd-preview="1">' + (S.previewBusy ? 'در حال به‌روزرسانی…' : 'به‌روزرسانی پیش‌نمایش') + '</button></div></div>';
  }

  function body() {
    if (S.loading && !S.loaded) return '<div class="fd-card"><p>در حال بارگذاری قالب‌ها…</p></div>';
    if (S.error && !S.loaded) return '<div class="fd-card"><p class="fd-warn">' + esc(S.error) + '</p><button type="button" class="fd-btn" data-fd-reload="1">تلاش دوباره</button></div>';
    if (!S.work) return '<div class="fd-card"><p>قالبی پیدا نشد.</p></div>';
    const w = S.work;
    const isActive = (S.templates.find((t) => t.id === w.id) || {}).is_active;
    const groupDesign = S.designTab === 'group';
    const actions = groupDesign ?
      '<button type="button" class="fd-btn primary" data-fd-save-group="1" ' + (S.busy ? 'disabled' : '') + '>ذخیرهٔ طراحی گروهی</button>' :
      '<button type="button" class="fd-btn primary" data-fd-save="0" ' + (S.busy ? 'disabled' : '') + '>ذخیره</button>' +
      '<button type="button" class="fd-btn" data-fd-save="1" ' + (S.busy ? 'disabled' : '') + '>ذخیره و فعال‌سازی</button>' +
      (w.id && !isActive ? '<button type="button" class="fd-btn" data-fd-activate="' + w.id + '">انتخاب به‌عنوان قالب فعال</button>' : '') +
      '<button type="button" class="fd-btn" data-fd-testprint="1" ' + (w.id ? '' : 'disabled title="ابتدا قالب را ذخیره کنید"') + '>چاپ آزمایشی این قالب</button>' +
      (w.id ? '<button type="button" class="fd-btn danger" data-fd-deltpl="' + w.id + '">حذف قالب</button>' : '');
    return '<style>' + css() + '</style>' +
      '<div class="fd-head"><div><h2>طراحی فیش غذا</h2><p class="fd-help">طراحی انفرادی و گروهی مستقل ذخیره می‌شوند. قالب انفرادی فعال، رفتار چاپ فعلی را حفظ می‌کند.</p></div>' +
      '<div class="fd-actions top">' + actions + '</div></div>' + designTabsHtml() +
      (groupDesign ? groupModeHtml() : '') + tabsHtml() +
      (S.dirty ? '<div class="fd-dirty">تغییرات این طراحی ذخیره نشده است. تا «ذخیره» نزنید روی چاپ اثری ندارد.</div>' : '') +
      '<div class="fd-layout"><div class="fd-side">' + paperHtml() + fieldListHtml() + '</div>' +
      '<div class="fd-main">' + (groupDesign ? groupCanvasHtml() : canvasHtml()) + '<div class="fd-zoom">بزرگ‌نمایی: <button type="button" data-fd-zoom="-1">−</button> <button type="button" data-fd-zoom="1">+</button></div>' + previewHtml() + '</div>' +
      '<div class="fd-side">' + propsHtml() + layersHtml() + '</div></div>';
  }

  function css() {
    return '.fd-root{direction:rtl;font-family:inherit}.fd-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-bottom:10px}.fd-head h2{margin:0 0 4px}' +
      '.fd-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.fd-actions.top{margin:0}.fd-btn{border:1px solid #cbd8df;background:#fff;border-radius:9px;padding:7px 12px;cursor:pointer;font:inherit;color:#0f766e}.fd-btn.primary{background:#0f766e;color:#fff;border-color:#0f766e}.fd-btn.danger{color:#dc2626;border-color:#f3c0c0}.fd-btn[disabled]{opacity:.5;cursor:not-allowed}' +
      '.fd-design-tabs{display:flex;gap:8px;margin:12px 0 8px;border-bottom:1px solid #dce5eb}.fd-design-tab{border:1px solid #cbd8df;background:#f7fafb;border-radius:10px 10px 0 0;padding:9px 16px;cursor:pointer;font:inherit;font-weight:600;color:#334155}.fd-design-tab.on{background:#0f766e;color:#fff;border-color:#0f766e}.fd-tabs{display:flex;gap:6px;flex-wrap:wrap;margin:8px 0}.fd-tab{border:1px solid #cbd8df;background:#fff;border-radius:10px 10px 0 0;padding:7px 12px;cursor:pointer;font:inherit}.fd-tab.on{background:#0f766e;color:#fff;border-color:#0f766e}.fd-tab.add{border-style:dashed}.fd-tab[disabled]{opacity:.45;cursor:not-allowed}.fd-act{background:#fff;color:#0f766e;border-radius:6px;padding:0 6px;font-size:11px}' +
      '.fd-dirty{background:#fff7e6;border:1px solid #f2c97d;color:#8a5a00;border-radius:9px;padding:7px 12px;margin-bottom:8px}.fd-layout{display:grid;grid-template-columns:300px minmax(340px,1fr) 300px;gap:14px;align-items:start}@media(max-width:1200px){.fd-layout{grid-template-columns:1fr}}' +
      '.fd-card{background:#fff;border:1px solid #dce5eb;border-radius:12px;padding:12px;margin-bottom:12px}.fd-card h3{margin:0 0 8px;font-size:15px}.fd-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.fd-f{display:flex;flex-direction:column;gap:3px;font-size:12px}.fd-f.full{grid-column:1/-1}.fd-f.chk{flex-direction:row;align-items:center;gap:6px}.fd-f input[type=number],.fd-f input[type=text],.fd-f select,.fd-f textarea,.fd-card input[type=text]{border:1px solid #cbd8df;border-radius:8px;padding:6px;font:inherit;width:100%;box-sizing:border-box}' +
      '.fd-help{color:#6d7c89;font-size:12px;margin:6px 0 0}.fd-warn{color:#b45309;background:#fff7e6;border-radius:8px;padding:6px 8px;font-size:12px}.fd-fields{display:grid;grid-template-columns:1fr auto;gap:4px 8px;align-items:center}.fd-fr{display:flex;gap:6px;align-items:center;font-size:13px}.fd-mini{border:1px dashed #0f766e;background:#fff;color:#0f766e;border-radius:7px;font-size:11px;padding:2px 6px;cursor:pointer}' +
      '.fd-radio{display:flex;align-items:center;gap:6px;font-size:12px;margin:3px 0}.fd-thumb{height:22px;max-width:60px;object-fit:contain;border:1px solid #dce5eb;border-radius:4px}.fd-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}' +
      '.fd-canvas-wrap{overflow:auto;background:#eef2f4;border-radius:12px;padding:12px;text-align:center}.fd-canvas{position:relative;background:#fff;margin:0 auto;box-shadow:0 2px 14px #00000026;direction:ltr;touch-action:none;user-select:none;overflow:hidden}.fd-ruler-x{position:relative;height:14px;margin:0 auto 2px;direction:ltr;font-size:9px;color:#6d7c89}.fd-ruler-x i{position:absolute;top:0;font-style:normal;transform:translateX(-50%)}' +
      '.fd-border{position:absolute;border:1px solid #000;pointer-events:none}.fd-el{position:absolute;cursor:move;box-sizing:border-box;outline:1px dashed #9db4c0;direction:rtl}.fd-el.fd-sel{outline:2px solid #0f766e;z-index:5}.fd-el.fd-off{opacity:.3}.fd-text{width:100%;height:100%;overflow:visible;white-space:pre-wrap;color:#000}.fd-line{border-top:1px dashed #000;margin-top:3px}.fd-el img{max-width:100%;max-height:100%;object-fit:contain;pointer-events:none}.fd-ph{font-size:11px;color:#9aa8b2}.fd-qr{background:repeating-conic-gradient(#000 0 25%,#fff 0 50%) 0 0/20% 20%;color:transparent}' +
      '.fd-h{position:absolute;right:-5px;bottom:-5px;width:11px;height:11px;background:#0f766e;border:2px solid #fff;border-radius:3px;cursor:nwse-resize}.fd-dim{font-size:11px;color:#6d7c89;margin-top:6px}.fd-zoom{font-size:12px;margin:6px 0}.fd-zoom button{border:1px solid #cbd8df;background:#fff;border-radius:6px;width:28px;cursor:pointer}' +
      '.fd-layers{display:flex;flex-direction:column;gap:4px;max-height:260px;overflow:auto}.fd-layer{display:flex;justify-content:space-between;align-items:center;border:1px solid #e3ebf0;border-radius:8px;padding:4px 8px;font-size:12px;cursor:pointer}.fd-layer.on{border-color:#0f766e;background:#e9f7f5}.fd-layer.off{opacity:.55}.fd-lbtn button{border:0;background:transparent;cursor:pointer;color:#0f766e}' +
      '.fd-pv-paper{background:#eef2f4;padding:10px;text-align:center;border-radius:10px;overflow:auto}.fd-pv-paper img{background:#fff;box-shadow:0 2px 10px #0003}.fd-group-mode{border-right:4px solid #0f766e}.fd-live-title,.fd-group-server-preview h4{margin:10px 0 6px;font-size:13px}.fd-group-live-preview{background:#eef2f4;padding:10px;border-radius:10px;overflow:auto}.fd-group-server-preview{margin-top:14px;padding-top:8px;border-top:1px solid #dce5eb}.fd-real-preview-title{margin:0 0 8px;font-size:15px}.fd-sample-toggle{margin:0 0 8px}.fd-sample-toggle .fd-btn{font-size:12px}';
  }

  // ───────────────────────── تغییر داده و پیش‌نمایش ─────────────────────────
  function touch(redraw) {
    S.dirty = true;
    if (S.designTab === 'group') { S.groupDirty = true; S.groupWork = workFrom(S.work); }
    else { S.individualDirty = true; S.individualWork = workFrom(S.work); }
    if (redraw !== false) draw();
    schedulePreview(180);
  }
  function schedulePreview(ms) {
    clearTimeout(S.previewTimer);
    const seq = ++S.previewSeq;
    if (!S.work) return;
    S.previewTimer = setTimeout(() => runPreview(seq), ms);
  }
  function runPreview(seq) {
    if (seq === undefined) {
      clearTimeout(S.previewTimer);
      seq = ++S.previewSeq;
    }
    if (!S.work || !root() || seq !== S.previewSeq) return;
    S.previewBusy = true;
    S.preview = null;
    const w = workFrom(S.work);
    const sampleType = S.designTab === 'group' ? 'group' : S.previewKind;
    draw();
    callApi('/api/templates/preview', { method: 'POST', cache: 'no-store', body: JSON.stringify({ id: w.id, name: w.name, paper_width: w.paper_width, paper_height: w.paper_height, template: w.template, sample_type: sampleType }) })
      .then((x) => { if (seq === S.previewSeq) S.preview = x; })
      .catch((e) => { if (seq === S.previewSeq) S.preview = { error: (e && e.message) || 'پیش‌نمایش ساخته نشد' }; })
      .then(() => { if (seq === S.previewSeq) { S.previewBusy = false; draw(); } });
  }

  function guardDirty() {
    return !S.dirty || window.confirm('تغییرات ذخیره‌نشده از بین می‌رود. ادامه می‌دهید؟');
  }

  function switchDesignTab(tab) {
    if (!['individual', 'group'].includes(tab) || tab === S.designTab) return;
    if (S.designTab === 'group') S.groupWork = workFrom(S.work);
    else S.individualWork = workFrom(S.work);
    S.designTab = tab;
    S.previewKind = tab === 'group' ? 'group' : 'individual';
    const next = tab === 'group' ? S.groupWork : S.individualWork;
    if (next) setWork(next);
    S.preview = null;
    draw();
    schedulePreview(0);
  }

  function addElement(field) {
    const c = FIELD_BY_KEY()[field];
    if (!c) return null;
    const t = S.work.template;
    const W = Number(S.work.paper_width) || 80;
    const bottom = t.elements.reduce((m, e) => Math.max(m, e.y + e.height), 2);
    const isLogo = field === 'logo', isQr = field === 'qrcode', isLine = field === 'line';
    const w = isLogo ? Math.min(24, W - 6) : isQr ? Math.min(24, W - 6) : W - 12;
    const h = isLogo ? 14 : isQr ? 24 : isLine ? 0.5 : 6;
    const e = el(field, r1(isLogo || isQr ? (W - w) / 2 : 6), r1(bottom + 1), r1(w), h, field === 'food_name' ? 16 : 11, field === 'food_name', (isLogo || isQr || field === 'food_name' || field === 'system_name' || field === 'title' || field === 'free_text') ? 'center' : 'right', {
      show_label: c.type === 'text' && !!c.label_text, label: c.label_text || '', text: field === 'title' ? 'فیش غذای پرسنل' : field === 'free_text' ? 'متن دلخواه' : '',
    });
    t.elements.push(e);
    S.sel = e.id;
    return e;
  }

  async function saveGroup() {
    if (S.busy || !S.work) return;
    S.busy = true; draw();
    const w = S.work;
    try {
      const x = await callApi('/api/templates/group-design', { method: 'POST', body: JSON.stringify({ name: w.name || 'قالب گروهی', paper_width: w.paper_width, paper_height: w.paper_height, template: w.template }) });
      S.groupTemplate = x.group_template || { id: 0, name: w.name, paper_width: w.paper_width, paper_height: w.paper_height, template: w.template };
      S.groupWork = workFrom(S.groupTemplate);
      S.groupDirty = false;
      S.work = workFrom(S.groupWork);
      S.currentId = 0;
      S.dirty = false;
      S.preview = null;
      say('طراحی مستقل چاپ گروهی ذخیره شد.');
      schedulePreview(0);
    } catch (e) { say((e && e.message) || 'ذخیرهٔ طراحی گروهی انجام نشد'); }
    S.busy = false; draw();
  }

  async function save(activate) {
    if (S.designTab === 'group') return saveGroup();
    if (S.busy || !S.work) return;
    S.busy = true; draw();
    const w = S.work;
    try {
      const x = await callApi('/api/templates', { method: 'POST', body: JSON.stringify({ id: w.id || 0, name: w.name || 'قالب بدون نام', paper_width: w.paper_width, paper_height: w.paper_height, template: w.template, activate: !!activate }) });
      const keepSel = S.sel;
      applyList(x, true);
      const row = S.templates.find((t) => t.id === x.saved_id);
      if (row) { S.individualDirty = false; setWork(row); if (row.template.elements.some((e) => e.id === keepSel)) S.sel = keepSel; }
      say(activate ? 'قالب ذخیره و فعال شد؛ فیش بعدی با همین قالب چاپ می‌شود.' : 'قالب ذخیره شد.');
      schedulePreview(0);
    } catch (e) { say((e && e.message) || 'ذخیرهٔ قالب انجام نشد'); }
    S.busy = false; draw();
  }

  async function postAction(path, body, okMsg) {
    try {
      const x = await callApi(path, { method: 'POST', body: JSON.stringify(body) });
      applyList(x, false);
      if (okMsg) say(okMsg);
      schedulePreview(0);
    } catch (e) { say((e && e.message) || 'خطا'); }
    draw();
  }

  async function uploadLogo(file) {
    const fd = new FormData();
    fd.append('logo', file);
    try {
      const r = await fetch((window.FOOD_TICKET_API_BASE || '') + encodeURIComponent('templates/logo'), { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': window.FOOD_TICKET_CSRF || '' }, body: fd });
      const t = await r.text();
      let j = null; try { j = JSON.parse(t); } catch (e) { /* ignore */ }
      if (!r.ok || !j || j.error) throw new Error((j && j.error) || 'آپلود ناموفق بود');
      S.brand = j.brand || S.brand;
      if (S.work) { S.work.template.logo_source = 'ticket'; touch(false); }
      say('لوگوی فیش ذخیره شد؛ برای اعمال در چاپ، قالب را ذخیره کنید.');
      schedulePreview(200);
    } catch (e) { say((e && e.message) || 'آپلود ناموفق بود'); }
    draw();
  }

  // ───────────────────────── رویدادها ─────────────────────────
  function within(target) { const r = root(); return r && target && r.contains(target); }

  document.addEventListener('click', (ev) => {
    if (!within(ev.target)) return;
    const t = ev.target.closest('button,[data-fd-pick],[data-fd-el]');
    if (!t || !S.work) return;
    const ds = t.dataset;
    if (ds.fdDesignTab) { switchDesignTab(ds.fdDesignTab); return; }
    if (ds.fdReload) { S.loading = false; S.error = ''; load(); return; }
    if (ds.fdGroupMode) {
      const mode = ds.fdGroupMode === 'individual' ? 'individual' : 'group';
      if (mode === S.groupPrintMode) return;
      if (mode === 'individual' && !window.confirm('از این پس اعضای هر گروه به‌صورت فیش‌های جداگانه چاپ می‌شوند. ادامه می‌دهید؟')) return;
      callApi('/api/templates/group-mode', { method: 'POST', body: JSON.stringify({ mode }) }).then((x) => {
        S.groupPrintMode = x.group_print_mode === 'individual' ? 'individual' : mode;
        S.templates = x.templates || S.templates;
        switchDesignTab(mode === 'individual' ? 'individual' : 'group');
        draw();
        say(S.groupPrintMode === 'individual' ? 'حالت چاپ انفرادی گروه‌ها فعال شد.' : 'چاپ تجمیعی گروه‌ها فعال شد.');
      }).catch((e) => say((e && e.message) || 'ذخیرهٔ حالت چاپ گروهی انجام نشد'));
      return;
    }
    if (ds.fdOpen !== undefined) {
      const row = S.templates.find((x) => x.id === Number(ds.fdOpen));
      if (row && row.id !== S.currentId && guardDirty()) { S.individualDirty = false; setWork(row); draw(); schedulePreview(0); }
      return;
    }
    if (ds.fdNew !== undefined) { if (t.disabled) return; return openNewDialog(); }
    if (ds.fdSaveGroup !== undefined) { saveGroup(); return; }
    if (ds.fdSave !== undefined) { save(ds.fdSave === '1'); return; }
    if (ds.fdActivate !== undefined) { if (guardDirty()) postAction('/api/templates/activate', { id: Number(ds.fdActivate) }, 'قالب فعال شد؛ فیش بعدی با همین قالب چاپ می‌شود.'); return; }
    if (ds.fdDeltpl !== undefined) {
      if (window.confirm('این قالب حذف شود؟')) postAction('/api/templates/delete', { id: Number(ds.fdDeltpl) }, 'قالب حذف شد.');
      return;
    }
    if (ds.fdTestprint !== undefined) {
      if (S.dirty && !window.confirm('چاپ آزمایشی از نسخهٔ ذخیره‌شدهٔ قالب انجام می‌شود، نه تغییرات ذخیره‌نشده. ادامه؟')) return;
      callApi('/api/test-print', { method: 'POST', body: JSON.stringify({ template_id: S.work.id }) }).then((x) => say(x.message || 'چاپ آزمایشی ارسال شد.')).catch((e) => say((e && e.message) || 'چاپ آزمایشی ناموفق بود'));
      return;
    }
    if (ds.fdPreview !== undefined) { runPreview(); draw(); return; }
    if (ds.fdZoom !== undefined) { S.scale = Math.max(2, Math.min(8, S.scale + Number(ds.fdZoom))); draw(); return; }
    if (ds.fdAddfield) { addElement(ds.fdAddfield); touch(); return; }
    if (ds.fdUp !== undefined || ds.fdDown !== undefined) {
      ev.stopPropagation();
      const id = ds.fdUp !== undefined ? ds.fdUp : ds.fdDown;
      const arr = S.work.template.elements;
      const i = arr.findIndex((e) => e.id === id);
      const j = ds.fdUp !== undefined ? i - 1 : i + 1;
      if (i >= 0 && j >= 0 && j < arr.length) { const x = arr[i]; arr[i] = arr[j]; arr[j] = x; touch(); }
      return;
    }
    if (ds.fdDelete !== undefined) {
      S.work.template.elements = S.work.template.elements.filter((e) => e.id !== ds.fdDelete);
      S.sel = (S.work.template.elements[0] || {}).id || '';
      touch(); return;
    }
    if (ds.fdLogoRemove !== undefined) {
      const fd = new FormData(); fd.append('remove', '1');
      fetch((window.FOOD_TICKET_API_BASE || '') + encodeURIComponent('templates/logo'), { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': window.FOOD_TICKET_CSRF || '' }, body: fd })
        .then((r) => r.json()).then((j) => { S.brand = j.brand || S.brand; if (S.work.template.logo_source === 'ticket') S.dirty = true; draw(); schedulePreview(200); }).catch(() => say('حذف لوگو ناموفق بود'));
      return;
    }
    if (ds.fdPick !== undefined || ds.fdEl !== undefined) {
      const id = ds.fdPick !== undefined ? ds.fdPick : ds.fdEl;
      if (id !== S.sel) { S.sel = id; draw(); }
    }
  });

  document.addEventListener('change', (ev) => {
    if (!within(ev.target) || !S.work) return;
    const d = ev.target.dataset;
    if (ev.target.id === 'fd-logo-file') { const f = ev.target.files && ev.target.files[0]; if (f) uploadLogo(f); return; }
    if (d.fdToggle) {
      const key = d.fdToggle;
      const mine = S.work.template.elements.filter((e) => e.field === key);
      if (ev.target.checked) {
        if (mine.length) mine.forEach((e) => { e.enabled = true; }); else addElement(key);
        if (mine.length) S.sel = mine[0].id;
      } else {
        mine.forEach((e) => { e.enabled = false; });
      }
      touch(); return;
    }
    if (d.fdMeta) {
      const k = d.fdMeta; const w = S.work; const t = w.template;
      const v = ev.target.type === 'checkbox' ? ev.target.checked : ev.target.value;
      if (k === 'name') w.name = String(v);
      else if (k === 'paper_width') w.paper_width = Math.max(20, Math.min(120, num(v, 80)));
      else if (k === 'paper_height') w.paper_height = Math.max(15, Math.min(600, num(v, 50)));
      else if (k === 'variable') w.paper_height = v ? 0 : Math.max(15, r1(paperH()));
      else if (k === 'cut_paper') t.cut_paper = !!v;
      else if (k === 'border') t.border = !!v;
      else if (k === 'border_inset') t.border_inset = Math.max(0, Math.min(20, num(v, 4)));
      else if (k === 'logo_source') t.logo_source = v === 'ticket' ? 'ticket' : 'system';
      else if (k === 'name_source') t.name_source = v === 'custom' ? 'custom' : 'system';
      else if (k === 'custom_name') t.custom_name = String(v);
      touch(); return;
    }
    if (d.fdProp) {
      const e = selected(); if (!e) return;
      const k = d.fdProp;
      const v = ev.target.type === 'checkbox' ? ev.target.checked : ev.target.value;
      if (['x', 'y', 'width', 'height', 'font_size', 'line_height'].includes(k)) e[k] = r1(Math.max(k === 'height' ? 0.5 : 0, num(v, e[k])) * (k === 'line_height' ? 1 : 1));
      else if (k === 'bold' || k === 'border' || k === 'enabled' || k === 'show_label') e[k] = !!v;
      else e[k] = String(v);
      if (k === 'line_height') e[k] = Math.round(num(v, 1.2) * 100) / 100;
      touch(); return;
    }
  });

  // drag & resize (مختصات به میلی‌متر، گام ۰٫۵)
  document.addEventListener('pointerdown', (ev) => {
    if (!within(ev.target) || !S.work) return;
    if (ev.target.closest('[data-fd-group-style-pick]')) return;
    const handle = ev.target.closest('[data-fd-resize]');
    const node = ev.target.closest('[data-fd-el]');
    if (!node) return;
    const e = S.work.template.elements.find((x) => x.id === node.dataset.fdEl);
    if (!e) return;
    S.sel = e.id;
    S.drag = { e, mode: handle ? 'resize' : 'move', sx: ev.clientX, sy: ev.clientY, x: e.x, y: e.y, w: e.width, h: e.height, node, moved: false };
    try { node.setPointerCapture(ev.pointerId); } catch (x) { /* ignore */ }
    if (!handle) { /* انتخاب بدون بازرسم تا کشیدن قطع نشود */
      document.querySelectorAll('.fd-el.fd-sel').forEach((n) => n.classList.remove('fd-sel'));
      node.classList.add('fd-sel');
    }
    ev.preventDefault();
  });
  document.addEventListener('pointermove', (ev) => {
    const d = S.drag; if (!d) return;
    const dx = (ev.clientX - d.sx) / S.scale, dy = (ev.clientY - d.sy) / S.scale;
    const snap = (v) => Math.round(v * 2) / 2;
    const W = Number(S.work.paper_width) || 80;
    if (d.mode === 'move') {
      d.e.x = Math.max(0, Math.min(W - 1, snap(d.x + dx)));
      d.e.y = Math.max(0, snap(d.y + dy));
      d.node.style.left = d.e.x * S.scale + 'px';
      d.node.style.top = d.e.y * S.scale + 'px';
    } else {
      d.e.width = Math.max(1, snap(d.w + dx));
      d.e.height = Math.max(0.5, snap(d.h + dy));
      d.node.style.width = d.e.width * S.scale + 'px';
      d.node.style.height = d.e.height * S.scale + 'px';
    }
    d.moved = true;
  });
  document.addEventListener('pointerup', () => {
    const d = S.drag; if (!d) return;
    S.drag = null;
    if (d.moved) touch(); else draw();
  });
  document.addEventListener('keydown', (ev) => {
    if (!S.work || !root() || !['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(ev.key)) return;
    const a = document.activeElement;
    if (a && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName)) return;
    const e = selected(); if (!e) return;
    const st = ev.shiftKey ? 2 : 0.5;
    if (ev.key === 'ArrowLeft') e.x = Math.max(0, r1(e.x - st));
    if (ev.key === 'ArrowRight') e.x = r1(e.x + st);
    if (ev.key === 'ArrowUp') e.y = Math.max(0, r1(e.y - st));
    if (ev.key === 'ArrowDown') e.y = r1(e.y + st);
    ev.preventDefault(); touch();
  });

  // ───────────────────────── قالب جدید ─────────────────────────
  function openNewDialog() {
    if (S.templates.length >= MAX_TEMPLATES) { say('حداکثر ' + MAX_TEMPLATES + ' قالب فیش قابل ذخیره است؛ ابتدا یک قالب را حذف کنید.'); return; }
    if (!guardDirty()) return;
    const layer = document.createElement('div');
    layer.style.cssText = 'position:fixed;inset:0;background:#0006;z-index:60;display:flex;align-items:center;justify-content:center;direction:rtl';
    const opts = Object.keys(PRESETS).map((k) => '<label style="display:flex;gap:8px;margin:6px 0"><input type="radio" name="fd-preset" value="' + k + '" ' + (k === 'small50' ? 'checked' : '') + '> ' + esc(PRESETS[k].label) + '</label>').join('');
    layer.innerHTML = '<div style="background:#fff;border-radius:14px;padding:18px;min-width:320px;max-width:92vw"><h3 style="margin:0 0 10px">قالب جدید</h3>' +
      '<label style="display:block;font-size:12px">نام قالب<input id="fd-new-name" type="text" style="width:100%;box-sizing:border-box;border:1px solid #cbd8df;border-radius:8px;padding:6px;margin:4px 0 10px" value="قالب ' + (S.templates.length + 1) + '"></label>' + opts +
      '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px"><button type="button" class="fd-btn" id="fd-new-cancel">انصراف</button><button type="button" class="fd-btn primary" id="fd-new-ok">ساخت قالب</button></div></div>';
    document.body.appendChild(layer);
    layer.querySelector('#fd-new-cancel').onclick = () => layer.remove();
    layer.querySelector('#fd-new-ok').onclick = () => {
      const key = (layer.querySelector('input[name="fd-preset"]:checked') || {}).value || 'blank';
      const p = PRESETS[key];
      const name = layer.querySelector('#fd-new-name').value.trim() || ('قالب ' + (S.templates.length + 1));
      layer.remove();
      S.work = { id: 0, name, paper_width: p.w, paper_height: p.h, template: p.build() };
      S.currentId = 0; S.dirty = true; S.individualDirty = true; S.individualWork = workFrom(S.work); S.preview = null;
      S.sel = (S.work.template.elements.find((e) => e.enabled) || {}).id || '';
      draw(); schedulePreview(0);
    };
  }

  window.FoodDesignerUI = { html, mount, reload: () => { S.loaded = false; S.loading = false; S.work = null; load(); } };
})();
