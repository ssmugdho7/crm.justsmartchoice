<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_386 extends App_migration
{
    public function up()
    {
        // Preserve schema/data. Repair email deduplication, staff merge fields,
        // sales item decimal input handling, and project upload filename behavior.
        if ($this->db->table_exists(db_prefix() . 'sc_email_dedupe')) {
            $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-180 days')))->delete(db_prefix() . 'sc_email_dedupe');
        }
    }
}
