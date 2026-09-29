<?php

defined('BASEPATH') or exit('No direct script access allowed');

function solar_pro_install_schema($CI)
{
    $charset = $CI->db->char_set ?: 'utf8';
    $collat  = $CI->db->dbcollat ? ' COLLATE ' . $CI->db->dbcollat : '';
    $engine  = ' ENGINE=InnoDB DEFAULT CHARSET=' . $charset . $collat;

    $queries = [];

    $queries[db_prefix() . 'solar_utilities'] = "CREATE TABLE `" . db_prefix() . "solar_utilities` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(191) NOT NULL,
      `short_name` varchar(80) DEFAULT NULL,
      `service_area` varchar(191) DEFAULT NULL,
      `website` varchar(191) DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `active` (`active`)
    )" . $engine;

    $queries[db_prefix() . 'solar_utility_rates'] = "CREATE TABLE `" . db_prefix() . "solar_utility_rates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `utility_id` int(11) NOT NULL,
      `rate_name` varchar(191) NOT NULL,
      `effective_date` date DEFAULT NULL,
      `end_date` date DEFAULT NULL,
      `customer_charge` decimal(15,4) NOT NULL DEFAULT 0,
      `energy_rate` decimal(15,6) NOT NULL DEFAULT 0,
      `fuel_rate` decimal(15,6) NOT NULL DEFAULT 0,
      `storm_charge` decimal(15,4) NOT NULL DEFAULT 0,
      `other_monthly_charge` decimal(15,4) NOT NULL DEFAULT 0,
      `gross_receipts_pct` decimal(8,4) NOT NULL DEFAULT 0,
      `utility_tax_pct` decimal(8,4) NOT NULL DEFAULT 0,
      `sales_tax_pct` decimal(8,4) NOT NULL DEFAULT 0,
      `minimum_bill` decimal(15,4) NOT NULL DEFAULT 0,
      `export_credit_rate` decimal(15,6) NOT NULL DEFAULT 0,
      `net_metering` tinyint(1) NOT NULL DEFAULT 1,
      `notes` text DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `utility_id` (`utility_id`),
      KEY `effective_date` (`effective_date`)
    )" . $engine;

    $queries[db_prefix() . 'solar_equipment'] = "CREATE TABLE `" . db_prefix() . "solar_equipment` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` varchar(30) NOT NULL,
      `manufacturer` varchar(120) NOT NULL,
      `model` varchar(191) NOT NULL,
      `watts` decimal(10,2) DEFAULT NULL,
      `ac_watts` decimal(10,2) DEFAULT NULL,
      `efficiency_pct` decimal(8,4) DEFAULT NULL,
      `width_mm` decimal(10,2) DEFAULT NULL,
      `height_mm` decimal(10,2) DEFAULT NULL,
      `warranty_years` int(11) DEFAULT NULL,
      `cost` decimal(15,2) NOT NULL DEFAULT 0,
      `sale_price` decimal(15,2) NOT NULL DEFAULT 0,
      `specs_json` longtext DEFAULT NULL,
      `spec_file` varchar(255) DEFAULT NULL,
      `spec_original_name` varchar(255) DEFAULT NULL,
      `spec_mime` varchar(120) DEFAULT NULL,
      `spec_preview_json` longtext DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `type_active` (`type`,`active`)
    )" . $engine;

    $queries[db_prefix() . 'solar_analyses'] = "CREATE TABLE `" . db_prefix() . "solar_analyses` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `public_token` varchar(64) NOT NULL,
      `status` varchar(30) NOT NULL DEFAULT 'draft',
      `first_name` varchar(100) DEFAULT NULL,
      `last_name` varchar(100) DEFAULT NULL,
      `email` varchar(191) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `address` varchar(255) NOT NULL,
      `city` varchar(100) DEFAULT NULL,
      `state` varchar(60) DEFAULT 'FL',
      `zip` varchar(20) DEFAULT NULL,
      `latitude` decimal(12,8) DEFAULT NULL,
      `longitude` decimal(12,8) DEFAULT NULL,
      `utility_id` int(11) DEFAULT NULL,
      `utility_rate_id` int(11) DEFAULT NULL,
      `annual_consumption_kwh` decimal(15,2) NOT NULL DEFAULT 0,
      `average_monthly_bill` decimal(15,2) NOT NULL DEFAULT 0,
      `panel_equipment_id` int(11) DEFAULT NULL,
      `inverter_equipment_id` int(11) DEFAULT NULL,
      `panel_watts` decimal(10,2) NOT NULL DEFAULT 440,
      `panel_count` int(11) NOT NULL DEFAULT 0,
      `system_kw` decimal(12,3) NOT NULL DEFAULT 0,
      `annual_production_kwh` decimal(15,2) NOT NULL DEFAULT 0,
      `solar_offset_pct` decimal(10,2) NOT NULL DEFAULT 0,
      `system_price` decimal(15,2) NOT NULL DEFAULT 0,
      `year1_savings` decimal(15,2) NOT NULL DEFAULT 0,
      `payback_years` decimal(10,2) DEFAULT NULL,
      `lifetime_savings` decimal(15,2) NOT NULL DEFAULT 0,
      `analysis_source` varchar(30) NOT NULL DEFAULT 'fallback',
      `imagery_quality` varchar(20) DEFAULT NULL,
      `google_building_name` varchar(255) DEFAULT NULL,
      `google_response_json` longtext DEFAULT NULL,
      `assumptions_json` longtext DEFAULT NULL,
      `lead_id` int(11) DEFAULT NULL,
      `client_id` int(11) DEFAULT NULL,
      `proposal_id` int(11) DEFAULT NULL,
      `project_id` int(11) DEFAULT NULL,
      `created_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `public_token` (`public_token`),
      KEY `lead_id` (`lead_id`),
      KEY `client_id` (`client_id`),
      KEY `created_by` (`created_by`),
      KEY `status` (`status`)
    )" . $engine;

    $queries[db_prefix() . 'solar_consumption'] = "CREATE TABLE `" . db_prefix() . "solar_consumption` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) NOT NULL,
      `month_number` tinyint(2) NOT NULL,
      `kwh` decimal(15,2) NOT NULL DEFAULT 0,
      `bill_amount` decimal(15,2) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `analysis_month` (`analysis_id`,`month_number`)
    )" . $engine;

    $queries[db_prefix() . 'solar_production_monthly'] = "CREATE TABLE `" . db_prefix() . "solar_production_monthly` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) NOT NULL,
      `month_number` tinyint(2) NOT NULL,
      `production_kwh` decimal(15,2) NOT NULL DEFAULT 0,
      `consumption_kwh` decimal(15,2) NOT NULL DEFAULT 0,
      `utility_cost_without_solar` decimal(15,2) NOT NULL DEFAULT 0,
      `utility_cost_with_solar` decimal(15,2) NOT NULL DEFAULT 0,
      `savings` decimal(15,2) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE KEY `analysis_month` (`analysis_id`,`month_number`)
    )" . $engine;

    $queries[db_prefix() . 'solar_contract_templates'] = "CREATE TABLE `" . db_prefix() . "solar_contract_templates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(191) NOT NULL,
      `content` longtext NOT NULL,
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `created_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    )" . $engine;

    $queries[db_prefix() . 'solar_contracts'] = "CREATE TABLE `" . db_prefix() . "solar_contracts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) NOT NULL,
      `template_id` int(11) DEFAULT NULL,
      `title` varchar(191) NOT NULL,
      `content` longtext NOT NULL,
      `status` varchar(30) NOT NULL DEFAULT 'draft',
      `version_no` int(11) NOT NULL DEFAULT 1,
      `public_token` varchar(64) NOT NULL,
      `signed_name` varchar(191) DEFAULT NULL,
      `signed_email` varchar(191) DEFAULT NULL,
      `signature_data` longtext DEFAULT NULL,
      `signature_ip` varchar(64) DEFAULT NULL,
      `signature_user_agent` varchar(255) DEFAULT NULL,
      `signed_at` datetime DEFAULT NULL,
      `document_hash` varchar(64) DEFAULT NULL,
      `created_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `public_token` (`public_token`),
      KEY `analysis_id` (`analysis_id`),
      KEY `status` (`status`)
    )" . $engine;

    $queries[db_prefix() . 'solar_contract_initials'] = "CREATE TABLE `" . db_prefix() . "solar_contract_initials` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `contract_id` int(11) NOT NULL,
      `field_key` varchar(100) NOT NULL,
      `initials_data` longtext NOT NULL,
      `ip_address` varchar(64) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `contract_field` (`contract_id`,`field_key`)
    )" . $engine;

    $queries[db_prefix() . 'solar_documents'] = "CREATE TABLE `" . db_prefix() . "solar_documents` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) NOT NULL,
      `contract_id` int(11) DEFAULT NULL,
      `file_name` varchar(255) NOT NULL,
      `stored_name` varchar(255) NOT NULL,
      `file_type` varchar(100) DEFAULT NULL,
      `file_size` bigint(20) NOT NULL DEFAULT 0,
      `uploaded_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `analysis_id` (`analysis_id`)
    )" . $engine;

    $queries[db_prefix() . 'solar_proposal_templates'] = "CREATE TABLE `" . db_prefix() . "solar_proposal_templates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(191) NOT NULL,
      `hero_title` varchar(255) NOT NULL,
      `intro_html` longtext DEFAULT NULL,
      `closing_html` longtext DEFAULT NULL,
      `primary_color` varchar(20) NOT NULL DEFAULT '#0E6F5B',
      `secondary_color` varchar(20) NOT NULL DEFAULT '#3598DB',
      `accent_color` varchar(20) NOT NULL DEFAULT '#F28C28',
      `active` tinyint(1) NOT NULL DEFAULT 1,
      `is_default` tinyint(1) NOT NULL DEFAULT 0,
      `created_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    )" . $engine;

    $queries[db_prefix() . 'solar_proposals'] = "CREATE TABLE `" . db_prefix() . "solar_proposals` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) NOT NULL,
      `template_id` int(11) DEFAULT NULL,
      `title` varchar(191) NOT NULL,
      `status` varchar(30) NOT NULL DEFAULT 'draft',
      `public_token` varchar(64) NOT NULL,
      `expires_on` date DEFAULT NULL,
      `accepted_name` varchar(191) DEFAULT NULL,
      `accepted_email` varchar(191) DEFAULT NULL,
      `accepted_phone` varchar(60) DEFAULT NULL,
      `accepted_ip` varchar(64) DEFAULT NULL,
      `accepted_user_agent` varchar(255) DEFAULT NULL,
      `accepted_at` datetime DEFAULT NULL,
      `declined_at` datetime DEFAULT NULL,
      `sent_at` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL DEFAULT 0,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `public_token` (`public_token`),
      KEY `analysis_id` (`analysis_id`),
      KEY `status` (`status`)
    )" . $engine;

    $queries[db_prefix() . 'solar_proposal_comments'] = "CREATE TABLE `" . db_prefix() . "solar_proposal_comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `proposal_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `contact_name` varchar(191) DEFAULT NULL,
      `contact_email` varchar(191) DEFAULT NULL,
      `message` text NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `proposal_id` (`proposal_id`)
    )" . $engine;

    $queries[db_prefix() . 'solar_contract_comments'] = "CREATE TABLE `" . db_prefix() . "solar_contract_comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `contract_id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `contact_name` varchar(191) DEFAULT NULL,
      `contact_email` varchar(191) DEFAULT NULL,
      `message` text NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `contract_id` (`contract_id`)
    )" . $engine;

    $queries[db_prefix() . 'solar_api_logs'] = "CREATE TABLE `" . db_prefix() . "solar_api_logs` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `analysis_id` int(11) DEFAULT NULL,
      `provider` varchar(50) NOT NULL,
      `endpoint` varchar(191) DEFAULT NULL,
      `http_status` int(11) DEFAULT NULL,
      `success` tinyint(1) NOT NULL DEFAULT 0,
      `request_summary` text DEFAULT NULL,
      `response_summary` longtext DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `provider_created` (`provider`,`created_at`)
    )" . $engine;

    foreach ($queries as $table => $sql) {
        if (!$CI->db->table_exists($table)) {
            $CI->db->query($sql);
        }
    }

    // Upgrade-safe schema repairs for Solar Pro only. Existing data is preserved.
    if ($CI->db->table_exists(db_prefix() . 'solar_contracts') && !$CI->db->field_exists('native_contract_id', db_prefix() . 'solar_contracts')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'solar_contracts` ADD `native_contract_id` INT(11) NULL DEFAULT NULL AFTER `analysis_id`, ADD KEY `native_contract_id` (`native_contract_id`)');
    }
    if ($CI->db->table_exists(db_prefix() . 'solar_documents') && !$CI->db->field_exists('document_type', db_prefix() . 'solar_documents')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "solar_documents` ADD `document_type` VARCHAR(40) NOT NULL DEFAULT 'other' AFTER `contract_id`");
    }
    if ($CI->db->table_exists(db_prefix() . 'solar_analyses') && !$CI->db->field_exists('analysis_method', db_prefix() . 'solar_analyses')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "solar_analyses` ADD `analysis_method` VARCHAR(30) NOT NULL DEFAULT 'auto' AFTER `status`");
    }
    if ($CI->db->table_exists(db_prefix() . 'solar_contract_templates') && !$CI->db->field_exists('template_type', db_prefix() . 'solar_contract_templates')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "solar_contract_templates` ADD `template_type` VARCHAR(30) NOT NULL DEFAULT 'custom' AFTER `name`, ADD `is_system_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `template_type`");
    }
    if ($CI->db->table_exists(db_prefix() . 'solar_contracts') && !$CI->db->field_exists('signed_phone', db_prefix() . 'solar_contracts')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "solar_contracts` ADD `signed_phone` VARCHAR(60) DEFAULT NULL AFTER `signed_email`, ADD `signed_first_name` VARCHAR(100) DEFAULT NULL AFTER `signed_name`, ADD `signed_last_name` VARCHAR(100) DEFAULT NULL AFTER `signed_first_name`");
    }
    if ($CI->db->table_exists(db_prefix() . 'solar_contracts') && !$CI->db->field_exists('contract_value', db_prefix() . 'solar_contracts')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "solar_contracts` ADD `contract_value` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `title`, ADD `start_date` DATE DEFAULT NULL AFTER `contract_value`, ADD `end_date` DATE DEFAULT NULL AFTER `start_date`");
    }
}

