<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_250 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('goals', 'install.php');
        update_option('goals_smart_choice_version', '2.5.0');
    }
}
