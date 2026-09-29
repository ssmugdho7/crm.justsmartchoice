<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration {
	public function up()
	{
		$this->ci->db->query("ALTER TABLE ".db_prefix()."task_comments ADD COLUMN `parent_id` INT DEFAULT NULL AFTER `taskid` ;");

	}

	public function down()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'task_comments DROP COLUMN `parent_id`');
	}
}