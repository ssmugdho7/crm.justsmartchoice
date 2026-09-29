<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_350 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.5.0');
        update_option('smart_choice_enterprise_latest_version', '3.5.0');
        update_option('smart_choice_core_latest_version', '3.5.0');
        update_option('smart_choice_latest_version', '3.5.0');
        update_option('smart_choice_core_upgrade_last_patch', '3.5.0');
        update_option('smart_choice_enterprise_update_status', 'current');
        update_option('smart_choice_core_toast_style', '1');
        update_option('smart_choice_core_table_tools', '1');

        $CI = &get_instance();

        // PR Chat group creator visibility + administrator edit safety.
        if ($CI->db->table_exists(db_prefix().'chatgroups')) {
            if (!$CI->db->field_exists('smart_choice_admin_editable', db_prefix().'chatgroups')) {
                $CI->db->query('ALTER TABLE `'.db_prefix().'chatgroups` ADD `smart_choice_admin_editable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `created_by_id`');
            }
            $CI->db->query('UPDATE `'.db_prefix().'chatgroups` SET `smart_choice_admin_editable` = 1 WHERE `smart_choice_admin_editable` IS NULL OR `smart_choice_admin_editable` = 0');
        }

        // Ensure common Smart Choice options exist.
        $defaults = [
            'smart_choice_staff_table_compact' => '1',
            'smart_choice_client_header_original' => '1',
            'smart_choice_global_toast_master' => '1',
            'smart_choice_purchase_use_crm_items' => '1',
        ];
        foreach ($defaults as $key => $value) {
            if (get_option($key) === '') {
                add_option($key, $value);
            } else {
                update_option($key, $value);
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
