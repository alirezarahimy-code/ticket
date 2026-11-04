from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[3]
PACKAGE = ROOT / "downloads/ticket-update-1.62.73-food-dashboard-cleanup-20261104-r2.zip"
with ZipFile(PACKAGE) as archive:
    PANEL = archive.read("files/food-ticket-web/index.html").decode("utf-8")
SUMMARY_START = PANEL.index("  const summaryCards=[")
SUMMARY_END = PANEL.index("\n  ];", SUMMARY_START)
SUMMARY = PANEL[SUMMARY_START:SUMMARY_END]

REMOVED_CARDS = (
    "cancelled-orders",
    "risk-score-dinner",
    "cancelled-dinner-orders",
    "auto-printed-lunch-today",
    "auto-printed-dinner-today",
    "auto-guests-lunch-today",
    "auto-guests-dinner-today",
)


def test_dashboard_summary_cleanup_preserves_report_metrics():
    for card_id in REMOVED_CARDS:
        assert card_id not in SUMMARY
    assert "id:'risk-score'" in SUMMARY
    assert "<tr><th>فیش خودکار پرسنل</th>" in PANEL
    assert "<tr><th>فیش خودکار مهمان</th>" in PANEL
    assert "filter(event=>!['auto_printed','auto_guest']" in PANEL
