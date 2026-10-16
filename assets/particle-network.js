/*
 * موتور افکت شبکهٔ ذرات معلق.
 * پس‌زمینهٔ تمام‌صفحه: data-config روی تگ script.
 * پیش‌نمایش: ParticleNetwork.mount(container, config) و متدهای update/destroy.
 */
(function () {
    'use strict';

    var DEFAULTS = {
        density: 4500,
        maxParticles: 320,
        speed: 0.25,
        linkDistance: 130,
        mouseRadius: 180,
        mouseStrength: 1,
        opacity: 1,
        dotScale: 1,
        color: '#172b36',
        showDots: true,
        showLinks: true,
        connectCursor: true,
        autoMove: true
    };
    var LIMITS = {
        density: [1800, 9000],
        maxParticles: [40, 450],
        speed: [0, 1],
        linkDistance: [50, 240],
        mouseRadius: [60, 320],
        mouseStrength: [0, 2],
        opacity: [0.1, 1],
        dotScale: [0.5, 2.5]
    };

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function asBoolean(value, fallback) {
        if (typeof value === 'boolean') {
            return value;
        }
        if (value === 1 || value === '1' || value === 'true' || value === 'on' || value === 'yes') {
            return true;
        }
        if (value === 0 || value === '0' || value === 'false' || value === 'off' || value === 'no') {
            return false;
        }
        return fallback;
    }

    function normalize(input) {
        var result = {};
        Object.keys(DEFAULTS).forEach(function (key) {
            result[key] = DEFAULTS[key];
        });
        if (input && typeof input === 'object') {
            Object.keys(DEFAULTS).forEach(function (key) {
                if (Object.prototype.hasOwnProperty.call(input, key) && input[key] !== undefined && input[key] !== null) {
                    result[key] = input[key];
                }
            });
        }
        Object.keys(LIMITS).forEach(function (key) {
            var parsed = Number(result[key]);
            if (!Number.isFinite(parsed)) {
                parsed = Number(DEFAULTS[key]);
            }
            result[key] = clamp(parsed, LIMITS[key][0], LIMITS[key][1]);
            if (key === 'density' || key === 'maxParticles' || key === 'linkDistance' || key === 'mouseRadius') {
                result[key] = Math.round(result[key]);
            }
        });
        result.color = /^#[0-9a-f]{6}$/i.test(String(result.color))
            ? String(result.color).toLowerCase()
            : DEFAULTS.color;
        ['showDots', 'showLinks', 'connectCursor', 'autoMove'].forEach(function (key) {
            result[key] = asBoolean(result[key], DEFAULTS[key]);
        });
        result._rgb = parseColor(result.color);
        return result;
    }

    function parseColor(hex) {
        var value = parseInt(String(hex).slice(1), 16);
        return ((value >> 16) & 255) + ', ' + ((value >> 8) & 255) + ', ' + (value & 255);
    }

    function inertInstance() {
        return {
            update: function () {},
            destroy: function () {}
        };
    }

    function create(container, inputConfig, options) {
        options = options || {};
        var fixed = options.fixed === true;
        if (!fixed && (!container || typeof container.appendChild !== 'function')) {
            return inertInstance();
        }
        var config = normalize(inputConfig || {});
        var canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.className = fixed
            ? 'particle-network-canvas particle-network-background'
            : 'particle-network-canvas particle-network-preview-canvas';
        canvas.style.cssText = fixed
            ? 'position:fixed;inset:0;width:100%;height:100%;z-index:-1;pointer-events:none;display:block'
            : 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:auto;display:block';

        if (fixed) {
            if (!document.body) {
                return inertInstance();
            }
            document.body.insertBefore(canvas, document.body.firstChild);
        } else {
            container.appendChild(canvas);
        }

        var context = null;
        try {
            context = canvas.getContext ? canvas.getContext('2d') : null;
        } catch (ignore) {
            context = null;
        }
        if (!context) {
            if (canvas.parentNode) {
                canvas.parentNode.removeChild(canvas);
            }
            return inertInstance();
        }

        var reducedQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
        var reduceMotion = !!(reducedQuery && reducedQuery.matches);
        var width = 1;
        var height = 1;
        var dpr = 1;
        var particles = [];
        var mouse = { x: -10000, y: -10000, active: false };
        var running = false;
        var destroyed = false;
        var rafId = 0;
        var lastTime = 0;
        var resizeObserver = null;
        var api;

        function dimensions() {
            if (fixed) {
                return { width: window.innerWidth || 1, height: window.innerHeight || 1 };
            }
            var rect = container.getBoundingClientRect();
            return { width: rect.width || 1, height: rect.height || 1 };
        }

        function makeParticle() {
            var angle = Math.random() * Math.PI * 2;
            var speed = 0.3 + Math.random() * 0.7;
            return {
                x: Math.random() * width,
                y: Math.random() * height,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                radius: 0.8 + Math.random() * 1.6
            };
        }

        function render() {
            if (destroyed) {
                return;
            }
            context.clearRect(0, 0, width, height);
            var rgb = config._rgb;
            var i;
            var particle;

            if (config.showLinks) {
                for (var a = 0; a < particles.length; a++) {
                    var first = particles[a];
                    for (var b = a + 1; b < particles.length; b++) {
                        var second = particles[b];
                        var dx = first.x - second.x;
                        var dy = first.y - second.y;
                        var distance = Math.hypot(dx, dy);
                        if (distance >= config.linkDistance) {
                            continue;
                        }
                        var boost = 0;
                        if (mouse.active) {
                            var midX = (first.x + second.x) * 0.5 - mouse.x;
                            var midY = (first.y + second.y) * 0.5 - mouse.y;
                            boost = Math.max(0, 1 - Math.hypot(midX, midY) / config.mouseRadius);
                        }
                        var alpha = (1 - distance / config.linkDistance)
                            * (0.15 + boost * 0.75 * config.mouseStrength)
                            * config.opacity;
                        if (alpha <= 0.01) {
                            continue;
                        }
                        context.strokeStyle = 'rgba(' + rgb + ', ' + Math.min(0.95, alpha).toFixed(3) + ')';
                        context.lineWidth = 0.7;
                        context.beginPath();
                        context.moveTo(first.x, first.y);
                        context.lineTo(second.x, second.y);
                        context.stroke();
                    }
                }
            }

            if (config.connectCursor && mouse.active && config.mouseStrength > 0) {
                for (i = 0; i < particles.length; i++) {
                    particle = particles[i];
                    var cursorDistance = Math.hypot(particle.x - mouse.x, particle.y - mouse.y);
                    if (cursorDistance >= config.mouseRadius) {
                        continue;
                    }
                    var cursorAlpha = (1 - cursorDistance / config.mouseRadius)
                        * 0.9 * config.mouseStrength * config.opacity;
                    if (cursorAlpha <= 0.01) {
                        continue;
                    }
                    context.strokeStyle = 'rgba(' + rgb + ', ' + Math.min(0.98, cursorAlpha).toFixed(3) + ')';
                    context.lineWidth = 0.8;
                    context.beginPath();
                    context.moveTo(particle.x, particle.y);
                    context.lineTo(mouse.x, mouse.y);
                    context.stroke();
                }
            }

            if (config.showDots) {
                context.fillStyle = 'rgba(' + rgb + ', ' + Math.min(1, 0.75 * config.opacity).toFixed(3) + ')';
                for (i = 0; i < particles.length; i++) {
                    particle = particles[i];
                    context.beginPath();
                    context.arc(particle.x, particle.y, particle.radius * config.dotScale, 0, Math.PI * 2);
                    context.fill();
                }
            }
        }

        function resize() {
            var size = dimensions();
            width = Math.max(1, Math.round(size.width));
            height = Math.max(1, Math.round(size.height));
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.round(width * dpr);
            canvas.height = Math.round(height * dpr);
            context.setTransform(dpr, 0, 0, dpr, 0, 0);
            var count = Math.min(config.maxParticles, Math.max(12, Math.floor((width * height) / config.density)));
            particles = [];
            for (var index = 0; index < count; index++) {
                particles.push(makeParticle());
            }
            render();
            start();
        }

        function canAnimate() {
            return !reduceMotion && config.autoMove && config.speed > 0;
        }

        function stop() {
            running = false;
            if (rafId) {
                window.cancelAnimationFrame(rafId);
            }
            rafId = 0;
        }

        function animate(timestamp) {
            if (!running || destroyed || document.hidden || !canAnimate()) {
                stop();
                return;
            }
            var delta = lastTime ? clamp((timestamp - lastTime) / (1000 / 60), 0.2, 2.5) : 1;
            lastTime = timestamp;
            for (var index = 0; index < particles.length; index++) {
                var point = particles[index];
                point.x += point.vx * config.speed * delta;
                point.y += point.vy * config.speed * delta;
                if (point.x < -10) { point.x = width + 10; } else if (point.x > width + 10) { point.x = -10; }
                if (point.y < -10) { point.y = height + 10; } else if (point.y > height + 10) { point.y = -10; }
            }
            render();
            rafId = window.requestAnimationFrame(animate);
        }

        function start() {
            if (running || destroyed || document.hidden || !canAnimate()) {
                return;
            }
            running = true;
            lastTime = 0;
            rafId = window.requestAnimationFrame(animate);
        }

        function localPosition(event) {
            if (fixed) {
                return { x: event.clientX, y: event.clientY };
            }
            var rect = container.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        }

        function onMove(event) {
            var point = localPosition(event);
            if (!fixed && (point.x < 0 || point.y < 0 || point.x > width || point.y > height)) {
                mouse.active = false;
            } else {
                mouse.x = point.x;
                mouse.y = point.y;
                mouse.active = true;
            }
            if (canAnimate()) {
                start();
            } else {
                render();
            }
        }

        function onLeave() {
            mouse.active = false;
            if (!canAnimate()) {
                render();
            }
        }

        function onResize() {
            resize();
        }

        function onVisibility() {
            if (document.hidden) {
                stop();
            } else if (canAnimate()) {
                start();
            } else {
                render();
            }
        }

        function onMotionPreference(event) {
            reduceMotion = !!event.matches;
            if (canAnimate()) {
                start();
            } else {
                stop();
                render();
            }
        }

        resize();
        if (!fixed && window.ResizeObserver) {
            resizeObserver = new window.ResizeObserver(onResize);
            resizeObserver.observe(container);
        } else {
            window.addEventListener('resize', onResize);
        }
        var eventTarget = fixed ? window : container;
        eventTarget.addEventListener('pointermove', onMove, { passive: true });
        if (!fixed) {
            container.addEventListener('pointerleave', onLeave, { passive: true });
        } else {
            document.addEventListener('mouseleave', onLeave, { passive: true });
            window.addEventListener('blur', onLeave);
        }
        document.addEventListener('visibilitychange', onVisibility);
        if (reducedQuery) {
            if (typeof reducedQuery.addEventListener === 'function') {
                reducedQuery.addEventListener('change', onMotionPreference);
            } else if (typeof reducedQuery.addListener === 'function') {
                reducedQuery.addListener(onMotionPreference);
            }
        }

        api = {
            update: function (nextConfig) {
                if (destroyed) {
                    return;
                }
                var merged = {};
                Object.keys(DEFAULTS).forEach(function (key) {
                    merged[key] = config[key];
                });
                if (nextConfig && typeof nextConfig === 'object') {
                    Object.keys(DEFAULTS).forEach(function (key) {
                        if (Object.prototype.hasOwnProperty.call(nextConfig, key)) {
                            merged[key] = nextConfig[key];
                        }
                    });
                }
                var rebuildParticles = merged.density !== config.density || merged.maxParticles !== config.maxParticles;
                config = normalize(merged);
                if (rebuildParticles) {
                    resize();
                } else {
                    render();
                    if (canAnimate()) {
                        start();
                    } else {
                        stop();
                    }
                }
            },
            destroy: function () {
                if (destroyed) {
                    return;
                }
                destroyed = true;
                stop();
                if (resizeObserver) {
                    resizeObserver.disconnect();
                } else {
                    window.removeEventListener('resize', onResize);
                }
                var target = fixed ? window : container;
                target.removeEventListener('pointermove', onMove);
                if (!fixed) {
                    container.removeEventListener('pointerleave', onLeave);
                } else {
                    document.removeEventListener('mouseleave', onLeave);
                    window.removeEventListener('blur', onLeave);
                }
                document.removeEventListener('visibilitychange', onVisibility);
                if (reducedQuery) {
                    if (typeof reducedQuery.removeEventListener === 'function') {
                        reducedQuery.removeEventListener('change', onMotionPreference);
                    } else if (typeof reducedQuery.removeListener === 'function') {
                        reducedQuery.removeListener(onMotionPreference);
                    }
                }
                if (canvas.parentNode) {
                    canvas.parentNode.removeChild(canvas);
                }
                if (fixed && window.__particleNetworkBackground === api) {
                    window.__particleNetworkBackground = null;
                }
            }
        };
        return api;
    }

    function bootBackground(config) {
        if (window.__particleNetworkBackground && typeof window.__particleNetworkBackground.destroy === 'function') {
            window.__particleNetworkBackground.destroy();
        }
        window.__particleNetworkBackground = create(document.body, config || {}, { fixed: true });
        return window.__particleNetworkBackground;
    }

    window.ParticleNetwork = {
        version: '1.56.1',
        defaults: Object.assign({}, DEFAULTS),
        normalize: normalize,
        mount: function (container, config) {
            return create(container, config || {}, { fixed: false });
        },
        background: bootBackground
    };

    var currentScript = document.currentScript;
    if (currentScript && currentScript.hasAttribute('data-config')) {
        var initialConfig = {};
        try {
            initialConfig = JSON.parse(currentScript.getAttribute('data-config') || '{}') || {};
        } catch (ignore) {
            initialConfig = {};
        }
        var boot = function () {
            window.ParticleNetwork.background(initialConfig);
        };
        if (document.body) {
            boot();
        } else {
            document.addEventListener('DOMContentLoaded', boot, { once: true });
        }
    }
})();
