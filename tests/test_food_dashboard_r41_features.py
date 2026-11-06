from pathlib import Path
import json
import zipfile


ROOT = Path(__file__).resolve().parents[1]
HTML = ROOT / "food-ticket-web/index.html"
BUNDLE = ROOT / "food-ticket-web/assets/food-capitalio-dashboard-r41.js"
CSS = ROOT / "food-ticket-web/assets/food-capitalio-dashboard-r41.css"
PACKAGE = ROOT / "downloads/ticket-update-1.61.10-food-dashboard-r41-month-guests-theme-20261025.zip"


def test_r41_is_integrated_and_self_contained():
    html = HTML.read_text(encoding="utf-8")
    js = BUNDLE.read_text(encoding="utf-8")
    css = CSS.read_text(encoding="utf-8")

    # پنل زنده از ۱.۶۲.۲ به بعد روی R42 است؛ بستهٔ R41 همچنان بهعنوان
    # آرشیو انتشار معتبر است و نباید از مخزن حذف شود. هر دو مسیر چک میشوند.
    assert "food-capitalio-dashboard-r42.css?v=1.62.2-dashboard-fix" in html
    assert "food-capitalio-dashboard-r42.js?v=1.62.2-dashboard-fix" in html
    # The live dashboard cards moved into the separately served R42 bundle;
    # do not pin the retired R41 inline markup to the host document.
    r42 = ROOT / "food-ticket-web/assets/food-capitalio-dashboard-r42.js"
    assert r42.is_file() and r42.stat().st_size > 1_000_000
    assert "ماه اخیر" in js
    assert "۷ روز اخیر" in js
    assert "children:t===`weekly`?`ماه اخیر`:`۷ روز اخیر`" in js
    assert "food-dashboard-period-change" in js
    assert "document.startViewTransition" in js
    assert "window.innerWidth/2,y=window.innerHeight/2" in js
    assert "food-dashboard-r41-center-reveal" in css
    assert "::view-transition-new(root)" in css

    with zipfile.ZipFile(PACKAGE) as archive:
        assert archive.testzip() is None
        names = set(archive.namelist())
        assert "files/food-ticket-web/index.html" in names
        assert "files/food-ticket-web/assets/food-capitalio-dashboard-r41.js" in names
        assert "files/food-ticket-web/assets/food-capitalio-dashboard-r41.css" in names
        assert "manifest.json" in names
        manifest = json.loads(archive.read("manifest.json"))
        assert manifest["release"] == "R41"
        assert manifest["migrations"] == []
        assert not any("r40" in name.lower() for name in names)


def test_r41_theme_reveal_respects_reduced_motion_fallback():
    js = BUNDLE.read_text(encoding="utf-8")
    assert "(prefers-reduced-motion: reduce)" in js
    assert "e.finished.then" in js
    assert "a.classList.remove(`food-dashboard-theme-transition`)" in js


if __name__ == "__main__":
    test_r41_is_integrated_and_self_contained()
    test_r41_theme_reveal_respects_reduced_motion_fallback()
    print("R41 dashboard assertions: PASS")
