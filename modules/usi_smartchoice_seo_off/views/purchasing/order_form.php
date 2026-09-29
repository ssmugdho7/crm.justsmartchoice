<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-shopping-cart"></i> <?php echo html_escape($title); ?></h4><hr>
<?php echo form_open(current_url()); ?>
<div class="row">
  <div class="col-md-4"><?php echo render_input('po_number', 'PO Number', $purchase_order['po_number'] ?? ''); ?></div>
  <div class="col-md-8"><?php echo render_input('title', 'Title', $purchase_order['title'] ?? ''); ?></div>
</div>
<div class="row">
  <div class="col-md-4"><label>Vendor</label><select name="vendor_id" class="form-control"><option value="0">Not Assigned</option><?php foreach($vendors as $vendor){ ?><option value="<?php echo (int)$vendor['id']; ?>" <?php echo ((int)($purchase_order['vendor_id'] ?? 0)===(int)$vendor['id']?'selected':''); ?>><?php echo html_escape($vendor['vendor_name']); ?></option><?php } ?></select></div>
  <div class="col-md-4"><label>Material Takeoff</label><select name="takeoff_id" class="form-control"><option value="0">Not Assigned</option><?php foreach($takeoffs as $takeoff){ ?><option value="<?php echo (int)$takeoff['id']; ?>" <?php echo ((int)($purchase_order['takeoff_id'] ?? 0)===(int)$takeoff['id']?'selected':''); ?>><?php echo html_escape($takeoff['title']); ?></option><?php } ?></select></div>
  <div class="col-md-4"><label>Status</label><select name="status" class="form-control"><?php foreach(['draft'=>'Draft','ready_to_order'=>'Ready To Order','ordered'=>'Ordered','received'=>'Received','cancelled'=>'Cancelled'] as $k=>$v){ ?><option value="<?php echo $k; ?>" <?php echo (($purchase_order['status'] ?? 'draft')===$k?'selected':''); ?>><?php echo $v; ?></option><?php } ?></select></div>
</div>
<div class="row">
  <div class="col-md-3"><?php echo render_input('ai_estimate_id', 'AI Estimate ID', $purchase_order['ai_estimate_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('customer_id', 'Customer ID', $purchase_order['customer_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('project_id', 'Project ID', $purchase_order['project_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('requested_date', 'Requested Date', $purchase_order['requested_date'] ?? '', 'date'); ?></div>
</div>
<div class="row">
  <div class="col-md-8"><?php echo render_input('delivery_address', 'Delivery Address', $purchase_order['delivery_address'] ?? ''); ?></div>
  <div class="col-md-4"><?php echo render_input('delivery_fee', 'Delivery Fee', $purchase_order['delivery_fee'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
</div>
<?php echo render_textarea('vendor_notes', 'Vendor Notes', $purchase_order['vendor_notes'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('internal_notes', 'Internal Notes', $purchase_order['internal_notes'] ?? '', ['rows'=>5]); ?>
<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Purchase Order</button>
<a href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
