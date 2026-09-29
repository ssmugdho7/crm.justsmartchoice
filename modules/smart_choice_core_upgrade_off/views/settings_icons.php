<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('smart_choice_core_upgrade/save_settings')); ?>
<div class="panel_s"><div class="panel-body smart-choice-settings-panel">
<h4><i class="fa fa-icons"></i> Smart Choice Menu Icons</h4>
<p>Control the default icon style used for main menu items, submenus, and setup menu sections. Existing module-specific icons remain active unless a module does not provide one.</p>
<?php echo render_input('smart_choice_default_main_menu_icon', 'Default Main Menu Icon', get_option('smart_choice_default_main_menu_icon') ?: 'fa fa-layer-group'); ?>
<?php echo render_input('smart_choice_default_sub_menu_icon', 'Default Submenu Icon', get_option('smart_choice_default_sub_menu_icon') ?: 'fa fa-circle-dot'); ?>
<?php echo render_input('smart_choice_default_setup_menu_icon', 'Default Setup Menu Icon', get_option('smart_choice_default_setup_menu_icon') ?: 'fa fa-sliders'); ?>
<?php render_yes_no_option('smart_choice_core_dedupe_selects', 'Prevent Double Dropdown Scrollbars'); ?>
<?php render_yes_no_option('smart_choice_core_toast_style', 'Enable Smart Choice Rounded Alerts'); ?>
<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Icon Settings</button>
</div></div>
<?php echo form_close(); ?>
