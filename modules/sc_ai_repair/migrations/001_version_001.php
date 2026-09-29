<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_001 extends App_module_migration {
 public function up(){
  $CI=&get_instance(); $p=db_prefix();
  $queries=[
   "CREATE TABLE IF NOT EXISTS `{$p}sc_ai_repair_runs` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`staff_id` INT UNSIGNED NOT NULL DEFAULT 0,`type` VARCHAR(30) NOT NULL,`status` VARCHAR(30) NOT NULL,`request_text` LONGTEXT NULL,`summary` LONGTEXT NULL,`created_at` DATETIME NOT NULL,`completed_at` DATETIME NULL,PRIMARY KEY (`id`),KEY `status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
   "CREATE TABLE IF NOT EXISTS `{$p}sc_ai_repair_changes` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`run_id` INT UNSIGNED NOT NULL,`path` VARCHAR(500) NOT NULL,`operation` VARCHAR(30) NOT NULL,`before_hash` CHAR(64) NULL,`after_hash` CHAR(64) NULL,`backup_path` VARCHAR(500) NULL,`diff_text` LONGTEXT NULL,`applied` TINYINT(1) NOT NULL DEFAULT 0,`rolled_back` TINYINT(1) NOT NULL DEFAULT 0,`created_at` DATETIME NOT NULL,PRIMARY KEY (`id`),KEY `run_id` (`run_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
   "CREATE TABLE IF NOT EXISTS `{$p}sc_ai_repair_findings` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`run_id` INT UNSIGNED NOT NULL,`severity` VARCHAR(20) NOT NULL,`category` VARCHAR(50) NOT NULL,`path` VARCHAR(500) NULL,`message` TEXT NOT NULL,`solution` LONGTEXT NULL,PRIMARY KEY (`id`),KEY `run_id` (`run_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
   "CREATE TABLE IF NOT EXISTS `{$p}sc_ai_repair_versions` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`version` VARCHAR(30) NOT NULL,`run_id` INT UNSIGNED NULL,`notes` TEXT NULL,`created_at` DATETIME NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `version` (`version`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  ]; foreach($queries as $q){$CI->db->query($q);} 
 }
 public function down(){ /* Upgrade-only. Preserve repair history and backups. */ }
}
