<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$options = [
    'smart_choice_core_upgrade_enabled' => '1',
    'smart_choice_core_upgrade_version' => '3.1.2',
    'smart_choice_enterprise_current_version' => '3.1.2',
    'smart_choice_enterprise_latest_version' => '3.1.2',
    'smart_choice_enterprise_update_channel' => 'stable',
    'smart_choice_enterprise_update_url' => '',
    'smart_choice_enterprise_update_package_path' => 'modules/smart_choice_core_upgrade/uploads/packages/',
    'smart_choice_enterprise_update_last_check' => date('Y-m-d H:i:s'),
    'smart_choice_enterprise_update_status' => 'current',
    'smart_choice_enterprise_allow_zip_upload' => '1',
    'smart_choice_enterprise_require_backup_before_update' => '1',
    'smart_choice_core_theme_name' => 'Smart Choice',
    'smart_choice_core_theme_enabled' => '1',
    'smart_choice_core_main_menu_search' => '1',
    'smart_choice_core_setup_menu_search' => '1',
    'smart_choice_core_table_tools' => '1',
    'smart_choice_core_pdf_fullscreen' => '1',
    'smart_choice_core_media_viewer_upgrade' => '1',
    'smart_choice_core_creator' => 'Harold Cabrera',
    'smart_choice_core_quality' => '★★★★★',
    'smart_choice_windows_timezone_label' => 'Eastern Time (US & Canada)',
    'smart_choice_php_timezone' => 'America/New_York',
    'smart_choice_allowed_languages' => 'english,spanish',
    'smart_choice_default_state' => 'FL',
    'smart_choice_default_tax_mode' => 'Florida Sales Tax',
    'smart_choice_quickbooks_desktop_enabled' => '1',
    'smart_choice_reports_enabled' => '1',
];
foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    } else {
        update_option($name, $value);
    }
}

// Native Perfex localization options. Perfex stores the PHP timezone ID; this module shows the Windows label.
update_option('default_timezone', 'America/New_York');
update_option('dateformat', 'm/d/Y');
update_option('time_format', '12');
update_option('active_language', 'english');
update_option('company_vat', get_option('company_vat') ?: 'EIN');

// Ensure a Florida default tax exists without overwriting existing tax setup.
if ($CI->db->table_exists(db_prefix() . 'taxes')) {
    $exists = $CI->db->where('name', 'Florida Sales Tax')->get(db_prefix() . 'taxes')->row();
    if (!$exists) {
        $CI->db->insert(db_prefix() . 'taxes', [
            'name' => 'Florida Sales Tax',
            'taxrate' => 6.00,
        ]);
    }
}

if (!$CI->db->table_exists(db_prefix() . 'smart_choice_core_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_core_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `level` VARCHAR(30) NOT NULL DEFAULT "info",
        `event_type` VARCHAR(100) NOT NULL,
        `message` TEXT NULL,
        `data` LONGTEXT NULL,
        `staff_id` INT(11) NULL,
        `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `event_type` (`event_type`),
        KEY `level` (`level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_choice_upgrade_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_upgrade_history` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `from_version` VARCHAR(50) NULL,
        `to_version` VARCHAR(50) NULL,
        `package_name` VARCHAR(255) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT "staged",
        `message` TEXT NULL,
        `staff_id` INT(11) NULL,
        `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `datefinished` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_choice_reports')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_reports` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `report_key` VARCHAR(100) NOT NULL,
        `report_name` VARCHAR(191) NOT NULL,
        `report_group` VARCHAR(100) NOT NULL DEFAULT "General",
        `description` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `report_key` (`report_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$reports = [
    ['sales_summary', 'Sales Summary', 'Sales', 'Invoices, estimates, proposals, payments, and credit notes.'],
    ['income_per_job', 'Income Per Job', 'Projects', 'Income by project and customer.'],
    ['expenses_per_job', 'Expenses Per Job', 'Projects', 'Expenses and accounts payable by project.'],
    ['profit_loss_per_job', 'Profit And Loss Per Job', 'Projects', 'Income minus expenses per project.'],
    ['quickbooks_desktop_export', 'QuickBooks Desktop Export', 'QuickBooks', 'IIF export/import workflow for QuickBooks Desktop Enterprise.'],
    ['purchase_orders', 'Purchase Orders', 'Purchasing', 'Purchase orders from Purchasing Hub when installed.'],
    ['accounts_payable', 'Accounts Payable', 'Accounting', 'Bills and payables from Accounting Hub.'],
];
if ($CI->db->table_exists(db_prefix() . 'smart_choice_reports')) {
    foreach ($reports as $report) {
        $exists = $CI->db->where('report_key', $report[0])->get(db_prefix() . 'smart_choice_reports')->row();
        $row = ['report_key' => $report[0], 'report_name' => $report[1], 'report_group' => $report[2], 'description' => $report[3], 'is_active' => 1];
        if ($exists) {
            $CI->db->where('id', $exists->id)->update(db_prefix() . 'smart_choice_reports', $row);
        } else {
            $CI->db->insert(db_prefix() . 'smart_choice_reports', $row);
        }
    }
}

$uploadDir = FCPATH . 'modules/smart_choice_core_upgrade/uploads/packages/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}
if (is_dir($uploadDir) && !file_exists($uploadDir . 'index.html')) {
    @file_put_contents($uploadDir . 'index.html', '');
}

// Copy bundled modules into the CRM modules folder for a test/staging install. Existing folders are not deleted.
$bundleDir = __DIR__ . '/bundled_modules/';
if (is_dir($bundleDir)) {
    $iterator = new DirectoryIterator($bundleDir);
    foreach ($iterator as $item) {
        if ($item->isDot() || !$item->isDir()) {
            continue;
        }
        $source = $item->getPathname();
        $target = FCPATH . 'modules/' . $item->getFilename();
        smart_choice_core_recursive_copy($source, $target);
    }
}

$CI->db->insert(db_prefix() . 'smart_choice_upgrade_history', [
    'from_version' => get_option('smart_choice_enterprise_current_version'),
    'to_version' => '3.1.2',
    'package_name' => 'Smart Choice Core Module Activation',
    'status' => 'installed',
    'message' => 'Smart Choice Core defaults applied. Embedded module folders copied when available.',
    'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
    'datecreated' => date('Y-m-d H:i:s'),
    'datefinished' => date('Y-m-d H:i:s'),
]);

function smart_choice_core_recursive_copy($src, $dst)
{
    if (!is_dir($src)) {
        return false;
    }
    if (!is_dir($dst)) {
        @mkdir($dst, 0755, true);
    }
    $dir = opendir($src);
    if (!$dir) {
        return false;
    }
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $source = $src . DIRECTORY_SEPARATOR . $file;
        $target = $dst . DIRECTORY_SEPARATOR . $file;
        if (is_dir($source)) {
            smart_choice_core_recursive_copy($source, $target);
        } else {
            @copy($source, $target);
        }
    }
    closedir($dir);
    return true;
}
