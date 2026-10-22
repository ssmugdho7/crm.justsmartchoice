<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Projects Workspace
Description: Emerald project portfolio, progress, task punch lists and native project workspace.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/

register_language_files('sc_projects_workspace', ['sc_projects_workspace']);
register_activation_hook('sc_projects_workspace', 'scpr_activate');
// Use the existing owner-controlled licensing system; never block native Projects.
$licensingHelper = dirname(__DIR__) . '/sc_module_licensing/helpers/sc_module_licensing_helper.php';
if (is_file($licensingHelper)) {
    require_once $licensingHelper;
}
function scpr_activate()
{
    if (!function_exists('scl_licensed') || !scl_licensed('sc_projects_workspace')) {
        show_error(_l('scpr_license_required'), 403);
        exit;
    }
}
// Reuses Perfex's registered Projects capabilities and native record/membership checks.
require_once __DIR__ . '/includes/projects.php';
