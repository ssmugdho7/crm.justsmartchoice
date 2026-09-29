<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('estimating_hub_ai').'install.php');
        estimating_hub_ai_install(false);
        update_option('estimating_hub_ai_version','1.0.8');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
