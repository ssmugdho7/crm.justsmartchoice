<?php
defined('BASEPATH') or exit('No direct script access allowed');

function employees_tracker_module_uninstall()
{
    $CI = &get_instance();
    // Keep data for safety; uncomment to drop.
    // $CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'employees_tracker_locations`');
    // $CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'employees_tracker_projects`');
    // $CI->db->query('DROP TABLE IF EXISTS `'.db_prefix().'employees_tracker_assignments`');
    // delete_option('employees_tracker_poll_interval');
    // delete_option('employees_tracker_allow_staff_self_share');
}
