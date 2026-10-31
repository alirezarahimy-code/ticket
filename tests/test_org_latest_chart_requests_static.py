from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path):
    return (ROOT / path).read_text()


def test_export_menu_stacks_above_search_toolbar():
    css = read('assets/style.css')
    js = read('assets/organization.js')
    assert '.org-header { z-index: 60; }' in css
    assert '.org-export-menu { z-index: 200; }' in css
    assert "if (type === 'pdf') return printWholeChart();" in js


def test_pdf_print_uses_full_chart_image_and_single_landscape_print_page():
    js = read('assets/organization.js')
    css = read('assets/style.css')
    assert 'var createChartImageBlob = function (done)' in js
    assert 'svgReader.readAsDataURL(svgBlob)' in js
    assert 'imageReader.readAsDataURL(blob)' in js
    assert 'stage.scrollWidth, stage.offsetWidth' in js
    assert 'var printWindow = window.open(\'\', \'_blank\')' in js
    assert "imageReader.readAsDataURL(blob)" in js
    assert "printWindow.print();" in js
    assert "rootBox.setAttribute('class', 'page-organization')" in js
    assert 'body.org-image-print-page img' in css
    assert '@page { size: landscape; margin: 5mm; }' in css
    assert 'break-inside: avoid' in css


def test_deputy_and_department_cards_have_only_requested_add_actions():
    js = read('assets/organization.js')
    deputy = js[js.index('var renderDeputy ='):js.index('var renderDepartment =')]
    department = js[js.index('var renderDepartment ='):js.index('var expertNode =')]
    assert '+ افزودن اداره جدید' in deputy
    assert 'data-org-action=\"add-experts\"' in deputy
    assert 'data-org-action=\"add-department\"' in deputy
    assert 'data-org-action=\"add-experts\"' in department
    assert 'data-org-action=\"add-department\"' not in department
    assert "label: 'افزودن ادارهٔ زیرمجموعه'" not in js


def test_partner_company_tree_suppresses_independent_unit_cards():
    js = read('assets/organization.js')
    assert "var leftLiaison = rootType() === 'company_manager' ? '' : renderLiaisonSide('left');" in js
    assert "var rightLiaison = rootType() === 'company_manager' ? '' : renderLiaisonSide('right');" in js


def test_fixed_company_labels_show_in_selector_and_root_card_and_membership_is_derived():
    index = read('index.php')
    js = read('assets/organization.js')
    actions = read('actions/settings-actions.php')
    assert "'companyName' => (string) $currentCompany['name']" in index
    assert "e((string) $companyOption['name'])" in index
    helpers = read('organization.php')
    assert 'org-company-edit-link' in index
    assert 'company_names[' in index
    assert 'org-company-node-name' in js
    assert 'esc(data.companyName ||' in js
    assert 'trim((string) ($company[\'name\'] ?? \'\'))' in helpers
    assert 'org_user_company_memberships' in helpers
    assert 'change_user_company' not in index
    assert "if ($action === 'save_org_company_names')" in actions
    assert 'org_company_user_label' in index


if __name__ == '__main__':
    tests = [value for key, value in globals().items() if key.startswith('test_')]
    for test in tests:
        test()
        print('ok -', test.__name__)
    print(f'{len(tests)} organization chart request checks passed')
