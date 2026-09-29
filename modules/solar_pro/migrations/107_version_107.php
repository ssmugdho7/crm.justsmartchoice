<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Solar Pro 1.0.7 - independent proposals/contracts, contract template seeding, signatures and proposal email template. */
class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('solar_pro', 'helpers/solar_pro_helper.php');
        $CI = &get_instance();
        solar_pro_install_schema($CI);
        solar_pro_install_options();
        solar_pro_seed_contract_templates($CI);
        solar_pro_seed_email_template($CI);
    }
}
