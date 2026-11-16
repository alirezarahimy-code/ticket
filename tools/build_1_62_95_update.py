#!/usr/bin/env python3
"""سازندهٔ بستهٔ میانی ۱.۶۲.۹۵ — «شماره برگه خروج» اجباری (CD/DVD) + غذا: پنجرهٔ سختگیرانهٔ Worker،
ثبت خودکار delivered در گزارش تحویل (مهاجرت ۰۳۷)، حذف رفرش خودکار گزارش‌ها، تأییدیهٔ تعطیل.

پایه: دقیقاً مثل سرور تولید بازسازی می‌شود —
`git archive v1.62.88` + بستهٔ میانی ۱٫۶۲٫۹۰ + ۱٫۶۲٫۹۳ + ۱٫۶۲٫۹۴ (هر سه با SHA‑256 ثبت‌شده).
سپس فقط دلتای همین چهار محور از **درخت‌کاری فعلی** (= HEAD شاخه) برداشته می‌شود؛ چون هر هانکِ
تفاضل پایه/درخت در زمان ساخت دستی بازبینی شد و هیچ محتوای RC/«مرکز درخواست‌ها» وارد بسته نمی‌شود.

- مهاجرت `037-food-auto-receipts-delivered.sql` علاوه‌بر `files/migrations/`، یک کپی هم در ریشهٔ ZIP دارد؛
- فایل VERSION داخل ZIP به مقدار «1.62.95» تزریق می‌شود و VERSION ریشه دست‌نخورده می‌ماند؛
- انتشار (تگ/release گیتهاب) همیشه با تصمیم صریح مالک انجام می‌شود.
"""

from __future__ import annotations

import hashlib
import io
import json
import subprocess
import tarfile
import tempfile
from pathlib import Path, PurePosixPath, PureWindowsPath

ROOT = Path(__file__).resolve().parent.parent
RELEASE_VERSION = "1.62.95"
REQUIRES_VERSION = "1.62.94"
RELEASE_DATE = "2026-11-11"
PACKAGE_NAME = "ticket-update-1.62.94-to-1.62.95-food-auto-receipts-cddvd-20261111.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.95.md"
ZIP_DATE = (2026, 11, 11, 0, 0, 0)
BASE_TAG = "v1.62.88"
INTERIM = [
    ("downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip",
     "downloads/ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip.sha256"),
    ("downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip",
     "downloads/ticket-update-1.62.90-to-1.62.93-food-reconciliation-leftover-20261106.zip.sha256"),
    ("downloads/ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip",
     "downloads/ticket-update-1.62.93-to-1.62.94-food-guest-org-holiday-20261110.zip.sha256"),
]
# دلتا: فایل‌هایی که بین پایهٔ ۱٫۶۲٫۹۴ و HEAD شاخه فقط با همین محورها تغییر کرده‌اند
# (تفاضل پایه/درخت هنگام ساخت بازبینی شد؛ فقط همین فایل‌ها و هانک‌های مصوب مالک).
DELTA_FROM_WORKTREE = [
    "assets/food-menu.js",
    "assets/food-order.js",
    "assets/food-order.css",
    "food-order.php",
    "food-ticket-web/assets/food-menu.js",
    "food-ticket-web/assets/food-order.css",
    "food-ticket-web/index.html",
    "cron/food_ticket_worker.php",
    "food-ticket-auto-receipts.php",
    "cd-dvd.php",
    "assets/cd-dvd.js",
    "migrations/037-food-auto-receipts-delivered.sql",
]
MIGRATIONS: list[str] = [
    "037-food-auto-receipts-delivered.sql",
]


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
        if sha256(pkg.read_bytes()) != read_sidecar(sidecar):
            raise SystemExit(f"SHA-256 بستهٔ میانی با فایل .sha256 ثبت‌شده نمی‌خواند: {pkg_rel}")
        out.append(pkg)
    return out


def reconstruct_base(dest: Path) -> None:
    """پایهٔ واقعی ۱٫۶۲٫۹۴ تولید: v1.62.88 + هر سه بستهٔ میانی."""
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
    # دروازهٔ بازسازی: ۱٫۶۲٫۹۴ شاخهٔ RC را وارد نمی‌کند؛ وجود request-center در پایه باید خطا بدهد.
    if (dest / "modules" / "request-center").exists():
        raise SystemExit("بازسازی پایه خراب شد: modules/request-center نباید در پایهٔ غذا وجود داشته باشد.")


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
        raise SystemExit("require ناموفق در پایهٔ هدف:\n  " + "\n  ".join(missing))


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


def report_worktree_vs_base(base: Path) -> None:
    """فهرست فایل‌های تفاضل‌دار بین پایهٔ بازسازی‌شده و درخت‌کاری (صرفاً برای صورت‌جلسهٔ سازنده).

    نکته: شاخهٔ کاری شامل خط RC/۱٫۶۳ هم هست؛ بنابراین تفاضل شامل فایل‌های RC است که عمداً
    داخل بسته نیستند. تضمین «وارد نشدن RC» بر عهدهٔ دو کنترل سخت‌گیرانهٔ دیگر است
    (check_no_foreign_content + check_requires_exist) و این که فقط DELTA_FROM_WORKTREE وارد بسته می‌شود.
    """
    differing = []
    for path in sorted(base.rglob("*")):
        if not path.is_file():
            continue
        rel = path.relative_to(base).as_posix()
        candidate = ROOT / rel
        if not candidate.is_file():
            differing.append(f"MISSING_IN_WORKTREE {rel}")
            continue
        if candidate.read_bytes() != path.read_bytes():
            differing.append(rel + ("  [در بسته]" if rel in DELTA_FROM_WORKTREE else "  [خارج از بسته]"))
    print("تفاضل پایه/درخت (نماینده؛ فایل‌های RC خارج از بسته‌اند):")
    for line in differing:
        print("   ", line)