function solar_pro_install_options()
{
    $defaults = [
        'solar_pro_google_api_key' => '',
        'solar_pro_google_geocoding_api_key' => '',
        'solar_pro_google_required_quality' => 'BASE',
        'solar_pro_enphase_api_key' => '',
        'solar_pro_enphase_client_id' => '',
        'solar_pro_enphase_client_secret' => '',
        'solar_pro_enphase_base_url' => 'https://api.enphaseenergy.com/api/v4',
        'solar_pro_enphase_organization' => '',
        'solar_pro_enphase_model' => '',
        'solar_pro_default_panel_watts' => '440',
        'solar_pro_daily_kwh_400' => '1.50',
        'solar_pro_daily_kwh_440' => '1.65',
        'solar_pro_price_per_panel' => '1800.00',
        'solar_pro_price_per_watt' => '0',
        'solar_pro_pricing_mode' => 'panel',
        'solar_pro_target_offset_pct' => '100',
        'solar_pro_system_loss_pct' => '14',
        'solar_pro_shading_loss_pct' => '0',
        'solar_pro_production_multiplier_pct' => '100',
        'solar_pro_utility_escalation_pct' => '3',
        'solar_pro_solar_degradation_pct' => '0.50',
        'solar_pro_financial_years' => '25',
        'solar_pro_finance_apr_pct' => '6.99',
        'solar_pro_finance_term_years' => '25',
        'solar_pro_finance_down_payment' => '0',
        'solar_pro_default_energy_rate' => '0.16',
        'solar_pro_default_customer_charge' => '15.00',
        'solar_pro_public_calculator_enabled' => '1',
        'solar_pro_public_create_lead' => '1',
        'solar_pro_default_state' => 'FL',
        'solar_pro_brand_primary' => '#F28C28',
        'solar_pro_brand_secondary' => '#3598DB',
        'solar_pro_brand_success' => '#169179',
        'solar_pro_brand_dark' => '#0E6F5B',
        'solar_pro_co2_kg_per_kwh' => '0.386',
        'solar_pro_tree_kg_co2_year' => '22',
        'solar_pro_car_co2_tons_year' => '4.6',
        'solar_pro_appointment_url' => '',
    ];

    foreach ($defaults as $key => $value) {
        add_option($key, $value, 1);
    }
}

