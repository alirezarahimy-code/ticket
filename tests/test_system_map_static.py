#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""آزمون ایستای نقشهٔ سامانه — تضمین میکند نقشه کامل و بهروز بماند.

بدون نیاز به PHP، MySQL، مرورگر یا شبکه.
اجرا: python3 tests/test_system_map_static.py
"""

from __future__ import annotations

import csv
import json
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MAP = ROOT / "docs" / "system-map"
DOC = ROOT / "docs" / "SYSTEM-MAP-FA.md"
AGENTS = ROOT / "AGENTS.md"
BUILDER = ROOT / "tools" / "build_system_map.py"
FINDER = ROOT / "tools" / "find-in-system.py"

EXPECTED_FILES = {
    "FILES.tsv": ["path", "category", "lines", "bytes"],
    "SYMBOLS.tsv": ["name", "kind", "file", "line", "category"],
    "ROUTES.tsv": ["kind", "name", "file", "line"],
    "TABLES.tsv": ["table", "file", "line"],
    "UPDATES.tsv": ["package", "version", "requires_version", "files", "migrations", "date"],
    "FEATURES.tsv": ["keywords", "title", "primary_files", "tables", "note"],
}


def load(name: str) -> list[dict[str, str]]:
    with (MAP / name).open(encoding="utf-8") as handle:
        return list(csv.DictReader(handle, delimiter="\t"))


def main() -> int:
    failures: list[str] = []
    checks = 0

    def check(condition: bool, message: str) -> None:
        nonlocal checks
        checks += 1
        if not condition:
            failures.append(message)

    # ۱. وجود و ساختار ایندکسها --------------------------------------------
    for name, header in EXPECTED_FILES.items():
        path = MAP / name
        check(path.is_file(), f"ایندکس وجود ندارد: docs/system-map/{name}")
        if not path.is_file():
            continue
        with path.open(encoding="utf-8") as handle:
            first = handle.readline().rstrip("\n").split("\t")
        check(first == header, f"{name}: سرستونها نادرستاند → {first}")

    for name in ("summary.json",):
        check((MAP / name).is_file(), f"فایل وجود ندارد: docs/system-map/{name}")

    # ۲. ابزارها و اسناد ----------------------------------------------------
    for path in (DOC, AGENTS, BUILDER, FINDER):
        check(path.is_file(), f"فایل وجود ندارد: {path.relative_to(ROOT)}")
    if DOC.is_file() and AGENTS.is_file():
        doc = DOC.read_text(encoding="utf-8")
        agents = AGENTS.read_text(encoding="utf-8")
        check("tools/find-in-system.py" in doc, "SYSTEM-MAP-FA.md ابزار جستجو را معرفی نکرده")
        check("docs/system-map/" in doc, "SYSTEM-MAP-FA.md ایندکسها را معرفی نکرده")
        check("قواعد ثابت" in doc, "SYSTEM-MAP-FA.md قواعد ثابت کاربر را نیاورده")
        check("SYSTEM-MAP-FA.md" in agents, "AGENTS.md به نقشهٔ سامانه ارجاع نمیدهد")

    if failures:
        print("FAIL — فایلهای پایه ناقصاند:")
        for failure in failures:
            print(f"  - {failure}")
        return 1

    files = load("FILES.tsv")
    symbols = load("SYMBOLS.tsv")
    routes = load("ROUTES.tsv")
    tables = load("TABLES.tsv")
    updates = load("UPDATES.tsv")
    features = load("FEATURES.tsv")
    summary = json.loads((MAP / "summary.json").read_text(encoding="utf-8"))

    # ۳. تازگی (مهمترین چک) --------------------------------------------------
    result = subprocess.run(
        [sys.executable, str(BUILDER), "--check"],
        cwd=ROOT, capture_output=True, text=True, timeout=120,
    )
    check(result.returncode == 0, "ایندکسها کهنهاند — python3 tools/build_system_map.py را اجرا کن")

    # ۴. پوشش فایلها ---------------------------------------------------------
    paths = {row["path"] for row in files}
    for required in (
        "index.php", "bootstrap.php", "food-ticket.php", "permissions.php",
        "lib/db-core.php", "lib/tickets-sla.php", "actions/ticket-actions.php",
        "actions/org-actions.php", "schema.sql", "install-empty-db.sql",
        "migrations/013-manual-attendance-record-backfill.sql",
        "cron/food_ticket_worker.php", "food-ticket-web/index.html",
        ".github/workflows/ci.yml", "VERSION", "tools/build_system_map.py",
        "tools/find-in-system.py", "docs/SYSTEM-MAP-FA.md",
    ):
        check(required in paths, f"فایل کلیدی در ایندکس نیست: {required}")
    check(len(files) >= 800, f"تعداد فایلهای ایندکسشده کم است: {len(files)}")

    # ۵. اعتبار شماره خط نمادها ----------------------------------------------
    line_counts = {row["path"]: int(row["lines"]) for row in files}
    bad_lines = [s for s in symbols
                 if s["file"] in line_counts
                 and not (1 <= int(s["line"]) <= line_counts[s["file"]])]
    check(not bad_lines, f"{len(bad_lines)} نماد شماره خط نامعتبر دارند")
    check(len(symbols) >= 4000, f"تعداد نمادها کم است: {len(symbols)}")

    # ۶. روتها ---------------------------------------------------------------
    pages = {r["name"] for r in routes if r["kind"] == "page"}
    actions = {r["name"] for r in routes if r["kind"] == "action"}
    apis = {r["name"] for r in routes if r["kind"] == "api"}
    for page in ("dashboard", "food-ticket", "food-order", "traffic-control",
                 "settings", "ticket", "assets", "organization", "system-health"):
        check(page in pages, f"صفحهٔ کلیدی ایندکس نشده: ?page={page}")
    for action in ("create_ticket", "save_org_node", "system_update",
                   "food_ticket_save_config", "supervisor_decision"):
        check(action in actions, f"اکشن کلیدی ایندکس نشده: {action}")
    check(len(pages) >= 35, f"تعداد صفحات کم است: {len(pages)}")
    check(len(actions) >= 80, f"تعداد اکشنها کم است: {len(actions)}")
    check(len(apis) >= 60, f"تعداد APIها کم است: {len(apis)}")

    # ۷. جداول ---------------------------------------------------------------
    table_names = {t["table"] for t in tables}
    for table in ("tickets", "users", "food_orders", "food_ticket_events",
                  "assets", "role_permissions", "system_logs", "holidays"):
        check(table in table_names, f"جدول کلیدی ایندکس نشده: {table}")
    check(len(table_names) >= 70, f"تعداد جداول کم است: {len(table_names)}")

    # ۸. بستهها و ویژگیها ----------------------------------------------------
    check(len(updates) >= 110, f"تعداد بستههای آپدیت کم است: {len(updates)}")
    check(len(features) >= 36, f"تعداد حوزههای ویژگی کم است: {len(features)}")
    check(summary["generated_by"] == "tools/build_system_map.py",
          "summary.json منبع تولید را اعلام نکرده")

    # ۹. ابزار جستجو واقعاً کار میکند ----------------------------------------
    for args, needle in (
        (["--symbol", "food_ticket_mark_delivered_on_print"], "food-ticket-runtime.php"),
        (["--page", "food-ticket"], "?page=food-ticket"),
        (["--table", "food_orders"], "schema.sql"),
        (["ثبت دستی حضور"], "گروههای غذایی"),
    ):
        proc = subprocess.run(
            [sys.executable, str(FINDER), *args],
            cwd=ROOT, capture_output=True, text=True, timeout=60,
        )
        check(proc.returncode == 0 and needle in proc.stdout,
              f"find-in-system.py روی {args} نتیجهٔ موردانتظار ({needle}) را نداد")

    # --- نتیجه --------------------------------------------------------------
    print(f"نقشه: {len(files)} فایل · {len(symbols)} نماد · {len(routes)} روت · "
          f"{len(table_names)} جدول · {len(updates)} بسته · {len(features)} ویژگی")
    print(f"چکهای اجراشده: {checks}")
    if failures:
        print(f"\nFAIL — {len(failures)} مورد:")
        for failure in failures:
            print(f"  - {failure}")
        return 1
    print("PASS — نقشهٔ سامانه کامل، معتبر و بهروز است.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
