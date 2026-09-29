<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_140 extends App_module_migration {
 public function up(){
  update_option('smart_choice_core_enhancements_version','1.4.0');
  update_option('smart_choice_core_upgrade_applied','140');
 }
}
