"""نگهبان نقشهٔ کد — docs/system-map/BLOCKS.json

قانونی که این تست اجرا می‌کند: «اگر محتوای یک آدرس تغییر کند یا جابه‌جا
شود، آدرس باید به‌روز شود.» بدون نگهبان، نقشه ظرف چند هفته دروغ می‌شود و
بدتر از نداشتنش است، چون اعتماد کاذب می‌سازد.

این تست عمداً به php یا php-wasm وابسته نیست تا در CI هم اجرا شود؛ اثر
انگشت را مستقل در پایتون بازمحاسبه می‌کند. اگر دو پیاده‌سازی با هم
اختلاف داشته باشند، همین تست اول از همه قرمز می‌شود.
"""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
from pathlib import Path

import pytest

ROOT = Path(__file__).resolve().parent.parent
INDEX_PATH = ROOT / "docs/system-map/BLOCKS.json"
LEDGER_PATH = ROOT / "docs/system-map/REVIEW-LEDGER.json"

# باید مو‌به‌مو با tools/code_index.php یکی باشد، وگرنه یکی از دو طرف
# فایل‌هایی را می‌بیند که دیگری نمی‌بیند و نگهبان بی‌دلیل قرمز می‌شود.
# updates/ و downloads/ کپی منجمد نسخه‌های منتشرشده‌اند، نه کد زنده.
EXCLUDED_PREFIXES = ("updates/", "downloads/", "vendor/")
TRACKED_GLOBS = ("*.php", "*.js", "*.css", "*.sql", "*.py")


def _tracked_production_files() -> list[str]:
    out = subprocess.run(
        ["git", "ls-files", *TRACKED_GLOBS],
        cwd=ROOT, capture_output=True, text=True, check=True,
    ).stdout.split("\n")
    return sorted(
        p for p in out
        if p and not p.startswith(EXCLUDED_PREFIXES) and (ROOT / p).is_file()
    )


def fingerprint(text: str) -> str:
    """بازتولید دقیق ci_fingerprint() از tools/code_index.php.

    گام‌ها باید مو‌به‌مو با نسخهٔ PHP یکی باشند، وگرنه هر اجرا همهٔ بلوک‌ها
    را «تغییر یافته» گزارش می‌کند و نگهبان بی‌مصرف می‌شود.
    """
    s = re.sub(r"[ \t]+", " ", text)
    # عمداً از \R استفاده نمی‌شود: در PHP حالت بایتی، \R بایت 0x85 را هم
    # «خط جدید» می‌شمارد و آن بایت در UTF-8 فارسی دائماً هست (م = D9 85).
    # این باگ واقعاً رخ داد و شماره‌خط‌های کل نقشه را خراب کرده بود.
    s = re.sub(r"[ \t]*(?:\r\n|\n|\r)[ \t]*", "\n", s)
    s = s.strip(" \t\n\r\0\x0b")
    return hashlib.sha256(s.encode("utf-8")).hexdigest()[:12]


@pytest.fixture(scope="module")
def index() -> dict:
    assert INDEX_PATH.is_file(), (
        "docs/system-map/BLOCKS.json وجود ندارد. با «php tools/code_index.php» بسازید."
    )
    return json.loads(INDEX_PATH.read_text(encoding="utf-8"))


@pytest.fixture(scope="module")
def sources() -> dict[str, list[str]]:
    return {
        p: (ROOT / p).read_text(encoding="utf-8", errors="replace").split("\n")
        for p in _tracked_production_files()
    }


# ---------------------------------------------------------------------------
# کامل بودن: هیچ فایلی از قلم نیفتد
# ---------------------------------------------------------------------------


def test_every_production_file_is_indexed(index):
    indexed = {f["path"] for f in index["files"]}
    tracked = set(_tracked_production_files())
    missing = tracked - indexed
    assert not missing, (
        f"{len(missing)} فایل کد تولیدی در نقشه نیست — نقشه را بازتولید کنید: "
        f"{sorted(missing)[:10]}"
    )


