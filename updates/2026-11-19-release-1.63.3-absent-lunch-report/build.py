#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""سازندهٔ بستهٔ نهایی بهروزرسانی ۱٫۶۳٫۳ — تب «غایبین ناهار» + مشخصات تأییدشدهٔ نهایی.

منابع در src/، خروجی در files/، manifest.json و
downloads/ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip(+.sha256).

هیچ فایل محصولی در شاخه دستکاری نمیشود؛ فقط بسته ساخته میشود (سامانهٔ زنده همان ۱٫۶۳٫۲).
"""
from __future__ import annotations
import hashlib, json, re, zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PKG = ROOT / 'updates/2026-11-19-release-1.63.3-absent-lunch-report'
SRC = PKG / 'src'
FILES = PKG / 'files'
DOWNLOADS = ROOT / 'downloads'
ZIP_PATH = DOWNLOADS / 'ticket-update-1.63.2-to-1.63.3-absent-lunch-report-20261119.zip'
VERSION = '1.63.3'
REQUIRES = '1.63.2'
PANEL_VERSION = '1.37.58'
DATE = '2026-11-19'

def sha256(b: bytes) -> str:
    return hashlib.sha256(b).hexdigest()

def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')

def must_replace(text: str, old: str, new: str, label: str, count: int = 1) -> str:
    n = text.count(old)
    if n != count:
        raise SystemExit(f'{label}: انتظار {count} رخداد، پیدا شد {n}')
    return text.replace(old, new, count)

def src(name: str) -> str:
    return (SRC / name).read_text(encoding='utf-8')

# ---------------------------------------------------------------- 1) بکاند (بدون تغییر، فقط ناهار)
backend = read('food-ticket.php')
# ۱.۶۳٫۳ — سازنده نسبت به فایل‌های از پیش built شده مقاوم است: یا روی مبنای ۱٫۶۳٫۲
# بازسازی می‌کند، یا اگر داکروت/شاخه از قبل نسخهٔ نهایی را دارد (بازساخت بستهٔ in-flight)
# آن را همان‌طور می‌پذیرد.
if "const FOOD_TICKET_PANEL_VERSION = '1.37.53';" in backend:
    fn_re = re.compile(r'function food_ticket_api_absent\(string \$fromDate.*?\n\}\n', re.S)
    m = fn_re.search(backend)
    if not m:
        raise SystemExit('تابع food_ticket_api_absent در food-ticket.php پیدا نشد')
    enhanced = src('backend_absent.php').strip() + '\n'
    backend = backend[:m.start()] + enhanced + backend[m.end():]
    backend = must_replace(backend, "const FOOD_TICKET_PANEL_VERSION = '1.37.53';",
                           f"const FOOD_TICKET_PANEL_VERSION = '{PANEL_VERSION}';", 'panel version php')
    print('backend rebuilt from 1.63.2 base')
elif "const FOOD_TICKET_PANEL_VERSION = '1.37.58';" in backend:
    print('backend already built (1.37.58) — using as-is')
else:
    raise SystemExit('نسخهٔ پنل food-ticket.php نه 1.37.53 است نه 1.37.58؛ بازسازی متوقف شد')

# ---------------------------------------------------------------- 2) پنل HTML
html = read('food-ticket-web/index.html')
if "PANEL_VERSION='1.37.53'" in html:
    _html_rebuild = True
    html = must_replace(html, "PANEL_VERSION='1.37.53'", f"PANEL_VERSION='{PANEL_VERSION}'", 'panel version html')
elif "PANEL_VERSION='1.37.58'" in html and 'window.AbsentLunchReport' in html:
    _html_rebuild = False
    print('panel html already built (1.37.58) — using as-is')
else:
    raise SystemExit('panel HTML نه 1.37.53 است نه built 1.37.58؛ بازسازی متوقف شد')

if _html_rebuild:
    tabs_old = "const REPORT_TABS=[['classic','گزارشگیری'],['aggregate','گزارش تجمیعی'],['delivered','غذای تحویلی'],['statistics','آمار سفارشها'],['todayorders','سفارشهای امروز'],['reconciliation','تطبیق خرید و توزیع']];"
    tabs_new = tabs_old[:-2] + ",['absent-lunch','غایبین ناهار']];"
    html = must_replace(html, tabs_old, tabs_new, 'REPORT_TABS')

    html = must_replace(html, ' else body=reportClassicView();',
        " else if(tab==='absent-lunch')body=window.AbsentLunchReport?window.AbsentLunchReport.html():'<section class=\"card\"><p>در حال بارگذاری تب غایبین ناهار…</p></section>';else body=reportClassicView();",
        'reportView dispatch')

    html = must_replace(html, 'function render(){const pages={',
        "function render(){if(state.report&&state.report.type==='absent'){state.report.type='attendance';state.reportTab='absent-lunch';persist()}const pages={",
        'render normalization')

    render_old = "if(view==='reports'){ensureReportRange();if((state.reportTab||'classic')==='aggregate')loadAggregate(false);else if(!FOOD_REPORT_TABS[state.reportTab]&&state.report&&state.report.type==='absent')loadAbsent(false)}"
    render_new = "if(view==='reports'){ensureReportRange();if((state.reportTab||'classic')==='aggregate')loadAggregate(false);else if((state.reportTab||'classic')==='absent-lunch'&&window.AbsentLunchReport)window.AbsentLunchReport.load(false);else if(!FOOD_REPORT_TABS[state.reportTab]&&state.report&&state.report.type==='absent')loadAbsent(false)}"
    html = must_replace(html, render_old, render_new, 'render loader hook')

    # گزینهٔ قدیمی «نوع گزارش = غایبین» از فرم حذف میشود (نسخهٔ زنده مخزن اکنون فقط یک فرم دارد)
    opt_re = re.compile(r'<option value="absent" \$\{r\.type===\'absent\'\?\'selected\':\'\'\}>غایبین[^<]*</option>')
    html, n_opt = opt_re.subn('', html)
    if n_opt != 1:
        raise SystemExit(f'حذف گزینهٔ absent: انتظار ۱، پیدا شد {n_opt}')

    # پنج چیپ آمار فقط داخل فرم «گزارشگیری» (نه صفحهٔ غایبین)
    classic_decl = "const rows=currentReportRows().rows,"
    classic_chips = ("const rows=currentReportRows().rows,classicChips=`<div class=\"alr-classic-chips\">"
        "<span class=\"alr-chip\" data-classic-chip=\"records\">تعداد رکورد <b>${toFaDigits(rows.length)}</b></span>"
        "<span class=\"alr-chip\" data-classic-chip=\"printed\">چاپشده <b>${toFaDigits(rows.filter(x=>x.status==='printed'||String(x.result||'').includes('چاپ')).length)}</b></span>"
        "<span class=\"alr-chip\" data-classic-chip=\"auto\">چاپ خودکار <b>${toFaDigits(rows.filter(x=>String(x.result||'').includes('خودکار')).length)}</b></span>"
        "<span class=\"alr-chip\" data-classic-chip=\"nofood\">بدون غذا <b>${toFaDigits(rows.filter(x=>!x.food).length)}</b></span>"
        "<span class=\"alr-chip\" data-classic-chip=\"errors\">خطا <b>${toFaDigits(rows.filter(x=>x.status==='error'||String(x.result||'').includes('خطا')).length)}</b></span>"
        "</div>`,")
    html = must_replace(html, classic_decl, classic_chips, 'classic chips declaration')
    form_pat = re.compile(r'(<form class="card form-grid" id="report-filter">.*?<div class="form-actions"><button class="btn primary">نمایش گزارش</button></div>)(</form>)', re.S)
    html, n_form = form_pat.subn(lambda m: m.group(1) + '${classicChips}' + m.group(2), html, count=1)
    if n_form != 1:
        raise SystemExit(f'جایگذاری چیپها در فرم گزارشگیری: انتظار ۱، پیدا شد {n_form}')

    # استایل و ماژول نهایی inline (بدون asset تازه ⇒ نیازی به پین کش نیست)
    html = must_replace(html, '</head>', '<style>\n' + src('absent-lunch.css') + '</style>\n</head>', 'inject css')
    html = must_replace(html, '</body></html>', '<script>\n' + src('absent-lunch-module.js') + '\n</script>\n</body></html>', 'inject js')

else:
    print('html transformations skipped (already built)')

# ---------------------------------------------------------------- 3) راهنما و چتبات
guide = read('help-guide.php')
if src('help-guide-section.html') in guide:
    print('help-guide.php already built — using as-is')
else:
    guide = must_replace(guide, '<h2 id="support">', src('help-guide-section.html') + '\n<h2 id="support">', 'help guide')
chat = read('chatbot.php')
if 'food_absent_lunch_report' in chat:
    print('chatbot.php already built — using as-is')
else:
    chat = must_replace(chat, '    $flow = chatbot_flow_active();', src('chatbot-intent.php') + '    $flow = chatbot_flow_active();', 'chatbot intent')

# ---------------------------------------------------------------- 4) نوشتن خروجی
FILES.mkdir(parents=True, exist_ok=True)
# ۱٫۶۳٫۳ — کل بند ۱۷ روی همان نسخه: کپی فایل‌های محصولیِ تغییرکرده
EXTRA_FILES = [
    'assets/food-menu.css',
    'assets/food-menu.js',
    'assets/food-order-guest.js',
    'assets/food-org-groups.js',
    'cd-dvd.php',
    'food-order-import.php',
    'food-order.php',
    'food-reconciliation.php',
    'food-ticket-auto-receipts.php',
    'food-ticket-groups.php',
    'food-ticket-runtime.php',
    'food-ticket-web/assets/food-menu.css',
    'food-ticket-web/assets/food-menu.js',
    'food-ticket-web/assets/food-org-groups.js',
    'lib/org-grouping.php',
    'organization.php',
    'index.php',
    'modules/request-center/Actions/ticket-actions.php',
    'modules/request-center/Pages/create.php',
    'modules/request-center/Pages/detail.php',
    'modules/request-center/RequestCenterRepository.php',
    'modules/request-center/lib/functions.php',
]
MIGRATIONS = ['038-food-guest-actuals.sql', '039-liaison-manager-role-sync.sql']
for rel in EXTRA_FILES:
    src_path = ROOT / rel
    if not src_path.is_file():
        raise SystemExit(f'فایل محصولی پیدا نشد: {rel}')
    dst = FILES / rel
    dst.parent.mkdir(parents=True, exist_ok=True)
    dst.write_bytes(src_path.read_bytes())
for name in MIGRATIONS:
    if not (ROOT / 'migrations' / name).is_file():
        raise SystemExit(f'مهاجرت پیدا نشد: {name}')
(FILES / 'VERSION').write_text(VERSION + '\n', encoding='utf-8')
(FILES / 'food-ticket.php').write_text(backend, encoding='utf-8')
(FILES / 'food-ticket-web/index.html').write_text(html, encoding='utf-8')
(FILES / 'help-guide.php').write_text(guide, encoding='utf-8')
(FILES / 'chatbot.php').write_text(chat, encoding='utf-8')
(FILES / 'downloads').mkdir(exist_ok=True)
(FILES / 'downloads/CHANGE-REPORT-FA-1.63.3.md').write_text(src('CHANGE-REPORT-FA.md'), encoding='utf-8')
(DOWNLOADS / 'CHANGE-REPORT-FA-1.63.3.md').write_text(src('CHANGE-REPORT-FA.md'), encoding='utf-8')
(PKG / 'README.md').write_text(src('README.md'), encoding='utf-8')
(PKG / 'START.txt').write_text(src('START.txt'), encoding='utf-8')
(PKG / 'CHANGE-REPORT-FA.md').write_text(src('CHANGE-REPORT-FA.md'), encoding='utf-8')
for rel in ('tests/js/food-absent-lunch-tab.test.js', 'tests/js/food-absent-lunch-runtime.test.js',
            'tests/test_food_absent_lunch_tab_static.py', 'tests/test_food_absent_lunch_backend_static.py',
            'tests/test_auto_section_attribution_static.py'):
    out = FILES / rel
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text((SRC / 'tests' / Path(rel).name if not rel.startswith('tests/js/') else SRC / rel).read_text(encoding='utf-8'), encoding='utf-8')

FILES_LIST = [
    'VERSION', 'chatbot.php', 'downloads/CHANGE-REPORT-FA-1.63.3.md',
    'food-ticket-web/index.html', 'food-ticket.php', 'help-guide.php',
    'tests/js/food-absent-lunch-tab.test.js', 'tests/js/food-absent-lunch-runtime.test.js',
    'tests/test_food_absent_lunch_backend_static.py', 'tests/test_food_absent_lunch_tab_static.py',
    'tests/test_auto_section_attribution_static.py',
] + EXTRA_FILES
hashes = {rel: sha256((FILES / rel).read_bytes()) for rel in FILES_LIST}
manifest = {
    'name': 'absent-lunch-report-and-guest-day-close',
    'version': VERSION,
    'requires_version': REQUIRES,
    'date': DATE,
    'title': 'تب «غایبین ناهار» + اصلاحات نهایی (بند ۱۷): بستن روز، انتساب مهمان به ۶ بخش، یک‌دست‌سازی بخش‌های سازمانی',
    'panel_version': PANEL_VERSION,
    'migrations': MIGRATIONS,
    'files': FILES_LIST,
    'sha256': hashes,
    'description': ('Final 1.63.3: absent-lunch report tab (three subviews, per-deputy checkboxes + "all deputies", '
                    'pure print, XLSX) + full section-17 scope: single-source remainder at day close, exact-equality guest '
                    'actuals popup (6 org roots + conditional "no anchor" row), chart-less guest registrations blocked, '
                    '6-section org lists everywhere, partner-company manager and CEO subtree visibility for guest requests.'),
    'notes': ('پیشنیاز ۱٫۶۳٫۲؛ دارای دو مهاجرتِ idempotent است: 038-food-guest-actuals.sql (جدول food_guest_actuals) '
              'و 039-liaison-manager-role-sync.sql (ارتقای مدیران واحدهای مستقل به نقش «مدیر»). پشتیبان و نصب آزمایشی الزامی. '
              'بستهٔ نهایی جایگزین بستهٔ اولیهٔ همان ورژن شد؛ SHA تازه است. '
              'نسخهٔ پنل ۱٫۳۷٫۵۸؛ پس از نصب Ctrl+F5 کنید. داده‌های تاریخی NULL مهمان هرگز بازنویسی نمی‌شوند.'),
}
(PKG / 'manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')

# ---------------------------------------------------------------- 5) ZIP + sidecar
DOWNLOADS.mkdir(exist_ok=True)
with zipfile.ZipFile(ZIP_PATH, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
    z.write(PKG / 'manifest.json', 'manifest.json')
    for name in MIGRATIONS:
        z.write(ROOT / 'migrations' / name, 'migrations/' + name)
    for rel in FILES_LIST:
        z.write(FILES / rel, 'files/' + rel)
zip_bytes = ZIP_PATH.read_bytes()
(ZIP_PATH.with_suffix(ZIP_PATH.suffix + '.sha256')).write_text(sha256(zip_bytes) + '  ' + ZIP_PATH.name + '\n', encoding='utf-8')
print('WROTE', ZIP_PATH, len(zip_bytes), 'bytes')
print('SHA256', sha256(zip_bytes))
print('files', len(FILES_LIST) + 1 + len(MIGRATIONS))
