#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""سازندهٔ نقشهٔ ماشینی سامانه — تمام ایندکسها را از روی کد واقعی میسازد.

خروجیها در docs/system-map/ نوشته میشوند:
  FILES.tsv      هر فایل + دسته + تعداد خط + حجم
  SYMBOLS.tsv    تابع/کلاس/ثابت → فایل:خط (PHP, JS, Python, PowerShell)
  ROUTES.tsv     صفحهها (?page=), اکشنهای POST, endpointهای API → فایل:خط
  TABLES.tsv     جدولهای SQL → فایل:خط تعریف
  UPDATES.tsv    بستههای بهروزرسانی (نسخه، نیاز به نسخه، تعداد فایل/migration)
  FEATURES.tsv   ایندکس معکوس «ویژگی/واژهٔ فارسی → فایلهای دقیق»
  summary.json   آمار کلی

استفاده:
  python3 tools/build_system_map.py           # بازتولید ایندکسها
  python3 tools/build_system_map.py --check   # فقط بررسی تازگی (بدون نوشتن)

قاعده: این اسکریپت منبع حقیقت است. docs/SYSTEM-MAP-FA.md توضیح انسانی آن است.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT_DIR = ROOT / "docs" / "system-map"

# پوشههایی که محتوایشان تاریخچه/خروجی است و وارد ایندکس کد نمیشوند.
EXCLUDE_DIRS = {".git", "__pycache__", ".pytest_cache", "node_modules", "Unselected files"}
HISTORY_DIRS = {"updates", "downloads", "benchmark-results", "preview", "storage"}

TEXT_EXT = {".php", ".js", ".css", ".py", ".sql", ".md", ".txt", ".json", ".yml", ".yaml",
            ".html", ".ps1", ".bat", ".cmd", ".sh", ".csv", ".ics", ".htaccess", ".example", ""}

CATEGORY_RULES = [
    ("actions", lambda p: p.parts[0] == "actions"),
    ("lib", lambda p: p.parts[0] == "lib"),
    ("cron", lambda p: p.parts[0] == "cron"),
    ("api", lambda p: p.parts[0] == "api"),
    ("routes", lambda p: p.parts[0] == "routes"),
    ("migrations", lambda p: p.parts[0] == "migrations"),
    ("tests", lambda p: p.parts[0] == "tests"),
    ("docs", lambda p: p.parts[0] == "docs"),
    ("tools", lambda p: p.parts[0] == "tools"),
    ("assets", lambda p: p.parts[0] == "assets"),
    ("food-web", lambda p: p.parts[0] == "food-ticket-web"),
    ("app-mvc", lambda p: p.parts[0] == "app"),
    ("sql-root", lambda p: len(p.parts) == 1 and p.suffix == ".sql"),
    ("workflows", lambda p: p.parts[0] == ".github"),
    ("client-agent", lambda p: p.parts[0] == "client-agent"),
    ("history-updates", lambda p: p.parts[0] == "updates"),
    ("history-downloads", lambda p: p.parts[0] == "downloads"),
    ("benchmark", lambda p: p.parts[0] == "benchmark-results"),
    ("preview", lambda p: p.parts[0] == "preview"),
    ("config", lambda p: p.name in {"config.sample.php", ".env.example", ".gitignore", ".htaccess", "VERSION"}),
]