def test_index_has_no_phantom_files(index):
    indexed = {f["path"] for f in index["files"]}
    tracked = set(_tracked_production_files())
    phantom = indexed - tracked
    assert not phantom, f"این فایل‌ها در نقشه هستند ولی دیگر وجود ندارند: {sorted(phantom)[:10]}"


def test_file_ids_are_unique(index):
    ids = [f["id"] for f in index["files"]]
    dupes = {i for i in ids if ids.count(i) > 1}
    assert not dupes, f"شناسهٔ فایل تکراری: {dupes}"


def test_block_ids_are_unique(index):
    seen: set[str] = set()
    dupes: set[str] = set()
    for f in index["files"]:
        for b in f["blocks"]:
            if b["id"] in seen:
                dupes.add(b["id"])
            seen.add(b["id"])
    assert not dupes, f"شناسهٔ بلوک تکراری: {sorted(dupes)[:10]}"


# ---------------------------------------------------------------------------
# تازگی: نقشه باید با کد فعلی بخواند
# ---------------------------------------------------------------------------


def test_file_fingerprints_are_current(index, sources):
    stale = []
    for f in index["files"]:
        if f["path"] not in sources:
            continue
        actual = fingerprint((ROOT / f["path"]).read_text(encoding="utf-8", errors="replace"))
        if actual != f["file_fingerprint"]:
            stale.append(f["path"])
    assert not stale, (
        f"{len(stale)} فایل بعد از ساخت نقشه تغییر کرده‌اند. نقشه را بازتولید کنید "
        f"(php tools/code_index.php > docs/system-map/BLOCKS.json): {stale[:10]}"
    )


def test_block_fingerprints_are_current(index, sources):
    """قلب نگهبان: محتوای هر آدرس باید همانی باشد که ثبت شده."""
    stale = []
    for f in index["files"]:
        lines = sources.get(f["path"])
        if lines is None:
            continue
        for b in f["blocks"]:
            text = "\n".join(lines[b["line_start"] - 1:b["line_end"]])
            if fingerprint(text) != b["fingerprint"]:
                stale.append(f"{b['id']} {f['path']}:{b['line_start']} ({b['anchor']})")
    assert not stale, (
        f"{len(stale)} آدرس با محتوای فعلی نمی‌خواند — یعنی کد جابه‌جا یا عوض شده "
        f"ولی نقشه به‌روز نشده: {stale[:10]}"
    )


def test_block_ranges_are_inside_the_file(index, sources):
    bad = []
    for f in index["files"]:
        lines = sources.get(f["path"])
        if lines is None:
            continue
        for b in f["blocks"]:
            if b["line_start"] < 1 or b["line_end"] > len(lines) or b["line_end"] < b["line_start"]:
                bad.append(f"{b['id']} {b['line_start']}-{b['line_end']} از {len(lines)}")
    assert not bad, f"محدودهٔ نامعتبر: {bad[:10]}"


# ---------------------------------------------------------------------------
# پوشش: هیچ خطی از سامانه بیرون از نقشه نماند
# ---------------------------------------------------------------------------


def test_every_nonblank_line_is_covered_exactly_once(index, sources):
    """خواستهٔ صریح: «هیچ قسمتی از سامانه جا نمونه».

    همپوشانی هم به‌اندازهٔ جاماندگی بد است: یک خط در دو آدرس یعنی هنگام
    اصلاح، یکی از دو آدرس بی‌صدا کهنه می‌شود.
    """
    missing_total = 0
    overlap_total = 0
    worst = []
    for f in index["files"]:
        lines = sources.get(f["path"])
        if lines is None:
            continue
        n = len(lines)
        count = [0] * (n + 2)
        for b in f["blocks"]:
            if b["kind"] == "method":
                continue  # متد داخل کلاس است؛ بلوک کلاس پوششش می‌دهد
            for ln in range(b["line_start"], min(b["line_end"], n) + 1):
                count[ln] += 1
        # همپوشانی فقط روی خطی مجاز است که واقعاً چند نماد سطح‌بالا روی آن
        # شروع شده باشند (فایل‌های fixture گاهی چند تابع را در یک خط
        # می‌نویسند). هر همپوشانی دیگری یعنی نقشه خراب است.
        starts: dict[int, int] = {}
        for b in f["blocks"]:
            if b["kind"] == "method":
                continue
            starts[b["line_start"]] = starts.get(b["line_start"], 0) + 1
        shared = {ln for ln, c in starts.items() if c > 1}

        miss = [ln for ln in range(1, n + 1) if lines[ln - 1].strip() and count[ln] == 0]
        over = [
            ln for ln in range(1, n + 1)
            if lines[ln - 1].strip() and count[ln] > 1 and ln not in shared
        ]
        missing_total += len(miss)
        overlap_total += len(over)
        if miss or over:
            worst.append(f"{f['path']}: جامانده={len(miss)} همپوشان={len(over)} {miss[:3]}")
    assert missing_total == 0 and overlap_total == 0, (
        f"پوشش کامل نیست — جامانده={missing_total} همپوشان={overlap_total}: {worst[:8]}"
    )


