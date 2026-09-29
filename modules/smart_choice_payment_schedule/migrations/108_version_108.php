<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_108 extends App_module_migration
{
    public function up() { update_option('scps_version', '1.0.8'); }
    public function down() {}
}
