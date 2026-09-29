<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_407 extends CI_Migration
{
    public function up()
    {
        $table=db_prefix().'sc_sales_links';
        if(!$this->db->table_exists($table)) {
            $this->db->query("CREATE TABLE `{$table}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`rel_type` VARCHAR(30) NOT NULL,`rel_id` INT UNSIGNED NOT NULL,`title` VARCHAR(191) NULL,`url` TEXT NOT NULL,`sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 1,PRIMARY KEY (`id`),KEY `rel_lookup` (`rel_type`,`rel_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
        update_option('smart_choice_crm_build','4.0.7');
        update_option('smart_choice_crm_current_version','4.0.7');
    }
    public function down() {}
}
