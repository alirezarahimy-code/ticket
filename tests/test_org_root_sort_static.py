"""Mixed root-card ordering must not change parentage or cross companies."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_root_sort_includes_management_and_company_and_rechecks_locked_units():
    actions = (ROOT / 'actions/org-actions.php').read_text(encoding='utf-8')
    js = (ROOT / 'assets/organization.js').read_text(encoding='utf-8')
    segment = actions.split("if ($action === 'sort_org_root_units')", 1)[1].split("if ($action === 'sort_org_deputies')", 1)[0]
    assert 'require_permission(\'org.manage\')' in segment
    assert 'company_id = ?' in segment and 'parent_id = ?' in segment
    assert 'unit_type IN ("deputy", "management")' in segment and 'FOR UPDATE' in segment
    assert 'sort($sortedIds)' in segment and 'sort($expected)' in segment
    assert 'SET sort_order = ?' in segment and 'SET parent_id' not in segment
    assert "submit('sort_org_root_units', { unit_ids: current })" in js
    assert 'var sortHandle = data.canManage && data.sortEnabled' in js
    assert 'row.rects[cardIndex].top' in js
    assert actions.count("'sort_org_root_units'") >= 3
