<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('zpme_is_activated', FALSE);
add_option('zpme_license_key', FALSE);
add_option('zpme_activated_at', FALSE);
add_option('zpme_last_validate', FALSE);
add_option('pme_migrated_database', FALSE);

if ( ! get_option('pme_migrated_database'))
{

	$CI = &get_instance();
	$table_exist = $CI->db->query("SHOW TABLES LIKE '%task_types'")->num_rows();

	if ($table_exist <= 0)
	{
		require_once APP_MODULES_PATH.'project_management_enhancements/migrations/100_version_100.php';

		$migration = new Migration_Version_100();
		$migration->up();
	}
	update_option('pme_migrated_database', TRUE);
}


