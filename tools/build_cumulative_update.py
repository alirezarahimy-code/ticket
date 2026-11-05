#!/usr/bin/env python3
"""Build a cumulative update ZIP from the last shipped package plus audited branch overlays."""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE_PACKAGE = ROOT / "downloads" / "ticket-update-1.62.77-to-1.62.82-audit-visibility-20261105.zip"
RELEASE_VERSION = "1.62.83"
REQUIRES_VERSION = "1.62.77"
PACKAGE_NAME = "ticket-update-1.62.77-to-1.62.83-hardening-20261105.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.83.md"

OVERLAY_MIGRATIONS = (
    "migrations/032-schema-updates-baseline.sql",
)

OVERLAY_FILES = (
    "VERSION",
    "README.md",
    "system-update.php",
    "backup.php",
    "actions/access-group-actions.php",
    "actions/account-actions.php",
    "actions/asset-actions.php",
    "actions/backup-actions.php",
    "actions/food-brand-actions.php",
    "actions/org-actions.php",
    "actions/role-actions.php",
    "actions/service-actions.php",
    "actions/settings-actions.php",
    "actions/ticket-actions.php",
    "actions/user-structure-actions.php",
    "cd-dvd.php",
    "cron/domain_scan_worker.php",
    "cron/food_ticket_worker.php",
    "food-ticket.php",
    "food-order.php",
    "food-reconciliation.php",
    "food-ticket-templates.php",
    "governance.php",
    "install-empty-db.sql",
    "lib/logging-audit.php",
    "local-accounts.php",
    "migrations/026-logging-legacy-result-unknown.sql",
    "schema.sql",
    "traffic-control.php",
    "traffic-import.php",
    "tools/set_logging_acl.ps1",
    "tools/verify_logging_acl.ps1",
    "tools/verify_logging_web_protection.ps1",
)

FORBIDDEN_ROOTS = {"storage", "uploads", ".git", "node_modules", "vendor", "config.php", ".env"}


def safe_relative_path(value: str) -> bool:
    if not value or "\\" in value or "//" in value or value.startswith("/") or re.match(r"^[A-Za-z]:", value):
        return False
    parts = value.split("/")
    if any(part in ("", ".", "..") for part in parts):
        return False
    for part in parts:
        if re.search(r'[<>:"|?*\x00-\x1f]', part) or part.endswith((".", " ")):
            return False
        if re.match(r"^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\..*)?$", part, re.I):
            return False
    return parts[0].lower() not in FORBIDDEN_ROOTS


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def load_base_package() -> tuple[dict, dict[str, bytes], dict[str, bytes]]:
    if not BASE_PACKAGE.is_file():
        raise SystemExit(f"Base update package not found: {BASE_PACKAGE}")
    with zipfile.ZipFile(BASE_PACKAGE) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        if manifest.get("version") != "1.62.82":
            raise SystemExit(f"Unexpected base manifest version: {manifest.get('version')!r}")
        if str(manifest.get("requires_version")) != REQUIRES_VERSION:
            raise SystemExit(f"Unexpected base requires_version: {manifest.get('requires_version')!r}")
        files: dict[str, bytes] = {}
        migrations: dict[str, bytes] = {}
        for info in archive.infolist():
            if info.is_dir():
                continue
            name = info.filename
            mode = (info.external_attr >> 16) & 0o170000
            if mode == 0o120000:
                raise SystemExit(f"Symlink found in base package: {name}")
            if name == "manifest.json":
                continue
            if name.startswith("files/"):
                relative = name[6:]
                if not safe_relative_path(relative):
                    raise SystemExit(f"Unsafe base package file path: {name}")
                files[relative] = archive.read(name)
            elif name.startswith("migrations/"):
                relative = name[len("migrations/"):]
                if not re.fullmatch(r"\d{3}-[a-z0-9-]+\.sql", relative):
                    raise SystemExit(f"Unsafe base migration path: {name}")
                migrations[relative] = archive.read(name)
            else:
                raise SystemExit(f"Unexpected root entry in base update package: {name}")
    if not migrations:
        raise SystemExit("Base package has no migrations; refusing to build a cumulative package.")
    expected_files = set(manifest.get("files", []))
    if "files" in manifest and expected_files != set(files):
        raise SystemExit("Base package files inventory does not match its manifest.")
    expected_migrations = set(manifest.get("migrations", []))
    if expected_migrations != set(migrations):
        raise SystemExit("Base package migration inventory does not match its manifest.")
    for relative, expected in (manifest.get("sha256") or {}).items():
        if relative not in files or sha256(files[relative]) != expected:
            raise SystemExit(f"Base package SHA-256 mismatch: {relative}")
    return manifest, files, migrations


