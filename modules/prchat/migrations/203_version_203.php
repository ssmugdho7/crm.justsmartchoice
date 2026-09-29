<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_203 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        add_option('prchat_max_upload_size_kb', '204800');
        add_option('prchat_copy_group_uploads_to_project_media', '1');
        if (defined('PR_CHAT_MEDIA_PROJECTS_FOLDER') && !is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER)) {
            @mkdir(PR_CHAT_MEDIA_PROJECTS_FOLDER, 0755, true);
            @file_put_contents(PR_CHAT_MEDIA_PROJECTS_FOLDER . '/index.html', '');
        }
    }
}
