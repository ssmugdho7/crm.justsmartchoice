<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Appointly 2.2.0 migration.
 *
 * Records the repair release without modifying or removing appointment data.
 */
class Migration_Version_220 extends App_module_migration
{
    public function up()
    {
        update_option('appointly_smart_choice_version', '2.2.0');
        update_option('appointly_sc_public_logo_width', '58');
        update_option('appointly_sc_public_logo_height', '21');
    }

    public function down()
    {
        update_option('appointly_smart_choice_version', '2.1.9');
        update_option('appointly_sc_public_logo_width', '88');
        update_option('appointly_sc_public_logo_height', '30');
    }
}
