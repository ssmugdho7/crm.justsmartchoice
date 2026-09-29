<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.2
 *
 * Advances the module version so Perfex runs the upgrade flow. No schema or
 * user data is changed. The release repairs only chat bootstrap behavior.
 */
class Migration_Version_222 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_build') === false) {
            add_option('prchat_build', '2.2.2');
        } else {
            update_option('prchat_build', '2.2.2');
        }

        if (get_option('prchat_version') === false) {
            add_option('prchat_version', '2.2.2');
        } else {
            update_option('prchat_version', '2.2.2');
        }
    }
}
