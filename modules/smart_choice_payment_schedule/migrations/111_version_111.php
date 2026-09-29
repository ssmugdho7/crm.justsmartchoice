<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_111 extends App_module_migration { public function up(){ $CI=&get_instance(); $t=db_prefix().'sc_payment_schedules'; if($CI->db->table_exists($t) && !$CI->db->field_exists('discount_description',$t)){ $CI->db->query("ALTER TABLE `{$t}` ADD `discount_description` TEXT NULL AFTER `discount_reason`"); } update_option('scps_version','1.1.1'); } public function down(){} }
