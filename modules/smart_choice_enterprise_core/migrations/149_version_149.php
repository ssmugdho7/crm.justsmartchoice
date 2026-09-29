<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_149 extends App_module_migration { public function up(){ require_once module_dir_path(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME,'libraries/Enterprise_schema.php'); Enterprise_schema::install(); } public function down(){} }
