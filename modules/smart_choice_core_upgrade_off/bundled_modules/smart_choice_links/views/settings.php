<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s">
    <div class="panel-body">
        <h4 class="tw-font-semibold tw-mb-4"><?php echo _l('smart_choice_links_settings'); ?></h4>
        <p class="text-muted">
            Configure the Smart Choice Links quick-access dropdown shown near the CRM search bar.
        </p>

        <hr>

        <?php render_yes_no_option('smart_choice_links_enabled', 'Enable Smart Choice Links'); ?>
        <?php render_yes_no_option('smart_choice_links_show_topbar', 'Show star button near top search bar'); ?>
        <?php render_yes_no_option('smart_choice_links_open_new_tab', 'Open links in new tab by default'); ?>
        <?php render_yes_no_option('smart_choice_links_fix_bad_urls', 'Automatically fix common bad URLs'); ?>

        <hr>

        <?php echo render_input('settings[smart_choice_links_title]', 'Dropdown Title', get_option('smart_choice_links_title')); ?>
        <?php echo render_input('settings[smart_choice_links_button_label]', 'Button Label', get_option('smart_choice_links_button_label')); ?>
        <?php echo render_textarea('settings[smart_choice_links_footer_note]', 'Footer Note', get_option('smart_choice_links_footer_note')); ?>

        <div class="alert alert-info">
            <strong>Manage Links:</strong>
            <a href="<?php echo admin_url('smart_choice_links'); ?>">Open Smart Choice Links Manager</a>
        </div>
    </div>
</div>
