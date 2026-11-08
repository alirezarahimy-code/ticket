#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Release archive integrity and the documented upgrade route from installed 1.62.77."""

from __future__ import annotations

import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / "downloads"

UPGRADE_CHAIN = (
    ("ticket-update-1.62.77-to-1.62.82-audit-visibility-20261105.zip", "1.62.82", "1.62.77"),
    ("ticket-update-1.62.82-to-1.62.88-merged-20261105.zip", "1.62.88", "1.62.82"),
    ("ticket-update-1.62.88-to-1.62.89-logging-escalation-20261107.zip", "1.62.89", "1.62.88"),
    ("ticket-update-1.62.88-to-1.62.90-food-fixes-20261107.zip", "1.62.90", "1.62.89"),
)


def version_tuple(value: str) -> tuple[int, ...]:
    parts = re.findall(r"\d+", value or "")
    return tuple(int(part) for part in parts) or (0,)


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def read_manifest(archive_path: Path) -> tuple[dict, set[str]]:
    with zipfile.ZipFile(archive_path) as archive:
        manifest = json.loads(archive.read("manifest.json"))
        return manifest, set(archive.namelist())


def test_every_existing_zip_sha256_sidecar_matches_its_archive() -> None:
    sidecars = sorted(DOWNLOADS.glob("*.zip.sha256"))
    assert sidecars, "هیچ فایل checksum در downloads/ پیدا نشد"

    failures: list[str] = []
    for sidecar in sidecars:
        parts = sidecar.read_text(encoding="utf-8").strip().split()
        if len(parts) != 2 or re.fullmatch(r"[0-9a-fA-F]{64}", parts[0]) is None:
            failures.append(f"{sidecar.name}: قالب باید «SHA256  filename.zip» باشد")
            continue

        expected_name = sidecar.name[:-7]  # remove only .sha256
        supplied_name = parts[1].lstrip("*").replace("\\", "/")
        if supplied_name not in (expected_name, f"downloads/{expected_name}"):
            failures.append(f"{sidecar.name}: نام فایل داخل checksum با نام sidecar تطبیق ندارد")
            continue

        archive_path = DOWNLOADS / expected_name
        if not archive_path.is_file():
            failures.append(f"{sidecar.name}: فایل ZIP متناظر وجود ندارد")
            continue

        actual = sha256_file(archive_path)
        if actual.lower() != parts[0].lower():
            failures.append(
                f"{sidecar.name}: expected={parts[0].lower()} actual={actual}"
            )

    assert not failures, "ناهم‌خوانی checksum بسته‌های انتشار:\n  - " + "\n  - ".join(failures)


def test_documented_upgrade_chain_is_compatible_with_installed_1_62_77() -> None:
    readme = (ROOT / "README.md").read_text(encoding="utf-8")
    positions: list[int] = []

    for filename, target_version, maximum_source_version in UPGRADE_CHAIN:
        archive_path = DOWNLOADS / filename
        assert archive_path.is_file(), f"بستهٔ مسیر ارتقا وجود ندارد: {filename}"
        assert archive_path.with_name(filename + ".sha256").is_file(), (
            f"فایل checksum بسته وجود ندارد: {filename}.sha256"
        )

        manifest, members = read_manifest(archive_path)
        assert str(manifest.get("version", "")) == target_version, (
            f"{filename}: manifest.version باید {target_version} باشد"
        )

        required = str(manifest.get("requires_version", "")).strip()
        assert required, f"{filename}: requires_version در manifest خالی است"
        assert version_tuple(required) <= version_tuple(maximum_source_version), (
            f"{filename}: پیش‌نیاز {required} از نسخهٔ مبدأ مجاز "
            f"{maximum_source_version} بالاتر است"
        )

        assert "VERSION" in manifest.get("files", []), (
            f"{filename}: VERSION در فهرست فایل‌های manifest نیست"
        )
        assert "files/VERSION" in members, f"{filename}: files/VERSION در ZIP نیست"

        with zipfile.ZipFile(archive_path) as archive:
            inside_version = archive.read("files/VERSION").decode("utf-8", "replace").strip()
            hashes = manifest.get("sha256", {})
            if isinstance(hashes, dict) and "VERSION" in hashes:
                actual_version_hash = hashlib.sha256(archive.read("files/VERSION")).hexdigest()
                assert actual_version_hash == str(hashes["VERSION"]).lower(), (
                    f"{filename}: SHA-256 فایل VERSION با manifest برابر نیست"
                )

        assert inside_version == target_version, (
            f"{filename}: VERSION داخل ZIP برابر {inside_version} است، نه {target_version}"
        )

        migrations = manifest.get("migrations", [])
        assert isinstance(migrations, list), f"{filename}: migrations باید آرایه باشد"
        for migration in migrations:
            member = migration if str(migration).startswith("migrations/") else f"migrations/{migration}"
            assert member in members, f"{filename}: مهاجرت اعلام‌شده در ZIP نیست: {member}"

        if target_version == "1.62.89":
            assert any("034-logging-escalation.sql" in str(item) for item in migrations), (
                "بستهٔ 1.62.89 باید migration 034 را اجرا کند"
            )

        marker = f"downloads/{filename}"
        position = readme.find(marker)
        assert position >= 0, f"README مسیر نصب بسته را معرفی نکرده: {filename}"
        positions.append(position)

    assert positions == sorted(positions), (
        "ترتیب بسته‌ها در README با زنجیرهٔ نصب 1.62.77 تا 1.62.90 هم‌خوان نیست"
    )
