#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
فاز ۵ — آزمون ایستا نمودار دسترسی مرکز درخواست (قراردادِ مرزی، بدون پایگاه‌داده).

هدف: هر صفحه/اکشن ماژول باید یا
  الف) در سطح بالای فایل خودش بررسی مجوز/ورود انجام دهد، یا
  ب) در «حوزهٔ اسکوپ legacy» با require_permission اجرا شود، یا
  ج) خواندگی را با پیش‌شرط دامنه‌محور (findVisible) و سد 404 اعمال کند.

این آزمون مانع از حذفِ خاموشِ آن حصارها در تغییرات بعدی می‌شود.
"""

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PAGES = ROOT / "modules" / "request-center" / "Pages"
ACTIONS = ROOT / "modules" / "request-center" / "Actions"


def read(rel: Path) -> str:
    return rel.read_text(encoding="utf-8")


def test_all_pages_are_covered_by_a_server_side_guard():
    assert PAGES.is_dir(), "Pages directory missing"
    failures = []
    for page in sorted(PAGES.glob("*.php")):
        source = read(page)
        markers = [
            "require_permission(",
            "require_login()",
            "user_is_primary_admin(",
            "user_can(",
            "fetch_ticket(",          # detail: scope-enforced read + 404
            "request_center_is_unit_head(",  # unit workspace: domain-specific organizational guard
            "is_staff_role(",
        ]
        if not any(marker in source for marker in markers):
            failures.append(page.name)
    assert not failures, f"صفحه‌های بدون حصار سرور-محور: {failures}"


def test_unit_workspace_requires_active_unit_head_before_repository_reads():
    source = read(PAGES / "unit.php")
    assert "defined('APP_ROOT')" in source
    assert "request_center_is_unit_head($user)" in source
    assert "request_center_error_page(403" in source
    assert "$repo->listUnitHead($user" in source
    assert source.index("request_center_is_unit_head($user)") < source.index("$repo->listUnitHead($user")


def test_pages_have_no_unguarded_module_requests():
    failures = []
    for page in sorted(PAGES.glob("*.php")):
        source = read(page)
        # تمام جاهایی که مستقیماً 403/404 صادر می‌کنند یا مجوز می‌خواهند را شمارش کن
        if not re.search(r"require_permission\(|require_login\(|http_response_code\((403|404)\)|can_view_ticket\(", source):
            failures.append(page.name)
    assert not failures, f"صفحه‌های بدون مسیرِ مردودِ صریح: {failures}"


def test_calendar_page_requires_elevated_or_own_scope():
    source = read(PAGES / "calendar.php")
    assert "$canUseCalendar" in source
    assert "user_can($user, 'supervisor.panel')" in source
    assert "user_is_primary_admin($user)" in source
    assert "http_response_code(403)" in source
    # موعدهای SLA/OLA فقط از مخزنِ دامنه‌محور خوانده می‌شوند
    assert "calendarItems(" in source
    # فرم‌های کنشی فقط به مسیر کنترلیِ خودشان پست می‌زنند
    assert "calendar=1" in source


def test_detail_page_uses_scope_enforced_read_and_hides_internal_messages():
    source = read(PAGES / "detail.php")
    assert "fetch_ticket(" in source
    assert "can_view_ticket(" in source
    assert "http_response_code(404)" in source
    assert "is_internal" in source            # پیام‌های داخلی در سمت سرور فیلتر می‌شوند
    assert "$canSeeInternal" in source


def test_module_index_enforces_workspace_allowlist():
    source = read(PAGES / "index.php")
    # فضای درخواستی فقط اگر مجوز دارد پذیرفته می‌شود؛ وگرنه default
    assert "$workspaceOptions[$requestedWorkspace]" in source
    assert "user_can($user, 'supervisor.panel')" in source
    assert "user_is_primary_admin($user)" in source
    assert "require_login()" in source


def test_actions_require_app_root_guard():
    failures = []
    for action in sorted(ACTIONS.glob("*.php")):
        source = read(action)
        if "APP_ROOT" not in source:
            failures.append(action.name)
    assert not failures, f"اکشن‌های بدون APP_ROOT guard: {failures}"


def test_rc_post_protocol_enforces_csrf_at_the_door():
    # همهٔ اکشن‌های رجیسترشدهٔ RC از مسیر post_context_begin می‌گذرند که require_csrf دارد
    handler = read(ROOT / "modules" / "request-center" / "post-handler.php")
    assert "lib/http-post.php" in handler, "RC runner must hand POSTs to the shared protocol"
    assert "post_context_begin(" in handler, "RC runner must call the shared protocol"
    dispatcher = read(ACTIONS / "dispatch.php")
    for action in ("create_ticket", "claim_ticket", "reply", "update_ticket", "supervisor_decision"):
        assert action in handler + dispatcher, f"RC action '{action}' must stay wired in the registry"
    http_post = ROOT / "lib" / "http-post.php"
    if http_post.is_file():  # در مخزن کامل موجود است؛ در QA overlay نیست و همان‌جا لایه‌های دیگر گیت می‌کنند
        source = read(http_post)
        assert "require_csrf()" in source
        assert "post_context_begin" in source


def test_appointment_actions_wire_complete_and_notifications():
    source = read(ACTIONS / "appointment-actions.php")
    for marker in (
        "rc_appointment_complete",
        "rc_appointment_create",
        "rc_appointment_cancel",
        "require_csrf()",
        "notify_user(",
        "activity_log(",
        "sla_business_hours(",
        "holidays",
        "allow_outside_business",
    ):
        assert marker in source, f"appointment-actions: marker '{marker}' missing"


def test_entry_routes_calendar_post_regression_is_pinned():
    source = read(ROOT / "request-center.php")
    method = re.search(r"REQUEST_METHOD.*?\$rcMethod\s*===\s*'POST'.*?\{", source, re.S)
    assert method, "POST branch missing in request-center.php"
    post_block = source[method.start():]
    calendar_branch = post_block.find("['calendar'] ?? '') === '1'")
    if calendar_branch == -1:
        calendar_branch = post_block.find("calendar")
    assert calendar_branch != -1, "calendar branch missing inside POST handling"
    branch = post_block[calendar_branch:calendar_branch + 260]
    assert "Pages/index.php" in branch, "calendar POST must forward to Pages/index.php"
    assert "request_center_handle_post" in post_block


def test_calendar_items_read_is_scope_enforced_in_repository():
    source = read(ROOT / "modules" / "request-center" / "RequestCenterRepository.php")
    assert "ticket_scope($user" in source
    assert "function calendarItems(" in source


def test_pages_include_only_modules_are_guarded_and_htaccess_denies():
    # هر صفحه فقط با require بارگذاری می‌شود؛ باید یا گارد APP_ROOT داشته باشد
    # یا (مثل Pages/index.php) خودش bootstrap را بارگذاری کند (نقطهٔ ورود مستقل).
    failures = []
    for page in sorted(PAGES.glob("*.php")):
        source = read(page)
        guarded = "defined('APP_ROOT')" in source
        self_boots = "dirname(__DIR__, 3) . '/bootstrap.php'" in source
        if not (guarded or self_boots):
            failures.append(page.name)
    assert not failures, f"صفحه‌های بدون گارد APP_ROOT/خود-بوت: {failures}"
    denied = ROOT / "modules" / "request-center"
    for sub in ("Pages", "Actions", "lib"):
        ht = denied / sub / ".htaccess"
        assert ht.is_file(), f"{sub}/.htaccess مفقود است (deny غیرمستقیم وب)"
        assert "Require all denied" in ht.read_text(encoding="utf-8"), f"{sub}/.htaccess باید دسترسی مستقیم را ببندد"
    assets_ht = denied / "Assets" / ".htaccess"
    if assets_ht.is_file():
        assert "require all denied" not in assets_ht.read_text(encoding="utf-8").lower(), \
            "پوشهٔ Assets نباید deny شود؛ کاربر به CSS از مرورگر نیاز دارد"


def main() -> int:
    tests = [func for name, func in sorted(globals().items()) if name.startswith("test_")]
    failed = 0
    for func in tests:
        try:
            func()
            print(f"ok - {func.__name__}")
        except AssertionError as exc:
            print(f"FAIL - {func.__name__}: {exc}")
            failed += 1
    if failed:
        print(f"\n{failed} access-matrix check(s) failed")
        return 1
    print(f"\nALL-ACCESS-MATRIX-STATIC-CHECKS-PASSED ({len(tests)})")
    return 0


if __name__ == "__main__":
    sys.exit(main())
