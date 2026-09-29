<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<div class="sc-toolbar"><h4 class="no-margin"><i class="fa fa-shield"></i> AI Closeout &amp; Warranty</h4><div class="sc-toolbar-actions"><a href="<?php echo admin_url('usi_smartchoice_seo/closeout_record'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Closeout</a><a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a></div></div>
<div class="row sc-kpi-row sc-clickable-kpis">
<?php foreach (($counts ?? []) as $label => $value) { $statusMap = ['draft'=>'draft','in_review'=>'in_review','ready_for_customer'=>'ready_for_customer','completed'=>'completed','warranty_active'=>'warranty_active']; $href = isset($statusMap[$label]) ? admin_url('usi_smartchoice_seo/closeout_warranty?status=' . urlencode($statusMap[$label])) : admin_url('usi_smartchoice_seo/closeout_warranty'); ?>
  <div class="col-md-2 col-xs-6"><a class="sc-kpi sc-kpi-link" href="<?php echo $href; ?>"><strong><?php echo (int)$value; ?></strong><span><?php echo ucwords(str_replace('_',' ', $label)); ?></span></a></div>
<?php } ?>
</div>
<hr>
<?php echo form_open(admin_url('usi_smartchoice_seo/closeout_warranty'), ['method' => 'get', 'class' => 'form-inline sc-filter-bar']); ?>
  <select name="status" class="form-control input-sm"><option value="">All Statuses</option><?php foreach (['draft','in_review','ready_for_customer','completed'] as $status) { ?><option value="<?php echo html_escape($status); ?>" <?php echo (($filters['status'] ?? '') === $status ? 'selected' : ''); ?>><?php echo ucwords(str_replace('_',' ', $status)); ?></option><?php } ?></select>
  <input type="date" name="date_from" class="form-control input-sm" value="<?php echo html_escape($filters['date_from'] ?? ''); ?>">
  <input type="date" name="date_to" class="form-control input-sm" value="<?php echo html_escape($filters['date_to'] ?? ''); ?>">
  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-filter"></i> Filter</button><a href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty'); ?>" class="btn btn-default btn-sm">Clear</a>
<?php echo form_close(); ?>
<hr>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_closeouts')); ?>
<div class="table-responsive"><table class="table table-striped table-condensed sc-table sc-wide-actions-table">
<thead><tr><th width="35"><input type="checkbox" class="sc-check-all"></th><th class="sc-left">Title</th><th class="sc-left sc-col-date">Date</th><th class="sc-left">Status</th><th class="sc-left">Project</th><th class="sc-left">Ready</th><th class="sc-actions-col">Actions</th></tr></thead>
<tbody>
<?php foreach (($closeouts ?? []) as $row) { ?>
<tr><td><input type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td><td class="sc-left"><?php echo html_escape($row['title']); ?></td><td class="sc-left"><?php echo html_escape($row['closeout_date']); ?></td><td class="sc-left"><a class="label label-default sc-status-link" href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty?status=' . urlencode((string)$row['status'])); ?>"><?php echo ucwords(str_replace('_',' ', html_escape($row['status']))); ?></a></td><td class="sc-left"><?php echo (int)$row['project_id']; ?></td><td class="sc-left"><?php echo !empty($row['ready_for_customer']) ? 'Yes' : 'No'; ?></td><td class="sc-actions-col"><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_closeout/' . (int)$row['id']); ?>"><i class="fa fa-eye"></i> View</a><a class="btn btn-info btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/generate_warranty_records/' . (int)$row['id']); ?>"><i class="fa fa-shield"></i> Warranty</a><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/closeout_record/' . (int)$row['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_closeout/' . (int)$row['id']); ?>"><i class="fa fa-trash"></i> Delete</a></td></tr>
<?php } if (empty($closeouts)) { ?><tr><td colspan="7" class="text-center text-muted">No closeout packages found.</td></tr><?php } ?>
</tbody></table></div><button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button><?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
