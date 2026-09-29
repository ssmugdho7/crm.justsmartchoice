<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_860 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once(module_dir_path('superman') . 'helpers/superman_seed.php');
        superman_seed_default_mappings($CI);
    }

    public function down()
    {
        return true;
    }
}
