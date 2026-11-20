#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""لایهٔ نمایشی اعداد فارسی (lib/fa-digits.php) — آزمون رفتاریِ خروجیِ تابع ناب.

سند هدف: متن‌های قابل نمایش فارسی شوند و داده‌های ماشینی (اتربیوت‌ها، فرم‌ها،
اسکریپت‌ها، IP/ایمیل) و پاسخ‌های غیر HTML دست‌نخورده بمانند.
"""
import os
import shutil
import subprocess
import sys
from pathlib import Path

import pytest

PHP = shutil.which('php')
pytestmark = pytest.mark.skipif(PHP is None, reason='PHP CLI در دسترس نیست')

ROOT = Path(__file__).resolve().parent.parent
FA_DIGITS_PHP = ROOT / 'lib' / 'fa-digits.php'


def convert(html: str) -> str:
    assert PHP is not None
    proc = subprocess.run(
        [PHP, '-r', f'require $argv[1]; echo fa_digits_html(stream_get_contents(STDIN));', str(FA_DIGITS_PHP)],
        input=html.encode('utf-8'),
        capture_output=True,
        timeout=30,
    )
    assert proc.returncode == 0, proc.stderr.decode('utf-8', errors='replace')
    return proc.stdout.decode('utf-8')


def test_helper_exists_and_registered():
    source = FA_DIGITS_PHP.read_text(encoding='utf-8')
    for marker in ('function fa_digits_html(', 'function fa_digits_text(', 'function fa_digits_output_callback(',
                   'function fa_digits_normalize_digits(', 'function fa_digits_normalize_deep('):
        assert marker in source, marker
    bootstrap = (ROOT / 'bootstrap.php').read_text(encoding='utf-8')
    assert "require_once __DIR__ . '/lib/fa-digits.php'" in bootstrap
    assert "ob_start('fa_digits_output_callback')" in bootstrap
    assert "fa_digits_normalize_deep($_GET)" in bootstrap
    assert "fa_digits_normalize_deep($_POST)" in bootstrap
    # راه برگشت اضطراری
    assert "cfg('display.fa_digits', true)" in bootstrap


def test_text_nodes_convert_to_persian_digits():
    out = convert('<h1>مرکز خدمات 1405/08/22 — ساعت 14:30</h1>')
    assert '۱۴۰۵/۰۸/۲۲' in out
    assert '۱۴:۳۰' in out
    assert '1405' not in out


def test_attributes_and_form_values_stay_latin():
    out = convert('<a href="/t/123">تیکت 123</a><input type="text" value="09123456789"><input type="hidden" name="csrf" value="a1b2c3d4">')
    assert 'href="/t/123"' in out
    assert 'value="09123456789"' in out
    assert 'value="a1b2c3d4"' in out
    assert '>تیکت ۱۲۳<' in out


def test_script_style_pre_code_and_comment_stay_latin_but_textarea_converts():
    # ۱٫۶۳٫۳ — متن تایپی کاربر (textarea) باید ارقام نمایشی فارسی داشته باشد؛
    # دادهٔ canonical در پایگاه‌داده لاتین می‌ماند (fa_digits_normalize_deep).
    out = convert(
        '<script>var answer = 42; var s = "500";</script>'
        '<style>.x { margin: 10px; }</style>'
        '<pre>خط 1: 111</pre>'
        '<code>$n = 98765;</code>'
        '<textarea>عدد 123</textarea>'
        '<!-- نسخهٔ 1.62.95 -->'
    )
    assert 'var answer = 42;' in out
    assert '"500"' in out
    assert 'margin: 10px;' in out
    assert 'خط 1: 111' in out
    assert '$n = 98765;' in out
    assert 'عدد ۱۲۳' in out
    assert 'نسخهٔ 1.62.95' in out


def test_machine_tokens_stay_latin_inside_text():
    out = convert('<p>IP: 10.10.0.102 و ایمیل: support@example.com — شمارهٔ 123</p>')
    assert '10.10.0.102' in out
    assert 'support@example.com' in out
    assert 'شمارهٔ ۱۲۳' in out


def test_mixed_tag_heavy_page_keeps_layout_valid():
    src = '<div class="n" style="width:120px"><strong>۱۷ عدد</strong> و <em>1405/08/30</em></div>'
    out = convert(src)
    assert 'style="width:120px"' in out
    assert '<strong>۱۷ عدد</strong>' in out
    assert '۱۴۰۵/۰۸/۳۰' in out


def test_normalize_digits_roundtrip():
    proc = subprocess.run(
        [PHP, '-r', 'require $argv[1]; echo fa_digits_normalize_digits($argv[2]);', str(FA_DIGITS_PHP),
         '۱۴۰۵/۰۸/۲۲ و ١٢٣ و SUP-۰۰۰۰۹'],
        capture_output=True, timeout=30,
    )
    assert proc.returncode == 0
    assert proc.stdout.decode() == '1405/08/22 و 123 و SUP-00009'


def test_normalize_deep_skips_sensitive_keys():
    proc = subprocess.run(
        [PHP, '-r', 'require $argv[1]; echo json_encode(fa_digits_normalize_deep(json_decode($argv[2], true)), JSON_UNESCAPED_UNICODE);', str(FA_DIGITS_PHP),
         '{"password":"رمز۱۲۳","q":"SUP-۰۰۰۰۹","nested":{"csrf":"ا۱ب۲","n":"۴۵۶"}}'],
        capture_output=True, timeout=30,
    )
    assert proc.returncode == 0
    import json as _json
    data = _json.loads(proc.stdout.decode())
    assert data['password'] == 'رمز۱۲۳'
    assert data['q'] == 'SUP-00009'
    assert data['nested']['csrf'] == 'ا۱ب۲'
    assert data['nested']['n'] == '456'


def test_global_input_converter_shipped_and_included():
    render = (ROOT / 'lib' / 'render.php').read_text(encoding='utf-8')
    assert 'assets/fa-digits.js?v=1' in render
    root_js = (ROOT / 'assets' / 'fa-digits.js').read_bytes()
    panel_js = (ROOT / 'food-ticket-web' / 'assets' / 'fa-digits.js').read_bytes()
    assert root_js == panel_js, 'هر دو نسخهٔ assets/fa-digits.js باید بایت‌به‌بایت یکی باشند'
    import_js = (ROOT / 'food-order-import.php').read_text(encoding='utf-8')
    assert 'assets/fa-digits.js?v=1' in import_js
    panel = (ROOT / 'food-ticket-web' / 'index.html').read_text(encoding='utf-8')
    assert 'assets/fa-digits.js?v=1' in panel


def test_ticket_codes_convert_as_farsi_display():
    out = convert('<td>SUP-00009</td>')
    assert 'SUP-۰۰۰۰۹' in out


def main() -> int:
    tests = [func for name, func in sorted(globals().items()) if name.startswith('test_')]
    if PHP is None:
        print('SKIP: PHP CLI در دسترس نیست')
        return 0
    failed = 0
    for func in tests:
        try:
            func()
            print(f'ok - {func.__name__}')
        except AssertionError as exc:
            print(f'FAIL - {func.__name__}: {exc}')
            failed += 1
    if failed:
        print(f'\n{failed} fa-digits check(s) failed')
        return 1
    print(f'\nALL-FA-DIGITS-OUTPUT-TESTS-PASSED ({len(tests)})')
    return 0


if __name__ == '__main__':
    sys.exit(main())
