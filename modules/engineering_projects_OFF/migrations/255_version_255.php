<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_255 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        $install = FCPATH . 'modules/engineering_projects/install.php';
        if (file_exists($install)) { require_once($install); }
        update_option('engproj_version_safe_migrated', '2.5.5');
        return true;
    }
}