def test_symbol_blocks_end_on_a_closing_brace(index, sources):
    """محافظ در برابر خرابی شمارش آکولاد.

    یک بار واقعاً رخ داد: «{$var}» داخل رشته، توکن T_CURLY_OPEN می‌سازد ولی
    «}» پایانی‌اش رشتهٔ ساده است؛ بدون شمارش درست، بدنهٔ تابع زودتر بسته
    می‌شد و نقشه خاموش دروغ می‌گفت.
    """
    bad = []
    for f in index["files"]:
        lines = sources.get(f["path"])
        if lines is None:
            continue
        for b in f["blocks"]:
            if b["kind"] not in ("function", "method", "class", "trait", "interface"):
                continue
            last = lines[b["line_end"] - 1].strip()
            # تابع تک‌خطی («function e($s) { return $s; }») با } تمام می‌شود
            # ولی با آن شروع نمی‌شود؛ هر دو حالت معتبرند.
            if not (last.startswith("}") or last.endswith("}")):
                bad.append(f"{b['id']} {f['path']}:{b['line_end']} «{last[:40]}»")
    assert not bad, f"بلوک نمادی که با آکولاد بسته تمام نمی‌شود: {bad[:10]}"


# ---------------------------------------------------------------------------
# دفتر بازبینی
# ---------------------------------------------------------------------------


def test_ledger_exists_and_is_valid_json():
    assert LEDGER_PATH.is_file(), "docs/system-map/REVIEW-LEDGER.json وجود ندارد"
    data = json.loads(LEDGER_PATH.read_text(encoding="utf-8"))
    assert isinstance(data.get("reviewed"), dict)


def test_ledger_entries_point_at_real_addresses(index):
    """آدرس بازبینی‌شده‌ای که دیگر وجود ندارد یعنی دفتر از کد عقب افتاده."""
    data = json.loads(LEDGER_PATH.read_text(encoding="utf-8"))
    known = {b["id"] for f in index["files"] for b in f["blocks"]}
    orphan = [k for k in data["reviewed"] if k not in known]
    assert not orphan, (
        f"این آدرس‌ها در دفتر بازبینی هستند ولی در نقشه نیستند: {orphan[:10]}"
    )


def test_ledger_flags_blocks_changed_since_review(index):
    """بلوکی که بعد از بازبینی عوض شده، دیگر «بررسی‌شده» نیست.

    این تست عمداً شکست نمی‌دهد — کد باید بتواند تغییر کند. ولی آمار را
    صریح نگه می‌دارد تا درصد پوشش بازبینی واقعی بماند و توهم «۱۰۰٪ بررسی
    شده» پیش نیاید.
    """
    data = json.loads(LEDGER_PATH.read_text(encoding="utf-8"))
    fp = {b["id"]: b["fingerprint"] for f in index["files"] for b in f["blocks"]}
    stale = [k for k, v in data["reviewed"].items() if fp.get(k) != v.get("fingerprint")]
    data_stale = data.get("stale_on_last_run")
    if data_stale is not None:
        assert sorted(stale) == sorted(data_stale), (
            "فهرست بلوک‌های کهنه‌شده در دفتر با واقعیت نمی‌خواند؛ دفتر را به‌روز کنید"
        )


