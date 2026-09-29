<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<script>
const tourGuideTranslatorConfig = {
    selector: '#tour-guide-language-select',
    autoTranslateBtn: '.auto-translate-btn',
    stopAutoTranslateBtn: '.stop-auto-translate-btn',
    progressBarSelector: '#progress-bar',
    statusSelector: '#translation-status',
    progressContainerSelector: '.translation-progress',
    defaultLanguage: '<?= $default_language; ?>',
    translationStatusTexts: {
        allDone: "<?= tourGuideHelper()->translate('tour_guide_translator_all_done'); ?>",
        done: "<?= tourGuideHelper()->translate('tour_guide_translator_done'); ?>",
        error: "<?= tourGuideHelper()->translate('tour_guide_translator_error'); ?>",
        translating: "<?= tourGuideHelper()->translate('tour_guide_translator_translating'); ?>"
    }
};
document.addEventListener("DOMContentLoaded", () => {
    const tourGuideTranslator = new TourGuideTranslator(tourGuideTranslatorConfig);
    window.tourGuideTranslator = tourGuideTranslator;
});
</script>