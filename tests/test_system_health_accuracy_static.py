from __future__ import annotations

import hashlib
import json
import re
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
HEALTH = (ROOT / "system-health.php").read_text(encoding="utf-8")
BOOTSTRAP = (ROOT / "bootstrap.php").read_text(encoding="utf-8")
NAME = "ticket-update-1.62.25-system-health-accuracy-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME


def test_event_totals_are_independent_of_the_display_limit():
    start = HEALTH.index("function system_health_groups")
    end = HEALTH.index("/** بررسی‌های خودکار سلامت", start)
    groups = HEALTH[start:end]
    assert "GROUP BY context, message, level" in groups
    assert "foreach (['error', 'warning'] as $level)" in groups
    assert "ORDER BY g.cnt DESC, g.last_at DESC" in groups
    assert "LIMIT 100" in groups

    totals_start = HEALTH.index("function system_health_event_totals")
    totals_end = HEALTH.index("/** بررسی‌های خودکار سلامت", totals_start)
    totals = HEALTH[totals_start:totals_end]
    assert "SUM(level = 'error') AS errors" in totals
    assert "SUM(level = 'warning') AS warnings" in totals
    assert "GROUP BY context, message, level" in totals
    assert "LIMIT" not in totals


def test_export_reports_full_counts_and_discloses_truncated_group_list():
    start = HEALTH.index("function system_health_export_safe_xlsx")
    end = HEALTH.index("function system_health_render", start)
    export = HEALTH[start:end]
    assert "$eventTotals = system_health_event_totals($days)" in export
    assert "$errorOccurrences = $eventTotals['errors']" in export
    assert "$warningOccurrences = $eventTotals['warnings']" in export
    assert "گروه‌های رخداد در برگهٔ بعدی" in export
    assert "گروه‌های باقی‌ماندهٔ نمایش‌داده‌نشده" in export
    assert "مرجع فنی امن" in export
    assert "system_health_event_reference($row)" in export
    assert "A1:J1" in export and "A2:J2" in export


def test_safe_reference_uses_only_class_basename_line_or_fingerprint():
    start = HEALTH.index("function system_health_event_reference")
    end = HEALTH.index("/** URL خام درخواست", start)
    reference = HEALTH[start:end]
    assert "basename" not in reference  # filename is extracted by an allow-listed regex
    assert "[A-Za-z0-9_.-]+\\.php" in reference
    assert "system_health_fingerprint" in reference
    assert "strtr" in reference
    assert "technical" not in reference and "request_uri" not in reference


def test_future_exception_logs_include_a_validated_error_class():
    start = BOOTSTRAP.index("function system_error_record")
    end = BOOTSTRAP.index("// هندلرهای سراسری", start)
    recorder = BOOTSTRAP[start:end]
    assert "string $errorType = ''" in recorder
    assert "$meta['error_type'] = $errorType" in recorder
    assert "get_class($e)" in BOOTSTRAP
    assert "E_PARSE" in BOOTSTRAP and "E_COMPILE_ERROR" in BOOTSTRAP


def test_hotfix_package_is_a_valid_upgrade_from_16224():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as archive:
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert archive.testzip() is None
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["version"] == "1.62.25"
        assert manifest["requires_version"] == "1.62.24"
        assert manifest["migrations"] == []
        assert set(manifest["files"]) == {"VERSION", "system-health.php", "lib/logging-csrf.php"}
        assert archive.read("files/VERSION").decode().strip() == "1.62.25"
        health = archive.read("files/system-health.php").decode("utf-8")
        logger = archive.read("files/lib/logging-csrf.php").decode("utf-8")
        assert "system_health_event_totals" in health
        assert "system_health_event_reference" in health
        assert "error_type" in logger and "get_class($e)" in logger
        for path in manifest["files"]:
            assert f"files/{path}" in names
        for member in names:
            assert re.match(r"^(manifest\.json|files/.+)$", member)
            assert ".." not in member and not member.startswith("/")

    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar
