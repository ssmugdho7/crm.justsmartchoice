<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_106 extends App_module_migration
{
    public function up(): void
    {
        $CI = &get_instance();
        $items = db_prefix() . 'sdp_checklist_items';
        $checks = db_prefix() . 'sdp_checklist_checks';
        if (!$CI->db->table_exists($items)) {
            $CI->db->query("CREATE TABLE `{$items}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `staff_id` INT UNSIGNED NOT NULL, `title` VARCHAR(190) NOT NULL, `notes` TEXT NULL, `is_active` TINYINT(1) NOT NULL DEFAULT 1, `sort_order` INT NOT NULL DEFAULT 0, `created_at` DATETIME NOT NULL, PRIMARY KEY (`id`), KEY `staff_id` (`staff_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$CI->db->table_exists($checks)) {
            $CI->db->query("CREATE TABLE `{$checks}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `item_id` INT UNSIGNED NOT NULL, `staff_id` INT UNSIGNED NOT NULL, `check_date` DATE NOT NULL, `completed_at` DATETIME NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `item_day` (`item_id`,`check_date`), KEY `staff_day` (`staff_id`,`check_date`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        update_option('smart_daily_productivity_version', '1.0.6');
    }
    public function down() { return true; }
}