def render_download_index(package_name: str, checksum: str) -> str:
    return f"""<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>بستهٔ به‌روزرسانی ۱.۶۲.۸۳ — سامانهٔ درخواست پشتیبانی</title>
<style>
:root{{color-scheme:light;--ink:#132b3a;--muted:#5c7180;--line:#d9e4e8;--teal:#087f78;--amber:#d69623}}
*{{box-sizing:border-box}}body{{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(145deg,#e7f4f2,#f4f7fa 60%,#e8eff4);color:var(--ink);font:16px/1.9 Tahoma,"Segoe UI",sans-serif}}
main{{width:min(100%,900px);padding:32px;background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 18px 50px #173d4a18}}
.eyebrow{{color:var(--teal);font-size:13px;font-weight:700}}h1{{margin:4px 0 8px;font-size:26px}}h2{{margin:0 0 4px;font-size:19px}}p{{margin:8px 0 16px;color:var(--muted)}}
.package{{margin:20px 0;padding:18px;border:1px solid var(--line);border-radius:14px;background:#fbfdfe}}
.actions{{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0}}.button{{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:9px 18px;border-radius:10px;text-decoration:none;font-weight:700}}
.primary{{background:var(--teal);color:#fff}}.secondary{{border:1px solid var(--line);color:var(--ink);background:#fff}}
.warning{{margin-top:16px;padding:13px 15px;border-right:4px solid var(--amber);border-radius:8px;background:#fff8e8;color:#684b16;font-size:14px}}
.filename{{direction:ltr;text-align:left;overflow-wrap:anywhere;font:13px/1.7 monospace}}@media(max-width:520px){{main{{padding:22px}}h1{{font-size:22px}}.actions a{{width:100%}}}}
</style></head>
<body><main>
<span class="eyebrow">صفحهٔ دانلود — بستهٔ تجمیعی</span>
<h1>نسخهٔ ۱.۶۲.۸۳</h1>
<p>بستهٔ اصلاحات امنیتی نصب‌کننده، Logging، حسابرسی رویدادها، پشتیبان‌گیری و بازیابی، و ابزارهای کنترل ACL ویندوز.</p>
<section class="package">
<h2>به‌روزرسانی نصب موجود — ۱.۶۲.۸۳</h2>
<p>بستهٔ تجمیعی از پایهٔ <b>۱.۶۲.۷۷</b>؛ شامل تغییرات ۱.۶۲.۷۸ تا ۱.۶۲.۸۳ و مهاجرت ۰۳۱. فقط همین ZIP را در صفحهٔ «به‌روزرسانی و پشتیبان‌گیری» بارگذاری کنید.</p>
<div class="actions">
<a class="button primary" href="{package_name}" download>دانلود بستهٔ به‌روزرسانی ۱.۶۲.۸۳ (ZIP)</a>
<a class="button secondary" href="{package_name}.sha256" download>SHA-256 بسته</a>
<a class="button secondary" href="{REPORT_NAME}">گزارش تغییرات و تست‌ها</a>
</div>
<p class="filename">{package_name}<br>SHA-256: {checksum}</p>
</section>
<div class="warning"><b>پیش‌نیاز:</b> این بسته برای نصب‌های نسخهٔ ۱.۶۲.۷۷ و بالاتر است. اگر نصب شما ۱.۶۲.۷۵ یا ۱.۶۲.۷۶ است، ابتدا بستهٔ ۱.۶۲.۷۷ را نصب کنید. نسخه‌های قدیمی‌تر باید زنجیرهٔ ارتقا را به‌ترتیب طی کنند.</div>
<div class="warning"><b>پیش از نصب:</b> از دیتابیس، فایل <code>config.php</code> و پوشهٔ <code>storage/</code> نسخهٔ پشتیبان مستقل بگیرید و بسته را ابتدا روی Staging بیازمایید. پوشهٔ <code>storage/</code> در به‌روزرسانی جایگزین نمی‌شود.</div>
<div class="warning"><b>پس از نصب:</b> مقدار <code>VERSION</code> را بررسی کنید؛ سپس ACL پوشهٔ <code>storage/logs</code> و حفاظت وب را با ابزارهای داخل <code>tools/</code> ارزیابی کنید. تست‌های Windows و MariaDB در CI جایگزین آزمون روی Staging واقعی نیستند.</div>
</main></body></html>
"""


