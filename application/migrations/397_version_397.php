<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_397 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.9.7');
        update_option('smart_choice_sales_calculation_repair', '397');
        update_option('smart_choice_sidebar_repair', '397');
    }
}
