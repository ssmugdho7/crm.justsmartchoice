(function () {
  'use strict';
  window.showSalesHubToast = function (title, message, type) {
    var toast = document.createElement('div');
    toast.className = 'sc-hub-toast';
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    var heading = document.createElement('strong'); heading.textContent = title;
    var body = document.createElement('p'); body.textContent = message;
    toast.appendChild(heading); toast.appendChild(body); document.body.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 6000);
  };
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.closest('#wrapper') || event.defaultPrevented || !form.checkValidity()) return;
    // AJAX validators own their lifecycle. Lock only ordinary valid navigation submits.
    if (window.jQuery && window.jQuery(form).data('validator')) return;
    if (form.dataset.saleshubSubmitting) { event.preventDefault(); return; }
    form.dataset.saleshubSubmitting = '1';
    var button = event.submitter;
    if (button) { button.classList.add('is-loading'); button.setAttribute('aria-busy', 'true'); }
  });
  window.addEventListener('pageshow', function () {
    document.querySelectorAll('[data-saleshub-submitting]').forEach(function (form) { delete form.dataset.saleshubSubmitting; });
    document.querySelectorAll('.is-loading[aria-busy]').forEach(function (button) { button.classList.remove('is-loading'); button.removeAttribute('aria-busy'); });
  });
})();
