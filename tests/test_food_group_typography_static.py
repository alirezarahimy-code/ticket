from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class FoodGroupTypographyStaticTests(unittest.TestCase):
    def test_group_print_model_uses_separate_menu_and_member_typography(self) -> None:
        source = (ROOT / "food-ticket-templates.php").read_text(encoding="utf-8")
        model_start = source.index("function food_ticket_tpl_model(")
        model_end = source.index("\nfunction ", model_start + 10)
        model = source[model_start:model_end]
        self.assertIn("$groupMenuStyle", model)
        self.assertIn("$groupMemberStyle", model)
        self.assertIn("($groupLine['kind'] ?? '') === 'menu' ? $menuStyle : $memberStyle", model)
        self.assertIn("'type' => 'rect'", model)
        self.assertIn("} elseif ($type === 'rect') {", source)
        self.assertIn("elseif ($t -eq 'rect') {", source)

    def test_group_menu_counts_and_menu_to_member_spacing_are_rendered(self) -> None:
        runtime = (ROOT / "food-ticket-runtime.php").read_text(encoding="utf-8")
        templates = (ROOT / "food-ticket-templates.php").read_text(encoding="utf-8")
        self.assertIn("$byFood[$food]['count']++", runtime)
        self.assertIn("'count' => (int) $foodData['count']", runtime)
        self.assertIn("$food . ' — ' . $count . ' عدد'", runtime)
        self.assertIn("$lineY += 1.5; // فاصلهٔ بیشتر میان عنوان غذا و نخستین نام", templates)
        designer = (ROOT / "food-ticket-web/assets/food-designer.js").read_text(encoding="utf-8")
        self.assertIn("food + ' — ' + names.length + ' عدد'", designer)
        self.assertGreaterEqual(designer.count("const gapAfter = line.kind === 'menu' ? 1.5 : 0;"), 2)

    def test_group_designer_exposes_per_style_font_controls_and_refreshes_preview(self) -> None:
        source = (ROOT / "food-ticket-web/assets/food-designer.js").read_text(encoding="utf-8")
        self.assertIn("function groupTypographyPropsHtml(e)", source)
        self.assertIn("function groupLivePreviewHtml()", source)
        self.assertIn("پیش‌نمایش زندهٔ طراحی", source)
        self.assertIn("cache: 'no-store'", source)
        self.assertIn('data-fd-prop="font_family"', source)
        self.assertIn("inp('line_height'", source)
        self.assertIn("data-fd-group-style-pick", source)
        self.assertIn("function schedulePreview(ms)", source)
        self.assertIn("seq !== S.previewSeq", source)
        self.assertIn("sample_type: sampleType", source)


if __name__ == "__main__":
    unittest.main()
