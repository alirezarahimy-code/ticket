from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path):
    return (ROOT / path).read_text(encoding='utf-8')


def test_required_audit_derives_module_and_target_context():
    audit = read('lib/activity-audit.php')
    assert 'function activity_audit_context(' in audit
    assert "'module' => $context['module']" in audit
    assert "'target_type' => $context['target_type']" in audit
    assert "'target_label' => $context['target_label']" in audit
    assert "'change_number'" in audit and "'problem_number'" in audit


def test_holiday_mutations_are_audited_in_the_same_transaction():
    source = read('actions/holiday-actions.php')
    for action in ('holiday_created', 'holiday_activated', 'holiday_deactivated', 'holiday_deleted', 'holidays_imported'):
        assert f"'{action}'" in source
    assert source.count('save_audit_required(') >= 5
    assert 'FOR UPDATE' in source
    assert 'db()->rollBack()' in source
    assert source.count("db_commit('index.php');") == 4
    assert 'db()->commit()' not in source


def test_cddvd_governance_ldap_and_profile_events_have_explicit_classification():
    core = read('lib/logging-core.php')
    for action in ('cd_dvd_in', 'cd_dvd_out', 'cd_dvd_updated', 'cd_dvd_deleted',
                   'change_cab_approved', 'change_cab_rejected', 'change_transition',
                   'problem_created', 'ldap_bulk_sync', 'ldap_diagnosed',
                   'profile_updated', 'user_theme_changed'):
        assert f"'{action}' =>" in core


def test_metadata_allowlists_preserve_operational_context_without_disabling_redaction():
    core = read('lib/logging-core.php')
    for action in ('cd_dvd_updated', 'change_cab_rejected', 'ticket_updated', 'holiday_created'):
        assert f"'{action}' =>" in core
    assert "if (!isset($allowed[$normalizedKey]) || logging_sensitive_key((string) $key)) continue;" in core


def test_admin_category_no_longer_uses_null_module_as_wildcard():
    source = read('index.php')
    block = source[source.index("} elseif ($category === 'admin') {"):source.index("} elseif ($category === 'errors') {")]
    assert "l.category = 'audit'" in block
    assert 'l.module IS NULL' not in block


def test_ticket_timeline_supports_search_and_safe_details():
    source = read('index.php')
    assert "$_GET['event_q']" in source
    assert 'CAST(e.details AS CHAR) LIKE ?' in source
    assert 'activity_log_meta_lines(json_encode($visibleDetails' in source
    assert "if ($staff) $safeKeys" in source
    assert '$eventPageSize = 100;' in source and '$eventOffset = ($eventPage - 1) * $eventPageSize;' in source
    assert 'eventPages > 1' in source


def test_circuit_open_does_not_spool_success_audit_outside_business_transaction():
    core = read('lib/logging-core.php')
    block = core[core.index('if (logging_db_circuit_should_skip()) {', core.index('function logging_store_activity')):]
    assert 'Successful audit cannot be spooled independently' in block
    assert "(string) ($event['result'] ?? 'unknown') === 'success'" in block


def test_new_event_codes_have_catalog_definitions():
    catalog = read('lib/log-catalog.php')
    for code in ('AUD-ACL-002', 'AUD-USER-001', 'AUD-GOV-001', 'AUD-GOV-002', 'AUD-MEDIA-001', 'AUD-CAL-001'):
        assert f"'{code}' =>" in catalog
