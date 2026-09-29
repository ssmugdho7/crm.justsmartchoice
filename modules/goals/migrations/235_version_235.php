<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_235 extends App_module_migration { public function up() { update_option('goals_smart_choice_version', '2.3.5'); } }
