<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_129 extends App_module_migration{public function up(){update_option('cabinet_maker_version','1.2.9');update_option('cabinet_maker_enabled','1');}public function down(){}}
