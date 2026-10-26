from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class AdminLayoutDesignStaticTests(unittest.TestCase):
    def test_layout_editor_is_admin_only_and_saves_validated_page_settings(self) -> None:
        module = (ROOT / "admin-layout-design.php").read_text(encoding="utf-8")
        self.assertIn("function admin_layout_design_is_admin(array $user): bool", module)
        self.assertIn("require_primary_admin();", module)
        self.assertIn("require_csrf();", module)
        self.assertIn("function admin_layout_design_validate_styles", module)
        self.assertIn("admin_layout_design_validate_rules($body['rules'] ?? [])", module)
        self.assertIn("save_setting(ADMIN_LAYOUT_DESIGN_SETTING", module)
        self.assertNotIn("<script", module)
        self.assertNotIn("innerHTML", module)

    def test_design_view_is_available_in_shared_pages_and_food_ticket_panel(self) -> None:
        index = (ROOT / "index.php").read_text(encoding="utf-8")
        food_ticket = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
        javascript = (ROOT / "assets/admin-layout-design.js").read_text(encoding="utf-8")
        self.assertIn("data-layout-design-launch", index)
        self.assertIn("admin_layout_design_api_handle($user)", index)
        self.assertIn("data-layout-page=\"food-ticket\"", food_ticket)
        self.assertIn("assets/admin-layout-design.js?v=10", food_ticket)
        self.assertIn("grid-template-columns", javascript)
        self.assertIn("white-space", javascript)
        self.assertIn("data-ald-reset", javascript)

    def test_elements_support_pixel_drag_keyboard_offsets_and_separate_ordering(self) -> None:
        javascript = (ROOT / "assets/admin-layout-design.js").read_text(encoding="utf-8")
        module = (ROOT / "admin-layout-design.php").read_text(encoding="utf-8")
        css = (ROOT / "assets/admin-layout-design.css").read_text(encoding="utf-8")
        self.assertIn("draggable=\"true\"", javascript)
        self.assertIn("function ensureDragHandle(", javascript)
        self.assertIn("function startOffsetDrag(", javascript)
        self.assertIn("function startResize(", javascript)
        self.assertIn("function setPanelPosition(", javascript)
        self.assertIn("function startPanelDrag(", javascript)
        self.assertIn("localStorage.setItem(panelPositionKey", javascript)
        self.assertIn("data-ald-panel-grip", javascript)
        self.assertNotIn("function setLauncherPosition(", javascript)
        self.assertIn("function resizeSelected(", javascript)
        self.assertIn("max-width'", javascript)
        self.assertIn("9999", javascript)
        self.assertIn("ald-hover", javascript)
        self.assertIn("dataset.aldResize", javascript)
        self.assertIn("event.ctrlKey", javascript)
        self.assertIn("function onOffsetPointerMove(", javascript)
        self.assertIn("setSelectedOffset(offsetDrag.offsetX + dx", javascript)
        self.assertIn("function updateDragHandle(", javascript)
        self.assertIn("data-ald-offset-axis=\"x\"", javascript)
        self.assertIn("data-ald-offset-axis=\"y\"", javascript)
        self.assertIn("event.shiftKey ? 10 : 1", javascript)
        self.assertIn("pointerdown", javascript)
        self.assertIn("dataTransfer.setData", javascript)
        self.assertIn("data-ald-order-index", javascript)
        self.assertIn("Ctrl+جهت", javascript)
        self.assertIn("focusSelectedOrderRow", javascript)
        self.assertIn("function moveOrderItem(", javascript)
        self.assertIn("array_key_exists('order', $value)", module)
        self.assertIn("offset-x", module)
        self.assertIn("offset-y", module)
        self.assertIn("translate:", module)
        self.assertIn("admin_layout_design_stored_rules", module)
        self.assertIn("array_slice($path, $anchor)", module)
        self.assertIn("const persisted = await request(null, 'GET')", javascript)
        self.assertIn("const unmatched = persistedRules.filter", javascript)
        self.assertIn("$rawLength === 'none'", module)
        self.assertIn("$transientClasses", module)
        self.assertIn("$styles['order']", module)
        self.assertIn(".ald-order-item", css)
        self.assertIn(".ald-resize-handle", css)
        self.assertIn(".ald-design-mode .ald-panel .ald-head", css)
        self.assertIn("<details class=\"ald-advanced\">", javascript)

    def test_pixel_offsets_are_not_capped_at_five_hundred(self) -> None:
        javascript = (ROOT / "assets/admin-layout-design.js").read_text(encoding="utf-8")
        module = (ROOT / "admin-layout-design.php").read_text(encoding="utf-8")
        index = (ROOT / "index.php").read_text(encoding="utf-8")
        food_ticket = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
        self.assertNotIn('min="-500" max="500"', javascript)
        self.assertNotIn("Math.max(-500", javascript)
        self.assertIn("Number.isSafeInteger(roundedX)", javascript)
        self.assertIn("if ($offset !== false)", module)
        self.assertNotIn("$offset >= -500", module)
        self.assertIn("assets/admin-layout-design.js?v=10", index)
        self.assertIn("assets/admin-layout-design.js?v=10", food_ticket)

    def test_order_table_has_ninth_action_track_and_keeps_cancel_button_inline(self) -> None:
        css = (ROOT / "assets/food-order.css").read_text(encoding="utf-8")
        self.assertIn("minmax(120px,1fr); gap:8px", css)
        self.assertIn(".fo-my-orders .table-row > td:last-child .button { white-space:nowrap; }", css)


if __name__ == "__main__":
    unittest.main()
