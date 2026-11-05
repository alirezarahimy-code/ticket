#!/usr/bin/env python3
"""ممیزی دوره‌ای سامانه — سلامت کد، معماری و ریسک‌های عملیاتی.

چرا این ابزار وجود دارد
=======================
سامانه یک بار دچار نقصی شد که **ماه‌ها خاموش** ماند: فایل‌های اسکیما از
مهاجرت‌ها عقب افتادند، پس هر نصب تازه بخش بزرگی از پایگاه داده را نداشت.
چون کد لاگ‌گیری ``INSERT`` خود را داخل ``try/catch`` بی‌صدا دارد، هیچ خطایی
هم دیده نمی‌شد.

درس گرفته‌شده دو چیز بود:

۱. مشکلاتی که «خطا نمی‌دهند» را فقط بررسی فعال پیدا می‌کند، نه گزارش کاربر.
۲. هشدارِ همیشه-قرمز را همه نادیده می‌گیرند. وقتی سوئیت تست ۳۵ شکست دائمی
   داشت، همان شکست‌ها تبدیل به نویز پس‌زمینه شدند و یک باگ واقعی داخلشان
   گم شد.

بنابراین این ابزار بر پایهٔ **خط مبنا** کار می‌کند، نه صفر-تحمل. وضعیت
پذیرفته‌شدهٔ امروز در ``tools/audit-baseline.json`` ثبت می‌شود و اجرای بعدی
فقط وقتی قرمز می‌شود که اوضاع **بدتر** شده باشد. این‌طور خروجی همیشه
عملیاتی می‌ماند و به نویز تبدیل نمی‌شود.

نحوهٔ استفاده
=============
    python3 tools/system_audit.py                 # گزارش انسانی
    python3 tools/system_audit.py --json out.json # خروجی ماشینی
    python3 tools/system_audit.py --check         # قرمز شدن در صورت پس‌رفت
    python3 tools/system_audit.py --update-baseline

فقط کتابخانهٔ استاندارد پایتون لازم است.
"""

from __future__ import annotations

import argparse
import json
import re
import subprocess
import sys
from dataclasses import dataclass, field
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASELINE_PATH = ROOT / "tools" / "audit-baseline.json"
SCHEMA_FILES = ("schema.sql", "install-empty-db.sql")

# ``updates/`` آینهٔ بسته‌های منتشرشده است و عمداً تغییر نمی‌کند؛ ممیزی روی
# آن بی‌معناست. ``preview/`` و ``tests/`` هم کد عملیاتی نیستند.
EXCLUDED_DIRS = ("updates/", "preview/", "tests/", "downloads/")

SEVERITY_ORDER = {"بحرانی": 0, "بالا": 1, "متوسط": 2, "پایین": 3, "اطلاعی": 4}


@dataclass
class Finding:
    """یک مورد مشخص که باید دیده شود."""

    where: str
    detail: str


@dataclass
class CheckResult:
    key: str
    title: str
    severity: str
    summary: str
    findings: list[Finding] = field(default_factory=list)
    # معیار عددی‌ای که در خط مبنا ثبت می‌شود. کمتر = بهتر.
    metric: int = 0
    # توضیح اینکه چرا این بررسی اهمیت دارد.
    rationale: str = ""

    @property
    def count(self) -> int:
        return self.metric


# ---------------------------------------------------------------------------
# کمکی‌ها
# ---------------------------------------------------------------------------


def _tracked(pattern: str) -> list[Path]:
    """فایل‌های tracked گیت، بدون پوشه‌های کنارگذاشته."""
    out = subprocess.run(
        ["git", "ls-files", pattern], cwd=ROOT, capture_output=True, text=True
    ).stdout.split()
    return [
        ROOT / f for f in out if not any(f.startswith(d) for d in EXCLUDED_DIRS)
    ]


def _read(path: Path) -> str:
    try:
        return path.read_text(encoding="utf-8", errors="replace")
    except OSError:
        return ""


