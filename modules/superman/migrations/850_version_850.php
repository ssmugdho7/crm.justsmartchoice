<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_850 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('superman') . 'install.php');
    }

    public function down()
    {
        // Safe rollback method required by Perfex. Superman does not drop tables automatically.
        return true;
    }
}
