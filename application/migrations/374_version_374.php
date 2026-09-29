<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_374 extends CI_Migration
{
    public function up()
    {
        $expenses = db_prefix() . 'expenses';
        if ($this->db->table_exists($expenses) && !$this->db->field_exists('receipt_url', $expenses)) {
            $this->db->query("ALTER TABLE `{$expenses}` ADD `receipt_url` VARCHAR(500) NULL DEFAULT NULL");
        }
        update_option('smart_choice_contract_signature_storage_repair', '1');
        update_option('smart_choice_portal_finishing_version', '374');
    }

    public function down()
    {
        // Upgrade-only migration. Existing data is preserved.
    }
}