def _rel(path: Path) -> str:
    try:
        return str(path.relative_to(ROOT))
    except ValueError:
        return str(path)


def _strip_sql_comments(sql: str) -> str:
    sql = re.sub(r"/\*.*?\*/", " ", sql, flags=re.S)
    sql = re.sub(r"(^|\s)--[^\n]*", " ", sql)
    return re.sub(r"(^|\s)#[^\n]*", " ", sql)


def _schema_tables(name: str) -> set[str]:
    text = _strip_sql_comments(_read(ROOT / name))
    return {
        m.group(1).strip("`").lower()
        for m in re.finditer(
            r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z_]\w*)`?", text, re.I
        )
    }


# ---------------------------------------------------------------------------
# بررسی‌ها
# ---------------------------------------------------------------------------


def check_schema_parity() -> CheckResult:
    """دو فایل اسکیما باید دقیقاً یک ساختار بسازند."""
    a, b = SCHEMA_FILES
    ta, tb = _schema_tables(a), _schema_tables(b)
    findings = [Finding(a, f"جدول {t} در {b} نیست") for t in sorted(ta - tb)]
    findings += [Finding(b, f"جدول {t} در {a} نیست") for t in sorted(tb - ta)]
    return CheckResult(
        key="schema_parity",
        title="هم‌ترازی دو فایل اسکیما",
        severity="بحرانی",
        summary=f"{len(ta)} جدول در {a}، {len(tb)} جدول در {b}",
        findings=findings,
        metric=len(findings),
        rationale=(
            "نصب تازه بسته به مسیر انتخابی از یکی از این دو فایل تغذیه می‌شود. "
            "هر اختلاف یعنی دو نصب با ساختار متفاوت."
        ),
    )


def check_code_table_references() -> CheckResult:
    """هر جدولی که کد از آن می‌خواند باید در اسکیما باشد."""
    have = _schema_tables("schema.sql")
    # نام‌هایی که به‌خاطر ساختار SQL یا متن انگلیسی مثبت کاذب می‌دهند.
    noise = {
        "select", "where", "dual", "information_schema", "sqlite_master", "stdin",
        "current_timestamp", "the", "last", "opening", "failed", "ignore", "hresult",
        "every", "name", "value", "title", "prefix", "sequence", "attempts",
    }
    pattern = re.compile(
        r"\b(?:FROM|JOIN|INSERT\s+INTO|UPDATE|DELETE\s+FROM|REPLACE\s+INTO)"
        r"\s+`?([a-z][a-z0-9_]{2,})`?",
        re.I,
    )
    findings: list[Finding] = []
    seen: set[str] = set()
    for path in _tracked("*.php"):
        text = _read(path)
        # ``ON DUPLICATE KEY UPDATE <col>`` ستون است نه جدول.
        text = re.sub(r"ON\s+DUPLICATE\s+KEY\s+UPDATE\s+\w+", " ", text, flags=re.I)
        text = re.sub(r"/\*.*?\*/", " ", text, flags=re.S)
        for m in pattern.finditer(text):
            table = m.group(1).lower()
            if table in noise or table in have or table in seen:
                continue
            seen.add(table)
            findings.append(Finding(_rel(path), f"جدول ناشناخته: {table}"))
    return CheckResult(
        key="code_table_references",
        title="ارجاع کد به جدول‌های ناموجود",
        severity="بحرانی",
        summary=f"{len(seen)} نام جدولِ بدون تعریف در اسکیما",
        findings=findings,
        metric=len(findings),
        rationale="کوئری به جدول ناموجود روی نصب تازه خطای زمان اجرا می‌دهد.",
    )


def check_migration_guards() -> CheckResult:
    """مهاجرت باید در برابر اجرای دوباره امن باشد."""
    findings: list[Finding] = []
    total = 0
    for path in sorted((ROOT / "migrations").glob("*.sql")):
        if "rollback" in path.name.lower():
            continue
        total += 1
        text = _strip_sql_comments(_read(path))
        mutates = re.search(r"\bALTER\s+TABLE\b|\bCREATE\s+TABLE\b", text, re.I)
        if not mutates:
            continue
        guarded = re.search(r"IF\s+NOT\s+EXISTS|information_schema", text, re.I)
        if not guarded:
            findings.append(Finding(_rel(path), "بدون IF NOT EXISTS یا بررسی information_schema"))
    return CheckResult(
        key="migration_guards",
        title="امن بودن مهاجرت‌ها در اجرای دوباره",
        severity="بالا",
        summary=f"{total} مهاجرت بررسی شد",
        findings=findings,
        metric=len(findings),
        rationale=(
            "به‌روزرسان در حالت reinstall مهاجرت ثبت‌نشده را دوباره اجرا می‌کند. "
            "مهاجرت بدون گارد در این حالت کل به‌روزرسانی را می‌شکند."
        ),
    )


# ``catch`` ای که بدنه‌اش خالی است یا فقط کامنت دارد.
_SILENT_CATCH = re.compile(
    r"catch\s*\([^)]*\)\s*\{\s*(?://[^\n]*\n\s*|/\*.*?\*/\s*|\#[^\n]*\n\s*)*\}",
    re.S,
)


def check_silent_error_swallowing() -> CheckResult:
    """``catch`` بی‌صدا خطا را پنهان می‌کند و عیب را نامرئی."""
    findings: list[Finding] = []
    for path in _tracked("*.php"):
        text = _read(path)
        hits = list(_SILENT_CATCH.finditer(text))
        if hits:
            lines = [text[: m.start()].count("\n") + 1 for m in hits]
            findings.append(
                Finding(_rel(path), f"{len(hits)} مورد، خطوط {lines[:6]}")
            )
    total = sum(int(f.detail.split()[0]) for f in findings)
    return CheckResult(
        key="silent_error_swallowing",
        title="بلعیدن خاموش خطا",
        severity="بالا",
        summary=f"{total} بلوک catch بی‌صدا در {len(findings)} فایل",
        findings=findings,
        metric=total,
        rationale=(
            "همین الگو بود که نبودِ جدول‌های لاگ‌گیری را ماه‌ها پنهان کرد. "
            "خطا بلعیده می‌شود، قابلیت بی‌صدا از کار می‌افتد و هیچ‌کس خبردار "
            "نمی‌شود. دست‌کم یک log یا شمارنده لازم است."
        ),
    )


def check_architecture_pressure() -> CheckResult:
    """فایل‌های بیش‌ازحد بزرگ، فشار معماری را نشان می‌دهند."""
    limit = 1500
    findings: list[Finding] = []
    for path in _tracked("*.php"):
        lines = _read(path).count("\n") + 1
        if lines > limit:
            findings.append(Finding(_rel(path), f"{lines:,} خط"))
    findings.sort(key=lambda f: -int(f.detail.split()[0].replace(",", "")))
    return CheckResult(
        key="architecture_pressure",
        title=f"فایل‌های بزرگ‌تر از {limit} خط",
        severity="متوسط",
        summary=f"{len(findings)} فایل از آستانه عبور کرده‌اند",
        findings=findings,
        metric=len(findings),
        rationale=(
            "فایل بزرگ یعنی بازبینی سخت‌تر، تداخل بیشتر در ادغام و ریسک بالاتر "
            "اینکه تغییر در یک قابلیت، قابلیت دیگری را بشکند."
        ),
    )


def check_index_monolith() -> CheckResult:
    """چند «صفحه» در یک فایل واحد رندر می‌شوند."""
    text = _read(ROOT / "index.php")
    pages = sorted(set(re.findall(r"\$page === '([a-z0-9_-]+)'", text)))
    forms = text.count("<form")
    return CheckResult(
        key="index_monolith",
        title="تمرکز صفحات در index.php",
        severity="متوسط",
        summary=f"{len(pages)} صفحه و {forms} فرم در یک فایل",
        findings=[Finding("index.php", f"صفحه: {p}") for p in pages],
        metric=len(pages),
        rationale=(
            "هر صفحهٔ تازه این فایل را بزرگ‌تر می‌کند و شعاع انفجار هر تغییر را "
            "بالا می‌برد. اکشن‌ها از ۱.۶۲.۱۱ تا ۱.۶۲.۲۰ به actions/ منتقل شدند؛ "
            "این معیار نشان می‌دهد لایهٔ نمایش هنوز منتقل نشده است."
        ),
    )


def check_filesystem_safety() -> CheckResult:
    """نوشتن در فایل‌سیستم باید پوشه را بسازد و بازگشتی باشد."""
    findings: list[Finding] = []
    for path in _tracked("*.php"):
        text = _read(path)
        for m in re.finditer(r"@?mkdir\s*\(([^;]{0,120}?)\)\s*;", text):
            args = m.group(1)
            if "true" not in args.lower():
                line = text[: m.start()].count("\n") + 1
                findings.append(Finding(f"{_rel(path)}:{line}", "mkdir بدون recursive"))
    return CheckResult(
        key="filesystem_safety",
        title="ساخت پوشه بدون حالت بازگشتی",
        severity="متوسط",
        summary=f"{len(findings)} فراخوانی mkdir بدون recursive",
        findings=findings,
        metric=len(findings),
        rationale=(
            "روی نصب تازه پوشه‌های میانی وجود ندارند. mkdir بدون recursive "
            "شکست می‌خورد و نوشتن بعدی هم خطا می‌دهد."
        ),
    )


def check_hardcoded_environment() -> CheckResult:
    """آدرس و مسیر سخت‌کدشده نصب روی ماشین دیگر را می‌شکند."""
    findings: list[Finding] = []
    patterns = (
        (re.compile(r"https?://(?:localhost|127\.0\.0\.1)[^\s\"']*"), "آدرس محلی"),
        (re.compile(r"[\"'][A-Za-z]:\\\\"), "مسیر مطلق ویندوز"),
    )
    for path in _tracked("*.php"):
        text = _read(path)
        for pattern, label in patterns:
            for m in pattern.finditer(text):
                line = text[: m.start()].count("\n") + 1
                findings.append(
                    Finding(f"{_rel(path)}:{line}", f"{label}: {m.group(0)[:48]}")
                )
    return CheckResult(
        key="hardcoded_environment",
        title="آدرس و مسیر سخت‌کدشده",
        severity="متوسط",
        summary=f"{len(findings)} مورد",
        findings=findings,
        metric=len(findings),
        rationale="نصب روی سرور دیگر با مسیر یا پورت متفاوت شکست می‌خورد.",
    )


def check_release_consistency() -> CheckResult:
    """VERSION، README، بستهٔ منتشرشده و صفحهٔ دانلود باید یک عدد بگویند."""
    findings: list[Finding] = []
    version = _read(ROOT / "VERSION").strip()
    readme = _read(ROOT / "README.md")
    m = re.search(r"نسخه تحویل فعلی:\s*`([^`]+)`", readme)
    if not m:
        findings.append(Finding("README.md", "خط «نسخه تحویل فعلی» پیدا نشد"))
    elif m.group(1) != version:
        findings.append(
            Finding("README.md", f"README می‌گوید {m.group(1)} ولی VERSION می‌گوید {version}")
        )

    page_path = ROOT / "downloads" / "index.html"
    page = _read(page_path)
    if version.replace(".", "\u200c.") not in page and version not in page:
        # صفحه ممکن است عدد را با رقم فارسی بنویسد؛ نام فایل بسته را می‌سنجیم.
        if not re.search(rf"ticket-(?:update|full)[^\"']*{re.escape(version)}", page):
            findings.append(
                Finding("downloads/index.html", f"صفحهٔ دانلود به نسخهٔ {version} اشاره نمی‌کند")
            )

    for zip_path in sorted((ROOT / "downloads").glob(f"*{version}*.zip")):
        sha_file = zip_path.with_name(zip_path.name + ".sha256")
        if not sha_file.exists():
            findings.append(Finding(_rel(zip_path), "فایل .sha256 ندارد"))
            continue
        import hashlib

        actual = hashlib.sha256(zip_path.read_bytes()).hexdigest()
        if actual not in sha_file.read_text(encoding="utf-8"):
            findings.append(Finding(_rel(zip_path), "checksum ثبت‌شده نمی‌خواند"))
        if actual not in page:
            findings.append(Finding(_rel(zip_path), "checksum در صفحهٔ دانلود نیست"))

    return CheckResult(
        key="release_consistency",
        title="یکپارچگی انتشار",
        severity="بالا",
        summary=f"نسخهٔ جاری {version}",
        findings=findings,
        metric=len(findings),
        rationale=(
            "اگر صفحهٔ دانلود عقب بماند، کاربر بستهٔ اشتباه نصب می‌کند. این دقیقاً "
            "همان چیزی بود که صفحه را روی ۱.۶۲.۷۷ نگه داشته بود."
        ),
    )


def check_secrets() -> CheckResult:
    """اعتبارنامه نباید داخل مخزن باشد."""
    patterns = (
        re.compile(r"(?i)\b(?:password|passwd|secret|api[_-]?key)\s*=\s*['\"][^'\"]{8,}['\"]"),
        re.compile(r"AKIA[0-9A-Z]{16}"),
        re.compile(r"-----BEGIN (?:RSA |OPENSSH )?PRIVATE KEY-----"),
    )
    # نمونه‌ها و جای‌گیرها اعتبارنامهٔ واقعی نیستند.
    placeholder = re.compile(
        r"(?i)(example|sample|changeme|your[_-]?|xxx|placeholder|\.\.\.|password123|<|\$)"
    )
    findings: list[Finding] = []
    for path in _tracked("*.php") + _tracked("*.ps1") + _tracked("*.bat"):
        if path.name == "config.sample.php":
            continue
        text = _read(path)
        for pattern in patterns:
            for m in pattern.finditer(text):
                if placeholder.search(m.group(0)):
                    continue
                line = text[: m.start()].count("\n") + 1
                findings.append(Finding(f"{_rel(path)}:{line}", m.group(0)[:48]))
    return CheckResult(
        key="secrets",
        title="اعتبارنامه در مخزن",
        severity="بحرانی",
        summary=f"{len(findings)} مورد مشکوک",
        findings=findings,
        metric=len(findings),
        rationale="اعتبارنامهٔ کامیت‌شده در تاریخچهٔ گیت می‌ماند حتی بعد از حذف.",
    )


CHECKS = (
    check_schema_parity,
    check_code_table_references,
    check_migration_guards,
    check_release_consistency,
    check_secrets,
    check_silent_error_swallowing,
    check_architecture_pressure,
    check_index_monolith,
    check_filesystem_safety,
    check_hardcoded_environment,
)


# ---------------------------------------------------------------------------
# اجرا و گزارش
# ---------------------------------------------------------------------------


def run_all() -> list[CheckResult]:
    results = [check() for check in CHECKS]
    results.sort(key=lambda r: (SEVERITY_ORDER.get(r.severity, 9), r.key))
    return results


def render_markdown(results: list[CheckResult], baseline: dict[str, int] | None) -> str:
    lines = ["# گزارش ممیزی دوره‌ای سامانه", ""]
    lines.append("| بررسی | شدت | معیار | خط مبنا | روند |")
    lines.append("|---|---|---|---|---|")
    for r in results:
        base = baseline.get(r.key) if baseline else None
        if base is None:
            trend = "—"
        elif r.metric > base:
            trend = f"🔴 پس‌رفت (+{r.metric - base})"
        elif r.metric < base:
            trend = f"🟢 بهبود (−{base - r.metric})"
        else:
            trend = "⚪ بدون تغییر"
        lines.append(
            f"| {r.title} | {r.severity} | {r.metric} | {base if base is not None else '—'} | {trend} |"
        )
    lines.append("")

    for r in results:
        if not r.findings:
            continue
        lines.append(f"## {r.title}  ({r.severity})")
        lines.append("")
        lines.append(f"*{r.rationale}*")
        lines.append("")
        lines.append(f"خلاصه: {r.summary}")
        lines.append("")
        for f in r.findings[:25]:
            lines.append(f"- `{f.where}` — {f.detail}")
        if len(r.findings) > 25:
            lines.append(f"- … و {len(r.findings) - 25} مورد دیگر")
        lines.append("")
    return "\n".join(lines)


def load_baseline() -> dict[str, int]:
    if not BASELINE_PATH.exists():
        return {}
    data = json.loads(BASELINE_PATH.read_text(encoding="utf-8"))
    return {k: int(v) for k, v in data.get("metrics", {}).items()}


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="ممیزی دوره‌ای سامانه")
    parser.add_argument("--json", metavar="PATH", help="نوشتن خروجی ماشینی")
    parser.add_argument("--markdown", metavar="PATH", help="نوشتن گزارش خوانا")
    parser.add_argument(
        "--check",
        action="store_true",
        help="خروج با کد ۱ اگر معیاری از خط مبنا بدتر شده باشد",
    )
    parser.add_argument(
        "--update-baseline", action="store_true", help="ثبت وضعیت فعلی به‌عنوان خط مبنا"
    )
    args = parser.parse_args(argv)

    results = run_all()
    baseline = load_baseline()

    if args.update_baseline:
        BASELINE_PATH.write_text(
            json.dumps(
                {
                    "_توضیح": (
                        "وضعیت پذیرفته‌شدهٔ فعلی. ممیزی فقط وقتی قرمز می‌شود که عددی "
                        "از این مقادیر بیشتر شود. برای بهبود، عدد را کم کنید و دوباره ثبت کنید."
                    ),
                    "metrics": {r.key: r.metric for r in results},
                },
                ensure_ascii=False,
                indent=2,
            )
            + "\n",
            encoding="utf-8",
        )
        print(f"خط مبنا در {_rel(BASELINE_PATH)} به‌روز شد.")
        return 0

    report = render_markdown(results, baseline or None)
    if args.markdown:
        Path(args.markdown).write_text(report, encoding="utf-8")
    if args.json:
        Path(args.json).write_text(
            json.dumps(
                {
                    "metrics": {r.key: r.metric for r in results},
                    "checks": [
                        {
                            "key": r.key,
                            "title": r.title,
                            "severity": r.severity,
                            "metric": r.metric,
                            "summary": r.summary,
                            "findings": [
                                {"where": f.where, "detail": f.detail} for f in r.findings
                            ],
                        }
                        for r in results
                    ],
                },
                ensure_ascii=False,
                indent=2,
            )
            + "\n",
            encoding="utf-8",
        )
    if not args.markdown:
        print(report)

    if args.check:
        regressions = [
            (r, baseline[r.key]) for r in results if r.key in baseline and r.metric > baseline[r.key]
        ]
        if regressions:
            print("\n🔴 پس‌رفت نسبت به خط مبنا:", file=sys.stderr)
            for r, base in regressions:
                print(f"   {r.title}: {base} → {r.metric}", file=sys.stderr)
            print(
                "\nیا علت را برطرف کنید، یا اگر تغییر عمدی است با "
                "--update-baseline خط مبنا را به‌روز کنید.",
                file=sys.stderr,
            )
            return 1
        missing = [r.key for r in results if r.key not in baseline]
        if missing:
            print(f"\n⚠️  بررسی تازه بدون خط مبنا: {missing}", file=sys.stderr)
        print("\n🟢 هیچ پس‌رفتی نسبت به خط مبنا نیست.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
