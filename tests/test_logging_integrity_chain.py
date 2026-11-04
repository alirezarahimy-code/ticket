import hashlib
import hmac
import json
import os
import shutil
import subprocess
from pathlib import Path

import pytest

# این آزمون به مفسر PHP نیاز دارد؛ روی ماشین‌هایی که PHP CLI ندارند رد می‌شود.
pytestmark = pytest.mark.skipif(shutil.which('php') is None, reason='PHP CLI در دسترس نیست')

ROOT = Path(__file__).resolve().parents[1]
VERIFY = ROOT / 'cron/verify_logging_integrity.php'

def _write_snapshot(folder: Path, date: str, previous_date, previous_hmac: str, key: bytes):
    key_id = hashlib.sha256(key).hexdigest()[:16]
    record = {'date': date, 'source': 'test', 'id': 1}
    canonical = json.dumps(record, ensure_ascii=False, separators=(',', ':')).encode()
    record_hash = hashlib.sha256(('0' * 64 + '\n').encode() + canonical).hexdigest()
    archive = folder / f'logging-integrity-{date}.jsonl'
    archive.write_text(json.dumps({'previous_hash': '0' * 64, 'record_hash': record_hash, 'record': record}, separators=(',', ':')) + '\n', encoding='utf-8')
    signature = hmac.new(key, f'{date}|1|{record_hash}|{previous_hmac}|{key_id}'.encode(), hashlib.sha256).hexdigest()
    manifest = {
        'format': 2, 'date': date, 'records': 1, 'final_hash': record_hash,
        'prev_export_date': previous_date, 'prev_export_hmac': previous_hmac,
        'key_id': key_id, 'hmac_sha256': signature,
    }
    Path(str(archive) + '.manifest.json').write_text(json.dumps(manifest), encoding='utf-8')
    return archive, signature

def _verify(archive: Path, key: bytes):
    env = dict(os.environ, LOGGING_INTEGRITY_KEY=key.decode())
    return subprocess.run(['php', str(VERIFY), str(archive)], env=env, capture_output=True, text=True)

def test_consecutive_daily_exports_verify_as_a_chain(tmp_path):
    key = b'K' * 32
    _, first_hmac = _write_snapshot(tmp_path, '2026-11-02', None, '0' * 64, key)
    second, _ = _write_snapshot(tmp_path, '2026-11-03', '2026-11-02', first_hmac, key)
    result = _verify(second, key)
    assert result.returncode == 0, result.stderr + result.stdout
    assert 'daily chain and HMAC are valid' in result.stdout

def test_missing_previous_day_is_rejected(tmp_path):
    key = b'K' * 32
    # The manifest claims a genesis snapshot while an older archive is present.
    _write_snapshot(tmp_path, '2026-11-01', None, '0' * 64, key)
    later, _ = _write_snapshot(tmp_path, '2026-11-03', None, '0' * 64, key)
    result = _verify(later, key)
    assert result.returncode != 0
    assert 'chain gap detected' in result.stderr

def test_tampered_intermediate_archive_breaks_the_entire_chain(tmp_path):
    key = b'K' * 32
    first, first_hmac = _write_snapshot(tmp_path, '2026-11-01', None, '0' * 64, key)
    second, second_hmac = _write_snapshot(tmp_path, '2026-11-02', '2026-11-01', first_hmac, key)
    third, _ = _write_snapshot(tmp_path, '2026-11-03', '2026-11-02', second_hmac, key)
    second.write_text(second.read_text(encoding='utf-8').replace('"id":1', '"id":2'), encoding='utf-8')
    result = _verify(third, key)
    assert result.returncode != 0
    assert 'Previous archive record chain or final hash is invalid' in result.stderr

def test_legacy_format_one_archive_remains_verifiable(tmp_path):
    key = b'K' * 32
    date = '2026-11-01'
    record = {'date': date, 'source': 'legacy', 'id': 1}
    canonical = json.dumps(record, ensure_ascii=False, separators=(',', ':')).encode()
    final_hash = hashlib.sha256(('0' * 64 + '\n').encode() + canonical).hexdigest()
    archive = tmp_path / f'logging-integrity-{date}.jsonl'
    archive.write_text(json.dumps({'previous_hash': '0' * 64, 'record_hash': final_hash, 'record': record}, separators=(',', ':')) + '\n', encoding='utf-8')
    signature = hmac.new(key, f'{date}|1|{final_hash}'.encode(), hashlib.sha256).hexdigest()
    manifest = {'format': 1, 'date': date, 'records': 1, 'final_hash': final_hash, 'hmac_sha256': signature}
    Path(str(archive) + '.manifest.json').write_text(json.dumps(manifest), encoding='utf-8')
    result = _verify(archive, key)
    assert result.returncode == 0, result.stderr + result.stdout
    assert 'format 1' in result.stdout

