<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.4 upgrade.
 *
 * Advances the installed version after repairing the ChatZoomView bootstrap.
 * Existing messages, settings, uploads, credentials, and schema are preserved.
 */
class Migration_Version_224 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('prchat', 'install.php');

        if (get_option('prchat_build') === false) {
            add_option('prchat_build', '2.2.4');
        } else {
            update_option('prchat_build', '2.2.4');
        }

        if (get_option('prchat_version') === false) {
            add_option('prchat_version', '2.2.4');
        } else {
            update_option('prchat_version', '2.2.4');
        }
    }
}
