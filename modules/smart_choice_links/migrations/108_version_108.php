<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        if (!function_exists('smart_choice_links_ensure_database')) {
            require_once(__DIR__ . '/../smart_choice_links.php');
        }

        smart_choice_links_ensure_database();
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
