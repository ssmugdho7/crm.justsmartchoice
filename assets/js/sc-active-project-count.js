(function () {
  'use strict';
  var widget = document.getElementById('widget-sc_active_projects');
  if (!widget || widget.dataset.refreshBound) return;
  widget.dataset.refreshBound = '1';
  var count = widget.querySelector('[data-active-project-count]');
  var pending = false;
  function refresh() {
    if (pending || document.hidden || !count) return;
    pending = true;
    fetch(widget.dataset.countUrl, {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json'}})
      .then(function (response) { if (!response.ok) throw new Error('Count unavailable'); return response.json(); })
      .then(function (data) {
        if (Number.isInteger(data.count) && data.count >= 0) count.textContent = String(data.count);
      })
      .catch(function () { /* Keep the last valid count on network/session errors. */ })
      .finally(function () { pending = false; });
  }
  window.addEventListener('focus', refresh);
  window.addEventListener('pageshow', refresh);
  document.addEventListener('visibilitychange', refresh);
  window.setInterval(refresh, 30000);
  refresh();
})();
