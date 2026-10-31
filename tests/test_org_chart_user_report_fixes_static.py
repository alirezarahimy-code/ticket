from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def read(path):
    return (ROOT / path).read_text(encoding='utf-8')

def test_print_fits_the_complete_tree_and_hides_screen_controls():
    js = read('assets/organization.js')
    css = read('assets/style.css')
    assert "var createChartImageBlob = function (done)" in js
    assert "var width = Math.max(stage.scrollWidth, stage.offsetWidth)" in js
    assert "var pixelScale = Math.min(2, 12000 / Math.max(width, height)" in js
    assert "var printWindow = window.open('', '_blank')" in js
    assert "printWindow.print();" in js
    assert "body.org-image-print-page img" in css
    assert "@page { size: landscape; margin: 5mm; }" in css

def test_search_matches_units_managers_and_experts_then_centers_result():
    js = read('assets/organization.js')
    assert "normalize(String(item.name || '') + ' ' + String(item.code || ''))" in js
    assert "nodeMatch(manager)" in js
    assert "personItem) ? ' is-match'" in js
    assert "first.scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' })" in js

def test_toolbar_is_outside_the_horizontal_scroller_and_root_is_centered_on_open():
    index = read('index.php')
    js = read('assets/organization.js')
    css = read('assets/style.css')
    assert '<main class="org-canvas-shell">' in index
    assert '<div class="org-canvas" id="org-canvas"><div class="org-chart-wrap" id="org-chart-wrap"></div></div>' in index
    assert "rootCard.scrollIntoView({ block: 'start', inline: 'center' })" in js
    assert '.org-canvas-shell > .org-canvas-toolbar' in css

def test_company_switch_uses_csp_safe_change_handler_and_explicit_navigation():
    index = read('index.php')
    js = read('assets/organization.js')
    assert 'id="org-company-select" name="company_id" onchange=' not in index
    assert "companySelect.addEventListener('change'" in js
    assert "url.searchParams.set('company_id', this.value)" in js

def test_restore_input_stays_hidden_until_restore_button_opens_it():
    index = read('index.php')
    js = read('assets/organization.js')
    css = read('assets/style.css')
    assert 'id="org-backup-file" accept="application/json,.json" hidden' in index
    assert "if (backupInput) backupInput.click()" in js
    assert '#org-backup-file[hidden] { display: none !important' in css

def test_action_request_has_explicit_company_url_and_exposes_server_error_detail():
    js = read('assets/organization.js')
    assert 'action="index.php?page=organization"' in read('index.php')
    assert "endpoint.searchParams.set('page', 'organization')" in js
    assert "endpoint.searchParams.set('company_id', String(data.companyId || 1))" in js
    assert "detail.slice(0, 180)" in js

if __name__ == '__main__':
    tests = [value for name, value in globals().items() if name.startswith('test_')]
    for test in tests:
        test()
        print('ok -', test.__name__)
    print(f'{len(tests)} organization UI regression checks passed')
