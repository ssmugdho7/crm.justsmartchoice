<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-cubes"></i> Material Takeoff</h4>
<p class="text-muted mtop10">Create material takeoffs from AI estimates, measurement notes, and jobsite scope. Review all quantities before purchasing.</p>
<div class="row mtop15 sc-clickable-kpis">
  <div class="col-md-2 col-xs-6"><a class="sc-card sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>"><strong><?php echo (int)($counts['takeoffs'] ?? 0); ?></strong><br>Takeoffs</a></div>
  <div class="col-md-2 col-xs-6"><a class="sc-card sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>"><strong><?php echo (int)($counts['lines'] ?? 0); ?></strong><br>Line Items</a></div>
  <div class="col-md-2 col-xs-6"><a class="sc-card sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs?status=draft'); ?>"><strong><?php echo (int)($counts['draft'] ?? 0); ?></strong><br>Draft</a></div>
  <div class="col-md-2 col-xs-6"><a class="sc-card sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs?status=calculated'); ?>"><strong><?php echo (int)($counts['calculated'] ?? 0); ?></strong><br>Calculated</a></div>
  <div class="col-md-2 col-xs-6"><a class="sc-card sc-kpi-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs?status=ready_for_purchasing'); ?>"><strong><?php echo (int)($counts['ready'] ?? 0); ?></strong><br>Ready</a></div>
</div>
<hr>
<div class="btn-toolbar sc-toolbar" role="toolbar">
  <a href="<?php echo admin_url('usi_smartchoice_seo/material_takeoff'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Takeoff</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/export/takeoffs'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/takeoffs'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
</div>
<form method="get" class="mtop15" action="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>">
<div class="row">
  <div class="col-md-3"><input type="text" name="q" class="form-control" value="<?php echo html_escape($filters['q'] ?? ''); ?>" placeholder="Search title, notes, summary"></div>
  <div class="col-md-3"><select name="status" class="form-control"><option value="">All Statuses</option><?php foreach(['draft'=>'Draft','calculated'=>'Calculated','ready_for_purchasing'=>'Ready For Purchasing'] as $k=>$v){ ?><option value="<?php echo $k; ?>" <?php echo (($filters['status'] ?? '')===$k?'selected':''); ?>><?php echo $v; ?></option><?php } ?></select></div>
  <div class="col-md-3"><input type="text" name="service_category" class="form-control" value="<?php echo html_escape($filters['service_category'] ?? ''); ?>" placeholder="Service category"></div>
  <div class="col-md-3"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filter</button></div>
</div>
</form>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_takeoffs')); ?>
<div class="table-responsive mtop20">
<table class="table table-bordered table-striped sc-table sc-wide-actions-table">
<thead><tr><th width="35"><input type="checkbox" onclick="$('.sc-takeoff-check').prop('checked', this.checked);"></th><th class="sc-left">Title</th><th class="sc-left">AI Estimate</th><th class="sc-left">Category</th><th class="sc-left">Area</th><th class="sc-left sc-col-total">Total</th><th class="sc-left">Status</th><th class="sc-left sc-col-date">Updated</th><th class="sc-actions-col">Actions</th></tr></thead>
<tbody>
<?php if (!empty($takeoffs)) { foreach ($takeoffs as $row) { ?>
<tr>
<td><input type="checkbox" class="sc-takeoff-check" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
<td class="sc-left"><strong><?php echo html_escape($row['title']); ?></strong><br><small class="text-muted"><?php echo html_escape(substr((string)$row['takeoff_summary'], 0, 120)); ?></small></td>
<td class="sc-left"><a href="<?php echo admin_url('usi_smartchoice_seo/view_ai_estimate/' . (int)$row['ai_estimate_id']); ?>">#<?php echo (int)$row['ai_estimate_id']; ?></a></td>
<td class="sc-left"><?php echo html_escape($row['service_category']); ?></td>
<td class="sc-left"><?php echo html_escape($row['room_area']); ?></td>
<td class="sc-left">$<?php echo number_format((float)$row['material_total'], 2); ?></td>
<td class="sc-left"><a class="label label-default sc-status-link" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs?status=' . urlencode((string)$row['status'])); ?>"><?php echo html_escape(ucwords(str_replace('_',' ', $row['status']))); ?></a></td>
<td class="sc-left"><?php echo html_escape($row['updated_at']); ?></td>
<td class="sc-actions-col"><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_material_takeoff/' . (int)$row['id']); ?>"><i class="fa fa-eye"></i> View</a><a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/calculate_material_takeoff/' . (int)$row['id']); ?>"><i class="fa fa-calculator"></i> Calculate</a><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/material_takeoff/' . (int)$row['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_material_takeoff/' . (int)$row['id']); ?>"><i class="fa fa-trash"></i> Delete</a></td>
</tr>
<?php } } else { ?><tr><td colspan="9" class="text-center text-muted">No material takeoffs yet.</td></tr><?php } ?>
</tbody></table></div>
<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
