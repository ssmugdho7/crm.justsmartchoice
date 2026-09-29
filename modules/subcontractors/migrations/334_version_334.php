<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_334 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'smartsource_subcontractors';
        if ($CI->db->table_exists($table) && !$CI->db->field_exists('extra_answers', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `extra_answers` LONGTEXT NULL AFTER `notes`");
        }
        $defaults = [
            'smartsource_application_notify_staff_sms' => '0',
            'smartsource_telegram_enabled' => '0',
            'smartsource_telegram_bot_token' => '',
            'smartsource_telegram_chat_ids' => '',
            'smartsource_portal_primary_color' => '#F28C28',
            'smartsource_portal_secondary_color' => '#3598DB',
            'smartsource_portal_success_color' => '#169179',
            'smartsource_portal_logo_animation' => 'particles',
            'smartsource_portal_success_animation' => 'paper',
            'smartsource_portal_questions_json' => '[]',
        ];
        foreach ($defaults as $name => $value) {
            if (get_option($name) === false) { add_option($name, $value); }
        }
    }
}
