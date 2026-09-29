<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<script>
"use strict";

/** 
 * Script to management tour create/edit form including triggers
 */
// Translations
const translations = {
    tour_guide_triggers: "<?= tourGuideHelper()->translate('tour_guide_triggers'); ?>",
    tour_guide_add_trigger: "<?= tourGuideHelper()->translate('tour_guide_add_trigger'); ?>",
    tour_guide_trigger_id: "<?= tourGuideHelper()->translate('tour_guide_trigger_id'); ?>",
    tour_guide_remove: "<?= tourGuideHelper()->translate('tour_guide_remove'); ?>",
    tour_guide_and: "<?= tourGuideHelper()->translate('tour_guide_and'); ?>",
    tour_guide_or: "<?= tourGuideHelper()->translate('tour_guide_or'); ?>",
    tour_guide_add_rule: "<?= tourGuideHelper()->translate('tour_guide_add_rule'); ?>",
    tour_guide_select_field: "<?= tourGuideHelper()->translate('tour_guide_select_field'); ?>",
    tour_guide_url: "<?= tourGuideHelper()->translate('tour_guide_url'); ?>",
    tour_guide_path: "<?= tourGuideHelper()->translate('tour_guide_path'); ?>",
    tour_guide_select_operator: "<?= tourGuideHelper()->translate('tour_guide_select_operator'); ?>",
    tour_guide_equals: "<?= tourGuideHelper()->translate('tour_guide_equals'); ?>",
    tour_guide_not_equals: "<?= tourGuideHelper()->translate('tour_guide_not_equals'); ?>",
    tour_guide_contains: "<?= tourGuideHelper()->translate('tour_guide_contains'); ?>",
    tour_guide_does_not_contain: "<?= tourGuideHelper()->translate('tour_guide_does_not_contain'); ?>",
    tour_guide_starts_with: "<?= tourGuideHelper()->translate('tour_guide_starts_with'); ?>",
    tour_guide_not_starts_with: "<?= tourGuideHelper()->translate('tour_guide_not_starts_with'); ?>",
    tour_guide_ends_with: "<?= tourGuideHelper()->translate('tour_guide_ends_with'); ?>",
    tour_guide_not_ends_with: "<?= tourGuideHelper()->translate('tour_guide_not_ends_with'); ?>",
    tour_guide_greater_than: "<?= tourGuideHelper()->translate('tour_guide_greater_than'); ?>",
    tour_guide_less_than: "<?= tourGuideHelper()->translate('tour_guide_less_than'); ?>",
    tour_guide_greater_than_or_equals: "<?= tourGuideHelper()->translate('tour_guide_greater_than_or_equals'); ?>",
    tour_guide_less_than_or_equals: "<?= tourGuideHelper()->translate('tour_guide_less_than_or_equals'); ?>",
    tour_guide_matches_regex: "<?= tourGuideHelper()->translate('tour_guide_matches_regex'); ?>",
    tour_guide_is_empty: "<?= tourGuideHelper()->translate('tour_guide_is_empty'); ?>",
    tour_guide_is_not_empty: "<?= tourGuideHelper()->translate('tour_guide_is_not_empty'); ?>",
    tour_guide_value_placeholder: "<?= tourGuideHelper()->translate('tour_guide_value_placeholder'); ?>",

    tour_guide_referrer: "<?= tourGuideHelper()->translate('tour_guide_referrer'); ?>",
    tour_guide_browser: "<?= tourGuideHelper()->translate('tour_guide_browser'); ?>",
    tour_guide_device: "<?= tourGuideHelper()->translate('tour_guide_device'); ?>",
    tour_guide_os: "<?= tourGuideHelper()->translate('tour_guide_os'); ?>",
    tour_guide_time_on_page: "<?= tourGuideHelper()->translate('tour_guide_time_on_page'); ?>",
    tour_guide_number_of_visits: "<?= tourGuideHelper()->translate('tour_guide_number_of_visits'); ?>",
    tour_guide_language: "<?= tourGuideHelper()->translate('tour_guide_language'); ?>",
    tour_guide_screen_width: "<?= tourGuideHelper()->translate('tour_guide_screen_width'); ?>",
    tour_guide_screen_height: "<?= tourGuideHelper()->translate('tour_guide_screen_height'); ?>",
    tour_guide_scroll_position: "<?= tourGuideHelper()->translate('tour_guide_scroll_position'); ?>",
    tour_guide_user_role: "<?= tourGuideHelper()->translate('tour_guide_user_role'); ?>",
    tour_guide_finished_tours: "<?= tourGuideHelper()->translate('tour_guide_finished_tours'); ?>",

    primary_btn_class: "btn btn-primary btn-sm",
    secondary_btn_class: "btn btn-info btn-sm",
    accent_btn_class: "btn btn-danger btn-sm"
};


document.addEventListener("DOMContentLoaded", () => {

    // Initialize the query builder
    const queryBuilder = new TourGuideTriggerQueryBuilder('tour-guide-widget-container', translations);

    // Initate the Emoji picker to be used in tour form fields
    new TourGuideEmojiPicker({
        widgetSelector: ".tour-guide-form-main",
        emojiTriggerText: "😀",
        emojiTriggerSize: 24,
    });
});
</script>