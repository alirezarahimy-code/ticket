#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۴ — نسخهٔ «بازسازی‌شده روی پایهٔ واقعی ۱.۶۲٫۹۳».

تاریخچهٔ صادقانه:
- «نسخهٔ اول» همین بسته (۲۰۲۶‑۱۱‑۱۰) از فایل‌های شاخهٔ RC/۱٫۶۳ ساخته شده بود. سرور تولید شما
  (v1.62.88 + بسته‌های میانی ۱٫۶۲٫۹۰/۱٫۶۲٫۹۳) پوشهٔ `modules/request-center` و `lib/render.php` را
  ندارد؛ بنابراین `index.php` آن بسته با `require` ناموفق، همهٔ صفحه‌ها را به صفحهٔ خطای عمومی برده بود.
- این نسخه (rev.2) پایه را **دقیقاً مثل سرور شما** بازسازی می‌کند:
  `git archive v1.62.88` + پوشش بستهٔ میانی ۱٫۶۲٫۹۰ + پوشش بستهٔ میانی ۱٫۶۲٫۹۳ (هر دو با SHA‑256 ثبت‌شده).
- سپس فقط دلتای پنج محور ۱٫۶۲٫۹۴ اعمال می‌شود:
  * ۱۰ فایل که در شاخهٔ RC فقط در کمیت ۱٫۶۲٫۹۴ (bd49963) تغییر کرده‌اند، از همان کامیت خوانده می‌شوند؛
  * `index.php` سه ویرایش حداقلی می‌گیرد (pin استایل 3.4.61، pin اسکریپت organization.js v=23،
    لینک تب «گروه‌های دسترسی»)؛ هیچ تغییری از «مرکز درخواست‌ها» وارد نمی‌شود.
- دروازهٔ جدیدِ اعتبارسنجی: هر مسیر `__DIR__` است که در فایل‌های PHP ارسالی **require** می‌شود
  باید در پایهٔ بازسازی‌شده وجود داشته باشد؛ و هیچ رشتهٔ `modules/request-center` در هیچ فایل ارسالی نیست.

