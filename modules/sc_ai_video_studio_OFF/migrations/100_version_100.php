<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up(): void
    {
        require_once module_dir_path('sc_ai_video_studio', 'helpers/sc_ai_video_studio_helper.php');
        sc_ai_video_studio_ensure_schema();
        sc_ai_video_studio_seed_defaults();
    }

    public function down(): void
    {
    }
}
