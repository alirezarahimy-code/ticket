#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""جلوگیری از تکرار «تست پینشدهٔ کهنه» — مقایسهٔ پینهای ?v= با فایلهای زنده.

در ۲۰۲۶-۱۰-۲۷، ۱۳ پین کهنه در ۷ تست باعث FAIL بودند. این تست تضمین میکند
هر پین `?v=`ای که در یک فایل تست به **فایل زنده** (نه بستهٔ ZIP آرشیو) اشاره
میکند، با نسخهٔ واقعی همان asset هماهنگ باشد.

قواعد:
  - فقط assetهایی بررسی میشوند که در یکی از هاستهای زنده پیدا شوند.
  - استثناها (بستههای ZIP تاریخی، رشتههای `not in`) در لیست مجاز هستند؛
    چون آن پینها عمداً نسخهٔ قدیمی بستهٔ آرشیو شده را تأیید میکنند.

اجرا: python3 tests/test_cache_pin_consistency_static.py
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

HOSTS = {
    "food-ticket-web/index.html": "food-ticket-web/index.html",
    "index.php": "index.php",
    "food-ticket.php": "food-ticket.php",
    "food-order.php": "food-order.php",
}

# پینهایی که عمداً به بستهٔ ZIP تاریخی یا نسخهٔ قدیمی اشاره میکنند.
ALLOWED_LEGACY = {
    # بستهٔ آرشیو ۱.۶۲.x — محتوای ZIP نباید تغییر کند
    "tests/test_food_update_package_assets.py": {
        "food-manual-attendance.js", "food-menu.js", "food-groups.js",
    },
    # چک `not in` برای اطمینان از حذف نسخهٔ قدیمی
    "tests/test_release_1_62_15_service_modal_fix_static.py": {"style.css", "app.js"},
}

ASSET_PIN_RE = re.compile(r"([\w./-]+\.(?:js|css))\?v=([\w.\-]+)")

# برخی assetها در چند هاست با نسخههای متفاوت لود میشوند (مثلاً food-order.css
# در food-order.php نسخهٔ ۱۷ و در پنل غذا نسخهٔ ۱۳). پس نمیتوان یک نسخهٔ
# سراسری برای آنها در نظر گرفت؛ بلکه باید مجموعهٔ نسخههای مجاز را نگه داشت.
MULTI_HOST_ASSETS = {"food-order.css", "food-menu.css", "food-menu.js"}


def live_versions() -> tuple[dict[str, str], dict[str, set[str]]]:
    single: dict[str, str] = {}
    multi: dict[str, set[str]] = {}
    for rel in HOSTS.values():
        path = ROOT / rel
        if not path.is_file():
            continue
        text = path.read_text(encoding="utf-8")
        for match in ASSET_PIN_RE.finditer(text):
            asset, version = match.group(1), match.group(2)
            basename = asset.rsplit("/", 1)[-1]
            if basename in MULTI_HOST_ASSETS:
                multi.setdefault(basename, set()).add(version)
            else:
                single.setdefault(asset, version)
    return single, multi


def main() -> int:
    live, multi = live_versions()
    mismatches: list[str] = []

    for test_file in sorted((ROOT / "tests").glob("test_*.py")):
        rel = str(test_file.relative_to(ROOT))
        legacy_assets = ALLOWED_LEGACY.get(rel, set())
        text = test_file.read_text(encoding="utf-8")
        for lineno, line in enumerate(text.splitlines(), 1):
            # ردیفهای assertNotIn / "not in" عمداً نسخهٔ قدیمی را چک میکنند.
            if "assertNotIn" in line or " not in " in line or "not in src" in line:
                continue
            for match in ASSET_PIN_RE.finditer(line):
                asset, pinned = match.group(1), match.group(2)
                basename = asset.rsplit("/", 1)[-1]
                if basename in legacy_assets:
                    continue
                if basename in multi:
                    if pinned not in multi[basename]:
                        mismatches.append(
                            f"{rel}:{lineno}: {asset} → پین={pinned} | "
                            f"نسخههای مجاز={sorted(multi[basename])}"
                        )
                    continue
                actual = live.get(asset)
                if actual is None:
                    continue  # asset در هاستهای زنده نیست (مثلاً بستهٔ ZIP)
                if actual != pinned:
                    mismatches.append(
                        f"{rel}:{lineno}: {asset} → پین={pinned} | واقعی={actual}"
                    )

    print(f"تست همخوانی پینهای کش: {len(live)} نسخهٔ تکیهاست + "
          f"{len(multi)} asset چندهاست بررسی شد")
    if mismatches:
        print(f"FAIL — {len(mismatches)} پین کهنه:")
        for item in mismatches:
            print(f"  - {item}")
        print("\nراهحل: پین تست یا ?v= هاست را در همان کامیت هماهنگ کنید.")
        return 1
    print("PASS — تمام پینهای ?v= فایلهای زنده هماهنگاند.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
