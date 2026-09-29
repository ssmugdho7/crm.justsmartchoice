<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_398 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.9.8');
        update_option('smart_choice_sales_calculation_repair', '398');
        update_option('smart_choice_optional_items_repair', '398');
        update_option('smart_choice_attachment_preview_repair', '398');
    }
}
