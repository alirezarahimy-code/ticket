#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""ابزار مشترک تست‌های صفحهٔ دانلود (`downloads/index.html`).

قاعدهٔ پروژه: صفحهٔ دانلود فقط **جدیدترین بستهٔ منتشرشده** را نشان می‌دهد.
پیش از این، هر تست نسخهٔ خودش را به‌صورت رشتهٔ ثابت در همین صفحه بررسی می‌کرد؛
نتیجه این بود که با هر انتشار، ده‌ها تست کهنه می‌شد و صفحهٔ دانلود هم در عمل
روی نسخهٔ ۱.۶۲.۳۶ گیر کرد، در حالی که سامانه روی ۱.۶۲.۵۹ بود.

اکنون همهٔ این تست‌ها یک بررسی مشترک و **نسخه‌آگنوستیک** دارند:
فهرست بسته‌های منتشرشده را از لینک‌های واقعی صفحه می‌خوانند و آرشیوهای تاریخی یا
نامزدهای موجود در `downloads/` را انتشار فرض نمی‌کنند. صفحه باید دقیقاً یک ZIP
معتبر، لینک SHA-256 و گزارش تغییرات داشته باشد.
"""

from __future__ import annotations

import html
import json
import re
from pathlib import Path
from urllib.parse import unquote, urlsplit
import zipfile

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


def published_package_names() -> list[str]:
    """ZIPهایی را برمی‌گرداند که صفحهٔ دانلود واقعاً به آن‌ها لینک داده است.

    `downloads/` همچنین آرشیوهای تاریخی و بسته‌های نامزدِ انتشار دارد. حضور یک ZIP
    در پوشه به‌تنهایی به معنای انتشار آن نیست؛ لینک صفحهٔ دانلود مرجع عمومی است.
    """
    page = PAGE.read_text(encoding="utf-8")
    hrefs = re.findall(r"<a\b[^>]*\bhref\s*=\s*(['\"])(.*?)\1", page, re.I | re.S)
    names: list[str] = []
    for _, raw_href in hrefs:
        href = html.unescape(raw_href.strip())
        path = unquote(urlsplit(href).path)
        name = Path(path).name
        if name.lower().endswith(".zip") and name not in names:
            names.append(name)
    return names


def published_packages() -> list[dict]:
    """بسته‌های معتبر و موجود که صفحهٔ دانلود به آن‌ها لینک داده است."""
    available = {package["name"]: package for package in update_packages()}
    names = published_package_names()
    missing = [name for name in names if name not in available]
    assert not missing, "صفحهٔ دانلود به ZIP ناموجود یا بدون manifest لینک داده است: " + ", ".join(missing)
    return [available[name] for name in names]


def latest_package() -> dict:
    """بستهٔ یگانهٔ منتشرشده در صفحهٔ دانلود؛ آرشیوها/نامزدها مبنای انتشار نیستند."""
    packages = published_packages()
    assert packages, "صفحهٔ دانلود هیچ بستهٔ ZIP معتبری را منتشر نکرده است"
    return max(packages, key=lambda p: (version_tuple(p["version"]), p["newest_member"], p["name"]))


def latest_package_name() -> str:
    return latest_package()["name"]


def check_download_page_lists_only_latest_package() -> None:
    """صفحهٔ دانلود دقیقاً یک ZIP منتشرشده، checksum معتبر و گزارش تغییرات دارد."""
    page = PAGE.read_text(encoding="utf-8")
    names = published_package_names()
    assert len(names) == 1, f"صفحهٔ دانلود باید فقط یک بستهٔ جاری داشته باشد؛ لینک‌ها: {names}"
    latest = latest_package()
    name = latest["name"]
    stem = name[: -len(".zip")]

    assert f"{stem}.zip.sha256" in page, "لینک SHA-256 بستهٔ جاری در صفحه نیست"
    checksum = DOWNLOADS / f"{name}.sha256"
    assert checksum.is_file(), f"فایل SHA-256 بستهٔ جاری وجود ندارد: {checksum.name}"
    assert "CHANGE-REPORT" in page, "لینک گزارش تغییرات در صفحه نیست"


def read(relative: str) -> str:
    """خواندن فایل مخزن با UTF-8 (سازگار با بقیهٔ تست‌های ایستا)."""
    return (ROOT / relative).read_text(encoding="utf-8")
