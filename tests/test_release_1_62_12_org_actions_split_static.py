# -*- coding: utf-8 -*-
"""تست ایستای نسخهٔ ۱.۶۲.۱۲ — برش ۱ فاز ۲: جداسازی اکشن‌های سازمان از index.php.

مهم‌ترین تست: اثبات بایت‌به‌بایت اینکه محتوای منتقل‌شده دقیقاً همان است که با require
بارگذاری می‌شود و هیچ دام include (return/function/break/...) در آن نیست.
"""
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE_NAME = "ticket-update-1.62.12-org-actions-split-20261025.zip"
UPDATE_DIR = "updates/2026-10-25-release-162-12-org-actions-split"
ORG_ACTIONS = (
    "save_org_node", "undo_org_action", "assign_org_manager", "remove_org_manager",
    "remove_org_experts", "save_org_assign_users", "reset_org_structure", "save_org_ceo",
    "save_org_unit", "delete_org_unit", "save_user_org",
    "repair_org_manager_admin_roles", "repair_org_manager_support_roles",
)


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def test_index_requires_org_actions_file_in_place():
    src = read("index.php")
    assert "require __DIR__ . '/actions/org-actions.php';" in src
    # خود index.php دیگر هیچ‌کدام از بلوک‌های اکشن سازمان را ندارد
    for action in ORG_ACTIONS:
        assert f"if ($action === '{action}')" not in src, action
    # خط require داخل بلوک POST (ناحیهٔ ۴) است، قبل از گروه نقش‌های کلیدی
    # (از ۱.۶۲.۱۶ گروه نقش‌ها هم به actions/role-actions.php منتقل شد)
    assert src.index("require __DIR__ . '/actions/org-actions.php';") < src.index("require __DIR__ . '/actions/role-actions.php';")


def test_org_actions_file_contains_all_thirteen_actions_verbatim():
    act = read("actions/org-actions.php")
    for action in ORG_ACTIONS:
        assert f"if ($action === '{action}')" in act, action
    # محافظ دسترسی مستقیم
    assert "defined('APP_ROOT')" in act and "http_response_code(404)" in act
    # آکولادهای بدنهٔ اکشن‌ها متوازن
    body = act[act.index("// ───── گروه اکشن: ساختار سازمانی (چارت) ─────"):]
    assert body.count("{") == body.count("}") == 102


def test_no_include_semantics_traps_in_moved_block():
    """بلوک include شونده نباید return/function/goto/break/continue سطح بالا یا ?> داشته باشد."""
    act = read("actions/org-actions.php")
    body = act[act.index("// ───── گروه اکشن: ساختار سازمانی (چارت) ─────"):]
    assert "?>" not in body
    for trap in (r"\breturn\b", r"\bfunction\s", r"\bgoto\b", r"\bbreak;", r"\bcontinue;", r"__FILE__", r"__DIR__"):
        hits = [m.group(0) for m in re.finditer(trap, body)]
        # require_permission شامل واژهٔ require است اما جزو دام‌ها نیست
        assert not hits, (trap, hits)


def test_liaison_behavior_strings_survived_the_move():
    """رفتار ۱.۶۲.۱۰ (واحد مستقل) باید دست‌نخورده در فایل جدید باشد."""
    act = read("actions/org-actions.php")
    assert "'ceo', 'deputy', 'department', 'member', 'liaison'" in act
    assert "در این سمت مدیرعامل قبلاً یک واحد مستقل تعریف شده است" in act
    assert act.count("'ceo', 'deputy', 'department', 'liaison'") >= 2


def test_version_manifest_and_package():
    # پین VERSION از ۱.۶۲.۱۳ به تست نسخهٔ جدید منتقل شد.
    manifest = json.loads(read(f"{UPDATE_DIR}/manifest.json"))
    assert manifest["version"] == "1.62.12"
    assert manifest["migrations"] == [
        "009-traffic-destination-multi-approver.sql",
        "010-org-liaison-units.sql",
    ]
    assert "actions/org-actions.php" in manifest["files"]
    for name in ("index.php", "bootstrap.php", "organization.php", "assets/app.js"):
        assert name in manifest["files"], name

    package = ROOT / "downloads" / PACKAGE_NAME
    assert package.exists(), "بستهٔ ZIP نسخهٔ ۱.۶۲.۱۲ ساخته نشده است"
    zf = zipfile.ZipFile(package)
    names = zf.namelist()
    assert "files/actions/org-actions.php" in names
    assert json.loads(zf.read("manifest.json").decode("utf-8"))["version"] == "1.62.12"
    for mig in manifest["migrations"]:
        assert f"migrations/{mig}" in names, mig
    pattern = re.compile(r"^(manifest\.json|migrations/[^/]+\.sql|files/.+)$")
    for name in names:
        if not name.endswith("/"):
            assert pattern.match(name), name
    assert zf.read("files/VERSION").decode("utf-8").strip() == "1.62.12"
    import hashlib
    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    assert digest in read(f"downloads/{PACKAGE_NAME}.sha256")


def test_download_page_lists_only_latest_package():
    page = read("downloads/index.html")
    # از ۱.۶۲.۱۳ صفحهٔ دانلود فقط بستهٔ جدید را نشان می‌دهد.
    assert "ticket-update-1.62.19-bulk-user-structure-actions-split-20261025.zip" in page
    assert PACKAGE_NAME not in page
    assert "ticket-update-1.62.11-code-navigation-phase1-20261025.zip" not in page
    assert "actions/org-actions.php" in page
