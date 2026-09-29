<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="row purchasing-hub-cards">
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Open Accounts Payable</h4><strong><?php echo app_format_money($totals['open_bills'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Paid Vendor Bills</h4><strong><?php echo app_format_money($totals['paid_bills'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Purchase Orders</h4><strong><?php echo app_format_money($totals['purchase_orders'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Vendor Quotes</h4><strong><?php echo app_format_money($totals['vendor_quotes'], get_base_currency()); ?></strong></div></div></div>
</div>
<div class="panel_s"><div class="panel-body">
  <h3>Purchasing Hub</h3>
  <p>This clean build uses its own Purchasing Hub tables. It does not read the old automobile/item groups from other CRM modules.</p>
  <a href="<?php echo admin_url('purchasing_hub/items'); ?>" class="btn btn-primary">Manage Items</a>
  <a href="<?php echo admin_url('purchasing_hub/vendors'); ?>" class="btn btn-primary">Manage Vendors</a>
  <a href="<?php echo admin_url('purchasing_hub/reports'); ?>" class="btn btn-info">View Reports</a>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
