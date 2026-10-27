from __future__ import annotations

import hashlib
import json
from pathlib import Path
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
NAME = "ticket-update-1.62.36-manual-ticket-record-reconciliation-20261027.zip"
PACKAGE = ROOT / "downloads" / NAME
MIGRATION = "013-manual-attendance-record-backfill.sql"


def test_16236_package_and_checksum_are_integral():
    assert PACKAGE.is_file()
    with ZipFile(PACKAGE) as z:
        names = z.namelist()
        assert len(names) == len(set(names))
        assert z.testzip() is None
        manifest = json.loads(z.read("manifest.json"))
        assert manifest["version"] == "1.62.36"
        assert manifest["requires_version"] == "1.62.35"
        assert manifest["migrations"] == [MIGRATION]
        assert set(manifest["files"]) == {n[6:] for n in names if n.startswith("files/")}
        assert z.read("files/VERSION").decode().strip() == "1.62.36"
        assert f"migrations/{MIGRATION}" in names
        for name in names:
            assert ".." not in name and not name.startswith("/")
    digest = hashlib.sha256(PACKAGE.read_bytes()).hexdigest()
    sidecar = (ROOT / "downloads" / f"{NAME}.sha256").read_text(encoding="ascii")
    assert digest in sidecar


def test_migration_backfills_delivery_and_clears_same_day_absence():
    with ZipFile(PACKAGE) as z:
        sql = z.read(f"migrations/{MIGRATION}").decode()
    assert sql.count("ADD COLUMN IF NOT EXISTS") == 3
    assert "source_uid = 'manual-attendance'" in sql
    assert "event_type = 'printed'" in sql
    assert "COALESCE(print_status, '') NOT IN ('pending', 'printing')" in sql
    assert "AND event_type = 'printed')" in sql
    assert "delivery_status = 'delivered'" in sql
    assert "delivered_at = COALESCE(delivered_at, NOW())" in sql
    assert "event.punch_date" not in sql
    assert "e.punch_date = a.absence_date" in sql
    assert "event_type IN ('printed', 'no_food')" in sql
    assert "OR (e.event_type = 'printed' AND e.print_status = 'printed')" in sql


def test_runtime_reconciliation_updates_stored_rows_when_absences_are_read():
    source = (ROOT / "food-ticket-groups.php").read_text(encoding="utf-8")
    assert "function food_ticket_manual_attendance_reconcile_range(string $fromDate, string $toDate): array" in source
    assert "function food_ticket_manual_attendance_reconcile_date(string $date): array" in source
    assert "food_ticket_manual_attendance_reconcile_date($date);" in source
    assert "DELETE a FROM food_ticket_daily_absence AS a" in source
    assert "UPDATE food_ticket_events SET" in source
    assert "delivery_status = \"delivered\"" in source
    assert '((source_uid = "manual-attendance" AND event_type = "printed")' in source

    with ZipFile(PACKAGE) as z:
        order = z.read("files/food-order.php").decode("utf-8")
    assert "food_ticket_manual_attendance_reconcile_range($from, $to)" in order
    assert "e.source_uid = 'manual-attendance' AND e.event_type = 'printed' AND e.delivery_status = 'delivered'" in order


def test_download_page_points_to_16236():
    page = (ROOT / "downloads" / "index.html").read_text(encoding="utf-8")
    assert NAME in page and f"{NAME}.sha256" in page
    assert "۱.۶۲.۳۶" in page
    assert "پایگاه‌دادهٔ شما" in page
