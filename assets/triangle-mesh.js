/*
 * افکت نمایشی شبکهٔ مثلثی.
 * پس‌زمینه: تگ اسکریپت با data-config.
 * پیش‌نمایش: TriangleMesh.mount(container, config) و متدهای update/ripple/destroy.
 */
(function () {
    'use strict';

    var DEFAULTS = {
        cellSize: 46,
        mouseRadiusMult: 5.0,
        repulsionMult: 1.35,
        springK: 0.085,
        damping: 0.80,
        waveSpeed: 620,
        waveDuration: 1.6,
        waveWidth: 70,
        waveAmpMult: 0.32,
        idleAmp: 1.6,
        opacity: 1,
        meshColor: '#475569',
        dotColor: '#334155',
        showDots: true,
        maskVignette: true
    };
    var LIMITS = {
        cellSize: [28, 70], mouseRadiusMult: [1, 12], repulsionMult: [0, 4],
        springK: [0.01, 0.3], damping: [0.5, 0.98], waveSpeed: [100, 1500],
        waveDuration: [0.3, 4], waveWidth: [20, 200], waveAmpMult: [0, 1.5],
        idleAmp: [0, 6], opacity: [0.1, 1]
    };
    var VIGNETTE = 'radial-gradient(ellipse at center, #000 35%, transparent 92%)';

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function parseColor(value, fallback) {
        var match = /^#?([0-9a-f]{6})$/i.exec(String(value || ''));
        if (!match) {
            return fallback;
        }
        var number = parseInt(match[1], 16);
        return ((number >> 16) & 255) + ', ' + ((number >> 8) & 255) + ', ' + (number & 255);
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
        });
        result.meshColor = /^#[0-9a-f]{6}$/i.test(String(result.meshColor)) ? String(result.meshColor).toLowerCase() : DEFAULTS.meshColor;
        result.dotColor = /^#[0-9a-f]{6}$/i.test(String(result.dotColor)) ? String(result.dotColor).toLowerCase() : DEFAULTS.dotColor;
        result.showDots = asBoolean(result.showDots, DEFAULTS.showDots);
        result.maskVignette = asBoolean(result.maskVignette, DEFAULTS.maskVignette);
        result._mesh = parseColor(result.meshColor, '71, 85, 105');
        result._dot = parseColor(result.dotColor, '51, 65, 85');
        return result;
    }

    function inertInstance() {
        return {
            update: function () {},
            ripple: function () {},
            destroy: function () {}
        };
    }

    function create(options) {
        options = options || {};
        var fixed = options.fixed === true;
        var container = options.container || null;
        if (!fixed && (!container || typeof container.appendChild !== 'function')) {
            return inertInstance();
        }
        var cfg = normalize(options.config || {});
        var canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.className = 'triangle-mesh-canvas' + (fixed ? ' triangle-mesh-background' : ' triangle-mesh-preview-canvas');
        canvas.style.cssText = fixed
            ? 'position:fixed;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;display:block'
            : 'position:absolute;inset:0;width:100%;height:100%;display:block;pointer-events:auto';

        if (fixed) {
            if (!document.body) {
                return inertInstance();
            }
            document.body.insertBefore(canvas, document.body.firstChild);
            document.body.classList.add('triangle-mesh-background-active');
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
            if (fixed && document.body) {
                document.body.classList.remove('triangle-mesh-background-active');
            }
            return inertInstance();
        }

        var reducedQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
        var reduceMotion = !!(reducedQuery && reducedQuery.matches);
        var width = 1;
        var height = 1;
        var dpr = 1;
        var spacing = cfg.cellSize;
        var columns = 0;
        var rows = 0;
        var pointCount = 0;
        var edgeCount = 0;
        var baseX;
        var baseY;
        var currentX;
        var currentY;
        var velocityX;
        var velocityY;
        var phases;
        var edges;
        var mouse = { x: -10000, y: -10000, targetX: -10000, targetY: -10000, active: false };
        var ripples = [];
        var running = false;
        var destroyed = false;
        var rafId = 0;
        var lastTime = 0;
        var lastBuiltCell = -1;
        var resizeObserver = null;
        var api = null;

        function applyMask() {
            var mask = cfg.maskVignette ? VIGNETTE : 'none';
            canvas.style.webkitMaskImage = mask;
            canvas.style.maskImage = mask;
        }

        function size() {
            if (fixed) {
                return { width: window.innerWidth || 1, height: window.innerHeight || 1 };
            }
            var rect = container.getBoundingClientRect();
            return { width: rect.width || 1, height: rect.height || 1 };
        }

        function rebuild() {
            var dimensions = size();
            width = Math.max(1, Math.round(dimensions.width));
            height = Math.max(1, Math.round(dimensions.height));
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.round(width * dpr);
            canvas.height = Math.round(height * dpr);
            context.setTransform(dpr, 0, 0, dpr, 0, 0);

            spacing = clamp(Number(cfg.cellSize), 28, 70);
            lastBuiltCell = cfg.cellSize;
            var rowHeight = 0.8660254 * spacing;
            columns = Math.ceil(width / spacing) + 3;
            rows = Math.ceil(height / rowHeight) + 3;
            pointCount = columns * rows;
            baseX = new Float32Array(pointCount);
            baseY = new Float32Array(pointCount);
            currentX = new Float32Array(pointCount);
            currentY = new Float32Array(pointCount);
            velocityX = new Float32Array(pointCount);
            velocityY = new Float32Array(pointCount);
            phases = new Float32Array(pointCount);

            var row;
            var col;
            var index;
            for (row = 0; row < rows; row++) {
                var offsetX = row % 2 === 0 ? 0 : spacing / 2;
                for (col = 0; col < columns; col++) {
                    index = row * columns + col;
                    baseX[index] = col * spacing + offsetX - spacing;
                    baseY[index] = row * rowHeight - rowHeight;
                    currentX[index] = baseX[index];
                    currentY[index] = baseY[index];
                    phases[index] = Math.random() * Math.PI * 2;
                }
            }

            var edgeList = [];
            for (row = 0; row < rows; row++) {
                var evenRow = row % 2 === 0;
                for (col = 0; col < columns; col++) {
                    index = row * columns + col;
                    if (col + 1 < columns) {
                        edgeList.push(index, index + 1);
                    }
                    if (row + 1 < rows) {
                        var below = (row + 1) * columns + col;
                        edgeList.push(index, below);
                        if (evenRow && col > 0) {
                            edgeList.push(index, below - 1);
                        } else if (!evenRow && col + 1 < columns) {
                            edgeList.push(index, below + 1);
                        }
                    }
                }
            }
            edges = Int32Array.from(edgeList);
            edgeCount = edges.length / 2;
        }

        function render() {
            context.clearRect(0, 0, width, height);
            var radius = cfg.mouseRadiusMult * spacing;
            var active = mouse.active;
            var bucketCount = 9;
            var buckets = [];
            for (var b = 0; b < bucketCount; b++) {
                buckets.push([]);
            }

            for (var edge = 0; edge < edgeCount; edge++) {
                var first = edges[edge * 2];
                var second = edges[edge * 2 + 1];
                var x1 = currentX[first];
                var y1 = currentY[first];
                var x2 = currentX[second];
                var y2 = currentY[second];
                var length = Math.hypot(x2 - x1, y2 - y1);
                var stretch = Math.abs(length - spacing) / spacing;
                var alpha = 0.085 + Math.min(0.55, stretch * 2.2);
                if (active) {
                    var distance = Math.hypot((x1 + x2) * 0.5 - mouse.x, (y1 + y2) * 0.5 - mouse.y);
                    if (distance < radius) {
                        var falloff = 1 - distance / radius;
                        alpha += falloff * falloff * 0.5;
                    }
                }
                var bucketIndex = clamp(Math.floor(bucketCount * alpha), 0, bucketCount - 1);
                buckets[bucketIndex].push(x1, y1, x2, y2);
            }

            context.lineWidth = 1;
            context.lineCap = 'round';
            for (var bucket = 0; bucket < bucketCount; bucket++) {
                var lines = buckets[bucket];
                if (!lines.length) {
                    continue;
                }
                context.beginPath();
                for (var offset = 0; offset < lines.length; offset += 4) {
                    context.moveTo(lines[offset], lines[offset + 1]);
                    context.lineTo(lines[offset + 2], lines[offset + 3]);
                }
                var lineAlpha = Math.min(0.92, (bucket + 0.5) / bucketCount) * cfg.opacity;
                context.strokeStyle = 'rgba(' + cfg._mesh + ', ' + lineAlpha.toFixed(3) + ')';
                context.stroke();
            }

            if (!cfg.showDots) {
                return;
            }
            context.fillStyle = 'rgba(' + cfg._dot + ', ' + (0.42 * cfg.opacity).toFixed(3) + ')';
            for (var point = 0; point < pointCount; point++) {
                context.fillRect(currentX[point] - 1, currentY[point] - 1, 2, 2);
            }
            if (active) {
                context.fillStyle = 'rgba(' + cfg._dot + ', ' + Math.min(1, 0.95 * cfg.opacity).toFixed(3) + ')';
                for (var activePoint = 0; activePoint < pointCount; activePoint++) {
                    var dotDistance = Math.hypot(currentX[activePoint] - mouse.x, currentY[activePoint] - mouse.y);
                    if (dotDistance < radius) {
                        var dotFalloff = 1 - dotDistance / radius;
                        var dotSize = 2 + dotFalloff * dotFalloff * 4;
                        context.fillRect(currentX[activePoint] - dotSize / 2, currentY[activePoint] - dotSize / 2, dotSize, dotSize);
                    }
                }
            }
        }

        function hasMotion() {
            if (!fixed && (width <= 1 || height <= 1)) {
                return false;
            }
            if (!reduceMotion && cfg.idleAmp > 0) {
                return true;
            }
            if (mouse.active || ripples.length > 0) {
                return true;
            }
            for (var index = 0; index < pointCount; index++) {
                if (Math.abs(velocityX[index]) > 0.025 || Math.abs(velocityY[index]) > 0.025 ||
                    Math.abs(currentX[index] - baseX[index]) > 0.04 || Math.abs(currentY[index] - baseY[index]) > 0.04) {
                    return true;
                }
            }
            return false;
        }

        function physics(delta, now) {
            var radius = cfg.mouseRadiusMult * spacing;
            var repulsion = cfg.repulsionMult * spacing;
            var spring = cfg.springK * delta;
            var damping = Math.pow(cfg.damping, delta);
            var idle = reduceMotion ? 0 : cfg.idleAmp;

            if (mouse.active) {
                var follow = Math.min(1, 0.22 * delta);
                mouse.x += (mouse.targetX - mouse.x) * follow;
                mouse.y += (mouse.targetY - mouse.y) * follow;
            }
            var mouseX = mouse.x;
            var mouseY = mouse.y;

            for (var index = 0; index < pointCount; index++) {
                var targetX = baseX[index];
                var targetY = baseY[index];
                if (idle > 0) {
                    var phase = phases[index];
                    targetX += Math.sin(0.0006 * now + phase) * idle;
                    targetY += Math.cos(0.0005 * now + 1.3 * phase) * idle;
                }
                if (mouse.active) {
                    var dx = targetX - mouseX;
                    var dy = targetY - mouseY;
                    var distance = Math.hypot(dx, dy);
                    if (distance < radius && distance > 0.001) {
                        var falloff = 1 - distance / radius;
                        var force = falloff * falloff * repulsion;
                        targetX += (dx / distance) * force;
                        targetY += (dy / distance) * force;
                    }
                }
                velocityX[index] += (targetX - currentX[index]) * spring;
                velocityY[index] += (targetY - currentY[index]) * spring;
                velocityX[index] *= damping;
                velocityY[index] *= damping;
                currentX[index] += velocityX[index] * delta;
                currentY[index] += velocityY[index] * delta;
            }

            for (var rippleIndex = ripples.length - 1; rippleIndex >= 0; rippleIndex--) {
                var ripple = ripples[rippleIndex];
                var elapsed = (now - ripple.time) / 1000;
                if (elapsed > cfg.waveDuration) {
                    ripples.splice(rippleIndex, 1);
                    continue;
                }
                var waveRadius = cfg.waveSpeed * elapsed;
                var amplitude = (1 - elapsed / cfg.waveDuration) * spacing * cfg.waveAmpMult * delta;
                for (var ripplePoint = 0; ripplePoint < pointCount; ripplePoint++) {
                    var rx = baseX[ripplePoint] - ripple.x;
                    var ry = baseY[ripplePoint] - ripple.y;
                    var distanceFromOrigin = Math.hypot(rx, ry);
                    var difference = Math.abs(distanceFromOrigin - waveRadius);
                    if (difference < cfg.waveWidth && distanceFromOrigin > 0.001) {
                        var waveFalloff = 1 - difference / cfg.waveWidth;
                        velocityX[ripplePoint] += (rx / distanceFromOrigin) * waveFalloff * amplitude;
                        velocityY[ripplePoint] += (ry / distanceFromOrigin) * waveFalloff * amplitude;
                    }
                }
            }
        }

        function stop() {
            running = false;
            if (rafId) {
                window.cancelAnimationFrame(rafId);
            }
            rafId = 0;
        }

        function animate(timestamp) {
            if (!running || destroyed || document.hidden) {
                stop();
                return;
            }
            var delta = lastTime ? Math.min(2.5, Math.max(0.1, (timestamp - lastTime) / (1000 / 60))) : 1;
            lastTime = timestamp;
            physics(delta, timestamp);
            render();
            if (hasMotion()) {
                rafId = window.requestAnimationFrame(animate);
            } else {
                stop();
            }
        }

        function start() {
            if (running || destroyed || document.hidden || !hasMotion()) {
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

        function addRipple(x, y) {
            ripples.push({ x: x, y: y, time: window.performance.now() });
            if (ripples.length > 8) {
                ripples.shift();
            }
            start();
        }

        function onMove(event) {
            var point = localPosition(event);
            if (!fixed && (point.x < 0 || point.y < 0 || point.x > width || point.y > height)) {
                mouse.active = false;
                start();
                return;
            }
            if (!mouse.active) {
                mouse.x = point.x;
                mouse.y = point.y;
            }
            mouse.targetX = point.x;
            mouse.targetY = point.y;
            mouse.active = true;
            start();
        }

        function onDown(event) {
            var target = event.target;
            if (fixed && target && typeof target.closest === 'function' &&
                target.closest('button, input, textarea, select, option, a, label, summary, [role="button"], [role="dialog"], [contenteditable="true"]')) {
                return;
            }
            var point = localPosition(event);
            if (point.x >= 0 && point.y >= 0 && point.x <= width && point.y <= height) {
                addRipple(point.x, point.y);
            }
        }

        function onUp(event) {
            if (event.pointerType === 'touch') {
                mouse.active = false;
                start();
            }
        }

        function onLeave() {
            mouse.active = false;
            start();
        }

        function onOut(event) {
            if (fixed && event.relatedTarget === null) {
                mouse.active = false;
                start();
            }
        }

        function onVisibility() {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        }

        function onResize() {
            rebuild();
            render();
            start();
        }

        function onMotionPreference(event) {
            reduceMotion = !!event.matches;
            start();
        }

        applyMask();
        rebuild();
        render();
        start();

        if (!fixed && window.ResizeObserver) {
            resizeObserver = new window.ResizeObserver(onResize);
            resizeObserver.observe(container);
        } else {
            window.addEventListener('resize', onResize);
        }
        var eventTarget = fixed ? window : container;
        eventTarget.addEventListener('pointermove', onMove, { passive: true });
        eventTarget.addEventListener('pointerdown', onDown, { passive: true });
        window.addEventListener('pointerup', onUp, { passive: true });
        window.addEventListener('pointercancel', onUp, { passive: true });
        window.addEventListener('pointerout', onOut, { passive: true });
        window.addEventListener('blur', onLeave);
        if (!fixed) {
            container.addEventListener('pointerleave', onLeave);
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
                    merged[key] = cfg[key];
                });
                if (nextConfig && typeof nextConfig === 'object') {
                    Object.keys(DEFAULTS).forEach(function (key) {
                        if (Object.prototype.hasOwnProperty.call(nextConfig, key)) {
                            merged[key] = nextConfig[key];
                        }
                    });
                }
                var previousCellSize = cfg.cellSize;
                cfg = normalize(merged);
                applyMask();
                if (cfg.cellSize !== previousCellSize) {
                    rebuild();
                }
                render();
                start();
            },
            ripple: function () {
                addRipple(width * 0.5, height * 0.5);
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
                eventTarget.removeEventListener('pointermove', onMove);
                eventTarget.removeEventListener('pointerdown', onDown);
                window.removeEventListener('pointerup', onUp);
                window.removeEventListener('pointercancel', onUp);
                window.removeEventListener('pointerout', onOut);
                window.removeEventListener('blur', onLeave);
                if (!fixed) {
                    container.removeEventListener('pointerleave', onLeave);
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
                if (fixed && document.body && window.__triangleMeshBackground === api) {
                    window.__triangleMeshBackground = null;
                    document.body.classList.remove('triangle-mesh-background-active');
                }
            }
        };
        return api;
    }

    function bootBackground(config) {
        if (window.__triangleMeshBackground && typeof window.__triangleMeshBackground.destroy === 'function') {
            window.__triangleMeshBackground.destroy();
        }
        window.__triangleMeshBackground = create({ fixed: true, config: config || {} });
        return window.__triangleMeshBackground;
    }

    if (!window.TriangleMesh || window.TriangleMesh.version !== '1.56') {
        window.TriangleMesh = {
            version: '1.56',
            defaults: Object.assign({}, DEFAULTS),
            mount: function (container, config) {
                return create({ container: container, config: config || {}, fixed: false });
            },
            background: bootBackground
        };
    }

    var currentScript = document.currentScript;
    if (currentScript && currentScript.hasAttribute('data-config')) {
        var initialConfig = {};
        try {
            initialConfig = JSON.parse(currentScript.getAttribute('data-config') || '{}') || {};
        } catch (ignore) {
            initialConfig = {};
        }
        var boot = function () {
            if (window.TriangleMesh && typeof window.TriangleMesh.background === 'function') {
                window.TriangleMesh.background(initialConfig);
            }
        };
        if (document.body) {
            boot();
        } else {
            document.addEventListener('DOMContentLoaded', boot, { once: true });
        }
    }
})();
