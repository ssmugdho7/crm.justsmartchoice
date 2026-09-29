<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        if (!function_exists('smart_choice_links_ensure_database')) {
            require_once(__DIR__ . '/../smart_choice_links.php');
        }

        smart_choice_links_ensure_database();
        update_option('smart_choice_links_version', '1.0.1');
    }

    public function down()
    {
        // Rollback not implemented to protect user link data.
    }
}
