<?php defined('TOUR_GUIDE_DIR') or exit('No direct script access allowed'); ?>

<div id="tour-setup-widget" class="tour-guide-widget">
    <div class="tour-guide-widget-header tour-guide-flex-row">
        <h4 class="tour-guide-widget-title"><?php echo tourGuideHelper()->translate('tour_guide_widget_title'); ?></h4>
        <div class="tour-guide-widget-controls tour-guide-gap-2">

            <button type="button" title="<?php echo tourGuideHelper()->translate('tour_guide_auto_save_tooltip'); ?>" data-toggle="tooltip" data-placement="left" class="btn btn-xs">
                <input type="checkbox" id="tour-guide-auto-save-toggle" name="tour-guide-auto-save-toggle" checked>
            </button>

            <button type="button" id="tour-guide-minimize-btn" class="tour-guide-widget-btn btn btn-xs" title="<?php echo tourGuideHelper()->translate('tour_guide_minimize_tooltip'); ?>" data-toggle="tooltip" data-placement="left"><i class="tour-guide-icon  tour-guide-icon-minimize"></i></button>
            <button type="button" id="tour-guide-maximize-btn" class="tour-guide-widget-btn hidden btn btn-xs" title="<?php echo tourGuideHelper()->translate('tour_guide_maximize_tooltip'); ?>" data-toggle="tooltip" data-placement="left"><i class="tour-guide-icon  tour-guide-icon-maximize"></i></button>
            <button type="button" id="tour-guide-close-btn" data-confirm="<?= tourGuideHelper()->translate('tour_guide_close_confirm'); ?>" class="tour-guide-widget-btn btn btn-xs tour-remove-btn" title="<?php echo tourGuideHelper()->translate('tour_guide_close_tooltip'); ?>" data-toggle="tooltip" data-placement="left"><i class="tour-guide-icon  tour-guide-icon-close"></i></button>

        </div>
    </div>
    <div class="tour-guide-widget-content">
        <div class="tour-guide-widget-content-main-action tour-guide-flex-row tour-guide-gap-2">
            <div class="tour-guide-tour-select-wrapper">
                <select id="tour-guide-tour-select" class="form-control">
                    <?php foreach ($tour_guides as $tour_guide) : ?>
                        <option value="<?= $tour_guide['id']; ?>" <?php if ($active_tour_guide_id == $tour_guide['id']) echo 'selected'; ?>>
                            <?= $tour_guide['title']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>
            <div class="tour-guide-controls">
                <button id="tour-guide-step-add-btn" class="btn btn-primary"><?php echo tourGuideHelper()->translate('tour_guide_add_new_step'); ?></button>
                <button id="tour-guide-step-stop-btn" class="btn btn-danger hidden"><?php echo tourGuideHelper()->translate('tour_guide_stop_new_step'); ?></button>
            </div>
        </div>




        <!-- Drop Area -->
        <div id="tour-guide-drop-area" class="tour-guide-drop-area hidden">
            <p><?php echo tourGuideHelper()->translate('tour_guide_drag_drop_here'); ?></p>
            <a href="javascript:;" id="tour-guide-step-add-btn-no-element"><?php echo tourGuideHelper()->translate('tour_guide_add_new_detached_step'); ?></a>
        </div>

        <!-- Accordion for managing steps -->
        <div id="tour-guide-steps-accordion" class="tour-guide-accordion">
            <!-- Accordion items will be added dynamically -->
        </div>

        <!-- Save Button -->
        <button id="tour-guide-save-btn" class="btn btn-success btn-block hidden"><?php echo tourGuideHelper()->translate('tour_guide_save_steps'); ?></button>
    </div>
</div>

