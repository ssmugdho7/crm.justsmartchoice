<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro HMVC migration compatibility bridge.
 *
 * Perfex AdminController checks the CRM core migration level on every admin
 * request. In an HMVC module request, MX_Loader resolves config files inside
 * the active module first. Therefore this file MUST exist, but it MUST NOT
 * define a Solar Pro/core migration version of its own.
 *
 * Load the CRM's authoritative migration configuration verbatim so
 * application/config/migration.php remains the only source of truth for the
 * CRM files version. Solar Pro database upgrades are handled separately by
 * App_module_migration from modules/solar_pro/migrations/.
 */
$coreMigrationConfig = APPPATH . 'config/migration.php';

if (!is_file($coreMigrationConfig)) {
    show_error('Solar Pro could not locate the CRM core migration configuration.');
}

require $coreMigrationConfig;
