from pathlib import Path
import json
import re
import unittest

ROOT = Path(__file__).resolve().parents[1]


class TriangleEffectReleaseTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.bootstrap = (ROOT / "bootstrap.php").read_text(encoding="utf-8")
        cls.index = (ROOT / "index.php").read_text(encoding="utf-8")
        cls.engine = (ROOT / "assets/triangle-mesh.js").read_text(encoding="utf-8")
        cls.settings = (ROOT / "assets/triangle-settings.js").read_text(encoding="utf-8")

    def test_catalog_has_37_valid_non_print_routes(self):
        match = re.search(
            r"function triangle_effect_page_catalog\(\): array\s*\{(.*?)\n\}",
            self.bootstrap,
            re.S,
        )
        self.assertIsNotNone(match)
        keys = re.findall(r"^\s*'([a-z][a-z0-9-]*)'\s*=>", match.group(1), re.M)
        self.assertEqual(len(keys), 37)
        self.assertEqual(len(set(keys)), 37)
        self.assertTrue({"login", "dashboard", "settings", "governance", "ola"}.issubset(keys))
        self.assertFalse(any("print" in key or "export" in key for key in keys))

    def test_configuration_keys_match_the_engine(self):
        spec_match = re.search(
            r"function triangle_effect_config_spec\(\): array\s*\{(.*?)\n\}",
            self.bootstrap,
            re.S,
        )
        defaults_match = re.search(r"var DEFAULTS = \{(.*?)\n    \};", self.engine, re.S)
        self.assertIsNotNone(spec_match)
        self.assertIsNotNone(defaults_match)
        numeric_keys = re.findall(r"^\s*'([A-Za-z][A-Za-z0-9]*)'\s*=>", spec_match.group(1), re.M)
        engine_keys = re.findall(r"^\s*([A-Za-z][A-Za-z0-9]*):", defaults_match.group(1), re.M)
        self.assertEqual(
            set(engine_keys),
            set(numeric_keys) | {"meshColor", "dotColor", "showDots", "maskVignette"},
        )
        for key in engine_keys:
            self.assertIn(key, self.index)

    def test_settings_are_saved_with_existing_permission_and_settings_store(self):
        self.assertIn("$action === 'save_triangle_effect'", self.index)
        self.assertIn("require_permission('settings.general_edit')", self.index)
        self.assertIn("save_setting('triangle_effect_pages'", self.index)
        self.assertIn("save_setting('triangle_effect_config'", self.index)
        self.assertIn("triangle_effect_enabled_for_current_page()", self.bootstrap)
        self.assertIn("data-config", self.bootstrap)

    def test_engine_respects_reduced_motion_and_has_explicit_lifecycle(self):
        self.assertIn("prefers-reduced-motion: reduce", self.engine)
        self.assertIn("function (nextConfig)", self.engine)
        self.assertIn("ripple: function ()", self.engine)
        self.assertIn("destroy: function ()", self.engine)
        self.assertIn("requestAnimationFrame", self.engine)
        for direct_page in ("ola.php", "governance.php"):
            self.assertIn("triangle_effect_script_tag()", (ROOT / direct_page).read_text(encoding="utf-8"))

    def test_interaction_controls_are_wired(self):
        for marker in (
            "data-tm-search",
            "data-tm-all",
            "data-tm-preset",
            "data-tm-ripple",
            "triangle_pages[]",
            "save_triangle_effect",
        ):
            self.assertIn(marker, self.index)
        self.assertIn("function filterPages()", self.settings)
        self.assertIn("preview.update(readConfig())", self.settings)

    def test_help_and_chatbot_cover_the_feature(self):
        guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")
        chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
        permissions = (ROOT / "permissions.php").read_text(encoding="utf-8")
        self.assertIn('id="triangle-effect"', guide)
        self.assertIn("افکت نمایشی", guide)
        self.assertIn("'افکت' => ['افکت نمایشی']", chatbot)
        self.assertIn("تنظیمات عمومی و افکت نمایشی", permissions)

    def test_release_manifest_is_migration_free(self):
        manifest_path = ROOT / "updates/2026-10-16-release-156/manifest.json"
        self.assertTrue(manifest_path.is_file())
        manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
        self.assertEqual(manifest["version"], "1.56")
        self.assertEqual(manifest["migrations"], [])
        self.assertEqual((ROOT / "VERSION").read_text(encoding="utf-8").strip(), "1.59.2")


if __name__ == "__main__":
    unittest.main()
