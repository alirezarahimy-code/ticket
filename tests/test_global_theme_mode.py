from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_global_theme_is_attached_to_all_main_shells():
    bootstrap = (ROOT / "bootstrap.php").read_text(encoding="utf-8")
    index = (ROOT / "index.php").read_text(encoding="utf-8")
    css = (ROOT / "assets/theme-mode.css").read_text(encoding="utf-8")
    app = (ROOT / "assets/app.js").read_text(encoding="utf-8")

    assert "function app_theme_mode()" in bootstrap
    assert "ticket_theme_mode" in bootstrap
    assert "data-theme-mode=\"' . e($themeMode)" in index
    assert "app_theme_toggle_markup()" in index
    assert "assets/theme-mode.css?v=2" in index
    assert "data-theme-toggle" in bootstrap
    assert "themeRoot.setAttribute('data-theme-mode', mode)" in app
    assert "document.cookie = 'ticket_theme_mode=' + mode" in app
    assert "window.startViewTransition" not in app  # API is on document, not window.
    assert "document.startViewTransition(update)" in app
    assert "html[data-theme-mode=\"dark\"] body" in css
    assert "html[data-theme-mode=\"dark\"] .topbar" in css
    assert ".content-wrap" in css and ".navbar" in css and ".footer" in css


def test_food_panel_and_standalone_modules_follow_the_global_mode():
    host = (ROOT / "food-ticket-web-host.php").read_text(encoding="utf-8")
    food = (ROOT / "food-ticket.php").read_text(encoding="utf-8")
    page = (ROOT / "food-ticket-web/index.html").read_text(encoding="utf-8")
    brand = (ROOT / "food-ticket-web/assets/food-brand.js").read_text(encoding="utf-8")
    bundle = (ROOT / "food-ticket-web/assets/food-capitalio-dashboard-r42.js").read_text(encoding="utf-8")
    food_css = (ROOT / "food-ticket-web/assets/food-ticket-global-theme-r42.css").read_text(encoding="utf-8")

    assert "'theme_mode' => app_theme_mode()" in food
    assert "'theme_mode_href' => 'assets/theme-mode.css?v=2'" in food
    assert "array_key_exists('theme_mode', $opts)" in host
    assert "$themeModeHref !== ''" in host
    assert "food-capitalio-dashboard-r42.js?v=1.62.2-dashboard-fix" in page
    assert "food-global-theme-toggle" in page
    assert "window.addEventListener('ticket-theme-change'" in brand
    assert "window.addEventListener(`ticket-theme-change`" in bundle
    assert "data-theme-mode=\"dark\"" in food_css
    assert ".food-capitalio-dashboard-widget header button[aria-label^=\"تغییر به حالت\"]" in food_css


def test_independent_php_renderers_use_shared_mode():
    for path in ["governance.php", "ola.php"]:
        source = (ROOT / path).read_text(encoding="utf-8")
        assert "app_theme_mode()" in source
        assert "app_theme_toggle_markup()" in source
        assert "assets/theme-mode.css?v=2" in source
    fallback = (ROOT / "app/Views/layout/header.php").read_text(encoding="utf-8")
    assert "app_theme_mode()" in fallback
    assert "app_theme_toggle_markup()" in fallback


if __name__ == "__main__":
    test_global_theme_is_attached_to_all_main_shells()
    test_food_panel_and_standalone_modules_follow_the_global_mode()
    test_independent_php_renderers_use_shared_mode()
    print("Global day/night theme assertions: PASS")
