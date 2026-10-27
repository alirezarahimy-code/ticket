#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""تست ایستای «گزارش تجمیعی» — سمت سرور و ادغام با پنل.

چون PHP در محیط توسعه موجود نیست، ساختار توابع/routes/دسترسیها/منابع SQL
را بهصورت ایستا بررسی میکند. تست رفتاری JS جداگانه در
tests/js/food-aggregate-report.test.js اجرا میشود.

اجرا: python3 tests/test_food_aggregate_report_static.py
"""

from __future__ import annotations

import json
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PHP = ROOT / "food-ticket.php"
HTML = ROOT / "food-ticket-web" / "index.html"
JS_TEST = ROOT / "tests" / "js" / "food-aggregate-report.test.js"


def main() -> int:
    failures: list[str] = []
    checks = 0

    def check(condition: bool, message: str) -> None:
        nonlocal checks
        checks += 1
        if not condition:
            failures.append(message)

    php = PHP.read_text(encoding="utf-8")
    html = HTML.read_text(encoding="utf-8")

    # ۱) توابع سرور -----------------------------------------------------------
    for fn in (
        "function food_ticket_api_aggregate_report(",
        "function food_ticket_aggregate_columns(",
        "function food_ticket_aggregate_export(",
        "function food_ticket_aggregate_row(",
    ):
        check(fn in php, f"تابع PHP وجود ندارد: {fn}")

    # ۲) منابع SQL — فقط خواندنی و امن ----------------------------------------
    agg_start = php.find("function food_ticket_api_aggregate_report(")
    agg_end = php.find("function food_ticket_api_absent(", agg_start)
    agg_body = php[agg_start:agg_end]
    check(agg_body.count("SELECT COUNT(*) FROM food_orders") == 1,
          "شمارش سفارشها از food_orders انجام نمیشود")
    check("status = \"active\"" in agg_body,
          "شمارش سفارشها وضعیت active را فیلتر نمیکند")
    check("print_status = 'printed'" in agg_body,
          "فیشهای چاپشده فیلتر نشدهاند")
    check("event_type IN ('printed','guest')" in agg_body,
          "تفکیک فیش مهمان/پرسنل انجام نمیشود")
    check("food_ticket_api_absent($from, $to)" in agg_body,
          "غایبین از تابع رسمی سامانه گرفته نمیشوند")
    check("has_mismatch" in agg_body and "difference" in agg_body,
          "محاسبهٔ مغایرت انجام نمیشود")

    # نباید هیچ دستور تغییردهندهای داشته باشد (قاعدهٔ عدم حذف داده)
    for forbidden in ("DELETE FROM", "DROP TABLE", "UPDATE ", "INSERT INTO", "TRUNCATE"):
        check(forbidden not in agg_body,
              f"گزارش تجمیعی نباید دستور تغییردهنده داشته باشد: {forbidden}")

    # پارامتربندی شده (امن در برابر تزریق SQL)
    check("$pdo->prepare(" in agg_body or "db()->prepare(" in agg_body,
          "کوئریها پارامتری نیستند")
    check("BETWEEN ? AND ?" in agg_body, "فیلتر بازهٔ تاریخ پارامتری نیست")

    # ۳) routes و دسترسیها -----------------------------------------------------
    check("$route === 'aggregate-report'" in php, "route aggregate-report وجود ندارد")
    check("$route === 'aggregate-report-export'" in php,
          "route aggregate-report-export وجود ندارد")
    check("'aggregate-report' => 'food.reports'" in php,
          "دسترسی aggregate-report به food.reports محدود نشده")
    check("'aggregate-report-export' => 'food.reports'" in php,
          "دسترسی export به food.reports محدود نشده")
    check("'aggregate-report', 'aggregate-report-export'" in php,
          "فالبک مدیر پشتیبانی بهروز نشده")
    check("columns' => food_ticket_aggregate_columns()" in php,
          "فهرست ستونها به کلاینت ارسال نمیشود")

    # ۴) خروجی XLSX (نه CSV) --------------------------------------------------
    check("xlsx_build(" in php and "xlsx_send(" in php,
          "خروجی XLSX واقعی ساخته نمیشود")
    check(".xlsx" in php, "پسوند خروجی XLSX نیست")
    check("food_ticket_aggregate_export" in php, "تابع export صدا زده نمیشود")

    # ۵) UI — دوتا تب ----------------------------------------------------------
    for marker in (
        'data-report-tab="classic"', 'data-report-tab="aggregate"',
        "گزارش تجمیعی", "گزارشگیری",
        "function reportAggregateView()", "function reportView(){",
        "function reportClassicView(){",
        "function loadAggregate(", "function showAggregatePreview()",
        "function aggDownloadUrl()",
    ):
        check(marker in html, f"نشانهٔ UI وجود ندارد: {marker}")

    # محتوای قدیمی حفظ شده باشد
    check("id=\"report-filter\"" in html, "فرم گزارش قدیمی حذف شده است")
    check("reportClassicView()" in html, "تب اول به محتوای قدیمی وصل نیست")

    # ۵ شمارنده + مغایرت
    for label in ("تعداد کل سفارشها", "فیش مهمان چاپ شده",
                  "فیش چاپشده قطعی", "تعداد غایبین", "مغایرت"):
        check(label in html, f"شمارنده «{label}» در UI نیست")

    # انتخاب روز / فیلد / استخراج / چاپ / اسامی
    for marker in ('id="aggregate-filter"', "data-agg-field=",
                   'data-action="aggregate-export"', 'data-action="aggregate-print"',
                   "aggregate-today", "نفراتی که فیش چاپشده دارند", "غایبین"):
        check(marker in html, f"قابلیت UI وجود ندارد: {marker}")

    # دادههای سنگین نباید در localStorage ذخیره شوند
    check("aggregate:_ag" in html, "دادهٔ موقت aggregate در persist ذخیره میشود")

    # ۶) نسخه پنل بالا رفته باشد ----------------------------------------------
    check("PANEL_VERSION='1.37.29'" in html,
          "نسخهٔ پنل برای جلوگیری از کش قدیمی بالا نرفته است")

    # ۷) اجرای تست رفتاری JS --------------------------------------------------
    if JS_TEST.is_file():
        proc = subprocess.run(["node", str(JS_TEST)], cwd=ROOT,
                              capture_output=True, text=True, timeout=120)
        check(proc.returncode == 0,
              f"تست رفتاری JS ناموفق: {(proc.stdout + proc.stderr)[-300:]}")

    print(f"چکهای گزارش تجمیعی: {checks}")
    if failures:
        print(f"\nFAIL — {len(failures)} مورد:")
        for item in failures:
            print(f"  - {item}")
        return 1
    print("PASS — گزارش تجمیعی (سرور + پنل + تست رفتاری) کامل و ایمن است.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
