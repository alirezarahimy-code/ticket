from pathlib import Path
import hashlib
import json
import unittest
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]


class CdDvdReusableSerialAndHistoryTests(unittest.TestCase):
    def test_outgoing_serial_is_not_replaced_while_user_is_typing(self):
        script = (ROOT / "assets/cd-dvd.js").read_text(encoding="utf-8")
        self.assertIn("let serialFormInitialized = false;", script)
        self.assertIn("mediaChanged || (!serialFormInitialized && String(serial.value || '').trim() === '')", script)
        self.assertIn("const rangeIsValid = numeric !== '' && Number(numeric) >= bounds.min && Number(numeric) <= bounds.max;", script)
        self.assertIn("serialFull.value = rangeIsValid ? 'P86-' + numeric.padStart(4, '0') : '';", script)
        # Direction transitions remain protected server-side: OUT is only valid after IN.
        source = (ROOT / "cd-dvd.php").read_text(encoding="utf-8")
        self.assertIn("if ($last && $direction === (string) $last['direction'])", source)
        self.assertIn("'این شماره از سازمان خارج شده است.'", source)

    def test_history_has_readable_separator_before_receiver_label(self):
        source = (ROOT / "cd-dvd.php").read_text(encoding="utf-8")
        self.assertIn('<span class="cd-dvd-history-separator" aria-hidden="true"> - </span><span>تحویل‌گیرنده:', source)

    def test_both_cd_dvd_script_references_use_current_cache_version(self):
        source = (ROOT / "cd-dvd.php").read_text(encoding="utf-8")
        self.assertEqual(source.count('assets/cd-dvd.js?v=3.4.13'), 2)

    def test_r5_package_and_sidecar_are_consistent(self):
        package = ROOT / "downloads/ticket-1.63.4-r5-cd-dvd-fixes-20261119.zip"
        self.assertTrue(package.is_file())
        with ZipFile(package) as archive:
            self.assertIsNone(archive.testzip())
            manifest = json.loads(archive.read("manifest.json"))
            self.assertEqual(manifest["version"], "1.63.4")
            self.assertEqual(manifest["migrations"], [])
            self.assertEqual(manifest["release_status"], "RELEASE")
            self.assertEqual(set(manifest["sha256"]), set(manifest["files"]))
            actual = {name.removeprefix("files/") for name in archive.namelist()
                      if name.startswith("files/") and not name.endswith("/")}
            self.assertEqual(actual, set(manifest["files"]))
            for relative in manifest["files"]:
                self.assertEqual(hashlib.sha256(archive.read("files/" + relative)).hexdigest(),
                                 manifest["sha256"][relative])
            self.assertEqual(archive.read("files/cd-dvd.php"), (ROOT / "cd-dvd.php").read_bytes())
            self.assertEqual(archive.read("files/assets/cd-dvd.js"), (ROOT / "assets/cd-dvd.js").read_bytes())
        checksum = hashlib.sha256(package.read_bytes()).hexdigest()
        self.assertIn(checksum, package.with_name(package.name + ".sha256").read_text(encoding="ascii"))


if __name__ == "__main__":
    unittest.main()
