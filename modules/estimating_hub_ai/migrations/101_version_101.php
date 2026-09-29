<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_101 extends App_module_migration {
    public function up(){
        require_once(module_dir_path('estimating_hub_ai').'install.php');
        estimating_hub_ai_install();
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
