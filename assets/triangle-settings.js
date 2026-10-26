/* کنترل پیش‌نمایش زنده و فهرست صفحه‌های افکت نمایشی */
(function () {
    'use strict';

    var PRESETS = {
        default: {
            cellSize: 46, mouseRadiusMult: 5.0, repulsionMult: 1.35,
            springK: 0.085, damping: 0.80, waveSpeed: 620,
            waveDuration: 1.6, waveWidth: 70, waveAmpMult: 0.32,
            idleAmp: 1.6, opacity: 1, meshColor: '#475569', dotColor: '#334155',
            showDots: true, maskVignette: true
        },
        ocean: {
            cellSize: 42, mouseRadiusMult: 4.5, repulsionMult: 1.2,
            springK: 0.045, damping: 0.92, waveSpeed: 480,
            waveDuration: 2.2, waveWidth: 80, waveAmpMult: 0.65,
            idleAmp: 2.4, opacity: 1, meshColor: '#0284c7', dotColor: '#0369a1',
            showDots: true, maskVignette: true
        },
        magnet: {
            cellSize: 48, mouseRadiusMult: 7.2, repulsionMult: 2.6,
            springK: 0.12, damping: 0.78, waveSpeed: 750,
            waveDuration: 1.6, waveWidth: 70, waveAmpMult: 0.45,
            idleAmp: 1, opacity: 1, meshColor: '#334155', dotColor: '#1e293b',
            showDots: true, maskVignette: true
        },
        soft: {
            cellSize: 56, mouseRadiusMult: 5, repulsionMult: 1,
            springK: 0.07, damping: 0.85, waveSpeed: 560,
            waveDuration: 1.6, waveWidth: 70, waveAmpMult: 0.3,
            idleAmp: 1.2, opacity: 0.45, meshColor: '#64748b', dotColor: '#64748b',
            showDots: false, maskVignette: true
        }
    };

    function init() {
        var form = document.getElementById('triangle-form');
        var previewNode = document.getElementById('triangle-preview');
        if (!form || !previewNode || !window.TriangleMesh || typeof window.TriangleMesh.mount !== 'function') {
            return;
        }

        var preview = null;
        var effectTabs = Array.prototype.slice.call(form.querySelectorAll('[data-effect-tab]'));
        var effectPanels = Array.prototype.slice.call(form.querySelectorAll('[data-effect-panel]'));
        var activeEffectField = form.querySelector('[name="effect_type"]');
        var settingControls = Array.prototype.slice.call(form.querySelectorAll('[data-tm-key]'));
        var pageControls = Array.prototype.slice.call(form.querySelectorAll('input[name="triangle_pages[]"]'));
        var countNode = form.querySelector('[data-tm-count]');
        var searchInput = form.querySelector('[data-tm-search]');
        var noResults = form.querySelector('[data-tm-no-results]');

        function readConfig() {
            var config = {};
            var controls = form.querySelectorAll('[data-tm-key]');
            Array.prototype.forEach.call(controls, function (control) {
                var key = control.getAttribute('data-tm-key');
                if (!key) {
                    return;
                }
                if (control.type === 'checkbox') {
                    config[key] = !!control.checked;
                } else if (control.type === 'range' || control.type === 'number') {
                    var number = Number(control.value);
                    if (Number.isFinite(number)) {
                        config[key] = number;
                    }
                } else {
                    config[key] = control.value;
                }
            });
            return config;
        }

        function updateOutputs() {
            Array.prototype.forEach.call(form.querySelectorAll('[data-tm-out]'), function (output) {
                var key = output.getAttribute('data-tm-out');
                var control = form.querySelector('[data-tm-key="' + key + '"]');
                if (control) {
                    var displayed = control.value;
                    if (control.type === 'range') {
                        try {
                            displayed = Number(control.value).toLocaleString('fa-IR', { maximumFractionDigits: 3 });
                        } catch (ignore) {
                            displayed = control.value;
                        }
                    }
                    output.value = displayed;
                    output.textContent = displayed;
                }
            });
        }

        function activeEffect() {
            return activeEffectField && activeEffectField.value === 'particles' ? 'particles' : 'triangle';
        }

        function mountTrianglePreview() {
            if (!preview) {
                preview = window.TriangleMesh.mount(previewNode, readConfig());
            }
        }

        function destroyTrianglePreview() {
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

        function activateEffectTab(effect, updateField) {
            if (effect !== 'particles') {
                effect = 'triangle';
            }
            if (updateField && activeEffectField) {
                activeEffectField.value = effect;
            }
            effectTabs.forEach(function (tab) {
                var selected = tab.getAttribute('data-effect-tab') === effect;
                tab.classList.toggle('active', selected);
                tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                tab.tabIndex = selected ? 0 : -1;
            });
            effectPanels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-effect-panel') !== effect;
            });
            if (effect === 'triangle') {
                mountTrianglePreview();
            } else {
                destroyTrianglePreview();
            }
            document.dispatchEvent(new CustomEvent('ticket:effect-tab-change', { detail: { effect: effect } }));
        }

        effectTabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                activateEffectTab(tab.getAttribute('data-effect-tab'), true);
            });
            tab.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }
                event.preventDefault();
                var delta = event.key === 'ArrowLeft' ? 1 : -1;
                var next = (index + delta + effectTabs.length) % effectTabs.length;
                effectTabs[next].focus();
                effectTabs[next].click();
            });
        });

        function updatePageCount() {
            var count = pageControls.reduce(function (total, control) {
                return total + (control.checked ? 1 : 0);
            }, 0);
            if (countNode) {
                var chosen = String(count);
                var total = String(pageControls.length);
                try {
                    chosen = count.toLocaleString('fa-IR');
                    total = pageControls.length.toLocaleString('fa-IR');
                } catch (ignore) {}
                countNode.textContent = chosen + ' از ' + total + ' صفحه انتخاب شده';
            }
        }

        function filterPages() {
            var query = searchInput ? searchInput.value.trim().toLocaleLowerCase() : '';
            var visibleCount = 0;
            Array.prototype.forEach.call(form.querySelectorAll('.tm-group'), function (group) {
                var groupVisible = 0;
                Array.prototype.forEach.call(group.querySelectorAll('.tm-page'), function (label) {
                    var text = (label.textContent || '').toLocaleLowerCase();
                    var visible = !query || text.indexOf(query) !== -1;
                    label.hidden = !visible;
                    if (visible) {
                        groupVisible++;
                        visibleCount++;
                    }
                });
                group.hidden = groupVisible === 0;
            });
            if (noResults) {
                noResults.hidden = visibleCount !== 0;
            }
        }

        form.addEventListener('input', function (event) {
            var target = event.target;
            if (target && target.matches && target.matches('[data-tm-key]')) {
                syncPreview();
            } else if (target === searchInput) {
                filterPages();
            }
        });
        form.addEventListener('change', function (event) {
            var target = event.target;
            if (target && target.matches && target.matches('[data-tm-key]')) {
                syncPreview();
            } else if (target && target.matches && target.matches('input[name="triangle_pages[]"]')) {
                updatePageCount();
            }
        });
        if (searchInput) {
            searchInput.addEventListener('search', filterPages);
        }

        Array.prototype.forEach.call(form.querySelectorAll('[data-tm-all]'), function (button) {
            button.addEventListener('click', function () {
                var checked = button.getAttribute('data-tm-all') === '1';
                pageControls.forEach(function (control) {
                    control.checked = checked;
                });
                updatePageCount();
            });
        });

        Array.prototype.forEach.call(form.querySelectorAll('[data-tm-preset]'), function (button) {
            button.addEventListener('click', function () {
                var preset = PRESETS[button.getAttribute('data-tm-preset')];
                if (!preset) {
                    return;
                }
                settingControls.forEach(function (control) {
                    var key = control.getAttribute('data-tm-key');
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
            });
        });

        var rippleButton = form.querySelector('[data-tm-ripple]');
        if (rippleButton) {
            rippleButton.addEventListener('click', function () {
                if (preview) {
                    preview.ripple();
                }
            });
        }

        updateOutputs();
        updatePageCount();
        filterPages();
        activateEffectTab(activeEffect(), false);
        window.addEventListener('pagehide', destroyTrianglePreview, { once: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
