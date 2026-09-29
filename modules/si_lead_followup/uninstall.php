<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
if($CI->db->table_exists(db_prefix() . 'si_lead_followup_schedule')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_lead_followup_schedule");
}
if($CI->db->table_exists(db_prefix() . 'si_lead_followup_schedule_rel')) {
	$CI->db->query("DROP TABLE " . db_prefix() . "si_lead_followup_schedule_rel");
}
//settings
delete_option('si_lead_followup_activated');
delete_option('si_lead_followup_activation_code');
delete_option('sms_trigger_si_lead_followup_custom_sms');
delete_option('si_lead_followup_trigger_schedule_sms_last_run');
delete_option('si_lead_followup_clear_schedule_sms_log_after_days');