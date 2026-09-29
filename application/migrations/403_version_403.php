<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_403 extends CI_Migration
{
    public function up()
    {
        $table = db_prefix() . 'staff';
        $fields = [
            'instagram' => "VARCHAR(255) NULL",
            'tiktok' => "VARCHAR(255) NULL",
            'bank_account_number' => "VARCHAR(100) NULL",
            'bank_account_type' => "VARCHAR(30) NULL",
            'bank_routing_number' => "VARCHAR(50) NULL",
            'bank_swift_aba' => "VARCHAR(50) NULL",
            'bank_name' => "VARCHAR(255) NULL",
            'bank_website' => "VARCHAR(255) NULL",
            'bank_account_name' => "VARCHAR(255) NULL",
            'bank_account_address' => "VARCHAR(500) NULL",
            'employee_staff_id' => "VARCHAR(100) NULL",
            'emergency_contact_name' => "VARCHAR(255) NULL",
            'emergency_contact_relationship' => "VARCHAR(100) NULL",
            'emergency_contact_phone' => "VARCHAR(100) NULL",
            'start_date' => "DATE NULL",
            'employment_type' => "VARCHAR(50) NULL",
            'supervisor_staff_id' => "INT NULL",
            'team' => "VARCHAR(255) NULL",
            'probation_end_date' => "DATE NULL",
            'contract_expiration_date' => "DATE NULL",
        ];
        foreach ($fields as $name => $definition) {
            if (!$this->db->field_exists($name, $table)) {
                $this->db->query('ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . $definition);
            }
        }
        $defaults = [
            'sc_menu_icon_color' => '#169179',
            'sc_submenu_icon_color' => '#374151',
            'sc_menu_text_color' => '#334155',
            'sc_submenu_text_color' => '#4b5563',
            'sc_table_header_color' => '#3598DB',
            'sc_title_color' => '#1f2937',
            'sc_primary_button_color' => '#169179',
            'sc_admin_login_background' => '',
            'sc_client_login_background' => '',
            'sc_world_clocks' => json_encode([
                ['label'=>'Florida (Eastern)','zone'=>'America/New_York'],
                ['label'=>'India','zone'=>'Asia/Kolkata'],
                ['label'=>'Pakistan','zone'=>'Asia/Karachi'],
                ['label'=>'Philippines','zone'=>'Asia/Manila'],
                ['label'=>'Bangladesh','zone'=>'Asia/Dhaka'],
            ]),
        ];
        foreach ($defaults as $name => $value) {
            if (!option_exists($name)) { add_option($name, $value); }
        }
        update_option('smart_choice_crm_build', '4.0.3');
        update_option('smart_choice_crm_current_version', '4.0.3');
    }

    public function down()
    {
        // Production migrations are append-only. No destructive downgrade.
    }
}
