<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_114 extends App_module_migration
{
    public function up(){ update_option('publishx_module_version','1.1.4'); update_option('publishx_enable_view_tracking','1'); }
    public function down(){}
}
