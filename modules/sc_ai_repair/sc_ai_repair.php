<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice AI Repair
Description: Quality ★★★★★. Controlled CRM diagnostics, AI-assisted repair plans, backups, file changes, migrations, asset renaming, reports, and rollback for Perfex CRM.
Version: 1.0.4
Requires at least: 3.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
*/
define('SC_AI_REPAIR_MODULE_NAME', 'sc_ai_repair');
define('SC_AI_REPAIR_VERSION', '1.0.4');
$CI = &get_instance();
hooks()->add_action('admin_init', 'sc_ai_repair_admin_init');
function sc_ai_repair_admin_init() {
    $CI = &get_instance();
    sc_ai_repair_register_permissions();
    if (has_permission('sc_ai_repair', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('sc-ai-repair', [
            'name' => _l('sc_ai_repair'), 'href' => admin_url('sc_ai_repair'),
            'icon' => 'fa fa-wrench', 'position' => 98,
        ]);
    }
}
function sc_ai_repair_register_permissions() {
    register_staff_capabilities('sc_ai_repair', ['capabilities' => [
        'view_own'=>'View Own','view'=>'View (Global)','create'=>'Create','edit'=>'Edit','delete'=>'Delete'
    ]], _l('sc_ai_repair'));
}
register_activation_hook(SC_AI_REPAIR_MODULE_NAME, 'sc_ai_repair_activate');
function sc_ai_repair_activate(){ require_once(__DIR__.'/install.php'); }
register_language_files(SC_AI_REPAIR_MODULE_NAME, [SC_AI_REPAIR_MODULE_NAME]);
