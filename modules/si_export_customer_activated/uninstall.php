<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_export_customer_kyc_files')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_export_customer_kyc_files");
}
if($CI->db->table_exists(db_prefix() . 'si_export_customer_services')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_export_customer_services");
}

//settings
delete_option('si_export_customer_logo_size');
delete_option('si_export_customer_specimen_sign');
delete_option('si_export_customer_show_shipping_address');
delete_option('si_export_customer_show_billing_address');
delete_option('si_export_customer_print_files_separate_page');
delete_option('si_export_customer_print_heading_color');
delete_option('si_export_customer_show_primary_contact');
delete_option('si_export_customer_show_groups');
delete_option('si_export_customer_show_custom_fields');
delete_option('si_export_customer_print_heading_text');
delete_option('si_export_customer_print_custom_fields_text');
delete_option('si_export_customer_print_attachment_text');
delete_option('si_export_customer_print_specimen_sign_text');
delete_option('si_export_customer_print_services_text');
delete_option('si_export_customer_print_orientation');
delete_option('si_export_customer_print_services');
delete_option('si_export_customer_matrix_print_heading_text');
delete_option('si_export_customer_matrix_print_heading_color');
delete_option('si_export_customer_matrix_print_orientation');
delete_option('si_export_customer_matrix_print_services');
