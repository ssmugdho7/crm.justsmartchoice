<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<!-- Uses setup script -->
<script>
"use strict";

// Extract active tour and its steps
const TOUR_GUIDE_SETUP_ACTIVE_TOUR_ID = "<?= $active_tour_guide_id; ?>";
const TOUR_GUIDE_SETUP_ACTIVE_TOUR = TOUR_GUIDE_TOURS[TOUR_GUIDE_SETUP_ACTIVE_TOUR_ID] || {
    steps: []
};

// Instantiate the TourGuideSetupWidget with appropriate configuration object.
const tourGuideSetupWidget = new TourGuideSetupWidget({
    widgetSelector: ".tour-guide-widget",
    headerSelector: ".tour-guide-widget-header",
    widgetBodySelector: ".tour-guide-widget-content",
    minimizeBtnSelector: "#tour-guide-minimize-btn",
    maximizeBtnSelector: "#tour-guide-maximize-btn",
    closeBtnSelector: "#tour-guide-close-btn",
    autoSaveToggleSelector: "#tour-guide-auto-save-toggle",
    tourGuideListsSelector: "#tour-guide-tour-select",
    notifier: TOUR_GUIDE_NOTIFIER,
    httpRequest: {
        baseUrl: TOUR_GUIDE_ADMIN_URL,
        extraPostData: {},
        headers: {}
    }
});
window.tourGuideSetupWidget = tourGuideSetupWidget;

// Instantiate the TourGuideStepManager with the appropriate configuration object.
const tourGuideStepManager = new TourGuideStepManager({
    dropAreaSelector: "#tour-guide-drop-area",
    accordionSelector: "#tour-guide-steps-accordion",
    saveBtnSelector: "#tour-guide-save-btn",
    addBtnSelector: "#tour-guide-step-add-btn",
    stopBtnSelector: "#tour-guide-step-stop-btn",
    stepTemplateSelector: "#tour-guide-step-template",
    widget: tourGuideSetupWidget,
    getElementSelector: function(e) {
        let selector = TourGuideUtils.getSelector(e);
        return selector;
    },
    steps: TOUR_GUIDE_TOURS_STEPS,
});
window.tourGuideStepManager = tourGuideStepManager;

// Notify availability of tourGuideStepManager
document.dispatchEvent(new CustomEvent(TOUR_GUIDE_EVENTS.tourGuideStepManagerReady, {
    bubbles: true,
    detail: {
        tourGuideStepManager
    }
}))

// Track all steps forward and backward click tracks
TourGuideClickSequenceWidget.bootstrap(tourGuideStepManager);

document.addEventListener("DOMContentLoaded", function() {

    if (!document.querySelector(".tour-guide-widget")) return;


    tourGuideSetupWidget.init();
    tourGuideStepManager.init();
    document.querySelector("#tour-guide-step-add-btn-no-element").addEventListener("click", () => {
        tourGuideStepManager.addNewStep("body");
    });

    // Highlight data selector element
    const tourGuideElementHighlighter = new TourGuideElementHighlighter();
    tourGuideElementHighlighter.init();

    // Initate the Emoji picker
    new TourGuideEmojiPicker({
        widgetSelector: ".tour-guide-widget-content",
        emojiTriggerText: "😀",
        emojiTriggerSize: 24,
    });

    // Initiate step preivewer
    new TourGuideStepPreviewPlugin({
        triggerButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.play}"><i class="tour-guide-icon  tour-guide-icon-play-circle"></i></span>`,
        player: window.tourGuideRunner,
    });

    new TourGuideStepDescHtmlEditorQuillPlugin({
        triggerButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.editor}" style="margin-top:10px"><i class="tour-guide-icon tour-guide-icon-code-slash"></i></span>`,
        tourStepButtonWrapperSelector: '.tour-step-desc',
    });

    // Initiate step selector editor plugin
    new TourGuideSelectorEditorPlugin({
        triggerButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.selector}"><i class="tour-guide-icon tour-guide-icon-add-r"></i></span>`,
        deleteButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.delete}"><i class="tour-guide-icon tour-guide-icon-close text-danger"></i></span>`,
        editSelectorActionButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.edit}"><i class="tour-guide-icon tour-guide-icon-pencil"></i></span>`,
        replaceSelectorActionButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.replace}"><i class="tour-guide-icon tour-guide-icon-repeat"></i></span>`
    });

    // Initiate tag plugin
    new TourGuideTagSetupPlugin({
        triggerButtonContent: `<span data-toggle="tooltip" title="${TOUR_GUIDE_TRANSLATIONS_MAP.tags}"><i class="tour-guide-icon tour-guide-icon-brackets"></i></span>`,
    });
});
</script>