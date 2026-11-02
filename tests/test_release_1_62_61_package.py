"""Verify the cumulative prerelease is exactly 1.62.60 plus the guest fix."""
import hashlib
import json
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OLD = ROOT / 'downloads/ticket-update-1.62.60-workflow-safety-20261101.zip'
NEW = ROOT / 'downloads/ticket-update-1.62.61-guest-personal-scope-20261102.zip'


def test_cumulative_guest_fix_and_immutable_baseline():
    expected_changes = {'files/VERSION', 'files/food-order.php', 'files/help-guide.php', 'files/assets/food-order-guest.js'}
    expected_additions = {'files/assets/food-order.css', 'files/food-ticket-web/assets/food-order.css', 'files/CHANGE-REPORT-FA-1.62.61.md'}
    assert hashlib.sha256(OLD.read_bytes()).hexdigest() == '2bdfeb2599f22899797c1988ea5c82630412e410083685bac68b4b9516df1f29'
    assert hashlib.sha256(NEW.read_bytes()).hexdigest() == (NEW.parent / (NEW.name + '.sha256')).read_text().split()[0]
    with zipfile.ZipFile(OLD) as old, zipfile.ZipFile(NEW) as new:
        assert new.testzip() is None
        manifest = json.loads(new.read('manifest.json'))
        assert manifest['version'] == '1.62.61' and manifest['requires_version'] == '1.62.58'
        assert manifest['migrations'] == [] and new.read('files/VERSION') == b'1.62.61\n'
        before = {p for p in old.namelist() if p.startswith('files/')}
        after = {p for p in new.namelist() if p.startswith('files/')}
        assert after - before == expected_additions and before <= after
        assert {p for p in before if old.read(p) != new.read(p)} == expected_changes
        assert after == {'files/' + p for p in manifest['files']}
        assert len(new.namelist()) == len(set(new.namelist()))
        assert new.read('files/assets/food-order.css') == new.read('files/food-ticket-web/assets/food-order.css')
