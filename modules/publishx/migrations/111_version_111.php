<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('publishx', 'install.php');
    }

    public function down()
    {
        return true;
    }
}
