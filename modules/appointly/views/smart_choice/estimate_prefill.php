<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
(function($) {
    var source = <?= json_encode($prefill, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    function applyProperty() {
        if (String($('#clientid').val()) !== String(source.customer_id)) { return; }
        if (source.address) {
            $('#include_shipping').prop('checked', true).trigger('change');
            $('[name="shipping_street"]').val(source.address);
            $('[name="shipping_city"],[name="shipping_state"],[name="shipping_zip"]').val('');
            $('.shipping_street').text(source.address);
            $('.shipping_city,.shipping_state,.shipping_zip').text('');
            $('[name="google_map_link"]').val('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(source.address));
        }
    }
    // Customer initialization fetches native billing/shipping asynchronously. Apply the property once afterwards.
    $(document).on('ajaxComplete.appointmentPrefill', function(event, xhr, settings) {
        if (settings.url.indexOf('invoices/client_change_data/' + source.customer_id + '/') !== -1 && xhr.status >= 200 && xhr.status < 300) {
            applyProperty();
            $(document).off('ajaxComplete.appointmentPrefill');
        }
    });
    $(function() {
        $('[name="adminnote"]').val(source.notes + (source.address ? '\nProperty: ' + source.address : '') + '\nService: ' + source.service);
        $('.main [name="description"]').val(source.service);
        $('.main [name="long_description"]').val(source.notes);
        applyProperty();
    });
})(jQuery);
</script>
