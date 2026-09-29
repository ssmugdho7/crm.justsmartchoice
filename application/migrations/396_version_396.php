<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_396 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.9.6');
        update_option('smart_choice_sales_calculation_repair', '396');
    }
    public function down()
    {
        delete_option('smart_choice_sales_calculation_repair');
    }
}