function solar_pro_settings_keys()
{
    return [
        'solar_pro_google_api_key',
        'solar_pro_google_geocoding_api_key',
        'solar_pro_google_required_quality',
        'solar_pro_enphase_api_key',
        'solar_pro_enphase_client_id',
        'solar_pro_enphase_client_secret',
        'solar_pro_enphase_base_url',
        'solar_pro_enphase_organization',
        'solar_pro_enphase_model',
        'solar_pro_default_panel_watts',
        'solar_pro_daily_kwh_400',
        'solar_pro_daily_kwh_440',
        'solar_pro_price_per_panel',
        'solar_pro_price_per_watt',
        'solar_pro_pricing_mode',
        'solar_pro_target_offset_pct',
        'solar_pro_system_loss_pct',
        'solar_pro_shading_loss_pct',
        'solar_pro_production_multiplier_pct',
        'solar_pro_utility_escalation_pct',
        'solar_pro_solar_degradation_pct',
        'solar_pro_financial_years',
        'solar_pro_finance_apr_pct',
        'solar_pro_finance_term_years',
        'solar_pro_finance_down_payment',
        'solar_pro_default_energy_rate',
        'solar_pro_default_customer_charge',
        'solar_pro_public_calculator_enabled',
        'solar_pro_public_create_lead',
        'solar_pro_default_state',
        'solar_pro_brand_primary',
        'solar_pro_brand_secondary',
        'solar_pro_brand_success',
        'solar_pro_brand_dark',
        'solar_pro_co2_kg_per_kwh',
        'solar_pro_tree_kg_co2_year',
        'solar_pro_car_co2_tons_year',
        'solar_pro_appointment_url',
    ];
}

function solar_pro_secret_setting_keys()
{
    return [
        'solar_pro_google_api_key',
        'solar_pro_google_geocoding_api_key',
        'solar_pro_enphase_api_key',
        'solar_pro_enphase_client_secret',
    ];
}

