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
    // Opening another volume can finish before the native tree's lazy loading
    // expands it. Reveal its children after navigation, using the native arrow
    // handler so loading, permissions and later collapse remain elFinder-owned.
    manager.bind('opendone', function () {
      window.requestAnimationFrame(function () {
        const cwd = manager.cwd();
        if (!cwd) return;
        const entry = $(document.getElementById(manager.navHash2Id(cwd.hash)));
        if (entry.hasClass('elfinder-navbar-root') && entry.hasClass('elfinder-navbar-collapsed') &&
            !entry.hasClass('elfinder-navbar-expanded')) {
          entry.children('.elfinder-navbar-arrow').trigger('click');
        }
      });
    });
  });
})();
