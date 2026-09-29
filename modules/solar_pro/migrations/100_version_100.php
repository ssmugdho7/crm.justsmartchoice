<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Historical v1.0.0 migration marker.
 *
 * The original v1.0.0 activation hook already created the schema and stored
 * installed_version 1.0.0 in tblmodules. This file completes the proper
 * Perfex semantic migration sequence (100 -> 101 -> 102) for future safety.
 */
class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('solar_pro', 'helpers/solar_pro_helper.php');
        $CI = &get_instance();
        solar_pro_install_schema($CI);
        solar_pro_install_options();
        solar_pro_seed_defaults($CI);
    }
}
