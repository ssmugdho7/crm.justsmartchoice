<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_378 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_sales_attachment_upgrade', '378');
        update_option('smart_choice_customer_books_layout', '378');
        update_option('smart_choice_email_dedupe_upgrade', '378');

        if ($this->db->table_exists(db_prefix() . 'sc_email_dedupe')) {
            $this->db->query(
                'DELETE FROM `' . db_prefix() . 'sc_email_dedupe` '
                . 'WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 7 DAY)'
            );
        }
    }

    public function down()
    {
        // Upgrade-only migration. Existing customer data and attachments are preserved.
    }
}
