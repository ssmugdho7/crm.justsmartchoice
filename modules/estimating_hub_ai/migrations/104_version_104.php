<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_104 extends App_module_migration {
    public function up(){
        require_once(module_dir_path('estimating_hub_ai').'install.php');
        estimating_hub_ai_add_missing_columns();
        if(function_exists('estimating_hub_ai_seed_expanded_cost_items')) estimating_hub_ai_seed_expanded_cost_items();
        if(function_exists('estimating_hub_ai_seed_labor_templates_v104')) estimating_hub_ai_seed_labor_templates_v104();
        foreach(['uploads/training','uploads/photos','uploads/documents','uploads/exports'] as $d){
            $path=module_dir_path('estimating_hub_ai').$d;
            if(!is_dir($path)) @mkdir($path,0755,true);
            if(!file_exists($path.'/index.html')) @file_put_contents($path.'/index.html','');
        }
        update_option('estimating_hub_ai_version','1.0.4');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
