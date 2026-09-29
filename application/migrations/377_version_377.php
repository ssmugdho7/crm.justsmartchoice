<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_377 extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists(db_prefix() . 'files')) {
            $this->db->where_in('rel_type', ['invoice', 'estimate', 'proposal']);
            $this->db->group_start()->where('visible_to_customer', 0)->or_where('visible_to_customer IS NULL', null, false)->group_end();
            $this->db->update(db_prefix() . 'files', ['visible_to_customer' => 1]);
        }
        update_option('smart_choice_document_rendering_version', '377');
    }

    public function down()
    {
        // Upgrade-only migration. Existing customer-visible files are preserved.
    }
}
