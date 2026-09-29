<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('smart_choice_core_upgrade/save_settings')); ?>
<div class="panel_s"><div class="panel-body smart-choice-settings-panel">
<h4>Smart Choice Admin Settings</h4>
<p>Centralized settings for the Smart Choice CRM enterprise layer.</p>
<?php echo render_input('smart_choice_core_theme_name', 'Theme Name', get_option('smart_choice_core_theme_name') ?: 'Smart Choice'); ?>
<?php echo render_input('smart_choice_windows_timezone_label', 'Windows Time Zone Label', get_option('smart_choice_windows_timezone_label') ?: 'Eastern Time (US & Canada)'); ?>
<?php echo render_input('smart_choice_default_state', 'Default State', get_option('smart_choice_default_state') ?: 'FL'); ?>
<?php echo render_input('smart_choice_default_tax_mode', 'Default Tax Setup', get_option('smart_choice_default_tax_mode') ?: 'Florida Sales Tax'); ?>
<?php render_yes_no_option('smart_choice_core_theme_enabled', 'Enable Smart Choice Theme'); ?>
<?php render_yes_no_option('smart_choice_core_main_menu_search', 'Enable Main Menu Search'); ?>
<?php render_yes_no_option('smart_choice_core_setup_menu_search', 'Enable Setup Menu Search'); ?>
<?php render_yes_no_option('smart_choice_core_table_tools', 'Enable Table Tools'); ?>
<?php render_yes_no_option('smart_choice_quickbooks_desktop_enabled', 'Enable QuickBooks Desktop IIF Workflow'); ?>
<?php render_yes_no_option('smart_choice_reports_enabled', 'Enable Smart Choice Reports'); ?>
<button type="submit" class="btn btn-primary">Save Smart Choice Settings</button>
</div></div>
<?php echo form_close(); ?>
