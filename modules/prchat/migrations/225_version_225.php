<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.5 upgrade.
 *
 * Repairs staff, group, and client list population and numeric staff-row
 * selection without deleting or overwriting existing data or credentials.
 */
class Migration_Version_225 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('prchat', 'install.php');

        if (get_option('chat_show_only_users_with_chat_permissions') === false) {
            add_option('chat_show_only_users_with_chat_permissions', '0');
        }

        if (get_option('prchat_build') === false) {
            add_option('prchat_build', '2.2.5');
        } else {
            update_option('prchat_build', '2.2.5');
        }

        if (get_option('prchat_version') === false) {
            add_option('prchat_version', '2.2.5');
        } else {
            update_option('prchat_version', '2.2.5');
        }
    }
}
