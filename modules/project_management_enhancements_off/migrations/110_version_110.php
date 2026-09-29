<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration {
	public function up()
	{
		$this->ci->db->where('editable', 0);
		$this->ci->db->delete(db_prefix().'task_types');
	}

	public function down()
	{
	}
}