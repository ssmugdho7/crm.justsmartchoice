<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_245 extends App_module_migration { public function up() { update_option('goals_smart_choice_version', '2.4.5'); } }
