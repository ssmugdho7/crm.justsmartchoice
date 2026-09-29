<?php

defined('BASEPATH') || exit('No direct script access allowed');

$CI = &get_instance();

if (!option_exists('customtables_enabled')) {
    add_option('customtables_enabled', '1');
}
if (!option_exists('table_custom_style')) {
    add_option('table_custom_style', '[]');
}
if (!option_exists('custom_css_for_table')) {
    add_option('custom_css_for_table', '');
}
if (!option_exists('customtables_module_description')) {
    add_option('customtables_module_description', 'Custom Data Tables allows staff to personalize Perfex CRM list views, choose useful columns, style CRM tables, and improve daily visibility for leads, customers, projects, invoices, tasks, contracts, and items without modifying Perfex core files.');
}

// Keep installation safe: do not copy or overwrite Perfex core files.
