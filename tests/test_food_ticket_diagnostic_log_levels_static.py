from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NETPRINT = (ROOT / "food-ticket-netprint.php").read_text(encoding="utf-8")
TEMPLATES = (ROOT / "food-ticket-templates.php").read_text(encoding="utf-8")
NAME = "ticket-update-1.62.27-food-log-levels-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_successful_print_diagnostics_are_debug_not_errors():
    trim_start = NETPRINT.index("function food_ticket_np_trim_edges")
    trim_end = NETPRINT.index("function food_ticket_np_trim_top", trim_start)
    trim_log = NETPRINT[trim_start:trim_end]
    assert "system_log('debug', 'food_print'" in trim_log
    assert "app_error_log" not in trim_log

    align_start = NETPRINT.index("if (!empty($cfg['log_align']))")
    align_end = NETPRINT.index("return $out;", align_start)
    align_log = NETPRINT[align_start:align_end]
    assert "system_log('debug', 'food_print_align'" in align_log
    assert "app_error_log" not in align_log


def test_template_adjustment_is_debug_and_render_constraints_are_warning():
    assert "system_log('debug', 'food_template', sprintf('[food-template] top gap trimmed" in TEMPLATES
    assert "system_log('debug', 'food_template', sprintf('[food-template] bottom fit" in TEMPLATES
    assert "system_log('warning', 'food_template', '[food-template] ' . $w)" in TEMPLATES
    assert "system_log('warning', 'food_template', sprintf(" in TEMPLATES
    assert "app_error_log('[food-template] top gap trimmed" not in TEMPLATES
    assert "app_error_log(sprintf('[food-template] bottom fit" not in TEMPLATES


def test_combined_hotfix_includes_inventory_compatibility_and_print_log_fixes():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.27"
        assert manifest["requires_version"] == "1.62.25"
        assert manifest["migrations"] == []
        expected = {"VERSION", "asset-inventory-page.php", "food-ticket-netprint.php", "food-ticket-templates.php"}
        assert set(manifest["files"]) == expected
        assert {name[6:] for name in names if name.startswith("files/")} == expected
        assert archive.read("files/VERSION").decode().strip() == "1.62.27"
        assert "?int $legacyCollected = null" in archive.read("files/asset-inventory-page.php").decode()
        assert "system_log('debug', 'food_print'" in archive.read("files/food-ticket-netprint.php").decode()
        assert "system_log('debug', 'food_print_align'" in archive.read("files/food-ticket-netprint.php").decode()
        assert "system_log('debug', 'food_template'" in archive.read("files/food-ticket-templates.php").decode()
        for name in names:
            assert name == "manifest.json" or name.startswith("files/")
            assert ".." not in name and not name.startswith("/")

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar
