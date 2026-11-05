<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Admin Fixes
Description: Update-safe admin workflow and presentation fixes for the existing CRM theme.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/
define('SCAF_MODULE_NAME', 'sc_admin_fixes');
register_language_files(SCAF_MODULE_NAME, ['sc_admin_fixes', 'customers']);
register_activation_hook(SCAF_MODULE_NAME, 'scaf_activate');
function scaf_activate() { /* No schema or customer data changes are needed. */ }
foreach (['money', 'common', 'customers', 'sales', 'projects', 'appointments', 'saleshub'] as $include) {
    $path = __DIR__ . '/includes/' . $include . '.php';
    if (is_file($path)) { require_once $path; }
}
