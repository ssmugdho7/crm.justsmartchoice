<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Module Guard
Description: Global module-side guard for Smart Choice CRM modules. Normalizes tables, buttons, dropdowns, notifications, assets, same-tab admin links, and safe browser API behavior without changing CRM core files.
Version: 1.0.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

hooks()->add_action('app_admin_head', 'smart_choice_module_guard_head');
hooks()->add_action('app_admin_footer', 'smart_choice_module_guard_footer');
hooks()->add_action('app_customers_head', 'smart_choice_module_guard_head');
hooks()->add_action('app_customers_footer', 'smart_choice_module_guard_footer');

function smart_choice_module_guard_head() {
    echo '<style id="smart-choice-module-guard-css">:root{--sc-green:#169179;--sc-green-dark:#0e6f5b;--sc-black:#111827;--sc-gray:#4b5563;--sc-light:#f8fafc;--sc-border:#d1d5db;--sc-muted:#eef7f4;--sc-orange:#f97316;--sc-blue:#2563eb}.dataTables_wrapper,.table-responsive,.panel-table-full{max-width:100%!important;overflow-x:hidden!important}table.dataTable,table.table{width:100%!important;max-width:100%!important;font-size:12px!important}table.dataTable th,table.dataTable td,table.table th,table.table td{vertical-align:middle!important;padding:6px 8px!important;line-height:1.25!important}.dt-buttons .btn,.btn.btn-default-dt-options,.btn{border-radius:6px!important;font-size:12px!important;padding:5px 9px!important;line-height:1.2!important;background-image:none!important;box-shadow:none!important}.btn-info,.btn-primary,.btn-success{background:#169179!important;border-color:#0e6f5b!important;color:#fff!important}.label,.badge{background-image:none!important;border-radius:999px!important;padding:4px 8px!important;font-size:11px!important}.dropdown-menu{max-height:70vh!important;overflow-y:auto!important;overflow-x:hidden!important}.btn-group>.btn{margin-right:4px!important}</style>';
}
function smart_choice_module_guard_footer() {
    echo '<script id="smart-choice-module-guard-js">(function(){function n(){var $=window.jQuery||window.$;if(!$)return;$(".table-responsive,.dataTables_wrapper,.panel-table-full").css({"max-width":"100%","overflow-x":"hidden"});$("table.dataTable,table.table").css({"width":"100%","max-width":"100%"});$("a[target=\"_blank\"][href*=\"/admin/\"]").removeAttr("target");$(".btn").each(function(){this.style.backgroundImage="none"});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",n)}else{n()}setTimeout(n,800);setTimeout(n,2000);})();</script>';
}
