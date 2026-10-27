#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""آزمون ایستای Skill Manifest — بدون نیاز به PHP، MySQL یا مرورگر.

بررسی میکند که مانیفست مهارتها کامل، ساختاریافته و صادقانه باشد:
  - ۲۳ مهارت اصلی (S01..S23) و ۸ مهارت مکمل (C01..C08)
  - هر مهارت دارای رفتار، چکلیست (حداقل ۲ مورد)، گردشکار و معیار موفقیت
  - اعلام صریح وضعیت حافظه (موقت / فقط این جلسه) و ممنوعیت ادعای نصب دائمی
  - وجود هفت مرحلهٔ گردشکار
  - پیوند AGENTS.md به مانیفست
  - نبود آلودگی خط نوشتاری بیگانه (CJK/عبری/ارمنی) در متن فارسی

اجرا: python3 tests/test_skill_manifest_static.py
"""

from __future__ import annotations

import re
import sys
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MANIFEST = ROOT / "docs" / "SKILL-MANIFEST-FA.md"
AGENTS = ROOT / "AGENTS.md"

CORE_COUNT = 23
COMPLEMENTARY_COUNT = 8

REQUIRED_LABELS = (
    "- **رفتار قابل مشاهده:**",
    "- **چکلیست:**",
    "- **اتصال به گردشکار:**",
    "- **معیار موفقیت:**",
)

WORKFLOW_STAGES = ("درک", "برنامهریزی", "تولید", "اجرا", "تست", "بازبینی", "تحویل")

HEADING_RE = re.compile(r"^### ([SC]\d{2})\s+—\s+(.+)$", re.MULTILINE)
TABLE_ROW_RE = re.compile(r"^\| ([SC]\d{2}) \|(.+)$", re.MULTILINE)
CHECKBOX_RE = re.compile(r"^\s*- \[ \]", re.MULTILINE)

# محدودههای یونیکدی که نباید در یک متن فارسی/انگلیسی دیده شوند.
FOREIGN_SCRIPT_RANGES = (
    ("CJK", 0x4E00, 0x9FFF),
    ("Hebrew", 0x0590, 0x05FF),
    ("Armenian", 0x0530, 0x058F),
    ("Hiragana/Katakana", 0x3040, 0x30FF),
)


def sections(text: str) -> dict[str, str]:
    """بخشبندی مانیفست بر اساس تیترهای ### Sxx / ### Cxx."""
    matches = list(HEADING_RE.finditer(text))
    out: dict[str, str] = {}
    for i, match in enumerate(matches):
        start = match.end()
        end = matches[i + 1].start() if i + 1 < len(matches) else len(text)
        body = text[start:end]
        # تیترهای ## بعدی جزو بدنهٔ مهارت نیستند.
        boundary = re.search(r"^## ", body, re.MULTILINE)
        if boundary:
            body = body[: boundary.start()]
        out[match.group(1)] = body
    return out


def foreign_characters(text: str) -> list[str]:
    found: list[str] = []
    for lineno, line in enumerate(text.splitlines(), 1):
        for char in line:
            codepoint = ord(char)
            for name, low, high in FOREIGN_SCRIPT_RANGES:
                if low <= codepoint <= high:
                    found.append(f"سطر {lineno}: {name} U+{codepoint:04X} ({char!r})")
                    break
    return found