def _write_supplement(folder: Path, date: str, base_manifest: dict, sequence: int, previous_hmac: str, records: list[dict], key: bytes):
    key_id = hashlib.sha256(key).hexdigest()[:16]
    previous = '0' * 64
    lines = []
    for record in records:
        canonical = json.dumps(record, ensure_ascii=False, separators=(',', ':')).encode()
        record_hash = hashlib.sha256((previous + '\n').encode() + canonical).hexdigest()
        lines.append(json.dumps({'previous_hash': previous, 'record_hash': record_hash, 'record': record}, ensure_ascii=False, separators=(',', ':')))
        previous = record_hash
    archive = folder / f'logging-integrity-{date}.late-{sequence:06d}-20261104T000000Z-test.jsonl'
    archive.write_text('\n'.join(lines) + '\n', encoding='utf-8')
    base_hmac = base_manifest['hmac_sha256']
    base_final = base_manifest['final_hash']
    signature_input = f'{date}|{len(records)}|{previous}|{base_hmac}|{base_final}|{sequence}|{previous_hmac}|{key_id}'
    signature = hmac.new(key, signature_input.encode(), hashlib.sha256).hexdigest()
    manifest = {
        'format': 3, 'date': date, 'records': len(records), 'final_hash': previous,
        'base_manifest_hmac': base_hmac, 'base_final_hash': base_final,
        'supplement_sequence': sequence, 'prev_supplement_hmac': previous_hmac,
        'key_id': key_id, 'hmac_sha256': signature,
    }
    Path(str(archive) + '.manifest.json').write_text(json.dumps(manifest), encoding='utf-8')
    return archive, signature


def test_late_arrival_supplements_verify_against_immutable_base_and_sequence(tmp_path):
    key = b'L' * 32
    base, _ = _write_snapshot(tmp_path, '2026-11-04', None, '0' * 64, key)
    base_manifest = json.loads(Path(str(base) + '.manifest.json').read_text(encoding='utf-8'))
    first, first_hmac = _write_supplement(tmp_path, '2026-11-04', base_manifest, 1, '0' * 64, [{'source': 'system', 'id': 91}], key)
    second, _ = _write_supplement(tmp_path, '2026-11-04', base_manifest, 2, first_hmac, [{'source': 'activity', 'id': 22}], key)
    result = _verify(second, key)
    assert result.returncode == 0, result.stderr + result.stdout
    assert 'late-arrival records verified' in result.stdout
    assert _verify(first, key).returncode == 0


def test_late_arrival_supplement_detects_missing_predecessor(tmp_path):
    key = b'M' * 32
    base, _ = _write_snapshot(tmp_path, '2026-11-04', None, '0' * 64, key)
    base_manifest = json.loads(Path(str(base) + '.manifest.json').read_text(encoding='utf-8'))
    first, first_hmac = _write_supplement(tmp_path, '2026-11-04', base_manifest, 1, '0' * 64, [{'source': 'system', 'id': 92}], key)
    second, _ = _write_supplement(tmp_path, '2026-11-04', base_manifest, 2, first_hmac, [{'source': 'system', 'id': 93}], key)
    first.unlink()
    Path(str(first) + '.manifest.json').unlink()
    result = _verify(second, key)
    assert result.returncode != 0
    assert 'supplement chain metadata' in result.stderr


def test_late_arrival_supplement_detects_tampering(tmp_path):
    key = b'N' * 32
    base, _ = _write_snapshot(tmp_path, '2026-11-04', None, '0' * 64, key)
    base_manifest = json.loads(Path(str(base) + '.manifest.json').read_text(encoding='utf-8'))
    supplement, _ = _write_supplement(tmp_path, '2026-11-04', base_manifest, 1, '0' * 64, [{'source': 'system', 'id': 94}], key)
    supplement.write_text(supplement.read_text(encoding='utf-8').replace('"id":94', '"id":95'), encoding='utf-8')
    result = _verify(supplement, key)
    assert result.returncode != 0
    assert 'record chain' in result.stderr
