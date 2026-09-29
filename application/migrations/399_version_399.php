<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_399 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.9.9');
        update_option('smart_choice_pei_calculation_repair', '399');
        update_option('smart_choice_pei_unit_layout_repair', '399');
        update_option('smart_choice_sales_attachment_repair', '399');
    }

    public function down()
    {
        // Upgrade-only migration. Existing data and settings are preserved.
    }
}
