(function () {
  'use strict';
  if (!/^\/admin\/utilities\/media\/?$/.test(location.pathname)) return;
  const $ = window.scMediaJQuery || window.jQuery;
  if (!$) return;
  $(function () {
    const manager = $('#elfinder').elfinder('instance');
    if (!manager || !manager.draggable) return;
    // Root entries navigate between volumes; they are not movable folders.
    // Keep file/subfolder dragging, but do not let a small pointer movement
    // swallow the click on Public (elFinder's drag threshold is only 4px).
    const cancel = manager.draggable.cancel || 'input,textarea,button,select,option';
    manager.draggable.cancel = cancel + ',.elfinder-navbar-root';
    // The bundled elFinder treats every non-JSON same-origin postMessage as a
    // reason to sync. Extension/devtools messages then abort folder navigation
    // and repeatedly reopen the previous directory. Replace only this manager's
    // legacy CORS listener, retaining the upload/editor response protocol.
    $(window).off('message.' + manager.namespace).on('message.' + manager.namespace, function (event) {
      const message = event.originalEvent;
      if (!message || typeof message.data !== 'string') return;
      const origins = [manager.options.url, manager.uploadURL].filter(Boolean).map(url => new URL(url, location.href).origin);
      if (!origins.includes(message.origin)) return;
      let response;
      try { response = JSON.parse(message.data); } catch (_) { return; }
      if (!response || typeof response !== 'object' || Array.isArray(response) ||
          !response.data || typeof response.data !== 'object' || Array.isArray(response.data)) return;
      const data = response.data;
      if (data.error) {
        if (response.bind) manager.trigger(response.bind + 'fail', data);
        manager.error(data.error);
        return;
      }
      if (data.warning) manager.error(data.warning);
      manager.updateCache(data);
      if (data.removed && data.removed.length) manager.remove(data);
      if (data.added && data.added.length) manager.add(data);
      if (data.changed && data.changed.length) manager.change(data);
      if (response.bind) {
        manager.trigger(response.bind, data);
        manager.trigger(response.bind + 'done');
      }
      if (data.sync) manager.sync();
    });
  });
})();
