<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_420 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix().'sc_staff_idle_logs';
        if (!$this->db->table_exists($table)) {
            $this->db->query("CREATE TABLE `{$table}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`staff_id` INT NOT NULL,`idle_minutes` INT NOT NULL DEFAULT 10,`recorded_at` DATETIME NOT NULL,`ip_address` VARCHAR(64) NULL,`user_agent` VARCHAR(255) NULL,PRIMARY KEY (`id`),KEY `staff_id` (`staff_id`),KEY `recorded_at` (`recorded_at`)) ENGINE=InnoDB DEFAULT CHARSET=".$this->db->char_set);
        }
        update_option('sc_crm_build_version','4.2.0');
        update_option('smart_choice_crm_build','4.2.0');
        update_option('smart_choice_crm_current_version','4.2.0');
    }
    public function down() { /* Non-destructive rollback: preserve idle audit records. */ }
}
