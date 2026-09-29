<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_122 extends App_module_migration
{
    public function up()
    {
        $options = [
            'hrp_show_crm_staff_id' => '1',
            'hrp_use_crm_logo' => '1',
            'hrp_default_payslip_language' => 'employee',
            'hrp_default_payslip_note' => 'Thank you for your professional service.',
            'hrp_default_pay_frequency' => 'biweekly',
            'hrp_overtime_multiplier' => '1.5',
            'hrp_sync_sales_commissions' => '1',
            'hrp_sync_subcontractor_payments' => '1',
            'hrp_enable_1099_tracking' => '1',
            'hrp_enable_project_job_costing' => '1',
            'hrp_preserve_core_tables' => '1',
            'hrp_federal_tax_year' => '2026',
            'hrp_florida_state_withholding_rate' => '0',
            'hrp_social_security_rate' => '6.2',
            'hrp_medicare_rate' => '1.45',
            'hrp_futa_rate' => '0.6',
            'hrp_enable_workers_comp' => '1',
            'hrp_workers_comp_policy_number' => '',
            'hrp_workers_comp_carrier' => '',
            'hrp_general_liability_carrier' => '',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            }
        }

        update_option('hr_payroll_version', '1.2.2');
        if (get_option('hr_payroll_upgrade_notice_122') === '') {
            add_option('hr_payroll_upgrade_notice_122', '1');
        } else {
            update_option('hr_payroll_upgrade_notice_122', '1');
        }
    }

    public function down()
    {
    }
}
