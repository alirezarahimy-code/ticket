#!/usr/bin/env python3
"""Safely refresh the embedded 1.62.90 release report and its ZIP checksum."""

from __future__ import annotations

import hashlib
import json
import os
import tempfile
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DOWNLOADS = ROOT / "downloads"
PACKAGE_NAME = "ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip"
REPORT_NAME = "CHANGE-REPORT-FA-1.62.90.md"
TARGET_VERSION = "1.62.90"
REQUIRES_VERSION = "1.62.88"


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def main() -> None:
    package = DOWNLOADS / PACKAGE_NAME
    report = DOWNLOADS / REPORT_NAME
    sidecar = DOWNLOADS / (PACKAGE_NAME + ".sha256")
    embedded_report = "files/" + REPORT_NAME

    if not package.is_file() or not report.is_file():
        raise SystemExit("بسته یا گزارش انتشار 1.62.90 پیدا نشد.")

    report_bytes = report.read_bytes()
    with zipfile.ZipFile(package, "r") as source:
        if source.testzip() is not None:
            raise SystemExit("ZIP فعلی دارای CRC نامعتبر است.")
        infos = source.infolist()
        names = [info.filename for info in infos]
        if len(names) != len({name.casefold() for name in names}):
            raise SystemExit("ZIP فعلی مسیر تکراری case-insensitive دارد.")
        if "manifest.json" not in names or embedded_report not in names:
            raise SystemExit("manifest یا گزارش تعبیه‌شده در ZIP موجود نیست.")

        manifest = json.loads(source.read("manifest.json"))
        if str(manifest.get("version")) != TARGET_VERSION:
            raise SystemExit("نسخهٔ manifest با 1.62.90 برابر نیست.")
        if str(manifest.get("requires_version")) != REQUIRES_VERSION:
            raise SystemExit("requires_version بستهٔ 1.62.90 غیرمنتظره است.")
        if REPORT_NAME not in manifest.get("files", []):
            raise SystemExit("گزارش در inventory فایل‌های manifest ثبت نشده است.")

        payloads = {
            info.filename: source.read(info.filename)
            for info in infos
            if not info.is_dir() and info.filename != "manifest.json"
        }
        old_report = payloads[embedded_report]
        if old_report == report_bytes:
            # Still verify the existing package and sidecar before exiting.
            for relative, expected in (manifest.get("sha256") or {}).items():
                entry = "files/" + relative
                if entry not in payloads or sha256(payloads[entry]) != str(expected).lower():
                    raise SystemExit(f"SHA-256 فعلی نامعتبر است: {relative}")
            current_hash = sha256(package.read_bytes())
            if sidecar.is_file():
                fields = sidecar.read_text(encoding="ascii").strip().split()
                if len(fields) != 2 or fields[0].lower() != current_hash:
                    raise SystemExit("sidecar فعلی با ZIP تطبیق ندارد.")
            print("PACKAGE_REBUILT=0")
            print(f"SHA256={current_hash}")
            return

        # Refuse to mask any pre-existing payload corruption.
        for relative, expected in (manifest.get("sha256") or {}).items():
            if relative == REPORT_NAME:
                continue
            entry = "files/" + relative
            if entry not in payloads or sha256(payloads[entry]) != str(expected).lower():
                raise SystemExit(f"فایل غیرمرتبط با گزارش، hash نامعتبر دارد: {relative}")

        payloads[embedded_report] = report_bytes
        manifest.setdefault("sha256", {})[REPORT_NAME] = sha256(report_bytes)
        manifest_bytes = (
            json.dumps(manifest, ensure_ascii=False, indent=2) + "\n"
        ).encode("utf-8")

        fd, temp_name = tempfile.mkstemp(prefix=PACKAGE_NAME + ".", suffix=".tmp", dir=DOWNLOADS)
        os.close(fd)
        temp_path = Path(temp_name)
        try:
            with zipfile.ZipFile(temp_path, "w") as target:
                for info in infos:
                    if info.is_dir():
                        target.writestr(info, b"")
                    elif info.filename == "manifest.json":
                        target.writestr(info, manifest_bytes)
                    else:
                        target.writestr(info, payloads[info.filename])

            with zipfile.ZipFile(temp_path, "r") as rebuilt:
                if rebuilt.testzip() is not None:
                    raise SystemExit("CRC بستهٔ بازسازی‌شده نامعتبر است.")
                rebuilt_names = [info.filename for info in rebuilt.infolist()]
                if len(rebuilt_names) != len({name.casefold() for name in rebuilt_names}):
                    raise SystemExit("بستهٔ بازسازی‌شده مسیر تکراری دارد.")
                checked = json.loads(rebuilt.read("manifest.json"))
                if checked.get("version") != TARGET_VERSION:
                    raise SystemExit("نسخهٔ manifest پس از بازسازی تغییر کرده است.")
                for relative, expected in checked["sha256"].items():
                    actual = sha256(rebuilt.read("files/" + relative))
                    if actual != str(expected).lower():
                        raise SystemExit(f"SHA-256 پس از بازسازی نامعتبر است: {relative}")
                if rebuilt.read(embedded_report) != report_bytes:
                    raise SystemExit("گزارش تعبیه‌شده پس از بازسازی با گزارش مخزن برابر نیست.")

            os.replace(temp_path, package)
        finally:
            if temp_path.exists():
                temp_path.unlink()

    checksum = sha256(package.read_bytes())
    sidecar.write_text(f"{checksum}  {PACKAGE_NAME}\n", encoding="ascii")
    print("PACKAGE_REBUILT=1")
    print(f"PACKAGE={package}")
    print(f"SHA256={checksum}")
    print(f"SIDECAR={sidecar}")


if __name__ == "__main__":
    main()
