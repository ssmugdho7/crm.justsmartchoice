<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_253 extends CI_Migration
{
    public function up()
    {
        $CI =& get_instance();
        $install = module_dir_path('engineering_projects', 'install.php');
        if (file_exists($install)) {
            require_once($install);
        }
        update_option('engproj_version_safe_migrated', '2.5.3');
    }

    public function down()
    {
        // Safe rollback intentionally empty.
    }
}