<template id="tour-guide-step-template">
    <div class="card-header" id="heading-${stepUid}" data-selector="${stepSelectorPath}">
        <div class="tour-guide-flex-row">
            <button class="btn btn-link btn-block tour-guide-flex-1" data-tgb-toggle="collapse" data-tgb-target="#collapse-${stepUid}" aria-expanded="true" aria-controls="collapse-${stepUid}">
                <?php echo tourGuideHelper()->translate('tour_guide_step'); ?>: <span id="tour-step-order-${stepUid}">${stepOrder}</span>
            </button>
            <div class="tour-step-controls">
                <button class="btn btn-xs btn-icon remove-step tour-remove-btn" data-confirm="<?php echo tourGuideHelper()->encode(tourGuideHelper()->translate('tour_guide_confirm_delete')); ?>" data-index="${stepUid}" data-toggle="tooltip" title="<?php echo tourGuideHelper()->translate('tour_guide_remove_step'); ?>">
                    <i class="tour-guide-icon  tour-guide-icon-trash-can"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="collapse-${stepUid}" class="step collapse card-body" aria-labelledby="heading-${stepUid}" data-parent="#tour-guide-steps-accordion" data-selector="${stepSelectorPath}">
        <input type="hidden" value="${stepSelectorPath}" id="tour-step-selector-${stepUid}">
        <div class="form-group tour-step-title">
            <label for="tour-step-title-${stepUid}"><?php echo tourGuideHelper()->translate('tour_guide_step_title'); ?></label>
            <input type="text" class="form-control" id="tour-step-title-${stepUid}" placeholder="<?php echo tourGuideHelper()->translate('tour_guide_step_title_placeholder'); ?>" data-toggle="tooltip" title="<?php echo tourGuideHelper()->translate('tour_guide_step_title'); ?>" value="${stepTitle}">
        </div>
        <div class="form-group tour-step-desc">
            <label for="tour-step-desc-${stepUid}"><?php echo tourGuideHelper()->translate('tour_guide_step_description'); ?></label>
            <textarea class="form-control" id="tour-step-desc-${stepUid}" placeholder="<?php echo tourGuideHelper()->translate('tour_guide_step_description_placeholder'); ?>" data-toggle="tooltip" title="<?php echo tourGuideHelper()->translate('tour_guide_step_description'); ?>">${stepDescription}</textarea>
        </div>
    </div>
</template>

<template id="tour-guide-step-click-tracks-template">
    <div class="click-sequence-widget">
        <hr />
        <div class="click-sequence-header tour-guide-flex-row">
            <span class="tour-guide-sequence-title"><?= tourGuideHelper()->translate('tour_guide_sequence_title'); ?>
                (${stepClickTracksGroup})</span>
            <div>
                <button class="btn btn-xs btn-info tour-guide-start-sequence-btn hidden"><i class="tour-guide-icon  tour-guide-icon-plus"></i>
                    <?= tourGuideHelper()->translate('tour_guide_step_widget_start_sequence'); ?>
                    (${stepClickTracksGroup})</button>
                <button class="btn btn-painted btn-xs text-primary play-sequence-btn hidden" data-toggle="tooltip" title="<?= tourGuideHelper()->translate('tour_guide_step_widget_play_sequence'); ?>">
                    <i class="tour-guide-icon  tour-guide-icon-play-circle"></i>
                </button>
                <button class="btn btn-painted btn-xs text-warning stop-sequence-btn hidden" data-toggle="tooltip" title="<?= tourGuideHelper()->translate('tour_guide_step_widget_stop_sequence'); ?>">
                    <i class="tour-guide-icon  tour-guide-icon-stop"></i>
                </button>
                <button class="btn btn-painted btn-xs tour-remove-btn trash-sequence-btn hidden" data-toggle="tooltip" title="<?= tourGuideHelper()->translate('tour_guide_step_widget_trash_sequence'); ?>">
                    <i class="tour-guide-icon  tour-guide-icon-trash"></i>
                </button>
            </div>
        </div>

        <ol class="click-sequence-list">
            <!-- Dynamic click sequence steps will be appended here -->
        </ol>
    </div>
</template>

<template id="tour-guide-step-click-tracks-row-template">
    <li class="click-sequence-list-item" data-index="${index}" data-selector="${clickTrack.selector}">
        <span>${clickTrack.selector}</span>
        <span>
            <button data-index="${index}" class="btn btn-painted btn-xs tour-remove-btn"><i class="tour-guide-icon  tour-guide-icon-close text-danger"></i></button>
        </span>
    </li>
</template>

<?php require __DIR__ . '/scripts/setup.php'; ?>