function solar_pro_seed_defaults($CI)
{
    $utilities = [
        ['Duke Energy Florida', 'Duke Energy', 'Florida'],
        ['Tampa Electric Company', 'TECO', 'West Central Florida'],
        ['Florida Power & Light', 'FPL', 'Florida'],
        ['SECO Energy', 'SECO', 'Central Florida'],
        ['Withlacoochee River Electric Cooperative', 'WREC', 'West Central Florida'],
        ['Lakeland Electric', 'Lakeland Electric', 'Lakeland'],
        ['Orlando Utilities Commission', 'OUC', 'Orlando'],
        ['Kissimmee Utility Authority', 'KUA', 'Kissimmee'],
        ['Clay Electric Cooperative', 'Clay Electric', 'North Florida'],
        ['Peace River Electric Cooperative', 'PRECO', 'Central/Southwest Florida'],
        ['Gainesville Regional Utilities', 'GRU', 'Gainesville'],
        ['JEA', 'JEA', 'Jacksonville'],
        ['Florida Keys Electric Cooperative', 'FKEC', 'Florida Keys'],
        ['Lee County Electric Cooperative', 'LCEC', 'Southwest Florida'],
    ];

    foreach ($utilities as $utility) {
        $CI->db->where('name', $utility[0]);
        if (!$CI->db->get(db_prefix() . 'solar_utilities')->row()) {
            $CI->db->insert(db_prefix() . 'solar_utilities', [
                'name' => $utility[0], 'short_name' => $utility[1], 'service_area' => $utility[2],
                'active' => 1, 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    $equipment = [
        ['panel', 'Generic', '400 W Solar Module', 400, null, 20.0, 25],
        ['panel', 'Generic', '440 W Solar Module', 440, null, 21.0, 25],
        ['inverter', 'Enphase', 'IQ8+', null, 300, 97.0, 25],
        ['inverter', 'Enphase', 'IQ8M', null, 325, 97.0, 25],
        ['inverter', 'Enphase', 'IQ8A', null, 366, 97.0, 25],
        ['inverter', 'Enphase', 'IQ8X', null, 384, 97.0, 25],
    ];
    foreach ($equipment as $item) {
        $CI->db->where('manufacturer', $item[1])->where('model', $item[2]);
        if (!$CI->db->get(db_prefix() . 'solar_equipment')->row()) {
            $CI->db->insert(db_prefix() . 'solar_equipment', [
                'type' => $item[0], 'manufacturer' => $item[1], 'model' => $item[2],
                'watts' => $item[3], 'ac_watts' => $item[4], 'efficiency_pct' => $item[5],
                'warranty_years' => $item[6], 'active' => 1, 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    solar_pro_seed_contract_templates($CI);
    solar_pro_seed_proposal_templates($CI);
    solar_pro_install_proposal_assets();
    solar_pro_seed_email_template($CI);
}


function solar_pro_seed_proposal_templates($CI)
{
    if (!$CI->db->table_exists(db_prefix().'solar_proposal_templates')) { return; }
    if (total_rows(db_prefix().'solar_proposal_templates') > 0) { return; }
    $CI->db->insert(db_prefix().'solar_proposal_templates', [
        'name' => 'Futuristic Solar Magazine',
        'hero_title' => _l('solar_pro_power_your_future'),
        'intro_html' => '<p>See what your home can produce, what your energy can cost, and how your solar investment can perform over time.</p>',
        'closing_html' => '<p>Your solar analysis is an estimate based on the information available today. Final engineering, utility approval, site conditions, equipment availability, and contract terms control the final system.</p>',
        'primary_color' => '#0E6F5B', 'secondary_color' => '#3598DB', 'accent_color' => '#F28C28',
        'active' => 1, 'is_default' => 1, 'created_by' => 0, 'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function solar_pro_install_proposal_assets()
{
    $source=module_dir_path('solar_pro','assets/img/');
    $dest=FCPATH.'uploads/solar_pro/proposal_assets/';
    if(!is_dir($dest)){@mkdir($dest,0755,true);}
    if(!is_dir($dest)){return false;}
    foreach(['co2-impact.png','trees-impact.png','clean-energy.png','cars-impact.png','solar-energy-flow.png','installation-flow.png','enphase-microinverter.png','enphase-iq8.png','enphase-gateway.png'] as $file){
        if(is_file($source.$file) && (!is_file($dest.$file) || @md5_file($source.$file)!==@md5_file($dest.$file))){@copy($source.$file,$dest.$file);}
    }
    return true;
}
function solar_pro_proposal_asset_path($file)
{
    $upload=FCPATH.'uploads/solar_pro/proposal_assets/'.basename((string)$file);
    return is_file($upload)?$upload:module_dir_path('solar_pro','assets/img/'.basename((string)$file));
}
function solar_pro_proposal_asset_url($file)
{
    $upload=FCPATH.'uploads/solar_pro/proposal_assets/'.basename((string)$file);
    return is_file($upload)?base_url('uploads/solar_pro/proposal_assets/'.rawurlencode(basename((string)$file))):module_dir_url('solar_pro','assets/img/'.basename((string)$file));
}


function solar_pro_pdf_image_src($file)
{
    $path = solar_pro_proposal_asset_path($file);
    if (!is_file($path)) { return ''; }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : ($ext === 'webp' ? 'image/webp' : 'image/png');
    $data = @file_get_contents($path);
    return $data === false ? '' : 'data:' . $mime . ';base64,' . base64_encode($data);
}

function solar_pro_appointment_url()
{
    $url = trim((string) solar_pro_setting('solar_pro_appointment_url', ''));
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

function solar_pro_humanize($value)
{
    return ucwords(str_replace(['_', '-'], ' ', trim((string) $value)));
}

function solar_pro_contract_merge_fields(array $analysis, array $extra = [])
{
    $finance = solar_pro_finance_summary($analysis);
    $CI = &get_instance();
    $company = get_option('companyname');
    $companyAddress = trim(implode(', ', array_filter([
        get_option('company_address'), get_option('company_city'), get_option('company_state'), get_option('company_zip')
    ])));
    $panel = !empty($analysis['panel_equipment_id']) ? $CI->db->where('id',(int)$analysis['panel_equipment_id'])->get(db_prefix().'solar_equipment')->row_array() : null;
    $inverter = !empty($analysis['inverter_equipment_id']) ? $CI->db->where('id',(int)$analysis['inverter_equipment_id'])->get(db_prefix().'solar_equipment')->row_array() : null;
    $price = (float)($analysis['system_price'] ?? 0);
    $kw = (float)($analysis['system_kw'] ?? 0);
    $ppw = $kw > 0 ? $price / ($kw * 1000) : 0;
    $taxCredit = $price * 0.30;
    $transactionDate = date('F j, Y');
    $cancelDeadline = solar_pro_add_business_days(date('Y-m-d'), 3);
    $fields = [
        '{company_name}' => $company,
        '{company_address}' => $companyAddress,
        '{company_phone}' => get_option('company_phonenumber'),
        '{company_email}' => get_option('smtp_email') ?: get_option('company_email'),
        '{customer_first_name}' => (string)($analysis['first_name'] ?? ''),
        '{customer_last_name}' => (string)($analysis['last_name'] ?? ''),
        '{customer_name}' => trim(($analysis['first_name'] ?? '').' '.($analysis['last_name'] ?? '')),
        '{customer_email}' => (string)($analysis['email'] ?? ''),
        '{customer_phone}' => (string)($analysis['phone'] ?? ''),
        '{property_address}' => trim(implode(', ', array_filter([$analysis['address'] ?? '', $analysis['city'] ?? '', $analysis['state'] ?? '', $analysis['zip'] ?? '']))),
        '{transaction_date}' => $transactionDate,
        '{cancellation_deadline}' => date('F j, Y', strtotime($cancelDeadline)),
        '{system_price}' => app_format_money($price, get_base_currency()),
        '{system_price_number}' => number_format($price,2,'.',''),
        '{price_per_watt}' => number_format($ppw,2),
        '{panel_count}' => (string)(int)($analysis['panel_count'] ?? 0),
        '{panel_watts}' => number_format((float)($analysis['panel_watts'] ?? 0),0),
        '{panel_brand}' => $panel ? trim($panel['manufacturer'].' '.$panel['model']) : _l('solar_pro_not_specified'),
        '{inverter_brand}' => $inverter ? trim($inverter['manufacturer'].' '.$inverter['model']) : _l('solar_pro_not_specified'),
        '{system_kw}' => number_format($kw,3),
        '{annual_production}' => number_format((float)($analysis['annual_production_kwh'] ?? 0),0),
        '{solar_offset}' => number_format((float)($analysis['solar_offset_pct'] ?? 0),1),
        '{apr}' => number_format((float)$finance['apr_pct'],2).'%',
        '{loan_term_months}' => (string)((int)$finance['term_years']*12),
        '{loan_payment}' => app_format_money((float)$finance['monthly_payment'], get_base_currency()),
        '{tax_credit_30}' => app_format_money($taxCredit, get_base_currency()),
        '{year1_savings}' => app_format_money((float)($analysis['year1_savings'] ?? 0), get_base_currency()),
        '{monthly_savings}' => app_format_money((float)$finance['monthly_savings'], get_base_currency()),
        '{annual_savings}' => app_format_money((float)$finance['annual_savings'], get_base_currency()),
    ];
    try {
        $CI->load->library('merge_fields/other_merge_fields');
        $fields = array_merge($fields, $CI->other_merge_fields->format());
        if (!empty($analysis['client_id'])) {
            $CI->load->library('merge_fields/client_merge_fields');
            $contactId = function_exists('get_primary_contact_user_id') ? get_primary_contact_user_id((int)$analysis['client_id']) : '';
            $fields = array_merge($fields, $CI->client_merge_fields->format((int)$analysis['client_id'], $contactId));
        }
    } catch (Throwable $e) {
        log_message('debug', 'Solar Pro CRM merge fields unavailable: ' . $e->getMessage());
    }
    return array_merge($fields, $extra);
}

function solar_pro_apply_merge_fields($content, array $analysis, array $extra = [])
{
    return strtr((string)$content, solar_pro_contract_merge_fields($analysis, $extra));
}

function solar_pro_add_business_days($date, $days)
{
    $ts = strtotime($date); $added = 0;
    while ($added < (int)$days) { $ts = strtotime('+1 day', $ts); $n=(int)date('N',$ts); if ($n < 6) { $added++; } }
    return date('Y-m-d',$ts);
}

function solar_pro_environment_summary(array $analysis)
{
    $kwh = max(0,(float)($analysis['annual_production_kwh'] ?? 0));
    $kgPerKwh = max(0.001,(float)solar_pro_setting('solar_pro_co2_kg_per_kwh','0.386'));
    $treeKg = max(1,(float)solar_pro_setting('solar_pro_tree_kg_co2_year','22'));
    $kg = $kwh*$kgPerKwh;
    $co2Tons = $kg/1000;
    $cars = $co2Tons / max(0.1,(float)solar_pro_setting('solar_pro_car_co2_tons_year','4.6'));
    return ['co2_kg'=>$kg,'co2_tons'=>$co2Tons,'trees'=>$kg/$treeKg,'cars'=>$cars];
}

function solar_pro_seed_email_template($CI)
{
    if (!$CI->db->table_exists(db_prefix().'emailtemplates')) { return; }
    $slug='solar-proposal-send';
    $templates = [
        'english' => [
            'name'=>'Solar Proposal - Send to Customer',
            'subject'=>'Your Solar Proposal from {company_name}',
            'message'=>'<p>Hello {customer_name},</p><p>Your solar proposal is ready. Review your system design, projected savings, environmental benefits, and financial summary using the secure link below.</p><p><a href="{proposal_link}">View Solar Proposal</a></p><p>Thank you,<br>{company_name}</p>',
        ],
        'spanish' => [
            'name'=>'Propuesta Solar - Enviar al Cliente',
            'subject'=>'Su Propuesta Solar de {company_name}',
            'message'=>'<p>Hola {customer_name},</p><p>Su propuesta solar está lista. Revise el diseño del sistema, los ahorros proyectados, los beneficios ambientales y el resumen financiero en el enlace seguro.</p><p><a href="{proposal_link}">Ver Propuesta Solar</a></p><p>Gracias,<br>{company_name}</p>',
        ],
    ];
    foreach ($templates as $language=>$tpl) {
        if ($CI->db->where('slug',$slug)->where('language',$language)->get(db_prefix().'emailtemplates')->row()) { continue; }
        $CI->db->insert(db_prefix().'emailtemplates',[
            'type'=>'client','slug'=>$slug,'language'=>$language,'name'=>$tpl['name'],'subject'=>$tpl['subject'],'message'=>$tpl['message'],
            'fromname'=>'{company_name}','fromemail'=>'','plaintext'=>0,'active'=>1,'order'=>1
        ]);
    }
}

function solar_pro_seed_contract_templates($CI)
{
    if (!$CI->db->table_exists(db_prefix().'solar_contract_templates')) { return; }
    $templates = [
        ['Solar Purchase Agreement - Financing','financing',solar_pro_default_contract_template('financing')],
        ['Solar Purchase Agreement - Cash','cash',solar_pro_default_contract_template('cash')],
    ];
    foreach($templates as $t){
        $existing=$CI->db->where('name',$t[0])->get(db_prefix().'solar_contract_templates')->row_array();
        if(!$existing){
            $CI->db->insert(db_prefix().'solar_contract_templates',[
                'name'=>$t[0],'template_type'=>$t[1],'is_system_default'=>1,'content'=>$t[2],'active'=>1,'created_by'=>0,'created_at'=>date('Y-m-d H:i:s')
            ]);
        }
    }
}

function solar_pro_default_contract_template($type='financing')
{
    $company='{company_name}';
    $payment = $type==='cash'
        ? '<h3>PAYMENT AND PRICE DETAILS - CASH</h3><table class="sp-contract-table"><tr><th>Total Cash Contract Price</th><td>{system_price}</td></tr><tr><th>Down Payment (30%)</th><td>30% of contract price</td></tr><tr><th>Pre-Installation Payment (20%)</th><td>20% after permit approval and scheduling</td></tr><tr><th>Installation Day Payment (45%)</th><td>45% on installation completion</td></tr><tr><th>PTO Payment (5%)</th><td>5% following Permission to Operate</td></tr></table>'
        : '<h3>ESTIMATED SCHEDULE OF PAYMENTS AND FINANCIAL DISCLOSURE</h3><table class="sp-contract-table"><tr><th>System Cost</th><td>{system_price}</td></tr><tr><th>Estimated Loan Payment</th><td>{loan_payment}</td></tr><tr><th>Number of Payments</th><td>{loan_term_months}</td></tr><tr><th>Estimated Federal Tax Credit (30%)</th><td>{tax_credit_30}</td></tr><tr><th>Annual Percentage Rate</th><td>{apr}</td></tr></table><p><strong>Tax-credit disclosure:</strong> '.$company.' does not provide tax advice or guarantee eligibility for any tax credit. The customer is responsible for consulting a qualified tax professional.</p>';
    return '<div class="sp-contract-document">'
    .'<div class="sp-contract-cover"><h1>STATE OF FLORIDA</h1><h2>'.($type==='cash'?'CASH PURCHASE AGREEMENT':'FINANCING PURCHASE AGREEMENT').'</h2></div>'
    .'<h3>AGREEMENT KEY TERMS AND CONDITIONS</h3><p><strong>ESTIMATED DESCRIPTION OF THE PROJECT AND SIGNIFICANT MATERIALS AND EQUIPMENT.</strong> Equipment description may be finalized during site design and documented by amendment to this agreement.</p>'
    .'<ol><li><strong>Estimated Agreement Price.</strong> The Agreement Price is subject to final site survey and any written amendments or change orders agreed to by both parties.</li><li><strong>Installation Timeline.</strong> '.$company.' will install the System within a reasonable period after execution and required amendments, subject to engineering, permitting, jurisdictional review, utility review, material availability, weather, customer-caused delay, and other conditions outside the contractor’s control.</li></ol>'
    .'<table class="sp-contract-table"><tr><th>Property Owner(s)</th><td>{customer_name}</td></tr><tr><th>Installation Address</th><td>{property_address}</td></tr><tr><th>Phone</th><td>{customer_phone}</td></tr><tr><th>Email</th><td>{customer_email}</td></tr><tr><th>System Size</th><td>{system_kw} kW</td><th>Module Quantity</th><td>{panel_count}</td></tr><tr><th>Panel</th><td>{panel_brand}</td><th>Inverter</th><td>{inverter_brand}</td></tr><tr><th>Estimated First-Year Production</th><td>{annual_production} kWh</td><th>Solar Offset</th><td>{solar_offset}%</td></tr><tr><th>Sales Price/Watt</th><td>${price_per_watt}</td><th>Total System Cost</th><td>{system_price}</td></tr></table>'
    .$payment
    .'<p><strong>Customer Initials:</strong> {solar_initial:key_terms}</p>'
    .'<div class="sp-page-break"></div><h2>NOTICE REGARDING FLORIDA CONSTRUCTION LIEN LAW</h2><p><strong>ACCORDING TO FLORIDA’S CONSTRUCTION LIEN LAW (SECTIONS 713.001-713.37, FLORIDA STATUTES), THOSE WHO WORK ON YOUR PROPERTY OR PROVIDE MATERIALS AND SERVICES AND ARE NOT PAID IN FULL HAVE A RIGHT TO ENFORCE THEIR CLAIM FOR PAYMENT AGAINST YOUR PROPERTY. THIS CLAIM IS KNOWN AS A CONSTRUCTION LIEN. IF YOUR CONTRACTOR OR A SUBCONTRACTOR FAILS TO PAY SUBCONTRACTORS, SUB-SUBCONTRACTORS, OR MATERIAL SUPPLIERS, THOSE PEOPLE WHO ARE OWED MONEY MAY LOOK TO YOUR PROPERTY FOR PAYMENT, EVEN IF YOU HAVE ALREADY PAID YOUR CONTRACTOR IN FULL. IF YOU FAIL TO PAY YOUR CONTRACTOR, YOUR CONTRACTOR MAY ALSO HAVE A LIEN ON YOUR PROPERTY. THIS MEANS IF A LIEN IS FILED YOUR PROPERTY COULD BE SOLD AGAINST YOUR WILL TO PAY FOR LABOR, MATERIALS, OR OTHER SERVICES THAT YOUR CONTRACTOR OR A SUBCONTRACTOR MAY HAVE FAILED TO PAY. TO PROTECT YOURSELF, YOU SHOULD STIPULATE IN THIS CONTRACT THAT BEFORE ANY PAYMENT IS MADE, YOUR CONTRACTOR IS REQUIRED TO PROVIDE YOU WITH A WRITTEN RELEASE OF LIEN FROM ANY PERSON OR COMPANY THAT HAS PROVIDED TO YOU A “NOTICE TO OWNER.” FLORIDA’S CONSTRUCTION LIEN LAW IS COMPLEX, AND IT IS RECOMMENDED THAT YOU CONSULT AN ATTORNEY.</strong></p>'
    .'<p>(1)(a) If the contract is written, the notice must be in the contract document. If the contract is oral or implied, the notice must be provided in a document referencing the contract.</p><p>(b) Failure to provide such written notice does not bar enforcement of a lien against a person who has not been adversely affected.</p><p>(c) This section may not be construed to adversely affect lien and bond rights of lienors who are not in privity with the owner and does not apply when the owner is a contractor licensed under chapter 489 or a person creating parcels or offering parcels for sale or lease in the ordinary course of business.</p>'
    .'<p><strong>Customer Property Owner Signature:</strong> {solar_signature}</p><p><strong>Date:</strong> {transaction_date} &nbsp;&nbsp; <strong>Printed Name:</strong> {customer_name}</p>'
    .'<h2>FLORIDA HOMEOWNERS’ CONSTRUCTION RECOVERY FUND</h2><p><strong>A PAYMENT, UP TO A LIMITED AMOUNT, MAY BE AVAILABLE FROM THE FLORIDA HOMEOWNERS’ CONSTRUCTION RECOVERY FUND IF YOU LOSE MONEY ON A PROJECT PERFORMED UNDER CONTRACT, WHERE THE LOSS RESULTS FROM SPECIFIED VIOLATIONS OF FLORIDA LAW BY A LICENSED CONTRACTOR. FOR INFORMATION ABOUT THE RECOVERY FUND AND FILING A CLAIM, CONTACT THE FLORIDA CONSTRUCTION INDUSTRY LICENSING BOARD.</strong></p><p>Division of Professions / Construction Industry Licensing Board, 1940 North Monroe Street, Tallahassee, FL 32399-0783. Phone: 850-487-1395.</p>'
    .'<p><strong>Warranty.</strong> '.$company.' warrants labor as stated in this agreement and will pass through applicable manufacturer warranties. Roof-penetration responsibility applies to the area of the roof affected by the solar installation and not to the entire roof unless otherwise stated in writing.</p><p><strong>Timeline for Completion.</strong> Project dates are estimates and may change for circumstances beyond contractor control. Permitting, inspections, engineering, utility review, and Permission to Operate are outside the contractor’s exclusive control.</p>'
    .'<div class="sp-page-break"></div><h2>3 DAY RIGHT OF RESCISSION AND NOTICE RIGHT TO CANCEL</h2><p>The Notice of Cancellation regarding your right to cancel this Agreement is incorporated into this Agreement.</p><p><strong>Date of Transaction:</strong> {transaction_date}</p><p>You may cancel this transaction, without penalty or obligation, within three business days from the transaction date. If you cancel, property traded in, payments made, and negotiable instruments executed by you will be returned as required by applicable law following receipt of the seller’s cancellation notice. Any security interest arising out of the transaction will be canceled as required by law.</p><p>If you cancel, you must make available to the seller at your residence, in substantially as good condition as when received, goods delivered under this Agreement, or comply with reasonable seller instructions for return shipment at seller expense and risk. If the seller does not pick up available goods within the period provided by applicable law, the customer may retain or dispose of the goods as permitted by law.</p><p>If you fail to make goods available, or agree to return them and fail to do so, you remain responsible for obligations imposed by applicable law and this Agreement.</p><p>Site survey, design, engineering, permitting, and similar work may involve costs. Cancellation may result in responsibility for costs already incurred to the extent allowed by law and this Agreement.</p><p>To cancel, deliver a signed and dated cancellation notice or other written notice to: <strong>{company_name}, {company_address}</strong>, no later than midnight of <strong>{cancellation_deadline}</strong>.</p><p>Customer Printed Name: {customer_name}</p><p>Customer Signature: {solar_signature}</p><p>I acknowledge receipt of the Notice of Right to Cancel. Customer Initials: {solar_initial:cancellation_notice}</p>'
    .'<div class="sp-page-break"></div><h2>DISCLAIMERS AND DEFINITIONS</h2><p>This Agreement incorporates by reference its Cover Page, disclosures, attachments, amendments, and exhibits.</p><ol><li><strong>Agreement.</strong> This Solar Purchase Agreement between '.$company.' and Customer.</li><li><strong>Cover Page.</strong> The first page and key commercial terms.</li><li><strong>Disclosures.</strong> Separate disclosures incorporated by reference.</li><li><strong>Price.</strong> The price shown in this Agreement and approved written change orders.</li><li><strong>Property.</strong> The installation address identified by Customer.</li><li><strong>Effective Date.</strong> The date upon which this Agreement becomes effective under its terms.</li><li><strong>Customer.</strong> The property owner and authorized signing party.</li><li><strong>Product/System.</strong> The photovoltaic solar system and associated equipment.</li><li><strong>Work.</strong> The scope of work performed under this Agreement.</li><li><strong>Installation.</strong> The contracted solar installation scope.</li><li><strong>Completed Installation.</strong> The System is installed and ready for start-up/testing, subject to utility requirements.</li><li><strong>PTO.</strong> Permission to Operate from the electric utility.</li></ol>'
    .'<div class="sp-page-break"></div><h2>SALE VERIFICATION DISCLOSURE</h2><table class="sp-contract-table"><tr><td>1. I understand that my monthly payment may change if a tax-credit-related loan re-amortization or similar financing condition is not completed. (Not applicable to cash terms.)</td><td>{solar_initial:verification_1}</td></tr><tr><td>2. I understand that after installation the project may require additional time to receive Permission to Operate from the utility.</td><td>{solar_initial:verification_2}</td></tr><tr><td>3. I understand that after the system begins operating I may receive an additional utility bill based on the utility billing cycle.</td><td>{solar_initial:verification_3}</td></tr><tr><td>4. I understand that inverter/monitoring internet connectivity must be maintained and service charges may apply when customer-caused connectivity issues require an onsite visit.</td><td>{solar_initial:verification_4}</td></tr><tr><td>5. I understand that the sales representative is not a tax professional and that tax-credit eligibility must be discussed with a qualified tax professional.</td><td>{solar_initial:verification_5}</td></tr><tr><td>6. I understand that applicable law may provide a three-business-day cancellation period.</td><td>{solar_initial:verification_6}</td></tr><tr><td>7. I agree to contact '.$company.' regarding issues, comments, or complaints and allow reasonable time to respond.</td><td>{solar_initial:verification_7}</td></tr><tr><td>8. I understand the solar system is designed to offset a portion or all of historical energy consumption based on the assumptions in the analysis.</td><td>{solar_initial:verification_8}</td></tr><tr><td>9. I understand that some utility interconnection classifications may require customer liability insurance or other customer obligations not included in the contract price unless expressly stated.</td><td>{solar_initial:verification_9}</td></tr><tr><td>10. I understand that communications related to this transaction must be truthful, accurate, and in good faith.</td><td>{solar_initial:verification_10}</td></tr></table>'
    .'<div class="sp-page-break"></div><h2>TERMS & CONDITIONS</h2><ol><li><strong>Acceptance.</strong> All agreements are subject to final approval by '.$company.'. Customer accepts the products and services described in this Agreement and these terms and conditions.</li><li><strong>Quotations and Prices.</strong> This Agreement expires thirty (30) calendar days from the date signed unless accepted earlier. Quoted prices apply to the listed products, services and quantities and may be adjusted only as permitted by this Agreement, applicable law, or written change order.</li><li><strong>Limited Warranty.</strong> Workmanship and product warranties are governed by this Agreement, written company warranty documents, and applicable manufacturer warranties. Manufacturer warranties remain subject to manufacturer terms and exclusions.</li><li><strong>Customer Responsibility.</strong> Customer is responsible for protecting materials delivered to the property from risks not caused by '.$company.' and for maintaining appropriate property insurance.</li><li><strong>Payment Terms.</strong> Payments are due according to the payment schedule selected for this Agreement. Amounts not paid when due may accrue lawful charges, collection costs and reasonable attorney fees to the extent permitted by law.</li><li><strong>Delays.</strong> Performance dates are estimates. '.$company.' is not responsible for delays caused by permitting, inspections, utility interconnection, weather, labor or material shortages, transportation, governmental action, force majeure, customer delays, or other events outside reasonable control.</li><li><strong>Title; Security Interest; Lien Rights.</strong> To the extent permitted by law, title and security interests may remain with '.$company.' until payment obligations are satisfied. Nothing in this Agreement waives lawful lien rights.</li><li><strong>Cancellation.</strong> Cancellation rights are governed by this Agreement and applicable law, including any mandatory statutory rescission rights.</li><li><strong>Transfer.</strong> Customer may not transfer duties under this Agreement without written consent from '.$company.'.</li><li><strong>Successors.</strong> This Agreement binds lawful heirs, executors, administrators, successors and permitted assigns.</li><li><strong>Installation of Solar Panels.</strong> Installation may require penetrations or modifications to roof, soffit, fascia, drywall, block, stucco, trim and similar surfaces. Repairs are limited to the affected installation area unless otherwise agreed in writing.</li><li><strong>Waste.</strong> '.$company.' may remove installation debris and replaced materials. If Customer elects to retain removed products or parts, '.$company.' does not guarantee their condition or future function.</li><li><strong>Access.</strong> Customer grants reasonable property access during normal working hours and agrees to move or protect personal property near work areas.</li><li><strong>Assignment and Delegation.</strong> '.$company.' may assign rights or delegate obligations where allowed by law.</li><li><strong>Governing Law.</strong> This Agreement is governed by the law applicable to the project location and company contracting authority.</li><li><strong>Validity; Severability.</strong> If any provision is invalid or unenforceable, the remaining provisions remain in effect to the maximum extent allowed by law.</li><li><strong>Non-Waiver.</strong> Failure or delay in enforcing a right does not waive that right unless the waiver is in writing.</li><li><strong>Dispute Resolution.</strong> Disputes will be handled according to applicable law and any written arbitration or dispute-resolution provision agreed by the parties.</li><li><strong>Entire Agreement; Modification.</strong> This Agreement, attachments, exhibits, disclosures and written amendments are the complete agreement. Modifications must be in writing and signed or otherwise lawfully accepted by the parties.</li><li><strong>Attachments.</strong> Required notices, disclosures, exhibits and attachments are incorporated by reference. Customer initials acknowledge receipt where requested.</li></ol>'
    .'<div class="sp-signature-block"><h2>FINAL ACCEPTANCE</h2><p>By signing below, Customer agrees to the terms of this Agreement and authorizes '.$company.' to perform the contracted solar photovoltaic work subject to applicable law, permits, inspections and utility requirements.</p><p><strong>Customer Name:</strong> {customer_name}</p><p><strong>Customer Signature:</strong> {solar_signature}</p><p><strong>Customer Initials:</strong> {solar_initial:final_acceptance}</p></div>'
    .'</div>';
}

function solar_pro_random_token()
{
    try {
        return bin2hex(random_bytes(24));
    } catch (Throwable $e) {
        return hash('sha256', uniqid('solar_pro_', true) . mt_rand());
    }
}

function solar_pro_setting($key, $default = '')
{
    $value = get_option($key);
    return ($value === false || $value === null || $value === '') ? $default : $value;
}

function solar_pro_month_names()
{
    return [1 => _l('solar_pro_january'), _l('solar_pro_february'), _l('solar_pro_march'), _l('solar_pro_april'), _l('solar_pro_may'), _l('solar_pro_june'), _l('solar_pro_july'), _l('solar_pro_august'), _l('solar_pro_september'), _l('solar_pro_october'), _l('solar_pro_november'), _l('solar_pro_december')];
}


function solar_pro_finance_summary(array $analysis)
{
    $price = max(0, (float)($analysis['system_price'] ?? 0));
    $down = max(0, min($price, (float)solar_pro_setting('solar_pro_finance_down_payment', 0)));
    $principal = max(0, $price - $down);
    $apr = max(0, (float)solar_pro_setting('solar_pro_finance_apr_pct', 6.99));
    $termYears = max(1, (int)solar_pro_setting('solar_pro_finance_term_years', 25));
    $months = $termYears * 12;
    $r = $apr / 100 / 12;
    $payment = $principal <= 0 ? 0 : ($r > 0 ? $principal * ($r * pow(1 + $r, $months)) / (pow(1 + $r, $months) - 1) : $principal / $months);
    $before = (float)($analysis['average_monthly_bill'] ?? 0);
    if ($before <= 0) { $before = ((float)($analysis['year1_savings'] ?? 0) / 12) + 15; }
    $afterUtility = 0;
    $monthlyRows = $analysis['monthly'] ?? [];
    if ($monthlyRows) { $afterUtility = array_sum(array_column($monthlyRows,'utility_cost_with_solar')) / max(1,count($monthlyRows)); }
    $combined = $payment + $afterUtility;
    $monthlySavings = $before - $combined;
    $annualSavings = $monthlySavings * 12;

    // Long-term projection. Utility escalation is configurable in Settings and
    // defaults to 3% per year; production degradation remains independently
    // configurable so the report is transparent rather than hard-coded.
    $year1 = max(0, (float)($analysis['year1_savings'] ?? 0));
    $escPct = (float)solar_pro_setting('solar_pro_utility_escalation_pct',3);
    $esc = $escPct / 100;
    $deg = (float)solar_pro_setting('solar_pro_solar_degradation_pct',.5)/100;
    $yearly = [];
    $cumulativeGross = 0;
    $cumulativeNet = -$down;
    $projections = [];
    $maxYears = max(25, $termYears);
    for ($y=1; $y<=$maxYears; $y++) {
        $grossSavings = $year1 * pow(1+$esc,$y-1) * pow(1-$deg,$y-1);
        $annualLoan = $y <= $termYears ? $payment * 12 : 0;
        $netCash = $grossSavings - $annualLoan;
        $cumulativeGross += $grossSavings;
        $cumulativeNet += $netCash;
        $yearly[$y] = [
            'year'=>$y,
            'annual_savings'=>$grossSavings,
            'annual_loan'=>$annualLoan,
            'net_cash'=>$netCash,
            'cumulative_savings'=>$cumulativeGross,
            'cumulative_net'=>$cumulativeNet,
        ];
        if (in_array($y,[5,10,20,25],true)) { $projections[$y] = $cumulativeNet; }
    }
    foreach ([5,10,20,25] as $y) { if (!isset($projections[$y])) { $projections[$y] = $cumulativeNet; } }
    return [
        'apr_pct'=>$apr,'term_years'=>$termYears,'down_payment'=>$down,'principal'=>$principal,
        'monthly_payment'=>$payment,'before_monthly'=>$before,'after_utility_monthly'=>$afterUtility,
        'combined_monthly'=>$combined,'monthly_savings'=>$monthlySavings,'annual_savings'=>$annualSavings,
        'utility_escalation_pct'=>$escPct,'yearly'=>$yearly,'projections'=>$projections,
    ];
}


/**
 * Return one of the only two supported Solar Pro public languages.
 */
function solar_pro_public_language_code($requested = '')
{
    $requested = strtolower(trim((string) $requested));
    return in_array($requested, ['es', 'spanish'], true) ? 'es' : 'en';
}

/**
 * Read Solar Pro copy directly from the module English/Spanish language files.
 * This keeps public portal wording editable without changing a view/controller.
 */
function solar_pro_public_l($key, $languageCode = 'en')
{
    static $cache = [];
    $code = solar_pro_public_language_code($languageCode);
    $language = $code === 'es' ? 'spanish' : 'english';
    if (!isset($cache[$language])) {
        $file = module_dir_path('solar_pro', 'language/' . $language . '/solar_pro_lang.php');
        $moduleStrings = [];
        if (is_file($file)) {
            $loader = static function ($path) {
                $lang = [];
                include $path;
                return is_array($lang) ? $lang : [];
            };
            $moduleStrings = $loader($file);
        }
        $cache[$language] = $moduleStrings;
    }
    return array_key_exists($key, $cache[$language]) ? $cache[$language][$key] : _l($key);
}
