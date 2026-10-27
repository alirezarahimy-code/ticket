"""جلوگیری از تکرار تجربهٔ ۱.۶۲.۳۷–۱.۶۲.۴۰: تعریف مجدد تابع در فایلهای PHP.

هر تابعی که دوبار در یک فایل تعریف شود، PHP 8 همان لحظه Fatal error میدهد و کل
سامانه ۵۰۰ میشود. این تست بهصورت static تمام فایلهای PHP ریشهٔ پروژه را اسکن
میکند و در صورت مشاهدهٔ هرگونه تعریف تکراری شکست میخورد.

همچنین بررسی میکند که فایلهای حساس هویت/شناسنامه محافظ لازم را داشته باشند تا
در صورت لود دوباره (require ساده بهجای require_once) باز هم Fatal رخ ندهد.
"""
from __future__ import annotations

import collections
import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SKIP_DIRS = {
    '.git', 'node_modules', 'vendor', 'storage', 'uploads', 'downloads',
    'updates', 'preview', '.cache', '__pycache__', 'tools', 'tests',
}
FUNCTION_RE = re.compile(r'\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(')


class NoDuplicatePhpFunctionsTest(unittest.TestCase):
    def test_no_duplicate_function_declarations(self) -> None:
        problems: list[str] = []
        for path in ROOT.rglob('*.php'):
            if any(part in SKIP_DIRS for part in path.relative_to(ROOT).parts):
                continue
            source = path.read_text(encoding='utf-8', errors='ignore')
            names = FUNCTION_RE.findall(source)
            duplicates = [
                f'{name} ({count} بار)'
                for name, count in collections.Counter(names).items()
                if count > 1
            ]
            if duplicates:
                problems.append(f'{path.relative_to(ROOT)}: ' + ', '.join(duplicates))
        self.assertEqual([], problems, 'تعریف مجدد تابع باعث Fatal error و ۵۰۰ میشود')

    def test_identity_file_has_redeclare_guard(self) -> None:
        names = ROOT / 'asset-names.php'
        self.assertTrue(names.is_file(), 'asset-names.php وجود ندارد')
        source = names.read_text(encoding='utf-8')
        self.assertIn(
            "if (!function_exists('asset_identity_key'))", source,
            'محافظ تعریف مجدد توابع هویت حذف شده است',
        )

    def test_asset_profile_has_reload_guard(self) -> None:
        profile = ROOT / 'asset-profile.php'
        self.assertTrue(profile.is_file(), 'asset-profile.php وجود ندارد')
        source = profile.read_text(encoding='utf-8')
        self.assertIn(
            "if (function_exists('asset_profile_column_sql'))", source,
            'محافظ لود دوبارهٔ asset-profile.php حذف شده است',
        )

    def test_index_uses_require_once_for_sensitive_modules(self) -> None:
        index = ROOT / 'index.php'
        self.assertTrue(index.is_file(), 'index.php وجود ندارد')
        source = index.read_text(encoding='utf-8')
        for module in ['asset-names.php', 'asset-profile.php']:
            self.assertIn(
                f"require_once __DIR__ . '/{module}';", source,
                f'بارگذاری {module} باید require_once باشد',
            )


if __name__ == '__main__':
    unittest.main()
