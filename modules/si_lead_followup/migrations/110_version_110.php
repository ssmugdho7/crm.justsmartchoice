<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_110 extends App_module_migration
{
	public function up()
	{ 
		$CI = &get_instance();
		$email_subject = 'Email Lead follow-up!';

		//get Email template subject to add in old schedules
		$CI->db->where('language',get_option('active_language'));
		$CI->db->where('type','si_lead_followup');
		$CI->db->where('slug','si-lead-followup-lead-followup-email');
		$email_template = $CI->db->get(db_prefix() . 'emailtemplates')->row();
		if($email_template){
			$email_subject = $email_template->subject;
		}
		//add email subject field
		if(!$CI->db->field_exists('email_subject',db_prefix() . 'si_lead_followup_schedule')) {
			$CI->db->query("ALTER TABLE `" . db_prefix() . "si_lead_followup_schedule` 
							 ADD `email_subject` varchar(255) NOT NULL DEFAULT '' AFTER `content`");
		   	$CI->db->query('UPDATE `' . db_prefix() . 'si_lead_followup_schedule` SET email_subject = "' . $email_subject . '"');

			//change email subject in all email templates
			$CI->db->where('type','si_lead_followup');
			$CI->db->where('slug','si-lead-followup-lead-followup-email');
			$CI->db->set('subject','{email_subject}');
			$CI->db->update(db_prefix() . 'emailtemplates');
		}
	}
}