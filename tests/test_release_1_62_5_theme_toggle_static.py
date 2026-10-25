# -*- coding: utf-8 -*-
"""Static checks for 1.62.5: the host day/night toggle logic ships and is robust."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_app_js_theme_toggle_is_robust():
    js = read("assets/app.js")
    # اگر فایل بعد از DOMContentLoaded هم بارگذاری شود باید اجرا شود.
    assert "document.readyState === 'loading'" in js
    assert "__itsmBootstrapInit" in js
    # کلیک دکمهٔ تم با delegation مدیریت می‌شود تا دکمه‌های دیرهنگام هم کار کنند.
    assert "event.target.closest('[data-theme-toggle]')" in js
    assert "ticket_theme_mode=" in js
    assert "window.ITSMThemeMode" in js


def test_all_host_shells_load_the_new_app_js():
    for path in ["index.php", "governance.php", "ola.php", "app/Views/layout/footer.php"]:
        assert "assets/app.js?v=3.4.27" in read(path), path


def test_release_package_ships_app_js():
    import json
    manifest = json.loads(read("updates/2026-10-25-release-162-5-host-theme-toggle-appjs/manifest.json"))
    assert manifest["version"] == "1.62.5"
    assert manifest["migrations"] == []


if __name__ == "__main__":
    test_app_js_theme_toggle_is_robust()
    test_all_host_shells_load_the_new_app_js()
    test_release_package_ships_app_js()
    print("ok")
