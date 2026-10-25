import json
from pathlib import Path
from zipfile import ZipFile


ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-dashboard-capitalio-r34-20261025.zip"
RELEASE_PATH = "2026-10-25-release-161-10-r34-food-capitalio-dashboard"


def test_r34_food_dashboard_is_integrated_and_uses_food_apis():
    assert PACKAGE.is_file(), f"R34 package is missing: {PACKAGE}"
    with ZipFile(PACKAGE) as archive:
        names = set(archive.namelist())
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.61.10"
        assert manifest["migrations"] == []

        page = archive.read("files/food-ticket-web/index.html").decode("utf-8")
        assert "<iframe" not in page.lower()
        assert "food-ticket-web/capitalio-dashboard.html" not in page
        assert "PANEL_VERSION='1.37.19'" in page
        assert "/api/monitoring?from=" in page
        assert "/api/orders?date=" in page
        assert "#008A45" in page
        assert "#D7263D" in page
        assert "#1D4ED8" in page
        assert "#F08A00" in page
        assert "food-dash-grad-orders" in page
        assert "dashboardSparklineSvg" in page
        assert "food-dash-point-hit" in page
        assert "showFoodDashboardTooltip" in page
        assert "food-ticket-dashboard-theme" in page

        css = archive.read("files/food-ticket-web/assets/food-capitalio-dashboard.css").decode("utf-8")
        assert "food-theme-circle-reveal" in css
        assert "food-dash-stat:hover" in css
        assert "food-dash-tooltip" in css
        assert "food-dash-enter" in css
        assert "2s ease-in-out" in css

        php = archive.read("files/food-ticket.php").decode("utf-8")
        assert "FOOD_TICKET_PANEL_VERSION = '1.37.19'" in php

        release_manifest = f"files/updates/{RELEASE_PATH}/manifest.json"
        release_report = f"files/updates/{RELEASE_PATH}/CHANGE-REPORT-FA.md"
        assert release_manifest in names
        assert release_report in names
        expected = {
            "manifest.json",
            "files/food-ticket.php",
            "files/food-ticket-web/index.html",
            "files/food-ticket-web/assets/food-capitalio-dashboard.css",
            release_manifest,
            release_report,
        }
        assert names == expected, f"unexpected files in food-only update: {names - expected}"