def main() -> int:
    failures: list[str] = []
    checks = 0

    def check(condition: bool, message: str) -> None:
        nonlocal checks
        checks += 1
        if not condition:
            failures.append(message)

    # --- وجود فایلها -------------------------------------------------------
    for path in (MANIFEST, AGENTS):
        check(path.is_file(), f"فایل وجود ندارد: {path.relative_to(ROOT)}")

    if failures:
        for failure in failures:
            print(f"FAIL: {failure}")
        return 1

    text = MANIFEST.read_text(encoding="utf-8")
    agents_text = AGENTS.read_text(encoding="utf-8")
    # نرمالسازی برای مقایسهٔ پایدار (کشیدگیها و نیمفاصلهها)
    normalized = unicodedata.normalize("NFKC", text)

    # --- شمارش و پیوستگی شناسهها ------------------------------------------
    secs = sections(text)
    core = sorted(k for k in secs if k.startswith("S"))
    complementary = sorted(k for k in secs if k.startswith("C"))

    check(len(core) == CORE_COUNT, f"تعداد مهارتهای اصلی {len(core)} است؛ باید {CORE_COUNT} باشد")
    check(
        len(complementary) == COMPLEMENTARY_COUNT,
        f"تعداد مهارتهای مکمل {len(complementary)} است؛ باید {COMPLEMENTARY_COUNT} باشد",
    )
    expected_core = [f"S{i:02d}" for i in range(1, CORE_COUNT + 1)]
    expected_comp = [f"C{i:02d}" for i in range(1, COMPLEMENTARY_COUNT + 1)]
    check(core == expected_core, f"شناسههای اصلی ناپیوستهاند: {core}")
    check(complementary == expected_comp, f"شناسههای مکمل ناپیوستهاند: {complementary}")

    # --- ساختار تکتک مهارتها -----------------------------------------------
    for skill_id, body in sorted(secs.items()):
        for label in REQUIRED_LABELS:
            check(label in body, f"{skill_id}: برچسب الزامی یافت نشد → {label}")
        checkboxes = CHECKBOX_RE.findall(body)
        check(
            2 <= len(checkboxes) <= 5,
            f"{skill_id}: تعداد آیتمهای چکلیست {len(checkboxes)} است؛ باید بین ۲ تا ۵ باشد",
        )
        check(
            "منبع کشف" in body if skill_id.startswith("C") else True,
            f"{skill_id}: مهارت مکمل باید «منبع کشف» داشته باشد",
        )

    # --- جدول مانیفست ------------------------------------------------------
    table_rows = dict()
    for match in TABLE_ROW_RE.finditer(text):
        table_rows[match.group(1)] = match.group(2)
    for skill_id in expected_core + expected_comp:
        check(skill_id in table_rows, f"{skill_id}: ردیفی در جدول مانیفست ندارد")
        if skill_id in table_rows:
            row = table_rows[skill_id]
            check("فعال" in row, f"{skill_id}: وضعیت «فعال» در جدول مشخص نیست")
            check("موقت" in row, f"{skill_id}: وضعیت حافظهٔ «موقت» در جدول مشخص نیست")

    # --- صداقت دربارهٔ حافظه ------------------------------------------------
    check(
        "موقت / فقط این جلسه" in normalized or "موقت / فقط این جلسه" in text,
        "اعلام وضعیت «موقت / فقط این جلسه» در مانیفست یافت نشد",
    )
    check(
        "من حافظهٔ پایدار رفتاری ندارم" in text or "حافظهٔ پایدار رفتاری ندارم" in text,
        "اعلام صریح نبود حافظهٔ پایدار رفتاری یافت نشد",
    )
    check(
        "هرگز نباید گفته شود" in text,
        "قید ممنوعیت ادعای نصب دائمی یافت نشد",
    )

    # --- گردشکار ------------------------------------------------------------
    for stage in WORKFLOW_STAGES:
        check(stage in text, f"مرحلهٔ گردشکار «{stage}» در مانیفست یافت نشد")
    check(
        "درک → برنامهریزی → تولید → اجرا → تست → بازبینی → تحویل" in text
        or "درک → برنامه" in text,
        "خط رسمی گردشکار هفت مرحلهای یافت نشد",
    )

    # --- خودآزمون، گزارش و قواعد -------------------------------------------
    for marker in ("پروتکل خودآزمون پیش از تحویل", "قالب گزارش پایان کار", "قواعد اجرایی همیشگی"):
        check(marker in text, f"بخش «{marker}» در مانیفست یافت نشد")

    # --- پیوند AGENTS.md ----------------------------------------------------
    check("docs/SKILL-MANIFEST-FA.md" in agents_text, "AGENTS.md به مانیفست ارجاع نمیدهد")
    check("موقت / فقط این جلسه" in agents_text, "AGENTS.md وضعیت حافظه را اعلام نکرده است")
    check("last verified" in agents_text, "AGENTS.md فاقد تاریخ last verified است")
    check(len(agents_text.splitlines()) <= 120, "AGENTS.md بیش از ۱۲۰ خط است؛ خلاصهتر شود")

    # --- آلودگی خط نوشتاری --------------------------------------------------
    for label, content in (("docs/SKILL-MANIFEST-FA.md", text), ("AGENTS.md", agents_text)):
        for issue in foreign_characters(content):
            check(False, f"{label}: کاراکتر بیگانه → {issue}")

    # --- نتیجه --------------------------------------------------------------
    print(f"Skill Manifest: {len(core)} مهارت اصلی + {len(complementary)} مکمل = {len(secs)} مهارت")
    print(f"چکهای اجراشده: {checks}")
    if failures:
        print(f"\nFAIL — {len(failures)} مورد:")
        for failure in failures:
            print(f"  - {failure}")
        return 1
    print("PASS — مانیفست کامل، ساختاریافته و صادقانه است.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
