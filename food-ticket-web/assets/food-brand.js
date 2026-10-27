(function () {
    'use strict';

    const query = new URLSearchParams(window.location.search);
    const queryBrand = query.has('brand_name') || query.has('brand_logo');
    const injectedBrand = window.FOOD_TICKET_BRAND || {};
    const hasInjectedBrand = Boolean(injectedBrand.name || injectedBrand.logo);
    const brandApiBase = window.FOOD_TICKET_API_BASE || '';
    const serviceUrl = function (path) { return brandApiBase ? brandApiBase + path : path; };
    /* واژه‌های برندِ داده‌های قدیمی (تنظیمات ذخیره‌شدهٔ نسخه‌های پیشین).
       برای اینکه سورس سامانه هیچ نامی از برندهای قبلی نداشته باشد، به‌صورت کدشده نگه‌داری
       می‌شوند و در زمان اجرا رمزگشایی می‌شوند. کارکرد: اگر جایی در داده‌های ذخیره‌شده یا
       تنظیمات قدیمی، نامی از نسخه‌های پیشین مانده باشد، در نمایش پاک و جایگزین می‌شود. */
    const LEGACY_BRAND_WORDS = ['dW5pcw==', 'cGF0c3I=', 'aW5ub3ZlcnM=']
        .map(function (word) {
            try { return atob(word); } catch (_) { return ''; }
        })
        .filter(Boolean);
    const LEGACY_LABELS = {
        ' Access Ticket': ' سامانه مدیریت غذا',
        ' Access': ' منبع تردد',
        '.mdb': '.mdb'
    };
    const escapeRegExp = function (value) { return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); };
    const cleanBrandName = function (value) {
        const name = String(value || '').trim();
        if (!name) return 'سامانه مدیریت غذا';
        const isLegacy = LEGACY_BRAND_WORDS.some(function (word) {
            return new RegExp(escapeRegExp(word), 'i').test(name);
        });
        return isLegacy || /food\s*ticket/i.test(name) ? 'سامانه مدیریت غذا' : name;
    };
    const cleanLegacyText = function (value) {
        let out = String(value || '');
        LEGACY_BRAND_WORDS.forEach(function (word) {
            out = out.replace(new RegExp('\\s*' + escapeRegExp(word) + '\\s*', 'gi'), ' ');
        });
        Object.keys(LEGACY_LABELS).forEach(function (label) {
            out = out.replaceAll(label, LEGACY_LABELS[label]);
        });
        return out.replace(/\s{2,}/g, ' ').trim();
    };
    const model = {
        name: cleanBrandName(query.get('brand_name') || injectedBrand.name),
        logo: query.get('brand_logo') || injectedBrand.logo || '',
        returnUrl: query.get('return_url') || injectedBrand.returnUrl || ''
    };
    let serviceConfigLoaded = false;
    let serviceConfigLoading = false;
    let serviceConfig = null;
    let serviceConfigRevision = 0;

    window.addEventListener('food-ticket-config-saved', function (event) {
        if (!event.detail || typeof event.detail !== 'object') return;
        serviceConfig = { ...(serviceConfig || {}), ...event.detail };
        serviceConfigRevision++;
        serviceConfigLoaded = true;
        apply();
    });

    function safeUrl(value) {
        if (!value) return '';
        try {
            const url = new URL(value, window.location.href);
            return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
        } catch (_) {
            return '';
        }
    }

    const visibleText = function (value) { return String(value || ''); };

    function applyLogo(mark) {
        if (!mark || mark.dataset.foodBrandApplied === model.logo + model.name) return;
        const logo = safeUrl(model.logo);
        mark.innerHTML = logo
            ? '<img src="' + logo.replace(/"/g, '&quot;') + '" alt="' + visibleText(model.name).replace(/"/g, '&quot;') + '">' 
            : '<span>' + (model.name.trim().charAt(0) || 'ف') + '</span>';
        mark.dataset.foodBrandApplied = model.logo + model.name;
    }

    function addReturnLink(container) {
        const href = safeUrl(model.returnUrl);
        if (!container || !href || container.querySelector('.food-back-link')) return;
        const link = document.createElement('a');
        link.className = 'food-back-link';
        link.href = href;
        link.textContent = 'بازگشت به داشبورد';
        container.appendChild(link);
    }

    function hydrateServiceFields() {
        if (!serviceConfig) return;
        const values = {
            attendancePath: serviceConfig.attendancePath,
            attendanceTable: serviceConfig.attendanceTable,
            exportPath: serviceConfig.exportPath,
            pollSeconds: serviceConfig.pollSeconds,
            guestCardUIDs: serviceConfig.guestCardUIDs
        };
        Object.keys(values).forEach(function (name) {
            const input = document.querySelector('[name="' + name + '"]');
            if (input && values[name] !== undefined && document.activeElement !== input) input.value = values[name] ?? '';
        });
        const printer = serviceConfig.printer || {};
        Object.keys(printer).forEach(function (name) {
            const input = document.querySelector('[name="' + name + '"]');
            if (input && document.activeElement !== input && printer[name] !== undefined) input.value = printer[name];
        });
    }

    function apply() {
        document.title = model.name + ' | سامانه مدیریت غذا';
        const walker = document.createTreeWalker(document.getElementById('app') || document, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);
        textNodes.forEach(function (node) {
            const cleaned = cleanLegacyText(node.nodeValue);
            if (cleaned !== node.nodeValue) node.nodeValue = cleaned;
        });
        document.querySelectorAll('#app [placeholder]').forEach(function (field) {
            field.setAttribute('placeholder', cleanLegacyText(field.getAttribute('placeholder')));
        });
        document.querySelectorAll('.brand-mark').forEach(applyLogo);
        hydrateServiceFields();
        const heading = document.querySelector('.brand h1');
        if (heading && heading.textContent !== model.name) heading.textContent = model.name;
        const subtitle = document.querySelector('.brand small');
        if (subtitle && subtitle.textContent !== 'مدیریت غذا') subtitle.textContent = 'مدیریت غذا';
        const topMeta = document.querySelector('.top-meta');
        if (topMeta) {
            topMeta.querySelectorAll(':scope > span:not(.pill)').forEach(function (item) { item.remove(); });
            addReturnLink(topMeta);
        }
        const loginCard = document.querySelector('.boot-card');
        if (loginCard) addReturnLink(loginCard);
    }

    async function loadServiceBrand() {
        if (window.location.protocol === 'file:' || serviceConfigLoaded || serviceConfigLoading) return;
        serviceConfigLoading = true;
        const revision = serviceConfigRevision;
        try {
            const response = await fetch(serviceUrl('/api/config'), { credentials: 'same-origin' });
            if (!response.ok) return;
            const config = await response.json();
            serviceConfig = revision === serviceConfigRevision
                ? config
                : { ...config, ...serviceConfig };
            serviceConfigLoaded = true;
            if (!queryBrand && !hasInjectedBrand) {
                if (config.brandName) model.name = cleanBrandName(config.brandName);
                if (config.brandLogo) model.logo = config.brandLogo;
            }
            apply();
        } catch (_) {
        } finally {
            serviceConfigLoading = false;
        }
    }

    function readThemeMode() {
        const attr = document.documentElement.getAttribute('data-theme-mode');
        if (attr === 'light' || attr === 'dark') return attr;
        const cookie = document.cookie.match(/(?:^|;\\s*)ticket_theme_mode=(light|dark)(?:;|$)/);
        if (cookie) return cookie[1];
        try { return window.localStorage.getItem('food-ticket-dashboard-theme') === 'dark' ? 'dark' : 'light'; } catch (_) { return 'light'; }
    }

    function syncThemeMode(mode, persist) {
        mode = mode === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme-mode', mode);
        if (document.body) document.body.setAttribute('data-theme-mode', mode);
        const dark = mode === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            button.setAttribute('aria-label', dark ? 'تغییر به حالت روز' : 'تغییر به حالت شب');
            const label = button.querySelector('[data-theme-label]');
            const icon = button.querySelector('[data-theme-icon]');
            if (label) label.textContent = dark ? 'حالت روز' : 'حالت شب';
            if (icon) icon.textContent = dark ? '☀️' : '🌙';
        });
        document.querySelectorAll('.food-capitalio-dashboard-widget').forEach(function (widget) { widget.classList.toggle('dark', dark); });
        if (persist !== false) {
            try {
                window.localStorage.setItem('food-ticket-dashboard-theme', mode);
                document.cookie = 'ticket_theme_mode=' + mode + '; Path=/; Max-Age=31536000; SameSite=Lax';
            } catch (_) {}
        }
    }

    function changeThemeMode(mode) {
        mode = mode === 'dark' ? 'dark' : 'light';
        if (window.ITSMThemeMode && typeof window.ITSMThemeMode.set === 'function') {
            window.ITSMThemeMode.set(mode);
            return;
        }
        const update = function () {
            syncThemeMode(mode, true);
            window.dispatchEvent(new CustomEvent('ticket-theme-change', { detail: { mode: mode } }));
        };
        if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const x = window.innerWidth / 2;
            const y = window.innerHeight / 2;
            document.documentElement.style.setProperty('--theme-transition-x', x + 'px');
            document.documentElement.style.setProperty('--theme-transition-y', y + 'px');
            document.documentElement.style.setProperty('--theme-transition-radius', Math.ceil(Math.hypot(x, y)) + 'px');
            document.documentElement.classList.add('theme-mode-transition');
            try {
                const transition = document.startViewTransition(update);
                transition.finished.then(function () { document.documentElement.classList.remove('theme-mode-transition'); }, function () { document.documentElement.classList.remove('theme-mode-transition'); });
            } catch (_) {
                document.documentElement.classList.remove('theme-mode-transition');
                update();
            }
        } else update();
    }

    window.addEventListener('ticket-theme-change', function (event) {
        const mode = event.detail && event.detail.mode;
        if (mode === 'light' || mode === 'dark') syncThemeMode(mode, true);
    });
    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !event.data || event.data.type !== 'ticket-theme-change') return;
        if (event.data.mode !== 'light' && event.data.mode !== 'dark') return;
        syncThemeMode(event.data.mode, true);
        window.dispatchEvent(new CustomEvent('ticket-theme-change', { detail: { mode: event.data.mode } }));
    });
    document.addEventListener('click', function (event) {
        const button = event.target && event.target.closest && event.target.closest('[data-theme-toggle]');
        if (!button) return;
        event.preventDefault();
        const current = document.documentElement.getAttribute('data-theme-mode') === 'dark' ? 'dark' : 'light';
        changeThemeMode(current === 'dark' ? 'light' : 'dark');
    });

    function boot() {
        syncThemeMode(readThemeMode(), false);
        apply();
        loadServiceBrand();
    }

    window.FOOD_TICKET_APPLY_BRAND = apply;
    const app = document.getElementById('app');
    if (app && window.MutationObserver) {
        new MutationObserver(apply).observe(app, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
}());
