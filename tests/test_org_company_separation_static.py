from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def read(path):
    return (ROOT / path).read_text()

def test_company_migration_is_separate_from_page_load_and_seeds_independent_roots():
    migration = read('migrations/018-org-company-separation.sql')
    index = read('index.php')
    assert 'CREATE TABLE IF NOT EXISTS org_companies' in migration
    assert 'ALTER TABLE org_units ADD COLUMN IF NOT EXISTS company_id' in migration
    assert "'company_manager'" in migration
    assert "'ceo'" in migration
    assert "WHERE NOT EXISTS (SELECT 1 FROM org_units WHERE company_id = 2 AND unit_type = 'company_manager')" in migration
    assert '018-org-company-separation.sql' in index
    assert "require_once.*018-org-company" not in index

def test_org_chart_is_filtered_to_selected_company_and_partner_root_is_manager():
    index = read('index.php')
    helpers = read('organization.php')
    js = read('assets/organization.js')
    assert 'org_company_context_id($organizationUser' in index
    assert 'org_units_all(false, $companyId)' in index
    assert 'COALESCE(org_membership.company_id, 0) AS company_id' in index
    assert '(org_membership.company_id = ? OR org_membership.user_id IS NULL)' in index
    assert 'function org_user_company_memberships' in helpers
    assert "'companyRootType' => $companyRootType" in index
    assert "'companyRootTitle' => $companyRootTitle" in index
    assert 'not([name="company_id"])' in js
    assert 'function org_company_root_type' in helpers
    assert "? 'company_manager' : 'ceo'" in helpers or "? 'company_manager'" in helpers
    assert "item.type === rootType()" in js
    assert "target.type === 'company_manager'" in js

def test_company_status_is_derived_from_tree_membership_not_a_user_setting():
    settings = read('index.php')
    helpers = read('organization.php')
    org_actions = read('actions/org-actions.php')
    user_actions = read('actions/user-structure-actions.php')
    assert "org_user_company_membership_join_sql('u')" in settings
    assert "org_company_user_label((int) ($managedUser['company_id'] ?? 0))" in settings
    assert 'org_user_available_for_company' in org_actions
    assert 'company_id = ? AND COALESCE(auth_source' not in org_actions
    assert 'value="change_user_company"' not in settings
    assert "if ($action === 'change_user_company')" not in user_actions
    assert 'UPDATE users SET company_id' not in user_actions
    assert "'own' => 'شرکت اصلی'" in helpers
    assert "'partner' => 'شرکت همکار'" in helpers
    assert 'بدون شرکت' in helpers
    assert 'نقش‌ها را تغییر نمی‌دهد' in settings


def test_primary_admin_can_edit_display_only_company_names_without_changing_membership_or_roles():
    index = read('index.php')
    actions = read('actions/settings-actions.php')
    helpers = read('organization.php')
    assert 'org-company-picker' in index
    assert 'user_is_primary_admin($organizationUser)' in index
    assert 'name="company_names[' in index
    assert 'save_org_company_names' in index
    assert "if ($action === 'save_org_company_names')" in actions
    assert 'UPDATE org_companies SET name = ? WHERE id = ? AND is_active = 1' in actions
    assert 'هیچ نقش، مجوز یا عضویت کاربر را تغییر نمی‌دهد' in index
    action = actions[actions.index("if ($action === 'save_org_company_names')") : actions.index('// ───── گروه اکشن: نگهداری لاگ‌ها', actions.index("if ($action === 'save_org_company_names')"))]
    assert 'UPDATE users' not in action and 'role =' not in action
    assert 'org_company_display_name' in helpers

def test_manager_positions_map_to_existing_global_manager_role_without_admin_promotion():
    helpers = read('organization.php')
    assert "return ['ceo', 'company_manager', 'deputy', 'department'];" in helpers
    assert "role = 'manager'" in helpers
    assert 'function org_expert_user_ids()' in helpers
    assert "role = 'user' WHERE id IN ($expertPlaceholders)" in helpers
    assert "'primary_admin'" in helpers and "'supervisor'" in helpers
    assert "role = 'primary_admin'" not in helpers

def test_backup_restore_and_reset_are_scoped_to_one_company():
    actions = read('actions/org-actions.php')
    js = read('assets/organization.js')
    assert "array_key_exists('companyId', $backup) ? (int) $backup['companyId'] : 1" in actions
    assert '$backupCompanyId !== $orgCompanyId' in actions
    assert 'org_company_member_user_ids($orgCompanyId)' in actions
    assert 'UPDATE users SET org_unit_id = NULL, manager_user_id = NULL WHERE id IN (' in actions
    assert 'WHERE id = ? AND COALESCE(auth_source' in actions
    assert 'WHERE company_id = ? AND unit_type <> ?' in actions
    assert 'WHERE company_id = ? AND id <> ?' in actions
    assert 'companyId: Number(data.companyId || 1)' in js
    assert "post_value('org_unit_id', '0')" in actions
    assert 'company_id = ? AND unit_type = "liaison"' in actions

def test_shared_service_department_model_is_not_company_partitioned_by_migration():
    migration = read('migrations/018-org-company-separation.sql')
    assert 'ALTER TABLE departments' not in migration
    assert 'ALTER TABLE handling_units' not in migration
    assert 'ALTER TABLE service_catalog' not in migration

if __name__ == '__main__':
    tests = [v for k, v in globals().items() if k.startswith('test_')]
    for test in tests:
        test()
        print('ok -', test.__name__)
    print(f'{len(tests)} company-separation static checks passed')
