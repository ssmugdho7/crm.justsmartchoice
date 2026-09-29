<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_124 extends App_module_migration
{
    public function up(): void
    {
        require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php');
        usi_smartchoice_seo_ensure_schema();
    }

    public function down(): void
    {
        // Preserve production video studio data on downgrade. Manual cleanup is handled by uninstall only.
    }
}
