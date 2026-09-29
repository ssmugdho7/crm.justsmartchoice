<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration {
	public function up()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'task_comments MODIFY COLUMN content longtext');
	}

	public function down()
	{
	}
}