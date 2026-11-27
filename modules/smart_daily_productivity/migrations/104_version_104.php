<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_104 extends App_module_migration {
 public function up(): void { update_option('smart_daily_productivity_version', '1.0.4'); }
 public function down() { return true; }
}
