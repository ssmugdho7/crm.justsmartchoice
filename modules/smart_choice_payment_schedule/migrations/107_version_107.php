<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_107 extends App_module_migration
{
    public function up() { update_option('scps_version', '1.0.7'); }
    public function down() {}
}
