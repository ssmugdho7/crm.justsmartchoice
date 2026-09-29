<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<script>
"use strict";

// Constants for URLs and data
if (typeof TOUR_GUIDE_CSRF_DATA === "undefined") {

    const TOUR_GUIDE_CSRF_DATA = typeof csrfData === "undefined" ? {} : csrfData?.formatted;
}

const TOUR_GUIDE_ADMIN_URL = "<?= tourGuideHelper()->adminUrl(); ?>";
const TOUR_GUIDE_USER_URL = "<?= tourGuideHelper()->userUrl(); ?>";
const TOUR_GUIDE_APP_BASE_URL = "<?= tourGuideHelper()->baseUrl(); ?>";
const TOUR_GUIDE_TOURS_RAW = <?= $tour_guides_json; ?>;

const TOUR_GUIDE_TOURS = {}
const TOUR_GUIDE_ACTIVE_TOURS = {};
const TOUR_GUIDE_TOURS_STEPS = {};
const TOUR_GUIDE_DEFAULT_EMPTY_SELECTOR = "body";

// Parse and prepare tour various format
for (const key in TOUR_GUIDE_TOURS_RAW) {

    const tour = TOUR_GUIDE_TOURS_RAW[key];
    tour.steps = tour.steps ? JSON.parse(tour.steps) : [];
    tour.settings = tour.settings ? JSON.parse(tour.settings) : {};
    tour.settings.triggers = tour.settings.triggers ? JSON.parse(tour.settings.triggers) : [];

    TOUR_GUIDE_TOURS[key] = tour;
    TOUR_GUIDE_TOURS_STEPS[key] = tour.steps;

    if (tour.status == 'active') {
        TOUR_GUIDE_ACTIVE_TOURS[key] = tour;
    }
}

// Contextual informations
const TOUR_GUIDE_USER_INFO = <?= json_encode(tourGuideHelper()->activeUser()); ?>;
const TOUR_GUIDE_USER_ROLES = <?= json_encode(tourGuideHelper()->userRoles()); ?>;

const TOUR_GUIDE_SYSTEM_LOCALE =
    "<?= tourGuideHelper()->systemLocale(); ?>"; //The default language inwhich the tour step is created i.e 'en'

const TOUR_GUIDE_TRANSLATIONS_MAP = {
    nextLabel: "<?= tourGuideHelper()->translate('tour_guide_next'); ?>", // text for next button
    prevLabel: "<?= tourGuideHelper()->translate('tour_guide_prev'); ?>", // text for prev button
    finishLabel: "<?= tourGuideHelper()->translate('tour_guide_finish'); ?>", // text for finish button

    // Others
    play: "<?= tourGuideHelper()->translate('tour_guide_play'); ?>",
    edit: "<?= tourGuideHelper()->translate('tour_guide_edit'); ?>",
    delete: "<?= tourGuideHelper()->translate('tour_guide_delete'); ?>",
    selector: "<?= tourGuideHelper()->translate('tour_guide_selector'); ?>",
    replace: "<?= tourGuideHelper()->translate('tour_guide_replace'); ?>",
    tags: "<?= tourGuideHelper()->translate('tour_guide_tags'); ?>",
    tagsHint: "<?= tourGuideHelper()->translate('tour_guide_tags_hint'); ?>",

    // Quilljs Image plugin
    editor: "<?= tourGuideHelper()->translate('tour_guide_editor'); ?>",
    imageURL: "<?= tourGuideHelper()->translate('tour_guide_image_url'); ?>",
    altText: "<?= tourGuideHelper()->translate('tour_guide_alt_text'); ?>",
    imageWidth: "<?= tourGuideHelper()->translate('tour_guide_image_width'); ?>",
    imageHeight: "<?= tourGuideHelper()->translate('tour_guide_image_height'); ?>",
};
</script>


<link rel="stylesheet" type="text/css" href="<?= tourGuideHelper()->asset('tour-guide.min.css'); ?>">
<script src="<?= tourGuideHelper()->asset('tour-guide.min.js'); ?>"></script>