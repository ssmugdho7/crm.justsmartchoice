<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_202 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('telegram_chat', 'install.php');
        return true;
    }

    public function down()
    {
        return true;
    }
}
