<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<?php if ($estimate) { ?>
<a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/calculate_ai_estimate/' . (int)$estimate['id']); ?>"><i class="fa fa-calculator"></i> Calculate Numbers</a>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/estimate_review/' . (int)$estimate['id']); ?>"><i class="fa fa-check-square-o"></i> Estimate Review</a>
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/create_crm_estimate_from_ai/' . (int)$estimate['id']); ?>"><i class="fa fa-file-text-o"></i> Create CRM Estimate Draft</a>
<a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/build_customer_package/' . (int)$estimate['id']); ?>"><i class="fa fa-folder-open-o"></i> Build Customer Package</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimate/' . (int)$estimate['id']); ?>">Edit</a>
<?php } ?>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">Back</a>
</div></div>
<?php if ($estimate) { ?>
<div class="row">
  <div class="col-md-3"><div class="sc-card"><div class="sc-card-count"><?php echo app_format_money((float)$estimate['subtotal'], get_base_currency()); ?></div><div class="sc-card-label">Subtotal</div></div></div>
  <div class="col-md-3"><div class="sc-card"><div class="sc-card-count"><?php echo app_format_money((float)$estimate['total'], get_base_currency()); ?></div><div class="sc-card-label">Total</div></div></div>
  <div class="col-md-3"><div class="sc-card"><div class="sc-card-count"><?php echo html_escape((string)($estimate['pricing_confidence'] ?? '0')); ?>%</div><div class="sc-card-label">Pricing Confidence</div></div></div>
  <div class="col-md-3"><div class="sc-card"><div class="sc-card-count"><?php echo (int)($estimate['converted_estimate_id'] ?? 0); ?></div><div class="sc-card-label">CRM Estimate ID</div></div></div>
</div>
<h5>Estimate Details</h5>
<table class="table table-condensed sc-table"><tbody><?php foreach ($estimate as $key=>$value) { ?><tr><th><?php echo ucwords(str_replace('_',' ',html_escape($key))); ?></th><td><?php echo nl2br(html_escape((string)$value)); ?></td></tr><?php } ?></tbody></table>
<h5>Calculated Line Items</h5>
<div class="table-responsive"><table class="table table-striped table-condensed sc-table"><thead><tr><th>#</th><th>Description</th><th>Scope Detail</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Materials</th><th>Labor</th><th>Overhead / Profit</th><th>Total</th><th>Source</th><th>Confidence</th></tr></thead><tbody>
<?php foreach (($lines ?? []) as $line) { ?><tr><td><?php echo (int)$line['line_order']; ?></td><td><?php echo html_escape($line['description']); ?></td><td><?php echo html_escape($line['long_description']); ?></td><td><?php echo html_escape($line['quantity']); ?></td><td><?php echo html_escape($line['unit']); ?></td><td><?php echo app_format_money((float)$line['unit_rate'], get_base_currency()); ?></td><td><?php echo app_format_money((float)$line['material_total'], get_base_currency()); ?></td><td><?php echo app_format_money((float)$line['labor_total'], get_base_currency()); ?></td><td><?php echo app_format_money((float)$line['overhead_profit_total'], get_base_currency()); ?></td><td><?php echo app_format_money((float)$line['line_total'], get_base_currency()); ?></td><td><?php echo html_escape($line['source_type']); ?> <?php echo (int)$line['source_id']; ?></td><td><?php echo html_escape($line['confidence']); ?>%</td></tr><?php } ?>
<?php if (empty($lines)) { ?><tr><td colspan="12" class="text-center text-muted">No calculated line items yet. Click Calculate Numbers.</td></tr><?php } ?>
</tbody></table></div>
<h5>Historical Price Sources</h5>
<div class="table-responsive"><table class="table table-striped table-condensed sc-table"><thead><tr><th>Price Row</th><th>CRM Estimate</th><th>Service</th><th>Source Description</th><th>Rate</th><th>Total</th><th>Confidence</th></tr></thead><tbody>
<?php foreach (($sources ?? []) as $source) { ?><tr><td><?php echo (int)$source['price_index_id']; ?></td><td><?php echo (int)$source['source_estimate_id']; ?></td><td><?php echo html_escape($source['service_keyword']); ?></td><td><?php echo html_escape($source['source_description']); ?></td><td><?php echo app_format_money((float)$source['source_rate'], get_base_currency()); ?></td><td><?php echo app_format_money((float)$source['source_total'], get_base_currency()); ?></td><td><?php echo html_escape($source['confidence']); ?>%</td></tr><?php } ?>
<?php if (empty($sources)) { ?><tr><td colspan="7" class="text-center text-muted">No historical source rows were matched yet.</td></tr><?php } ?>
</tbody></table></div>
<h5>Photos</h5><div class="row"><?php foreach ($photos as $photo) { ?><div class="col-md-3"><div class="thumbnail"><img src="<?php echo site_url($photo['file_path']); ?>" alt="<?php echo html_escape($photo['original_name']); ?>"><div class="caption small"><?php echo html_escape($photo['ai_caption']); ?></div></div></div><?php } ?></div>
<?php } ?>
</div></div></div></div><?php init_tail(); ?>
