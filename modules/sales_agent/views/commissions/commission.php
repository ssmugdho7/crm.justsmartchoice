<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$id = isset($commission['id']) ? $commission['id'] : '';
$get = function($key, $default = '') use ($commission) { return isset($commission[$key]) ? $commission[$key] : $default; };
?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo $title; ?></h4>
            <hr class="hr-panel-heading" />
            <?php echo form_open(admin_url('sales_agent/commission/'.$id)); ?>
              <?php echo render_select('invoice_id', $invoices, ['id', ['prefix','number']], _l('invoice'), $get('invoice_id')); ?>
              <div class="row">
                <div class="col-md-6"><?php echo render_select('staff_id', $staff, ['staffid', ['firstname','lastname']], _l('sa_sales_rep_or_agent'), $get('staff_id')); ?></div>
                <div class="col-md-6"><?php echo render_input('customer_id', _l('client'), $get('customer_id'), 'number'); ?></div>
              </div>
              <div class="row">
                <div class="col-md-6"><?php echo render_input('invoice_total', _l('invoice_amount'), $get('invoice_total'), 'number', ['step'=>'0.01']); ?></div>
                <div class="col-md-6"><?php echo render_input('base_amount', _l('sa_base_amount'), $get('base_amount'), 'number', ['step'=>'0.01']); ?></div>
              </div>
              <div class="row">
                <div class="col-md-4"><?php echo render_select('commission_type', [['id'=>'percentage','name'=>_l('sa_percentage')],['id'=>'fixed','name'=>_l('sa_fixed_amount')]], ['id','name'], _l('sa_commission_type'), $get('commission_type','percentage')); ?></div>
                <div class="col-md-4"><?php echo render_input('commission_rate', _l('sa_commission_rate'), $get('commission_rate', get_option('sa_default_commission_rate')), 'number', ['step'=>'0.0001']); ?></div>
                <div class="col-md-4"><?php echo render_input('expense_deduction', _l('sa_expense_deduction'), $get('expense_deduction', 0), 'number', ['step'=>'0.01']); ?></div>
              </div>
              <?php echo render_select('status', [['id'=>'pending','name'=>_l('sa_pending')],['id'=>'approved','name'=>_l('sa_approved')],['id'=>'paid','name'=>_l('sa_paid')]], ['id','name'], _l('status'), $get('status','pending')); ?>
              <?php echo render_date_input('payment_date', _l('payment_date'), $get('payment_date')); ?>
              <?php echo render_input('payment_reference', _l('payment_reference'), $get('payment_reference')); ?>
              <?php echo render_textarea('notes', _l('notes'), $get('notes')); ?>
              <div class="alert alert-info"><?php echo _l('sa_commission_construction_hint'); ?></div>
              <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
              <a href="<?php echo admin_url('sales_agent/commissions'); ?>" class="btn btn-default"><?php echo _l('back'); ?></a>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body></html>
