"""Static safety/compatibility checks for the 1.62.49 organization-tree update."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(name: str) -> str:
    return (ROOT / name).read_text(encoding="utf-8")


def test_additive_sort_migration_is_not_run_on_page_load():
    migration = read("migrations/017-org-tree-order.sql")
    assert "ADD COLUMN IF NOT EXISTS sort_order" in migration
    assert "DROP" not in migration.upper() and "DELETE" not in migration.upper()
    assert "sort_order" not in read("organization.php").split("function org_ensure_schema", 1)[1].split("function org_ticket_schema_ensure", 1)[0]
    rollback = read("migrations/017-org-tree-order-rollback.sql")
    assert "DROP COLUMN IF EXISTS sort_order" in rollback


def test_recursive_tree_and_nested_department_validation():
    js = read("assets/organization.js")
    actions = read("actions/org-actions.php")
    assert "var renderUnitBranch" in js
    assert "children(item.id).filter(function (child) { return child.type === 'department'; })" in js
    assert "org-orphan-units" in js and "org-cycle-warning" in js
    assert "var directDepartments = children(ceo.id, 'department')" in js
    assert "[org_company_root_type($orgCompanyId), 'deputy', 'management', 'office', 'department', 'member', 'liaison']" in actions
    assert "org_unit_subtree_ids($unitId)" in actions
    assert "انتقال واحد به خودش یا یکی از زیرمجموعه‌هایش مجاز نیست" in actions


def test_undo_redo_is_delta_scoped_and_does_not_restore_user_roles():
    org = read("organization.php")
    actions = read("actions/org-actions.php")
    assert "org_snapshot_delta" in org
    assert "org_restore_snapshot($undo['before'], $undo['after'], $orgCompanyId)" in actions
    assert "org_restore_snapshot($redo['after'], $redo['before'], $orgCompanyId)" in actions
    assert "function org_snapshot(?int $companyId = null)" in org
    assert "JOIN org_units ou ON ou.id = m.unit_id WHERE ou.company_id = " in org
    snapshot = org.split("function org_snapshot(?int $companyId = null): array", 1)[1].split("function org_snapshot_maps", 1)[0]
    assert "SELECT u.id, u.org_unit_id, u.manager_user_id FROM users u" in snapshot
    assert "org_user_company_membership_join_sql('u')" in snapshot
    assert "SELECT id, org_unit_id, manager_user_id, role" not in snapshot


def test_existing_data_fields_and_privacy_boundary_are_kept():
    index = read("index.php")
    org = read("organization.php")
    assert "'parentId' => (int) ($unit['parent_id'] ?? 0)" in index
    assert "'managerId' => (int) ($unit['manager_user_id'] ?? 0)" in index
    assert "'chartSide' => (string) ($unit['chart_side'] ?? '')" in index
    chart_payload = index.split("$orgChartUsers = []", 1)[1].split("$orgUndo =", 1)[0]
    assert "national_code" not in chart_payload and "employee_number" not in chart_payload
    assert "function org_liaison_units" in org
    assert "unit_type = \"liaison\"" in org


def test_ui_keeps_fixed_sidebars_focus_pan_and_four_deputies_per_row():
    js = read("assets/organization.js")
    css = read("assets/style.css")
    page = read("index.php")
    # از ۱.۶۲.۵۳ ردیف‌ها با اندازهٔ دستهٔ متغیر ساخته می‌شوند (۵ + تعداد دسته‌ها).
    assert "var batchSize = 5 + batches.length;" in js
    assert "class=\"org-deputy-row org-root-unit-row\"" in js
    assert "event.code === 'Space'" in js and "org-panning" in js
    assert "data-org-view=\"focus\"" in page and "◫" in page
    assert ".org-sidebar { order: 3;" in css and ".org-pool-sidebar { order: 1;" in css
    assert "sort_org_deputies" in read("actions/org-actions.php")


def test_soft_delete_and_shared_food_users_are_protected():
    js = read("assets/organization.js")
    actions = read("actions/org-actions.php")
    assert "پیش‌نمایش حذف نرم" in js
    assert "UPDATE org_units SET is_active = 0" in actions
    assert "COALESCE(auth_source, \"\") <> \"food\"" in actions
    delete_block = actions.split("if ($action === 'delete_org_unit')", 1)[1].split("if ($action === 'save_user_org')", 1)[0]
    assert "DELETE FROM org_units" not in delete_block
