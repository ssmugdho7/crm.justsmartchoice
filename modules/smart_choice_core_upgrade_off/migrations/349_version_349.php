<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_349 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.4.9');
        update_option('smart_choice_enterprise_latest_version', '3.4.9');
        update_option('smart_choice_core_latest_version', '3.4.9');
        update_option('smart_choice_latest_version', '3.4.9');
        update_option('smart_choice_enterprise_update_status', 'current');
        update_option('smart_choice_core_upgrade_last_patch', '3.4.9');

        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix().'est_ai_items')) {
            $fields = [
                'cost_item_id' => "INT(11) NULL AFTER `estimate_id`",
                'division' => "VARCHAR(50) NULL AFTER `cost_item_id`",
                'trade' => "VARCHAR(100) NULL AFTER `division`",
                'category' => "VARCHAR(150) NULL AFTER `trade`",
                'unit' => "VARCHAR(30) NOT NULL DEFAULT 'each' AFTER `description`",
                'qty' => "DECIMAL(15,4) NOT NULL DEFAULT 0 AFTER `unit`",
                'material_cost' => "DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `qty`",
                'labor_hours' => "DECIMAL(15,4) NOT NULL DEFAULT 0 AFTER `material_cost`",
                'labor_rate' => "DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `labor_hours`",
                'equipment_cost' => "DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `labor_rate`",
                'waste_factor' => "DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER `equipment_cost`",
                'markup_percent' => "DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER `waste_factor`",
                'total' => "DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `markup_percent`",
                'source' => "VARCHAR(100) NULL AFTER `total`",
            ];
            foreach ($fields as $field => $definition) {
                if (!$CI->db->field_exists($field, db_prefix().'est_ai_items')) {
                    $CI->db->query('ALTER TABLE `'.db_prefix().'est_ai_items` ADD `'.$field.'` '.$definition);
                }
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
