#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""تست نگهبان: منبع حقیقت نسخه — «کد مخزن» باید همان نسخه‌ای باشد که منتشر شده است.

ریشهٔ اشکالی که این تست می‌گیرد (رخداد ۲۰۲۶-۱۱-۰۱):
شاخهٔ `main` روی نسخهٔ `1.62.36` مانده بود، در حالی که آخرین بستهٔ منتشرشده در
`downloads/` نسخهٔ `1.62.59` بود. یعنی کد مخزن نسبت به سامانهٔ زنده عقب افتاده
بود؛ هر ایجنت یا توسعه‌دهنده‌ای که از `main` شروع می‌کرد، نسخهٔ کهنه را
«وضعیت فعلی» فرض می‌کرد.

این تست سه چیز را ماشینی بررسی می‌کند:

1. `VERSION` مخزن باید برابر **بیشترین نسخهٔ manifest** در `downloads/` باشد
   (یا از آن جدیدتر؛ عقب‌ماندن ممنوع است).
2. محتوای فایل‌های برنامه در **تازه‌ترین بستهٔ همان نسخه** باید بایت‌به‌بایت با
   مخزن یکی باشد؛ اگر بسته جدیدتر از مخزن باشد، یعنی نسخه‌ای منتشر شده که کدش
   در Git نیست.
3. اگر `files/VERSION` داخل بسته با نسخهٔ manifest آن یکی نباشد (باگ بستهٔ
   منتشرشده)، تست فقط وقتی سبز می‌ماند که این ناهم‌خوانی در فهرست
   `KNOWN_PACKAGE_VERSION_DRIFT` ثبت شده باشد؛ ناهم‌خوانی تازه = شکست.

اجرا:
    python3 tests/test_version_source_of_truth_static.py