def test_human_readable_map_is_in_sync(index):
    """نقشهٔ خوانا باید از همین فهرست ساخته شده باشد، نه نسخه‌ای قدیمی."""
    map_path = ROOT / "docs/system-map/REVIEW-MAP-FA.md"
    assert map_path.is_file(), "docs/system-map/REVIEW-MAP-FA.md وجود ندارد"
    text = map_path.read_text(encoding="utf-8")
    n_blocks = sum(len(f["blocks"]) for f in index["files"])
    assert f"| آدرس‌ها (بلوک) | {n_blocks:,} |" in text, (
        "تعداد آدرس‌ها در نقشهٔ خوانا با فهرست نمی‌خواند؛ "
        "python tools/render_code_map.py را دوباره اجرا کنید"
    )
    assert f"| فایل‌های کد تولیدی | {len(index['files'])} |" in text, (
        "تعداد فایل‌ها در نقشهٔ خوانا کهنه است"
    )


# ---------------------------------------------------------------------------
# فهرست موضوعی — «فلان بخش کجاست؟»
# ---------------------------------------------------------------------------

TOPICS_PATH = ROOT / "docs" / "system-map" / "TOPICS.json"


@pytest.fixture(scope="module")
def topics() -> dict:
    assert TOPICS_PATH.is_file(), "docs/system-map/TOPICS.json وجود ندارد"
    return json.loads(TOPICS_PATH.read_text(encoding="utf-8"))


def test_every_page_of_the_system_has_a_topic(topics):
    """هر صفحه‌ای که در کد مسیریابی می‌شود باید در فهرست موضوعی باشد.

    وگرنه کاربر نام بخشی را جست‌وجو می‌کند، پیدا نمی‌کند، و دوباره مجبور
    می‌شود کل کد را بگردد — یعنی فهرست کار خودش را نکرده.
    """
    src = (ROOT / "index.php").read_text(encoding="utf-8")
    routed = set(re.findall(r"\$page\s*===?\s*'([a-z0-9\-]+)'", src))
    listed = {t["key"] for t in topics["pages"]}
    missing = routed - listed
    assert not missing, f"این صفحه‌ها در فهرست موضوعی نیستند: {sorted(missing)}"


def test_every_permission_has_a_topic(topics):
    src = (ROOT / "permissions.php").read_text(encoding="utf-8")
    codes = set(re.findall(r"'code'\s*=>\s*'([a-z0-9_.]+)'", src))
    listed = {t["key"] for t in topics["permissions"]}
    missing = codes - listed
    assert not missing, f"این مجوزها در فهرست موضوعی نیستند: {sorted(missing)[:10]}"


def test_every_topic_resolves_to_at_least_one_address(topics):
    empty = [
        t["id"] for t in topics["pages"] + topics["permissions"] + topics["tables"]
        if not t["primary"]
    ]
    assert not empty, f"این موضوع‌ها هیچ آدرس اصلی ندارند: {empty[:10]}"


def test_topic_addresses_exist_in_the_block_index(index, topics):
    """آدرسی که در فهرست موضوعی هست ولی در نقشه نیست، کاربر را گمراه می‌کند."""
    known = {b["id"] for f in index["files"] for b in f["blocks"]}
    bad = []
    for t in topics["pages"] + topics["permissions"] + topics["tables"]:
        for i in t["primary"] + t["related"]:
            if i not in known:
                bad.append(f"{t['id']} → {i}")
    assert not bad, f"آدرس ناموجود در فهرست موضوعی: {bad[:10]}"


def test_boot_file_points_agents_at_the_map():
    """الزام بوت: ایجنت جلسهٔ تازه باید اول نقشه را بشناسد."""
    agents = (ROOT / "AGENTS.md").read_text(encoding="utf-8")
    assert "docs/system-map/REVIEW-MAP-FA.md" in agents, (
        "AGENTS.md به نقشهٔ موضوعی ارجاع نمی‌دهد؛ ایجنت جلسهٔ بعد آن را پیدا نمی‌کند"
    )
    assert "tools/code_index.php" in agents, "دستور بازتولید نقشه در AGENTS.md نیست"
