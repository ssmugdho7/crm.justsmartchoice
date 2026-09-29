<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_218 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'appointly_services';

        if (!$CI->db->table_exists($table)) {
            return;
        }

        if (!$CI->db->field_exists('sort_order', $table)) {
            $CI->db->query(
                'ALTER TABLE `' . $table . '` ADD `sort_order` INT(11) NOT NULL DEFAULT 0 AFTER `active`'
            );
        }

        $services = [
            ['Free In-Home Consultation', '#169179', 1],
            ['Remodeling & Renovation Planning Session', '#3598db', 2],
            ['Project Estimate & Scope Review', '#f59e0b', 3],
            ['Engineering / Permit Consultation', '#8b5cf6', 4],
            ['Repair & Troubleshooting Visit', '#ef4444', 5],
        ];

        foreach ($services as $service) {
            $row = $CI->db->where('name', $service[0])->get($table)->row();
            if ($row) {
                $CI->db->where('id', $row->id)->update($table, [
                    'color' => $service[1],
                    'sort_order' => $service[2],
                    'active' => 1,
                ]);
            }
        }

        $activeRows = $CI->db->select('id')->where('active', 1)->order_by('sort_order', 'ASC')->order_by('id', 'ASC')->get($table)->result_array();
        $activeIds = array_map(static function ($row) {
            return (string) $row['id'];
        }, $activeRows);
        update_option('appointments_booking_services_availability', json_encode($activeIds));
    }
}
