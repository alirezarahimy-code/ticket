(function () {
  'use strict';
  var form = document.getElementById('offline-map-create');
  var feedback = document.getElementById('offline-map-feedback');
  var endpoint = 'offline-map.php?api=1';
  function send(data) {
    return fetch(endpoint, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': data.csrf || '', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: data });
  }
  if (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var data = new FormData(form);
      data.set('action', 'create');
      if (feedback) feedback.textContent = 'در حال ثبت…';
      send(data).then(function (response) {
        return response.json().then(function (body) { if (!response.ok) throw new Error(body.error || 'خطا در ثبت'); return body; });
      }).then(function (body) {
        if (feedback) feedback.textContent = body.message || 'ثبت شد.';
        window.location.reload();
      }).catch(function (error) { if (feedback) feedback.textContent = error.message; });
    });
  }
  var mapNode = document.getElementById('offline-map-map');
  if (mapNode && mapNode.getAttribute('data-map-ready') === '1') {
    try {
      if (!window.maplibregl || !window.pmtiles) throw new Error('کتابخانه‌های محلی نقشه بارگذاری نشده‌اند.');
      var protocol = new window.pmtiles.Protocol();
      window.maplibregl.addProtocol('pmtiles', protocol.tile);
      var map = new window.maplibregl.Map({
        container: mapNode,
        style: 'modules/offline-map/data/style.json',
        center: [53.2, 32.4],
        zoom: 4.3,
        attributionControl: true
      });
      map.addControl(new window.maplibregl.NavigationControl(), 'top-left');
      map.on('load', function () {
        fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
          .then(function (response) { if (!response.ok) throw new Error('دریافت عوارض نقشه ناموفق بود.'); return response.json(); })
          .then(function (body) {
            var features = (body.features || []).map(function (item) {
              return { type: 'Feature', id: item.id, geometry: item.geometry, properties: { title: item.title || '', description: item.description || '' } };
            });
            map.addSource('offline-map-user-features', { type: 'geojson', data: { type: 'FeatureCollection', features: features } });
            map.addLayer({ id: 'offline-map-user-points', type: 'circle', source: 'offline-map-user-features',
              paint: { 'circle-radius': 7, 'circle-color': '#087f67', 'circle-stroke-width': 2, 'circle-stroke-color': '#ffffff' } });
            map.on('click', 'offline-map-user-points', function (event) {
              var feature = event.features && event.features[0]; if (!feature) return;
              var content = document.createElement('div');
              var heading = document.createElement('strong'); heading.textContent = feature.properties.title || 'نقطه';
              var description = document.createElement('p'); description.textContent = feature.properties.description || '';
              content.appendChild(heading); content.appendChild(description);
              new window.maplibregl.Popup().setLngLat(event.lngLat).setDOMContent(content).addTo(map);
            });
            map.on('mouseenter', 'offline-map-user-points', function () { map.getCanvas().style.cursor = 'pointer'; });
            map.on('mouseleave', 'offline-map-user-points', function () { map.getCanvas().style.cursor = ''; });
          }).catch(function (error) { if (feedback) feedback.textContent = error.message; });
      });
    } catch (error) {
      if (feedback) feedback.textContent = error.message;
    }
  }
  document.querySelectorAll('[data-delete-feature]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!window.confirm('این نقطه حذف شود؟')) return;
      var data = new FormData();
      data.set('csrf', window.ITSM_CSRF || (document.querySelector('#offline-map-create [name=csrf]') || {}).value || '');
      data.set('action', 'delete');
      data.set('id', button.getAttribute('data-delete-feature') || '');
      send(data).then(function (response) {
        return response.json().then(function (body) { if (!response.ok) throw new Error(body.error || 'خطا در حذف'); return body; });
      }).then(function () { window.location.reload(); }).catch(function (error) { window.alert(error.message); });
    });
  });
})();