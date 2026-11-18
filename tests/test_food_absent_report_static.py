"""Static contract wrapper for the standalone food absence report tests."""
from pathlib import Path
import subprocess

ROOT = Path(__file__).resolve().parents[1]


def test_standalone_absent_report_panel_and_chatbot_contract():
    result = subprocess.run(
        ["node", str(ROOT / "tests/js/food-absent-report.test.js")],
        cwd=ROOT,
        capture_output=True,
        text=True,
        timeout=60,
    )
    assert result.returncode == 0, result.stdout + result.stderr
