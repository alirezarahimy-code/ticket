#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""تست نگهبان «دکمهٔ استارت جلسه».

تضمین میکند هر چیزی که برای بازیابی کامل زمینه در یک جلسهٔ جدید لازم است
وجود داشته باشد و به درستی به هم وصل باشد.

اجرا: python3 tests/test_session_boot_static.py
"""

from __future__ import annotations

import json
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BOOT = ROOT / "tools" / "session-boot.py"

POINTERS = {
    "AGENTS.md": "tools/session-boot.py",
    "CLAUDE.md": "tools/session-boot.py",
    ".github/copilot-instructions.md": "tools/session-boot.py",
    ".cursor/rules/arena-boot.mdc": "tools/session-boot.py",
    ".cursorrules": "tools/session-boot.py",
    "docs/SESSION-BOOT-FA.md": "tools/session-boot.py",
}

CORE_DOCS = [
    "docs/SKILL-MANIFEST-FA.md",
    "docs/SYSTEM-MAP-FA.md",
    "docs/SESSION-BOOT-FA.md",
    "docs/system-map/summary.json",
    "tools/build_system_map.py",
    "tools/find-in-system.py",
    "tools/session-boot.py",
]

GUARD_TESTS = [
    "tests/test_skill_manifest_static.py",
    "tests/test_system_map_static.py",
    "tests/test_cache_pin_consistency_static.py",
]

TRIGGER_PHRASES = ("بهروز شو", "بوت کن")


def main() -> int:
    failures: list[str] = []
    checks = 0

    def check(condition: bool, message: str) -> None:
        nonlocal checks
        checks += 1
        if not condition:
            failures.append(message)

    # ۱. فایلهای هسته -------------------------------------------------------
    for rel in CORE_DOCS:
        check((ROOT / rel).is_file(), f"فایل هسته وجود ندارد: {rel}")
    for rel in GUARD_TESTS:
        check((ROOT / rel).is_file(), f"تست نگهبان وجود ندارد: {rel}")

    # ۲. پوینترها -----------------------------------------------------------
    for rel, needle in POINTERS.items():
        path = ROOT / rel
        check(path.is_file(), f"فایل پوینتر وجود ندارد: {rel}")
        if path.is_file():
            text = path.read_text(encoding="utf-8")
            check(needle in text, f"{rel} به {needle} ارجاع نمیدهد")
            has_trigger = any(p in text for p in TRIGGER_PHRASES)
            check(has_trigger, f"{rel} عبارت «بهروز شو»/«بوت کن» را نمیشناسد")

    # ۳. مانیفست همچنان کامل است --------------------------------------------
    manifest = (ROOT / "docs" / "SKILL-MANIFEST-FA.md").read_text(encoding="utf-8")
    for sid in ("S01", "S23", "C01", "C08"):
        check(f"| {sid} |" in manifest, f"مانیفست فاقد مهارت {sid} است")
    check("موقت / فقط این جلسه" in manifest,
          "مانیفست وضعیت حافظهٔ موقت را اعلام نکرده است")

    # ۴. نقشه معتبر است ------------------------------------------------------
    summary_path = ROOT / "docs" / "system-map" / "summary.json"
    if summary_path.is_file():
        summary = json.loads(summary_path.read_text(encoding="utf-8"))
        check(summary.get("generated_by") == "tools/build_system_map.py",
              "summary.json منبع تولید را اعلام نکرده")
        check(summary.get("update_packages", 0) >= 110, "تعداد بستههای آپدیت کم است")
        check(summary.get("unique_tables", 0) >= 70, "تعداد جداول کم است")

    # ۵. بوت واقعاً اجرا میشود و خروجی کامل میدهد ----------------------------
    #    نکته: حتماً --skip-tests بزن؛ وگرنه بوت، این تست را دوباره صدا میزند
    #    و حلقهٔ بازگشتی بیپایان (تایماوت) میسازد.
    if BOOT.is_file():
        proc = subprocess.run(
            [sys.executable, str(BOOT), "--no-refresh", "--skip-tests"],
            cwd=ROOT, capture_output=True, text=True, timeout=90,
        )
        out = proc.stdout
        for marker in (
            "SESSION BOOT", "وضعیت حافظه", "مهارتهای نصبشده",
            "گردشکار الزامی", "قواعد همیشگی", "قواعد ثابت کاربر",
            "نقشهٔ سامانه", "فایلهای بارگذاری خودکار", "BOOT COMPLETE",
        ):
            check(marker in out, f"خروجی بوت فاقد بخش «{marker}» است")
        check("31 مهارت" in out or "۳۱ مهارت" in out,
              "خروجی بوت تعداد ۳۱ مهارت را نشان نمیدهد")

        # وضعیت نقشه/تستها ممکن است STALE باشد (فایلهای تازه اضافه شدهاند)؛
        # خود بوت در حالت عادی آن را بازتولید میکند. اینجا فقط ساختار مهم است.
        check("تستهای نگهبان: بهدرخواست فراخوان رد شدند" in out,
              "خروجی بوت نشان نمیدهد که تستها برای جلوگیری از حلقه رد شدهاند")
        check("--skip-tests" in (ROOT / "tools" / "session-boot.py").read_text(encoding="utf-8"),
              "session-boot.py فلگ --skip-tests را نمیشناسد")

    # ۶. فرم ماشینی (--json) -------------------------------------------------
    if BOOT.is_file():
        proc = subprocess.run(
            [sys.executable, str(BOOT), "--no-refresh", "--json", "--skip-tests"],
            cwd=ROOT, capture_output=True, text=True, timeout=90,
        )
        try:
            data = json.loads(proc.stdout)
            check("skills" in data and len(data["skills"]) >= 31,
                  "خروجی JSON فاقد فهرست کامل مهارتهاست")
            check("guard_tests" in data, "خروجی JSON فاقد نتایج تستهای نگهبان است")
        except json.JSONDecodeError:
            check(False, "خروجی --json معتبر نیست")

    print(f"چکهای بوت: {checks}")
    if failures:
        print(f"\nFAIL — {len(failures)} مورد:")
        for item in failures:
            print(f"  - {item}")
        return 1
    print("PASS — دکمهٔ استارت جلسه کامل و قابلاطمینان است.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