def validate_archive(path: Path, expected_manifest: dict) -> None:
    import zipfile
    with zipfile.ZipFile(path, "r") as z:
        bad = z.testzip()
        if bad is not None:
            raise SystemExit(f"CRC خراب در ZIP: {bad}")
        names = z.infolist() and [i.filename for i in z.infolist()]
        if len(names) != len(set(n.casefold() for n in names)):
            raise SystemExit("ZIP مسیر تکراری یا تکرار بدون حساسیت به حروف دارد.")
        if any(not safe_archive_path(n) for n in names):
            raise SystemExit("ZIP مسیر مطلق یا traversal دارد.")
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


def build() -> tuple[Path, Path]:
    with tempfile.TemporaryDirectory(prefix="base-1.62.94-") as tmp:
        base = Path(tmp)
        reconstruct_base(base)

        files: dict[str, bytes] = {}
        for relative in DELTA_FROM_WORKTREE:
            source = ROOT / relative
            if not source.is_file():
                raise SystemExit(f"فایل اعلام‌شده موجود نیست: {relative}")
            files[relative] = source.read_bytes()
        report_worktree_vs_base(base)

        code_files = {k: v for k, v in files.items() if k.endswith(".php")}
        check_requires_exist(base, code_files)
        check_no_foreign_content(files)

        report = ROOT / "downloads" / REPORT_NAME
        if not report.is_file():
            raise SystemExit(f"گزارش تغییرات موجود نیست: {report}")
        files[REPORT_NAME] = report.read_bytes()
        files["VERSION"] = f"{RELEASE_VERSION}\n".encode("utf-8")

        manifest = {
            "name": "food-auto-receipts-endofday-cddvd-exit-sheet-required",
            "version": RELEASE_VERSION,
            "date": RELEASE_DATE,
            "title": "ثبت خودکار delivered در گزارش تحویل با گذر پایان‌روز ≥۲۳ (شام + ناهار تعطیل) + حذف رفرش گزارش‌ها + تأییدیهٔ ثبت در تعطیل + «شماره برگه خروج» الزامی در CD/DVD (۱.۶۲.۹۴ ← ۱.۶۲.۹۵)",
            "requires_version": REQUIRES_VERSION,
            "migrations": list(MIGRATIONS),
            "files": sorted(files),
            "sha256": {name: sha256(files[name]) for name in sorted(files)},
            "release_status": "INTERIM_UPDATE_TEST_ON_STAGING_FIRST",
            "description": (
                "Interim 1.62.x update, rebuilt on the real production base (v1.62.88 + interim 1.62.90/1.62.93/1.62.94, "
                "byte-identical to the published intermediate packages): "
                "(1) Worker strict window — zero activity outside the work window, with the only exception of a once-per-day "
                "end-of-day pass at >= 23:00 Tehran time (real traffic first, then automatic receipts for today and yesterday, "
                "backoff 10 minutes, idempotent via source_key + GET_LOCK); "
                "(2) automatic receipts for staff of self-service-closed meals (dinner; lunch on Thu/Fri/official holidays) are now "
                "recorded with delivery_status='delivered' and shown in the printed/delivered report (and its Excel/print exports) "
                "with the 'auto print' badge; guest rows are printed without a delivered mark; migration 037 back-fills legacy "
                "auto_printed rows (auto_guest untouched); normal-day lunch is NEVER auto-recorded; "
                "(3) the 30-second automatic report refresh intervals in both food panels were removed (manual 'show report' only); "
                "live/dashboard refreshes intentionally kept; "
                "(4) ordering on a holiday now asks for explicit confirmation (order-on-holiday is allowed by owner decision); "
                "(5) CD/DVD: the 'exit sheet number' field is required for registering an OUT transfer — client-side focus+message "
                "without page reload or data loss, plus server-side validation. "
                "Migration 037 is additive and idempotent. Contains NO Request Center content and NO RC-line files. "
                "Install on staging first; production with owner sign-off; restart the food Worker after install."
            ),
        }

        out_dir = ROOT / "downloads"
        out_dir.mkdir(parents=True, exist_ok=True)
        package_path = out_dir / PACKAGE_NAME
        temporary = package_path.with_suffix(package_path.suffix + ".tmp")

        import zipfile

        def entry(name: str) -> zipfile.ZipInfo:
            info = zipfile.ZipInfo(name, date_time=ZIP_DATE)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            return info

        try:
            with zipfile.ZipFile(temporary, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as z:
                z.writestr(entry("manifest.json"), (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8"))
                for name in sorted(files):
                    z.writestr(entry("files/" + name), files[name])
                for name in MIGRATIONS:
                    z.writestr(entry("migrations/" + name), (ROOT / "migrations" / name).read_bytes())
            validate_archive(temporary, manifest)
            temporary.replace(package_path)
            checksum = sha256(package_path.read_bytes())
            sidecar = out_dir / (PACKAGE_NAME + ".sha256")
            sidecar_tmp = sidecar.with_suffix(sidecar.suffix + ".tmp")
            sidecar_tmp.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
            sidecar_tmp.replace(sidecar)
            if sidecar.read_text(encoding="ascii").split()[0] != sha256(package_path.read_bytes()):
                raise SystemExit("SHA-256 فایل جانبی با ZIP نهایی تطبیق ندارد.")
            print(f"OK {PACKAGE_NAME}")
            print(f"Files: {len(files)} | migrations: {len(MIGRATIONS)} | bytes: {package_path.stat().st_size}")
            print(f"SHA-256: {checksum}")
            return package_path, sidecar
        finally:
            if temporary.exists():
                temporary.unlink()


if __name__ == "__main__":
    build()
