<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_415 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'sc_sales_translation_cache';
        if (!$this->db->table_exists($table)) {
            $this->db->query("CREATE TABLE `{$table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `cache_key` CHAR(64) NOT NULL,
                `target_language` VARCHAR(20) NOT NULL,
                `source_text` MEDIUMTEXT NULL,
                `translated_text` MEDIUMTEXT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `cache_language` (`cache_key`,`target_language`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
        // Repair older unpaid invoices that accidentally lost their allowed online gateway.
        if ((string) get_option('paymentmethod_stripe_active') === '1' && $this->db->table_exists(db_prefix().'invoices')) {
            $rows = $this->db->select('id,allowed_payment_modes')->where_in('status', [1,3,4,6])->get(db_prefix().'invoices')->result();
            foreach ($rows as $row) {
                $modes = @unserialize((string)$row->allowed_payment_modes);
                $modes = is_array($modes) ? $modes : [];
                if (count($modes) === 0) {
                    $this->db->where('id',(int)$row->id)->update(db_prefix().'invoices',['allowed_payment_modes'=>serialize(['stripe'])]);
                }
            }
        }
        update_option('sc_crm_build_version', '4.1.5');
        update_option('smart_choice_crm_build', '4.1.5');
        update_option('smart_choice_crm_current_version', '4.1.5');
    }
}
