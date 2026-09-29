<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_379 extends CI_Migration
{
    public function up()
    {
        // Finalize the 3.7.8 upgrade after the corrected 378 core migration.
        update_option('smart_choice_sales_attachment_upgrade', '379');
        update_option('smart_choice_customer_books_layout', '379');
        update_option('smart_choice_email_dedupe_upgrade', '379');
        update_option('smart_choice_core_upgrade_applied', '379');
        update_option('smart_choice_crm_build', '3.7.9 SC');
    }

    public function down()
    {
        // Upgrade-only migration. Existing CRM data is preserved.
    }
}
