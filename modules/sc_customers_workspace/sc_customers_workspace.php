<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Customers Workspace
Description: Executive emerald customer directory, profiles, contacts and forms.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/

register_language_files('sc_customers_workspace', ['sc_customers_workspace']);
register_activation_hook('sc_customers_workspace', 'sccw_activate');

$licensingHelper = dirname(__DIR__) . '/sc_module_licensing/helpers/sc_module_licensing_helper.php';
if (is_file($licensingHelper)) {
    require_once $licensingHelper;
}

function sccw_activate()
{
    if (!function_exists('scl_licensed') || !scl_licensed('sc_customers_workspace')) {
        show_error(_l('sccw_license_required'), 403);
        exit;
    }
}

require_once __DIR__ . '/includes/customers.php';
