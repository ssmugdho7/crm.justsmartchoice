<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_112 extends App_module_migration { public function up(){ require module_dir_path('google_meet').'install.php'; update_option('google_meet_version','1.1.2'); } public function down(){} }
