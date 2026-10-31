"""The corrected archive must preserve the shipped payload and match its manifest."""
import hashlib
import json
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DOWNLOADS = ROOT / 'downloads'
OLD = DOWNLOADS / 'ticket-update-1.62.59-cumulative-org-card-drag-20261031.zip'
NEW = DOWNLOADS / 'ticket-update-1.62.59-version-integrity-20261101.zip'
UPDATE = ROOT / 'updates/2026-11-01-release-162-59-version-integrity'
REPORT = 'CHANGE-REPORT-FA-1.62.59-VERSION-INTEGRITY.md'


def test_corrected_package_contract_and_checksum():
    checksum, filename = NEW.with_suffix('.zip.sha256').read_text().split()
    assert filename == NEW.name
    assert checksum == hashlib.sha256(NEW.read_bytes()).hexdigest()
    with zipfile.ZipFile(NEW) as archive, zipfile.ZipFile(OLD) as original:
        assert archive.testzip() is None
        manifest = json.loads(archive.read('manifest.json'))
        old_manifest = json.loads(original.read('manifest.json'))
        assert manifest == json.loads((UPDATE / 'manifest.json').read_text())
        assert manifest['version'] == '1.62.59'
        assert manifest['requires_version'] == '1.62.58'
        assert manifest['migrations'] == []
        assert (UPDATE / 'migrations').is_dir()
        assert archive.read('files/VERSION') == (ROOT / 'VERSION').read_bytes() == b'1.62.59\n'
        assert manifest['files'] == old_manifest['files'] + [REPORT]
        names = archive.namelist()
        assert len(names) == len(set(names))
        assert set(names) == {'manifest.json'} | {'files/' + f for f in manifest['files']}
        for name in manifest['files']:
            assert not name.startswith('/') and '..' not in name and '\\' not in name
            assert name.split('/')[0] not in {'config.php', '.env', 'storage', 'uploads'}
            target = ROOT / name if (ROOT / name).is_file() else DOWNLOADS / name
            assert archive.read('files/' + name) == target.read_bytes()
            if name not in {'VERSION', REPORT}:
                assert archive.read('files/' + name) == original.read('files/' + name)
        assert archive.read('files/' + REPORT) == (UPDATE / 'CHANGE-REPORT-FA.md').read_bytes()


def test_verifier_is_cli_only_and_read_only():
    source = (DOWNLOADS / 'verify-package-1.62.59.php').read_text()
    assert "PHP_SAPI !== 'cli'" in source
    assert "hash_file('sha256', $file)" in source
    assert "exit($failed ? 1 : 0)" in source
    assert not re.search(r'\b(?:unlink|copy|file_put_contents|fwrite\s*\(\s*\$file)\s*\(', source)
