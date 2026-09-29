<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$hrpStandalone = isset($hrp_standalone_settings_form) && $hrp_standalone_settings_form;
if ($hrpStandalone) {
    echo form_open(admin_url('hr_payroll/payroll_hub_settings_save'), ['id' => 'hrp-payroll-hub-settings-form']);
}
?>
<div class="payroll-hub-settings payroll-hub-page hrp-native-settings-panel">
    <div class="ph-hero">
        <div>
            <h3><?php echo _l('payroll_hub_settings'); ?></h3>
            <p><?php echo _l('payroll_hub_settings_help'); ?></p>
        </div>
        <span class="ph-badge">Smart Choice Contractors USA</span>
    </div>

    <div class="alert alert-info hrp-settings-intro">
        <strong>Payroll Hub Configuration Center</strong><br>
        Save the everyday Payroll Hub options on this page. Use Advanced Payroll Configuration below for payroll columns, tax tables, deductions, insurance, integrations, permissions, payslip templates, currency rates, and data-management tools.
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ph-card">
                <h4><?php echo _l('payroll_general_settings'); ?></h4>
                <?php echo render_yes_no_option('hrp_show_crm_staff_id', 'show_crm_staff_id'); ?>
                <?php echo render_yes_no_option('hrp_use_crm_logo', 'use_crm_logo'); ?>
                <?php echo render_select(
                    'settings[hrp_default_payslip_language]',
                    [
                        ['id' => 'employee', 'name' => _l('employee_language')],
                        ['id' => 'english', 'name' => 'English'],
                        ['id' => 'spanish', 'name' => 'Español'],
                    ],
                    ['id', 'name'],
                    'default_payslip_language',
                    get_option('hrp_default_payslip_language')
                ); ?>
                <?php echo render_input('settings[hrp_default_payslip_note]', 'default_payslip_note', get_option('hrp_default_payslip_note')); ?>
                <?php echo render_select(
                    'settings[hrp_default_pay_frequency]',
                    [
                        ['id' => 'weekly', 'name' => _l('weekly')],
                        ['id' => 'biweekly', 'name' => _l('biweekly')],
                        ['id' => 'semimonthly', 'name' => _l('semi_monthly')],
                        ['id' => 'monthly', 'name' => _l('monthly')],
                    ],
                    ['id', 'name'],
                    'default_pay_frequency',
                    get_option('hrp_default_pay_frequency')
                ); ?>
                <?php echo render_input('settings[hrp_overtime_multiplier]', 'overtime_multiplier', get_option('hrp_overtime_multiplier'), 'number', ['step' => '0.01', 'min' => '0']); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ph-card">
                <h4><?php echo _l('payroll_integration_settings'); ?></h4>
                <?php echo render_yes_no_option('hrp_sync_sales_commissions', 'sync_sales_commissions'); ?>
                <?php echo render_yes_no_option('hrp_sync_subcontractor_payments', 'sync_subcontractor_payments'); ?>
                <?php echo render_yes_no_option('hrp_enable_1099_tracking', 'enable_1099_tracking'); ?>
                <?php echo render_yes_no_option('hrp_enable_project_job_costing', 'enable_project_job_costing'); ?>
                <?php echo render_yes_no_option('hrp_preserve_core_tables', 'preserve_core_tables'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ph-card">
                <h4><?php echo _l('florida_payroll_defaults'); ?></h4>
                <?php echo render_input('settings[hrp_federal_tax_year]', 'federal_tax_year', get_option('hrp_federal_tax_year'), 'number', ['min' => '2020', 'max' => '2100']); ?>
                <?php echo render_input('settings[hrp_florida_state_withholding_rate]', 'florida_state_withholding_rate', get_option('hrp_florida_state_withholding_rate'), 'number', ['step' => '0.01', 'min' => '0']); ?>
                <?php echo render_input('settings[hrp_social_security_rate]', 'social_security_rate', get_option('hrp_social_security_rate'), 'number', ['step' => '0.001', 'min' => '0']); ?>
                <?php echo render_input('settings[hrp_medicare_rate]', 'medicare_rate', get_option('hrp_medicare_rate'), 'number', ['step' => '0.001', 'min' => '0']); ?>
                <?php echo render_input('settings[hrp_futa_rate]', 'futa_rate', get_option('hrp_futa_rate'), 'number', ['step' => '0.001', 'min' => '0']); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ph-card">
                <h4><?php echo _l('insurance_and_workers_comp'); ?></h4>
                <?php echo render_yes_no_option('hrp_enable_workers_comp', 'enable_workers_comp'); ?>
                <?php echo render_input('settings[hrp_workers_comp_policy_number]', 'workers_comp_policy_number', get_option('hrp_workers_comp_policy_number')); ?>
                <?php echo render_input('settings[hrp_workers_comp_carrier]', 'workers_comp_carrier', get_option('hrp_workers_comp_carrier')); ?>
                <?php echo render_input('settings[hrp_general_liability_carrier]', 'general_liability_carrier', get_option('hrp_general_liability_carrier')); ?>
            </div>
        </div>
    </div>

    <div class="ph-card hrp-ui-settings">
        <h4><i class="fa-solid fa-palette"></i> Payroll Hub Appearance & Table Controls</h4>
        <p class="text-muted">These options affect Payroll Hub only and do not change the rest of the CRM.</p>
        <div class="row">
            <div class="col-md-4">
                <label>Primary / Menu Green</label>
                <input type="color" class="form-control" name="settings[hrp_ui_primary_color]" value="<?php echo html_escape(get_option('hrp_ui_primary_color') ?: '#0e6f5b'); ?>">
            </div>
            <div class="col-md-4">
                <label>Secondary Color</label>
                <input type="color" class="form-control" name="settings[hrp_ui_secondary_color]" value="<?php echo html_escape(get_option('hrp_ui_secondary_color') ?: '#075f8f'); ?>">
            </div>
            <div class="col-md-4">
                <label>Table Header Background</label>
                <input type="color" class="form-control" name="settings[hrp_ui_table_header_bg]" value="<?php echo html_escape(get_option('hrp_ui_table_header_bg') ?: '#0e6f5b'); ?>">
            </div>
        </div>
        <div class="row mtop15">
            <div class="col-md-4">
                <label>Table Header Text</label>
                <input type="color" class="form-control" name="settings[hrp_ui_table_header_text]" value="<?php echo html_escape(get_option('hrp_ui_table_header_text') ?: '#ffffff'); ?>">
            </div>
            <div class="col-md-4">
                <label>Table Row Hover</label>
                <input type="color" class="form-control" name="settings[hrp_ui_table_row_hover]" value="<?php echo html_escape(get_option('hrp_ui_table_row_hover') ?: '#eef8f4'); ?>">
            </div>
            <div class="col-md-4">
                <label>Table Border Color</label>
                <input type="color" class="form-control" name="settings[hrp_ui_table_border_color]" value="<?php echo html_escape(get_option('hrp_ui_table_border_color') ?: '#d8e2df'); ?>">
            </div>
        </div>
        <div class="row mtop15">
            <div class="col-md-4">
                <?php echo render_input('settings[hrp_ui_table_font_size]', 'Table Font Size (px)', get_option('hrp_ui_table_font_size') ?: '13', 'number', ['min'=>'10','max'=>'18']); ?>
            </div>
            <div class="col-md-4">
                <?php echo render_input('settings[hrp_ui_rows_per_page]', 'Default Rows Per Page', get_option('hrp_ui_rows_per_page') ?: '25', 'number', ['min'=>'5','max'=>'200']); ?>
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_ui_table_compact', 'Compact Payroll Tables'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_ui_table_striped', 'Striped Payroll Tables'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_ui_hide_select_column', 'Hide Selection Checkbox Column'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_ui_hide_actions_column', 'Hide Actions Column'); ?></div>
        </div>
    </div>

    <div class="ph-card hrp-menu-settings">
        <h4><i class="fa-solid fa-list-check"></i> Payroll Hub Menu Visibility</h4>
        <p class="text-muted">Turn sections on or off in the Payroll Hub sidebar. Disabling a menu entry does not delete its data.</p>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_employees', 'Employees'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_attendance', 'Attendance'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_commissions', 'Commissions'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_deductions', 'Deductions'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_bonuses', 'Bonuses'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_insurance', 'Insurance'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_payslips', 'Payslips'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_templates', 'Payslip Templates'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_income_tax', 'Income Taxes'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-4"><?php echo render_yes_no_option('hrp_menu_reports', 'Reports'); ?></div>
        </div>
    </div>

    <div class="ph-card hrp-advanced-settings">
        <div class="hrp-settings-card-heading">
            <div>
                <h4>Advanced Payroll Configuration</h4>
                <p>Open the complete Payroll Hub configuration screens. These are part of this module and remain available for detailed payroll administration.</p>
            </div>
            <a class="btn btn-primary" href="<?php echo admin_url('hr_payroll/setting'); ?>">
                <i class="fa-solid fa-gears"></i> Open All Payroll Settings
            </a>
        </div>

        <div class="hrp-settings-link-grid">
            <a href="<?php echo admin_url('hr_payroll/setting?group=payroll_columns'); ?>"><i class="fa-solid fa-table-columns"></i><span>Payroll Columns</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=income_tax_rates'); ?>"><i class="fa-solid fa-percent"></i><span>Income Tax Rates</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=income_tax_rebates'); ?>"><i class="fa-solid fa-receipt"></i><span>Tax Rebates</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=earnings_list'); ?>"><i class="fa-solid fa-money-bill-trend-up"></i><span>Earnings</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=salary_deductions_list'); ?>"><i class="fa-solid fa-money-bill-transfer"></i><span>Deductions</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=insurance_list'); ?>"><i class="fa-solid fa-shield-halved"></i><span>Insurance</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=company_contributions_list'); ?>"><i class="fa-solid fa-building-circle-check"></i><span>Company Contributions</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=pdf_payslip_template'); ?>"><i class="fa-solid fa-file-pdf"></i><span>Payslip PDF Templates</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=data_integration'); ?>"><i class="fa-solid fa-link"></i><span>Data Integration</span></a>
            <a href="<?php echo admin_url('hr_payroll/setting?group=currency_rates'); ?>"><i class="fa-solid fa-scale-balanced"></i><span>Currency Rates</span></a>
            <?php if (is_admin()) { ?>
                <a href="<?php echo admin_url('hr_payroll/setting?group=permissions'); ?>"><i class="fa-solid fa-user-shield"></i><span>Permissions</span></a>
                <a href="<?php echo admin_url('hr_payroll/setting?group=reset_data'); ?>"><i class="fa-solid fa-database"></i><span>Reset / Data Tools</span></a>
            <?php } ?>
        </div>
    </div>

    <div class="ph-card">
        <h4><?php echo _l('payroll_hub_data_policy'); ?></h4>
        <p><?php echo _l('payroll_hub_data_policy_help'); ?></p>
    </div>

    <div class="hrp-settings-save-bar">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> <?php echo _l('save'); ?> Payroll Hub Settings
        </button>
        <a href="<?php echo admin_url('hr_payroll/payroll_hub_health'); ?>" class="btn btn-default">
            <i class="fa-solid fa-heart-pulse"></i> Health Check
        </a>
    </div>
</div>

<?php if ($hrpStandalone) { echo form_close(); } ?>
