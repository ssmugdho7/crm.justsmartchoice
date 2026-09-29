<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<div class="tour-guide-translations">
    <div class="tour-guide-flex">
        <div class="language-selector">
            <label for="language-select"><?= tourGuideHelper()->translate('tour_guide_select_language'); ?>:</label>
            <div class="row">
                <div class="col-md-6">
                    <select id="tour-guide-language-select" class="form-control">
                        <?php foreach ($supported_languages as $language) : ?>
                        <option value="<?= tourGuideHelper()->encode($language[$lang_compare_index]); ?>"
                            <?= strtolower($language[$lang_compare_index]) == $default_language ? 'selected' : ''; ?>>
                            <?= tourGuideHelper()->encode($language['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary auto-translate-btn"
                        data-languages="all"><?= tourGuideHelper()->translate('tour_guide_auto_translate_all'); ?></button>

                    <button type="button" class="btn btn-primary auto-translate-btn"
                        data-languages="current"><?= tourGuideHelper()->translate('tour_guide_auto_translate_selected'); ?></button>
                    <button type="button" class="btn btn-danger stop-auto-translate-btn hidden"
                        data-languages="current"><?= tourGuideHelper()->translate('tour_guide_stop_auto_translate'); ?></button>
                </div>
            </div>
        </div>

    </div>
    <div class="translation-progress" style="display:none;">
        <div>
            <hr />
        </div>
        <p id="translation-status"></p>
        <progress id="progress-bar" value="0" max="100"></progress>
    </div>

    <div>
        <hr />
    </div>

    <?php foreach ($supported_languages as $language) : $language_id = strtolower($language[$lang_compare_index]); ?>
    <div class="steps-container <?= $language_id == $default_language ? '' : 'hidden'; ?>"
        data-steps-lang="<?= $language[$lang_compare_index]; ?>">
        <!-- Step translations will be here as in previous example -->
        <?php
            $index = 0;
            foreach ($steps as $step) :
                $stepUid =  $step->uid;
                $stepTitle = $steps_translations[$language_id][$stepUid]['title'] ?? $step->title;
                $stepDescription = $steps_translations[$language_id][$stepUid]['description'] ?? $step->description;
                $index++;
            ?>
        <div class="tour-guide-step-translation" data-step-uid="<?= $stepUid; ?>">
            <div class="h4 tour-guide-step-translation-step-divider">
                <span><?php echo tourGuideHelper()->translate('tour_guide_step'); ?></span> <?= $index; ?>.
            </div>
            <div class="form-group">
                <label
                    for="tour-step-title-<?= $stepUid; ?>"><?php echo tourGuideHelper()->translate('tour_guide_step_title'); ?></label>
                <input type="text" name="steps_translations[<?= $language_id; ?>][<?= $stepUid; ?>][title]"
                    class="form-control" id="tour-step-title-<?= $stepUid; ?>"
                    placeholder="<?php echo tourGuideHelper()->translate('tour_guide_step_title_placeholder'); ?>"
                    data-toggle="tooltip" title="<?php echo tourGuideHelper()->translate('tour_guide_step_title'); ?>"
                    value="<?= $stepTitle; ?>">
            </div>
            <div class="form-group">
                <label
                    for="tour-step-desc-<?= $stepUid; ?>"><?php echo tourGuideHelper()->translate('tour_guide_step_description'); ?></label>
                <textarea name="steps_translations[<?= $language_id; ?>][<?= $stepUid; ?>][description]"
                    class="form-control" id="tour-step-desc-<?= $stepUid; ?>"
                    placeholder="<?php echo tourGuideHelper()->translate('tour_guide_step_description_placeholder'); ?>"
                    data-toggle="tooltip"
                    title="<?php echo tourGuideHelper()->translate('tour_guide_step_description'); ?>"><?= $stepDescription; ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/scripts/translate.php'; ?>