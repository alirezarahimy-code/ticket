(function () {
  'use strict';
  var api = 'offline-map.php?api=1';
  var mapNode = document.getElementById('offline-map-map');
  var statusNode = document.getElementById('offline-map-statusline');
  var dialog = document.getElementById('offline-map-feature-dialog');
  var form = document.getElementById('offline-map-feature-form');
  var formFeedback = document.getElementById('offline-map-form-feedback');
  var state = { map: null, features: [], tool: 'pan', vertices: [], geometry: null, editId: 0, measure: false, userVisible: true };

  function status(message) { if (statusNode) statusNode.textContent = message; }
  function request(url, options) {
    return fetch(url, Object.assign({ credentials: 'same-origin', headers: { Accept: 'application/json' } }, options || {}))
      .then(function (response) {
        return response.json().then(function (body) {
          if (!response.ok) throw new Error(body.error || 'درخواست ناموفق بود.');
          return body;
        });
      });
  }
  function csrfToken() {
    var node = document.querySelector('meta[name="csrf-token"]');
    var hidden = document.querySelector('#offline-map-feature-form [name="csrf"]');
    return node ? node.content : (hidden ? hidden.value : (window.ITSM_CSRF || ''));
  }
  function post(data) {
    data.set('csrf', csrfToken());
    return request('offline-map.php', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }, body: data });
  }
  function setTool(tool, preserveVertices) {
    state.tool = tool;
    if (!preserveVertices) { state.vertices = []; state.editId = 0; }
    document.querySelectorAll('[data-tool]').forEach(function (button) {
      button.classList.toggle('is-active', button.getAttribute('data-tool') === tool);
    });
    var drawing = tool === 'Point' || tool === 'LineString' || tool === 'Polygon' || tool === 'measure' || tool === 'measure-area';
    ['offline-map-undo', 'offline-map-finish', 'offline-map-cancel'].forEach(function (id) {
      var button = document.getElementById(id);
      if (button) button.disabled = !drawing || state.vertices.length === 0;
    });
    if (state.map) state.map.getCanvas().style.cursor = drawing ? 'crosshair' : '';
    if (tool === 'measure' || tool === 'measure-area') status('برای اندازه‌گیری، نقاط مسیر یا رأس‌های محدوده را روی نقشه انتخاب کنید.');
    else if (drawing) status('برای ترسیم، رأس‌ها را روی نقشه انتخاب کنید؛ سپس «پایان ترسیم» را بزنید.');
    else status('حالت انتخاب و جابه‌جایی فعال است.');
  }
  function updateDrawPreview() {
    if (!state.map || !state.map.getSource('offline-map-draft')) return;
    var geometry = null;
    if (state.tool === 'Point' && state.vertices.length) geometry = { type: 'Point', coordinates: state.vertices[0] };
    if (state.tool === 'LineString' && state.vertices.length) geometry = { type: 'LineString', coordinates: state.vertices };
    if ((state.tool === 'Polygon' || state.tool === 'measure-area') && state.vertices.length >= 2) {
      var ring = state.vertices.slice();
      if (ring.length >= 3) ring.push(ring[0]);
      geometry = { type: 'Polygon', coordinates: [ring] };
    }
    state.map.getSource('offline-map-draft').setData({ type: 'FeatureCollection', features: geometry ? [{ type: 'Feature', geometry: geometry, properties: {} }] : [] });
  }
  function loadAttachments(featureId) {
    var box = document.getElementById('offline-map-attachments');
    if (!box) return;
    box.replaceChildren();
    if (!featureId) return;
    request(api + '&kind=attachments&feature_id=' + encodeURIComponent(featureId)).then(function (body) {
      (body.attachments || []).forEach(function (item) {
        var link = document.createElement('a'); link.href = item.download_url; link.textContent = item.original_name + ' (' + Math.ceil(item.size_bytes / 1024) + ' KB)';
        link.target = '_blank'; link.rel = 'noopener'; box.appendChild(link);
      });
    }).catch(function (e) { box.textContent = e.message; });
  }
  function openFeatureDialog(geometry, existing) {
    if (!dialog || !form) return;
    state.geometry = geometry;
    state.editId = existing ? Number(existing.id) : 0;
    form.reset();
    form.elements.id.value = String(state.editId);
    form.elements.title.value = existing ? existing.title : '';
    form.elements.description.value = existing ? existing.description : '';
    form.elements.category.value = existing && existing.properties ? (existing.properties.category || 'general') : 'general';
    form.elements.address.value = existing && existing.properties ? (existing.properties.address || '') : '';
    form.elements.phone.value = existing && existing.properties ? (existing.properties.phone || '') : '';
    document.getElementById('offline-map-dialog-title').textContent = state.editId ? 'ویرایش عارضه' : 'ثبت عارضهٔ جدید';
    if (formFeedback) formFeedback.textContent = '';
    loadAttachments(state.editId);
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', 'open');
  }
  function addFeatureToMap(item) {
    var geometry = item.geometry;
    if (!geometry) return null;
    return { type: 'Feature', id: item.id, geometry: geometry, properties: {
      id: String(item.id), title: item.title || '', description: item.description || '',
      feature_type: item.feature_type || geometry.type, created_by: String(item.created_by || ''), category: (item.properties || {}).category || 'general',
      address: (item.properties || {}).address || '', phone: (item.properties || {}).phone || ''
    } };
  }
  function renderFeatureSource() {
    if (!state.map || !state.map.getSource('offline-map-user-features')) return;
    var features = state.features.map(addFeatureToMap).filter(Boolean);
    state.map.getSource('offline-map-user-features').setData({ type: 'FeatureCollection', features: features });
    renderFeatureList();
  }
  function renderFeatureList() {
    var node = document.getElementById('offline-map-feature-list');
    if (!node) return;
    node.replaceChildren();
    if (!state.features.length) {
      var empty = document.createElement('p'); empty.className = 'muted'; empty.textContent = 'عارضه‌ای برای نمایش وجود ندارد.'; node.appendChild(empty); return;
    }
    state.features.forEach(function (item) {
      var button = document.createElement('button'); button.type = 'button'; button.className = 'offline-map-feature-item';
      button.setAttribute('data-feature-focus', String(item.id));
      var symbol = document.createElement('span'); symbol.className = 'offline-map-feature-symbol type-' + String(item.feature_type || '').toLowerCase();
      var text = document.createElement('span'); var strong = document.createElement('strong'); strong.textContent = item.title;
      var small = document.createElement('small'); small.textContent = item.feature_type + ' · ' + item.updated_at;
      text.appendChild(strong); text.appendChild(small); button.appendChild(symbol); button.appendChild(text);
      node.appendChild(button);
    });
  }
  function loadFeatures(mineOnly) {
    return request(api + '&mine=' + (mineOnly ? '1' : '0')).then(function (body) {
      state.features = body.features || [];
      renderFeatureSource();
      return state.features;
    });
  }
  function distanceKm(points) {
    var total = 0, rad = Math.PI / 180;
    for (var i = 1; i < points.length; i++) {
      var a = points[i - 1], b = points[i];
      var dLat = (b[1] - a[1]) * rad, dLon = (b[0] - a[0]) * rad;
      var x = Math.sin(dLat / 2) ** 2 + Math.cos(a[1] * rad) * Math.cos(b[1] * rad) * Math.sin(dLon / 2) ** 2;
      total += 6371.0088 * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
    }
    return total;
  }
  function areaKm2(points) {
    if (points.length < 3) return 0;
    var sum = 0, rad = Math.PI / 180, meanLat = points.reduce(function (v, p) { return v + p[1]; }, 0) / points.length;
    var scaleX = 111.320 * Math.cos(meanLat * rad), scaleY = 110.574;
    for (var i = 0; i < points.length; i++) {
      var a = points[i], b = points[(i + 1) % points.length];
      sum += (a[0] * scaleX) * (b[1] * scaleY) - (b[0] * scaleX) * (a[1] * scaleY);
    }
    return Math.abs(sum / 2);
  }
  function finishDrawing() {
    if (state.tool === 'measure') {
      status('طول مسیر: ' + distanceKm(state.vertices).toFixed(3) + ' کیلومتر');
      if (state.map && state.map.getSource('offline-map-draft')) state.map.getSource('offline-map-draft').setData({ type: 'FeatureCollection', features: [] });
      setTool('pan'); return;
    }
    if (state.tool === 'measure-area') {
      status('مساحت تقریبی: ' + areaKm2(state.vertices).toFixed(3) + ' کیلومتر مربع');
      if (state.map && state.map.getSource('offline-map-draft')) state.map.getSource('offline-map-draft').setData({ type: 'FeatureCollection', features: [] });
      setTool('pan'); return;
    }
    if (state.tool === 'Point' && state.vertices.length >= 1) {
      openFeatureDialog({ type: 'Point', coordinates: state.vertices[state.vertices.length - 1] }, state.editId ? state.features.find(function (x) { return Number(x.id) === Number(state.editId); }) : null);
    } else if (state.tool === 'LineString' && state.vertices.length >= 2) {
      openFeatureDialog({ type: 'LineString', coordinates: state.vertices.slice() }, state.editId ? state.features.find(function (x) { return Number(x.id) === Number(state.editId); }) : null);
    } else if (state.tool === 'Polygon' && state.vertices.length >= 3) {
      var ring = state.vertices.slice(); ring.push(ring[0]);
      openFeatureDialog({ type: 'Polygon', coordinates: [ring] }, state.editId ? state.features.find(function (x) { return Number(x.id) === Number(state.editId); }) : null);
    } else {
      status('برای تکمیل این هندسه رأس کافی انتخاب نشده است.'); return;
    }
    if (state.map && state.map.getSource('offline-map-draft')) state.map.getSource('offline-map-draft').setData({ type: 'FeatureCollection', features: [] });
  }
  function popupForFeature(feature, lngLat) {
    var box = document.createElement('div'); box.className = 'offline-map-popup';
    var title = document.createElement('strong'); title.textContent = feature.properties.title || 'عارضه';
    var desc = document.createElement('p'); desc.textContent = feature.properties.description || '';
    box.appendChild(title); box.appendChild(desc);
    var isOwner = mapNode && Number(feature.properties.created_by) === Number(mapNode.getAttribute('data-user-id'));
    var isAdmin = mapNode && mapNode.getAttribute('data-can-admin') === '1';
    if (mapNode && mapNode.getAttribute('data-can-edit') === '1' && (isOwner || isAdmin)) {
      var edit = document.createElement('button'); edit.type = 'button'; edit.className = 'button secondary'; edit.textContent = 'ویرایش اطلاعات';
      edit.addEventListener('click', function () {
        var item = state.features.find(function (x) { return Number(x.id) === Number(feature.properties.id); });
        if (item) openFeatureDialog(item.geometry, item);
      });
      box.appendChild(edit);
      var geometryEdit = document.createElement('button'); geometryEdit.type = 'button'; geometryEdit.className = 'button secondary'; geometryEdit.textContent = 'ویرایش هندسه';
      geometryEdit.addEventListener('click', function () {
        var item = state.features.find(function (x) { return Number(x.id) === Number(feature.properties.id); });
        if (!item || !item.geometry) return;
        state.editId = Number(item.id);
        var type = item.geometry.type;
        if (type === 'Point') state.vertices = [];
        else if (type === 'LineString') state.vertices = item.geometry.coordinates.map(function (p) { return p.slice(); });
        else if (type === 'Polygon') state.vertices = item.geometry.coordinates[0].slice(0, -1).map(function (p) { return p.slice(); });
        else return;
        setTool(type, true); updateDrawPreview();
        status('هندسهٔ موجود بارگذاری شد؛ رأس‌ها را اضافه یا با «بازگشت رأس» حذف کنید و پایان ترسیم را بزنید.');
      });
      box.appendChild(geometryEdit);
      var del = document.createElement('button'); del.type = 'button'; del.className = 'button danger'; del.textContent = 'حذف';
      del.addEventListener('click', function () {
        if (!window.confirm('این عارضه حذف شود؟')) return;
        var data = new FormData(); data.set('action', 'delete'); data.set('id', feature.properties.id);
        post(data).then(function () { return loadFeatures(false); }).catch(function (e) { window.alert(e.message); });
      });
      box.appendChild(del);
    }
    new window.maplibregl.Popup({ maxWidth: '280px' }).setLngLat(lngLat).setDOMContent(box).addTo(state.map);
  }
  function loadLocalOverlay(file, sourceId, layers) {
    if (!state.map) return Promise.resolve(false);
    return fetch('modules/offline-map/data/overlays/' + file, { credentials: 'same-origin', headers: { Accept: 'application/geo+json, application/json' } })
      .then(function (response) { if (!response.ok) throw new Error('overlay unavailable'); return response.json(); })
      .then(function (geojson) {
        if (!geojson || geojson.type !== 'FeatureCollection' || !Array.isArray(geojson.features)) throw new Error('GeoJSON overlay format is invalid');
        state.map.addSource(sourceId, { type: 'geojson', data: geojson });
        layers.forEach(function (layer) { state.map.addLayer(layer); });
        return true;
      }).catch(function () { return false; });
  }
  function initMap() {
    if (!mapNode || mapNode.getAttribute('data-map-ready') !== '1') return;
    if (!window.maplibregl || !window.pmtiles) { status('کتابخانه‌های محلی MapLibre/PMTiles بارگذاری نشده‌اند.'); return; }
    try {
      var protocol = new window.pmtiles.Protocol();
      window.maplibregl.addProtocol('pmtiles', protocol.tile);
      fetch('modules/offline-map/data/style.json', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (response) { if (!response.ok) throw new Error('دریافت استایل محلی نقشه ناموفق بود.'); return response.json(); })
        .then(function (style) {
          // MapLibre requires an absolute glyph URL. Resolve it against the current application path,
          // and resolve PMTiles to the same local origin so subfolder deployments remain portable.
          style.glyphs = new URL('modules/offline-map/data/fonts/', window.location.href).toString() + '{fontstack}/{range}.pbf';
          if (style.sources && style.sources.iran) {
            style.sources.iran.url = 'pmtiles://' + new URL('modules/offline-map/data/iran.pmtiles', window.location.href).toString();
          }
          var map = new window.maplibregl.Map({
            container: mapNode, style: style,
            center: [53.2, 32.4], zoom: 4.3, minZoom: 3, maxZoom: 19,
            attributionControl: true, preserveDrawingBuffer: true
          });
          state.map = map;
      map.addControl(new window.maplibregl.NavigationControl({ showCompass: true }), 'top-left');
      map.addControl(new window.maplibregl.ScaleControl({ maxWidth: 120, unit: 'metric' }), 'bottom-left');
      map.on('mousemove', function (event) {
        var node = document.getElementById('offline-map-coordinate');
        if (node) node.textContent = 'مختصات: ' + event.lngLat.lng.toFixed(5) + ' ، ' + event.lngLat.lat.toFixed(5);
      });
      map.on('load', function () {
        map.addSource('offline-map-user-features', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
        map.addLayer({ id: 'offline-map-user-polygons', type: 'fill', source: 'offline-map-user-features', filter: ['==', ['geometry-type'], 'Polygon'], paint: { 'fill-color': ['coalesce', ['get', 'color'], '#16866b'], 'fill-opacity': 0.22, 'fill-outline-color': '#087f67' } });
        map.addLayer({ id: 'offline-map-user-lines', type: 'line', source: 'offline-map-user-features', filter: ['==', ['geometry-type'], 'LineString'], paint: { 'line-color': '#087f67', 'line-width': 4 } });
        map.addLayer({ id: 'offline-map-user-points', type: 'circle', source: 'offline-map-user-features', filter: ['==', ['geometry-type'], 'Point'], paint: { 'circle-radius': 7, 'circle-color': '#087f67', 'circle-stroke-width': 2, 'circle-stroke-color': '#ffffff' } });
        map.addSource('offline-map-draft', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
        map.addLayer({ id: 'offline-map-draft-fill', type: 'fill', source: 'offline-map-draft', paint: { 'fill-color': '#e7a33e', 'fill-opacity': 0.22 } });
        map.addLayer({ id: 'offline-map-draft-line', type: 'line', source: 'offline-map-draft', paint: { 'line-color': '#c47713', 'line-width': 3, 'line-dasharray': [2, 1] } });
        map.addLayer({ id: 'offline-map-draft-point', type: 'circle', source: 'offline-map-draft', paint: { 'circle-radius': 5, 'circle-color': '#c47713' } });
        if (mapNode.getAttribute('data-official-boundaries') === '1') {
          loadLocalOverlay('official-boundaries.geojson', 'offline-map-official-boundaries', [
            { id: 'offline-map-official-fill', type: 'fill', source: 'offline-map-official-boundaries', paint: { 'fill-color': '#8aa89a', 'fill-opacity': 0.035 } },
            { id: 'offline-map-official-line', type: 'line', source: 'offline-map-official-boundaries', paint: { 'line-color': '#658b79', 'line-width': ['interpolate', ['linear'], ['zoom'], 4, 0.8, 12, 1.5], 'line-opacity': 0.8 } }
          ]);
        }
        if (mapNode.getAttribute('data-municipal-districts') === '1') {
          loadLocalOverlay('municipal-districts.geojson', 'offline-map-municipal-districts', [
            { id: 'offline-map-district-fill', type: 'fill', source: 'offline-map-municipal-districts', paint: { 'fill-color': '#c5a36a', 'fill-opacity': 0.22 } },
            { id: 'offline-map-district-line', type: 'line', source: 'offline-map-municipal-districts', paint: { 'line-color': '#b08a4b', 'line-width': 1, 'line-opacity': 0.55 } }
          ]);
        }
        if (mapNode.getAttribute('data-transit-overlay') === '1') {
          loadLocalOverlay('transit.geojson', 'offline-map-transit', [
            { id: 'offline-map-transit-lines', type: 'line', source: 'offline-map-transit', filter: ['in', ['geometry-type'], ['literal', ['LineString', 'MultiLineString']]], paint: { 'line-color': '#7b4ca0', 'line-width': 3 } },
            { id: 'offline-map-transit-points', type: 'circle', source: 'offline-map-transit', filter: ['==', ['geometry-type'], 'Point'], paint: { 'circle-color': '#7b4ca0', 'circle-radius': 5, 'circle-stroke-color': '#fff', 'circle-stroke-width': 1.5 } }
          ]);
        }
        loadFeatures(false).then(function () { status('نقشه و عوارض سازمانی بارگذاری شدند.'); }).catch(function (e) { status(e.message); });
        ['offline-map-user-points', 'offline-map-user-lines', 'offline-map-user-polygons'].forEach(function (layer) {
          map.on('click', layer, function (event) { if (state.tool !== 'pan') return; var f = event.features && event.features[0]; if (f) popupForFeature(f, event.lngLat); });
          map.on('mouseenter', layer, function () { if (state.tool === 'pan') map.getCanvas().style.cursor = 'pointer'; });
          map.on('mouseleave', layer, function () { if (state.tool === 'pan') map.getCanvas().style.cursor = ''; });
        });
        map.on('click', function (event) {
          if (!['Point', 'LineString', 'Polygon', 'measure', 'measure-area'].includes(state.tool)) return;
          var point = [event.lngLat.lng, event.lngLat.lat];
          state.vertices.push(point); updateDrawPreview();
          var undo = document.getElementById('offline-map-undo'), finish = document.getElementById('offline-map-finish'), cancel = document.getElementById('offline-map-cancel');
          if (undo) undo.disabled = false;
          if (finish) finish.disabled = state.tool === 'Point' ? false : state.vertices.length < (state.tool === 'LineString' ? 2 : 3);
          if (cancel) cancel.disabled = false;
          if (state.tool === 'measure' && state.vertices.length > 1) status('طول فعلی: ' + distanceKm(state.vertices).toFixed(3) + ' کیلومتر');
          else if (state.tool === 'measure-area' && state.vertices.length > 2) status('مساحت تقریبی: ' + areaKm2(state.vertices).toFixed(3) + ' کیلومتر مربع');
          else status('تعداد رأس‌های انتخاب‌شده: ' + state.vertices.length);
          if (state.tool === 'Point') finishDrawing();
        });
      });
          map.on('error', function (event) { if (event && event.error) status('خطای بارگذاری نقشه: ' + event.error.message); });
        }).catch(function (e) { status('راه‌اندازی نقشه ناموفق بود: ' + e.message); });
    } catch (e) { status('راه‌اندازی نقشه ناموفق بود: ' + e.message); }
  }

  document.querySelectorAll('[data-tool]').forEach(function (button) { button.addEventListener('click', function () { setTool(button.getAttribute('data-tool')); }); });
  var undo = document.getElementById('offline-map-undo');
  if (undo) undo.addEventListener('click', function () { state.vertices.pop(); updateDrawPreview(); undo.disabled = !state.vertices.length; });
  var finish = document.getElementById('offline-map-finish');
  if (finish) finish.addEventListener('click', finishDrawing);
  var cancel = document.getElementById('offline-map-cancel');
  if (cancel) cancel.addEventListener('click', function () { state.vertices = []; updateDrawPreview(); setTool('pan'); });
  document.querySelectorAll('[data-dialog-close]').forEach(function (button) { button.addEventListener('click', function () { if (dialog) dialog.close(); setTool('pan'); }); });
  if (dialog) dialog.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
  if (form) form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!state.geometry) { if (formFeedback) formFeedback.textContent = 'هندسه‌ای برای ذخیره وجود ندارد.'; return; }
    var data = new FormData(form);
    data.set('action', 'save'); data.set('geometry', JSON.stringify(state.geometry));
    data.set('properties', JSON.stringify({ category: form.elements.category.value, address: form.elements.address.value, phone: form.elements.phone.value }));
    if (formFeedback) formFeedback.textContent = 'در حال ذخیره…';
    post(data).then(function (body) {
      var featureId = Number(body.id || state.editId);
      var filesInput = form.elements.namedItem('files[]');
      var files = filesInput && filesInput.files ? Array.prototype.slice.call(filesInput.files) : [];
      if (!files.length) return body;
      var upload = new FormData(); upload.set('action', 'attachment'); upload.set('feature_id', String(featureId));
      files.forEach(function (file) { upload.append('files[]', file); });
      return post(upload).then(function () { return body; });
    }).then(function (body) {
      dialog.close(); state.geometry = null; state.editId = 0; setTool('pan');
      return loadFeatures(false).then(function () { status(body.message || 'ذخیره شد.'); });
    }).catch(function (e) { if (formFeedback) formFeedback.textContent = e.message; });
  });
  var featureList = document.getElementById('offline-map-feature-list');
  if (featureList) featureList.addEventListener('click', function (event) {
    var button = event.target.closest('[data-feature-focus]'); if (!button || !state.map) return;
    var item = state.features.find(function (x) { return Number(x.id) === Number(button.getAttribute('data-feature-focus')); });
    if (!item || !item.geometry) return;
    var coords = item.geometry.type === 'Point' ? item.geometry.coordinates : item.geometry.type === 'LineString' ? item.geometry.coordinates : item.geometry.coordinates[0];
    var lng = 0, lat = 0; coords.forEach(function (p) { lng += p[0]; lat += p[1]; });
    state.map.flyTo({ center: [lng / coords.length, lat / coords.length], zoom: item.geometry.type === 'Point' ? 15 : 12 });
  });
  var mine = document.getElementById('offline-map-only-mine');
  if (mine) mine.addEventListener('change', function () { loadFeatures(mine.checked).catch(function (e) { status(e.message); }); });
  document.querySelectorAll('[data-layer-toggle]').forEach(function (input) {
    input.addEventListener('change', function () {
      var layer = input.getAttribute('data-layer-toggle');
      var layerMap = { roads: ['roads-casing','roads-main','road-labels'], places: ['places','poi-circles','poi-labels'], buildings: ['buildings'], water: ['water','waterway'], boundaries: ['admin-boundaries','offline-map-official-fill','offline-map-official-line'], 'official-boundaries': ['offline-map-official-fill','offline-map-official-line'], districts: ['offline-map-district-fill','offline-map-district-line'], transport: ['railways','rail-stations','transit-points','offline-map-transit-lines','offline-map-transit-points'] };
      if (state.map && layerMap[layer]) layerMap[layer].forEach(function (id) { if (state.map.getLayer(id)) state.map.setLayoutProperty(id, 'visibility', input.checked ? 'visible' : 'none'); });
      if (layer === 'user' && state.map) {
        ['offline-map-user-points', 'offline-map-user-lines', 'offline-map-user-polygons'].forEach(function (id) {
          if (state.map.getLayer(id)) state.map.setLayoutProperty(id, 'visibility', input.checked ? 'visible' : 'none');
        });
      }
    });
  });

  var searchForm = document.getElementById('offline-map-place-search');
  if (searchForm) searchForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var q = document.getElementById('offline-map-search-input').value.trim();
    var results = document.getElementById('offline-map-search-results');
    if (q.length < 2) { results.textContent = 'حداقل دو نویسه وارد کنید.'; return; }
    results.textContent = 'در حال جست‌وجو در شاخص محلی…';
    request(api + '&kind=places&q=' + encodeURIComponent(q)).then(function (body) {
      results.replaceChildren();
      if (!body.results || !body.results.length) { results.textContent = 'نتیجه‌ای پیدا نشد.'; return; }
      body.results.forEach(function (item) {
        var button = document.createElement('button'); button.type = 'button'; button.className = 'offline-map-search-result';
        var name = document.createElement('strong'); name.textContent = item.name_fa || item.name;
        var detail = document.createElement('small'); detail.textContent = (item.category || item.kind || 'مکان') + ' · ' + item.name;
        button.appendChild(name); button.appendChild(detail);
        button.addEventListener('click', function () {
          if (state.map) state.map.flyTo({ center: [Number(item.lon), Number(item.lat)], zoom: 16 });
          var marker = document.createElement('div'); marker.className = 'offline-map-search-marker';
          if (state.map) new window.maplibregl.Marker({ element: marker }).setLngLat([Number(item.lon), Number(item.lat)]).addTo(state.map);
        });
        results.appendChild(button);
      });
    }).catch(function (e) { results.textContent = e.message; });
  });

  var districtOpacity = document.getElementById('offline-map-district-opacity');
  if (districtOpacity) districtOpacity.addEventListener('input', function () {
    if (state.map && state.map.getLayer('offline-map-district-fill')) state.map.setPaintProperty('offline-map-district-fill', 'fill-opacity', Number(districtOpacity.value) / 100);
  });
  var exportButton = document.getElementById('offline-map-export');
  if (exportButton) exportButton.addEventListener('click', function () {
    var collection = { type: 'FeatureCollection', features: state.features.map(function (item) {
      return { type: 'Feature', id: item.id, geometry: item.geometry, properties: { title: item.title, description: item.description, feature_type: item.feature_type, properties: item.properties || {} } };
    }) };
    var blob = new Blob([JSON.stringify(collection, null, 2)], { type: 'application/geo+json;charset=utf-8' });
    var url = URL.createObjectURL(blob); var link = document.createElement('a'); link.href = url; link.download = 'offline-map-features.geojson';
    document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(url);
  });
  var printButton = document.getElementById('offline-map-print');
  if (printButton) printButton.addEventListener('click', function () { window.print(); });
  initMap();
})();
