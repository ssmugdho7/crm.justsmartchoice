<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-check-circle"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/field_verification'); ?>"><i class="fa fa-plus"></i> New Verification</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/field_verifications'); ?>"><i class="fa fa-download"></i> Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/field_verifications'); ?>"><i class="fa fa-file-excel-o"></i> Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/field_verifications'); ?>"><i class="fa fa-refresh"></i> Reload</a>
</div></div>
<form method="get" class="usi-filter"><div class="row">
<div class="col-md-3"><input type="text" name="search" class="form-control input-sm" placeholder="Search title, address, notes" value="<?php echo html_escape($filters['search'] ?? ''); ?>"></div>
<div class="col-md-2"><select name="field_status" class="form-control input-sm"><option value="">All Statuses</option><?php foreach(['pending'=>'Pending','in_progress'=>'In Progress','verified'=>'Verified','needs_revision'=>'Needs Revision','blocked'=>'Blocked'] as $k=>$v){ ?><option value="<?php echo $k; ?>" <?php echo (($filters['field_status'] ?? '')===$k?'selected':''); ?>><?php echo $v; ?></option><?php } ?></select></div>
<div class="col-md-2"><select name="ready_for_estimate" class="form-control input-sm"><option value="">Ready?</option><option value="1" <?php echo (($filters['ready_for_estimate'] ?? '')==='1'?'selected':''); ?>>Ready</option><option value="0" <?php echo (($filters['ready_for_estimate'] ?? '')==='0'?'selected':''); ?>>Not Ready</option></select></div>
<div class="col-md-2"><button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-filter"></i> Filter</button></div>
</div></form>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_field_verifications')); ?>
<div class="table-responsive"><table class="table table-bordered table-hover usi-compact-table">
<thead><tr><th class="usi-check"><input type="checkbox" onclick="$('input[name=&quot;ids[]&quot;]').prop('checked', this.checked);"></th><th>Title</th><th>Estimate</th><th>Customer</th><th>Address</th><th>Status</th><th>Ready</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach($verifications as $row){ ?><tr>
<td><input type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
<td><?php echo html_escape($row['verification_title']); ?></td>
<td><?php echo (int)$row['ai_estimate_id']; ?></td><td><?php echo (int)$row['customer_id']; ?></td>
<td><?php echo html_escape($row['jobsite_address']); ?></td><td><?php echo ucwords(str_replace('_',' ',html_escape($row['field_status']))); ?></td><td><?php echo ((int)$row['ready_for_estimate']===1?'Yes':'No'); ?></td><td><?php echo html_escape($row['updated_at'] ?: $row['created_at']); ?></td>
<td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_field_verification/'.(int)$row['id']); ?>">View</a> <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/field_verification/'.(int)$row['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_field_verification/'.(int)$row['id']); ?>">Delete</a></td>
</tr><?php } if(empty($verifications)){ ?><tr><td colspan="9">No field verification records found.</td></tr><?php } ?>
</tbody></table></div><div class="usi-table-footer"><button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button></div><?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?>