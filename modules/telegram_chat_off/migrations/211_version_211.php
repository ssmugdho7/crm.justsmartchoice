<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_211 extends App_module_migration
{
    public function up()
    {
        // Perfex manages module migration state. Do not load a module-level
        // CodeIgniter migration configuration because it overrides the CRM core.
        update_option('telegram_chat_module_version', '2.1.1');
        return true;
    }

    public function down()
    {
        update_option('telegram_chat_module_version', '2.1.0');
        return true;
    }
}
