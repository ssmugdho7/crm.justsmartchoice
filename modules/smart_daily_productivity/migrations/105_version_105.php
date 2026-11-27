<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_105 extends App_module_migration {
 public function up(): void {
  $CI = &get_instance();
  $table = db_prefix() . 'sdp_reminders';
  if (!$CI->db->table_exists($table)) {
   $CI->db->query("CREATE TABLE `" . $table . "` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `staff_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(190) NOT NULL,
    `notes` TEXT NULL,
    `remind_at` DATETIME NOT NULL,
    `alarm_file` VARCHAR(40) NOT NULL DEFAULT 'alarm 1.mp3',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`), KEY `staff_remind_at` (`staff_id`,`remind_at`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }
  update_option('smart_daily_productivity_version', '1.0.5');
 }
 public function down() { return true; }
}
