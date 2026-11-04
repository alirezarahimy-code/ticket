from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
exporter = (ROOT / 'cron/export_logging_integrity.php').read_text(encoding='utf-8')
verifier = (ROOT / 'cron/verify_logging_integrity.php').read_text(encoding='utf-8')
integrity = (ROOT / 'lib/logging-integrity.php').read_text(encoding='utf-8')

def test_integrity_export_and_verification_contracts():
    assert 'outside the application root' in exporter
    assert "hash('sha256', $previous . \"\\n\" . $canonical)" in integrity
    assert "hash_hmac('sha256'" in integrity
    assert 'immutable daily snapshot' in exporter
    assert 'late-arrival supplement' in exporter
    assert '--reconcile-only' in exporter
    assert 'logging_integrity_row_is_late' in exporter
    assert 'logging_integrity_verify_supplement_chain' in verifier
    assert 'hash_equals' in integrity
    assert all(x in exporter for x in ['ticket_events', 'log_issue_history', 'log_issue_notifications', 'log_issue_events', 'log_issues'])

def test_integrity_supplement_is_linked_to_base_and_ordered():
    assert "'format' => 3" in exporter
    assert 'base_manifest_hmac' in integrity
    assert 'supplement_sequence' in integrity
    assert 'prev_supplement_hmac' in integrity
    assert 'max_ids' in integrity and 'mutable_hashes' in integrity


def test_integrity_export_scheduled_job_reconciles_seven_previous_days():
    batch = (ROOT / 'cron/run_logging_integrity_export.bat').read_text(encoding='utf-8')
    assert 'AddDays(-$i)' in batch
    assert '$i -le 7' in batch
    assert '--reconcile-only' in batch
