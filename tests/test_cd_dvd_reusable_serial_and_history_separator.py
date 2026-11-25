from pathlib import Path
import unittest

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
        self.assertIn("function cd_dvd_normalize_input_serial(string $serial, string $media, string $direction): string", source)
        self.assertIn("return $query->fetchColumn() ? $canonical : $serial;", source)
        self.assertIn("'۰' => '0'", source)
        self.assertIn("'٩' => '9'", source)
        self.assertIn('id="cd-dvd-serial" name="serial"', source)
        self.assertIn('id="cd-dvd-serial-full" value=', source)
        self.assertNotIn('name="serial" id="cd-dvd-serial-full"', source)
        self.assertIn("elseif ($serial === '' && $excludeId === 0)", source)
        self.assertIn("Never infer a new serial during edit", source)
        self.assertIn("cd_dvd_acquire_write_lock($scope)", source)
        self.assertIn("updateForm();", script)

    def test_history_has_readable_separator_before_receiver_label(self):
        source = (ROOT / "cd-dvd.php").read_text(encoding="utf-8")
        self.assertIn('<span class="cd-dvd-history-separator" aria-hidden="true"> - </span><span>تحویل‌گیرنده:', source)

    def test_both_cd_dvd_script_references_use_current_cache_version(self):
        source = (ROOT / "cd-dvd.php").read_text(encoding="utf-8")
        self.assertEqual(source.count('assets/cd-dvd.js?v=3.4.15'), 2)


if __name__ == "__main__":
    unittest.main()
