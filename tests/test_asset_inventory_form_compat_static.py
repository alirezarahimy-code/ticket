from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
SOURCE = (ROOT / "asset-inventory-page.php").read_text(encoding="utf-8")
INDEX = (ROOT / "index.php").read_text(encoding="utf-8")
NAME = "ticket-update-1.62.26-asset-profile-form-fix-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_renderer_normalizes_legacy_and_current_argument_orders():
    start = SOURCE.index("function inventory_render_assets_content")
    end = SOURCE.index("\n    echo '<section class=\"page-heading inventory-page-heading\"", start)
    signature = SOURCE[start:end]
    assert "bool|int $selectOrOnline" in signature
    assert "?int $legacyCollected = null" in signature
    assert "if ($legacyCollected !== null)" in signature
    assert "$canConfigureNetworkScan = $networkOrConfigure" in signature
    assert "$networkScanEnabled = $selectOrNetwork" in signature
    assert "$canSelectAssets = (bool) $selectOrOnline" in signature
    assert "$onlineCount = $onlineOrCollected" in signature
    assert "$collectedCount = $legacyCollected" in signature


def test_current_index_call_uses_the_11_argument_layout():
    calls = [line.strip() for line in INDEX.splitlines() if "inventory_render_assets_content(" in line]
    assert len(calls) == 1
    call = calls[0]
    assert "$canExtractLocalServer" not in call
    assert "$canConfigureNetworkScan" in call and "$collectedCount" in call


def test_16224_archive_has_the_legacy_argument_that_caused_the_type_error():
    legacy_package = ROOT / "downloads" / "ticket-update-1.62.24-cumulative-20261027.zip"
    with ZipFile(legacy_package) as archive:
        old_index = archive.read("files/index.php").decode("utf-8")
    call = next(line for line in old_index.splitlines() if "inventory_render_assets_content($assetRows" in line)
    assert "$canExtractLocalServer" in call
    assert "?int $legacyCollected = null" in SOURCE


def test_form_hotfix_is_a_valid_incremental_upgrade_from_16225():

    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.26"
        assert manifest["requires_version"] == "1.62.25"
        assert manifest["migrations"] == []
        assert set(manifest["files"]) == {"VERSION", "asset-inventory-page.php"}
        assert archive.read("files/VERSION").decode().strip() == "1.62.26"
        included = archive.read("files/asset-inventory-page.php").decode("utf-8")
        assert "?int $legacyCollected = null" in included
        assert "if ($legacyCollected !== null)" in included
        assert all(name in names for name in ["files/VERSION", "files/asset-inventory-page.php"])

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar
    page = (ROOT / "downloads" / "index.html").read_text(encoding="utf-8")
    current = "ticket-update-1.62.36-manual-ticket-record-reconciliation-20261027.zip"
    assert current in page
    assert f"{current}.sha256" in page