# ایندکس معکوس ویژگیها. کلید = واژههایی که کاربر ممکن است بگوید (فارسی/انگلیسی).
# مقدار = (عنوان, فایلهای اصلی, جداول مرتبط, یادداشت)
FEATURES: list[tuple[str, str, str, str, str]] = [
    ("ورود|لاگین|login|احراز هویت|نشست|session|رمز عبور",
     "ورود و نشست", "actions/account-actions.php;lib/auth-scope.php;session_bootstrap.php;lib/db-core.php;lib/ldap.php;index.php",
     "users;login_attempts;api_rate_limits", "قفل ۵ تلاش ناموفق؛ سشن ۳۶۵ روزه؛ LDAP/AD"),
    ("دسترسی|نقش|مجوز|permission|role|سوپروایزر|ادمین اصلی",
     "نقشها و دسترسیها", "permissions.php;actions/role-actions.php;index.php",
     "role_permissions;users", "هر قابلیت جدید باید در permission_catalog ثبت شود"),
    ("درخواست|تیکت|ticket|ثبت درخواست|ارجاع|پاسخ|وضعیت درخواست",
     "چرخهٔ درخواستها", "index.php;actions/ticket-actions.php;lib/tickets-sla.php;lib/activity-audit.php",
     "tickets;ticket_messages;ticket_events;ticket_attachments;ticket_ratings;ticket_number_seq",
     "واژهٔ رابط کاربر «درخواست» است نه «تیکت»"),
    ("صف کاری|queue|کارپوشه|عملیات گروهی|bulk",
     "صف کاری و عملیات گروهی", "index.php;actions/bulk-actions.php;actions/ticket-actions.php",
     "tickets", "فیلتر بدون مسئول / خارج از SLA / مسئول من"),
    ("SLA|OLA|مهلت|تعطیلات|تقویم|زمانبندی|هشدار",
     "SLA/OLA و تقویم", "lib/tickets-sla.php;ola.php;actions/holiday-actions.php;cron/sla_maintenance.php;assets/jalali-calendar.js",
     "ticket_sla_alerts;ticket_ola_alerts;ola_policies;holidays;change_records",
     "پنجشنبه/جمعه مصرف SLA نمیکنند"),
    ("تأیید نهایی|سوپروایزر|بازگشایی|supervisor",
     "پنل سوپروایزر", "actions/supervisor-actions.php;index.php",
     "tickets;ticket_ratings", "بستن نهایی قبل از امتیاز ممکن نیست"),
    ("خدمات|کاتالوگ|service|دستهبندی|فرم پویا",
     "کاتالوگ خدمات", "index.php;actions/service-actions.php",
     "service_catalog;service_catalog_fields;categories;handling_units",
     "دو حوزه: خدمات IT و خدمات پشتیبانی"),
    ("دانشنامه|knowledge|راهنما|help|مقاله",
     "دانشنامه و راهنما", "index.php;help-guide.php;chatbot.php",
     "knowledge_articles", "چتبات h2/h3 و data-kw راهنما را ایندکس میکند"),
    ("اعلان|notification|پاپآپ|اطلاعرسانی",
     "اعلانهای داخلی آفلاین", "lib/notify-users.php;actions/notification-actions.php;index.php",
     "notifications", "بدون ایمیل/پیامک؛ بررسی هر ۳۰ ثانیه"),
    ("دارایی|شناسنامه|asset|inventory|سختافزار|RAM|GPU|CMDB",
     "دارایی و شناسنامهٔ فنی", "asset-profile.php;inventory.php;asset-inventory-page.php;asset-names.php;actions/asset-actions.php;api/inventory.php",
     "assets;asset_profiles;asset_relations;asset_history_events;asset_inventory_history;asset_memory_modules;asset_storage_devices;asset_graphics_adapters;asset_peripherals",
     "فرم چرخهعمر/خرید/اسقاط در UI حذف شده است"),
    ("اسکن دامنه|domain|پینگ|ping|شبکه|LDAP|AD",
     "اسکن دامنه و شبکه", "domain-scan.php;cron/domain_scan_worker.php;lib/ldap.php;actions/asset-actions.php;client-agent/Collect-Inventory.ps1",
     "domain_scan_runs;domain_scan_queue", "Agent PowerShell اینونتوری میفرستد"),
    ("سازمان|چارت|org|معاونت|واحد|نماینده|مدیر واحد",
     "ساختار سازمانی", "organization.php;actions/org-actions.php;assets/organization.js",
     "org_units;org_unit_managers;departments;handling_units",
     "واحد مستقل (نمایندگی) جایگاه چپ/راست دارد؛ migration 010"),
    ("تغییر|مشکل|change|problem|CAB|governance|حاکمیت",
     "مدیریت تغییر و مشکل", "governance.php;index.php",
     "change_records;change_approvals;change_ticket_links;problem_records;problem_ticket_links",
     "صفحهای مستقل که require مستقیم bootstrap دارد"),
    ("تردد|ترافیک|traffic|ملاقات|مقصد|تأییدکننده",
     "کنترل تردد عمومی", "traffic-control.php;traffic-import.php;assets/traffic-control.js",
     "traffic_visits;traffic_destinations;traffic_visit_history",
     "۱ تا ۳ تأییدکننده (migration 009)؛ ورود اکسل سوابق قدیمی"),
    ("CD|DVD|رسانه|دیسک|آرشیو",
     "آرشیو CD/DVD", "cd-dvd.php;actions/cd-dvd-actions.php;assets/cd-dvd.js",
     "cd_dvd_records;cd_dvd_types", "شماره خودکار P86-0001..2000"),
    ("غذا|فیش|food|سفارش غذا|منو|وعده|ناهار|شام",
     "ماژول سفارش غذا (کلی)", "food-order.php;food-order-import.php;food-ticket.php;food-ticket-order-source.php;assets/food-menu.js;assets/food-order.js",
     "food_orders;food_catalog;food_calendar;food_calendar_items;food_order_logs;food_order_cancel_requests;food_guest_requests",
     "منبع سفارش فقط MySQL داخلی است؛ Access سفارش حذف شد"),
    ("سامانه مدیریت غذا|پنل غذا|food-ticket|داشبورد غذا|Capitalio",
     "سامانه مدیریت غذا (پنل ادمین)", "food-ticket.php;food-ticket-web/index.html;food-ticket-web-host.php;food-ticket-web/assets/",
     "food_ticket_config;food_ticket_events", "نام قدیمی «چاپ فیش غذا» منسوخ است"),
    ("موتور پایش|تطبیق|TENTER|Access|تردد دستگاه|UID|L_UID",
     "موتور پایش تردد و تطبیق", "food-ticket-engine.php;food-ticket-env.php;food-ticket-runtime.php;cron/food_ticket_worker.php",
     "food_ticket_events;food_ticket_card_map;food_ticket_group_uid_history",
     "تنها منبع خارجی باقیمانده = فایل Access TENTER (جدول C_Date/C_Time/UID)"),
    ("صف چاپ|چاپ فیش|پرینتر|netprint|TCP 9100|قالب فیش|کالیبراسیون",
     "صف چاپ و چاپ فیش", "food-ticket-runtime.php;food-ticket-templates.php;food-ticket-netprint.php;food-ticket-print_log-",
     "food_ticket_events;food_ticket_print_log;food_ticket_templates",
     "چاپ TCP 9100 و صف ویندوز؛ تنظیمات حاشیه در storage/food_ticket_netprint.json"),
    ("گروه غذایی|نماینده گروه|غیبت|ثبت دستی|حضور دستی|manual attendance",
     "گروههای غذایی و ثبت دستی", "food-ticket-groups.php;food-ticket-web/assets/food-groups.js;food-ticket-web/assets/food-manual-attendance.js",
     "food_ticket_groups;food_ticket_group_members;food_ticket_group_runs;food_ticket_daily_absence;food_ticket_day_visits",
     "نسخهٔ ۱.۶۲.۳۶: migration 013 فیشهای دستی قدیمی را delivered میکند"),
    ("قالب فیش|طراحی فیش|لوگو|برند غذا|template",
     "قالبها و هویت بصری غذا", "food-ticket-templates.php;food-ticket-web/assets/food-designer.js;actions/food-brand-actions.php;food-ticket-web/assets/food-brand.js",
     "food_ticket_templates", "تم شب/روز سراسری پنل غذا"),
    ("گزارش غذا|تحویل|فیش چاپشده|آمار غذا",
     "گزارشهای غذا", "food-ticket.php;food-ticket-web/assets/food-capitalio-dashboard-r42.js;food-order.php",
     "food_ticket_events;food_ticket_print_log;food_orders", "reconciliation هنگام خواندن گزارش اجرا میشود"),
    ("پرسنل|کارمند|کد پرسنلی|اتصال پرسنل|ایمپورت اکسل",
     "پرسنل و ورود اطلاعات", "food-personnel-link.php;food-ticket-import.php;local-accounts.php;food-order-import.php",
     "users;food_ticket_groups;food_orders", "ستون «famili» در ایمپورت Access حفظ شود"),
    ("چتبات|چت بات|chatbot|صدا|TTS|Piper|گوینده",
     "چتبات سخنگو", "chatbot.php;chatbot-tts-lexicon.php;assets/chatbot-drag.js;index.php",
     "chatbot_faq;chatbot_logs", "دانش را خودکار از help-guide.php میگیرد"),
    ("جستجوی سراسری|global search|search",
     "جستجوی سراسری", "global-search.php;index.php",
     "tickets;assets;knowledge_articles;traffic_visits;cd_dvd_records", "درخواست، دارایی، دانشنامه، تردد، CD"),
    ("گزارش|report|خروجی|Excel|XLSX|CSV|چارت|نمودار|آنالیتیکس",
     "گزارشها و خروجیها", "index.php;xlsx-writer.php;backup.php",
     "tickets;ticket_ratings", "خروجی همه گزارشها XLSX است (قاعدهٔ ثابت کاربر)"),
    ("پشتیبان|backup|restore|بازیابی|بهروزرسانی سامانه|updater|manifest",
     "پشتیبانی و بهروزرسانی", "backup.php;system-update.php;actions/backup-actions.php;tools/backup.ps1;docs/DOWNLOAD-PUBLISHING-POLICY-FA.md",
     "schema_updates", "ZIP فقط manifest.json + migrations/ + files/ میپذیرد"),
    ("سلامت سامانه|system-health|لاگ|log|خطا|حسابرسی|audit|فعالیت",
     "سلامت، لاگ و حسابرسی", "system-health.php;lib/activity-audit.php;lib/logging-csrf.php;cron/prune_activity_logs.php;index.php",
     "system_logs;activity_logs;system_incidents", "خروجی امن و خوانا؛ لاگها بدون تأیید حذف نمیشوند"),
    ("نصب|install|config|پیکربندی|دیتابیس خالی",
     "نصب و پیکربندی", "install.php;schema.sql;install-empty-db.sql;config.sample.php;.env.example;rebuild-final.sql",
     "settings", "install.php?force=1 خطرناک است؛ قبل از اجرا تأیید بگیر"),
    ("مهاجرت|migration|upgrade|ارتقا|schema_updates|ستون جدید",
     "مهاجرتهای پایگاهداده", "migrations/;schema.sql;install-empty-db.sql;upgrade-1.1-departments.sql",
     "schema_updates", "idempotent و افزایشی؛ هر سه محل (دو SQL + ensure_schema) همگام شوند"),
    ("کرون|cron|worker|زمانبند|job|سرویس",
     "کرونها و سرویسها", "cron/;install-food-worker-SERVICE.ps1;START-FOOD-WORKER.txt",
     "food_ticket_worker_status", "food_ticket_worker.php پیوسته؛ بقیه زمانبندیشده"),
    ("تم|theme|شب|روز|RTL|فونت|Vazirmatn|طراحی صفحه|layout",
     "ظاهر، تم و چیدمان", "assets/style.css;assets/app.js;assets/theme-mode.css;admin-layout-design.php;assets/admin-layout-design.js;assets/fonts/",
     "settings", "تغییر CSS/JS باید ?v= را در index.php بالا ببرد"),
    ("تست|test|آزمون|CI|benchmark|کارایی",
     "تستها و کیفیت", "tests/;tools/lint-php-syntax.php;.github/workflows/ci.yml;benchmark-results/",
     "—", "CI: PHP 8.2 + SecurityTest + ArchitectureTest + test_food_ticket_logic"),
    ("نسخه|version|انتشار|release|tag|changelog|بسته",
     "نسخهبندی و انتشار", "VERSION;updates/;downloads/;docs/RELEASE-1.62.36-FA.md;docs/HANDOFF-FA.md",
     "—", "شماره نسخه فقط با انتشار واقعی عوض میشود (قاعدهٔ ثابت)"),
    ("قوانین ثابت|قواعد کار|HANDOFF|باید|نباید",
     "قواعد ثابت کاربر", "docs/HANDOFF-FA.md;AGENTS.md;docs/ARCHITECTURE-FA.md",
     "—", "حتماً بند ۱ HANDOFF خوانده شود؛ پاسخ فارسی، XLSX، عدم حذف داده"),
    ("MVC|app/|router|controller|مدل قدیمی|کد مرده",
     "لایهٔ MVC بلااستفاده", "app/;routes/web.php",
     "—", "هیچ فایلی اینها را require نمیکند؛ روتر واقعی index.php است"),
]


def iter_files() -> list[Path]:
    result = []
    for path in ROOT.rglob("*"):
        if not path.is_file():
            continue
        rel = path.relative_to(ROOT)
        if any(part in EXCLUDE_DIRS for part in rel.parts):
            continue
        # خروجیهای خودِ سازنده را ایندکس نکن؛ وگرنه FIXED-POINT نداریم و
        # هر بار --check کهنه اعلام میکند (تولید فایل → تغییر لیست → ...).
        if rel.parts[:2] == ("docs", "system-map"):
            continue
        if rel.name.endswith((".zip", ".sha256", ".woff2", ".png", ".jpg", ".jpeg", ".gif", ".xlsx", ".log")):
            continue
        result.append(rel)
    return sorted(result, key=lambda p: str(p))


def categorize(rel: Path) -> str:
    for name, rule in CATEGORY_RULES:
        try:
            if rule(rel):
                return name
        except (IndexError, AttributeError):
            continue
    if len(rel.parts) == 1:
        return "core" if rel.suffix == ".php" else "root-misc"
    return "other"


def read_text(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8", errors="replace")
    except OSError:
        return ""


def count_lines(text: str) -> int:
    return text.count("\n") + (1 if text and not text.endswith("\n") else 0)


# --- استخراج نمادها ---------------------------------------------------------
PHP_SYMBOL_RE = re.compile(
    r"^\s*(?:(?:abstract|final|static|public|protected|private)\s+)*"
    r"(?:(function|class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)"
    r"|(?:const|define)\s*\(?\s*['\"]?([A-Za-z_][A-Za-z0-9_]*))",
    re.MULTILINE,
)
JS_SYMBOL_RE = re.compile(
    r"^\s*(?:(?:export\s+)?(?:async\s+)?function\s+([A-Za-z_$][\w$]*)"
    r"|(?:class)\s+([A-Za-z_$][\w$]*)"
    r"|(?:const|let|var)\s+([A-Za-z_$][\w$]*)\s*=\s*(?:async\s*)?(?:function|\([^)]*\)\s*=>|[A-Za-z_$][\w$]*\s*=>)"
    r"|window\.([A-Za-z_$][\w$]*)\s*=)",
    re.MULTILINE,
)
PY_SYMBOL_RE = re.compile(r"^\s*(def|class)\s+([A-Za-z_][A-Za-z0-9_]*)", re.MULTILINE)
PS_SYMBOL_RE = re.compile(r"^\s*function\s+([A-Za-z_][A-Za-z0-9_-]*)", re.MULTILINE | re.IGNORECASE)


def extract_symbols(rel: Path, text: str) -> list[tuple[str, str, int, str]]:
    """→ [(kind, name, line, category)]"""
    ext = rel.suffix.lower()
    out: list[tuple[str, str, int, str]] = []
    patterns = {
        ".php": (PHP_SYMBOL_RE, "php"),
        ".js": (JS_SYMBOL_RE, "js"),
        ".py": (PY_SYMBOL_RE, "py"),
        ".ps1": (PS_SYMBOL_RE, "ps1"),
    }
    if ext not in patterns:
        return out
    regex, lang = patterns[ext]
    for match in regex.finditer(text):
        line = text.count("\n", 0, match.start()) + 1
        groups = [g for g in match.groups() if g]
        if lang == "php":
            kind = (match.group(1) or ("const" if match.group(3) else "symbol")).lower()
            name = match.group(2) or match.group(3) or ""
        elif lang == "js":
            kind = "function"
            name = groups[0] if groups else ""
            if match.group(2):
                kind = "class"
            elif match.group(4):
                kind = "namespace"
        else:
            kind = match.group(1).lower()
            name = match.group(2) if lang == "py" else match.group(1)
        if not name:
            continue
        out.append((kind, name, line, categorize(rel)))
    # حذف تکراریها در یک خط
    seen: set[tuple[str, str, int]] = set()
    unique = []
    for kind, name, line, cat in out:
        key = (kind, name, line)
        if key not in seen:
            seen.add(key)
            unique.append((kind, name, line, cat))
    return unique


# --- استخراج روتها ----------------------------------------------------------
PAGE_RE = re.compile(r"\$page\s*===\s*'([a-z0-9-]+)'|\bpage=([a-z0-9-]+)")
ACTION_RE = re.compile(r"['\"]action['\"]\s*=>\s*['\"]([a-z0-9_-]+)['\"]|\$action\s*===\s*['\"]([a-z0-9_-]+)['\"]|case\s+'([a-z0-9_-]+)'\s*:")
ROUTE_ADD_RE = re.compile(r"\$router->add\(\s*'([^']*)'")
API_ROUTE_RE = re.compile(r"\$route\s*===\s*'([a-z0-9_/-]+)'|\$apiRoute\s*===\s*'([a-z0-9_/-]+)'")


def extract_routes(rel: Path, text: str) -> list[tuple[str, str, int, str]]:
    """→ [(نوع, نام, خط, فایل)]"""
    out: list[tuple[str, str, int, str]] = []
    str_rel = str(rel)
    seen: set[tuple[str, str]] = set()

    def add(kind: str, name: str, pos: int) -> None:
        key = (kind, name)
        if key in seen or not name:
            return
        seen.add(key)
        out.append((kind, name, text.count("\n", 0, pos) + 1, str_rel))

    if str_rel == "index.php" or str_rel.startswith("actions/") or str_rel in {
        "food-ticket.php", "food-order.php", "traffic-control.php", "governance.php", "ola.php"
    }:
        for m in PAGE_RE.finditer(text):
            add("page", m.group(1) or m.group(2), m.start())
        for m in ACTION_RE.finditer(text):
            add("action", next((g for g in m.groups() if g), ""), m.start())
        for m in API_ROUTE_RE.finditer(text):
            add("api", next((g for g in m.groups() if g), ""), m.start())
    if str_rel == "routes/web.php":
        for m in ROUTE_ADD_RE.finditer(text):
            add("mvc-route", m.group(1), m.start())
    return out


# --- استخراج جداول ----------------------------------------------------------
CREATE_TABLE_RE = re.compile(r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`\"]?([A-Za-z_][A-Za-z0-9_]*)[`\"]?", re.IGNORECASE)


def extract_tables(rel: Path, text: str) -> list[tuple[str, int, str]]:
    out = []
    for m in CREATE_TABLE_RE.finditer(text):
        out.append((m.group(1), text.count("\n", 0, m.start()) + 1, str(rel)))
    return out


# --- بستههای آپدیت ----------------------------------------------------------
def extract_updates() -> list[tuple[str, str, str, int, int, str]]:
    out = []
    upd = ROOT / "updates"
    if not upd.is_dir():
        return out
    for manifest in sorted(upd.glob("*/manifest.json")):
        try:
            data = json.loads(manifest.read_text(encoding="utf-8"))
        except (json.JSONDecodeError, OSError):
            continue
        out.append((
            manifest.parent.name,
            str(data.get("version", "")),
            str(data.get("requires_version", "")),
            len(data.get("files", []) or []),
            len(data.get("migrations", []) or []),
            str(data.get("date", "")),
        ))
    return out


def tsv(header: list[str], rows: list[list]) -> str:
    lines = ["\t".join(header)]
    for row in rows:
        lines.append("\t".join(str(cell).replace("\t", " ").replace("\n", " ") for cell in row))
    return "\n".join(lines) + "\n"


def build() -> dict[str, str]:
    files = iter_files()
    file_rows: list[list] = []
    symbol_rows: list[list] = []
    route_rows: list[list] = []
    table_rows: list[list] = []
    category_counts: dict[str, int] = {}
    total_lines = 0

    for rel in files:
        path = ROOT / rel
        text = read_text(path)
        lines = count_lines(text)
        cat = categorize(rel)
        size = path.stat().st_size
        file_rows.append([str(rel), cat, lines, size])
        category_counts[cat] = category_counts.get(cat, 0) + 1
        total_lines += lines
        if rel.suffix.lower() in {".php", ".js", ".py", ".ps1"}:
            for kind, name, line, scat in extract_symbols(rel, text):
                symbol_rows.append([name, kind, str(rel), line, scat])
        if rel.suffix.lower() in {".php"} or str(rel) == "routes/web.php":
            for kind, name, line, rfile in extract_routes(rel, text):
                route_rows.append([kind, name, rfile, line])
        if rel.suffix.lower() == ".sql":
            for name, line, tfile in extract_tables(rel, text):
                table_rows.append([name, tfile, line])
        # food_api routeها در فایلهای غذا
        if str(rel).startswith("food-"):
            for m in API_ROUTE_RE.finditer(text):
                name = next((g for g in m.groups() if g), "")
                if name:
                    route_rows.append(["api", name, str(rel), text.count("\n", 0, m.start()) + 1])

    updates = extract_updates()
    feature_rows = [[kw, title, primary, tables, note] for kw, title, primary, tables, note in FEATURES]

    tables_by_file: dict[str, set[str]] = {}
    for name, tfile, _line in table_rows:
        tables_by_file.setdefault(tfile, set()).add(name)

    summary = {
        "generated_by": "tools/build_system_map.py",
        "total_files_indexed": len(file_rows),
        "total_lines_indexed": total_lines,
        "categories": dict(sorted(category_counts.items())),
        "symbols": len(symbol_rows),
        "routes": len(route_rows),
        "table_definitions": len(table_rows),
        "unique_tables": len({r[0] for r in table_rows}),
        "update_packages": len(updates),
        "features": len(feature_rows),
        "schema_sql_tables": len(tables_by_file.get("schema.sql", set())),
        "install_empty_db_tables": len(tables_by_file.get("install-empty-db.sql", set())),
    }

    return {
        "FILES.tsv": tsv(["path", "category", "lines", "bytes"], file_rows),
        "SYMBOLS.tsv": tsv(["name", "kind", "file", "line", "category"], symbol_rows),
        "ROUTES.tsv": tsv(["kind", "name", "file", "line"], route_rows),
        "TABLES.tsv": tsv(["table", "file", "line"], table_rows),
        "UPDATES.tsv": tsv(["package", "version", "requires_version", "files", "migrations", "date"], updates),
        "FEATURES.tsv": tsv(["keywords", "title", "primary_files", "tables", "note"], feature_rows),
        "summary.json": json.dumps(summary, ensure_ascii=False, indent=2) + "\n",
    }


def main() -> int:
    parser = argparse.ArgumentParser(description="بازتولید نقشهٔ ماشینی سامانه")
    parser.add_argument("--check", action="store_true", help="فقط بررسی تازگی؛ خروجی ننویس")
    args = parser.parse_args()

    generated = build()
    if args.check:
        missing = []
        stale = []
        for name, content in generated.items():
            target = OUT_DIR / name
            if not target.is_file():
                missing.append(name)
                continue
            if target.read_text(encoding="utf-8") != content:
                stale.append(name)
        if missing or stale:
            print("FAIL — نقشهٔ ماشینی بهروز نیست.")
            for name in missing:
                print(f"  فایل وجود ندارد: docs/system-map/{name}")
            for name in stale:
                print(f"  تغییر کرده (بازتولید کن): docs/system-map/{name}")
            print("راهحل: python3 tools/build_system_map.py")
            return 1
        summary = json.loads(generated["summary.json"])
        print(f"PASS — نقشه بهروز است. {summary['total_files_indexed']} فایل، "
              f"{summary['symbols']} نماد، {summary['routes']} روت، "
              f"{summary['update_packages']} بسته آپدیت.")
        return 0

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    for name, content in generated.items():
        (OUT_DIR / name).write_text(content, encoding="utf-8")
    summary = json.loads(generated["summary.json"])
    print(f"نقشه ساخته شد → docs/system-map/ ({len(generated)} فایل)")
    print(f"  فایلها: {summary['total_files_indexed']} · خطوط: {summary['total_lines_indexed']}")
    print(f"  نمادها: {summary['symbols']} · روتها: {summary['routes']} · جداول: {summary['unique_tables']}")
    print(f"  بستههای آپدیت: {summary['update_packages']} · ویژگیها: {summary['features']}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
