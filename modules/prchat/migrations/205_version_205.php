<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_205 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        update_option('prchat_smart_choice_upload_uses_crm_file_types', '1');
        update_option('prchat_smart_choice_upload_max_size', get_option('media_max_file_size_upload') ?: '512');
        update_option('prchat_smart_choice_project_media_enabled', '1');
        update_option('prchat_smart_choice_call_number_line_enabled', '1');
    }
}
