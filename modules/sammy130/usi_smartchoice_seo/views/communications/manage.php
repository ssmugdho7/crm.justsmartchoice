<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<div class="usi-toolbar"><h4>Customer Communications</h4><div class="usi-actions">
<a href="<?php echo admin_url('usi_smartchoice_seo/communication'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Message</a>
<a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/communications'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Sample Header</a>
<a href="<?php echo admin_url('usi_smartchoice_seo/export_csv/communications'); ?>" class="btn btn-default btn-sm"><i class="fa fa-file-excel-o"></i> Export</a>
<a href="<?php echo admin_url('usi_smartchoice_seo/communications'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a>
</div></div>
<form method="get" class="row sc-filter-row">
<div class="col-md-3"><input type="text" name="search" class="form-control input-sm" placeholder="Search messages" value="<?php echo html_escape($filters['search'] ?? ''); ?>"></div>
<div class="col-md-2"><select name="communication_type" class="form-control input-sm"><option value="">All Types</option><?php foreach(['follow_up','estimate_follow_up','customer_package_follow_up','approval_request','revision_request'] as $type){ ?><option value="<?php echo $type; ?>" <?php echo (($filters['communication_type'] ?? '') === $type ? 'selected' : ''); ?>><?php echo ucwords(str_replace('_',' ',$type)); ?></option><?php } ?></select></div>
<div class="col-md-2"><select name="send_status" class="form-control input-sm"><option value="">All Statuses</option><?php foreach(['draft','ready_to_send','sent','needs_revision','cancelled'] as $status){ ?><option value="<?php echo $status; ?>" <?php echo (($filters['send_status'] ?? '') === $status ? 'selected' : ''); ?>><?php echo ucwords(str_replace('_',' ',$status)); ?></option><?php } ?></select></div>
<div class="col-md-2"><button class="btn btn-default btn-sm" type="submit"><i class="fa fa-filter"></i> Filter</button></div>
</form>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_communications')); ?>
<div class="table-responsive"><table class="table table-striped table-condensed usi-compact-table"><thead><tr><th><input type="checkbox" class="usi-check-all"></th><th>Subject</th><th>Type</th><th>Status</th><th>Language</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach($communications as $row){ ?><tr>
<td><input type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
<td><?php echo html_escape($row['subject']); ?></td>
<td><?php echo html_escape(ucwords(str_replace('_',' ',$row['communication_type']))); ?></td>
<td><span class="label label-default"><?php echo html_escape(ucwords(str_replace('_',' ',$row['send_status']))); ?></span></td>
<td><?php echo html_escape($row['language']); ?></td>
<td><?php echo html_escape($row['updated_at']); ?></td>
<td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_communication/'.(int)$row['id']); ?>"><i class="fa fa-eye"></i> View</a> <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/communication/'.(int)$row['id']); ?>"><i class="fa fa-pencil"></i> Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_communication/'.(int)$row['id']); ?>"><i class="fa fa-trash"></i> Delete</a></td>
</tr><?php } ?>
<?php if(empty($communications)){ ?><tr><td colspan="7" class="text-center text-muted">No customer communications found.</td></tr><?php } ?>
</tbody></table></div>
<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Mass Delete</button>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
</body></html>
