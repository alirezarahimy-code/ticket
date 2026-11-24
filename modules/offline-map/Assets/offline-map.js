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
    return node ? node.content : (window.ITSM_CSRF || '');
  }
  function post(data) {
    data.set('csrf', csrfToken());
    return request('offline-map.php', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' }, body: data });
  }
  function setTool(tool) {
    state.tool = tool;
    state.vertices = [];
    document.querySelectorAll('[data-tool]').forEach(function (button) {
      button.classList.toggle('is-active', button.getAttribute('data-tool') === tool);
    });
    var drawing = tool === 'Point' || tool === 'LineString' || tool === 'Polygon' || tool === 'measure';
    ['offline-map-undo', 'offline-map-finish', 'offline-map-cancel'].forEach(function (id) {
      var button = document.getElementById(id);
      if (button) button.disabled = !drawing || state.vertices.length === 0;
    });
    if (state.map) state.map.getCanvas().style.cursor = drawing ? 'crosshair' : '';
    if (tool === 'measure') status('برای اندازه‌گیری، نقاط مسیر را روی نقشه انتخاب کنید.');
    else if (drawing) status('برای ترسیم، رأس‌ها را روی نقشه انتخاب کنید؛ سپس «پایان ترسیم» را بزنید.');
    else status('حالت انتخاب و جابه‌جایی فعال است.');
  }
  function updateDrawPreview() {
    if (!state.map || !state.map.getSource('offline-map-draft')) return;
    var geometry = null;
    if (state.tool === 'Point' && state.vertices.length) geometry = { type: 'Point', coordinates: state.vertices[0] };
    if (state.tool === 'LineString' && state.vertices.length) geometry = { type: 'LineString', coordinates: state.vertices };
    if (state.tool === 'Polygon' && state.vertices.length >= 2) {
      var ring = state.vertices.slice();
      if (ring.length >= 3) ring.push(ring[0]);
      geometry = { type: 'Polygon', coordinates: [ring] };
    }
    state.map.getSource('offline-map-draft').setData({ type: 'FeatureCollection', features: geometry ? [{ type: 'Feature', geometry: geometry, properties: {} }] : [] });
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
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', 'open');
  }
  function addFeatureToMap(item) {
    var geometry = item.geometry;
    if (!geometry) return null;
    return { type: 'Feature', id: item.id, geometry: geometry, properties: {
      id: String(item.id), title: item.title || '', description: item.description || '',
      feature_type: item.feature_type || geometry.type, category: (item.properties || {}).category || 'general',
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
  function finishDrawing() {
    if (state.tool === 'measure') {
      status('طول مسیر: ' + distanceKm(state.vertices).toFixed(3) + ' کیلومتر');
      setTool('pan'); return;
    }
    if (state.tool === 'Point' && state.vertices.length >= 1) {
      openFeatureDialog({ type: 'Point', coordinates: state.vertices[0] });
    } else if (state.tool === 'LineString' && state.vertices.length >= 2) {
      openFeatureDialog({ type: 'LineString', coordinates: state.vertices.slice() });
    } else if (state.tool === 'Polygon' && state.vertices.length >= 3) {
      var ring = state.vertices.slice(); ring.push(ring[0]);
      openFeatureDialog({ type: 'Polygon', coordinates: [ring] });
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
    if (mapNode && mapNode.getAttribute('data-can-edit') === '1') {
      var edit = document.createElement('button'); edit.type = 'button'; edit.className = 'button secondary'; edit.textContent = 'ویرایش';
      edit.addEventListener('click', function () {
        var item = state.features.find(function (x) { return Number(x.id) === Number(feature.properties.id); });
        if (item) openFeatureDialog(item.geometry, item);
      });
      box.appendChild(edit);
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
  function initMap() {
    if (!mapNode || mapNode.getAttribute('data-map-ready') !== '1') return;
    if (!window.maplibregl || !window.pmtiles) { status('کتابخانه‌های محلی MapLibre/PMTiles بارگذاری نشده‌اند.'); return; }
    try {
      var protocol = new window.pmtiles.Protocol();
      window.maplibregl.addProtocol('pmtiles', protocol.tile);
      var map = new window.maplibregl.Map({
        container: mapNode, style: 'modules/offline-map/data/style.json',
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
        loadFeatures(false).then(function () { status('نقشه و عوارض سازمانی بارگذاری شدند.'); }).catch(function (e) { status(e.message); });
        ['offline-map-user-points', 'offline-map-user-lines', 'offline-map-user-polygons'].forEach(function (layer) {
          map.on('click', layer, function (event) { if (state.tool !== 'pan') return; var f = event.features && event.features[0]; if (f) popupForFeature(f, event.lngLat); });
          map.on('mouseenter', layer, function () { if (state.tool === 'pan') map.getCanvas().style.cursor = 'pointer'; });
          map.on('mouseleave', layer, function () { if (state.tool === 'pan') map.getCanvas().style.cursor = ''; });
        });
        map.on('click', function (event) {
          if (!['Point', 'LineString', 'Polygon', 'measure'].includes(state.tool)) return;
          var point = [event.lngLat.lng, event.lngLat.lat];
          state.vertices.push(point); updateDrawPreview();
          var undo = document.getElementById('offline-map-undo'), finish = document.getElementById('offline-map-finish'), cancel = document.getElementById('offline-map-cancel');
          if (undo) undo.disabled = false;
          if (finish) finish.disabled = state.tool === 'Point' ? false : state.vertices.length < (state.tool === 'LineString' ? 2 : 3);
          if (cancel) cancel.disabled = false;
          if (state.tool === 'measure' && state.vertices.length > 1) status('طول فعلی: ' + distanceKm(state.vertices).toFixed(3) + ' کیلومتر');
          else status('تعداد رأس‌های انتخاب‌شده: ' + state.vertices.length);
          if (state.tool === 'Point') finishDrawing();
        });
      });
      map.on('error', function (event) { if (event && event.error) status('خطای بارگذاری نقشه: ' + event.error.message); });
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

  initMap();
})();
