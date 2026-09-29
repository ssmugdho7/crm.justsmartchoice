<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_117 extends App_module_migration
{
	public function up()
	{
		$CI = &get_instance();   
		add_option('omni_show_public_page', 1);
	}


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
