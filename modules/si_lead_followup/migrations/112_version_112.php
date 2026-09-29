<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_112 extends App_module_migration
{
	public function up()
	{ 
		$CI = &get_instance();
		if(!$CI->db->field_exists('email_attachment_id',db_prefix() . 'si_lead_followup_schedule')) {
			$CI->db->query('ALTER TABLE `' . db_prefix() . 'si_lead_followup_schedule` 
							 ADD `email_attachment_id` int(11) NOT NULL DEFAULT "0" AFTER `email_content`');
		}

		//add path for upload
		$path = get_upload_path_by_type('si_lead_followup');
		_maybe_create_upload_path($path);
	}
}