<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_102 extends App_module_migration {
 public function up() {
  require __DIR__.'/../install.php';
  $CI = &get_instance();
  if (!$CI->db->field_exists('assigned_staff_id',db_prefix().'dmn')) throw new RuntimeException('DMN schema upgrade failed');
  set_alert('success', _l('dmn_upgrade_success'));
  return true;
 }
 public function down() { return true; }
}