- «مرکز درخواست‌ها» / RC / مهاجرت ۰۳۵ در این بسته نیست و مهاجرت تازه‌ای هم ندارد؛
- فایل VERSION داخل ZIP به مقدار «1.62.94» تزریق می‌شود؛
- انتشار (تگ/merge به main) فقط با دستور صریح مالک انجام می‌شود.
"""

from __future__ import annotations

import hashlib
import io
import json
import os
import shutil
import subprocess
import tarfile
import tempfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.94"
REQUIRES_VERSION = "1.62.93"
RELEASE_DATE = "2026-11-10"
PACKAGE_NAME = "ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.94.md"
ZIP_DATE = (2026, 11, 10, 0, 0, 0)
BASE_TAG = "v1.62.88"
INTERIM = [
    ("downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip",
     "downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip.sha256"),
    ("downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip",
     "downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip.sha256"),
]
# کامیت «نسخهٔ منبع» برای فایل‌های پنج محور (تنها کامیتی که این فایل‌ها را در شاخهٔ RC لمس کرده است)
DELTA_REF = "bd49963"
DELTA_FROM_REF = [
    "organization.php",
    "actions/org-actions.php",
    "assets/organization.js",
    "assets/style.css",
    "food-order.php",
    "food-reconciliation.php",
    "assets/food-order.js",
    "assets/food-menu.js",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/index.html",
]
MIGRATIONS: list[str] = []


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def read_sidecar(path: Path) -> str:
    return path.read_text(encoding="ascii").split()[0].strip().lower()


def verify_interim() -> list[Path]:
    out = []
    for pkg_rel, sidecar_rel in INTERIM:
        pkg, sidecar = ROOT / pkg_rel, ROOT / sidecar_rel
        if not pkg.is_file() or not sidecar.is_file():
            raise SystemExit(f"بستهٔ میانی ثبت‌شده موجود نیست: {pkg_rel}")
        got = sha256(pkg.read_bytes())
        if got != read_sidecar(sidecar):
            raise SystemExit(f"SHA-256 بستهٔ میانی با فایل .sha256 ثبت‌شده نمی‌خواند: {pkg_rel}")
        out.append(pkg)
    return out


def git_bytes(ref: str, path: str) -> bytes:
    return subprocess.check_output(["git", "show", f"{ref}:{path}"], cwd=ROOT)


def reconstruct_base(dest: Path) -> None:
    """پایهٔ واقعی ۱٫۶۲٫۹۳ تولید: v1.62.88 + هر دو بستهٔ میانی."""
    dest.mkdir(parents=True, exist_ok=True)
    archive = subprocess.check_output(["git", "archive", BASE_TAG], cwd=ROOT)
    with tarfile.open(fileobj=io.BytesIO(archive)) as tar:
        for member in tar.getmembers():
            name = member.name
            pure = PurePosixPath(name)
            if pure.is_absolute() or any(part in ("", ".", "..") for part in pure.parts):
                raise SystemExit(f"git archive مسیر نامعتبر دارد: {name}")
            if not (member.isfile() or member.isdir()):
                continue
            tar.extract(member, dest)
    import zipfile
    for pkg in verify_interim():
        with zipfile.ZipFile(pkg) as z:
            for name in z.namelist():
                if not name.startswith("files/") or name.endswith("/"):
                    continue
                rel = name[len("files/"):]
                target = dest / rel
                target.parent.mkdir(parents=True, exist_ok=True)
                target.write_bytes(z.read(name))
    # دروازهٔ بازسازی: بسته‌های میانی `index.php` را دست نزده‌اند؛ باید همان فایل تگ بماند.
    if (dest / "index.php").read_bytes() != git_bytes(BASE_TAG, "index.php"):
        raise SystemExit("بازسازی پایه خراب شد: index.php بعد از پوشش بسته‌های میانی تغییر کرده است.")


def apply_index_php_delta(source: str) -> str:
    """سه ویرایش حداقلی روی index.php پایهٔ ۱٫۶۲٫۹۳ — هر کدام دقیقاً یک‌بار اعمال می‌شوند."""
    replacements = [
        ("assets/style.css?v=3.4.60", "assets/style.css?v=3.4.61"),
        ("assets/organization.js?v=22", "assets/organization.js?v=23"),
        (
            "    if ($canManageSettingsUsers) {\n"
            "        $tabLinks .= '<a href=\"#roles\">نقش‌ها و دسترسی‌ها</a>';\n"
            "    }\n",
            "    if ($canManageSettingsUsers) {\n"
            "        $tabLinks .= '<a href=\"#roles\">نقش‌ها و دسترسی‌ها</a>';\n"
            "    }\n"
            "    if ($canManageSettingsUsers) {\n"
            "        $tabLinks .= '<a href=\"#access-groups\">گروه‌های دسترسی</a>';\n"
            "    }\n",
        ),
    ]
    out = source
    for old, new in replacements:
        count = out.count(old)
        if count != 1:
            raise SystemExit(f"ویرایش index.php دقیق انجام نشد ({count} بار یافت شد؛ باید ۱ بار): {old[:60]!r}")
        out = out.replace(old, new, 1)
    return out


def safe_archive_path(name: str) -> bool:
    p = PurePosixPath(name)
    win = PureWindowsPath(name)
    return (
        bool(name)
        and not p.is_absolute()
        and not win.is_absolute()
        and not win.drive
        and "\\" not in name
        and "//" not in name
        and not name.endswith("/")
        and all(part not in ("", ".", "..") for part in p.parts)
    )


def check_requires_exist(base: Path, php_files: dict[str, bytes]) -> None:
    """هر require مسیر __DIR__ در فایل‌های ارسالی باید در پایهٔ بازسازی‌شده موجود باشد."""
    import re
    pattern = re.compile(r"(?:require|require_once|include|include_once)\s*\(?\s*__DIR__\s*\.\s*'([^']+)'")
    missing = []
    for name, data in php_files.items():
        for match in pattern.finditer(data.decode("utf-8")):
            rel = match.group(1).lstrip("/")
            if not (base / rel).is_file():
                missing.append(f"{name} -> {rel}")
    if missing:
        raise SystemExit("require ناموفق در پایهٔ هدف (این دقیقاً همان چیزی است که نسخهٔ اول بسته را شکست):\n  " + "\n  ".join(missing))


def check_no_foreign_content(files: dict[str, bytes]) -> None:
    """هیچ اثری از «مرکز درخواست‌ها»/RC در فایل‌های متنی نباید بماند."""
    import re
    pattern = re.compile(r"modules/request-center|request-center\.php|REQUEST_CENTER_INDEPENDENT_ENTRY|request_center_|rc_candidate", re.IGNORECASE)
    for name, data in files.items():
        if not name.lower().endswith((".php", ".js", ".html", ".css", ".md", ".json")):
            continue
        hit = pattern.search(data.decode("utf-8", errors="ignore"))
        if hit:
            raise SystemExit(f"اثر «مرکز درخواست‌ها»/RC در {name}: {hit.group(0)}")


def build() -> tuple[Path, Path]:
    with tempfile.TemporaryDirectory(prefix="base-1.62.93-") as tmp:
        base = Path(tmp)
        reconstruct_base(base)

        files: dict[str, bytes] = {}
        for relative in DELTA_FROM_REF:
            files[relative] = git_bytes(DELTA_REF, relative)
        files["index.php"] = apply_index_php_delta((base / "index.php").read_text(encoding="utf-8")).encode("utf-8")

        code_files = dict(files)
        check_requires_exist(base, {k: v for k, v in code_files.items() if k.endswith(".php")})
        check_no_foreign_content(code_files)

        report = ROOT / "downloads" / REPORT_NAME
        if not report.is_file():
            raise SystemExit(f"گزارش تغییرات موجود نیست: {report}")
        files[REPORT_NAME] = report.read_bytes()
        files["VERSION"] = f"{RELEASE_VERSION}\n".encode("utf-8")

        manifest = {
            "name": "food-guest-org-groups-holiday",
            "version": RELEASE_VERSION,
            "date": RELEASE_DATE,
            "title": "مرج «پرس سفارش/تحویل واقعی پیمانکار»، ستون‌های جدای مهمان در «تطبیق روزانه»، کارت سوم «واحد مستقل» کنار مدیرعامل، اصلاح گروه‌های دسترسی و تعطیل رسمی (۱.۶۲.۹۳ ← ۱.۶۲.۹۴)",
            "requires_version": REQUIRES_VERSION,
            "migrations": list(MIGRATIONS),
            "files": sorted(files),
            "sha256": {name: sha256(files[name]) for name in sorted(files)},
            "release_status": "INTERIM_UPDATE_TEST_ON_STAGING_FIRST",
            "description": (
                "Interim 1.62.x update, rebuilt on the real production base (v1.62.88 + interim 1.62.90/1.62.93, "
                "byte-identical to the official intermediate packages): (1) portion merge (portion_quantity); "
                "(2) guest columns split in daily food reconciliation (22 export columns; total_employee_orders added); "
                "(3) third independent (liaison) unit slot beside the CEO (chart_side='center'); "
                "(4) org.manage access-group grant now enables org-tree editing; "
                "(5) holidays no longer block employee food ordering (label only). "
                "Contains NO Request Center content, NO RC-line index.php, NO lib/render.php, and NO new migrations. "
                "Install on staging first; production with owner sign-off. Supersedes the 2026-11-10 first build, "
                "which was built from RC-branch files and broke every page on the real base."
            ),
        }

        out_dir = ROOT / "downloads"
        out_dir.mkdir(parents=True, exist_ok=True)
        package_path = out_dir / PACKAGE_NAME
        temporary = package_path.with_suffix(package_path.suffix + ".tmp")

        def entry(name: str) -> zipfile.ZipInfo:
            info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            return info

        import zipfile
        try:
            with zipfile.ZipFile(temporary, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as z:
                z.writestr(entry("manifest.json"), (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8"))
                for name in sorted(files):
                    z.writestr(entry("files/" + name), files[name])
            validate_archive(temporary, manifest)
            temporary.replace(package_path)
            checksum = sha256(package_path.read_bytes())
            sidecar = out_dir / (PACKAGE_NAME + ".sha256")
            sidecar_tmp = sidecar.with_suffix(sidecar.suffix + ".tmp")
            sidecar_tmp.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
            sidecar_tmp.replace(sidecar)
            recorded = sidecar.read_text(encoding="ascii").split()[0]
            if recorded != sha256(package_path.read_bytes()):
                raise SystemExit("SHA-256 فایل جانبی با ZIP نهایی تطبیق ندارد.")
            print(f"OK {PACKAGE_NAME}")
            print(f"Files: {len(files)} | migrations: {len(MIGRATIONS)} | bytes: {package_path.stat().st_size}")
            print(f"SHA-256: {checksum}")
            return package_path, sidecar
        finally:
            if temporary.exists():
                temporary.unlink()


def validate_archive(path: Path, expected_manifest: dict) -> None:
    import zipfile
    with zipfile.ZipFile(path, "r") as z:
        bad = z.testzip()
        if bad is not None:
            raise SystemExit(f"CRC خراب در ZIP: {bad}")
        names = [i.filename for i in z.infolist()]
        if len(names) != len(set(n.casefold() for n in names)):
            raise SystemExit("ZIP مسیر تکراری یا تکرار بدون حساسیت به حروف دارد.")
        if any(not safe_archive_path(n) for n in names):
            raise SystemExit("ZIP مسیر مطلق یا traversal دارد.")
        lowered_names = [n.lower() for n in names]
        for token in ("request-center", "request_center", "requestcenter", "035-request", "rc-candidate"):
            if any(token in n for n in lowered_names):
                raise SystemExit(f"خطای حاکم: فایل مرتبط با «مرکز درخواست‌ها» در بسته پیدا شد: {token}")
        if "manifest.json" not in names:
            raise SystemExit("manifest.json در ZIP وجود ندارد.")
        got = json.loads(z.read("manifest.json"))
        if got != expected_manifest:
            raise SystemExit("manifest داخل ZIP با manifest ساخته‌شده یکسان نیست.")
        file_entries = {n[len("files/"):] for n in names if n.startswith("files/")}
        if file_entries != set(got["files"]):
            raise SystemExit("فهرست فایل‌های واقعی ZIP با manifest.files یکسان نیست.")
        migration_entries = {n[len("migrations/"):] for n in names if n.startswith("migrations/")}
        if migration_entries != set(got["migrations"]):
            raise SystemExit("فهرست مهاجرت‌های ZIP با manifest.migrations یکسان نیست.")
        hashes = got.get("sha256")
        if not isinstance(hashes, dict) or set(hashes) != set(got["files"]):
            raise SystemExit("کلیدهای manifest.sha256 باید دقیقاً با manifest.files برابر باشند.")
        for relative, expected in hashes.items():
            if sha256(z.read("files/" + relative)) != expected:
                raise SystemExit(f"عدم تطابق SHA-256 فایل: {relative}")
        if got.get("version") != RELEASE_VERSION or got.get("requires_version") != REQUIRES_VERSION:
            raise SystemExit("قرارداد نسخهٔ manifest نامعتبر است.")


if __name__ == "__main__":
    build()
