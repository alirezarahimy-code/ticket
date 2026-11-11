"""قفل قرارداد دامنهٔ «چاپ خودکار فیش» (خواستهٔ صریح مالک، ۱۴۰۵):

ثبت خودکار فقط و فقط شامل «شام؛ ناهارِ پنجشنبه/جمعه/تعطیلات رسمی» است.
«ناهار روز عادی» تحت هیچ شرایطی نباید فیش خودکار یا تحویل خودکار بگیرد.

این تست‌ها بدون اجرای PHP، محتوای کد را می‌سنجند.
"""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
AUTO = (ROOT / "food-ticket-auto-receipts.php").read_text(encoding="utf-8")
WORKER = (ROOT / "cron/food_ticket_worker.php").read_text(encoding="utf-8")
ORDER = (ROOT / "food-order.php").read_text(encoding="utf-8")
MIG_037 = (ROOT / "migrations/037-food-auto-receipts-delivered.sql").read_text(encoding="utf-8")


def test_staff_lunch_requires_closed_canteen_day() -> None:
    # دروازهٔ کارکنان: ناهار فقط اگر «holiday» (پنجشنبه/جمعه/تعطیل فعال در جدول holidays) باشد.
    assert "if ($mealType === 'lunch' && empty($holiday['holiday'])) {" in AUTO


def test_guest_lunch_requires_closed_canteen_day() -> None:
    # همان دروازه برای درخواست مهمان.
    assert AUTO.count("if ($mealType === 'lunch' && empty($holiday['holiday'])) {") >= 2


def test_dinner_is_not_gated_by_holiday() -> None:
    # شام همیشه شامل ثبت خودکار است (تعطیل یا عادی) — منبع عبارت‌گوی «شام».
    assert "'auto-dinner'" in AUTO
    assert "'auto-guest-dinner'" in AUTO
    # شام نباید پشت دروازهٔ تعطیل مخفی شده باشد: بعد از دروازهٔ ناهار، شرط بعدی فقط meal_type است.
    dinner_block = AUTO[AUTO.index("'auto-dinner'"):]
    assert "empty($holiday['holiday'])" not in dinner_block.split("//", 1)[0][:200]


def test_auto_receipts_only_run_in_end_of_day_pass() -> None:
    # تنها فراخوانی ثبت خودکار در Worker: داخل بلوک پایان‌روز (ساعت ۲۳ به بعد).
    idx = WORKER.index("food_ticket_auto_receipts_run(")
    head = WORKER[:idx]
    assert "if ($endOfDayDue && $readSource && $processorEnabled" in head
    assert "$tehranHour >= 23" in WORKER
    # خارج از پایان‌روز هیچ فراخوانی دیگری وجود ندارد.
    assert WORKER.count("food_ticket_auto_receipts_run(") == 2  # دیروز + امروز


def test_auto_receipts_are_marked_delivered_for_staff() -> None:
    assert "'delivery_status' => 'delivered'," in AUTO


def test_only_migration_037_flips_legacy_auto_rows() -> None:
    assert "event_type = 'auto_printed'" in MIG_037
    assert "delivery_status = 'delivered'" in MIG_037
    # مهمان هرگز تحویل‌شده شمرده نمی‌شود (فقط از توضیح کنار گذاشته می‌شود؛ خود SQL فقط auto_printed را هدف می‌گیرد).
    sql_code = "\n".join(l for l in MIG_037.splitlines() if not l.strip().startswith("--"))
    assert "auto_guest" not in sql_code


def test_delivery_report_includes_auto_rows() -> None:
    # گزارش «غذای تحویل‌شده» ردیف‌های ثبت خودکار را نمایش می‌دهد (نه فقط شمارش ته صفحه).
    assert "OR (e.event_type = 'auto_printed' AND e.delivery_status = 'delivered')" in ORDER
    assert "OR e.event_type = 'auto_guest'" in ORDER


def test_holiday_source_is_single_calendar() -> None:
    # تعطیل رسمی = جدول holidays (فعال) یا پنجشنبه/جمعه — همان تعریف کار B و پنجرهٔ کاری.
    assert "FROM holidays WHERE holiday_date = ? AND is_active = 1" in AUTO
    assert "in_array($dayOfWeek, [4, 5], true)" in AUTO