def build(output_dir: Path) -> tuple[Path, Path]:
    current_version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    if current_version != RELEASE_VERSION:
        raise SystemExit(f"VERSION must be {RELEASE_VERSION}, found {current_version!r}")
    report_path = ROOT / "downloads" / REPORT_NAME
    if not report_path.is_file():
        raise SystemExit(f"Release report not found: {report_path}")

    manifest, files, migrations = load_base_package()
    for migration_path in OVERLAY_MIGRATIONS:
        migration_file = ROOT / migration_path
        migration_name = migration_file.name
        if not re.fullmatch(r"\d{3}-[a-z0-9-]+\.sql", migration_name):
            raise SystemExit(f"Unsafe release migration name: {migration_name}")
        migration_data = migration_file.read_bytes()
        migrations[migration_name] = migration_data
        files["migrations/" + migration_name] = migration_data

    for relative in OVERLAY_FILES:
        source = ROOT / relative
        if not source.is_file():
            raise SystemExit(f"Required overlay file missing: {relative}")
        if not safe_relative_path(relative):
            raise SystemExit(f"Unsafe overlay path: {relative}")
        files[relative] = source.read_bytes()
    files[REPORT_NAME] = report_path.read_bytes()

    if files.get("VERSION", b"").decode("utf-8", "replace").strip() != RELEASE_VERSION:
        raise SystemExit("files/VERSION does not match release version.")
    if len(files) > 5000 or any(len(data) > 128 * 1024 * 1024 for data in files.values()):
        raise SystemExit("Release package exceeds file count or per-file size limits.")
    if sum(map(len, files.values())) > 512 * 1024 * 1024:
        raise SystemExit("Release package exceeds total uncompressed size limit.")

    manifest["version"] = RELEASE_VERSION
    manifest["requires_version"] = REQUIRES_VERSION
    manifest["files"] = sorted(files)
    manifest["sha256"] = {name: sha256(files[name]) for name in sorted(files)}
    manifest["migrations"] = sorted(migrations)
    manifest["description"] = "Cumulative hardening update from 1.62.77 through 1.62.83"

    output_dir.mkdir(parents=True, exist_ok=True)
    package_path = output_dir / PACKAGE_NAME
    manifest_bytes = (json.dumps(manifest, ensure_ascii=False, indent=2) + "\n").encode("utf-8")
    with zipfile.ZipFile(package_path, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        archive.writestr("manifest.json", manifest_bytes)
        for name in sorted(migrations):
            archive.writestr("migrations/" + name, migrations[name])
        for name in sorted(files):
            archive.writestr("files/" + name, files[name])

    with zipfile.ZipFile(package_path) as archive:
        names = [info.filename for info in archive.infolist()]
        if len(names) != len(set(name.casefold() for name in names)):
            raise SystemExit("Generated ZIP contains duplicate case-insensitive paths.")
        actual_files = {name[6:] for name in names if name.startswith("files/")}
        if actual_files != set(manifest["files"]):
            raise SystemExit("Generated ZIP file inventory does not match manifest.")
        loaded = json.loads(archive.read("manifest.json"))
        if loaded["version"] != RELEASE_VERSION or loaded["requires_version"] != REQUIRES_VERSION:
            raise SystemExit("Generated manifest version contract is invalid.")
        for relative, expected in loaded["sha256"].items():
            if sha256(archive.read("files/" + relative)) != expected:
                raise SystemExit(f"SHA-256 mismatch after packaging: {relative}")
        migration_names = {name[len("migrations/"):] for name in names if name.startswith("migrations/")}
        if migration_names != set(loaded["migrations"]):
            raise SystemExit("Generated migration inventory does not match manifest.")

    checksum = sha256(package_path.read_bytes())
    sidecar = output_dir / (PACKAGE_NAME + ".sha256")
    sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
    index = output_dir / "index.html"
    index.write_text(render_download_index(PACKAGE_NAME, checksum), encoding="utf-8")
    return package_path, sidecar


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output-dir", type=Path, default=ROOT / "downloads")
    args = parser.parse_args()
    package, sidecar = build(args.output_dir)
    print(f"PACKAGE={package}")
    print(f"SHA256={sidecar.read_text(encoding='ascii').split()[0]}")
    print(f"SIDECAR={sidecar}")


if __name__ == "__main__":
    main()
