<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'hrp_ui_primary_color' => '#0e6f5b',
            'hrp_ui_secondary_color' => '#075f8f',
            'hrp_ui_table_header_bg' => '#0e6f5b',
            'hrp_ui_table_header_text' => '#ffffff',
            'hrp_ui_table_row_hover' => '#eef8f4',
            'hrp_ui_table_border_color' => '#d8e2df',
            'hrp_ui_table_font_size' => '13',
            'hrp_ui_rows_per_page' => '25',
            'hrp_ui_table_compact' => '0',
            'hrp_ui_table_striped' => '1',
            'hrp_ui_hide_select_column' => '0',
            'hrp_ui_hide_actions_column' => '0',
            'hrp_menu_employees' => '1',
            'hrp_menu_attendance' => '1',
            'hrp_menu_commissions' => '1',
            'hrp_menu_deductions' => '1',
            'hrp_menu_bonuses' => '1',
            'hrp_menu_insurance' => '1',
            'hrp_menu_payslips' => '1',
            'hrp_menu_templates' => '1',
            'hrp_menu_income_tax' => '1',
            'hrp_menu_reports' => '1',
        ];
        foreach ($defaults as $key => $value) {
            if (get_option($key) === '') {
                add_option($key, $value);
            }
        }
        update_option('hr_payroll_upgrade_notice_126', '1');
    }
    public function down() {}
}
