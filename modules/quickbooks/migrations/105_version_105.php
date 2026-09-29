<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration {
	public function up() {
		// Perform database upgrade here
	}


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}