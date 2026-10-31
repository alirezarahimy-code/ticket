from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def read(path: str) -> str:
    return (ROOT / path).read_text(encoding="utf-8")


def test_space_drag_is_local_and_uses_immediate_scroll():
    js = read("assets/organization.js")
    css = read("assets/style.css")
    assert "event.code === 'Space'" in js
    assert "event.preventDefault(); event.stopPropagation(); canvas.setPointerCapture(event.pointerId)" in js
    assert "event.pointerId !== state.pan.pointerId" in js
    assert ".org-canvas { scroll-behavior: auto !important; overscroll-behavior: contain; }" in css
    assert ".org-canvas.org-panning * { pointer-events: none" not in css


def test_empty_expert_cards_are_not_rendered_by_default():
    js = read("assets/organization.js")
    assert "if (!experts.length) return '';" in js
    assert "renderExperts(item)" in js


def test_role_repairs_and_routing_diagnostics_live_in_primary_admin_settings():
    index = read("index.php")
    actions = read("actions/org-actions.php")
    organization_page = index.split("if ($page === 'organization')", 1)[1].split("if ($page === 'settings')", 1)[0]
    settings_page = index.split("if ($page === 'settings')", 1)[1].split("if ($page === 'logs')", 1)[0]
    assert 'href="#org-admin-repair"' in settings_page
    assert 'id="org-admin-repair"' in settings_page
    assert "اصلاح مدیران سازمانی که ادمین اصلی شده‌اند" in settings_page
    assert "اصلاح مدیران سازمانی که ادمین اصلی شده‌اند" not in organization_page
    assert "تشخیص ارجاع خودکار درخواست‌ها" not in organization_page
    assert "redirect('index.php?page=settings#org-admin-repair')" in actions
    assert "if ($canManageSettingsUsers)" in settings_page


def test_each_top_level_branch_has_an_inherited_distinct_color_for_cards_and_wires():
    js = read("assets/organization.js")
    css = read("assets/style.css")
    assert "var branchColorAt = function (index)" in js
    assert "--org-branch-color:' + branchColor" in js
    assert "renderUnitBranch(child, path, branchColor)" in js
    assert "getComputedStyle(branch).getPropertyValue('--org-branch-color')" in js
    assert ".org-branch .org-node.org-deputy" in css
    assert "border-top-color: var(--org-branch-color)" in css


def test_add_submission_is_locked_in_ui_and_replay_token_is_checked_server_side():
    js = read("assets/organization.js")
    actions = read("actions/org-actions.php")
    assert "state.submitting" in js
    assert "name = 'org_request_token'" in js
    assert "if (isset($seenTokens[$requestToken]))" in actions
    assert "این درخواست قبلاً دریافت شده و دوباره اجرا نشد" in actions
    assert "save_org_node" in actions.split("$orgTokenActions =", 1)[1].split(";", 1)[0]


def test_exports_cover_full_chart_and_include_print_preview_and_backup_restore():
    js = read("assets/organization.js")
    index = read("index.php")
    css = read("assets/style.css")
    actions = read("actions/org-actions.php")
    for export_format in ("pdf", "png", "excel", "json"):
        assert f'data-org-export="{export_format}"' in index
    assert "data-org-tool=\"print\"" in index and "data-org-tool=\"backup\"" in index
    assert "data-org-tool=\"restore-backup\"" in index
    assert "units: units, users: data.users || []" in js
    # از ۱.۶۲.۵۲ نام فایل خروجی اکسل به تفکیک شرکت است.
    assert "export=excel" in js and "xlsx_send('organization-tree-company-'" in index
    assert "foreignObject" in js and "toBlob" in js
    assert "org-printing" in css and "@page { size: landscape" in css
    assert "restore_org_backup" in actions and "role =" not in actions.split("if ($action === 'restore_org_backup')", 1)[1].split("if ($action === 'reset_org_structure')", 1)[0]


def test_server_actions_refresh_chart_data_without_reloading_and_preserve_view():
    js = read("assets/organization.js")
    assert "fetch(endpoint.toString()," in js
    assert "new FormData(form)" in js
    assert "window.location.reload(); return;" in js
    assert "savedView = { zoom: state.zoom, left: canvas ? canvas.scrollLeft : 0, top: canvas ? canvas.scrollTop : 0 }" in js
    assert "state.zoom = savedView.zoom" in js
    assert "updatedCanvas.scrollLeft = savedView.left" in js


def test_ceo_peer_units_accept_and_display_experts_inside_their_card():
    js = read("assets/organization.js")
    php = read("organization.php")
    actions = read("actions/org-actions.php")
    assert "item.type === 'liaison'" in js and "org-liaison-experts" in js
    assert "['department', 'deputy', 'management', 'liaison']" in php
    assert "کارشناس را می‌توان به معاونت، مدیریت مستقل، اداره یا واحد مستقل وصل کرد" in actions


def test_space_repeats_are_prevented_and_wires_use_measured_zoom_scale():
    js = read("assets/organization.js")
    assert "if (event.code === 'Space' && !editing)" in js
    assert "event.preventDefault();" in js
    assert "}, true);" in js
    assert "rect.width / stage.offsetWidth" in js
    assert "rect.height / stage.offsetHeight" in js
    assert "new ResizeObserver" in js
