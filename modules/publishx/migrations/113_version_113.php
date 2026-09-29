<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('publishx', 'install.php');
        update_option('publishx_module_version', '1.1.3');
    }
    public function down() {}
}
