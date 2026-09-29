<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_363 extends CI_Migration {
 public function up(){
  update_option('smart_choice_crm_build','3.6.3 SC');
  update_option('smart_choice_core_upgrade_applied','363');
 }
 public function down(){}
}
