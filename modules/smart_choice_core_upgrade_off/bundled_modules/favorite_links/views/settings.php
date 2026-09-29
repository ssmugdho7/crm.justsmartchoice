<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s favorite-links-panel">
    <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('favorite_links_settings'); ?></h4>
        <hr class="hr-panel-heading" />

        <p class="text-muted">
            Favorite Links adds a quick-access star beside the CRM search bar for daily Smart Choice Contractors USA shortcuts.
        </p>

        <?php echo render_yes_no_option('favorite_links_enabled', 'favorite_links_enabled'); ?>
        <?php echo render_yes_no_option('favorite_links_show_star_near_search', 'favorite_links_show_star_near_search'); ?>
        <?php echo render_yes_no_option('favorite_links_open_new_tab', 'favorite_links_open_new_tab'); ?>
        <?php echo render_yes_no_option('favorite_links_enable_hotkeys', 'favorite_links_enable_hotkeys'); ?>

        <div class="form-group">
            <label for="favorite_links_default_target" class="control-label"><?php echo _l('favorite_links_default_target'); ?></label>
            <select name="settings[favorite_links_default_target]" id="favorite_links_default_target" class="selectpicker" data-width="100%">
                <?php foreach (['_self' => 'Same Window', '_blank' => 'New Tab', '_parent' => 'Parent Frame', '_top' => 'Top Frame'] as $value => $label) { ?>
                    <option value="<?php echo html_escape($value); ?>" <?php echo get_option('favorite_links_default_target') === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="alert alert-info">
            <strong>Correct internal link format:</strong><br>
            <code>admin/modules</code><br>
            <code>admin/invoices</code><br>
            <code>admin/estimates</code>
        </div>
    </div>
</div>
