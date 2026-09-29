<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro v1.0.3
 *
 * Upgrade-only, non-destructive schema/default repair. Existing records,
 * settings and API credentials are preserved.
 */
class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('solar_pro', 'helpers/solar_pro_helper.php');
        $CI = &get_instance();
        solar_pro_install_schema($CI);
        solar_pro_install_options();
        solar_pro_seed_defaults($CI);
        add_option('solar_pro_schema_version', '1.0.3', 1);
        update_option('solar_pro_schema_version', '1.0.3');
    }
}
