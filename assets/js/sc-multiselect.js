(function ($) {
  'use strict';
  if (!$ || !$.fn.selectpicker) return;
  function enhance(select) {
    if (!select.multiple) return;
    var picker = $(select).data('selectpicker');
    if (picker) picker.$newElement.addClass('sc-multiselect show-tick');
  }
  $(document).on('loaded.bs.select rendered.bs.select refreshed.bs.select', 'select', function () {
    enhance(this);
  });
  $(function () { $('select[multiple]').each(function () { enhance(this); }); });
})(window.jQuery);