ادغام در pytest: به‌صورت `test_*` خودکار شناسایی می‌شود.
"""

from __future__ import annotations

import json
import re
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / "downloads"

# ناهم‌خوانی‌های شناخته‌شدهٔ فایل VERSION داخل بسته‌های منتشرشده.
# کلید = نام فایل ZIP، مقدار = نسخه‌ای که داخل بسته نوشته شده (باگ بسته، نه مخزن).
KNOWN_PACKAGE_VERSION_DRIFT: dict[str, str] = {
    # manifest می‌گوید 1.62.59 ولی files/VERSION داخل بسته 1.62.58 است.
    "ticket-update-1.62.59-cumulative-org-card-drag-20261031.zip": "1.62.58",
}

# فایل‌هایی که مقایسهشان با بسته معنی ندارد (خودِ نسخه، گزارش‌های تغییرات).
EXCLUDED_FROM_COMPARE = {"VERSION"}


def version_tuple(value: str) -> tuple[int, ...]:
    parts = re.findall(r"\d+", value or "")
    return tuple(int(p) for p in parts) or (0,)


def repo_version() -> str:
    return (ROOT / "VERSION").read_text(encoding="utf-8").strip()


def scan_packages() -> list[dict]:
    """همهٔ ZIPهایی که manifest.json معتبر دارند را برمی‌گرداند."""
    packages: list[dict] = []
    for path in sorted(DOWNLOADS.glob("*.zip")):
        try:
            with zipfile.ZipFile(path) as archive:
                manifest = json.loads(archive.read("manifest.json"))
                newest_member = max(
                    (info.date_time for info in archive.infolist()),
                    default=(0, 0, 0, 0, 0, 0),
                )
                members = {
                    info.filename: info
                    for info in archive.infolist()
                    if info.filename.startswith("files/") and not info.is_dir()
                }
        except (zipfile.BadZipFile, KeyError, ValueError):
            continue  # آرشیو منبع/پشتیبان است، نه بستهٔ به‌روزرسانی
        version = str(manifest.get("version") or "")
        if not version:
            continue
        packages.append(
            {
                "path": path,
                "manifest": manifest,
                "version": version,
                "newest_member": newest_member,
                "members": members,
            }
        )
    return packages


def resolve_repo_path(relative: str, downloads: Path = DOWNLOADS) -> Path | None:
    """مسیر معادل فایل داخل بسته را در مخزن پیدا می‌کند.

    گزارش‌های تغییرات در ریشهٔ بسته هستند ولی در مخزن زیر `downloads/` بایگانی
    می‌شوند؛ بقیهٔ فایل‌ها دقیقاً با مسیر خودشان در مخزن مقایسه می‌شوند.
    """
    direct = ROOT / relative
    if direct.is_file():
        return direct
    archived = downloads / Path(relative).name
    if archived.is_file():
        return archived
    return None


def newest_package_for(version: str, packages: list[dict]) -> dict:
    same = [p for p in packages if p["version"] == version]
    return max(same, key=lambda p: (p["newest_member"], p["path"].name))


class VersionSourceOfTruthTests(unittest.TestCase):
    def test_repo_version_is_not_behind_the_newest_published_package(self) -> None:
        packages = scan_packages()
        self.assertTrue(packages, "هیچ بستهٔ به‌روزرسانی با manifest در downloads/ پیدا نشد")
        newest_shipped = max(packages, key=lambda p: version_tuple(p["version"]))["version"]
        current = repo_version()
        self.assertGreaterEqual(
            version_tuple(current),
            version_tuple(newest_shipped),
            msg=(
                f"VERSION مخزن = {current} ولی تازه‌ترین بستهٔ منتشرشده = {newest_shipped}؛ "
                "یعنی کد مخزن از سامانهٔ زنده عقب است. ابتدا فایل‌های همان بسته را "
                "(پس از تأیید کاربر) وارد مخزن کنید و VERSION را هماهنگ کنید."
            ),
        )

    def test_repo_matches_the_newest_package_of_the_current_version(self) -> None:
        current = repo_version()
        packages = scan_packages()
        candidates = [p for p in packages if p["version"] == current]
        if not candidates:
            self.skipTest(f"برای نسخهٔ {current} بسته‌ای در downloads/ نیست")
        package = newest_package_for(current, packages)
        archive_bytes_checked = 0
        mismatches: list[str] = []
        with zipfile.ZipFile(package["path"]) as archive:
            for name in sorted(package["members"]):
                relative = name[len("files/"):]
                if relative in EXCLUDED_FROM_COMPARE:
                    continue
                target = resolve_repo_path(relative)
                if target is None:
                    mismatches.append(f"{relative} → در مخزن وجود ندارد")
                    continue
                if archive.read(name) != target.read_bytes():
                    mismatches.append(f"{relative} → محتوای مخزن با بسته یکسان نیست")
                archive_bytes_checked += 1
        self.assertGreater(archive_bytes_checked, 0, "بسته هیچ فایل برنامه‌ای ندارد")
        self.assertEqual(
            mismatches,
            [],
            msg=(
                f"مخزن با تازه‌ترین بستهٔ {current} "
                f"({package['path'].name}) هم‌خوان نیست:\n  - "
                + "\n  - ".join(mismatches)
            ),
        )

    def test_package_manifest_version_matches_its_own_version_file(self) -> None:
        drift: list[str] = []
        for package in scan_packages():
            name = package["path"].name
            members = package["members"]
            if "files/VERSION" not in members:
                continue
            with zipfile.ZipFile(package["path"]) as archive:
                inside = archive.read("files/VERSION").decode("utf-8", "replace").strip()
            expected_drift = KNOWN_PACKAGE_VERSION_DRIFT.get(name)
            if expected_drift is not None:
                if inside != expected_drift:
                    drift.append(
                        f"{name}: ناهم‌خوانی ثبت‌شده {expected_drift} است ولی داخل بسته {inside} است"
                    )
                continue
            if inside != package["version"]:
                drift.append(
                    f"{name}: manifest={package['version']} ولی files/VERSION={inside}"
                )
        self.assertEqual(
            drift,
            [],
            msg=(
                "ناهم‌خوانی نسخهٔ داخل بسته با manifest (بستهٔ منتشرشده ناسالم است):\n  - "
                + "\n  - ".join(drift)
            ),
        )

    def test_docs_do_not_advertise_an_older_version_than_version_file(self) -> None:
        current = repo_version()
        readme = (ROOT / "README.md").read_text(encoding="utf-8")
        match = re.search(r"نسخه تحویل فعلی:\s*`([^`]+)`", readme)
        self.assertIsNotNone(match, "خط «نسخه تحویل فعلی» در README.md پیدا نشد")
        self.assertEqual(
            match.group(1).strip(),
            current,
            msg="README.md نسخهٔ قدیمی را تبلیغ می‌کند؛ با VERSION هماهنگ کنید",
        )


if __name__ == "__main__":
    unittest.main(verbosity=2)
