<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_220 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $options = [
            'prchat_sms_enabled' => '0',
            'prchat_sms_provider' => 'twilio',
            'prchat_sms_status_callback' => '',
            'prchat_ai_use_core_settings' => '1',
        ];
        foreach ($options as $name => $value) {
            if (get_option($name) === false) { add_option($name, $value); }
        }
        $table = db_prefix() . 'prchat_sms_log';
        if (!$CI->db->table_exists($table)) {
            $CI->db->query("CREATE TABLE `{$table}` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `staff_id` INT UNSIGNED NOT NULL,
              `recipient_staff_id` INT UNSIGNED NOT NULL,
              `to_number` VARCHAR(40) NOT NULL,
              `from_number` VARCHAR(40) NULL,
              `message` TEXT NOT NULL,
              `provider` VARCHAR(30) NOT NULL DEFAULT 'twilio',
              `provider_message_id` VARCHAR(191) NULL,
              `status` VARCHAR(30) NOT NULL DEFAULT 'queued',
              `error_message` TEXT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              KEY `recipient_staff_id` (`recipient_staff_id`),
              KEY `status` (`status`),
              KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
    }
}
