<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.3 upgrade.
 *
 * Repairs the module schema/options idempotently and advances the installed
 * version without deleting or overwriting existing data or credentials.
 */
class Migration_Version_223 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('prchat', 'install.php');

        if (get_option('prchat_build') === false) {
            add_option('prchat_build', '2.2.3');
        } else {
            update_option('prchat_build', '2.2.3');
        }

        if (get_option('prchat_version') === false) {
            add_option('prchat_version', '2.2.3');
        } else {
            update_option('prchat_version', '2.2.3');
        }
    }
}
