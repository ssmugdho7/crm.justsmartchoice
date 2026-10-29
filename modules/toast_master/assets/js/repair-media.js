(function () {
  'use strict';
  if (!/^\/admin\/development\/view\/\d+\/?$/.test(location.pathname)) return;
  const labels = window.scRepairMediaLabels;
  if (!labels) return;
  function sourceFor(media) {
    const value = media.currentSrc || media.src || media.querySelector('source')?.src;
    try {
      const uri = new URL(value, location.href);
      return uri.origin === location.origin && /^\/admin\/development\/file\/\d+\/?$/.test(uri.pathname) ? uri.href : null;
    } catch (_) { return null; }
  }
  function open(media) {
    const src = sourceFor(media);
    if (!src || document.querySelector('.sc-repair-media-dialog')) return;
    const previous = document.activeElement;
    const dialog = document.createElement('dialog');
    dialog.className = 'sc-repair-media-dialog';
    dialog.setAttribute('aria-label', labels.preview);
    const close = document.createElement('button');
    close.type = 'button'; close.className = 'sc-repair-media-close';
    close.textContent = '×'; close.setAttribute('aria-label',labels.close);
    const preview = document.createElement(media.tagName === 'VIDEO' ? 'video' : 'img');
    preview.src = src;
    if (preview.tagName === 'VIDEO') {
      media.pause();
      preview.controls = true; preview.preload = 'metadata'; preview.playsInline = true;
    } else { preview.alt = media.alt || labels.preview; }
    function dismiss() { dialog.close(); }
    close.addEventListener('click', dismiss);
    dialog.addEventListener('click', event => {
      if (event.target === dialog || (preview.tagName === 'IMG' && event.target === preview)) dismiss();
    });
    dialog.addEventListener('close', () => {
      if (preview.tagName === 'VIDEO') preview.pause();
      dialog.remove(); previous?.focus({preventScroll:true});
      document.dispatchEvent(new Event('sc-portal-overlay-change'));
    }, {once:true});
    dialog.append(close, preview); document.body.appendChild(dialog); dialog.showModal(); close.focus();
    document.dispatchEvent(new Event('sc-portal-overlay-change'));
  }
  document.addEventListener('click', event => {
    const media = event.target.closest('a img');
    if (media && sourceFor(media)) event.preventDefault();
  });
  document.addEventListener('dblclick', event => {
    const media = event.target.closest('img,video');
    if (media && sourceFor(media)) { event.preventDefault(); open(media); }
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Enter') return;
    const media = event.target.matches('a') ? event.target.querySelector('img') : null;
    if (media && sourceFor(media)) { event.preventDefault(); open(media); }
  });
})();
