<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-shopping-cart"></i> AI Purchasing</h4>
<p class="text-muted mtop10">Create purchase orders from material takeoffs, track vendors, compare pricing, and move materials from estimate planning into production purchasing.</p>
<div class="row mtop15">
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['purchase_orders'] ?? 0); ?></strong><br>Purchase Orders</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['vendors'] ?? 0); ?></strong><br>Vendors</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['draft'] ?? 0); ?></strong><br>Draft</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['ready'] ?? 0); ?></strong><br>Ready</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['ordered'] ?? 0); ?></strong><br>Ordered</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['received'] ?? 0); ?></strong><br>Received</div></div>
</div>
<hr>
<div class="btn-toolbar sc-toolbar" role="toolbar">
  <a href="<?php echo admin_url('usi_smartchoice_seo/purchase_order'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Purchase Order</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/vendor'); ?>" class="btn btn-default btn-sm"><i class="fa fa-truck"></i> New Vendor</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/export/purchase_orders'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/purchase_orders'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
</div>
<form method="get" class="mtop15" action="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>">
<div class="row">
  <div class="col-md-3"><input type="text" name="q" class="form-control" value="<?php echo html_escape($filters['q'] ?? ''); ?>" placeholder="Search PO, title, notes"></div>
  <div class="col-md-3"><select name="status" class="form-control"><option value="">All Statuses</option><?php foreach(['draft'=>'Draft','ready_to_order'=>'Ready To Order','ordered'=>'Ordered','received'=>'Received','cancelled'=>'Cancelled'] as $k=>$v){ ?><option value="<?php echo $k; ?>" <?php echo (($filters['status'] ?? '')===$k?'selected':''); ?>><?php echo $v; ?></option><?php } ?></select></div>
  <div class="col-md-3"><select name="vendor_id" class="form-control"><option value="">All Vendors</option><?php foreach($vendors as $vendor){ ?><option value="<?php echo (int)$vendor['id']; ?>" <?php echo ((int)($filters['vendor_id'] ?? 0)===(int)$vendor['id']?'selected':''); ?>><?php echo html_escape($vendor['vendor_name']); ?></option><?php } ?></select></div>
  <div class="col-md-3"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filter</button></div>
</div>
</form>
<div class="row mtop20">
<div class="col-md-8">
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_purchase_orders')); ?>
<div class="table-responsive">
<table class="table table-bordered table-striped sc-table">
<thead><tr><th width="35"><input type="checkbox" onclick="$('.sc-po-check').prop('checked', this.checked);"></th><th>PO Number</th><th>Title</th><th>Vendor</th><th>Status</th><th>Total</th><th>Updated</th><th width="230">Actions</th></tr></thead>
<tbody>
<?php if (!empty($purchase_orders)) { foreach ($purchase_orders as $row) { ?>
<tr>
<td><input type="checkbox" class="sc-po-check" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
<td><strong><?php echo html_escape($row['po_number']); ?></strong></td>
<td><?php echo html_escape($row['title']); ?><br><small class="text-muted">Takeoff #<?php echo (int)$row['takeoff_id']; ?> / Estimate #<?php echo (int)$row['ai_estimate_id']; ?></small></td>
<td><?php echo html_escape($row['vendor_name'] ?? 'Not Assigned'); ?></td>
<td><span class="label label-default"><?php echo html_escape(str_replace('_', ' ', $row['status'])); ?></span></td>
<td>$<?php echo number_format((float)$row['total'], 2); ?></td>
<td><?php echo html_escape($row['updated_at']); ?></td>
<td>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_purchase_order/' . (int)$row['id']); ?>"><i class="fa fa-eye"></i> View</a>
<a class="btn btn-success btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/recalculate_purchase_order/' . (int)$row['id']); ?>"><i class="fa fa-calculator"></i> Recalculate</a>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/purchase_order/' . (int)$row['id']); ?>"><i class="fa fa-pencil"></i></a>
<a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_purchase_order/' . (int)$row['id']); ?>"><i class="fa fa-trash"></i></a>
</td>
</tr>
<?php } } else { ?><tr><td colspan="8" class="text-center text-muted">No purchase orders yet. Create one from a material takeoff or click New Purchase Order.</td></tr><?php } ?>
</tbody></table></div>
<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button>
<?php echo form_close(); ?>
</div>
<div class="col-md-4">
<h5><i class="fa fa-truck"></i> Vendors</h5>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Vendor</th><th>Category</th><th>Rating</th><th width="95">Actions</th></tr></thead><tbody>
<?php if (!empty($vendors)) { foreach ($vendors as $vendor) { ?><tr><td><strong><?php echo html_escape($vendor['vendor_name']); ?></strong><?php if ((int)$vendor['preferred'] === 1) { ?> <span class="label label-success">Preferred</span><?php } ?><br><small><?php echo html_escape($vendor['phone']); ?></small></td><td><?php echo html_escape($vendor['category']); ?></td><td><?php echo number_format((float)$vendor['rating'], 2); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/vendor/' . (int)$vendor['id']); ?>"><i class="fa fa-pencil"></i></a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_vendor/' . (int)$vendor['id']); ?>"><i class="fa fa-trash"></i></a></td></tr><?php } } else { ?><tr><td colspan="4" class="text-center text-muted">No vendors yet.</td></tr><?php } ?>
</tbody></table></div>
<h5 class="mtop20"><i class="fa fa-cubes"></i> Ready Takeoffs</h5>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table"><thead><tr><th>Takeoff</th><th>Total</th><th>Action</th></tr></thead><tbody>
<?php if (!empty($takeoffs)) { foreach ($takeoffs as $takeoff) { ?><tr><td><?php echo html_escape($takeoff['title']); ?></td><td>$<?php echo number_format((float)$takeoff['material_total'], 2); ?></td><td><a class="btn btn-info btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/create_purchase_order_from_takeoff/' . (int)$takeoff['id']); ?>"><i class="fa fa-shopping-cart"></i> PO</a></td></tr><?php } } else { ?><tr><td colspan="3" class="text-center text-muted">No takeoffs marked ready.</td></tr><?php } ?>
</tbody></table></div>
</div>
</div>
</div></div></div></div></div></div>
<?php init_tail(); ?>
