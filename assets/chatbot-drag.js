/* جابجایی دکمه بات پاسخ‌گو با ماوس/لمس.
 * قواعد:
 *  - دکمه در هر نقطه از صفحه قابل کشیدن است.
 *  - بعد از رها کردن، همیشه به نزدیک‌ترین کنار صفحه (راست یا چپ) می‌چسبد.
 *    (باند ۱۰٪ وسط هم به نزدیک‌ترین کنار می‌رود.)
 *  - ارتفاع (بالا/پایین) همان جایی می‌ماند که رها شده است.
 *  - باکس چت کنار دکمه (از سمت مرکز صفحه) باز می‌شود و روی دکمه نمی‌افتد؛ دکمهٔ ✕ و Esc آن را می‌بندند.
 *  - با تغییر اندازهٔ پنجره، دکمه داخل صفحه نگه داشته می‌شود.
 * این فایل خارجی است چون CSP اسکریپت درون‌خطی بدون nonce را اجازه نمی‌دهد.
 */
(function () {
    var bot = document.getElementById('cb-open');
    var panel = document.getElementById('cb-panel');
    if (!bot || !panel) return;

    var EDGE = 8;          // فاصلهٔ دکمه از لبه‌ها (px)
    var DRAG_MIN = 6;      // کمتر از این جابجایی، کلیک حساب می‌شود
    var PANEL_GAP = 10;    // فاصلهٔ باکس چت از دکمه

    var dragging = false;
    var moved = false;
    var startX = 0, startY = 0, origLeft = 0, origTop = 0;

    function size() {
        var r = bot.getBoundingClientRect();
        return { w: r.width, h: r.height };
    }

    function clampTop(top) {
        var s = size();
        var max = window.innerHeight - s.h - EDGE;
        return Math.max(EDGE, Math.min(top, Math.max(EDGE, max)));
    }

    function clampLeft(left) {
        var s = size();
        var max = window.innerWidth - s.w - EDGE;
        return Math.max(EDGE, Math.min(left, Math.max(EDGE, max)));
    }

    function place(left, top) {
        bot.style.left = left + 'px';
        bot.style.top = top + 'px';
        bot.style.right = 'auto';
        bot.style.bottom = 'auto';
    }

    function snapToSide(left, top) {
        var s = size();
        var center = left + s.w / 2;
        var sideLeft = center < window.innerWidth / 2 ? EDGE : window.innerWidth - s.w - EDGE;
        place(sideLeft, clampTop(top));
    }

    // باکس چت کنار دکمه باز می‌شود (از سمت مرکز صفحه)، تا هرگز روی دکمه نیفتد.
    // اگر جا نبود، بالا/پایین دکمه باز می‌شود. دکمهٔ ✕ همیشه برای بستن هست.
    function positionPanel() {
        var r = bot.getBoundingClientRect();
        var vw = window.innerWidth, vh = window.innerHeight;
        var panelH = Math.min(panel.offsetHeight || 460, vh - 2 * EDGE);
        var panelW = panel.offsetWidth || Math.min(360, vw - 36);
        var onRight = (r.left + r.width / 2) > vw / 2;
        var left, top;
        if (onRight) {
            left = r.left - PANEL_GAP - panelW;
        } else {
            left = r.right + PANEL_GAP;
        }
        var sideOk = left >= EDGE && left + panelW <= vw - EDGE;
        if (sideOk) {
            top = Math.max(EDGE, Math.min(r.top, vh - panelH - EDGE));
            panel.style.left = left + 'px';
            panel.style.right = 'auto';
            panel.style.top = top + 'px';
            panel.style.bottom = 'auto';
        } else {
            // جای کنار دکمه نیست: بالا یا پایین دکمه، هر کدام جا داشته باشد
            var leftFix = Math.max(EDGE, Math.min(r.left, vw - panelW - EDGE));
            panel.style.left = leftFix + 'px';
            panel.style.right = 'auto';
            if (r.top - PANEL_GAP - panelH >= EDGE) {
                panel.style.top = (r.top - PANEL_GAP - panelH) + 'px';
                panel.style.bottom = 'auto';
            } else {
                panel.style.top = Math.max(EDGE, Math.min(r.bottom + PANEL_GAP, vh - panelH - EDGE)) + 'px';
                panel.style.bottom = 'auto';
            }
        }
        return panelW;
    }

    // بستن با دکمهٔ ✕ و با کلید Esc
    var closeBtn = document.getElementById('cb-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            panel.classList.remove('open');
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel.classList.contains('open')) {
            panel.classList.remove('open');
        }
    });

    // پنل را هنگام باز شدن در جای درست قرار بده
    var observer = new MutationObserver(function () {
        if (panel.classList.contains('open')) positionPanel();
    });
    observer.observe(panel, { attributes: true, attributeFilter: ['class'] });

    bot.addEventListener('pointerdown', function (event) {
        if (event.button !== undefined && event.button !== 0) return;
        dragging = true;
        moved = false;
        startX = event.clientX;
        startY = event.clientY;
        var r = bot.getBoundingClientRect();
        origLeft = r.left;
        origTop = r.top;
    });

    window.addEventListener('pointermove', function (event) {
        if (!dragging) return;
        var dx = event.clientX - startX;
        var dy = event.clientY - startY;
        if (!moved && Math.abs(dx) < DRAG_MIN && Math.abs(dy) < DRAG_MIN) return;
        if (!moved) {
            moved = true;
            bot.classList.add('cb-dragging');
            bot.style.transition = 'none';
        }
        place(clampLeft(origLeft + dx), clampTop(origTop + dy));
        if (panel.classList.contains('open')) positionPanel();
        event.preventDefault();
    });

    function endDrag() {
        if (!dragging) return;
        dragging = false;
        if (!moved) return;
        bot.classList.remove('cb-dragging');
        bot.style.transition = '';
        var r = bot.getBoundingClientRect();
        snapToSide(r.left, r.top);
        if (panel.classList.contains('open')) positionPanel();
        // کلیک بعد از کشیدن نباید پنل را باز/بسته کند
        bot.dataset.justDragged = '1';
        setTimeout(function () { delete bot.dataset.justDragged; }, 0);
    }
    window.addEventListener('pointerup', endDrag);
    window.addEventListener('pointercancel', endDrag);

    // جلوی کلیک بعد از جابجایی را می‌گیرد (قبل از listener اصلی بات)
    bot.addEventListener('click', function (event) {
        if (bot.dataset.justDragged === '1') {
            event.stopImmediatePropagation();
            event.preventDefault();
        }
    }, true);

    window.addEventListener('resize', function () {
        var r = bot.getBoundingClientRect();
        var onRight = (r.left + r.width / 2) > window.innerWidth / 2;
        var sideLeft = onRight ? window.innerWidth - r.width - EDGE : EDGE;
        place(sideLeft, clampTop(r.top));
        if (panel.classList.contains('open')) positionPanel();
    });

    // موقعیت اولیه: همان گوشهٔ پیش‌فرض (چپ-پایین) که در CSS تعریف شده؛ به inline تبدیل می‌شود
    bot.style.touchAction = 'none';
    var init = bot.getBoundingClientRect();
    place(init.left, init.top);
})();
