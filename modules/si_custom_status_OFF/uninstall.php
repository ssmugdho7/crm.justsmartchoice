<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_custom_status')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_custom_status");
}
if($CI->db->table_exists(db_prefix() . 'si_custom_status_default')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_custom_status_default");
}

//settings
delete_option('si_custom_status_edit_default_status_tasks');
delete_option('si_custom_status_edit_default_status_projects');
delete_option('si_custom_status_edit_default_status_estimates');