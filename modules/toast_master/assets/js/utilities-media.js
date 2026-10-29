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
  });
})();
