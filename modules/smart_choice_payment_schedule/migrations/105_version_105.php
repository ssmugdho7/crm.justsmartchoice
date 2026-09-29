<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_105 extends App_module_migration
{
    public function up() { update_option('scps_version', '1.0.5'); }
    public function down() {}
}
