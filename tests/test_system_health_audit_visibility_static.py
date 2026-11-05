"""ممیزی باید از مرورگر دیده شود، نه فقط از خط فرمان سرور.

مدیر سامانه به سرور دسترسی SSH ندارد و نباید داشته باشد. اگر وضعیت ممیزی
فقط در لاگ فایل باشد، عملاً کسی نمی‌بیندش و سکوت صفحه به‌اشتباه نشانهٔ
سلامت تلقی می‌شود.
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
HEALTH = (ROOT / "system-health.php").read_text(encoding="utf-8")


def _checks_body() -> str:
    start = HEALTH.index("function system_health_checks(")
    return HEALTH[start:]


def test_audit_status_is_reported_on_the_health_page():
    body = _checks_body()
    assert "ممیزی سلامت سامانه" in body, "وضعیت ممیزی در صفحهٔ سلامت نمایش داده نمی‌شود"
    assert "خط مبنای ممیزی" in body, "وضعیت خط مبنا در صفحهٔ سلامت نیست"


def test_health_page_loads_the_audit_module():
    assert "require_once __DIR__ . '/lib/logging-audit.php'" in HEALTH, (
        "صفحهٔ سلامت ماژول ممیزی را بار نمی‌زند؛ توابع ممیزی تعریف‌نشده می‌مانند"
    )


def test_missing_heartbeat_is_not_reported_as_healthy():
    """نبودِ ضربان یعنی «نمی‌دانم»، نه «سالم»."""
    body = _checks_body()
    seg = body[body.index("ممیزی سلامت سامانه"):]
    seg = seg[:seg.index("$loggingCounters")]
    m = re.search(r"if \(!is_file\(\$heartbeat\)\) \{\s*\$checks\[\] = \['ممیزی سلامت سامانه', '(\w+)'", seg)
    assert m, "شاخهٔ «ضربان وجود ندارد» پیدا نشد"
    assert m.group(1) in ("warn", "fail"), (
        f"نبودِ ضربان نباید ok گزارش شود، ولی '{m.group(1)}' است"
    )


def test_stale_audit_is_a_failure_not_a_warning():
    """اگر کرون یک شبانه‌روز نخوابیده باشد، این خرابی است نه تذکر."""
    seg = _checks_body()
    assert "time() - 86400" in seg, "آستانهٔ کهنگی ممیزی تعریف نشده"
    m = re.search(
        r"\$lastTs < time\(\) - 86400\) \{\s*\$checks\[\] = \['ممیزی سلامت سامانه', '(\w+)'", seg)
    assert m and m.group(1) == "fail", "ممیزیِ کهنه باید fail باشد"


def test_structural_problems_outrank_regressions():
    """ناسازگاری ساختاری fail است؛ پس‌رفت رفتاری warn."""
    seg = _checks_body()
    sm = re.search(r"\$structural > 0\) \{\s*\$checks\[\] = \['ممیزی سلامت سامانه', '(\w+)'", seg)
    rm = re.search(r"\$regressions > 0\) \{\s*\$checks\[\] = \['ممیزی سلامت سامانه', '(\w+)'", seg)
    assert sm and sm.group(1) == "fail", "ناسازگاری ساختاری باید fail باشد"
    assert rm and rm.group(1) == "warn", "پس‌رفت رفتاری باید warn باشد"


def test_probe_failure_is_surfaced_not_swallowed():
    """اگر خواندن وضعیت ممیزی شکست بخورد، خودش یک خرابی است."""
    seg = _checks_body()
    m = re.search(r"catch \(Throwable \$auditProbe\) \{\s*\$checks\[\] = \['ممیزی سلامت سامانه', '(\w+)'", seg)
    assert m and m.group(1) == "fail", (
        "شکست خواندن وضعیت ممیزی نباید بی‌صدا یا صرفاً تذکر باشد"
    )


def test_baseline_message_does_not_demand_server_access():
    """کاربر گفت نمی‌خواهد وارد سرور شود؛ پیام نباید او را به SSH بفرستد."""
    seg = _checks_body()
    seg = seg[seg.index("خط مبنای ممیزی"):]
    seg = seg[:seg.index("catch (Throwable $auditProbe)")]
    assert "--update-baseline" not in seg, (
        "پیام خط مبنا نباید اجرای دستی روی سرور را الزام کند؛ ساخت آن خودکار است"
    )
    assert "خودکار" in seg, "باید صریح بگوید ساخت خط مبنا خودکار است"
