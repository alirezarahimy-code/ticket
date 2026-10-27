from __future__ import annotations

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OLD_NAME = "ticket-update-1.62.37-central-food-reports-20261027.zip"


class Revoked16237PackageTests(unittest.TestCase):
    def test_previous_wrong_location_package_is_revoked_from_current_downloads(self):
        page = (ROOT / "downloads" / f"{OLD_NAME[:-4]}.html").read_text(encoding="utf-8")
        self.assertFalse((ROOT / "downloads" / OLD_NAME).exists())
        self.assertFalse((ROOT / "downloads" / f"{OLD_NAME}.sha256").exists())
        self.assertIn("این بسته لغو شده است", page)
        self.assertIn("1.62.38-food-reports-inside-food-manager", page)


if __name__ == "__main__":
    unittest.main()
