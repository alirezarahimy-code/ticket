import json
from pathlib import Path
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-dashboard-r40-layout-fix-20261025.zip"
RELEASE = "2026-10-25-release-161-10-r40-food-dashboard-grid-cascade-fix"


def test_r40_dashboard_grids_are_isolated_from_legacy_host_grid_rule():
    css_path = ROOT / "food-ticket-web/assets/food-capitalio-dashboard-r40.css"
    index_path = ROOT / "food-ticket-web/index.html"

    css = css_path.read_text(encoding="utf-8")
    index = index_path.read_text(encoding="utf-8")
    assert ".food-capitalio-dashboard-widget .grid" in css
    for declaration in (
        "display: revert-layer",
        "grid-template-columns: revert-layer",
        "gap: revert-layer",
        "margin: revert-layer",
    ):
        assert declaration in css
    assert "food-capitalio-dashboard-r40.css?v=1.61.10-r40" in index

    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        names = set(archive.namelist())
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["release"] == "R40"
        assert manifest["migrations"] == []
        packaged_css = archive.read(
            "files/food-ticket-web/assets/food-capitalio-dashboard-r40.css"
        ).decode("utf-8")
        packaged_index = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        assert "grid-template-columns: revert-layer" in packaged_css
        assert "food-capitalio-dashboard-r40.css?v=1.61.10-r40" in packaged_index
        assert f"files/updates/{RELEASE}/CHANGE-REPORT-FA.md" in names
