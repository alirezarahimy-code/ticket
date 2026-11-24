(function () {
  'use strict';
  var form = document.getElementById('offline-map-create');
  var feedback = document.getElementById('offline-map-feedback');
  var endpoint = 'offline-map.php';
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