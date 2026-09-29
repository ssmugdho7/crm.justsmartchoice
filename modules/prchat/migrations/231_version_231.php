<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.3.1 - per-viewer message visibility for scoped deletion.
 *
 * Non-destructive: existing chat message rows, uploads, settings and API
 * credentials are preserved. The table stores only viewer-specific hides.
 */
class Migration_Version_231 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'prchat_message_visibility';
        if (!$CI->db->table_exists($table)) {
            $CI->db->query("CREATE TABLE `{$table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `message_type` VARCHAR(16) NOT NULL,
                `message_id` INT UNSIGNED NOT NULL,
                `viewer_type` VARCHAR(16) NOT NULL,
                `viewer_id` INT UNSIGNED NOT NULL,
                `created_by` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_visibility` (`message_type`,`message_id`,`viewer_type`,`viewer_id`),
                KEY `viewer_lookup` (`viewer_type`,`viewer_id`,`message_type`,`message_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
    }

    public function down()
    {
        // Upgrade-only. Do not remove visibility data automatically.
    }
}
