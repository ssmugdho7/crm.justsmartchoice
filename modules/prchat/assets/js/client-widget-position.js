(function () {
  'use strict';
  var button = document.getElementById('ch_pointer-main');
  var widget = button && button.closest('.ch_pointer');
  if (!widget) return;
  var key = 'prchat-client-widget-position';
  var drag, position, ignoreClickUntil = 0;
  var margin = 8;
  button.style.touchAction = 'none';

  function bounds() {
    return {
      x: Math.max(margin, window.innerWidth - widget.offsetWidth - margin),
      y: Math.max(margin, window.innerHeight - widget.offsetHeight - margin)
    };
  }
  function move(x, y) {
    var limit = bounds();
    x = Math.max(margin, Math.min(limit.x, x));
    y = Math.max(margin, Math.min(limit.y, y));
    widget.style.setProperty('left', x + 'px', 'important');
    widget.style.setProperty('top', y + 'px', 'important');
    widget.style.setProperty('right', 'auto', 'important');
    widget.style.setProperty('bottom', 'auto', 'important');
    position = {x: (x - margin) / (limit.x - margin || 1), y: (y - margin) / (limit.y - margin || 1)};
  }
  function restore() {
    if (!position) return;
    var limit = bounds();
    move(margin + position.x * (limit.x - margin), margin + position.y * (limit.y - margin));
  }
  try {
    var saved = JSON.parse(localStorage.getItem(key));
    if (saved && Number.isFinite(saved.x) && Number.isFinite(saved.y)) {
      position = {x: Math.max(0, Math.min(1, saved.x)), y: Math.max(0, Math.min(1, saved.y))};
      restore();
    }
  } catch (error) { /* Storage is optional; dragging still works. */ }

  button.addEventListener('pointerdown', function (event) {
    if (!event.isPrimary || event.button !== 0) return;
    var rect = widget.getBoundingClientRect();
    drag = {id: event.pointerId, x: event.clientX, y: event.clientY, left: rect.left, top: rect.top, moved: false};
    button.setPointerCapture(event.pointerId);
  });
  button.addEventListener('pointermove', function (event) {
    if (!drag || event.pointerId !== drag.id) return;
    var dx = event.clientX - drag.x, dy = event.clientY - drag.y;
    if (!drag.moved && Math.hypot(dx, dy) < 8) return;
    drag.moved = true;
    move(drag.left + dx, drag.top + dy);
    event.preventDefault();
  });
  function finish(event) {
    if (!drag || event.pointerId !== drag.id) return;
    if (drag.moved) {
      ignoreClickUntil = Date.now() + 500;
      try { localStorage.setItem(key, JSON.stringify(position)); } catch (error) { /* Optional. */ }
    }
    drag = null;
  }
  button.addEventListener('pointerup', finish);
  button.addEventListener('pointercancel', finish);
  button.addEventListener('lostpointercapture', finish);
  button.addEventListener('click', function (event) {
    if (event.detail !== 0 && Date.now() < ignoreClickUntil) {
      event.preventDefault();
      event.stopImmediatePropagation();
    }
  }, true);
  window.addEventListener('resize', restore);
  // Mobile browsers can settle their viewport and widget size after this script.
  window.addEventListener('load', restore);
  if (window.visualViewport) window.visualViewport.addEventListener('resize', restore);
  requestAnimationFrame(restore);
}());
