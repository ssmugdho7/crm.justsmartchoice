<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_sms_templates')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_sms_templates");
}
if($CI->db->table_exists(db_prefix() . 'si_sms_schedule')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_sms_schedule");
}
if($CI->db->table_exists(db_prefix() . 'si_sms_schedule_rel')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_sms_schedule_rel");
}
//settings
delete_option('si_sms_send_to_customer');
delete_option('si_sms_send_to_alt_client');
delete_option('si_sms_activated');
delete_option('si_sms_activation_code');
delete_option('si_sms_project_status_exclude');
delete_option('si_sms_task_status_exclude');
delete_option('si_sms_invoice_status_exclude');
delete_option('si_sms_lead_status_exclude');
delete_option('si_sms_ticket_status_exclude');
delete_option('sms_trigger_si_sms_custom_sms');
delete_option('si_sms_trigger_schedule_sms_last_run');
delete_option('si_sms_clear_schedule_sms_log_after_days');
delete_option('si_sms_skip_draft_status_when_create');