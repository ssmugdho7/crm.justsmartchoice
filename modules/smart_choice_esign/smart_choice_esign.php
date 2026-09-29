<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Electronic Signatures
Description: Adds customer initials, inline merge fields, and signature/initial audit support for contracts, estimates, and proposals.
Version: 1.0.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA
*/
define('SCES_MODULE_NAME', 'smart_choice_esign');
define('SCES_VERSION', '1.0.0');
register_language_files(SCES_MODULE_NAME, [SCES_MODULE_NAME]);
register_activation_hook(SCES_MODULE_NAME, 'sces_activate');
hooks()->add_action('app_customers_head', 'sces_customer_assets');
hooks()->add_action('app_admin_head', 'sces_admin_assets');
function sces_activate(){ require_once __DIR__ . '/install.php'; update_option('sces_version', SCES_VERSION); }
function sces_customer_assets(){ echo '<link rel="stylesheet" href="'.module_dir_url(SCES_MODULE_NAME,'assets/css/esign.css?v=100').'">'; }
function sces_admin_assets(){ echo '<link rel="stylesheet" href="'.module_dir_url(SCES_MODULE_NAME,'assets/css/esign.css?v=100').'">'; }
