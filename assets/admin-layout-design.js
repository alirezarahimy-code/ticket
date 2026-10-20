(() => {
  'use strict';

  if (!document.body || document.body.dataset.layoutDesignAdmin !== '1') return;
  let launcher = document.querySelector('[data-layout-design-launch]');
  if (!launcher) {
    launcher = document.createElement('button');
    launcher.type = 'button';
    launcher.className = 'ald-floating-launch';
    launcher.dataset.layoutDesignLaunch = '1';
    launcher.dataset.layoutPageTitle = 'سامانه مدیریت غذا';
    launcher.setAttribute('aria-pressed', 'false');
    launcher.textContent = '🎨 طراحی همین صفحه';
    document.body.appendChild(launcher);
  }

  const page = document.body.dataset.layoutPage || 'dashboard';
  const api = 'index.php?layout_design_api=1&layout_page=' + encodeURIComponent(page);
  const panelPositionKey = 'admin-layout-design-panel-position-v1:' + page;
  // Clear the previous build's launcher placement; the movable object is the editor panel itself.
  try { localStorage.removeItem('admin-layout-design-launcher-position-v1'); } catch (ignore) {}
  launcher.classList.remove('ald-launcher-movable', 'is-dragging');
  launcher.removeAttribute('title');
  ['position', 'left', 'top', 'right', 'bottom', 'margin', 'z-index'].forEach((property) => launcher.style.removeProperty(property));
  const propertyTitles = {
    width: 'عرض', 'min-width': 'حداقل عرض', 'max-width': 'حداکثر عرض', order: 'ترتیب نمایش', 'offset-x': 'جابجایی افقی (px)', 'offset-y': 'جابجایی عمودی (px)',
    height: 'ارتفاع', 'min-height': 'حداقل ارتفاع', 'max-height': 'حداکثر ارتفاع', 'margin-top': 'فاصله از بالا',
    'margin-bottom': 'فاصله از پایین', padding: 'فاصلهٔ داخلی', gap: 'فاصله بین اجزا',
    'row-gap': 'فاصلهٔ ردیف‌ها', 'column-gap': 'فاصلهٔ ستون‌ها', 'font-size': 'اندازهٔ نوشته',
    'line-height': 'ارتفاع خط', 'text-align': 'تراز نوشته', 'white-space': 'شکستن خط',
    display: 'نوع چیدمان', 'grid-template-columns': 'عرض ستون‌ها', 'flex-wrap': 'رفتن به خط بعد',
    'justify-content': 'تراز افقی اجزا', 'align-items': 'تراز عمودی اجزا',
  };
  const selectOptions = {
    'text-align': [['', 'پیش‌فرض'], ['right', 'راست'], ['center', 'وسط'], ['left', 'چپ']],
    'white-space': [['', 'پیش‌فرض'], ['normal', 'شکستن در صورت نیاز'], ['nowrap', 'بدون شکستن خط'], ['pre-wrap', 'حفظ فاصله و شکستن خط']],
    display: [['', 'پیش‌فرض'], ['block', 'بلوک'], ['inline-block', 'بلوک درون‌خطی'], ['inline-flex', 'فلکس درون‌خطی'], ['flex', 'فلکس'], ['grid', 'شبکه']],
    'flex-wrap': [['', 'پیش‌فرض'], ['nowrap', 'یک خط'], ['wrap', 'اجازهٔ خط بعد']],
    'justify-content': [['', 'پیش‌فرض'], ['start', 'ابتدا'], ['center', 'وسط'], ['end', 'انتها'], ['space-between', 'فاصلهٔ مساوی']],
    'align-items': [['', 'پیش‌فرض'], ['start', 'ابتدا'], ['center', 'وسط'], ['end', 'انتها'], ['stretch', 'کشیده']],
  };
  const lengthProps = new Set(['width', 'min-width', 'max-width', 'height', 'min-height', 'max-height', 'margin-top', 'margin-bottom', 'padding', 'gap', 'row-gap', 'column-gap']);
  const textProps = new Set(['font-size', 'line-height', 'grid-template-columns']);
  let rules = [];
  let selected = null;
  let selectedPath = null;
  let active = false;
  let dirty = false;
  let loading = false;
  let panel;
  let hovered = null;
  let dragHandle = null;
  let resizeHandles = [];
  let offsetDrag = null;
  let resizeDrag = null;
  let panelDrag = null;
  let panelPositioned = false;

  const esc = (value) => String(value == null ? '' : value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
  const pathKey = (path) => JSON.stringify(path);

  function setPanelPosition(left, top, persist) {
    if (!panel) return;
    const rect = panel.getBoundingClientRect();
    const width = Math.min(rect.width || 380, Math.max(240, window.innerWidth - 16));
    const height = Math.min(rect.height || 500, Math.max(180, window.innerHeight - 16));
    const maxLeft = Math.max(8, window.innerWidth - width - 8);
    const maxTop = Math.max(8, window.innerHeight - height - 8);
    left = Math.max(8, Math.min(maxLeft, Number(left) || 8));
    top = Math.max(8, Math.min(maxTop, Number(top) || 8));
    panel.style.position = 'fixed';
    panel.style.inset = 'auto';
    panel.style.left = Math.round(left) + 'px';
    panel.style.top = Math.round(top) + 'px';
    panel.style.right = 'auto';
    panel.style.bottom = 'auto';
    panel.style.width = width + 'px';
    panel.style.height = height + 'px';
    panel.style.maxHeight = 'calc(100vh - 16px)';
    panel.style.zIndex = '10050';
    panelPositioned = true;
    if (persist) {
      try { localStorage.setItem(panelPositionKey, JSON.stringify({ left: Math.round(left), top: Math.round(top) })); } catch (ignore) {}
    }
  }

  function restorePanelPosition() {
    if (!panel) return;
    try {
      const saved = JSON.parse(localStorage.getItem(panelPositionKey) || 'null');
      if (saved && Number.isFinite(Number(saved.left)) && Number.isFinite(Number(saved.top))) setPanelPosition(saved.left, saved.top, false);
    } catch (ignore) {}
  }

  function startPanelDrag(event) {
    if (!panel || event.button !== 0 || event.target.closest('button,input,select,textarea,a')) return;
    const rect = panel.getBoundingClientRect();
    panelDrag = {
      pointerId: event.pointerId == null ? 1 : event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      left: rect.left,
      top: rect.top,
      moved: false,
      grip: event.currentTarget,
    };
    event.currentTarget.focus({ preventScroll: true });
    if (event.currentTarget.setPointerCapture && event.pointerId != null) {
      try { event.currentTarget.setPointerCapture(event.pointerId); } catch (ignore) {}
    }
  }

  function onPanelPointerMove(event) {
    if (!panelDrag || (event.pointerId != null && event.pointerId !== panelDrag.pointerId)) return;
    const dx = event.clientX - panelDrag.startX;
    const dy = event.clientY - panelDrag.startY;
    if (!panelDrag.moved && Math.hypot(dx, dy) < 4) return;
    panelDrag.moved = true;
    event.preventDefault();
    panel.classList.add('is-dragging');
    setPanelPosition(panelDrag.left + dx, panelDrag.top + dy, false);
  }

  function finishPanelDrag(event) {
    if (!panelDrag || (event && event.pointerId != null && event.pointerId !== panelDrag.pointerId)) return;
    const moved = panelDrag.moved;
    panelDrag = null;
    if (panel) panel.classList.remove('is-dragging');
    if (moved && panel) setPanelPosition(parseFloat(panel.style.left), parseFloat(panel.style.top), true);
  }

  function movePanelBy(dx, dy, step) {
    if (!panel) return;
    const rect = panel.getBoundingClientRect();
    const amount = step || 1;
    setPanelPosition(rect.left + dx * amount, rect.top + dy * amount, true);
  }

  function makePath(element) {
    const path = [];
    let node = element;
    while (node && node !== document.body && node.nodeType === 1) {
      const tag = node.tagName.toLowerCase();
      if (['script', 'style', 'svg', 'path', 'iframe'].includes(tag)) return null;
      const siblings = node.parentElement ? Array.from(node.parentElement.children).filter((item) => item.tagName === node.tagName) : [];
      const classes = Array.from(node.classList || []).filter((name) => /^[A-Za-z_][A-Za-z0-9_-]{0,59}$/.test(name) && !['ald-hover', 'ald-selected', 'ald-drag-source', 'ald-drop-target', 'ald-dragging'].includes(name)).slice(0, 4);
      const id = /^[A-Za-z][A-Za-z0-9_-]{0,79}$/.test(node.id || '') ? node.id : '';
      path.unshift({ tag, id, classes, index: Math.max(1, siblings.indexOf(node) + 1) });
      node = node.parentElement;
    }
    if (!path.length || path.length > 18) return null;
    // Anchor the stored path at its deepest stable id; app wrappers above an id
    // may differ between the editor preview and the next page render.
    for (let index = path.length - 1; index >= 0; index -= 1) {
      if (path[index].id) return path.slice(index);
    }
    return path;
  }

  function selectorFor(path) {
    if (!Array.isArray(path) || !path.length) return '';
    return 'body[data-layout-page="' + page + '"] ' + path.map((part) => {
      let selector = part.tag;
      if (part.id) selector += '#' + part.id;
      (part.classes || []).forEach((name) => { selector += '.' + name; });
      if (!part.id) selector += ':nth-of-type(' + Number(part.index) + ')';
      return selector;
    }).join(' > ');
  }

  function elementForPath(path) {
    try {
      const matched = document.querySelector(selectorFor(path));
      if (matched) return matched;
    } catch (ignore) {}
    for (let index = path.length - 1; index >= 0; index -= 1) {
      const segment = path[index];
      if (!segment.id) continue;
      const candidate = document.getElementById(segment.id);
      if (candidate && candidate.tagName.toLowerCase() === segment.tag) return candidate;
    }
    return null;
  }

  function applyAll() {
    rules.forEach((rule) => {
      const node = elementForPath(rule.path);
      if (!node) return;
      const styles = rule.styles || {};
      Object.entries(styles).forEach(([property, value]) => {
        if (propertyTitles[property] && !['offset-x', 'offset-y'].includes(property) && value !== '') node.style.setProperty(property, value, 'important');
      });
      const x = Number.parseInt(styles['offset-x'] || '0', 10) || 0;
      const y = Number.parseInt(styles['offset-y'] || '0', 10) || 0;
      if (x || y) node.style.setProperty('translate', x + 'px ' + y + 'px', 'important');
    });
  }

  function removeRule(path) {
    const key = pathKey(path);
    rules = rules.filter((rule) => pathKey(rule.path) !== key);
  }

  function selectedRule(create) {
    if (!selectedPath) return null;
    let rule = rules.find((item) => pathKey(item.path) === pathKey(selectedPath));
    if (!rule && create) {
      rule = { path: selectedPath, styles: {} };
      rules.push(rule);
    }
    return rule || null;
  }

  function previewProperty(property, value) {
    if (!selected) return;
    const rule = selectedRule(Boolean(value));
    if (!rule) {
      selected.style.removeProperty(property);
      return;
    }
    if (!value) delete rule.styles[property];
    else rule.styles[property] = value;
    selected.style.removeProperty(property);
    if (value) selected.style.setProperty(property, value, 'important');
    if (!Object.keys(rule.styles).length) removeRule(selectedPath);
    const control = panel && panel.querySelector('[data-ald-property="' + property + '"]');
    if (control && control !== document.activeElement) control.value = value;
    dirty = true;
    updateButtons();
  }

  function selectedDescription() {
    if (!selected) return 'برای شروع، روی بخش یا کنترلی که می‌خواهید چینش آن را اصلاح کنید کلیک کنید.';
    const bits = [selected.tagName.toLowerCase()];
    if (selected.id) bits.push('#' + selected.id);
    Array.from(selected.classList || []).slice(0, 2).forEach((name) => bits.push('.' + name));
    return 'جزء انتخاب‌شده: ' + bits.join('');
  }

  function propertyControl(property, styles) {
    const title = propertyTitles[property];
    const value = styles[property] || '';
    if (selectOptions[property]) {
      return '<label class="ald-control"><span>' + esc(title) + '</span><select data-ald-property="' + esc(property) + '">' + selectOptions[property].map(([optionValue, label]) => '<option value="' + esc(optionValue) + '"' + (value === optionValue ? ' selected' : '') + '>' + esc(label) + '</option>').join('') + '</select></label>';
    }
    const placeholder = property === 'grid-template-columns' ? 'مثال: 42px 1fr 1fr' : (property === 'font-size' ? 'مثال: 14' : (property === 'line-height' ? 'مثال: 1.5' : 'مثال: 120px یا 40%'));
    return '<label class="ald-control"><span>' + esc(title) + '</span><input type="text" inputmode="text" data-ald-property="' + esc(property) + '" value="' + esc(value) + '" placeholder="' + esc(placeholder) + '"></label>';
  }

  function selectedOffset() {
    const rule = selectedRule(false);
    return {
      x: rule ? (Number.parseInt(rule.styles['offset-x'] || '0', 10) || 0) : 0,
      y: rule ? (Number.parseInt(rule.styles['offset-y'] || '0', 10) || 0) : 0,
    };
  }

  function renderPositionControls() {
    const host = panel && panel.querySelector('[data-ald-position]');
    if (!host) return;
    if (!selected) {
      host.innerHTML = '<div class="ald-position-empty">یک جزء را انتخاب کنید تا جابه‌جایی پیکسلی آن فعال شود.</div>';
      return;
    }
    const offset = selectedOffset();
    const rect = selected.getBoundingClientRect();
    host.innerHTML = '<div class="ald-position-heading"><strong>جابه‌جایی دقیق (پیکسل)</strong><span class="ald-size-readout" data-ald-size-readout>' + Math.round(rect.width) + ' × ' + Math.round(rect.height) + ' px</span><button type="button" data-ald-offset-reset aria-label="صفرکردن جابه‌جایی">بازنشانی</button></div>' +
      '<div class="ald-axis-row"><span>X افقی</span><button type="button" data-ald-nudge-axis="x" data-ald-nudge-delta="-1" aria-label="یک پیکسل به چپ">−</button><input type="number" min="-500" max="500" step="1" data-ald-offset-axis="x" value="' + offset.x + '" aria-label="جابجایی افقی به پیکسل"><button type="button" data-ald-nudge-axis="x" data-ald-nudge-delta="1" aria-label="یک پیکسل به راست">+</button></div>' +
      '<div class="ald-axis-row"><span>Y عمودی</span><button type="button" data-ald-nudge-axis="y" data-ald-nudge-delta="-1" aria-label="یک پیکسل به بالا">−</button><input type="number" min="-500" max="500" step="1" data-ald-offset-axis="y" value="' + offset.y + '" aria-label="جابجایی عمودی به پیکسل"><button type="button" data-ald-nudge-axis="y" data-ald-nudge-delta="1" aria-label="یک پیکسل به پایین">+</button></div>' +
      '<small>دستگیرهٔ ⠿ را برای حرکت و مربع‌های دور جزء را برای تغییر اندازه بکشید. جهت‌ها حرکت ۱px و Shift+جهت حرکت ۱۰px است؛ Ctrl+جهت اندازه را ۱px و Ctrl+Shift+جهت ۱۰px تغییر می‌دهد.</small>';
  }

  function setSelectedOffset(x, y) {
    if (!selected || !selectedPath) return;
    const previous = selectedOffset();
    x = Math.max(-500, Math.min(500, Math.round(Number(x) || 0)));
    y = Math.max(-500, Math.min(500, Math.round(Number(y) || 0)));
    if (previous.x === x && previous.y === y) return;
    let rule = selectedRule(false);
    if (!rule && (x !== 0 || y !== 0)) rule = selectedRule(true);
    if (rule) {
      rule.styles = rule.styles || {};
      if (x === 0) delete rule.styles['offset-x']; else rule.styles['offset-x'] = String(x);
      if (y === 0) delete rule.styles['offset-y']; else rule.styles['offset-y'] = String(y);
      if (!Object.keys(rule.styles).length) removeRule(selectedPath);
    }
    selected.style.removeProperty('translate');
    if (x !== 0 || y !== 0) selected.style.setProperty('translate', x + 'px ' + y + 'px', 'important');
    dirty = true;
    renderPositionControls();
    updateDragHandle();
    updateButtons();
  }

  function nudgeSelected(dx, dy, step) {
    if (!selected) return false;
    const offset = selectedOffset();
    setSelectedOffset(offset.x + dx * (step || 1), offset.y + dy * (step || 1));
    return true;
  }

  function renderControls() {
    if (!panel) return;
    renderPositionControls();
    renderOrderControls();
    const host = panel.querySelector('[data-ald-controls]');
    const desc = panel.querySelector('[data-ald-selection]');
    if (desc) desc.textContent = selectedDescription();
    if (!host) return;
    if (!selected) {
      host.innerHTML = '<p class="ald-empty">برای شروع، روی فیلد یا بخش موردنظر کلیک کنید.</p>';
      return;
    }
    const rule = selectedRule(false);
    const styles = rule ? rule.styles : {};
    const common = ['width', 'padding', 'gap', 'font-size', 'text-align'];
    const advanced = Object.keys(propertyTitles).filter((property) => !['order', 'offset-x', 'offset-y'].includes(property) && !common.includes(property));
    host.innerHTML = '<div class="ald-basic-controls">' + common.map((property) => propertyControl(property, styles)).join('') + '</div>' +
      '<details class="ald-advanced"><summary>تنظیم‌های پیشرفتهٔ ظاهری</summary><div class="ald-advanced-controls">' + advanced.map((property) => propertyControl(property, styles)).join('') +
      '</div><p class="ald-help">برای حذف یک تغییر، مقدار آن را خالی کنید. تنظیم‌ها فقط ظاهر را تغییر می‌دهند.</p></details>';
  }

  function updateButtons() {
    if (!panel) return;
    const save = panel.querySelector('[data-ald-save]');
    if (save) { save.disabled = loading || !dirty; save.textContent = loading ? 'در حال ذخیره…' : (dirty ? 'ذخیرهٔ چیدمان' : 'تغییری برای ذخیره نیست'); }
    const reset = panel.querySelector('[data-ald-reset]');
    if (reset) reset.disabled = loading || !rules.length;
  }

  function ensureDragHandle() {
    if (dragHandle) return dragHandle;
    dragHandle = document.createElement('button');
    dragHandle.type = 'button';
    dragHandle.className = 'ald-drag-handle';
    dragHandle.draggable = false;
    dragHandle.hidden = true;
    dragHandle.title = 'این دستگیره را بکشید تا جزء را چند پیکسل جابه‌جا کنید';
    dragHandle.setAttribute('aria-label', 'دستگیرهٔ جابه‌جایی پیکسلی جزء انتخاب‌شده');
    dragHandle.textContent = '⠿ حرکت';
    dragHandle.addEventListener('pointerdown', startOffsetDrag);
    document.body.appendChild(dragHandle);
    return dragHandle;
  }

  function ensureResizeHandles() {
    if (resizeHandles.length) return resizeHandles;
    ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'].forEach((direction) => {
      const handle = document.createElement('button');
      handle.type = 'button';
      handle.className = 'ald-resize-handle ald-resize-' + direction;
      handle.dataset.aldResize = direction;
      handle.hidden = true;
      handle.title = 'برای تغییر اندازه بکشید';
      const directionName = { nw: 'گوشهٔ بالا-چپ', n: 'لبهٔ بالا', ne: 'گوشهٔ بالا-راست', e: 'لبهٔ راست', se: 'گوشهٔ پایین-راست', s: 'لبهٔ پایین', sw: 'گوشهٔ پایین-چپ', w: 'لبهٔ چپ' }[direction];
      handle.setAttribute('aria-label', 'تغییر اندازه از ' + directionName);
      handle.addEventListener('pointerdown', startResize);
      document.body.appendChild(handle);
      resizeHandles.push(handle);
    });
    return resizeHandles;
  }

  function updateDragHandle() {
    if (!dragHandle || !active || !selected) {
      if (dragHandle) dragHandle.hidden = true;
      resizeHandles.forEach((handle) => { handle.hidden = true; });
      return;
    }
    ensureResizeHandles();
    const rect = selected.getBoundingClientRect();
    const sizeReadout = panel && panel.querySelector('[data-ald-size-readout]');
    if (sizeReadout) sizeReadout.textContent = Math.round(rect.width) + ' × ' + Math.round(rect.height) + ' px';
    dragHandle.hidden = false;
    dragHandle.style.left = Math.max(8, Math.min(window.innerWidth - 132, rect.left + 4)) + 'px';
    dragHandle.style.top = Math.max(8, rect.top - 36) + 'px';
    const centers = {
      nw: [rect.left, rect.top], n: [rect.left + rect.width / 2, rect.top], ne: [rect.right, rect.top],
      e: [rect.right, rect.top + rect.height / 2], se: [rect.right, rect.bottom], s: [rect.left + rect.width / 2, rect.bottom],
      sw: [rect.left, rect.bottom], w: [rect.left, rect.top + rect.height / 2],
    };
    resizeHandles.forEach((handle) => {
      const [x, y] = centers[handle.dataset.aldResize];
      handle.hidden = false;
      handle.style.left = Math.max(0, Math.min(window.innerWidth - 11, Math.round(x - 5))) + 'px';
      handle.style.top = Math.max(0, Math.min(window.innerHeight - 11, Math.round(y - 5))) + 'px';
    });
  }

  function startOffsetDrag(event) {
    if (!selected || event.button !== 0) return;
    event.preventDefault();
    const offset = selectedOffset();
    offsetDrag = {
      pointerId: event.pointerId == null ? 1 : event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      offsetX: offset.x,
      offsetY: offset.y,
    };
    dragHandle.classList.add('is-dragging');
    if (dragHandle.setPointerCapture && event.pointerId != null) {
      try { dragHandle.setPointerCapture(event.pointerId); } catch (ignore) {}
    }
  }

  function resizeExtra(element, axis, computed) {
    if (computed.boxSizing === 'border-box') return 0;
    const sides = axis === 'x' ? ['Left', 'Right'] : ['Top', 'Bottom'];
    return sides.reduce((total, side) => total + (Number.parseFloat(computed['padding' + side]) || 0) + (Number.parseFloat(computed['border' + side + 'Width']) || 0), 0);
  }

  function setDimensionFromOuterSize(property, outerSize) {
    if (!selected) return;
    let computed = window.getComputedStyle(selected);
    if (computed.display === 'inline') {
      previewProperty('display', 'inline-block');
      computed = window.getComputedStyle(selected);
    }
    const axis = property === 'width' ? 'x' : 'y';
    const dimension = property === 'width' ? 'width' : 'height';
    previewProperty('min-' + dimension, '0px');
    previewProperty('max-' + dimension, 'none');
    const cssSize = Math.max(0, Math.max(24, Math.min(9999, outerSize)) - resizeExtra(selected, axis, computed));
    previewProperty(property, Math.round(cssSize * 100) / 100 + 'px');
  }

  function startResize(event) {
    if (!selected || event.button !== 0) return;
    event.preventDefault();
    const rect = selected.getBoundingClientRect();
    const offset = selectedOffset();
    const direction = event.currentTarget.dataset.aldResize;
    resizeDrag = {
      pointerId: event.pointerId == null ? 1 : event.pointerId,
      direction,
      startX: event.clientX,
      startY: event.clientY,
      width: rect.width,
      height: rect.height,
      offsetX: offset.x,
      offsetY: offset.y,
      needsInlineBlock: window.getComputedStyle(selected).display === 'inline',
    };
    event.currentTarget.classList.add('is-resizing');
    if (event.currentTarget.setPointerCapture && event.pointerId != null) {
      try { event.currentTarget.setPointerCapture(event.pointerId); } catch (ignore) {}
    }
  }

  function resizeSelected(dx, dy, step) {
    if (!selected) return false;
    const rect = selected.getBoundingClientRect();
    if (dx) setDimensionFromOuterSize('width', rect.width + dx * (step || 1));
    if (dy) setDimensionFromOuterSize('height', rect.height + dy * (step || 1));
    updateDragHandle();
    updateButtons();
    return true;
  }

  function onOffsetPointerMove(event) {
    if (offsetDrag && (event.pointerId == null || event.pointerId === offsetDrag.pointerId)) {
      event.preventDefault();
      const dx = Math.round(event.clientX - offsetDrag.startX);
      const dy = Math.round(event.clientY - offsetDrag.startY);
      setSelectedOffset(offsetDrag.offsetX + dx, offsetDrag.offsetY + dy);
      return;
    }
    if (!resizeDrag || (event.pointerId != null && event.pointerId !== resizeDrag.pointerId)) return;
    event.preventDefault();
    if (resizeDrag.needsInlineBlock) {
      previewProperty('display', 'inline-block');
      resizeDrag.needsInlineBlock = false;
    }
    const dx = Math.round(event.clientX - resizeDrag.startX);
    const dy = Math.round(event.clientY - resizeDrag.startY);
    const direction = resizeDrag.direction;
    const offset = { x: resizeDrag.offsetX, y: resizeDrag.offsetY };
    if (direction.includes('e')) setDimensionFromOuterSize('width', resizeDrag.width + dx);
    if (direction.includes('w')) {
      setDimensionFromOuterSize('width', resizeDrag.width - dx);
      offset.x += resizeDrag.width - selected.getBoundingClientRect().width;
    }
    if (direction.includes('s')) setDimensionFromOuterSize('height', resizeDrag.height + dy);
    if (direction.includes('n')) {
      setDimensionFromOuterSize('height', resizeDrag.height - dy);
      offset.y += resizeDrag.height - selected.getBoundingClientRect().height;
    }
    if (offset.x !== resizeDrag.offsetX || offset.y !== resizeDrag.offsetY) setSelectedOffset(offset.x, offset.y);
    updateDragHandle();
    updateButtons();
  }

  function finishOffsetDrag(event) {
    const pointerId = event && event.pointerId;
    if (offsetDrag && (pointerId == null || pointerId === offsetDrag.pointerId)) offsetDrag = null;
    if (resizeDrag && (pointerId == null || pointerId === resizeDrag.pointerId)) resizeDrag = null;
    if (dragHandle) dragHandle.classList.remove('is-dragging');
    resizeHandles.forEach((handle) => handle.classList.remove('is-resizing'));
  }

  function createPanel() {
    if (panel) return panel;
    panel = document.createElement('aside');
    panel.className = 'ald-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'طراحی چیدمان صفحه');
    panel.innerHTML = '<div class="ald-head" data-ald-panel-grip tabindex="0" aria-label="سربرگ قابل جابه‌جایی پنل طراحی؛ با کلیدهای جهت‌دار حرکت دهید"><div><strong>طراحی همین صفحه <span class="ald-panel-grip-mark" aria-hidden="true">⠿</span></strong><small>' + esc(launcher.dataset.layoutPageTitle || 'صفحهٔ فعلی') + ' · سربرگ را بکشید یا با جهت‌ها حرکت دهید</small></div><button type="button" class="ald-close" data-ald-close aria-label="بستن">×</button></div>' +
      '<div class="ald-instructions">یک جزء را انتخاب کنید؛ با دستگیره یا کلیدها چند پیکسل جابه‌جایش کنید. پنل را از سربرگ بکشید یا سربرگ را فوکوس کنید و با جهت‌ها جابه‌جایش کنید. ترتیب اجزا از فهرست پایین جداست؛ در حالت طراحی فرم ارسال نمی‌شود.</div>' +
      '<div class="ald-selection" data-ald-selection></div><section class="ald-position" data-ald-position aria-label="جابجایی پیکسلی جزء انتخاب‌شده"></section><section class="ald-order" data-ald-order aria-label="ترتیب اجزای این بخش"></section><div class="ald-controls" data-ald-controls></div>' +
      '<div class="ald-actions"><button type="button" class="ald-save" data-ald-save>ذخیرهٔ چیدمان</button><button type="button" class="ald-reset" data-ald-reset>بازگردانی پیش‌فرض</button></div>' +
      '<small class="ald-footnote">تنظیم‌ها فقط ظاهر همین صفحه را تغییر می‌دهند و برای همهٔ کاربران اعمال می‌شوند.</small>';
    document.body.appendChild(panel);
    ensureDragHandle();
    panelPositioned = false;
    const panelGrip = panel.querySelector('[data-ald-panel-grip]');
    panelGrip && panelGrip.addEventListener('pointerdown', startPanelDrag);
    panel.addEventListener('input', onControlChange);
    panel.addEventListener('change', onControlChange);
    panel.addEventListener('click', (event) => {
      if (event.target.closest('[data-ald-close]')) closeDesign();
      if (event.target.closest('[data-ald-save]')) saveDesign();
      if (event.target.closest('[data-ald-reset]')) resetDesign();
      const nudgeButton = event.target.closest('[data-ald-nudge-axis]');
      if (nudgeButton) {
        const axis = nudgeButton.dataset.aldNudgeAxis;
        const delta = Number(nudgeButton.dataset.aldNudgeDelta) || 0;
        nudgeSelected(axis === 'x' ? delta : 0, axis === 'y' ? delta : 0, 1);
      }
      if (event.target.closest('[data-ald-offset-reset]')) setSelectedOffset(0, 0);
      const moveButton = event.target.closest('[data-ald-move]');
      if (moveButton) moveOrderItem(Number(moveButton.dataset.aldIndex), Number(moveButton.dataset.aldIndex) + Number(moveButton.dataset.aldMoveStep));
      const orderRow = event.target.closest('[data-ald-order-index]');
      if (orderRow && !event.target.closest('button')) {
        const items = orderableSiblings();
        const item = items && items[Number(orderRow.dataset.aldOrderIndex)];
        if (item) selectDesignElement(item);
      }
    });
    panel.addEventListener('keydown', (event) => {
      if (event.target.closest('[data-ald-panel-grip]') && !event.target.closest('button') && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
        event.preventDefault();
        movePanelBy(event.key === 'ArrowLeft' ? -1 : (event.key === 'ArrowRight' ? 1 : 0), event.key === 'ArrowUp' ? -1 : (event.key === 'ArrowDown' ? 1 : 0), event.shiftKey ? 10 : 1);
        return;
      }
      if (!event.target.closest('[data-ald-order-index]') || !['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) return;
      event.preventDefault();
      const step = event.shiftKey ? 10 : 1;
      if (event.ctrlKey && !event.metaKey) {
        resizeSelected(event.key === 'ArrowRight' ? 1 : (event.key === 'ArrowLeft' ? -1 : 0), event.key === 'ArrowDown' ? 1 : (event.key === 'ArrowUp' ? -1 : 0), step);
        return;
      }
      if (event.altKey && ['ArrowUp', 'ArrowDown'].includes(event.key)) {
        moveSelected(event.key === 'ArrowUp' ? -1 : 1);
        return;
      }
      nudgeSelected(event.key === 'ArrowLeft' ? -1 : (event.key === 'ArrowRight' ? 1 : 0), event.key === 'ArrowUp' ? -1 : (event.key === 'ArrowDown' ? 1 : 0), step);
    });
    panel.addEventListener('dragstart', (event) => {
      const row = event.target.closest('[data-ald-order-index]');
      if (!row || !event.dataTransfer) return;
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', row.dataset.aldOrderIndex);
      row.classList.add('ald-dragging');
    });
    panel.addEventListener('dragover', (event) => {
      if (!event.target.closest('[data-ald-order-index]')) return;
      event.preventDefault();
      if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
    });
    panel.addEventListener('drop', (event) => {
      const row = event.target.closest('[data-ald-order-index]');
      if (!row || !event.dataTransfer) return;
      event.preventDefault();
      moveOrderItem(Number(event.dataTransfer.getData('text/plain')), Number(row.dataset.aldOrderIndex));
    });
    panel.addEventListener('dragend', () => panel.querySelectorAll('.ald-dragging').forEach((row) => row.classList.remove('ald-dragging')));
    renderControls();
    restorePanelPosition();
    return panel;
  }

  function chooseTarget(target) {
    if (!(target instanceof Element)) return null;
    const content = target.closest('.content-wrap,.page,#screen');
    if (!content || target.closest('.topbar,.footer,.notification-toasts,.ald-panel,.ald-floating-launch')) return null;
    const control = target.closest('input,select,textarea,button');
    const fieldLabel = target.closest('label');
    const candidate = control && content.contains(control)
      ? control
      : (fieldLabel && content.contains(fieldLabel)
        ? fieldLabel
        : (target.closest('form,fieldset,th,td,tr,table,.card,.form-card,.form-grid,.form-actions,.actions,.table-wrap,.fo-day,.fo-calendar-grid,.grid,.section-title,.page-heading') || target));
    return candidate instanceof Element ? candidate : null;
  }

  function layoutContainerForSelection() {
    let node = selected ? selected.parentElement : null;
    while (node && node !== document.body) {
      const display = window.getComputedStyle(node).display;
      if (['grid', 'inline-grid', 'flex', 'inline-flex'].includes(display)) return node;
      if (display !== 'contents') return null;
      node = node.parentElement;
    }
    return null;
  }

  function collectLayoutItems(container, result) {
    Array.from(container.children).forEach((child) => {
      if (!(child instanceof HTMLElement) || child.matches('script,style,[hidden],[data-ald-order]')) return;
      const style = window.getComputedStyle(child);
      if (style.display === 'none' || style.visibility === 'hidden') return;
      if (style.display === 'contents') {
        collectLayoutItems(child, result);
        return;
      }
      if (child.getClientRects().length) result.push(child);
    });
  }

  function orderableSiblings() {
    const container = layoutContainerForSelection();
    if (!container || !selected) return null;
    const items = [];
    collectLayoutItems(container, items);
    if (!items.includes(selected)) return null;
    return items.map((element, index) => ({
      element,
      index,
      order: Number.parseInt(window.getComputedStyle(element).order, 10) || 0,
    })).sort((a, b) => a.order - b.order || a.index - b.index).map((item) => item.element);
  }

  function elementCaption(element) {
    const label = String(element.innerText || element.textContent || '').replace(/\s+/g, ' ').trim();
    if (label) return label.slice(0, 54) + (label.length > 54 ? '…' : '');
    const named = element.getAttribute('aria-label') || element.getAttribute('name') || element.id;
    return named ? (element.tagName.toLowerCase() + ': ' + named).slice(0, 54) : '<' + element.tagName.toLowerCase() + '>';
  }

  function renderOrderControls() {
    const host = panel && panel.querySelector('[data-ald-order]');
    if (!host) return;
    if (!selected) {
      host.innerHTML = '<div class="ald-order-empty">پس از انتخاب جزء، ترتیب هم‌سطح آن در اینجا نمایش داده می‌شود.</div>';
      updateDragHandle();
      return;
    }
    const items = orderableSiblings();
    if (!items) {
      host.innerHTML = '<div class="ald-order-empty">این جزء در یک ردیف/شبکهٔ قابل‌مرتب‌سازی نیست؛ یک فیلد داخل فرم شبکه‌ای را انتخاب کنید.</div>';
      updateDragHandle();
      return;
    }
    if (items.length < 2) {
      host.innerHTML = '<div class="ald-order-empty">در این بخش جزء دیگری برای جابه‌جایی نیست.</div>';
      updateDragHandle();
      return;
    }
    host.innerHTML = '<div class="ald-order-heading"><strong>ترتیب فیلدهای همین بخش</strong><small>برای تغییر ترتیب فیلدها، از دکمه‌های این فهرست یا کشیدن ردیف‌ها استفاده کنید؛ حرکت پیکسلی از ابزار X/Y انجام می‌شود.</small></div>' +
      '<ol class="ald-order-list" aria-label="فهرست قابل مرتب‌سازی">' + items.map((element, index) =>
        '<li class="ald-order-item' + (element === selected ? ' is-selected' : '') + '" draggable="true" tabindex="0" data-ald-order-index="' + index + '" aria-label="' + esc(elementCaption(element)) + '">' +
        '<span class="ald-order-grip" aria-hidden="true">⠿</span><span class="ald-order-caption">' + esc(elementCaption(element)) + '</span>' +
        '<span class="ald-order-buttons"><button type="button" data-ald-move data-ald-index="' + index + '" data-ald-move-step="-1" aria-label="انتقال به بالا"' + (index === 0 ? ' disabled' : '') + '>↑</button>' +
        '<button type="button" data-ald-move data-ald-index="' + index + '" data-ald-move-step="1" aria-label="انتقال به پایین"' + (index === items.length - 1 ? ' disabled' : '') + '>↓</button></span></li>'
      ).join('') + '</ol><small class="ald-order-note">برای تغییر ترتیب از دکمه‌ها یا کشیدن ردیف استفاده کنید؛ Alt+↑/↓ هم ترتیب را عوض می‌کند. جهت‌های معمولی برای حرکت پیکسلی هستند.</small>';
    updateDragHandle();
  }

  function writeOrderRule(element, order) {
    const path = makePath(element);
    if (!path) return false;
    let rule = rules.find((item) => pathKey(item.path) === pathKey(path));
    if (!rule) {
      rule = { path, styles: {} };
      rules.push(rule);
    }
    rule.styles = rule.styles || {};
    rule.styles.order = String(order);
    element.style.setProperty('order', String(order), 'important');
    return true;
  }

  function applyOrder(items) {
    items.forEach((element, index) => writeOrderRule(element, index));
    dirty = true;
    renderOrderControls();
    updateDragHandle();
    updateButtons();
  }

  function moveOrderItem(from, to, focusAfter) {
    const items = orderableSiblings();
    if (!items || from < 0 || from >= items.length || to < 0 || to >= items.length || from === to) return false;
    const moved = items.splice(from, 1)[0];
    items.splice(to, 0, moved);
    applyOrder(items);
    if (focusAfter && panel) panel.querySelector('[data-ald-order-index="' + to + '"]')?.focus();
    return true;
  }

  function moveSelected(delta) {
    const items = orderableSiblings();
    if (!items || !selected) return false;
    const from = items.indexOf(selected);
    return moveOrderItem(from, from + delta, true);
  }

  function onPointerOver(event) {
    if (!active) return;
    const candidate = chooseTarget(event.target);
    if (hovered && hovered !== selected) hovered.classList.remove('ald-hover');
    hovered = candidate;
    if (hovered && hovered !== selected) hovered.classList.add('ald-hover');
  }

  function focusSelectedOrderRow() {
    if (!panel || !selected) return;
    const items = orderableSiblings();
    const index = items ? items.indexOf(selected) : -1;
    if (index >= 0) panel.querySelector('[data-ald-order-index=\"' + index + '\"]')?.focus({ preventScroll: true });
  }

  function selectDesignElement(candidate) {
    if (!(candidate instanceof Element)) return;
    if (selected) selected.classList.remove('ald-selected');
    selected = candidate;
    selectedPath = makePath(candidate);
    selected.classList.remove('ald-hover');
    selected.classList.add('ald-selected');
    renderControls();
    focusSelectedOrderRow();
  }

  function onDocumentClick(event) {
    if (!active || event.target.closest('.ald-panel') || event.target.closest('[data-layout-design-launch]')) return;
    const candidate = chooseTarget(event.target);
    if (!candidate) return;
    event.preventDefault();
    event.stopPropagation();
    if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
    selectDesignElement(candidate);
  }

  function onControlChange(event) {
    const offsetControl = event.target.closest('[data-ald-offset-axis]');
    if (offsetControl && selected) {
      if (event.type === 'input') return;
      const axis = offsetControl.dataset.aldOffsetAxis;
      const offset = selectedOffset();
      const value = Number.parseInt(offsetControl.value || '0', 10) || 0;
      setSelectedOffset(axis === 'x' ? value : offset.x, axis === 'y' ? value : offset.y);
      return;
    }
    const control = event.target.closest('[data-ald-property]');
    if (!control || !selected) return;
    const property = control.dataset.aldProperty;
    let value = String(control.value || '').trim();
    if (lengthProps.has(property) && value && /^\d+(?:\.\d+)?$/.test(value)) value += property === 'font-size' ? 'px' : 'px';
    if (property === 'font-size' && value.endsWith('px')) value = value.slice(0, -2);
    if (property === 'line-height' && value) value = value.replace(',', '.');
    if (textProps.has(property) && property !== 'grid-template-columns' && property !== 'line-height' && value && /^\d+(?:\.\d+)?$/.test(value)) value += 'px';
    previewProperty(property, value);
  }

  async function request(payload, method) {
    const response = await fetch(api, {
      method: method || 'GET',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: Object.assign({ Accept: 'application/json' }, method === 'POST' ? { 'Content-Type': 'application/json', 'X-CSRF-Token': window.ITSM_CSRF || '' } : {}),
      body: method === 'POST' ? JSON.stringify(payload) : undefined,
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.ok) throw new Error(result.error || 'ذخیره یا دریافت طراحی انجام نشد.');
    return result;
  }

  async function saveDesign() {
    if (loading || !dirty) return;
    loading = true;
    updateButtons();
    try {
      const saved = await request({ action: 'save', page, rules }, 'POST');
      const persisted = await request(null, 'GET');
      const persistedRules = Array.isArray(persisted.rules) ? persisted.rules : [];
      if (Number(saved.rules) !== persistedRules.length) {
        throw new Error('ذخیره‌سازی تأیید نشد؛ تعداد تنظیم‌های بازخوانی‌شده با تعداد ارسالی برابر نیست. دوباره تلاش کنید.');
      }
      const unmatched = persistedRules.filter((rule) => !elementForPath(rule.path));
      if (unmatched.length) {
        throw new Error('تنظیم‌ها به سرور رسیدند، اما ' + unmatched.length + ' جزء پس از بازخوانی پیدا نشد؛ صفحه را refresh نکنید و مورد را گزارش دهید.');
      }
      rules = persistedRules;
      applyAll();
      dirty = false;
      if (window.confirm('چیدمان ذخیره و دوباره از سرور تأیید شد. صفحه برای اعمال قطعی تنظیم‌ها بازخوانی شود؟')) window.location.reload();
    } catch (error) {
      window.alert(error.message || 'ذخیرهٔ چیدمان انجام نشد.');
    } finally {
      loading = false;
      updateButtons();
    }
  }

  async function resetDesign() {
    if (loading || !rules.length || !window.confirm('تنظیم‌های طراحی این صفحه به حالت پیش‌فرض برگردد؟')) return;
    loading = true;
    updateButtons();
    try {
      await request({ action: 'reset', page }, 'POST');
      window.location.reload();
    } catch (error) {
      window.alert(error.message || 'بازگردانی چیدمان انجام نشد.');
    } finally {
      loading = false;
      updateButtons();
    }
  }

  function closeDesign() {
    if (dirty && !window.confirm('تغییرهای ذخیره‌نشده دور ریخته شود؟')) return;
    active = false;
    document.body.classList.remove('ald-design-mode');
    if (selected) selected.classList.remove('ald-selected');
    if (hovered) hovered.classList.remove('ald-hover');
    finishOffsetDrag();
    if (dragHandle) { dragHandle.remove(); dragHandle = null; }
    resizeHandles.forEach((handle) => handle.remove());
    resizeHandles = [];
    selected = null;
    selectedPath = null;
    panel && panel.remove();
    panel = null;
    panelPositioned = false;
    if (dirty) window.location.reload();
  }

  async function openDesign() {
    if (active) return;
    active = true;
    dirty = false;
    launcher.setAttribute('aria-pressed', 'true');
    document.body.classList.add('ald-design-mode');
    createPanel();
    try {
      const data = await request(null, 'GET');
      document.getElementById('admin-page-layout-rules')?.remove();
      rules = Array.isArray(data.rules) ? data.rules : [];
      applyAll();
      renderControls();
      updateButtons();
    } catch (error) {
      window.alert(error.message || 'تنظیم‌های طراحی دریافت نشد.');
      closeDesign();
    }
  }

  launcher.addEventListener('click', openDesign);
  document.addEventListener('pointermove', onPanelPointerMove, true);
  document.addEventListener('pointerup', finishPanelDrag, true);
  document.addEventListener('pointercancel', finishPanelDrag, true);
  document.addEventListener('pointerover', onPointerOver, true);
  document.addEventListener('click', onDocumentClick, true);
  document.addEventListener('pointermove', onOffsetPointerMove, true);
  document.addEventListener('pointerup', finishOffsetDrag, true);
  document.addEventListener('pointercancel', finishOffsetDrag, true);
  window.addEventListener('scroll', updateDragHandle, true);
  window.addEventListener('resize', () => {
    updateDragHandle();
    if (panel && panelPositioned) setPanelPosition(parseFloat(panel.style.left), parseFloat(panel.style.top), true);
  });
  document.addEventListener('keydown', (event) => {
    if (!active) return;
    if (event.key === 'Escape') {
      closeDesign();
      return;
    }
    const insidePanel = event.target instanceof Element && event.target.closest('.ald-panel');
    if (!insidePanel && selected && !event.metaKey && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
      event.preventDefault();
      const step = event.shiftKey ? 10 : 1;
      if (event.ctrlKey) {
        resizeSelected(event.key === 'ArrowRight' ? 1 : (event.key === 'ArrowLeft' ? -1 : 0), event.key === 'ArrowDown' ? 1 : (event.key === 'ArrowUp' ? -1 : 0), step);
      } else if (event.altKey && ['ArrowUp', 'ArrowDown'].includes(event.key)) {
        moveSelected(event.key === 'ArrowUp' ? -1 : 1);
      } else {
        nudgeSelected(event.key === 'ArrowLeft' ? -1 : (event.key === 'ArrowRight' ? 1 : 0), event.key === 'ArrowUp' ? -1 : (event.key === 'ArrowDown' ? 1 : 0), step);
      }
    }
  });
})();
