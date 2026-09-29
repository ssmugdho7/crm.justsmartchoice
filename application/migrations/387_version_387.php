<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_387 extends App_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->db->select_max('number', 'max_number');
        $CI->db->where('status !=', 6);
        $row = $CI->db->get(db_prefix() . 'invoices')->row();
        $next = ((int) ($row->max_number ?? 0)) + 1;
        if ($next < 1) {
            $next = 1;
        }
        if ((int) get_option('next_invoice_number') < $next) {
            update_option('next_invoice_number', $next);
        }
        update_option('smart_choice_crm_build', '3.8.7');
    }

    public function down()
    {
        // Upgrade-only migration. Existing invoice data is preserved.
    }
}
