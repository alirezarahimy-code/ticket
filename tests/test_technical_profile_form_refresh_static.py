from __future__ import annotations

import hashlib
import json
import re
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
PROFILE = (ROOT / "asset-profile.php").read_text(encoding="utf-8")
NAME = "ticket-update-1.62.28-technical-profile-form-refresh-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME
EXPECTED = {"VERSION", "asset-profile.php", "asset-inventory-page.php", "assets/style.css", "index.php"}


def test_current_profile_module_has_complete_form_and_additive_schema():
    section_block = PROFILE[PROFILE.index("function asset_profile_sections"):PROFILE.index("function asset_profile_all_columns")]
    field_keys = set(re.findall(r"\['key' => '([^']+)'", section_block))
    section_count = len(re.findall(r"'title' => '", section_block))
    assert len(field_keys) == 74
    assert section_count == 9
    for key in ["record_number", "plaque_monitor", "owner_full_name", "os_name", "cpu_model", "ram_total", "ip_address", "monitor_model", "antivirus", "ad_online"]:
        assert key in field_keys
    assert "function asset_profile_render_sections" in PROFILE
    assert "function asset_profile_fill_from_asset" in PROFILE
    assert "function asset_profile_collection_status_summary" in PROFILE
    assert "function asset_profile_save" in PROFILE
    assert "DROP TABLE" not in PROFILE.upper()
    assert "ALTER TABLE asset_profiles ADD COLUMN" in PROFILE
    ensure_start = PROFILE.index("function asset_profile_ensure_schema")
    ensure_end = PROFILE.index("function asset_profile_sections", ensure_start)
    ensure = PROFILE[ensure_start:ensure_end]
    assert ensure.index("$done = true;") > ensure.index("foreach ($assetAdd as $name => $definition)")
    assert "ساختار پایگاه‌دادهٔ شناسنامهٔ سیستم آماده نیست" in ensure
    assert "throw new RuntimeException(" in ensure


def test_form_hotfix_packages_the_renderer_styles_and_compatible_page():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.28"
        assert manifest["requires_version"] == "1.62.25"
        assert manifest["migrations"] == []
        assert set(manifest["files"]) == EXPECTED
        assert {name[6:] for name in names if name.startswith("files/")} == EXPECTED
        assert archive.read("files/VERSION").decode().strip() == "1.62.28"

        profile = archive.read("files/asset-profile.php").decode("utf-8")
        page = archive.read("files/index.php").decode("utf-8")
        component = archive.read("files/asset-inventory-page.php").decode("utf-8")
        css = archive.read("files/assets/style.css").decode("utf-8")
        assert "asset_profile_render_sections($profile)" in page
        assert "asset_profile_save" in profile and "save_inventory_form" in page
        assert "function asset_profile_sections" in profile
        assert "?int $legacyCollected = null" in component
        assert "assets/style.css?v=3.4.47" in page
        assert ".inventory-form" in css and ".profile-grid" in css and ".form-section-head" in css

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar
    release_note = ROOT / "updates/2026-10-27-release-162-28-technical-profile-form-refresh/README.fa.md"
    assert release_note.is_file()
