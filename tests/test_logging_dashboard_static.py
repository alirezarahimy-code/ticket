from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
page = (ROOT / "logging-dashboard.php").read_text(encoding="utf-8")
index = (ROOT / "index.php").read_text(encoding="utf-8")
css = (ROOT / "assets/logging-dashboard.css").read_text(encoding="utf-8")
js = (ROOT / "assets/logging-dashboard.js").read_text(encoding="utf-8")


def test_dashboard_is_permission_gated_and_routed():
    assert "user_can($user, 'system.health')" in page
    assert "security_log_access_denied" in page
    assert "if ($page === 'logging-dashboard')" in index
    assert "index.php?page=logging-dashboard" in index


def test_dashboard_uses_real_logging_sources_and_local_runtime():
    for token in ["FROM system_logs", "FROM log_issues", "logging_spool_stats()", "logging-worker-status.json"]:
        assert token in page
    assert "https://" not in page and "cdn." not in page


def test_dashboard_handles_unknown_database_state_and_visualizations():
    assert "'database' => 'unknown'" in page
    assert "صفر تلقی نمی‌شوند" in page
    assert "logging_dashboard_svg_trend" in page
    assert "ld-trend-svg" in page
    assert "@media(max-width:720px)" in css


def test_dashboard_auto_refresh_is_bounded_and_visibility_aware():
    assert "60000" in js
    assert "document.visibilityState" in js
