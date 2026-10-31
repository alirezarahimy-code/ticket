#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""ابزار مشترک تست‌های صفحهٔ دانلود (`downloads/index.html`).

قاعدهٔ پروژه: صفحهٔ دانلود فقط **جدیدترین بستهٔ منتشرشده** را نشان می‌دهد.
پیش از این، هر تست نسخهٔ خودش را به‌صورت رشتهٔ ثابت در همین صفحه بررسی می‌کرد؛
نتیجه این بود که با هر انتشار، ده‌ها تست کهنه می‌شد و صفحهٔ دانلود هم در عمل
روی نسخهٔ ۱.۶۲.۳۶ گیر کرد، در حالی که سامانه روی ۱.۶۲.۵۹ بود.

اکنون همهٔ این تست‌ها یک بررسی مشترک و **نسخه‌آگنوستیک** دارند:
تازه‌ترین بسته از `downloads/*.zip` (بر اساس نسخهٔ manifest و سپس زمان داخلی ZIP)
باید در صفحه باشد، لینک SHA-256 داشته باشد و هیچ بستهٔ قدیمی‌تری در صفحه نباشد.
"""

from __future__ import annotations

import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / "downloads"
PAGE = DOWNLOADS / "index.html"


def version_tuple(value: str) -> tuple[int, ...]:
    parts = re.findall(r"\d+", value or "")
    return tuple(int(p) for p in parts) or (0,)


def update_packages() -> list[dict]:
    """همهٔ ZIPهای دارای manifest معتبر در `downloads/`."""
    packages: list[dict] = []
    for path in sorted(DOWNLOADS.glob("*.zip")):
        try:
            with zipfile.ZipFile(path) as archive:
                manifest = json.loads(archive.read("manifest.json"))
                newest_member = max(
                    (info.date_time for info in archive.infolist()),
                    default=(0, 0, 0, 0, 0, 0),
                )
        except (zipfile.BadZipFile, KeyError, ValueError):
            continue
        version = str(manifest.get("version") or "")
        if not version:
            continue
        packages.append(
            {
                "name": path.name,
                "version": version,
                "path": path,
                "newest_member": newest_member,
            }
        )
    return packages


def latest_package() -> dict:
    """جدیدترین بسته: بیشترین نسخه، و در نسخه‌های برابر، تازه‌ترین محتوا."""
    packages = update_packages()
    assert packages, "هیچ بستهٔ به‌روزرسانی با manifest در downloads/ نیست"
    best_version = max(version_tuple(p["version"]) for p in packages)
    newest = [p for p in packages if version_tuple(p["version"]) == best_version]
    return max(newest, key=lambda p: (p["newest_member"], p["name"]))


def latest_package_name() -> str:
    return latest_package()["name"]


def check_download_page_lists_only_latest_package() -> None:
    """صفحهٔ دانلود: فقط جدیدترین بسته، همراه لینک SHA-256 و گزارش تغییرات."""
    page = PAGE.read_text(encoding="utf-8")
    latest = latest_package()
    name = latest["name"]
    stem = name[: -len(".zip")]

    assert name in page, f"صفحهٔ دانلود جدیدترین بستهٔ {latest['version']} ({name}) را نشان نمی‌دهد"
    assert f"{stem}.zip.sha256" in page, "لینک SHA-256 جدیدترین بسته در صفحه نیست"
    assert "CHANGE-REPORT" in page, "لینک گزارش تغییرات در صفحه نیست"

    older = [
        package["name"]
        for package in update_packages()
        if package["name"] != name
        and version_tuple(package["version"]) < version_tuple(latest["version"])
        and package["name"] in page
    ]
    assert not older, "صفحهٔ دانلود بسته‌های قدیمی‌تر را هم نشان می‌دهد: " + ", ".join(sorted(older))


def read(relative: str) -> str:
    """خواندن فایل مخزن با UTF-8 (سازگار با بقیهٔ تست‌های ایستا)."""
    return (ROOT / relative).read_text(encoding="utf-8")
