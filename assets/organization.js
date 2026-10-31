(function () {
    'use strict';

    var data = window.PERSIAN_ORG_DATA || { canManage: false, undo: false, undoLabel: '', units: [], users: [] };
    var units = data.units || [];
    var users = (data.users || []).filter(function (item) { return item.active; });
    var state = { zoom: 1, search: '', poolSearch: '', filterStat: null, modal: null, inline: null, confirm: null, spaceDown: false, pan: null, deputyDrag: null, submitting: false, printSnapshot: null, wireObserver: null, observedStage: null, initialCentered: false };
    var $ = function (selector) { return document.querySelector(selector); };
    var $$ = function (selector) { return Array.prototype.slice.call(document.querySelectorAll(selector)); };
    var esc = function (value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
        });
    };
    var initial = function (name) { return String(name || '?').trim().charAt(0) || '?'; };
    var iconFor = function (type) {
        var paths = {
            ceo: '<path d="M4 20h16M6 20V8l6-4 6 4v12M9 12h2v2H9m4 0h2v-2h-2m-4 4h2v2H9m4 0h2v-2h-2"/>',
            deputy: '<path d="M3 20h18M5 20V8l7-4 7 4v12M8 11v6m4-6v6m4-6v6M7 20h10"/>',
            department: '<path d="M3 6h7l2 2h9v10H3zM3 10h18"/>',
            manager: '<circle cx="12" cy="8" r="3"/><path d="M5 20c.7-3.6 2.9-5.5 7-5.5s6.3 1.9 7 5.5M17 5v4m-2-2h4"/>',
            expert: '<circle cx="12" cy="8" r="3"/><path d="M5 20c.7-3.6 2.9-5.5 7-5.5s6.3 1.9 7 5.5M17 15v4m-2-2h4"/>',
            pool: '<path d="M4 8h16v11H4zM7 8V5h10v3M8 12h8M8 15h5"/>',
            user: '<circle cx="12" cy="8" r="3"/><path d="M5 20c.7-3.6 2.9-5.5 7-5.5s6.3 1.9 7 5.5"/>',
            add: '<path d="M12 5v14M5 12h14"/>',
            remove: '<path d="M5 12h14"/>',
            delete: '<path d="M5 7h14M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/>',
            manage: '<path d="M5 6h14M5 12h14M5 18h14M8 6v0m5 6v0m-3 6v0"/>',
            warning: '<circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 3v.1"/>',
            reset: '<path d="M5 8a8 8 0 1 1 1 8M5 8V3m0 5h5"/>',
            start: '<path d="M4 20h16M6 20V8l6-4 6 4v12M9 12h2v2H9m4 0h2v-2h-2"/>',
            export: '<path d="M12 4v12m0 0-4-4m4 4 4-4M5 20h14"/>'
        };
        return '<svg class="org-svg-icon org-svg-' + type + '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + (paths[type] || paths.user) + '</svg>';
    };
    var unit = function (id) { return units.find(function (item) { return Number(item.id) === Number(id); }) || null; };
    var person = function (id) { return users.find(function (item) { return Number(item.id) === Number(id); }) || null; };
    var rootType = function () { return data.companyRootType || 'ceo'; };
    var rootLabel = function () { return data.companyRootTitle || (data.companyType === 'partner' ? 'مدیر «' + (data.companyName || 'شرکت همکار') + '»' : 'مدیرعامل'); };
    var root = function () { return units.find(function (item) { return item.type === rootType(); }) || null; };
    var children = function (parentId, type) {
        return units.filter(function (item) { return Number(item.parentId) === Number(parentId) && (!type || item.type === type) && item.type !== 'liaison'; })
            .sort(function (a, b) { return Number(a.sortOrder || 0) - Number(b.sortOrder || 0) || a.name.localeCompare(b.name, 'fa') || Number(a.id) - Number(b.id); });
    };
    var descendantIds = function (unitId) {
        var found = [], stack = [Number(unitId)];
        while (stack.length) { var parentId = stack.pop(); children(parentId).forEach(function (child) { var id = Number(child.id); if (found.indexOf(id) === -1) { found.push(id); stack.push(id); } }); }
        return found;
    };
    var managedUnit = function (userId) { return units.find(function (item) { return Number(item.managerId) === Number(userId); }) || null; };
    // ۱.۶۲.۱۰: واحدهای مستقل (نمایندگی) کنار مدیرعامل — بدون والد و بدون خط اتصال به چارت.
    var liaisonAt = function (side) {
        return units.find(function (item) { return item.type === 'liaison' && item.chartSide === side; }) || null;
    };
    var roleOf = function (item) {
        var managed = managedUnit(item.id);
        var ceo = root();
        if (ceo && Number(ceo.managerId) === Number(item.id)) return rootType() === 'company_manager' ? 'company_manager' : 'ceo';
        if (managed && managed.type === 'deputy') return 'dep';
        if (managed && managed.type === 'department') return 'dept';
        if (managed && managed.type === 'liaison') return 'liaison';
        return Number(item.unitId) > 0 ? 'expert' : 'pool';
    };
    var positionOf = function (item) {
        var managed = managedUnit(item.id);
        if (managed && (managed.type === 'ceo' || managed.type === 'company_manager')) return rootLabel();
        if (managed && managed.type === 'liaison') return 'نمایندهٔ ' + managed.name;
        if (managed) return 'مدیر ' + managed.name;
        if (Number(item.unitId) > 0) {
            var assigned = unit(item.unitId);
            return assigned ? 'کارشناس ' + assigned.name : 'کارشناس';
        }
        return 'آزاد';
    };
    var sourceLabel = function (item) {
        if (!item || !item.source) return '';
        return item.source === 'ldap' ? 'دامینی' : 'محلی';
    };
    var allDepartments = function () { return units.filter(function (item) { return item.type === 'department'; }); };
    var expertHosts = function () {
        return units.filter(function (item) { return item.type === 'deputy' || item.type === 'department' || item.type === 'liaison'; });
    };
    var expertsIn = function (unitId) { return users.filter(function (item) { return Number(item.unitId) === Number(unitId) && roleOf(item) === 'expert'; }); };
    var poolUsers = function () { return users.filter(function (item) { return roleOf(item) === 'pool'; }); };
    var normalize = function (value) { return String(value || '').toLocaleLowerCase('fa').replace(/\s+/g, ' ').trim(); };
    var match = function (item) {
        var query = normalize(state.search);
        return !!query && normalize(String(item.name || '') + ' ' + String(item.code || '')).indexOf(query) !== -1;
    };
    var toast = function (message, icon) {
        var rootEl = $('#org-toast');
        if (!rootEl) return;
        var iconMarkup = String(icon || '✓').indexOf('<svg') === 0 ? icon : esc(icon || '✓');
        rootEl.innerHTML = '<span class="org-toast-icon">' + iconMarkup + '</span><span>' + esc(message) + '</span>';
        rootEl.classList.add('show');
        window.clearTimeout(rootEl._timer);
        rootEl._timer = window.setTimeout(function () { rootEl.classList.remove('show'); }, 2000);
    };
    var submit = function (action, fields) {
        if (!data.canManage || state.submitting) return;
        var form = $('#org-action-form');
        if (!form) return;
        state.submitting = true;
        var canvas = $('#org-canvas'), savedView = { zoom: state.zoom, left: canvas ? canvas.scrollLeft : 0, top: canvas ? canvas.scrollTop : 0 };
        $('.organization-workspace').classList.add('org-ajax-saving');
        form.querySelectorAll('input:not([name="csrf"]):not([name="company_id"])').forEach(function (input) { input.remove(); });
        var actionField = document.createElement('input'); actionField.type = 'hidden'; actionField.name = 'action'; actionField.value = action; form.appendChild(actionField);
        var tokenBytes = new Uint8Array(24);
        if (window.crypto && window.crypto.getRandomValues) window.crypto.getRandomValues(tokenBytes);
        else for (var tokenIndex = 0; tokenIndex < tokenBytes.length; tokenIndex++) tokenBytes[tokenIndex] = Math.floor(Math.random() * 256);
        var requestToken = Array.prototype.map.call(tokenBytes, function (byte) { return byte.toString(16).padStart(2, '0'); }).join('');
        var tokenField = document.createElement('input'); tokenField.type = 'hidden'; tokenField.name = 'org_request_token'; tokenField.value = requestToken; form.appendChild(tokenField);
        Object.keys(fields || {}).forEach(function (key) {
            var value = fields[key], list = Array.isArray(value) ? value : [value];
            list.forEach(function (entry) { var input = document.createElement('input'); input.type = 'hidden'; input.name = Array.isArray(value) ? key + '[]' : key; input.value = String(entry == null ? '' : entry); form.appendChild(input); });
        });
        var endpoint = new URL(form.getAttribute('action') || window.location.href, window.location.href); endpoint.hash = ''; endpoint.searchParams.set('page', 'organization'); endpoint.searchParams.set('company_id', String(data.companyId || 1));
        fetch(endpoint.toString(), { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.text().then(function (html) { if (!response.ok) { var detail = new DOMParser().parseFromString(html, 'text/html').body.textContent.replace(/\s+/g, ' ').trim(); throw new Error('درخواست ' + response.status + (detail ? ': ' + detail.slice(0, 180) : '')); } return html; }); })
            .then(function (html) {
                var parsed = new DOMParser().parseFromString(html, 'text/html');
                var dataScript = Array.prototype.slice.call(parsed.scripts).find(function (script) { return script.textContent.indexOf('window.PERSIAN_ORG_DATA') !== -1; });
                if (!dataScript) { window.location.reload(); return; }
                var source = dataScript.textContent, marker = 'window.PERSIAN_ORG_DATA', equalsAt = source.indexOf('=', source.indexOf(marker)), semicolonAt = source.lastIndexOf(';');
                var freshData = JSON.parse(source.slice(equalsAt + 1, semicolonAt).trim());
                data = freshData; units = freshData.units || []; users = (freshData.users || []).filter(function (item) { return item.active; }); state.zoom = savedView.zoom;
                var csrfSource = parsed.querySelector('#org-action-form input[name="csrf"]'), csrfTarget = form.querySelector('input[name="csrf"]');
                if (csrfSource && csrfTarget) csrfTarget.value = csrfSource.value;
                state.submitting = false; $('.organization-workspace').classList.remove('org-ajax-saving');
                renderAll();
                var notice = parsed.querySelector('.alert');
                if (notice) toast(notice.textContent.trim(), notice.classList.contains('success') ? '✓' : iconFor('warning'));
                else toast('تغییرات در درخت ذخیره شد.', '✓');
                window.requestAnimationFrame(function () { var updatedCanvas = $('#org-canvas'); if (updatedCanvas) { updatedCanvas.scrollLeft = savedView.left; updatedCanvas.scrollTop = savedView.top; } drawOrgWires(); });
            })
            .catch(function (error) { state.submitting = false; $('.organization-workspace').classList.remove('org-ajax-saving'); toast(error.message || 'ذخیرهٔ تغییرات انجام نشد.', iconFor('warning')); });
    };

    var closeConfirm = function () {
        $('#org-confirm-backdrop').classList.remove('show');
        state.confirm = null;
    };
    var askConfirm = function (options) {
        state.confirm = options.onConfirm;
        $('#org-confirm-icon').innerHTML = options.icon || iconFor('warning');
        $('#org-confirm-title').textContent = options.title || 'تأیید عملیات';
        $('#org-confirm-message').textContent = options.message || 'آیا مطمئن هستید؟';
        $('#org-confirm-details').innerHTML = options.details || '';
        $('#org-confirm-ok').textContent = options.okText || 'تأیید';
        $('#org-confirm-ok').className = 'org-btn ' + (options.danger ? 'org-btn-danger' : 'org-btn-success');
        $('#org-confirm-backdrop').classList.add('show');
    };

    var closeModal = function () {
        $('#org-modal-backdrop').classList.remove('show');
        state.modal = null;
    };
    var openModal = function (options) {
        state.modal = { title: options.title, subtitle: options.subtitle || 'از فهرست زیر انتخاب کنید', icon: options.icon || iconFor('user'), users: options.users || [], multi: !!options.multi, poolOnly: !!options.poolOnly, custom: !!options.custom, currentUser: options.currentUser || null, selected: [], search: '', onPick: options.onPick, onConfirm: options.onConfirm };
        var backdrop = $('#org-modal-backdrop');
        backdrop.classList.toggle('single', !state.modal.multi);
        $('#org-modal-title').textContent = state.modal.title;
        $('#org-modal-subtitle').textContent = state.modal.subtitle;
        $('#org-modal-icon').innerHTML = state.modal.icon;
        $('#org-modal-search').value = '';
        $('#org-modal-preview').innerHTML = state.modal.currentUser ? '<div class="org-position-preview"><span class="org-avatar-small">' + esc(initial(state.modal.currentUser.name)) + '</span><span><small>موقعیت فعلی</small><b>' + esc(state.modal.currentUser.name) + '</b><em>' + esc(positionOf(state.modal.currentUser)) + '</em></span></div>' : '';
        $('#org-modal-hint').hidden = state.modal.multi;
        $('#org-modal-backdrop').classList.add('show');
        renderModal();
        window.setTimeout(function () { $('#org-modal-search').focus(); }, 50);
    };
    var renderModal = function () {
        if (!state.modal) return;
        var query = normalize(state.modal.search);
        var list = state.modal.users.filter(function (item) { return !query || normalize(item.name).indexOf(query) !== -1; }).slice(0, 200);
        var body = $('#org-modal-body');
        if (!list.length) {
            body.innerHTML = '<div class="org-modal-empty">?<br>موردی یافت نشد</div>';
        } else {
            body.innerHTML = list.map(function (item) {
                var selected = state.modal.selected.indexOf(Number(item.id)) !== -1;
                var tag = state.modal.custom ? '' : '<span class="org-position-tag ' + (roleOf(item) === 'pool' ? 'pool' : '') + '">' + esc(positionOf(item)) + '</span>';
                var meta = !state.modal.custom ? esc(sourceLabel(item)) : '';
                return '<div class="org-user-item ' + (selected ? 'selected' : '') + '" data-org-modal-user="' + Number(item.id) + '">' + (state.modal.multi ? '<span class="org-user-check" role="checkbox" aria-checked="' + (selected ? 'true' : 'false') + '">' + (selected ? '✓' : '') + '</span>' : '') + '<span class="org-user-avatar">' + esc(initial(item.name)) + '</span><span class="org-user-info"><b>' + esc(item.name) + '</b><small>' + meta + '</small></span>' + tag + (state.modal.multi ? '' : '<span class="org-pick-arrow">‹</span>') + '</div>';
            }).join('');
        }
        var count = state.modal.selected.length;
        $('#org-modal-info').innerHTML = count ? '<b>' + count + ' مورد</b> انتخاب شده' : 'موردی انتخاب نشده';
        $('#org-modal-confirm').disabled = count === 0;
        $$('#org-modal-body [data-org-modal-user]').forEach(function (row) {
            row.addEventListener('click', function () {
                var id = Number(row.getAttribute('data-org-modal-user'));
                var selected = state.modal.users.find(function (item) { return Number(item.id) === id; });
                if (!selected) return;
                if (!state.modal.multi) {
                    var callback = state.modal.onPick;
                    closeModal();
                    if (callback) callback(selected);
                    return;
                }
                var index = state.modal.selected.indexOf(id);
                if (index === -1) state.modal.selected.push(id); else state.modal.selected.splice(index, 1);
                renderModal();
            });
        });
    };

    var renderLeftSidebar = function () {
        var ceo = root();
        var deputies = ceo ? children(ceo.id, 'deputy') : [];
        var departments = allDepartments();
        var expertCount = users.filter(function (item) { return roleOf(item) === 'expert'; }).length;
        var freeCount = poolUsers().length;
        var assigned = users.length - freeCount;
        var progress = users.length ? Math.round(assigned * 100 / users.length) : 0;
        var stat = function (key, icon, label, number) { return '<button class="org-stat ' + (state.filterStat === key ? 'active' : '') + '" data-org-filter="' + key + '"><span class="org-stat-icon ' + key + '">' + iconFor(icon) + '</span><span>' + esc(label) + '</span><b>' + number + '</b></button>'; };
        var quick = function (type, label, action, tone) { return '<button class="org-big-add ' + (tone || '') + '" data-org-action="' + action + '"><span class="org-action-symbol">' + iconFor(type) + '</span><span>' + label + '</span></button>'; };
        var quickUnits = quick('deputy', 'افزودن معاونت', 'add-deputy') + (root() ? quick('department', 'افزودن اداره', 'quick-department', 'green') : '<p class="org-side-note">برای ساخت اداره، ابتدا ریشهٔ سازمان را بسازید.</p>') + (deputies.length ? quick('expert', 'افزودن کارشناس', 'quick-expert', 'amber') : '<p class="org-side-note">کارشناس بعد از ساخت معاونت اضافه می‌شود؛ می‌تواند زیر معاونت یا هر اداره باشد.</p>');
        $('#org-sidebar').innerHTML = '<section class="org-sidebar-section"><h4>آمار سازمان</h4>' + stat('ceo', 'ceo', rootLabel(), ceo && ceo.managerId ? 1 : 0) + stat('dep', 'deputy', 'معاونت', deputies.length) + stat('dept', 'department', 'اداره', departments.length) + stat('expert', 'expert', 'کارشناس', expertCount) + stat('pool', 'pool', 'آزاد', freeCount) + '<div class="org-progress"><div><span>پیشرفت چیدمان</span><b>' + progress + '٪</b></div><i style="width:' + progress + '%"></i></div></section>' +
            '<section class="org-sidebar-section"><h4>افزودن سریع</h4>' + quickUnits + '</section><section class="org-sidebar-section"><h4>راهنما</h4><div class="org-help"><b>کلیک کارت</b> = تغییر مسئول<br>⋮ <b>منوی کارت</b> = عملیات بیشتر<br>Space + کشیدن <b>پس‌زمینه</b> = جابه‌جایی نمودار<br>' + (data.sortEnabled ? '⠿ <b>کشیدن دستگیره</b> = مرتب‌سازی معاونت‌ها' : 'برای ذخیرهٔ ترتیب معاونت‌ها مهاجرت افزایشی sort_order لازم است') + '<br>↕ <b>کشیدن کاربر</b> = انتساب سریع<br>↶ <b>بازگشت</b> = آخرین عملیات</div></section>';
    };
    var renderPoolSidebar = function () {
        var list;
        var title = 'کاربران آزاد';
        if (state.filterStat === 'ceo') { list = root() && root().managerId ? [person(root().managerId)] : []; title = rootLabel(); }
        else if (state.filterStat === 'dep') { list = users.filter(function (item) { return roleOf(item) === 'dep'; }); title = 'مدیران معاونت'; }
        else if (state.filterStat === 'dept') { list = users.filter(function (item) { return roleOf(item) === 'dept'; }); title = 'رؤسای ادارات'; }
        else if (state.filterStat === 'expert') { list = users.filter(function (item) { return roleOf(item) === 'expert'; }); title = 'کارشناسان'; }
        else list = poolUsers();
        list = list.filter(Boolean);
        var filtered = list.filter(function (item) { return !state.poolSearch || normalize(item.name).indexOf(normalize(state.poolSearch)) !== -1; });
         $('#org-pool-sidebar').innerHTML = '<div class="org-pool-header"><div><b>' + title + '</b><span>' + list.length + '</span></div><small>' + (state.filterStat ? 'برای بازگشت، دوباره روی آمار کلیک کنید' : 'دامینی و محلی — افراد را بکشید و روی موقعیت رها کنید') + '</small>' + (state.filterStat ? '<button class="org-clear-filter" data-org-filter="clear">حذف فیلتر</button>' : '') + '</div><div class="org-pool-search"><input id="org-pool-search" type="search" value="' + esc(state.poolSearch) + '" placeholder="جستجوی کاربر..."></div><div class="org-pool-list" id="org-pool-list">' + (filtered.length ? filtered.map(function (item) { return '<div class="org-pool-user" draggable="true" data-pool-user="' + item.id + '"><span class="org-pool-avatar">' + esc(initial(item.name)) + '</span><span><b>' + esc(item.name) + '</b><small>' + esc(sourceLabel(item)) + '</small></span></div>'; }).join('') : '<div class="org-pool-empty">—<br>' + (state.filterStat ? 'موردی نیست' : 'همه تخصیص یافتند') + '</div>') + '</div>';
        var search = $('#org-pool-search');
        if (search) search.addEventListener('input', function (event) { state.poolSearch = event.target.value; renderPoolSidebar(); });
        $$('#org-pool-list [data-pool-user]').forEach(function (item) {
            item.addEventListener('dragstart', function (event) { event.dataTransfer.setData('text/plain', JSON.stringify({ userId: Number(item.getAttribute('data-pool-user')) })); item.classList.add('dragging'); });
            item.addEventListener('dragend', function () { item.classList.remove('dragging'); });
        });
    };

    var nodeMatch = function (item) { return match(item) ? ' is-match' : ''; };
    var nodeMenuButton = function (unitId) { return data.canManage ? '<button class="org-node-menu-btn" data-org-menu="open" data-unit-id="' + unitId + '">⋮</button>' : ''; };
    var renderCeo = function () {
        var ceo = root();
        var manager = ceo && person(ceo.managerId);
        if (!manager) return '<div class="org-node org-empty-slot org-ceo-slot" data-unit="' + ceo.id + '" data-drop="ceo" data-org-action="pick-ceo"><span class="org-empty-icon">' + iconFor('ceo') + '</span><b>تعیین ' + esc(rootLabel()) + '</b><small class="org-company-node-name">' + esc(data.companyName || '') + '</small><small>کلیک یا کشیدن کاربر</small>' + (data.canManage ? '<button class="org-node-add" data-org-action="add-deputy">+ افزودن معاونت</button>' : '') + '</div>';
        return '<div class="org-node org-ceo' + nodeMatch(manager) + '" data-unit="' + ceo.id + '" data-drop="ceo"><div class="org-node-menu-wrap">' + nodeMenuButton(ceo.id) + '</div><div class="org-avatar">' + esc(initial(manager.name)) + '</div><div class="org-node-name">' + esc(manager.name) + '</div><div class="org-node-title"><span class="org-title-icon">' + iconFor('manager') + '</span> ' + esc(rootLabel()) + '</div><div class="org-company-node-name">' + esc(data.companyName || '') + '</div><div class="org-node-count">' + iconFor('deputy') + ' ' + children(ceo.id, 'deputy').length + ' معاونت</div>' + (data.canManage ? '<button class="org-node-add" data-org-action="add-deputy">+ افزودن معاونت</button>' : '') + '</div>';
    };
    var renderLiaisonSide = function (side) {
        var sideLabel = side === 'right' ? 'راست' : 'چپ';
        var item = liaisonAt(side);
        if (!item) {
            if (!data.canManage) return '';
            return '<div class="org-node org-empty-slot org-liaison-slot" data-org-action="add-liaison" data-liaison-side="' + side + '"><span class="org-empty-icon">' + iconFor('deputy') + '</span><b>واحد مستقل (نمایندگی)</b><small>سمت ' + sideLabel + ' ' + esc(rootLabel()) + ' — کلیک کنید</small></div>';
        }
        var manager = person(item.managerId), experts = expertsIn(item.id);
        var extra = !manager && data.canManage ? '<button class="org-node-add" data-org-action="pick-manager" data-unit-id="' + item.id + '">' + iconFor('manager') + ' تعیین نماینده</button>' : '';
        var title = manager ? iconFor('manager') + ' نمایندهٔ ' + esc(item.name) : iconFor('deputy') + ' بدون نماینده';
        var expertRoster = experts.length ? '<div class="org-liaison-experts"><b>' + iconFor('expert') + ' کارشناسان</b><span>' + experts.map(function (expert) { return esc(expert.name); }).join('، ') + '</span></div>' : '';
        var expertAdd = data.canManage ? '<button class="org-node-add org-node-add-expert" data-org-action="add-experts" data-unit-id="' + item.id + '">+ افزودن کارشناس</button>' : '';
        return '<div class="org-node org-liaison-node' + nodeMatch(item) + (manager ? nodeMatch(manager) : '') + '" data-unit="' + item.id + '" data-unit-id="' + item.id + '" data-drop="liaison"><div class="org-node-menu-wrap">' + nodeMenuButton(item.id) + '</div><div class="org-avatar ' + (!manager ? 'org-warning-avatar' : '') + '">' + (manager ? esc(initial(manager.name)) : iconFor('deputy')) + '</div><div class="org-node-name">' + esc(manager ? manager.name : item.name) + '</div><div class="org-node-title ' + (!manager ? 'org-warning-text' : '') + '">' + title + '</div><div class="org-node-count">واحد هم‌تراز ' + esc(rootLabel()) + '</div>' + expertRoster + extra + expertAdd + '</div>';
    };
    var renderLiaisonBand = function () {
        var right = renderLiaisonSide('right');
        var left = renderLiaisonSide('left');
        if (!right && !left) return '';
        return '<div class="org-liaison-band" aria-label="واحدهای مستقل کنار مدیر شرکت"><div class="org-liaison-pos org-liaison-side-right">' + right + '</div><div class="org-liaison-pos org-liaison-side-left">' + left + '</div></div>';
    };
    var renderDeputy = function (item) {
        var manager = person(item.managerId);
        var extra = !manager && data.canManage ? '<button class="org-node-add" data-org-action="pick-manager" data-unit-id="' + item.id + '">' + iconFor('manager') + ' تعیین مدیر</button>' : '';
        var title = manager ? iconFor('manager') + ' مدیر ' + esc(item.name) : iconFor('deputy') + ' بدون مدیر';
        var deptCount = children(item.id, 'department').length;
        var expertCount = expertsIn(item.id).length;
        var addButtons = data.canManage
            ? (data.sortEnabled && root() && Number(item.parentId) === Number(root().id) ? '<button class="org-sort-handle" type="button" data-deputy-drag="' + item.id + '" title="کشیدن برای مرتب‌سازی معاونت‌ها" aria-label="مرتب‌سازی ' + esc(item.name) + '">⠿</button>' : '') + '<div class="org-node-add-row"><button class="org-node-add" data-org-action="add-deputy">+ افزودن معاونت جدید</button><button class="org-node-add org-node-add-expert" data-org-action="add-experts" data-unit-id="' + item.id + '">+ افزودن کارشناس</button></div>'
            : '';
        return '<div class="org-node org-deputy' + nodeMatch(item) + (manager ? nodeMatch(manager) : '') + '" data-unit="' + item.id + '" data-drop="deputy"><div class="org-node-menu-wrap">' + nodeMenuButton(item.id) + '</div><div class="org-avatar ' + (!manager ? 'org-warning-avatar' : '') + '">' + (manager ? esc(initial(manager.name)) : iconFor('deputy')) + '</div><div class="org-node-name">' + esc(manager ? manager.name : item.name) + '</div><div class="org-node-title ' + (!manager ? 'org-warning-text' : '') + '">' + title + '</div><div class="org-node-count">' + iconFor('department') + ' ' + deptCount + ' اداره • ' + iconFor('expert') + ' ' + expertCount + ' کارشناس</div>' + extra + addButtons + '</div>';
    };
    var renderDepartment = function (item) {
        var manager = person(item.managerId);
        var experts = expertsIn(item.id);
        var extra = !manager && data.canManage ? '<button class="org-node-add" data-org-action="pick-manager" data-unit-id="' + item.id + '">' + iconFor('manager') + ' تعیین رئیس</button>' : '';
        var title = manager ? iconFor('manager') + ' رئیس ' + esc(item.name) : iconFor('department') + ' بدون رئیس';
        return '<div class="org-node org-department' + nodeMatch(item) + (manager ? nodeMatch(manager) : '') + '" data-unit="' + item.id + '" data-drop="department"><div class="org-node-menu-wrap">' + nodeMenuButton(item.id) + '</div><div class="org-avatar ' + (!manager ? 'org-warning-avatar' : '') + '">' + (manager ? esc(initial(manager.name)) : iconFor('department')) + '</div><div class="org-node-name">' + esc(manager ? manager.name : item.name) + '</div><div class="org-node-title ' + (!manager ? 'org-warning-text' : '') + '">' + title + '</div><div class="org-node-count">' + iconFor('expert') + ' ' + experts.length + ' کارشناس • ' + children(item.id, 'department').length + ' ادارهٔ زیرمجموعه</div>' + (data.canManage ? '<button class="org-node-add org-node-add-expert" data-org-action="add-experts" data-unit-id="' + item.id + '">+ افزودن کارشناس</button>' : '') + extra + '</div>';
    };
    var expertNode = function (item) {
        var experts = expertsIn(item.id);
        if (!experts.length) return '<div class="org-node org-empty-slot org-empty-small" data-drop="experts" data-unit-id="' + item.id + '" data-org-action="add-experts"><span class="org-empty-icon">' + iconFor('expert') + '</span><b>افزودن کارشناس</b><small>کلیک یا کشیدن کاربر</small></div>';
        return '<div class="org-node org-experts-group" data-drop="experts" data-unit-id="' + item.id + '" data-org-action="manage-experts"><div class="org-node-menu-wrap">' + nodeMenuButton(item.id) + '</div><div class="org-avatar org-expert-avatar">' + iconFor('expert') + '</div><div class="org-node-name">' + experts.length + ' کارشناس</div><div class="org-node-title">' + experts.slice(0, 3).map(function (personItem) { return esc(personItem.name.split(' ')[0]); }).join(' • ') + (experts.length > 3 ? ' +' + (experts.length - 3) : '') + '</div><div class="org-node-count">مدیریت کارشناسان</div></div>';
    };
    var renderExperts = function (item) {
        var experts = expertsIn(item.id);
        if (!experts.length) return '';
        var body = experts.length ? experts.slice(0, 5).map(function (personItem) {
            return '<div class="org-expert-person' + (match(personItem) ? ' is-match' : '') + '" title="' + esc(personItem.name) + '"><span>' + esc(initial(personItem.name)) + '</span>' + esc(personItem.name) + '</div>';
        }).join('') + (experts.length > 5 ? '<div class="org-expert-more">و ' + (experts.length - 5) + ' نفر دیگر</div>' : '') : '';
        return '<div class="org-experts-group" data-drop="experts" data-unit-id="' + item.id + '"><div class="org-expert-heading">' + iconFor('expert') + '<b>کارشناسان این واحد</b><span>' + experts.length + '</span></div>' + body + (data.canManage && experts.length ? '<button type="button" class="org-expert-manage" data-org-action="manage-experts" data-unit-id="' + item.id + '">مدیریت / عزل</button>' : '') + '</div>';
    };
    var branchColorAt = function (index) { return 'hsl(' + ((index * 137.508) % 360).toFixed(1) + ' 62% 42%)'; };
    var renderUnitBranch = function (item, ancestors, branchColor) {
        var path = Array.isArray(ancestors) ? ancestors.slice() : [];
        if (path.indexOf(Number(item.id)) !== -1) return '<div class="org-cycle-warning">پیوند والد/فرزند حلقه‌ای است؛ این کارت برای جلوگیری از توقف صفحه متوقف شد.</div>';
        path.push(Number(item.id));
        var card = item.type === 'deputy' ? renderDeputy(item) : renderDepartment(item);
        var subunits = children(item.id).filter(function (child) { return child.type === 'department'; });
        var descendants = subunits.map(function (child) { return renderUnitBranch(child, path, branchColor); }).join('');
        var colorStyle = branchColor ? ' style="--org-branch-color:' + branchColor + '"' : '';
        return '<div class="org-branch" data-branch-id="' + item.id + '"' + colorStyle + '><div class="org-branch-card">' + card + renderExperts(item) + '</div>' + (descendants ? '<div class="org-children">' + descendants + '</div>' : '') + '</div>';
    };
    var renderCeoBranch = function (ceo, deputies) {
        var batches = [];
        for (var i = 0; i < deputies.length; i += 4) batches.push(deputies.slice(i, i + 4));
        var rows = batches.map(function (batch, index) {
            return '<div class="org-deputy-row org-root-unit-row" data-row="' + index + '">' + batch.map(function (item) { return renderUnitBranch(item, [], branchColorAt(deputies.indexOf(item))); }).join('') + '</div>';
        }).join('');
        var directDepartments = children(ceo.id, 'department');
        var reachable = Object.create(null);
        var markTree = function (item) { var id = Number(item.id); if (reachable[id]) return; reachable[id] = true; children(id, 'department').forEach(markTree); };
        deputies.forEach(markTree); directDepartments.forEach(markTree);
        var orphans = units.filter(function (item) { return ['deputy', 'department'].indexOf(item.type) !== -1 && !reachable[Number(item.id)]; });
        var orphanIds = orphans.map(function (item) { return Number(item.id); });
        var orphanRoots = orphans.filter(function (item) { return orphanIds.indexOf(Number(item.parentId)) === -1; });
        var covered = Object.create(null);
        var markOrphanTree = function (item) { var id = Number(item.id); if (covered[id]) return; covered[id] = true; children(id, 'department').filter(function (child) { return orphanIds.indexOf(Number(child.id)) !== -1; }).forEach(markOrphanTree); };
        var orphanMarkup = '', orphanIndex = 0;
        while (Object.keys(covered).length < orphans.length) {
            var next = orphanRoots.find(function (item) { return !covered[Number(item.id)]; }) || orphans.find(function (item) { return !covered[Number(item.id)]; });
            if (!next) break;
            orphanMarkup += renderUnitBranch(next, [], branchColorAt(deputies.length + directDepartments.length + orphanIndex++));
            markOrphanTree(next);
        }
        var directMarkup = directDepartments.length ? '<div class="org-root-department-row org-root-unit-row">' + directDepartments.map(function (item, index) { return renderUnitBranch(item, [], branchColorAt(deputies.length + index)); }).join('') + '</div>' : '';
        var orphanSection = orphanMarkup ? '<section class="org-orphan-units"><h3>واحدهای خارج از مسیر اصلی یا دارای والد ناسازگار</h3><p>رکوردها حفظ شده‌اند؛ این بخش برای جلوگیری از پنهان‌شدن داده‌های قدیمی/ناقص است. با ویرایش والد می‌توان آن‌ها را به مسیر معتبر منتقل کرد.</p><div class="org-orphan-row">' + orphanMarkup + '</div></section>' : '';
        var leftLiaison = rootType() === 'company_manager' ? '' : renderLiaisonSide('left');
        var rightLiaison = rootType() === 'company_manager' ? '' : renderLiaisonSide('right');
        var markup = '<div class="org-chart-stage"><svg class="org-wires" id="org-wires" aria-hidden="true"></svg><div class="org-top-row"><div class="org-liaison-cell physical-left">' + leftLiaison + '</div><div class="org-ceo-cell">' + renderCeo() + '</div><div class="org-liaison-cell physical-right">' + rightLiaison + '</div></div><div class="org-deputy-rows">' + (rows || (data.canManage ? '<div class="org-empty-branch-hint">از «افزودن معاونت» برای شروع ساختار استفاده کنید.</div>' : '')) + directMarkup + orphanSection + '</div></div>';
        return markup;
    };
    var drawOrgWires = function () {
        var stage = $('.org-chart-stage'), svg = $('#org-wires');
        if (!stage || !svg) return;
        var rect = stage.getBoundingClientRect(), scaleX = stage.offsetWidth ? rect.width / stage.offsetWidth : 1, scaleY = stage.offsetHeight ? rect.height / stage.offsetHeight : 1;
        scaleX = Math.max(.01, scaleX); scaleY = Math.max(.01, scaleY);
        var width = Math.max(stage.scrollWidth, stage.offsetWidth), height = Math.max(stage.scrollHeight, stage.offsetHeight);
        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height); svg.setAttribute('width', width); svg.setAttribute('height', height); svg.innerHTML = '';
        var rel = function (element) { var r = element.getBoundingClientRect(); return { x: ((r.left + r.right) / 2 - rect.left) / scaleX, top: (r.top - rect.top) / scaleY, bottom: (r.bottom - rect.top) / scaleY, left: (r.left - rect.left) / scaleX, right: (r.right - rect.left) / scaleX }; };
        var path = function (d, cls, color) { var p = document.createElementNS('http://www.w3.org/2000/svg', 'path'); p.setAttribute('d', d); p.setAttribute('class', cls || 'org-wire'); if (color) p.style.stroke = color; svg.appendChild(p); };
        var ceoCard = stage.querySelector('.org-ceo');
        if (!ceoCard) ceoCard = stage.querySelector('.org-ceo-slot');
        var rows = $$('.org-deputy-row, .org-root-department-row');
        if (ceoCard && rows.length) {
            var c = rel(ceoCard), rowRects = rows.map(function (row) { var cards = Array.prototype.slice.call(row.querySelectorAll(':scope > .org-branch > .org-branch-card > .org-node')); if (!cards.length) return null; var rs = cards.map(rel); return { top: Math.min.apply(null, rs.map(function (x) { return x.top; })), left: Math.min.apply(null, rs.map(function (x) { return x.x; })), right: Math.max.apply(null, rs.map(function (x) { return x.x; })), centers: rs.map(function (x) { return x.x; }), cards: cards }; }).filter(Boolean);
            if (rowRects.length) {
                var trunkX = width - 15, firstY = rowRects[0].top - 19, lastY = rowRects[rowRects.length - 1].top - 19;
                path('M ' + c.x + ' ' + c.bottom + ' V ' + firstY + ' H ' + trunkX, 'org-wire org-wire-shared');
                if (rowRects.length > 1) path('M ' + trunkX + ' ' + firstY + ' V ' + lastY, 'org-wire org-wire-shared');
                rowRects.forEach(function (row) { path('M ' + trunkX + ' ' + (row.top - 19) + ' H ' + row.left, 'org-wire org-wire-shared'); row.centers.forEach(function (x, cardIndex) { var branch = row.cards[cardIndex].closest('.org-branch'), color = branch ? getComputedStyle(branch).getPropertyValue('--org-branch-color').trim() : ''; path('M ' + x + ' ' + (row.top - 19) + ' V ' + row.top, 'org-wire org-wire-shared', color); }); });
            }
        }
        $$('.org-branch').forEach(function (branch) {
            var parent = branch.querySelector(':scope > .org-branch-card > .org-node');
            var childrenRow = branch.querySelector(':scope > .org-children');
            if (!parent) return;
            var p = rel(parent), expertCard = branch.querySelector(':scope > .org-branch-card > .org-experts-group');
            if (expertCard) { var e = rel(expertCard); path('M ' + p.x + ' ' + p.bottom + ' V ' + e.top, 'org-wire org-wire-expert', getComputedStyle(branch).getPropertyValue('--org-branch-color').trim()); }
            if (!childrenRow) return;
            var childCards = Array.prototype.slice.call(childrenRow.querySelectorAll(':scope > .org-branch > .org-branch-card > .org-node'));
            if (!childCards.length) return;
            var kids = childCards.map(rel), joinY = Math.min.apply(null, kids.map(function (x) { return x.top; })) - 18;
            var branchColor = getComputedStyle(branch).getPropertyValue('--org-branch-color').trim();
            path('M ' + p.x + ' ' + p.bottom + ' V ' + joinY + ' H ' + Math.min.apply(null, kids.map(function (x) { return x.x; })) + (kids.length > 1 ? ' H ' + Math.max.apply(null, kids.map(function (x) { return x.x; })) : ''), 'org-wire', branchColor);
            kids.forEach(function (kid) { path('M ' + kid.x + ' ' + joinY + ' V ' + kid.top, 'org-wire', branchColor); });
        });
    };
    var bindDeputyReorder = function () {
        $$('[data-deputy-drag]').forEach(function (handle) {
            handle.addEventListener('pointerdown', function (event) {
                if (event.button !== undefined && event.button !== 0) return;
                event.preventDefault();
                var id = Number(handle.getAttribute('data-deputy-drag'));
                state.deputyDrag = { id: id, handle: handle };
                var branch = handle.closest('.org-branch'); if (branch) branch.classList.add('is-dragging');
                document.addEventListener('pointermove', onDeputyMove, { passive: false });
                document.addEventListener('pointerup', onDeputyUp, { once: true });
                document.addEventListener('pointercancel', cancelDeputyDrag, { once: true });
            });
        });
    };
    var onDeputyMove = function (event) {
        if (!state.deputyDrag) return;
        event.preventDefault();
        $$('.org-drop-before').forEach(function (item) { item.classList.remove('org-drop-before'); });
        var target = document.elementFromPoint(event.clientX, event.clientY); target = target && target.closest('.org-branch[data-branch-id]');
        if (target && target.closest('.org-deputy-row') && Number(target.getAttribute('data-branch-id')) !== state.deputyDrag.id) target.classList.add('org-drop-before');
    };
    var clearDeputyDrag = function () {
        if (state.deputyDrag) { var branch = state.deputyDrag.handle.closest('.org-branch'); if (branch) branch.classList.remove('is-dragging'); }
        $$('.org-drop-before').forEach(function (item) { item.classList.remove('org-drop-before'); });
        state.deputyDrag = null; document.removeEventListener('pointermove', onDeputyMove); document.removeEventListener('pointerup', onDeputyUp); document.removeEventListener('pointercancel', cancelDeputyDrag);
    };
    var onDeputyUp = function (event) {
        if (!state.deputyDrag) return;
        var sourceId = state.deputyDrag.id, hit = document.elementFromPoint(event.clientX, event.clientY), target = hit && hit.closest('.org-branch[data-branch-id]');
        var current = children(root() ? root().id : 0, 'deputy').map(function (item) { return Number(item.id); });
        var targetId = target && target.closest('.org-deputy-row') ? Number(target.getAttribute('data-branch-id')) : 0;
        clearDeputyDrag();
        var from = current.indexOf(sourceId), to = current.indexOf(targetId);
        if (from < 0 || to < 0 || from === to) return;
        current.splice(to, 0, current.splice(from, 1)[0]);
        current.forEach(function (id, index) { var node = unit(id); if (node) node.sortOrder = (index + 1) * 10; });
        renderChart();
        if (data.canManage) submit('sort_org_deputies', { unit_ids: current });
    };
    var cancelDeputyDrag = function () { clearDeputyDrag(); };
    var renderChart = function () {
        var ceo = root(), wrap = $('#org-chart-wrap'), deputies = ceo ? children(ceo.id, 'deputy') : [];
        if (!ceo) {
            wrap.innerHTML = '<div class="org-start-screen"><div class="org-start-icon">' + iconFor('start') + '</div><h2>شروع ساختار سازمانی</h2><p>در سه مرحله ساده، ساختار سازمان را بسازید.</p><div class="org-start-steps"><span><b>۱</b> ' + esc(rootLabel()) + '</span><span><b>۲</b> معاونت</span><span><b>۳</b> اداره یا کارشناس</span></div><button class="org-btn org-btn-primary" data-org-action="pick-ceo">' + esc('تعیین ' + rootLabel()) + '</button><button class="org-btn org-btn-ghost" data-org-action="add-deputy">افزودن معاونت بدون مدیر</button></div>';
            return;
        }
        wrap.innerHTML = renderCeoBranch(ceo, deputies);
        wrap.style.transform = 'scale(' + state.zoom + ')';
        if (!state.initialCentered && !state.printSnapshot) { state.initialCentered = true; window.requestAnimationFrame(function () { var rootCard = wrap.querySelector('.org-ceo, .org-ceo-slot'); if (rootCard) rootCard.scrollIntoView({ block: 'start', inline: 'center' }); }); }
        $('#org-zoom-level').textContent = Math.round(state.zoom * 100) + '٪';
        bindDropTargets(); bindDeputyReorder();
        var currentStage = $('.org-chart-stage');
        if (window.ResizeObserver && currentStage) {
            if (!state.wireObserver) state.wireObserver = new ResizeObserver(function () { window.requestAnimationFrame(drawOrgWires); });
            if (state.observedStage && state.observedStage !== currentStage) state.wireObserver.unobserve(state.observedStage);
            state.observedStage = currentStage; state.wireObserver.observe(currentStage);
        }
        window.requestAnimationFrame(function () { drawOrgWires(); });
    };

    var openManagerPicker = function (target) {
        var current = target.managerId ? person(target.managerId) : null;
        openModal({ icon: iconFor(target.type), title: (current ? 'تغییر ' : 'تعیین ') + ((target.type === 'ceo' || target.type === 'company_manager') ? rootLabel() : target.type === 'deputy' ? 'مدیر «' + target.name + '»' : target.type === 'liaison' ? 'نمایندهٔ «' + target.name + '»' : 'رئیس «' + target.name + '»'), subtitle: 'روی کاربر موردنظر کلیک کنید', users: users, currentUser: current, onPick: function (selected) { submit('assign_org_manager', { target_type: target.type, unit_id: target.id, user_id: selected.id }); } });
    };
    var addExperts = function (unitId) {
        var target = unit(unitId);
        if (!target) return;
        var hostKind = target.type === 'deputy' ? 'معاونت' : (target.type === 'liaison' ? ('واحد هم‌تراز ' + rootLabel()) : 'اداره');
        openModal({ icon: iconFor('expert'), title: 'افزودن کارشناس به ' + hostKind + ' «' + target.name + '»', subtitle: 'کاربران دامینی و محلی آزاد را با تیک انتخاب کنید', users: poolUsers(), poolOnly: true, multi: true, onConfirm: function (selected) { submit('save_org_assign_users', { member_unit_id: unitId, user_ids: selected.map(function (item) { return item.id; }) }); } });
    };
    var quickAddDepartment = function () {
        var parents = units.filter(function (item) { return ['deputy', 'department', rootType()].indexOf(item.type) !== -1; });
        if (!parents.length) return toast('ابتدا معاونت بسازید', iconFor('warning'));
        if (parents.length === 1) return showInlineAddDepartment(parents[0].id);
        openModal({ icon: iconFor('department'), title: 'افزودن اداره', subtitle: 'معاونت یا ادارهٔ والد را انتخاب کنید', users: parents.map(function (item) { return { id: item.id, name: item.name, code: children(item.id, 'department').length + ' اداره', role: 'choice' }; }), custom: true, onPick: function (selected) { showInlineAddDepartment(selected.id); } });
    };
    var quickAddExpert = function () {
        var hosts = expertHosts();
        if (!hosts.length) return toast('ابتدا معاونت بسازید', iconFor('warning'));
        if (hosts.length === 1) return addExperts(hosts[0].id);
        openModal({ icon: iconFor('expert'), title: 'افزودن کارشناس', subtitle: 'معاونت یا اداره مقصد را انتخاب کنید', users: hosts.map(function (item) {
            var kind = item.type === 'deputy' ? 'معاونت' : (item.type === 'liaison' ? ('واحد هم‌تراز ' + rootLabel()) : 'اداره');
            var extra = item.type === 'deputy'
                ? (children(item.id, 'department').length + ' اداره • ' + expertsIn(item.id).length + ' کارشناس')
                : (item.type === 'liaison' ? ('هم‌تراز ' + rootLabel() + ' • ' + expertsIn(item.id).length + ' کارشناس') : ((unit(item.parentId) ? unit(item.parentId).name : 'اداره') + ' • ' + expertsIn(item.id).length + ' کارشناس'));
            return { id: item.id, name: item.name, code: kind + ' — ' + extra, role: 'choice' };
        }), custom: true, onPick: function (selected) { addExperts(selected.id); } });
    };
    var showInline = function (type, parentId, side) {
        var anchor = type === 'deputy' ? ($('.org-node.org-ceo') || $('.org-node.org-ceo-slot'))
            : type === 'liaison' ? $('.org-liaison-slot[data-liaison-side="' + side + '"]')
            : $('.org-node[data-unit="' + parentId + '"]');
        if (!anchor) return;
        $$('.org-inline-form-wrap').forEach(function (item) { item.remove(); });
        var wrapper = document.createElement('div');
        wrapper.className = 'org-inline-form-wrap';
        wrapper.innerHTML = type === 'deputy' ? '<div class="org-inline-form"><label>نام معاونت *</label><input data-inline-field="name" placeholder="معاونت فناوری"><label>کد اختیاری</label><input data-inline-field="code" placeholder="DEP-01"><div><button class="org-btn org-btn-success" data-inline-submit="deputy">✓ افزودن</button><button class="org-btn org-btn-ghost" data-inline-cancel>انصراف</button></div></div>'
            : type === 'liaison' ? '<div class="org-inline-form"><label>نام واحد مستقل *</label><input data-inline-field="name" placeholder="مثلاً حراست مجموعه"><label>کد اختیاری</label><input data-inline-field="code" placeholder="LN-01"><div><button class="org-btn org-btn-success" data-inline-submit="liaison">✓ افزودن</button><button class="org-btn org-btn-ghost" data-inline-cancel>انصراف</button></div></div>'
            : '<div class="org-inline-form"><label>نام اداره *</label><input data-inline-field="name" placeholder="اداره فناوری اطلاعات"><div><button class="org-btn org-btn-success" data-inline-submit="department">✓ افزودن</button><button class="org-btn org-btn-ghost" data-inline-cancel>انصراف</button></div></div>';
        anchor.appendChild(wrapper);
        wrapper.addEventListener('click', function (event) { event.stopPropagation(); });
        wrapper.querySelector('[data-inline-field="name"]').focus();
        wrapper.querySelectorAll('input').forEach(function (input) { input.addEventListener('keydown', function (event) { if (event.key === 'Escape') wrapper.remove(); if (event.key === 'Enter') wrapper.querySelector('[data-inline-submit]').click(); }); });
        wrapper.querySelector('[data-inline-submit]').addEventListener('click', function () {
            var name = wrapper.querySelector('[data-inline-field="name"]').value.trim();
            var code = wrapper.querySelector('[data-inline-field="code"]');
            if (!name) return toast('نام الزامی است', iconFor('warning'));
            var fields = { node_type: type, unit_name: name, unit_code: code ? code.value.trim() : '', parent_id: type === 'deputy' ? root().id : (type === 'liaison' ? 0 : parentId), unit_manager_id: 0, is_primary_admin: 0 };
            if (type === 'liaison') fields.chart_side = side;
            submit('save_org_node', fields);
        });
        wrapper.querySelector('[data-inline-cancel]').addEventListener('click', function () { wrapper.remove(); });
    };
    // ویرایش نام، کد و (برای اداره) معاونت والد. مدیر فعلی و پرچم مدیر اصلیِ واحد دست‌نخورده می‌مانند.
    var showInlineEdit = function (target) {
        if (!target || !data.canManage) return;
        var node = $('.org-node[data-unit="' + target.id + '"]');
        if (!node) return;
        $$('.org-inline-form-wrap').forEach(function (item) { item.remove(); });
        var isDepartment = target.type === 'department';
        var blockedParents = descendantIds(target.id).concat([Number(target.id)]);
        var parentOptions = isDepartment ? units.filter(function (item) { return ['deputy', 'department', rootType()].indexOf(item.type) !== -1 && blockedParents.indexOf(Number(item.id)) === -1; }).map(function (item) {
            var level = 0, cursor = item, guard = 0; while (cursor && Number(cursor.parentId) > 0 && guard++ < 40) { level++; cursor = unit(cursor.parentId); }
            return '<option value="' + item.id + '"' + (Number(item.id) === Number(target.parentId) ? ' selected' : '') + '>' + '　'.repeat(Math.min(level, 8)) + esc(item.name) + '</option>';
        }).join('') : '';
        var wrapper = document.createElement('div');
        wrapper.className = 'org-inline-form-wrap';
        wrapper.innerHTML = '<div class="org-inline-form"><label>نام ' + (isDepartment ? 'اداره' : (target.type === 'liaison' ? 'واحد مستقل' : 'معاونت')) + ' *</label><input data-inline-field="name" value="' + esc(target.name) + '">'
            + '<label>کد اختیاری</label><input data-inline-field="code" value="' + esc(target.code || '') + '">'
            + (isDepartment ? '<label>معاونت والد</label><select data-inline-field="parent">' + parentOptions + '</select>' : '')
            + '<div><button class="org-btn org-btn-success" data-inline-submit="edit">✓ ذخیره تغییرات</button><button class="org-btn org-btn-ghost" data-inline-cancel>انصراف</button></div></div>';
        node.appendChild(wrapper);
        wrapper.addEventListener('click', function (event) { event.stopPropagation(); });
        wrapper.querySelector('[data-inline-field="name"]').focus();
        wrapper.querySelectorAll('input').forEach(function (input) { input.addEventListener('keydown', function (event) { if (event.key === 'Escape') wrapper.remove(); if (event.key === 'Enter') wrapper.querySelector('[data-inline-submit]').click(); }); });
        wrapper.querySelector('[data-inline-submit]').addEventListener('click', function () {
            var name = wrapper.querySelector('[data-inline-field="name"]').value.trim();
            var code = wrapper.querySelector('[data-inline-field="code"]').value.trim();
            var parentSelect = wrapper.querySelector('[data-inline-field="parent"]');
            if (!name) return toast('نام الزامی است', iconFor('warning'));
            var fields = {
                node_type: target.type,
                unit_id: target.id,
                unit_name: name,
                unit_code: code,
                parent_id: parentSelect ? parentSelect.value : target.parentId,
                unit_manager_id: target.managerId || 0
            };
            if (target.type === 'liaison') fields.chart_side = target.chartSide || 'right';
            // فقط وقتی پرچم «مدیر اصلیِ واحد» فعال است بفرست؛ این پرچم به‌هیچ‌وجه نقش ادمین سامانه نیست.
            if (target.primaryAdmin) fields.is_primary_admin = 1;
            submit('save_org_node', fields);
        });
        wrapper.querySelector('[data-inline-cancel]').addEventListener('click', function () { wrapper.remove(); });
    };
    var showInlineAddDeputy = function () { if (root()) showInline('deputy', 0); else toast('ریشهٔ سازمان هنوز ساخته نشده است', iconFor('warning')); };
    var showInlineAddDepartment = function (parentId) { showInline('department', parentId); };

    var openExpertManager = function (unitId) {
        var target = unit(unitId);
        if (!target) return;
        var experts = expertsIn(unitId);
        var backdrop = document.createElement('div');
        backdrop.className = 'org-modal-backdrop show';
        backdrop.innerHTML = '<section class="org-modal org-expert-manager"><header><div class="org-modal-icon org-expert-avatar">' + iconFor('expert') + '</div><div><h2>مدیریت کارشناسان «' + esc(target.name) + '»</h2><p>' + experts.length + ' نفر</p></div><button class="org-modal-close" type="button">×</button></header><div class="org-manage-list">' + (experts.length ? experts.map(function (item) { return '<div class="org-manage-row"><span class="org-user-avatar">' + esc(initial(item.name)) + '</span><span class="org-user-info"><b>' + esc(item.name) + '</b><small>' + esc(sourceLabel(item)) + '</small></span><button class="org-btn org-btn-ghost org-remove-user" data-remove-user="' + item.id + '">' + iconFor('remove') + ' عزل</button></div>'; }).join('') : '<div class="org-modal-empty">کارشناسی در این واحد نیست.</div>') + '</div><footer><button class="org-btn org-btn-success" data-add-more>+ افزودن کارشناس</button></footer></section>';
        document.body.appendChild(backdrop);
        var close = function () { backdrop.remove(); };
        backdrop.addEventListener('click', function (event) { if (event.target === backdrop || event.target.closest('.org-modal-close')) close(); });
        backdrop.querySelector('[data-add-more]').addEventListener('click', function () { close(); addExperts(unitId); });
        backdrop.querySelectorAll('[data-remove-user]').forEach(function (button) {
            button.addEventListener('click', function () {
                var item = person(Number(button.getAttribute('data-remove-user')));
                if (!item) return;
                askConfirm({ icon: iconFor('expert'), title: 'عزل کارشناس', message: '«' + item.name + '» از این واحد عزل شود؟', details: 'کاربر از واحد جدا شده و در فهرست کاربران آزاد قرار می‌گیرد.', okText: 'بله، عزل کن', danger: true, onConfirm: function () { close(); submit('save_user_org', { user_id: item.id, org_unit_id: 0, manager_user_id: 0 }); } });
            });
        });
    };
    var removeAllExperts = function (unitId) {
        var experts = expertsIn(unitId);
        if (!experts.length) return;
        var target = unit(unitId);
        askConfirm({ icon: iconFor('expert'), title: 'عزل همه کارشناسان', message: experts.length + ' کارشناس از «' + target.name + '» عزل شوند؟', details: 'همهٔ آن‌ها به فهرست کاربران آزاد برمی‌گردند.', okText: 'بله، عزل کن', danger: true, onConfirm: function () { submit('remove_org_experts', { unit_id: unitId, user_ids: experts.map(function (item) { return item.id; }) }); } });
    };
    var removeUnit = function (unitId) {
        var target = unit(unitId);
        if (!target) return;
        var ids = [Number(unitId)].concat(descendantIds(unitId));
        var affected = units.filter(function (item) { return ids.indexOf(Number(item.id)) !== -1; });
        var managerIds = affected.map(function (item) { return Number(item.managerId); }).filter(function (id) { return id > 0; });
        var assigned = (data.users || []).filter(function (item) { return ids.indexOf(Number(item.unitId)) !== -1 || managerIds.indexOf(Number(item.managerId)) !== -1; }).length;
        var list = affected.map(function (item) { return '• ' + esc(item.name); }).join('<br>');
        askConfirm({ icon: iconFor('delete'), title: 'پیش‌نمایش حذف نرم', message: '«' + target.name + '» و ' + (affected.length - 1) + ' ادارهٔ زیرمجموعه غیرفعال می‌شوند.', details: '<b>واحدهای متأثر:</b><br>' + list + '<br><br><b>' + assigned + ' کاربر</b> از این واحدها آزاد می‌شوند. رکوردها پاک نمی‌شوند و با Undo قابل بازگشت‌اند.', okText: 'تأیید حذف نرم', danger: true, onConfirm: function () { submit('delete_org_unit', { unit_id: unitId }); } });
    };

    var renderMenu = function (button) {
        $$('.org-node-menu').forEach(function (item) { item.remove(); });
        var id = Number(button.getAttribute('data-unit-id'));
        var target = unit(id);
        if (!target) return;
        var menu = document.createElement('div');
        menu.className = 'org-node-menu';
        var hasManager = Number(target.managerId) > 0;
        var items = [];
        if (target.type === 'ceo' || target.type === 'company_manager') items = [{ icon: iconFor('manager'), label: 'تغییر ' + rootLabel(), action: function () { openManagerPicker(target); } }, { icon: iconFor('add'), label: 'افزودن معاونت', action: showInlineAddDeputy }, { divider: true }, { icon: iconFor('remove'), label: 'برداشتن از سمت', danger: true, action: function () { removeManager(target); } }];
        if (target.type === 'deputy') items = [{ icon: iconFor('manager'), label: hasManager ? 'تغییر مدیر' : 'تعیین مدیر', action: function () { openManagerPicker(target); } }, { icon: iconFor('add'), label: 'افزودن اداره', action: function () { showInlineAddDepartment(target.id); } }, { icon: iconFor('expert'), label: 'افزودن کارشناس', action: function () { addExperts(target.id); } }, ...(expertsIn(target.id).length ? [{ icon: iconFor('manage'), label: 'مدیریت کارشناسان', action: function () { openExpertManager(target.id); } }, { icon: iconFor('delete'), label: 'عزل همه کارشناسان', danger: true, action: function () { removeAllExperts(target.id); } }] : []), { icon: iconFor('manage'), label: 'ویرایش معاونت', action: function () { showInlineEdit(target); } }, { divider: true }, ...(hasManager ? [{ icon: iconFor('remove'), label: 'برداشتن مدیر', danger: true, action: function () { removeManager(target); } }] : []), { icon: iconFor('delete'), label: 'حذف کل معاونت', danger: true, action: function () { removeUnit(target.id); } }];
        if (target.type === 'liaison') items = [{ icon: iconFor('manager'), label: hasManager ? 'تغییر نماینده' : 'تعیین نماینده', action: function () { openManagerPicker(target); } }, { icon: iconFor('expert'), label: 'افزودن کارشناس', action: function () { addExperts(target.id); } }, ...(expertsIn(target.id).length ? [{ icon: iconFor('manage'), label: 'مدیریت کارشناسان', action: function () { openExpertManager(target.id); } }, { icon: iconFor('delete'), label: 'عزل همه کارشناسان', danger: true, action: function () { removeAllExperts(target.id); } }] : []), { icon: iconFor('manage'), label: 'ویرایش واحد مستقل', action: function () { showInlineEdit(target); } }, { divider: true }, ...(hasManager ? [{ icon: iconFor('remove'), label: 'برداشتن نماینده', danger: true, action: function () { removeManager(target); } }] : []), { icon: iconFor('delete'), label: 'حذف واحد مستقل', danger: true, action: function () { removeUnit(target.id); } }];
        if (target.type === 'department') items = [{ icon: iconFor('manager'), label: hasManager ? 'تغییر رئیس' : 'تعیین رئیس', action: function () { openManagerPicker(target); } }, { icon: iconFor('manage'), label: 'ویرایش اداره', action: function () { showInlineEdit(target); } }, { icon: iconFor('expert'), label: 'افزودن کارشناس', action: function () { addExperts(target.id); } }, ...(expertsIn(target.id).length ? [{ icon: iconFor('manage'), label: 'مدیریت کارشناسان', action: function () { openExpertManager(target.id); } }, { icon: iconFor('delete'), label: 'عزل همه کارشناسان', danger: true, action: function () { removeAllExperts(target.id); } }] : []), { divider: true }, ...(hasManager ? [{ icon: iconFor('remove'), label: 'برداشتن رئیس', danger: true, action: function () { removeManager(target); } }] : []), { icon: iconFor('delete'), label: 'حذف کل اداره', danger: true, action: function () { removeUnit(target.id); } }];
        items.forEach(function (item) {
            if (item.divider) { var divider = document.createElement('div'); divider.className = 'org-menu-divider'; menu.appendChild(divider); return; }
            var row = document.createElement('button'); row.type = 'button'; row.className = 'org-menu-item' + (item.danger ? ' danger' : ''); row.innerHTML = '<span>' + item.icon + '</span><b>' + esc(item.label) + '</b>'; row.addEventListener('click', function (event) { event.stopPropagation(); menu.remove(); item.action(); }); menu.appendChild(row);
        });
        button.closest('.org-node').appendChild(menu);
        // اگر منو از پایین صفحه بیرون می‌زند، به سمت بالای کارت باز شود تا همهٔ گزینه‌ها (از جمله حذف) دیده شوند.
        var menuBox = menu.getBoundingClientRect();
        if (menuBox.bottom > window.innerHeight - 8) { menu.style.top = 'auto'; menu.style.bottom = '36px'; }
    };
    var removeManager = function (target) {
        if (!target.managerId) return;
        var manager = person(target.managerId);
        askConfirm({ icon: iconFor('manager'), title: 'عزل مدیر', message: '«' + (manager ? manager.name : 'کاربر') + '» از «' + target.name + '» عزل شود؟', details: 'خود واحد باقی می‌ماند و فقط سمت مدیر خالی می‌شود.', okText: 'بله، عزل کن', danger: true, onConfirm: function () { submit('remove_org_manager', { target_type: target.type, unit_id: target.id }); } });
    };
    var handleAction = function (element) {
        var action = element.getAttribute('data-org-action');
        var id = Number(element.getAttribute('data-unit-id') || 0);
        if (action === 'pick-ceo') return openManagerPicker(root());
        if (action === 'add-deputy') return showInlineAddDeputy();
        if (action === 'add-liaison') return showInline('liaison', 0, element.getAttribute('data-liaison-side') || 'right');
        if (action === 'add-department') return showInlineAddDepartment(id);
        if (action === 'quick-department') return quickAddDepartment();
        if (action === 'quick-expert') return quickAddExpert();
        if (action === 'pick-manager') return openManagerPicker(unit(id));
        if (action === 'add-experts') return addExperts(id);
        if (action === 'manage-experts') return openExpertManager(id);
    };
    var bindDropTargets = function () {
        $$('#org-chart-wrap [data-drop]').forEach(function (target) {
            target.addEventListener('dragover', function (event) { event.preventDefault(); target.classList.add('drop-target'); });
            target.addEventListener('dragleave', function () { target.classList.remove('drop-target'); });
            target.addEventListener('drop', function (event) {
                event.preventDefault(); target.classList.remove('drop-target');
                var payload; try { payload = JSON.parse(event.dataTransfer.getData('text/plain')); } catch (error) { return; }
                var selected = person(payload.userId); if (!selected) return;
                var targetUnit = unit(Number(target.getAttribute('data-unit-id') || target.getAttribute('data-unit')));
                if (target.getAttribute('data-drop') === 'experts' && targetUnit) submit('save_org_assign_users', { member_unit_id: targetUnit.id, user_ids: [selected.id] });
                else if (target.getAttribute('data-drop') === 'ceo') submit('assign_org_manager', { target_type: rootType(), unit_id: root().id, user_id: selected.id });
                else if (targetUnit) submit('assign_org_manager', { target_type: targetUnit.type, unit_id: targetUnit.id, user_id: selected.id });
            });
        });
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.org-export-wrap')) closeExportMenu();
        var exportToggle = event.target.closest('[data-org-export-toggle]');
        if (exportToggle) { var menu = $('#org-export-menu'), opening = menu.hidden; menu.hidden = !opening; exportToggle.setAttribute('aria-expanded', opening ? 'true' : 'false'); return; }
        var exportChoice = event.target.closest('[data-org-export]');
        if (exportChoice) { exportByType(exportChoice.getAttribute('data-org-export')); return; }
        var modalAction = event.target.closest('[data-org-modal]');
        if (modalAction) { closeModal(); return; }
        var confirmAction = event.target.closest('[data-org-confirm]');
        if (confirmAction) { closeConfirm(); return; }
        var undoClose = event.target.closest('[data-org-undo="close"]');
        if (undoClose) { $('#org-undo-bar').classList.remove('show'); return; }
        var filter = event.target.closest('[data-org-filter]');
        if (filter) { var key = filter.getAttribute('data-org-filter'); state.filterStat = key === 'clear' || state.filterStat === key ? null : key; renderLeftSidebar(); renderPoolSidebar(); return; }
        var menuButton = event.target.closest('[data-org-menu]');
        if (menuButton) { event.stopPropagation(); renderMenu(menuButton); return; }
        var action = event.target.closest('[data-org-action]');
        if (action) { event.stopPropagation(); handleAction(action); return; }
        var tool = event.target.closest('[data-org-tool]');
        if (tool) {
            var name = tool.getAttribute('data-org-tool');
            if (name === 'backup') exportJson(true);
            if (name === 'restore-backup') { var backupInput = $('#org-backup-file'); if (backupInput) backupInput.click(); }
            if (name === 'print') printWholeChart();
            if (name === 'reset') askReset();
            if (name === 'undo') {
                if (data.undo) submit('undo_org_action', {}); else toast('عملیاتی برای بازگشت وجود ندارد.', 'ℹ️');
            }
            if (name === 'redo') { if (data.redo) submit('redo_org_action', {}); else toast('عملیاتی برای انجام دوباره وجود ندارد.', 'ℹ️');
            }
            return;
        }
        if (!event.target.closest('.org-node-menu')) $$('.org-node-menu').forEach(function (item) { item.remove(); });
    });
    document.addEventListener('click', function (event) {
        if (event.target.closest('.org-inline-form-wrap')) return;
        var node = event.target.closest('.org-node[data-unit]');
        if (node && !event.target.closest('button') && data.canManage) openManagerPicker(unit(Number(node.getAttribute('data-unit'))));
    });
    $('#org-confirm-ok').addEventListener('click', function () { var callback = state.confirm; closeConfirm(); if (callback) callback(); });
    $('#org-confirm-backdrop').addEventListener('click', function (event) { if (event.target.id === 'org-confirm-backdrop') closeConfirm(); });
    $('#org-modal-search').addEventListener('input', function (event) { if (state.modal) { state.modal.search = event.target.value; renderModal(); } });
    $('#org-modal-confirm').addEventListener('click', function () { if (!state.modal || !state.modal.selected.length) return; var selected = state.modal.selected.map(function (id) { return state.modal.users.find(function (item) { return Number(item.id) === id; }); }).filter(Boolean); var callback = state.modal.onConfirm; closeModal(); if (callback) callback(selected); });
    $('#org-modal-backdrop').addEventListener('click', function (event) { if (event.target.id === 'org-modal-backdrop') closeModal(); });
    $('#org-chart-search').addEventListener('input', function (event) { state.search = event.target.value; renderChart(); if (state.search.trim()) window.requestAnimationFrame(function () { var first = $('#org-chart-wrap .org-node.is-match, #org-chart-wrap .org-expert-person.is-match'); if (first) first.scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' }); }); });
    $$('[data-org-zoom]').forEach(function (button) { button.addEventListener('click', function () { var action = button.getAttribute('data-org-zoom'); if (action === 'in') state.zoom = Math.min(1.4, state.zoom + .1); if (action === 'out') state.zoom = Math.max(.6, state.zoom - .1); if (action === 'reset') state.zoom = 1; if (action === 'fit') { var wrap = $('#org-chart-wrap'), canvas = $('#org-canvas'); if (wrap && canvas) { wrap.style.transform = 'none'; var width = Math.max(1, wrap.scrollWidth), height = Math.max(1, wrap.scrollHeight); var availableWidth = Math.max(1, canvas.clientWidth - 48); var availableHeight = Math.max(1, canvas.clientHeight - 64); state.zoom = Math.max(.35, Math.min(1, availableWidth / width, availableHeight / height)); } } renderChart(); }); });
    window.addEventListener('resize', function () { window.requestAnimationFrame(drawOrgWires); });
    var companySelect = $('#org-company-select');
    if (companySelect) companySelect.addEventListener('change', function () { var url = new URL(window.location.href); url.searchParams.set('page', 'organization'); url.searchParams.set('company_id', this.value); url.hash = ''; window.location.assign(url.toString()); });
    var focusToggle = $('[data-org-view="focus"]');
    if (focusToggle) focusToggle.addEventListener('click', function () { var workspace = $('.organization-workspace'), active = !workspace.classList.contains('org-focus-mode'); workspace.classList.toggle('org-focus-mode', active); focusToggle.setAttribute('aria-pressed', active ? 'true' : 'false'); var label = focusToggle.querySelector('span'); if (label) label.textContent = active ? 'نمایش پنل‌ها' : 'تمرکز روی نمودار'; window.requestAnimationFrame(drawOrgWires); });
    document.addEventListener('keydown', function (event) {
        var editing = event.target.closest('input,textarea,select,[contenteditable="true"]');
        if (event.code === 'Space' && !editing) {
            event.preventDefault();
            if (!event.repeat) { state.spaceDown = true; var canvas = $('#org-canvas'); if (canvas) canvas.classList.add('org-pan-ready'); }
        }
        if (event.key === 'Escape') { closeModal(); closeConfirm(); closeExportMenu(); $$('.org-node-menu').forEach(function (item) { item.remove(); }); }
    }, true);
    document.addEventListener('keyup', function (event) { if (event.code === 'Space') { state.spaceDown = false; var canvas = $('#org-canvas'); if (canvas) { canvas.classList.remove('org-pan-ready'); if (!state.pan) canvas.classList.remove('org-panning'); } } });
    $('#org-canvas').addEventListener('pointerdown', function (event) {
        if (!state.spaceDown || event.button !== 0 || event.target.closest('.org-canvas-toolbar, .org-node, .org-experts-group, [data-deputy-drag], input, button, select, textarea')) return;
        var canvas = $('#org-canvas'); state.pan = { x: event.clientX, y: event.clientY, left: canvas.scrollLeft, top: canvas.scrollTop, pointerId: event.pointerId };
        canvas.classList.add('org-panning'); event.preventDefault(); event.stopPropagation(); canvas.setPointerCapture(event.pointerId);
    });
    $('#org-canvas').addEventListener('pointermove', function (event) { if (!state.pan || event.pointerId !== state.pan.pointerId) return; var canvas = $('#org-canvas'); event.preventDefault(); canvas.scrollLeft = state.pan.left - (event.clientX - state.pan.x); canvas.scrollTop = state.pan.top - (event.clientY - state.pan.y); });
    var endPan = function (event) { if (!state.pan || (event && event.pointerId !== state.pan.pointerId)) return; var canvas = $('#org-canvas'), pointerId = state.pan.pointerId; state.pan = null; canvas.classList.remove('org-panning'); if (canvas.hasPointerCapture(pointerId)) canvas.releasePointerCapture(pointerId); };
    $('#org-canvas').addEventListener('pointerup', endPan);
    $('#org-canvas').addEventListener('pointercancel', endPan);
    $('#org-canvas').addEventListener('lostpointercapture', function () { if (state.pan) { state.pan = null; $('#org-canvas').classList.remove('org-panning'); } });
    window.addEventListener('blur', function () { state.spaceDown = false; endPan(); var canvas = $('#org-canvas'); if (canvas) canvas.classList.remove('org-pan-ready', 'org-panning'); });

    var askReset = function () {
        if (!data.canManage) return;
        var hasStructure = units.some(function (item) { return item.type !== rootType(); }) || (root() && root().managerId);
        if (!hasStructure) return toast('ساختار خالی است', 'ℹ️');
        askConfirm({ icon: iconFor('reset'), title: 'شروع مجدد', message: 'تمام ساختار پاک شود؟', details: units.filter(function (item) { return item.type !== rootType(); }).length + ' واحد سازمانی غیرفعال و ' + (data.users || []).filter(function (item) { return Number(item.unitId) > 0 || Number(item.managerId) > 0; }).length + ' انتساب سازمانی آزاد می‌شود. رکوردها پاک نمی‌شوند؛ پیش از ریست پشتیبان کامل JSON دانلود می‌شود؛ عملیات در همین نشست Undo/Redo دارد.', okText: 'بله، پاک کن', danger: true, onConfirm: function () { exportJson(true); submit('reset_org_structure', {}); } });
    };
    var downloadBlob = function (blob, filename) {
        var url = URL.createObjectURL(blob), link = document.createElement('a'); link.href = url; link.download = filename; link.style.display = 'none'; document.body.appendChild(link); link.click(); link.remove(); window.setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
    };
    var exportJson = function (backup) {
        var payload = { format: 'persian-org-tree-json', formatVersion: 1, backup: !!backup, companyId: Number(data.companyId || 1), companyName: data.companyName || '', exportedAt: new Date().toISOString(), units: units, users: data.users || [] };
        downloadBlob(new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json;charset=utf-8' }), (backup ? 'org-tree-backup-' : 'org-structure-') + Date.now() + '.json');
        toast(backup ? 'پشتیبان کامل JSON دانلود شد.' : 'فایل JSON کامل دانلود شد.', '↓');
    };
    var exportExcel = function () {
        fetch('index.php?page=organization&company_id=' + encodeURIComponent(data.companyId || 1) + '&export=excel', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { if (!response.ok) throw new Error('ساخت فایل Excel ناموفق بود.'); return response.blob(); })
            .then(function (blob) { if (blob.type.indexOf('spreadsheetml') === -1) throw new Error('پاسخ Excel معتبر نبود؛ صفحه را تازه و دوباره امتحان کنید.'); downloadBlob(blob, 'organization-tree-' + Date.now() + '.xlsx'); toast('فایل Excel کامل دانلود شد.', '↓'); })
            .catch(function (error) { toast(error.message || 'ساخت Excel ناموفق بود.', iconFor('warning')); });
    };
    var createChartImageBlob = function (done) {
        var stage = $('.org-chart-stage');
        if (!stage) return done(null, null);
        drawOrgWires();
        var width = Math.max(stage.scrollWidth, stage.offsetWidth), height = Math.max(stage.scrollHeight, stage.offsetHeight);
        if (!width || !height) return done(null, null);
        fetch('assets/style.css?v=3.4.57', { credentials: 'same-origin' }).then(function (response) {
            if (!response.ok) throw new Error('دریافت قالب نمودار ناموفق بود.');
            return response.text();
        }).then(function (cssText) {
            var svgNs = 'http://www.w3.org/2000/svg', htmlNs = 'http://www.w3.org/1999/xhtml';
            var svg = document.createElementNS(svgNs, 'svg'); svg.setAttribute('xmlns', svgNs); svg.setAttribute('width', width); svg.setAttribute('height', height); svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
            var foreign = document.createElementNS(svgNs, 'foreignObject'); foreign.setAttribute('x', '0'); foreign.setAttribute('y', '0'); foreign.setAttribute('width', width); foreign.setAttribute('height', height);
            var rootBox = document.createElementNS(htmlNs, 'body'); rootBox.setAttribute('xmlns', htmlNs); rootBox.setAttribute('class', 'page-organization'); rootBox.setAttribute('dir', 'rtl'); rootBox.setAttribute('data-theme-mode', document.body.getAttribute('data-theme-mode') || 'light');
            var styleNode = document.createElementNS(htmlNs, 'style'); styleNode.textContent = cssText + '\n.org-chart-wrap{width:' + width + 'px!important;min-width:0!important;padding:0!important;transform:none!important}.org-chart-stage{width:' + width + 'px!important;height:' + height + 'px!important;min-width:0!important;min-height:0!important}.org-node-add,.org-sort-handle,.org-node-menu-wrap,.org-node-menu,.org-expert-manage{visibility:hidden!important}.org-inline-form-wrap{display:none!important}'; rootBox.appendChild(styleNode);
            var wrap = document.createElementNS(htmlNs, 'div'); wrap.setAttribute('class', 'org-chart-wrap');
            var stageClone = stage.cloneNode(true); stageClone.style.width = width + 'px'; stageClone.style.height = height + 'px'; stageClone.style.transform = 'none'; wrap.appendChild(stageClone); rootBox.appendChild(wrap); foreign.appendChild(rootBox); svg.appendChild(foreign);
            var svgBlob = new Blob([new XMLSerializer().serializeToString(svg)], { type: 'image/svg+xml;charset=utf-8' }), image = new Image();
            image.onload = function () {
                var pixelScale = Math.min(2, 12000 / Math.max(width, height), Math.sqrt(36000000 / Math.max(1, width * height))), canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.floor(width * pixelScale)); canvas.height = Math.max(1, Math.floor(height * pixelScale));
                var context = canvas.getContext('2d'); context.fillStyle = '#ffffff'; context.fillRect(0, 0, canvas.width, canvas.height); context.drawImage(image, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function (blob) { done(blob || svgBlob, blob ? 'png' : 'svg'); }, 'image/png');
            };
            image.onerror = function () { done(svgBlob, 'svg'); };
            var svgReader = new FileReader();
            svgReader.onload = function () { image.src = String(svgReader.result || ''); };
            svgReader.onerror = function () { done(svgBlob, 'svg'); };
            svgReader.readAsDataURL(svgBlob);
        }).catch(function (error) { done(null, null, error); });
    };
    var exportPng = function () {
        toast('در حال ساخت تصویر کامل درخت…', '⏳');
        createChartImageBlob(function (blob, type, error) {
            if (!blob) return toast(error && error.message ? error.message : 'ساخت تصویر درخت ناموفق بود.', iconFor('warning'));
            downloadBlob(blob, 'organization-tree-' + Date.now() + (type === 'svg' ? '.svg' : '.png'));
            toast(type === 'svg' ? 'PNG در این مرورگر پشتیبانی نشد؛ نسخهٔ کامل SVG دانلود شد.' : 'تصویر کامل درخت دانلود شد.', '↓');
        });
    };
    var printWholeChart = function () {
        var printWindow = window.open('', '_blank');
        if (!printWindow) return toast('برای چاپ، پنجرهٔ بازشو را در مرورگر مجاز کنید و دوباره تلاش کنید.', iconFor('warning'));
        var cssUrl = new URL('assets/style.css?v=3.4.57', window.location.href).toString();
        printWindow.document.open();
        printWindow.document.write('<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>چاپ کامل نمودار سازمانی</title><link rel="stylesheet" href="' + esc(cssUrl) + '"></head><body class="org-image-print-page"></body></html>');
        printWindow.document.close();
        toast('در حال آماده‌سازی تصویر کامل برای چاپ/PDF…', '⏳');
        createChartImageBlob(function (blob) {
            if (!blob) { printWindow.close(); return toast('ساخت تصویر کامل درخت برای چاپ ناموفق بود.', iconFor('warning')); }
            var image = printWindow.document.createElement('img'), styleLink = printWindow.document.querySelector('link[rel="stylesheet"]'), imageReader = new FileReader();
            image.alt = 'ساختار کامل سازمان'; printWindow.document.body.appendChild(image);
            imageReader.onload = function () { image.src = String(imageReader.result || ''); };
            imageReader.onerror = function () { printWindow.close(); toast('خواندن تصویر چاپ ناموفق بود.', iconFor('warning')); };
            imageReader.readAsDataURL(blob);
            var fired = false, cssReady = !styleLink, maybePrint = function () {
                if (fired || !cssReady || !image.complete || image.naturalWidth <= 0) return;
                fired = true; printWindow.focus();
                printWindow.setTimeout(function () { printWindow.print(); }, 350);
            };
            image.addEventListener('load', maybePrint, { once: true });
            if (styleLink) { styleLink.addEventListener('load', function () { cssReady = true; maybePrint(); }, { once: true }); styleLink.addEventListener('error', function () { cssReady = true; maybePrint(); }, { once: true }); }
            image.addEventListener('error', function () { printWindow.close(); toast('مرورگر نتوانست تصویر چاپ را باز کند.', iconFor('warning')); }, { once: true });
            printWindow.addEventListener('afterprint', function () { printWindow.close(); }, { once: true });
            if (image.complete && image.naturalWidth > 0) maybePrint();
            printWindow.setTimeout(function () { cssReady = true; maybePrint(); }, 1500);
        });
    };
    var closeExportMenu = function () { var menu = $('#org-export-menu'), toggle = $('[data-org-export-toggle]'); if (menu) menu.hidden = true; if (toggle) toggle.setAttribute('aria-expanded', 'false'); };
    var loadBackupFile = function (file) {
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) return toast('حجم فایل پشتیبان بیش از ۵ مگابایت است.', iconFor('warning'));
        var reader = new FileReader();
        reader.onload = function () {
            var backup;
            try { backup = JSON.parse(String(reader.result || '')); } catch (error) { return toast('فایل JSON معتبر نیست.', iconFor('warning')); }
            if (!backup || backup.format !== 'persian-org-tree-json' || !Array.isArray(backup.units) || !Array.isArray(backup.users)) return toast('این فایل، پشتیبان معتبر چارت سازمانی نیست.', iconFor('warning'));
            var unitsCount = backup.units.length, userCount = backup.users.length;
            askConfirm({ icon: iconFor('warning'), title: 'بازیابی پشتیبان چارت', message: 'ساختار و انتساب‌های فعلی با محتوای این پشتیبان جایگزین شوند؟', details: unitsCount + ' واحد سازمانی و ' + userCount + ' رکورد کاربری در فایل بررسی می‌شود. نقش و دسترسی ادمین اصلی از پشتیبان خوانده یا تغییر داده نمی‌شود؛ عملیات با Undo همین نشست قابل بازگشت است.', okText: 'بازیابی و جایگزینی', danger: true, onConfirm: function () { submit('restore_org_backup', { backup_json: String(reader.result) }); } });
        };
        reader.onerror = function () { toast('خواندن فایل پشتیبان انجام نشد.', iconFor('warning')); };
        reader.readAsText(file);
    };
    $('#org-backup-file').addEventListener('change', function (event) { loadBackupFile(event.target.files && event.target.files[0]); event.target.value = ''; });
    var exportByType = function (type) {
        closeExportMenu();
        if (type === 'pdf') return printWholeChart();
        if (type === 'png') return exportPng();
        if (type === 'excel') return exportExcel();
        if (type === 'json') return exportJson(false);
    };
    var renderAll = function () {
        renderLeftSidebar(); renderChart(); renderPoolSidebar();
        var restoreButton = $('[data-org-tool="restore-backup"]'); if (restoreButton) restoreButton.hidden = !data.canManage;
        $$('[data-org-tool="undo"]').forEach(function (button) { button.disabled = !data.undo; });
        $$('[data-org-tool="redo"]').forEach(function (button) { button.disabled = !data.redo; });
        var undoBar = $('#org-undo-bar');
        if (undoBar) {
            window.clearTimeout(undoBar._timer);
            undoBar.classList.toggle('show', !!data.undo);
            var undoText = $('#org-undo-text');
            if (undoText) undoText.textContent = data.undoLabel || 'آخرین عملیات';
            if (data.undo) undoBar._timer = window.setTimeout(function () { undoBar.classList.remove('show'); }, 2000);
        }
    };
    renderAll();
}());
