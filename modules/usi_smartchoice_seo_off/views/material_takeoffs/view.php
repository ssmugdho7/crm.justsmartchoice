<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php if (empty($takeoff)) { ?><div class="alert alert-warning">Material takeoff not found.</div><?php } else { ?>
<h4 class="no-margin"><i class="fa fa-cubes"></i> <?php echo html_escape($takeoff['title']); ?></h4>
<div class="btn-toolbar sc-toolbar mtop15">
  <a href="<?php echo admin_url('usi_smartchoice_seo/calculate_material_takeoff/' . (int)$takeoff['id']); ?>" class="btn btn-success btn-sm"><i class="fa fa-calculator"></i> Calculate Materials</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/mark_takeoff_ready/' . (int)$takeoff['id']); ?>" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Mark Ready For Purchasing</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/create_purchase_order_from_takeoff/' . (int)$takeoff['id']); ?>" class="btn btn-info btn-sm"><i class="fa fa-shopping-cart"></i> Create Purchase Order</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/material_takeoff/' . (int)$takeoff['id']); ?>" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Edit</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
</div><hr>
<div class="row">
  <div class="col-md-3"><div class="sc-card"><strong>Status</strong><br><?php echo html_escape($takeoff['status']); ?></div></div>
  <div class="col-md-3"><div class="sc-card"><strong>Material Total</strong><br>$<?php echo number_format((float)$takeoff['material_total'], 2); ?></div></div>
  <div class="col-md-3"><div class="sc-card"><strong>Waste</strong><br><?php echo number_format((float)$takeoff['waste_percent'], 2); ?>%</div></div>
  <div class="col-md-3"><div class="sc-card"><strong>AI Estimate</strong><br>#<?php echo (int)$takeoff['ai_estimate_id']; ?></div></div>
</div>
<h5 class="mtop20">Scope / Notes</h5>
<div class="well well-sm"><?php echo nl2br(html_escape($takeoff['takeoff_summary'])); ?></div>
<div class="well well-sm"><?php echo nl2br(html_escape($takeoff['measurement_notes'])); ?></div>
<h5>Material Lines</h5>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table sc-material-lines-table"><thead><tr><th class="sc-left sc-col-item">Item</th><th class="sc-left">Trade</th><th class="sc-col-unit">Unit</th><th class="sc-col-qty">Qty</th><th class="sc-col-qty">Waste</th><th class="sc-col-qty">Total Qty</th><th class="sc-col-money">Unit Cost</th><th class="sc-col-money">Line Total</th><th class="sc-left sc-col-description">Source Note</th></tr></thead><tbody>
<?php if (!empty($lines)) { foreach ($lines as $line) { ?><tr><td class="sc-left"><strong><?php echo html_escape($line['item_name']); ?></strong></td><td class="sc-left"><?php echo html_escape($line['trade']); ?></td><td><?php echo html_escape($line['unit']); ?></td><td><?php echo number_format((float)$line['quantity'], 2); ?></td><td><?php echo number_format((float)$line['waste_quantity'], 2); ?></td><td><?php echo number_format((float)$line['total_quantity'], 2); ?></td><td>$<?php echo number_format((float)$line['unit_cost'], 2); ?></td><td>$<?php echo number_format((float)$line['line_total'], 2); ?></td><td class="sc-left"><?php echo html_escape($line['source_note']); ?></td></tr><?php } } else { ?><tr><td colspan="9" class="text-center text-muted">No material lines yet. Click Calculate Materials.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
