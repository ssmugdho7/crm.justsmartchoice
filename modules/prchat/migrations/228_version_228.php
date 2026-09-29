<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.8 upgrade.
 *
 * This release contains JavaScript/UI-only repairs for continuous voice-to-text
 * dictation and floating-chat maximize navigation. No schema installation or
 * repair work is required here.
 *
 * IMPORTANT: Do not include install.php from this migration. Running the full
 * installer during a normal module upgrade can perform unnecessary schema,
 * directory, option, and seed checks and can cause the Perfex module upgrade
 * request to remain loading on larger production databases.
 */
class Migration_Version_228 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_build') === false) {
            add_option('prchat_build', '2.2.8');
        } else {
            update_option('prchat_build', '2.2.8');
        }

        if (get_option('prchat_version') === false) {
            add_option('prchat_version', '2.2.8');
        } else {
            update_option('prchat_version', '2.2.8');
        }
    }

    public function down()
    {
        // No destructive rollback. Existing chat data must remain intact.
    }
}
