/* Runtime smoke test for the standalone particle canvas with a minimal DOM/canvas shim. */
'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

class FakeElement {
    constructor(tagName, box) {
        this.tagName = tagName;
        this.box = box || { left: 0, top: 0, width: 640, height: 260 };
        this.children = [];
        this.parentNode = null;
        this.attributes = {};
        this.listeners = {};
        this.style = {};
        this.className = '';
        this.width = 0;
        this.height = 0;
        this.context = {
            fills: 0,
            strokes: 0,
            clears: 0,
            setTransform() {},
            clearRect: () => { this.context.clears++; },
            beginPath() {},
            moveTo() {},
            lineTo() {},
            stroke: () => { this.context.strokes++; },
            arc() {},
            fill: () => { this.context.fills++; }
        };
    }
    setAttribute(name, value) { this.attributes[name] = String(value); }
    getAttribute(name) { return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null; }
    hasAttribute(name) { return Object.prototype.hasOwnProperty.call(this.attributes, name); }
    getContext(kind) { return kind === '2d' ? this.context : null; }
    getBoundingClientRect() { return this.box; }
    addEventListener(name, callback) { (this.listeners[name] || (this.listeners[name] = [])).push(callback); }
    removeEventListener(name, callback) {
        this.listeners[name] = (this.listeners[name] || []).filter((item) => item !== callback);
    }
    dispatch(name, event) { (this.listeners[name] || []).slice().forEach((callback) => callback(event || {})); }
    appendChild(child) {
        if (child.parentNode) child.parentNode.removeChild(child);
        this.children.push(child);
        child.parentNode = this;
        return child;
    }
    insertBefore(child, before) {
        if (child.parentNode) child.parentNode.removeChild(child);
        const index = before ? this.children.indexOf(before) : -1;
        if (index < 0) this.children.unshift(child);
        else this.children.splice(index, 0, child);
        child.parentNode = this;
        return child;
    }
    removeChild(child) {
        const index = this.children.indexOf(child);
        if (index >= 0) this.children.splice(index, 1);
        child.parentNode = null;
        return child;
    }
}

function makeEnvironment(reducedMotion, scriptConfig) {
    const rafs = new Map();
    const cancelled = [];
    let nextRaf = 1;
    const document = {
        hidden: false,
        listeners: {},
        body: new FakeElement('body', { left: 0, top: 0, width: 800, height: 600 }),
        currentScript: new FakeElement('script'),
        createElement: (tag) => new FakeElement(tag),
        addEventListener(name, callback) { (this.listeners[name] || (this.listeners[name] = [])).push(callback); },
        removeEventListener(name, callback) {
            this.listeners[name] = (this.listeners[name] || []).filter((item) => item !== callback);
        },
        dispatch(name, event) { (this.listeners[name] || []).slice().forEach((callback) => callback(event || {})); }
    };
    if (scriptConfig !== undefined) {
        document.currentScript.setAttribute('data-config', JSON.stringify(scriptConfig));
    }
    const media = {
        matches: !!reducedMotion,
        addEventListener() {},
        removeEventListener() {}
    };
    const window = {
        innerWidth: 800,
        innerHeight: 600,
        devicePixelRatio: 2,
        __particleNetworkBackground: null,
        listeners: {},
        matchMedia: () => media,
        requestAnimationFrame(callback) {
            const id = nextRaf++;
            rafs.set(id, callback);
            return id;
        },
        cancelAnimationFrame(id) { cancelled.push(id); rafs.delete(id); },
        addEventListener(name, callback) { (this.listeners[name] || (this.listeners[name] = [])).push(callback); },
        removeEventListener(name, callback) {
            this.listeners[name] = (this.listeners[name] || []).filter((item) => item !== callback);
        },
        dispatch(name, event) { (this.listeners[name] || []).slice().forEach((callback) => callback(event || {})); }
    };
    const source = fs.readFileSync(path.join(__dirname, '..', 'assets/particle-network.js'), 'utf8');
    vm.runInNewContext(source, { document, window, Math, Number, Object, Array, String, parseInt }, { filename: 'particle-network.js' });
    return { document, window, media, rafs, cancelled };
}

(function testPreviewLifecycleAndLiveConfig() {
    const env = makeEnvironment(false);
    const preview = new FakeElement('div', { left: 20, top: 30, width: 640, height: 260 });
    const api = env.window.ParticleNetwork.mount(preview, {
        density: 1800,
        maxParticles: 80,
        color: '#336699',
        showDots: true,
        showLinks: true,
        autoMove: true
    });
    const canvas = preview.children[0];
    assert(canvas && canvas.className.indexOf('particle-network-preview-canvas') !== -1, 'preview canvas should mount');
    assert.strictEqual(canvas.width, 1280, 'device-pixel ratio should be applied');
    assert(canvas.context.clears > 0 && canvas.context.fills > 0, 'initial render should draw particle dots');
    assert.strictEqual(env.rafs.size, 1, 'motion should schedule an animation frame');

    api.update({ showDots: false, showLinks: false, autoMove: false, color: '#112233' });
    assert.strictEqual(env.rafs.size, 0, 'static settings should stop the animation loop');
    const clearsBeforePointer = canvas.context.clears;
    preview.dispatch('pointermove', { clientX: 150, clientY: 110 });
    assert(canvas.context.clears > clearsBeforePointer, 'pointer changes should repaint even when auto motion is off');

    api.update({ showDots: true, density: 9000, maxParticles: 40 });
    assert(canvas.context.fills > 0, 'new particle settings should repaint the preview');
    api.destroy();
    assert.strictEqual(preview.children.length, 0, 'destroy should remove the preview canvas');
    assert(env.cancelled.length > 0, 'destroy should cancel pending animation frames');
})();

(function testReducedMotionAndBackgroundBoot() {
    const reduced = makeEnvironment(true);
    const preview = new FakeElement('div');
    const api = reduced.window.ParticleNetwork.mount(preview, { autoMove: true });
    assert.strictEqual(reduced.rafs.size, 0, 'reduced-motion preference should avoid continuous animation');
    api.destroy();

    const background = makeEnvironment(false, { density: 4500, maxParticles: 80, autoMove: true });
    assert(background.window.__particleNetworkBackground, 'data-config should create the fixed background instance');
    assert.strictEqual(background.document.body.children.length, 1, 'background canvas should be inserted into the body');
    background.window.__particleNetworkBackground.destroy();
    assert.strictEqual(background.document.body.children.length, 0, 'destroying background should remove its canvas');
})();

const normalized = makeEnvironment(false).window.ParticleNetwork.normalize({
    density: 1,
    maxParticles: 9999,
    color: 'red',
    opacity: 5,
    showDots: 'false'
});
assert.strictEqual(normalized.density, 1800);
assert.strictEqual(normalized.maxParticles, 450);
assert.strictEqual(normalized.color, '#172b36');
assert.strictEqual(normalized.opacity, 1);
assert.strictEqual(normalized.showDots, false);
console.log('PASS ParticleNetwork runtime: preview mount/update/destroy, pointer redraw, reduced motion, background mode, config bounds');
