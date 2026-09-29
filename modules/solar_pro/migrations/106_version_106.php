<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro 1.0.6 - native contracts, proposal/report finance, portal uploads,
 * analysis-method selection and schema repair. Upgrade only; no destructive SQL.
 */
class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('solar_pro', 'helpers/solar_pro_helper.php');
        $CI = &get_instance();
        solar_pro_install_schema($CI);
        solar_pro_install_options();
    }
}
