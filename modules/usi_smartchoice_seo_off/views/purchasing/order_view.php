<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php if (empty($purchase_order)) { ?><div class="alert alert-warning">Purchase order not found.</div><?php } else { ?>
<h4 class="no-margin"><i class="fa fa-shopping-cart"></i> <?php echo html_escape($purchase_order['po_number']); ?> - <?php echo html_escape($purchase_order['title']); ?></h4>
<div class="btn-toolbar sc-toolbar mtop15">
  <a href="<?php echo admin_url('usi_smartchoice_seo/recalculate_purchase_order/' . (int)$purchase_order['id']); ?>" class="btn btn-success btn-sm"><i class="fa fa-calculator"></i> Recalculate</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/mark_purchase_order_ordered/' . (int)$purchase_order['id']); ?>" class="btn btn-info btn-sm"><i class="fa fa-send"></i> Mark Ordered</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/mark_purchase_order_received/' . (int)$purchase_order['id']); ?>" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Mark Received</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/purchase_order/' . (int)$purchase_order['id']); ?>" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Edit</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
</div><hr>
<div class="row">
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Status</strong><br><?php echo html_escape(str_replace('_', ' ', $purchase_order['status'])); ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Subtotal</strong><br>$<?php echo number_format((float)$purchase_order['subtotal'], 2); ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Tax</strong><br>$<?php echo number_format((float)$purchase_order['tax_total'], 2); ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Delivery</strong><br>$<?php echo number_format((float)$purchase_order['delivery_fee'], 2); ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Total</strong><br>$<?php echo number_format((float)$purchase_order['total'], 2); ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Vendor</strong><br><?php echo html_escape($vendor['vendor_name'] ?? 'Not Assigned'); ?></div></div>
</div>
<div class="row mtop20">
  <div class="col-md-6"><h5>Vendor Notes</h5><div class="well well-sm"><?php echo nl2br(html_escape($purchase_order['vendor_notes'])); ?></div></div>
  <div class="col-md-6"><h5>Internal Notes</h5><div class="well well-sm"><?php echo nl2br(html_escape($purchase_order['internal_notes'])); ?></div></div>
</div>
<h5>Purchase Order Lines</h5>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Item</th><th>Category</th><th>Unit</th><th>Qty</th><th>Unit Cost</th><th>Line Total</th><th>Source Note</th></tr></thead><tbody>
<?php if (!empty($lines)) { foreach ($lines as $line) { ?><tr><td><?php echo html_escape($line['item_name']); ?></td><td><?php echo html_escape($line['category']); ?></td><td><?php echo html_escape($line['unit']); ?></td><td><?php echo number_format((float)$line['quantity'], 2); ?></td><td>$<?php echo number_format((float)$line['unit_cost'], 2); ?></td><td>$<?php echo number_format((float)$line['line_total'], 2); ?></td><td><?php echo html_escape($line['vendor_source_note']); ?></td></tr><?php } } else { ?><tr><td colspan="7" class="text-center text-muted">No purchase order lines. Create this order from a calculated material takeoff to copy lines automatically.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
