(function () {
    'use strict';

    var modal = document.getElementById('inspection-food-reminder');
    if (!modal) return;

    var userId = modal.getAttribute('data-user-id') || '0';
    var zone = modal.getAttribute('data-timezone') || 'Asia/Tehran';
    var serverNow = Number(modal.getAttribute('data-server-now')) || Math.floor(Date.now() / 1000);
    var nextAt = Number(modal.getAttribute('data-next-reminder-at')) || 0;
    var todayAt = Number(modal.getAttribute('data-today-reminder-at')) || 0;
    var dueNow = modal.getAttribute('data-due-now') === '1';
    var serverClockOffset = serverNow * 1000 - Date.now();
    var dismiss = modal.querySelector('.inspection-food-reminder-dismiss');
    var timer = null;
    var storagePrefix = 'inspection-food-absence-reminder:' + userId + ':';

    function nowSeconds() {
        return Math.floor((Date.now() + serverClockOffset) / 1000);
    }

    function dayKey(timestamp) {
        try {
            var parts = new Intl.DateTimeFormat('en-CA', {
                timeZone: zone,
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).formatToParts(new Date(timestamp * 1000));
            var values = {};
            parts.forEach(function (part) {
                if (part.type !== 'literal') values[part.type] = part.value;
            });
            return values.year + '-' + values.month + '-' + values.day;
        } catch (ignore) {
            return new Date(timestamp * 1000).toISOString().slice(0, 10);
        }
    }

    function wasShown(timestamp) {
        try {
            return window.localStorage.getItem(storagePrefix + dayKey(timestamp)) === '1';
        } catch (ignore) {
            return false;
        }
    }

    function markShown(timestamp) {
        try {
            window.localStorage.setItem(storagePrefix + dayKey(timestamp), '1');
        } catch (ignore) {}
    }

    function playAlarm() {
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        try {
            var context = new AudioContext();
            var gain = context.createGain();
            gain.gain.setValueAtTime(0.035, context.currentTime);
            gain.connect(context.destination);
            [880, 660, 880].forEach(function (frequency, index) {
                var oscillator = context.createOscillator();
                var start = context.currentTime + index * 0.24;
                oscillator.frequency.value = frequency;
                oscillator.type = 'sine';
                oscillator.connect(gain);
                oscillator.start(start);
                oscillator.stop(start + 0.16);
            });
            window.setTimeout(function () {
                if (context.close) context.close().catch(function () {});
            }, 1100);
        } catch (ignore) {}
    }

    function show(timestamp) {
        if (wasShown(timestamp)) return;
        markShown(timestamp);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-visible');
        playAlarm();
        if (dismiss) dismiss.focus();
    }

    function hide() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
    }

    function schedule(timestamp) {
        if (timer) window.clearTimeout(timer);
        var delay = Math.max(0, timestamp * 1000 - (Date.now() + serverClockOffset));
        timer = window.setTimeout(function () {
            var current = nowSeconds();
            if (current >= timestamp) show(timestamp);
            schedule(timestamp + 86400);
        }, Math.min(delay, 2147483000));
    }

    if (dueNow && !wasShown(todayAt)) show(todayAt);
    schedule(nextAt > nowSeconds() ? nextAt : nowSeconds() + 86400);

    if (dismiss) dismiss.addEventListener('click', hide);
    document.addEventListener('keydown', function (event) {
        if (!modal.hidden && event.key === 'Escape') {
            event.preventDefault();
            hide();
        }
    });
}());
