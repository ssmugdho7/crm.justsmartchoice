<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.7 upgrade.
 *
 * Restores the missing Pusher call-offer event bindings that prevented the
 * full-page and floating chat JavaScript from parsing. Also forces fresh
 * module assets after upgrade. Existing messages, settings, AI, voice,
 * uploads, contacts, groups, and client data are preserved.
 */
class Migration_Version_227 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('prchat', 'install.php');

        update_option('prchat_build', '2.2.7');
        update_option('prchat_version', '2.2.7');
    }

    public function down()
    {
        // No destructive rollback. Existing chat data must remain intact.
    }
}
