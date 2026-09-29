<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_100 extends App_module_migration{
 public function up(){
  add_option('master_crm_fix_pack_size_mb','480');
  add_option('master_crm_fix_include_database','1');
  add_option('master_crm_fix_exclude_archives','1');
  add_option('master_crm_fix_include_uploads','1');
  return true;
 }
 public function down(){return true;}
}
