<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_100 extends App_module_migration {public function up(){add_option('smart_choice_crm_manager_version','1.0.0');return true;}public function down(){return true;}}
