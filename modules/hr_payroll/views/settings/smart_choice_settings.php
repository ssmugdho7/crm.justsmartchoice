<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="hrp-smart-choice">
  <div class="hrp-page-heading"><div><h4><?php echo _l('hrp_payroll_hub_settings'); ?></h4><small><?php echo _l('hrp_settings_explanation'); ?></small></div><span class="label label-success">★★★★★</span></div>
  <div class="hrp-report-grid">
    <div class="hrp-report-card"><h5><?php echo _l('hrp_tax_configuration'); ?></h5><p>Federal withholding year: 2026<br>Florida individual income tax: 0%<br>FICA, Medicare, FUTA, reemployment tax, and workers compensation remain configurable.</p><a class="btn btn-info" href="<?php echo admin_url('hr_payroll/setting?group=income_tax_rates'); ?>"><?php echo _l('income_tax_rates'); ?></a></div>
    <div class="hrp-report-card"><h5><?php echo _l('hrp_prebuilt_catalogs'); ?></h5><p>Regular pay, overtime, PTO, per diem, mileage, commissions, performance incentives, insurance, garnishments, and construction deductions.</p><a class="btn btn-info" href="<?php echo admin_url('hr_payroll/setting?group=earnings_list'); ?>"><?php echo _l('earnings_list'); ?></a></div>
    <div class="hrp-report-card"><h5><?php echo _l('hrp_compliance_center'); ?></h5><p>Workers compensation classifications, insurance policies, payroll deductions, employee contributions, and 1099 tracking.</p><a class="btn btn-info" href="<?php echo admin_url('hr_payroll/setting?group=insurance_list'); ?>"><?php echo _l('insurance_list'); ?></a></div>
  </div>
  <div class="alert alert-warning mtop15"><?php echo _l('hrp_tax_advisory'); ?></div>
  <div class="hrp-toolbar">
    <a class="btn btn-default" href="<?php echo admin_url('hr_payroll/payslip_templates_manage'); ?>"><?php echo _l('hrp_template_library'); ?></a>
    <a class="btn btn-default" href="<?php echo admin_url('hr_payroll/reports'); ?>"><?php echo _l('hrp_reports'); ?></a>
    <a class="btn btn-default" href="<?php echo admin_url('hr_payroll/integrated_payees'); ?>"><?php echo _l('hrp_integrated_payees'); ?></a>
    <a class="btn btn-default" href="<?php echo admin_url('hr_payroll/payroll_hub_health'); ?>"><?php echo _l('hrp_health_check'); ?></a>
  </div>
</div>
