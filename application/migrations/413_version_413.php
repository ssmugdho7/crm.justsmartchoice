<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_413 extends CI_Migration {
 public function up(){
  $t=db_prefix().'sc_sales_discussions';if(!$this->db->table_exists($t)){$this->db->query("CREATE TABLE `{$t}` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`rel_type` VARCHAR(20) NOT NULL,`rel_id` INT UNSIGNED NOT NULL,`author_name` VARCHAR(191) NOT NULL,`author_email` VARCHAR(191) NOT NULL,`message` TEXT NOT NULL,`created_at` DATETIME NOT NULL,`ip_address` VARCHAR(64) NULL,PRIMARY KEY (`id`),KEY `rel_idx` (`rel_type`,`rel_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");}
  update_option('smart_choice_crm_build','4.1.3');
 }
}
