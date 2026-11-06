"""نگهبان ایستای ترمیم شماره‌گذاری درخواست و حذف N+1 در فهرست‌ها.

این تست‌ها رفتار را اجرا نمی‌کنند (آن کار بر عهدهٔ tests/test_ticket_number_autofill_unit.php
است که به PHP نیاز دارد)؛ اینجا فقط ساختارهایی قفل می‌شوند که اگر کسی ناخواسته برشان
گرداند، باگ بحرانی دوباره زنده می‌شود.
"""

from __future__ import annotations

import io
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def read(relative_path: str) -> str:
    with io.open(os.path.join(ROOT, relative_path), encoding="utf-8") as handle:
        return handle.read()


def autofill_body() -> str:
    source = read("lib/tickets-sla.php")
    start = source.index("function ticket_number_autofill(")
    end = source.index("\nfunction ", start + 10)
    return source[start:end]


def test_autofill_seeds_counter_from_existing_maximum():
    """شمارنده نباید از صفر شروع کند؛ وگرنه روی جدول دارای شماره، تکراری می‌سازد."""
    body = autofill_body()
    assert "ticket_number_sequence_floor($prefix)" in body, (
        "ticket_number_autofill باید کف شمارنده را از ticket_number_sequence_floor بگیرد"
    )
    assert "($sequence[$prefix] ?? 0) + 1" not in body, (
        "الگوی معیوب «شروع از صفر» برگشته است"
    )


def test_sequence_floor_consults_both_counter_and_existing_numbers():
    source = read("lib/tickets-sla.php")
    start = source.index("function ticket_number_sequence_floor(")
    end = source.index("\nfunction ", start + 10)
    body = source[start:end]
    assert "SELECT seq FROM ticket_number_seq" in body
    assert "MAX(CAST(SUBSTRING_INDEX" in body
    assert "return max($current, $existing);" in body, (
        "کف شمارنده باید بیشینهٔ «شمارندهٔ ثبت‌شده» و «بزرگ‌ترین شمارهٔ موجود» باشد"
    )


def test_autofill_isolates_each_row_so_one_failure_does_not_stop_the_loop():
    body = autofill_body()
    inner = body[body.index("foreach ($rows as $row)"):]
    assert "try {" in inner and "catch (Throwable)" in inner, (
        "هر ردیف باید try/catch مستقل داشته باشد تا یک برخورد کل ترمیم را قطع نکند"
    )


def test_autofill_reads_in_batches_and_cannot_loop_forever():
    body = autofill_body()
    assert "LIMIT 500" in body, "خواندن باید دسته‌ای باشد تا حافظه پر نشود"
    assert "if ($progress === 0)" in body, "بدون پیشرفت باید از حلقه خارج شود"


def test_completion_marker_is_not_trapped_in_the_empty_branch():
    """نشانگر باید بعد از شمارش باقی‌مانده ثبت شود، نه فقط وقتی از اول چیزی نبود."""
    body = autofill_body()
    marker_at = body.index("save_setting('ticket_autofill_done', '1')")
    remaining_at = body.index("SELECT COUNT(*) FROM tickets WHERE ticket_no IS NULL")
    assert remaining_at < marker_at, (
        "ثبت نشانگر باید بعد از شمارش درخواست‌های بی‌شماره بیاید"
    )
    assert "if ($remaining === 0)" in body


def test_counter_is_persisted_back_to_the_sequence_table():
    body = autofill_body()
    assert "ticket_number_sequence_persist($sequence)" in body
    source = read("lib/tickets-sla.php")
    assert "GREATEST(seq, VALUES(seq))" in source, (
        "ثبت شمارنده نباید مقدار بزرگ‌تر هم‌زمان را عقب ببرد"
    )


def test_fallback_number_is_not_a_fake_lookalike():
    """تیکت بدون شماره نباید شماره‌ای نشان دهد که در دیتابیس وجود ندارد."""
    source = read("lib/tickets-sla.php")
    start = source.index("function ticket_number(int $id): string")
    end = source.index("\nfunction ", start + 10)
    body = source[start:end]
    assert "return '#' . $id;" in body
    assert "'SBU1-86-'" not in body, "قالب شمارهٔ ساختگی برگشته است"

    for path in ("chatbot.php", "help-guide.php", "tests/smoke.php"):
        assert "SBU1-86" not in read(path), f"ارجاع به قالب ساختگی در {path} مانده است"


def test_chatbot_still_recognises_the_internal_reference():
    """قالب «#۱۲۳» باید به‌عنوان شناسهٔ عددی قابل تشخیص بماند."""
    source = read("chatbot.php")
    start = source.index("function chatbot_ticket_ref(")
    end = source.index("\n/**", start)
    body = source[start:end]
    assert re.search(r"preg_match_all\('/\\\\d\+/'", body) or "preg_match_all('/\\d+/'" in body, (
        "استخراج شناسهٔ عددی از متن کاربر باید حفظ شود"
    )


def test_list_pages_prime_the_number_cache_instead_of_querying_per_row():
    """هر فهرستی که ticket_no را با SELECT آورده، نباید در حلقه کوئری جدا بزند."""
    source = read("index.php")
    assert source.count("ticket_number_cache_prime(") >= 4, (
        "صف کار، گزارش، پنل سوپروایزر و فهرست اصلی باید کش را پر کنند"
    )
    for variable in ("$queueRows", "$reportRows", "$supervisorRows", "$tickets"):
        assert f"ticket_number_cache_prime({variable})" in source, (
            f"فهرست {variable} هنوز به ازای هر ردیف کوئری می‌زند"
        )


def test_cache_prime_helper_exists_and_tolerates_bad_rows():
    source = read("lib/tickets-sla.php")
    start = source.index("function ticket_number_cache_prime(")
    end = source.index("\nfunction ", start + 10)
    body = source[start:end]
    assert "array_key_exists('ticket_no', $row)" in body, (
        "ردیف بدون ستون ticket_no نباید کش را با مقدار غلط پر کند"
    )
    assert "$id <= 0" in body


def test_bulk_update_has_a_selection_cap():
    source = read("actions/bulk-actions.php")
    assert "count($ticketIds) > 200" in source, (
        "عملیات گروهی باید سقف داشته باشد تا قفل‌های تراکنش طولانی نشوند"
    )


def test_bulk_update_reports_tickets_that_were_already_in_the_target_status():
    source = read("actions/bulk-actions.php")
    assert "$bulkAlready" in source
    assert "درخواست از قبل در همین وضعیت بود." in source, (
        "تیکت هم‌وضعیت باید در گزارش دیده شود تا مجموع جور دربیاید"
    )
