<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('telegram_chat', 'install.php'));
        add_option('telegram_chat_module_version', '2.1.0');
        return true;
    }

    public function down()
    {
        return true;
    }
}
