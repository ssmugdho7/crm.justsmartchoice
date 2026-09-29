<?php
defined('BASEPATH') or exit('No direct script access allowed');

add_option('wshop_public_booking', '1', 1);
add_option('wshop_working_day', '1,2,3,4,5,6,0', 1);
add_option('wshop_shop_opens', '08:00', 1);
add_option('wshop_shop_closes', '17:30', 1);
add_option('wshop_repair_job_terms', '', 1);
add_option('wshop_report_footer', '', 1);
add_option('wshop_loan_terms', '', 1);
add_option("wshop_repair_job_prefix", 'REPAIR-', 1);
add_option("wshop_repair_job_number", 1, 1);
add_option('wshop_repair_job_number_format', 1);
add_option("wshop_inspection_prefix", 'INSPECTION-', 1);
add_option("wshop_inspection_number", 1, 1);
add_option('wshop_inspection_number_format', 1);

if (!$CI->db->table_exists(db_prefix() . "wshop_holidays")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_holidays` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`days_off` date NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_manufacturers")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_manufacturers` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`url` TEXT NULL,
		`support_url` TEXT NULL,
		`phone` TEXT NULL,
		`email` TEXT NULL,
		`manufacture_image` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_categories")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_categories` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`code` TEXT NULL,
		`name` TEXT NULL,
		`use_for` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_delivery_methods")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_delivery_methods` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_intervals")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_intervals` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`value` INT(11) NULL,
		`type` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_models")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_models` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`manufacturer_id` INT(11) NULL,
		`category_id` INT(11) NULL,
		`model_no` TEXT NULL,
		`fieldset_id` INT(11) NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_appointment_types")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_appointment_types` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`code` TEXT NULL,
		`name` TEXT NULL,
		`estimated_hours` DECIMAL(15,2) NULL DEFAULT '0.00',
		`description` TEXT NULL,
		`plate_renewal` INT(11) NULL DEFAULT '1',
		`warrant_of_fitness` INT(11) NULL DEFAULT '1',
		`next_service` INT(11) NULL DEFAULT '1',
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_appointment_products")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_appointment_products` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`appointment_type_id` INT(11) NULL,
		`item_id` INT(11) NULL DEFAULT '0',

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_fieldsets")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_fieldsets` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_customfields")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_customfields` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`fieldto` TEXT NULL,
		`name` TEXT NOT NULL,
		`slug` TEXT NOT NULL,
		`required` tinyint(1) NOT NULL DEFAULT '0',
		`type` TEXT NOT NULL,
		`options` longtext,
		`display_inline` tinyint(1) NOT NULL DEFAULT '0',
		`field_order` int(11) DEFAULT '0',
		`active` int(11) NOT NULL DEFAULT '1',
		`show_on_pdf` int(11) NOT NULL DEFAULT '0',
		`show_on_ticket_form` tinyint(1) NOT NULL DEFAULT '0',
		`only_admin` tinyint(1) NOT NULL DEFAULT '0',
		`show_on_table` tinyint(1) NOT NULL DEFAULT '0',
		`show_on_client_portal` int(11) NOT NULL DEFAULT '0',
		`disalow_client_to_edit` int(11) NOT NULL DEFAULT '0',
		`bs_column` int(11) NOT NULL DEFAULT '12',
		`default_value` mediumtext,
		`fieldset_id` INT(11) NULL,


		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_customfieldsvalues")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_customfieldsvalues` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`relid` int(11) NOT NULL,
		`fieldid` int(11) NOT NULL,
		`fieldto` TEXT NOT NULL,
		`value` mediumtext NOT NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_templates")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_templates` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`code` TEXT NULL,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_template_forms")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_template_forms` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,
		`inspection_template_id` INT(11) NULL,
		`form_order` int(11) DEFAULT '0',

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_template_form_details")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_template_form_details` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`fieldto` TEXT NULL,
		`name` TEXT NOT NULL,
		`slug` TEXT NOT NULL,
		`required` tinyint(1) NOT NULL DEFAULT '0',
		`type` TEXT NOT NULL,
		`options` longtext,
		`display_inline` tinyint(1) NOT NULL DEFAULT '0',
		`field_order` int(11) DEFAULT '0',
		`active` int(11) NOT NULL DEFAULT '1',
		`bs_column` int(11) NOT NULL DEFAULT '12',
		`default_value` mediumtext,
		`inspection_template_form_id` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_template_values")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_template_values` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`relid` int(11) NOT NULL COMMENT 'inspection id',
		`inspection_template_id` INT(11) NULL,
		`inspection_template_form_id` INT(11) NULL,
		`inspection_template_form_detail_id` int(11) NOT NULL,

		`fieldto` TEXT NOT NULL,
		`value` mediumtext NOT NULL,
		`inspection_result` TEXT NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_branches")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_branches` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(250) NOT NULL,
		`active` tinyint(1),
		`email` varchar(100) NOT NULL,
		`phonenumber` varchar(30) DEFAULT NULL,
		`address` TEXT NULL,
		`city` TEXT NULL,
		`state` TEXT NULL,
		`country` int(11) NOT NULL DEFAULT '0',
		`zip` TEXT NULL,
		`website` TEXT NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_devices")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_devices` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`code` TEXT NULL,
		`serial_no` TEXT NULL,
		`model_id` INT(11) NULL,
		`prod_date` date NULL,
		`client_id` INT(11) NULL,
		`purchase_date` date NULL,
		`warranty_start_date` date NULL,
		`warranty_period_months` INT(11) NULL DEFAULT '0',
		`warranty_expiry_date` date NULL,
		`warranty_expiring_alert` INT(11) NULL DEFAULT '0',
		`description` TEXT NULL,
		`status` tinyint(1),
		`primary_profile_image` TEXT NULL,
		`last_maintenance` date NULL,
		`next_maintenance` date NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_activity")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_activity` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`rel_type` varchar(20) DEFAULT NULL,
		`rel_id` int(11) NOT NULL,
		`description` mediumtext NOT NULL,
		`additional_data` mediumtext,
		`staffid` varchar(11) DEFAULT NULL,
		`full_name` varchar(100) DEFAULT NULL,
		`date` datetime NOT NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_labour_products")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_labour_products` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`code` TEXT NULL,
		`category_id` INT(11) NULL DEFAULT '0',
		`standard_time` DECIMAL(15,2) NULL DEFAULT '0.00',
		`labour_type` VARCHAR(5) NULL DEFAULT 'fixed' COMMENT 'fixed-rate',
		`labour_cost` DECIMAL(15,2) NULL DEFAULT '0.00',
		`tax` INT(11) NULL,
		`tax2` INT(11) NULL,
		`assign_staff` INT(11) NULL,
		`description` TEXT NULL,
		`status` tinyint(1),
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_labour_product_materials")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_labour_product_materials` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`labour_product_id` INT(11) NULL DEFAULT '0',
		`item_id` INT(11) NULL DEFAULT '0',
		`quantity` DECIMAL(15,2) NULL DEFAULT '0.00',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->field_exists('quantity' ,db_prefix() . 'wshop_labour_product_materials')) {
	$CI->db->query("ALTER TABLE `" . db_prefix() . "wshop_labour_product_materials`
		ADD COLUMN `quantity` DECIMAL(15,2) NULL DEFAULT '0.00',

		");
}
if (!$CI->db->table_exists(db_prefix() . "wshop_repair_jobs")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_repair_jobs` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`sent` tinyint(1) NOT NULL DEFAULT 0,
		`datesend` datetime DEFAULT NULL,
		`job_tracking_number` TEXT NULL,
		`name` TEXT NULL,
		`number` int(11) NOT NULL,
		`prefix` varchar(50) DEFAULT NULL,
		`number_format` int(11) NOT NULL DEFAULT '0', 
		`appointment_type_id` INT(11) NULL DEFAULT '0',
		`client_id` INT(11) NULL DEFAULT '0',
		`contact_id` INT(11) NULL DEFAULT '0',
		`phonenumber` TEXT NULL,
		`contact_name` TEXT NULL,
		`contact_email` TEXT NULL,
		`appointment_date` datetime NULL,
		`estimated_completion_date` datetime NULL,
		`device_id` INT(11) NULL DEFAULT '0',
		`branch_id` INT(11) NULL DEFAULT '0',
		`billing_type_id` INT(11) NULL DEFAULT '0',
		`collection_type_id` INT(11) NULL DEFAULT '0',
		`delivery_type_id` INT(11) NULL DEFAULT '0',
		`sale_agent` INT(11) NULL DEFAULT '0',
		`status` TEXT NULL,
		`reference_no` TEXT NULL,
		`issue_description` TEXT NULL,
		`job_description` TEXT NULL,
		`additional_description` TEXT NULL,
		`terms` TEXT NULL,
		`estimated_hours` DECIMAL(15,2) NULL DEFAULT '0.00',
		`billing_street` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`billing_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`billing_state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`billing_zip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`billing_country` int(11) DEFAULT NULL,
		`shipping_street` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`shipping_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`shipping_state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`shipping_zip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
		`shipping_country` int(11) DEFAULT NULL,
		`estimated_labour_discount_total` decimal(15,2) DEFAULT '0.00',
		`estimated_labour_subtotal` decimal(15,2) NOT NULL,
		`estimated_labour_total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`estimated_labour_total` decimal(15,2) NOT NULL,
		`estimated_material_discount_total` decimal(15,2) DEFAULT '0.00',
		`estimated_material_subtotal` decimal(15,2) NOT NULL,
		`estimated_material_total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`estimated_material_total` decimal(15,2) NOT NULL,
		`discount_percent` decimal(15,2) DEFAULT '0.00',
		`discount_total` decimal(15,2) DEFAULT '0.00',
		`discount_type` TEXT NULL,
		`currency` int(11) NOT NULL,
		`subtotal` decimal(15,2) NOT NULL,
		`total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`total` decimal(15,2) NOT NULL,
		`hash` varchar(32) NULL,
		`invoice_id` INT(11) NULL,
		`invoiced_date` datetime NULL,
		`purchase_request_id` TEXT NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_repair_job_labour_products")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_repair_job_labour_products` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`inspection_id` INT(11) NULL DEFAULT '0',
		`inspection_form_id` INT(11) NULL DEFAULT '0',
		`inspection_form_detail_id` INT(11) NULL DEFAULT '0',
		`labour_product_id` INT(11) NULL DEFAULT '0',
		`labour_type` TEXT NULL,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`estimated_hours` DECIMAL(15,2) NULL DEFAULT '0.00',
		`unit_price` DECIMAL(15,2) NULL DEFAULT '0.00',
		`qty` INT(11) NULL DEFAULT '1',
		`tax_id`  TEXT NULL,
		`tax_rate`  TEXT NULL,
		`tax_name`  TEXT NULL,
		`discount` decimal(15,2) DEFAULT '0.00',
		`subtotal` decimal(15,2) DEFAULT '0.00',
		`item_order` int(11) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}
if (!$CI->db->table_exists(db_prefix() . "wshop_repair_job_labour_materials")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_repair_job_labour_materials` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`inspection_id` INT(11) NULL DEFAULT '0',
		`inspection_form_id` INT(11) NULL DEFAULT '0',
		`inspection_form_detail_id` INT(11) NULL DEFAULT '0',
		`item_id` INT(11) NULL DEFAULT '0',
		`name` TEXT NULL,
		`description` TEXT NULL,
		`rate` DECIMAL(15,2) NULL DEFAULT '0.00',
		`estimated_qty` INT(11) NULL DEFAULT '1',
		`qty` INT(11) NULL DEFAULT '1',
		`tax_id`  TEXT NULL,
		`tax_rate`  TEXT NULL,
		`tax_name`  TEXT NULL,
		`discount` decimal(15,2) DEFAULT '0.00',
		`subtotal` decimal(15,2) DEFAULT '0.00',
		`item_order` int(11) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


if (!$CI->db->table_exists(db_prefix() . "wshop_inspections")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspections` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`sent` tinyint(1) NOT NULL DEFAULT 0,
		`datesend` datetime DEFAULT NULL,
		`number` int(11) NOT NULL,
		`prefix` varchar(50) DEFAULT NULL,
		`number_format` int(11) NOT NULL DEFAULT '0', 
		`inspection_type_id` INT(11) NULL DEFAULT '0',
		`device_id` INT(11) NULL DEFAULT '0',
		`inspection_template_id` INT(11) NULL DEFAULT '0',
		`client_id` INT(11) NULL DEFAULT '0',
		`contact_id` INT(11) NULL DEFAULT '0',
		`phonenumber` TEXT NULL,
		`contact_name` TEXT NULL,
		`contact_email` TEXT NULL,
		`person_in_charge` INT(11) NULL DEFAULT '0',
		`start_date` date NULL,
		`end_date` date NULL,
		`interval_id` INT(11) NULL,
		`next_inspection_date` date NULL,
		`next_inspection_alert` INT(11) NULL DEFAULT '0',
		`due_date_date_alert` INT(11) NULL DEFAULT '0',
		`description` TEXT NULL,
		`status` TEXT NULL,
		`visible_to_customer` INT(1) NULL DEFAULT '1',
		`currency` int(11) NOT NULL,
		`hash` varchar(32) NULL,
		`inspection_template_name` TEXT NULL,
		`estimated_labour_discount_total` decimal(15,2) DEFAULT '0.00',
		`estimated_labour_subtotal` decimal(15,2) NOT NULL,
		`estimated_labour_total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`estimated_labour_total` decimal(15,2) NOT NULL,
		`estimated_material_discount_total` decimal(15,2) DEFAULT '0.00',
		`estimated_material_subtotal` decimal(15,2) NOT NULL,
		`estimated_material_total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`estimated_material_total` decimal(15,2) NOT NULL,
		`discount_percent` decimal(15,2) DEFAULT '0.00',
		`discount_total` decimal(15,2) DEFAULT '0.00',
		`discount_type` TEXT NULL,
		`subtotal` decimal(15,2) NOT NULL,
		`total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
		`total` decimal(15,2) NOT NULL,
		`commpleted_date` date NULL,
		`invoice_id` INT(11) NULL,
		`invoiced_date` datetime NULL,
		`purchase_request_id` TEXT NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


if (!$CI->db->table_exists(db_prefix() . "wshop_repair_inspection_templates")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_repair_inspection_templates` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`inspection_id` INT(11) NULL DEFAULT '0',
		`inspection_template_id` INT(11) NULL DEFAULT '0',
		`code` TEXT NULL,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_forms")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_forms` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`description` TEXT NULL,
		`status` INT(11) NOT NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,
		`inspection_id` INT(11) NULL,
		`form_order` int(11) DEFAULT '0',

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_form_details")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_form_details` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`fieldto` TEXT NULL,
		`name` TEXT NOT NULL,
		`slug` TEXT NOT NULL,
		`required` tinyint(1) NOT NULL DEFAULT '0',
		`type` TEXT NOT NULL,
		`options` longtext,
		`display_inline` tinyint(1) NOT NULL DEFAULT '0',
		`field_order` int(11) DEFAULT '0',
		`active` int(11) NOT NULL DEFAULT '1',
		`bs_column` int(11) NOT NULL DEFAULT '12',
		`default_value` mediumtext,
		`inspection_form_id` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_inspection_values")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_inspection_values` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`relid` int(11) NOT NULL COMMENT 'inspection id',
		`inspection_form_id` INT(11) NULL,
		`inspection_form_detail_id` int(11) NOT NULL,

		`fieldto` TEXT NOT NULL,
		`value` mediumtext NOT NULL,
		`inspection_result` TEXT NULL,
		`comment` TEXT NULL,
		`approve` TEXT NULL,
		`approve_comment` TEXT NULL,
		`approved_date` datetime NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}


if (!$CI->db->table_exists(db_prefix() . "wshop_return_deliveries")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_return_deliveries` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`delivery_method_id` INT(11) NULL DEFAULT '0',
		`expected_delivery_date` date NULL,
		`status` TEXT NULL,
		`billing_street` varchar(200) DEFAULT NULL,
		`billing_city` varchar(100) DEFAULT NULL,
		`billing_state` varchar(100) DEFAULT NULL,
		`billing_zip` varchar(100) DEFAULT NULL,
		`billing_country` int(11) DEFAULT NULL,
		`shipping_street` varchar(200) DEFAULT NULL,
		`shipping_city` varchar(100) DEFAULT NULL,
		`shipping_state` varchar(100) DEFAULT NULL,
		`shipping_zip` varchar(100) DEFAULT NULL,
		`shipping_country` int(11) DEFAULT NULL,
		`transaction_type` varchar(100) NULL DEFAULT 'return' COMMENT 'return or delivery',
		`description` TEXT NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_return_delivery_notes")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_return_delivery_notes` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`return_delivery_id` INT(11) NULL DEFAULT '0',
		`transaction_type` varchar(100) NULL DEFAULT 'return' COMMENT 'return or delivery',
		`description` TEXT NULL,
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . "wshop_workshops")) {
	$CI->db->query("CREATE TABLE `" . db_prefix() . "wshop_workshops` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` TEXT NULL,
		`repair_job_id` INT(11) NULL DEFAULT '0',
		`report_type_id` INT(11) NULL DEFAULT '0',
		`report_status_id` INT(11) NULL DEFAULT '0',
		`sale_agent` INT(11) NULL DEFAULT '0',
		`from_date` datetime NULL,
		`to_date` datetime NULL,
		`parts_information` TEXT NULL,
		`description` TEXT NULL,
		`visible_to_customer` INT(1) NULL DEFAULT '1',
		`datecreated` datetime NULL,
		`staffid` INT(11) NULL,

		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}
