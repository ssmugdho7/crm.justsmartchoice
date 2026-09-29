<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration {
	public function up() {
		$CI = & get_instance();
		if (!$CI->db->table_exists(db_prefix() . 'spreadsheet_online_sharing_details')) {
		  $CI->db->query('CREATE TABLE `' . db_prefix() . "spreadsheet_online_sharing_details` (
		      `id` int(11) NOT NULL AUTO_INCREMENT,
		      `share_id` INT(11) NOT NULL,
		      `type` VARCHAR(45) NOT NULL,
		      `value` VARCHAR(255) NOT NULL,
		      PRIMARY KEY (`id`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
		}

		if (!$CI->db->field_exists('is_read' ,db_prefix() . 'spreadsheet_online_hash_share')) {
		    $CI->db->query('ALTER TABLE `' . db_prefix() . 'spreadsheet_online_hash_share`
		      ADD COLUMN `is_read` int(1) NOT NULL DEFAULT 0,
		      ADD COLUMN `is_write` int(1) NOT NULL DEFAULT 0
		  ');
		}

		if (!$CI->db->field_exists('type' ,db_prefix() . 'spreadsheet_online_hash_share')) {
		    $CI->db->query('ALTER TABLE `' . db_prefix() . 'spreadsheet_online_hash_share`
		      ADD COLUMN `type` VARCHAR(45) NULL
		  ');
		}

		add_option('spreadsheet_online_convert_old_share', 0);
		convert_old_share();
	}
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
