(function () {
  'use strict';
  var dashboard = document.querySelector('.sc-training-dashboard');
  if (!dashboard) return;
  dashboard.querySelectorAll('[data-training-audience]').forEach(function (button) {
    button.addEventListener('click', function () {
      var audience = button.getAttribute('data-training-audience');
      var visible = 0;
      dashboard.querySelectorAll('[data-training-book-audience]').forEach(function (book) {
        book.hidden = audience !== 'all' && book.getAttribute('data-training-book-audience') !== audience;
        if (!book.hidden) visible++;
      });
      dashboard.querySelectorAll('[data-training-audience]').forEach(function (option) { option.setAttribute('aria-pressed', option === button ? 'true' : 'false'); });
      dashboard.querySelector('.sc-training-filter-empty').hidden = visible !== 0;
    });
  });
})();
