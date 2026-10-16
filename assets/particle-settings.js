/* کنترل تب ذرات معلق و پیش‌نمایش زندهٔ آن در تنظیمات سامانه */
(function () {
    'use strict';

    function init() {
        var form = document.getElementById('triangle-form');
        var previewNode = document.getElementById('particle-preview');
        if (!form || !previewNode || !window.ParticleNetwork || typeof window.ParticleNetwork.mount !== 'function') {
            return;
        }

        var controls = Array.prototype.slice.call(form.querySelectorAll('[data-particle-key]'));
        var preview = null;
        var presets = {
            'default': {
                density: 4500, maxParticles: 320, speed: 0.25, linkDistance: 130,
                mouseRadius: 180, mouseStrength: 1, opacity: 1, dotScale: 1,
                color: '#172b36', showDots: true, showLinks: true,
                connectCursor: true, autoMove: true
            },
            calm: {
                density: 6800, maxParticles: 180, speed: 0.12, linkDistance: 100,
                mouseRadius: 135, mouseStrength: 0.55, opacity: 0.55, dotScale: 0.85,
                color: '#36817a', showDots: true, showLinks: true,
                connectCursor: true, autoMove: true
            },
            dense: {
                density: 2300, maxParticles: 380, speed: 0.2, linkDistance: 155,
                mouseRadius: 180, mouseStrength: 0.8, opacity: 0.72, dotScale: 0.9,
                color: '#293849', showDots: true, showLinks: true,
                connectCursor: true, autoMove: true
            },
            reactive: {
                density: 4000, maxParticles: 320, speed: 0.3, linkDistance: 135,
                mouseRadius: 275, mouseStrength: 1.7, opacity: 1, dotScale: 1.2,
                color: '#111827', showDots: true, showLinks: true,
                connectCursor: true, autoMove: true
            }
        };

        function activeEffect() {
            var field = form.querySelector('[name="effect_type"]');
            return field && field.value === 'particles' ? 'particles' : 'triangle';
        }

        function readConfig() {
            var config = {};
            controls.forEach(function (control) {
                var key = control.getAttribute('data-particle-key');
                if (!key) {
                    return;
                }
                if (control.type === 'checkbox') {
                    config[key] = !!control.checked;
                } else if (control.type === 'range' || control.type === 'number') {
                    var value = Number(control.value);
                    if (Number.isFinite(value)) {
                        config[key] = value;
                    }
                } else {
                    config[key] = control.value;
                }
            });
            return config;
        }

        function updateOutputs() {
            controls.forEach(function (control) {
                if (control.type !== 'range') {
                    return;
                }
                var output = form.querySelector('[data-particle-out="' + control.getAttribute('data-particle-key') + '"]');
                if (!output) {
                    return;
                }
                var value = control.value;
                try {
                    value = Number(value).toLocaleString('fa-IR', { maximumFractionDigits: 2 });
                } catch (ignore) {}
                output.value = value;
                output.textContent = value;
            });
        }

        function mountPreview() {
            if (!preview && activeEffect() === 'particles') {
                preview = window.ParticleNetwork.mount(previewNode, readConfig());
            }
        }

        function destroyPreview() {
            if (preview) {
                preview.destroy();
                preview = null;
            }
        }

        function syncPreview() {
            updateOutputs();
            if (preview) {
                preview.update(readConfig());
            }
        }

        function applyPreset(name) {
            var preset = presets[name];
            if (!preset) {
                return;
            }
            controls.forEach(function (control) {
                var key = control.getAttribute('data-particle-key');
                if (!Object.prototype.hasOwnProperty.call(preset, key)) {
                    return;
                }
                if (control.type === 'checkbox') {
                    control.checked = !!preset[key];
                } else {
                    control.value = String(preset[key]);
                }
            });
            syncPreview();
        }

        form.addEventListener('input', function (event) {
            if (event.target && event.target.matches && event.target.matches('[data-particle-key]')) {
                syncPreview();
            }
        });
        form.addEventListener('change', function (event) {
            if (event.target && event.target.matches && event.target.matches('[data-particle-key]')) {
                syncPreview();
            }
        });
        Array.prototype.forEach.call(form.querySelectorAll('[data-particle-preset]'), function (button) {
            button.addEventListener('click', function () {
                applyPreset(button.getAttribute('data-particle-preset'));
            });
        });
        document.addEventListener('ticket:effect-tab-change', function (event) {
            if (event.detail && event.detail.effect === 'particles') {
                mountPreview();
            } else {
                destroyPreview();
            }
        });

        updateOutputs();
        mountPreview();
        window.addEventListener('pagehide', destroyPreview, { once: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
