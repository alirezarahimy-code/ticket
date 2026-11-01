"""Guard the cumulative update's contents and truthful version metadata."""
import hashlib
import json
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PACKAGE = ROOT / 'downloads/ticket-update-1.62.60-workflow-safety-20261101.zip'
PREVIOUS = ROOT / 'downloads/ticket-update-1.62.59-cumulative-org-card-drag-20261031.zip'


class ReleasePackageTest(unittest.TestCase):
    def test_manifest_cumulative_files_and_hash(self):
        with zipfile.ZipFile(PACKAGE) as archive, zipfile.ZipFile(PREVIOUS) as previous:
            manifest = json.loads(archive.read('manifest.json'))
            old = json.loads(previous.read('manifest.json'))
            self.assertEqual(manifest['version'], '1.62.60')
            self.assertEqual(manifest['requires_version'], '1.62.58')
            self.assertEqual(manifest['migrations'], [])
            self.assertEqual(archive.read('files/VERSION').decode().strip(), manifest['version'])
            names = archive.namelist()
            self.assertEqual(len(names), len(set(names)))
            self.assertEqual(set(names), {'manifest.json'} | {'files/' + p for p in manifest['files']})
            self.assertTrue({p for p in old['files'] if not p.startswith('CHANGE-REPORT-')} <= set(manifest['files']))
            for rel in ('actions/ticket-actions.php', 'actions/supervisor-actions.php',
                        'actions/bulk-actions.php', 'lib/auth-scope.php', 'lib/notify-users.php',
                        'lib/tickets-sla.php', 'cron/ticket_auto_close.php', 'index.php', 'help-guide.php'):
                self.assertIn(rel, manifest['files'])
            for rel in manifest['files']:
                target = ROOT / ('downloads/' + rel if rel.startswith('CHANGE-REPORT-') else rel)
                self.assertEqual(archive.read('files/' + rel), target.read_bytes(), rel)
        self.assertEqual(hashlib.sha256(PACKAGE.read_bytes()).hexdigest(),
                         (PACKAGE.parent / (PACKAGE.name + '.sha256')).read_text().split()[0])


if __name__ == '__main__':
    unittest.main()
