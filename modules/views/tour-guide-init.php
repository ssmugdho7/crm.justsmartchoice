<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<link rel="stylesheet" type="text/css" href="<?= tourGuideHelper()->module_asset('css/tour-guide-custom.css'); ?>">

<script>
"use strict";
const TOUR_GUIDE_CSRF_DATA = <?= json_encode(get_csrf_for_ajax()['formatted']); ?>;
const TOUR_GUIDE_NOTIFIER = (type, message) => {
    if (typeof alert_float == 'function')
        return alert_float(type == 'success' ? type : 'danger', message);

    if (type != 'success') {
        alert(`${type}:${message}`);
    }
};

// Override hotkeys when in setup mode
<?php if (tourGuideHelper()->setupSessionActive()) : ?>

function add_hotkey(params) {
    return null;
}
<?php endif; ?>
</script>

<?php \TourGuide\TourGuideUIHelper::init(); ?>