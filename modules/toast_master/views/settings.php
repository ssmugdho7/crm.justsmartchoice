<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content toast-master-admin">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="toast-master-page-head">
                            <div>
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('toast_master_setting'); ?></h4>
                                <p class="text-muted no-margin"><?php echo _l('toast_master_setting_description'); ?></p>
                            </div>
                            <div>
                                <a href="<?php echo admin_url('toast_master/history'); ?>" class="btn btn-default btn-sm"><i class="fa-regular fa-clock"></i> <?php echo _l('toast_master_history'); ?></a>
                            </div>
                        </div>

                        <?php echo form_open($this->uri->uri_string()); ?>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="toast-master-card">
                                    <h5><?php echo _l('toast_master_general'); ?></h5>
                                    <?php render_yes_no_option('toast_master_enable', 'toast_master_enable'); ?>
                                    <?php echo render_input('settings[toast_master_duration]', 'toast_master_duration', get_option('toast_master_duration') ?: 4500, 'number', ['min' => 1200, 'step' => 100]); ?>
                                    <?php render_yes_no_option('toast_master_pause_on_hover', 'toast_master_pause_on_hover'); ?>
                                    <?php render_yes_no_option('toast_master_progress_bar', 'toast_master_progress_bar'); ?>
                                    <?php render_yes_no_option('toast_master_history_enable', 'toast_master_history_enable'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="toast-master-card">
                                    <h5><?php echo _l('toast_master_position_sound'); ?></h5>
                                    <div class="form-group">
                                        <label for="toaster_position"><?php echo _l('toaster_position'); ?></label>
                                        <select name="settings[toaster_position]" id="toaster_position" class="form-control selectpicker">
                                            <?php foreach ($positions as $value => $label) { ?>
                                                <option value="<?php echo html_escape($value); ?>" <?php echo get_option('toaster_position') == $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php render_yes_no_option('toast_master_sound_enable', 'toast_master_sound_enable'); ?>
                                    <div class="form-group">
                                        <label for="toast_master_sound_name"><?php echo _l('toast_master_sound_name'); ?></label>
                                        <select name="settings[toast_master_sound_name]" id="toast_master_sound_name" class="form-control selectpicker">
                                            <?php foreach ($sounds as $value => $label) { ?>
                                                <option value="<?php echo html_escape($value); ?>" <?php echo get_option('toast_master_sound_name') == $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php echo render_input('settings[toast_master_volume]', 'toast_master_volume', get_option('toast_master_volume') ?: 55, 'number', ['min' => 0, 'max' => 100]); ?>
                                    <div class="form-group">
                                        <label for="toast_master_animation"><?php echo _l('toast_master_animation'); ?></label>
                                        <select name="settings[toast_master_animation]" id="toast_master_animation" class="form-control selectpicker">
                                            <?php foreach ($animations as $value => $label) { ?>
                                                <option value="<?php echo html_escape($value); ?>" <?php echo get_option('toast_master_animation') == $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="toast-master-card toast-master-preview-card">
                                    <h5><?php echo _l('toast_master_preview'); ?></h5>
                                    <button type="button" class="btn btn-success btn-sm" onclick="smartChoiceToastPreview('success','<?php echo html_escape(_l('toast_master_preview_success')); ?>')"><?php echo _l('toast_master_success'); ?></button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="smartChoiceToastPreview('info','<?php echo html_escape(_l('toast_master_preview_info')); ?>')"><?php echo _l('toast_master_info'); ?></button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="smartChoiceToastPreview('warning','<?php echo html_escape(_l('toast_master_preview_warning')); ?>')"><?php echo _l('toast_master_warning'); ?></button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="smartChoiceToastPreview('error','<?php echo html_escape(_l('toast_master_preview_error')); ?>')"><?php echo _l('toast_master_error'); ?></button>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5><?php echo _l('toaster_style'); ?></h5>
                        <div class="toast-master-style-grid">
                            <?php foreach ($styles as $value => $label) { ?>
                                <label class="toast-master-style-option toast-style-preview-<?php echo (int)$value; ?> <?php echo get_option('toaster_style') == $value ? 'active' : ''; ?>">
                                    <input type="radio" name="settings[toaster_style]" value="<?php echo (int)$value; ?>" <?php echo get_option('toaster_style') == $value ? 'checked' : ''; ?>>
                                    <span class="toast-master-style-title"><?php echo html_escape($label); ?></span>
                                    <span class="toast-master-style-sample"><?php echo _l('toast_master_style_sample'); ?></span>
                                </label>
                            <?php } ?>
                        </div>

                        <hr>
                        <h5><?php echo _l('toast_master_channels'); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover toast-master-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('toast_master_notification_source'); ?></th>
                                        <th class="text-center"><?php echo _l('enabled'); ?></th>
                                        <th><?php echo _l('toast_master_controlled_message'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($channels as $option => $label) { ?>
                                        <tr>
                                            <td><?php echo html_escape($label); ?></td>
                                            <td class="text-center">
                                                <div class="onoffswitch">
                                                    <input type="checkbox" id="<?php echo html_escape($option); ?>" class="onoffswitch-checkbox" name="settings[<?php echo html_escape($option); ?>]" <?php echo get_option($option) == '1' ? 'checked' : ''; ?>>
                                                    <label class="onoffswitch-label" for="<?php echo html_escape($option); ?>"></label>
                                                </div>
                                            </td>
                                            <td><?php echo _l($option . '_description'); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mtop20">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fa-regular fa-floppy-disk"></i> <?php echo _l('save'); ?></button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function($){
    'use strict';
    function currentToastSettings(){
        var style = $('input[name="settings[toaster_style]"]:checked').val() || '1';
        return {
            style: String(style),
            position: $('#toaster_position').val() || 'top-right',
            soundEnabled: $('input[name="settings[toast_master_sound_enable]"]').is(':checked'),
            soundName: $('#toast_master_sound_name').val() || 'soft_click',
            volume: parseInt($('input[name="settings[toast_master_volume]"]').val() || 55, 10),
            duration: parseInt($('input[name="settings[toast_master_duration]"]').val() || 4500, 10),
            pauseOnHover: $('input[name="settings[toast_master_pause_on_hover]"]').is(':checked'),
            progressBar: $('input[name="settings[toast_master_progress_bar]"]').is(':checked'),
            animation: $('#toast_master_animation').val() || 'fade'
        };
    }
    window.smartChoiceToastPreview = function(type, message){
        if (typeof window.smartChoiceToastConfigure === 'function') {
            window.smartChoiceToastConfigure(currentToastSettings());
        }
        if (typeof window.smartChoiceNotify === 'function') {
            window.smartChoiceNotify(type, message, null, currentToastSettings());
        }
    };
    $(document).on('change input', '#toaster_position,#toast_master_sound_name,#toast_master_animation,input[name="settings[toaster_style]"],input[name="settings[toast_master_volume]"],input[name="settings[toast_master_duration]"],input[name="settings[toast_master_sound_enable]"],input[name="settings[toast_master_pause_on_hover]"],input[name="settings[toast_master_progress_bar]"]', function(){
        if (typeof window.smartChoiceToastConfigure === 'function') {
            window.smartChoiceToastConfigure(currentToastSettings());
        }
        $('.toast-master-style-option').removeClass('active');
        $('input[name="settings[toaster_style]"]:checked').closest('.toast-master-style-option').addClass('active');
    });
})(jQuery);
</script>
<?php init_tail(); ?>
