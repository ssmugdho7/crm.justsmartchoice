<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.6 upgrade.
 *
 * Repairs database-first staff, group, and client loading and keeps chat
 * operational when Pusher is unavailable or slow. Existing data and settings
 * are preserved.
 */
class Migration_Version_226 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('prchat', 'install.php');

        update_option('prchat_build', '2.2.6');
        update_option('prchat_version', '2.2.6');
    }
}
