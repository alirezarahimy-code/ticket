#!/usr/bin/env python3
"""قفل قرارداد «شماره برگه خروج» الزامی در کنترل CD/DVD (وظیفهٔ مالک ۱۴۰۵).

- فرم زندهٔ ثبت رسانه: input فیلد exit_sheet باید required + data-required-label داشته باشد.
- در حالت IN (ورود) فیلد خروجی است؛ پس باید hidden + disabled/required برداشته شود
  تا اعتبارسنجی کلاینت آن را رد نکند و مرورگر آن را ارسال نکند.
- در حالت OUT: خالی بودن ⇒ خطای فارسی سمت سرور (حتی اگر کسی HTML را دور بزند).
- اسکریپت cd-dvd.js بعد از تغییر منطق updateForm باید cache-buster تازه داشته باشد.
"""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


class ExitSheetRequiredTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.source = (ROOT / 'cd-dvd.php').read_text(encoding='utf-8')
        cls.script = (ROOT / 'assets' / 'cd-dvd.js').read_text(encoding='utf-8')

    def _live_form_exit_sheet_input(self) -> str:
        # فرم زنده (cd_dvd_render_page) فیلد را با کلاس cd-dvd-exit-sheet-field رندر می‌کند.
        match = re.search(r'<label class="cd-dvd-exit-sheet-field".*?</label>', self.source, re.DOTALL)
        self.assertIsNotNone(match, 'فیلد برگه خروج در فرم زنده پیدا نشد.')
        return match.group(0)

    def test_live_form_exit_sheet_is_required(self):
        field = self._live_form_exit_sheet_input()
        self.assertIn('required', field)
        self.assertIn('data-required-label="شماره برگه خروج"', field)
        self.assertIn('pattern="[0-9۰-۹]*"', field)

    def test_live_form_exit_sheet_disabled_for_in_direction(self):
        field = self._live_form_exit_sheet_input()
        self.assertIn("'disabled'", field, 'در حالت IN ورودی باید غیرفعال رندر شود تا الزامی نباشد.')

    def test_server_rejects_empty_exit_sheet_on_out(self):
        window = self.source[self.source.index('function cd_dvd_validate_payload'):]
        match = re.search(r"if \(\$direction === 'OUT' && \$exitSheet === ''\) \{\s*throw new RuntimeException\('[^']*برگه خروج[^']*'\);", window)
        self.assertIsNotNone(match, 'اعتبارسنجی سرور برای برگه خروج خالی در خروج پیاده نشد.')

    def test_js_disables_and_toggles_required_with_direction(self):
        block = re.search(r'if \(exitSheetField\) \{.*?\}', self.script, re.DOTALL)
        self.assertIsNotNone(block)
        snippet = block.group(0)
        self.assertIn('exitSheetInput.disabled = isIncoming', snippet)
        self.assertIn('exitSheetInput.required = !isIncoming', snippet)

    def test_js_submit_handler_focuses_empty_required_field(self):
        self.assertIn('emptyField.focus();', self.script)
        self.assertIn("window.alert('فیلد «' + label + '» خالی است و باید پر شود.');", self.script)
        self.assertIn('event.preventDefault();', self.script)

    def test_live_renderer_uses_fresh_cache_buster(self):
        self.assertIn('assets/cd-dvd.js?v=3.4.12', self.source)


if __name__ == '__main__':
    unittest.main()
