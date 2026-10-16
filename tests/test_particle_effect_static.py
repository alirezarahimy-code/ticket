from pathlib import Path
import json
import re
import unittest

ROOT = Path(__file__).resolve().parents[1]


class ParticleEffectReleaseTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.bootstrap = (ROOT / "bootstrap.php").read_text(encoding="utf-8")
        cls.index = (ROOT / "index.php").read_text(encoding="utf-8")
        cls.particle_engine = (ROOT / "assets/particle-network.js").read_text(encoding="utf-8")
        cls.particle_settings = (ROOT / "assets/particle-settings.js").read_text(encoding="utf-8")

    def test_automatic_login_and_dashboard_particle_loader_was_removed(self):
        self.assertNotIn("$GLOBALS['ui_particles']", self.index)
        self.assertNotIn("$withParticles", self.index)
        self.assertIn("particle_effect_script_tag()", self.index)
        self.assertIn("particle_effect_enabled_for_current_page()", self.bootstrap)
        self.assertIn("display_effect_type() === 'particles'", self.bootstrap)
        for page in ("ola.php", "governance.php"):
            self.assertIn("particle_effect_script_tag()", (ROOT / page).read_text(encoding="utf-8"))

    def test_particle_settings_are_sanitized_and_match_engine_defaults(self):
        spec = re.search(
            r"function particle_effect_config_spec\(\): array\s*\{(.*?)\n\}",
            self.bootstrap,
            re.S,
        )
        defaults = re.search(r"var DEFAULTS = \{(.*?)\n    \};", self.particle_engine, re.S)
        self.assertIsNotNone(spec)
        self.assertIsNotNone(defaults)
        spec_keys = re.findall(r"^\s*'([A-Za-z][A-Za-z0-9]*)'\s*=>", spec.group(1), re.M)
        engine_keys = re.findall(r"^\s*([A-Za-z][A-Za-z0-9]*):", defaults.group(1), re.M)
        self.assertEqual(
            set(engine_keys),
            set(spec_keys) | {"color", "showDots", "showLinks", "connectCursor", "autoMove"},
        )
        self.assertIn("particle_effect_sanitize_config", self.bootstrap)
        self.assertIn("prefers-reduced-motion: reduce", self.particle_engine)
        self.assertIn("destroy: function ()", self.particle_engine)

    def test_two_accessible_tabs_use_the_existing_shared_37_page_catalog(self):
        self.assertIn('id="triangle-form"', self.index)
        self.assertIn('data-effect-tab="triangle"', self.index)
        self.assertIn('data-effect-tab="particles"', self.index)
        self.assertIn('id="triangle-effect-panel"', self.index)
        self.assertIn('id="particle-effect-panel"', self.index)
        self.assertIn('name="triangle_pages[]"', self.index)
        self.assertIn("name=\"particle[' . e($key)", self.index)
        self.assertIn("$particleRange('density'", self.index)
        self.assertIn('data-particle-preset="reactive"', self.index)
        catalog = re.search(
            r"function triangle_effect_page_catalog\(\): array\s*\{(.*?)\n\}",
            self.bootstrap,
            re.S,
        )
        self.assertIsNotNone(catalog)
        self.assertEqual(len(re.findall(r"^\s*'[a-z][a-z0-9-]*'\s*=>", catalog.group(1), re.M)), 37)

    def test_active_effect_and_both_configs_are_saved_without_schema_change(self):
        for marker in (
            "save_setting('display_effect_type'",
            "save_setting('particle_effect_config'",
            "save_setting('triangle_effect_config'",
            "save_setting('triangle_effect_pages'",
            "particle_effect_config()",
            "particle_effect_script_tag()",
        ):
            self.assertIn(marker, self.index + self.bootstrap)
        manifest = json.loads(
            (ROOT / "updates/2026-10-16-release-156/manifest.json").read_text(encoding="utf-8")
        )
        self.assertEqual(manifest["version"], "1.56")
        self.assertEqual(manifest["migrations"], [])

    def test_particle_preview_controller_has_live_controls_and_preset_updates(self):
        self.assertIn("ParticleNetwork.mount(previewNode, readConfig())", self.particle_settings)
        self.assertIn("preview.update(readConfig())", self.particle_settings)
        self.assertIn("ticket:effect-tab-change", self.particle_settings)
        self.assertIn("data-particle-preset", self.particle_settings)
        self.assertIn("ParticleNetwork.background(initialConfig)", self.particle_engine)

    def test_login_dashboard_and_usage_guidance_are_explicit(self):
        guide = (ROOT / "help-guide.php").read_text(encoding="utf-8")
        chatbot = (ROOT / "chatbot.php").read_text(encoding="utf-8")
        self.assertIn("تب «ذرات معلق»", guide)
        self.assertIn("به‌طور خودکار در فرم ورود یا داشبورد نمایش داده نمی‌شود", guide)
        self.assertIn("'ذرات' => ['ذرات معلق', 'افکت نمایشی']", chatbot)


if __name__ == "__main__":
    unittest.main()
