<div class="tour-guide-user-widget-wrapper" style="display: none;">
    <div class="tour-guide-user-widget" data-dettached></div>
    <!-- Custom Trigger Icon for the Widget -->
    <button class="btn-primary tour-guide-user-trigger closed">
        <i class="tour-guide-icon  tour-guide-icon-question-circle" data-opened></i>
        <i class="tour-guide-icon  tour-guide-icon-close" data-closed></i>
    </button>
</div>


<script>
"use strict";

document.addEventListener("DOMContentLoaded", function() {
    // Custom translation keys
    const translations = {
        tour_guide_user_widget_close: "<?= tourGuideHelper()->translate('tour_guide_user_widget_close'); ?>",
        tour_guide_user_widget_open: "<?= tourGuideHelper()->translate('tour_guide_user_widget_open'); ?>",
        tour_guide_user_widget_one_tour_available: "<?= tourGuideHelper()->translate('tour_guide_user_widget_one_tour_available'); ?>",
        tour_guide_user_widget_multiple_tours_available: "<?= tourGuideHelper()->translate('tour_guide_user_widget_multiple_tours_available'); ?>",
        tour_guide_user_widget_no_tours_available: "<?= tourGuideHelper()->translate('tour_guide_user_widget_no_tours_available'); ?>",
        tour_guide_user_widget_start: "<i class='tour-guide-icon  tour-guide-icon-play-circle' data-toggle='tooltip' title='<?= tourGuideHelper()->translate('tour_guide_user_widget_start'); ?>'></i>",
        tour_guide_user_widget_toggle_open: "<?= tourGuideHelper()->translate('tour_guide_user_widget_toggle_open'); ?>",
        tour_guide_user_widget_toggle_close: "<?= tourGuideHelper()->translate('tour_guide_user_widget_toggle_close'); ?>",
    };

    const tourCanBeLaunched = (item) =>
        !item.settings.triggers?.length ||
        TourGuideTriggerEvaluator.evaluateTriggers(
            item.settings.triggers,
            TourGuideTriggerContext.getContext()
        );

    // Initialize the Tour Guide User Widget
    const tourGuideUserTours = Object.values(TOUR_GUIDE_ACTIVE_TOURS).filter((item) => Object
        .keys(item.steps).length && item.settings.allowManualReplay).filter((item) => tourCanBeLaunched(
        item)).sort((tourA, tourB) => tourB
        .priority - tourA.priority);

    if (tourGuideUserTours.length) {

        new TourGuideUserWidget(
            tourGuideUserTours, {
                triggerIcon: document.querySelector('.tour-guide-user-trigger'), // Custom trigger icon
                widgetWrapperSelector: '.tour-guide-user-widget-wrapper',
                widgetContainerSelector: '.tour-guide-user-widget', // Optional custom styling class
                translations: translations, // Translations object
                isMovable: true, // Enable dragging of the widget
                allowSingleTourMode: true,
                userContext: TOUR_GUIDE_USER_CONTEXT,
            }
        );
    }
});
</script>