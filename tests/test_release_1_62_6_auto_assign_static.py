# -*- coding: utf-8 -*-
"""Static checks for 1.62.6: ticket auto-assignment keeps org-chart managers
last in line, plus the diagnostics/repair tooling on the organization page."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INDEX = (ROOT / "index.php").read_text(encoding="utf-8")


def test_org_managers_are_deprioritized_in_auto_assignment():
    assert "function ticket_org_manager_rank_sql()" in INDEX
    # هر دو کوئری (مالک حوزه و جایگزین) باید رتبهٔ مدیر سازمانی را در ORDER BY داشته باشند.
    assert INDEX.count("ORDER BY ' . $orgManagerRank . 'COUNT(t.id), u.id LIMIT 1") == 2
    # روی نصب‌های بدون جدول چارت، رتبه خالی می‌شود و ثبت تیکت خطا نمی‌گیرد.
    assert "$ready = false;" in INDEX


def test_support_manager_repair_action_exists():
    assert "repair_org_manager_support_roles" in INDEX
    assert 'u.role = "support_manager" AND EXISTS (SELECT 1 FROM org_units ou' in INDEX
    assert "org_manager_support_role_repaired" in INDEX
    # ترمیم ادمین اصلی قبلی دست‌نخورده مانده است.
    assert "repair_org_manager_admin_roles" in INDEX


def test_auto_assign_diagnostics_card_is_rendered():
    assert "ticket-auto-assign-diagnostics" in INDEX
    assert "تشخیص ارجاع خودکار درخواست‌ها" in INDEX
    assert "مقصد فعلی ارجاع خودکار" in INDEX
    assert "آخرِ صف ارجاع خودکار" in INDEX


def test_help_guide_explains_ticket_assignee():
    help_text = (ROOT / "help-guide.php").read_text(encoding="utf-8")
    assert "مسئول درخواست (تیکت) چه کسی می‌شود؟" in help_text
    assert "تشخیص ارجاع خودکار درخواست‌ها" in help_text


if __name__ == "__main__":
    test_org_managers_are_deprioritized_in_auto_assignment()
    test_support_manager_repair_action_exists()
    test_auto_assign_diagnostics_card_is_rendered()
    test_help_guide_explains_ticket_assignee()
    print("ok")
