from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_group_design_is_saved_and_read_back():
    src = (ROOT / "food-ticket-templates.php").read_text(encoding="utf-8")
    assert "food_ticket_tpl_setting('food_ticket_group_template', '')" in src
    assert "save_setting('food_ticket_group_template', $encoded)" in src
    assert "قالب گروهی ذخیره نشد؛ مقدار ثبت‌شده با طراحی ارسالی یکسان نیست." in src
    assert "food_ticket_tpl_group_template()" in src
    assert "'field' => 'group_title'" in src


def test_group_preview_and_physical_print_share_group_template_and_gap():
    src = (ROOT / "food-ticket-templates.php").read_text(encoding="utf-8")
    designer = (ROOT / "food-ticket-web/assets/food-designer.js").read_text(encoding="utf-8")
    assert "!empty($event['group_ticket'])" in src
    assert "food_ticket_tpl_group_template()" in src
    assert "lineY += 1.5; // فاصلهٔ بیشتر میان عنوان غذا و نخستین نام" in src
    assert "const gapAfter = line.kind === 'menu' ? 1.5 : 0;" in designer
    assert "function groupTypographyPropsHtml(e)" in designer
    assert "inp('font_size', 'اندازهٔ فونت (pt)'" in designer


def test_main_page_routes_group_save_to_integrated_api_and_does_not_mislabel_404():
    panel = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")
    host = (ROOT / "food-ticket-web-host.php").read_text(encoding="utf-8")
    router = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
    entry = (ROOT / "index.php").read_text(encoding="utf-8")
    assert "window.FOOD_TICKET_API_BASE" in host
    assert "$apiBase = 'index.php?page=food-ticket&food_api=';" in router
    assert "str_starts_with($route, 'templates/')" in router
    assert "food_ticket_templates_api($route, $user);" in router
    assert "$page === 'food-ticket' && isset($_GET['food_api'])" in entry
    assert "/api/templates/group-design" in (ROOT / "food-ticket-web/assets/food-designer.js").read_text(encoding="utf-8")
    assert "مسیر API روی سرور پیدا نشد (HTTP 404)" in panel
    assert "این قابلیت در حالت «سرویس مستقل»" not in panel
