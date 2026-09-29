<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        $CI=&get_instance();
        if (get_option('solar_pro_car_co2_tons_year') === false) { add_option('solar_pro_car_co2_tons_year','4.6'); }
        if (get_option('solar_pro_utility_escalation_pct') === false) { add_option('solar_pro_utility_escalation_pct','3'); }
        $CI->load->helper('solar_pro/solar_pro'); solar_pro_install_proposal_assets();
    }
}
