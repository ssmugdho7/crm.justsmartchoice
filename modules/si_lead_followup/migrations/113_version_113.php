<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_113 extends App_module_migration
{
	public function up()
	{ 
		//path for upload
		$path = get_upload_path_by_type('si_lead_followup');

		//add .htaccess file
		if (!file_exists($path.'.htaccess') && is_writable($path)) {
		(\function_exists('sc_recovery_fopen') ? '\sc_recovery_fopen' : '\fopen')($path . '.htaccess', 'w');
			$fp = (\function_exists('sc_recovery_fopen') ? '\sc_recovery_fopen' : '\fopen')($path.'.htaccess','a+');
			if($fp)
			{
				fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
				(\function_exists('sc_recovery_fclose') ? '\sc_recovery_fclose' : '\fclose')($fp);
			}
		}
	}
}