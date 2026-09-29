<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** Solar Pro 1.1.0 (Perfex module migration 110). */
class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI=&get_instance();
        if (get_option('solar_pro_appointment_url') === false) { add_option('solar_pro_appointment_url',''); }
        $CI->load->helper('solar_pro/solar_pro');
        solar_pro_install_proposal_assets();
    }
}
