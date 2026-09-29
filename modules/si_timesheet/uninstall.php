<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_timesheet_filter')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_timesheet_filter");
}
//settings
delete_option('si_timesheet_completed_task_allow_add');
delete_option('si_timesheet_completed_task_allow_edit');
delete_option('si_timesheet_show_task_custom_fields');
delete_option('si_timesheet_show_staff_icon_in_calendar');
delete_option('si_timesheet_task_status_exclude_add');
