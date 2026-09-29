<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
delete_option('staff_members_create_inline_dmn_group');
if ($CI->db->table_exists(db_prefix() . 'dmn')) {
	$CI->db->query('DROP TABLE ' . db_prefix() . 'dmn');
}
if ($CI->db->table_exists(db_prefix() . 'dmn_votes')) {
	$CI->db->query('DROP TABLE ' . db_prefix() . 'dmn_votes');
}