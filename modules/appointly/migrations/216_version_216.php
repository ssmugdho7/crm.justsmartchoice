<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_216 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $servicesTable = db_prefix() . 'appointly_services';

        if ($CI->db->table_exists($servicesTable)
            && !$CI->db->field_exists('sort_order', $servicesTable)) {
            $CI->db->query(
                "ALTER TABLE `{$servicesTable}` " .
                "ADD `sort_order` INT(11) NOT NULL DEFAULT 0"
            );
        }

        $services = [
            ['Free In-Home Consultation', 1],
            ['Remodeling & Renovation Planning Session', 2],
            ['Project Estimate & Scope Review', 3],
            ['Engineering / Permit Consultation', 4],
            ['Repair & Troubleshooting Visit', 5],
        ];

        if ($CI->db->table_exists($servicesTable)) {
            $fields = $CI->db->list_fields($servicesTable);

            foreach ($services as $service) {
                $name = $service[0];
                $sortOrder = $service[1];

                $existing = $CI->db
                    ->where('name', $name)
                    ->get($servicesTable)
                    ->row();

                if ($existing) {
                    if (in_array('sort_order', $fields, true)) {
                        $CI->db
                            ->where('id', $existing->id)
                            ->update($servicesTable, ['sort_order' => $sortOrder]);
                    }
                    continue;
                }

                $insert = [];
                $candidateValues = [
                    'name'          => $name,
                    'description'   => 'Smart Choice Contractors USA appointment service.',
                    'duration'      => 60,
                    'price'         => 0,
                    'color'         => '#169179',
                    'active'        => 1,
                    'sort_order'    => $sortOrder,
                    'buffer_before' => 0,
                    'buffer_after'  => 0,
                ];

                foreach ($candidateValues as $field => $value) {
                    if (in_array($field, $fields, true)) {
                        $insert[$field] = $value;
                    }
                }

                if (isset($insert['name'])) {
                    $CI->db->insert($servicesTable, $insert);
                }
            }
        }

        $options = [
            'appointly_sc_facebook_enabled'          => '0',
            'appointly_sc_facebook_page_token'       => '',
            'appointly_sc_test_notification_text'    => 'Smart Choice Appointments test notification.',
            'appointly_sc_on_the_way_message'        => 'Your Smart Choice professional {staff_firstname} {staff_lastname} is on the way. Company phone: {company_phone}.',
            'appointly_sc_ten_minutes_message'       => 'Your Smart Choice professional is approximately 10 minutes away.',
            'appointly_smart_choice_version'         => '2.1.6',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            }
        }
    }
}
