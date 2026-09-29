<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_207 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'chatgroups') && !$CI->db->field_exists('group_image', db_prefix() . 'chatgroups')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "chatgroups` ADD `group_image` VARCHAR(255) NULL DEFAULT NULL AFTER `group_name`");
        }

        $options = [
            'prchat_smart_message_sound_enabled' => '1',
            'prchat_smart_center_toast_enabled' => '1',
            'prchat_smart_task_from_message_enabled' => '1',
            'prchat_smart_project_photo_actions_enabled' => '1',
            'prchat_twilio_enabled' => '0',
            'prchat_twilio_account_sid' => '',
            'prchat_twilio_auth_token' => '',
            'prchat_twilio_phone_number' => '',
            'prchat_twilio_voice_webhook' => '',
            'prchat_twilio_video_enabled' => '0',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            }
        }
    }
}
