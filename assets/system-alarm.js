/* آژیر سلامت سامانه — نمایش وسط صفحه برای مدیران (فقط وقتی سرور آلارم فعال برمی‌گرداند).
 * بستن هشدار = پنجرهٔ «آیا مطمئن هستید؟» + ثبت سابقه (SYS-ALARM-ACK) سمت سرور.
 * بدون وابستگی؛ بدون innerHTML (متن سرور همیشه با textContent نشست می‌گیرد). */
(function () {
    'use strict';

    var root = document.getElementById('system-alarm');
    if (!root) { return; }

    var endpoint = root.getAttribute('data-endpoint') || 'api/system-alarms.php';
    var list = document.getElementById('system-alarm-list');
    var title = document.getElementById('system-alarm-title');
    var closeBtn = document.getElementById('system-alarm-close');
    var confirmBox = document.getElementById('system-alarm-confirm');
    var confirmNo = document.getElementById('system-alarm-confirm-no');
    var confirmYes = document.getElementById('system-alarm-confirm-yes');
    var currentKeys = [];
    var ackInFlight = false;

    function textOf(value) {
        return value == null ? '' : String(value);
    }

    function showModal() {
        root.hidden = false;
        root.setAttribute('aria-hidden', 'false');
    }

    function hideModal() {
        root.hidden = true;
        root.setAttribute('aria-hidden', 'true');
    }

    function render(alarms) {
        if (!Array.isArray(alarms) || alarms.length === 0) {
            hideModal();
            return;
        }
        currentKeys = [];
        if (list) {
            while (list.firstChild) { list.removeChild(list.firstChild); }
            alarms.forEach(function (alarm) {
                if (!alarm || !alarm.key) { return; }
                currentKeys.push(String(alarm.key));
                var li = document.createElement('li');
                var strong = document.createElement('strong');
                strong.textContent = textOf(alarm.title || alarm.key);
                li.appendChild(strong);
                if (alarm.detail) {
                    var span = document.createElement('span');
                    span.textContent = ' — ' + textOf(alarm.detail);
                    li.appendChild(span);
                }
                if (alarm.link) {
                    var a = document.createElement('a');
                    a.className = 'system-alarm-link';
                    a.href = String(alarm.link);
                    a.textContent = ' باز کردن ←';
                    li.appendChild(a);
                }
                list.appendChild(li);
            });
        }
        if (title) {
            title.textContent = alarms.length === 1
                ? 'یک مشکل، کار سامانه را معطل کرده است.'
                : alarms.length + ' مشکل، کار سامانه را معطل کرده‌اند.';
        }
        if (confirmBox) { confirmBox.hidden = true; }
        showModal();
    }

    function poll() {
        window.fetch(endpoint, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (payload) {
                if (!payload || payload.ok !== true) { return; }
                render(payload.alarms || []);
            })
            .catch(function () { /* سکوت عمداً: قطعی خودِ دیده‌بانی را کرون/سیستم سلامت فریاد می‌زند */ });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            if (confirmBox) { confirmBox.hidden = false; }
        });
    }
    if (confirmNo) {
        confirmNo.addEventListener('click', function () {
            if (confirmBox) { confirmBox.hidden = true; }
        });
    }
    if (confirmYes) {
        confirmYes.addEventListener('click', function () {
            if (ackInFlight || currentKeys.length === 0) { return; }
            ackInFlight = true;
            window.fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': (window.ITSM_CSRF || '')
                },
                body: JSON.stringify({ action: 'ack', keys: currentKeys })
            }).then(function (response) {
                return response.ok ? response.json() : null;
            }).then(function (payload) {
                ackInFlight = false;
                if (payload && payload.ok === true) { hideModal(); }
                /* در صورت شکست، مدال باز می‌ماند؛ بستن بی‌ثبت سابقه ممکن نیست */
            }).catch(function () {
                ackInFlight = false;
            });
        });
    }

    poll();
    window.setInterval(poll, 60000);
})();
