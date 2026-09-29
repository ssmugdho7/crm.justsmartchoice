<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-file-pdf-o"></i> Document Intelligence</h4>
<p class="text-muted mtop10">Index, search, and review CRM documents, estimate text, scopes, uploaded document text, and Sammy AI memory records.</p>
<div class="row mtop15">
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['documents'] ?? 0); ?></strong><br>Documents</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['chunks'] ?? 0); ?></strong><br>Text Chunks</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['manual'] ?? 0); ?></strong><br>Manual</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['crm'] ?? 0); ?></strong><br>CRM Records</div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong><?php echo (int)($counts['indexed'] ?? 0); ?></strong><br>Indexed</div></div>
</div>
<hr>
<div class="btn-toolbar sc-toolbar" role="toolbar">
  <a href="<?php echo admin_url('usi_smartchoice_seo/document_item'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Document</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/rebuild_document_index'); ?>" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> Rebuild Document Index</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/export/documents'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Export</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/documents'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Sample Header</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/document_intelligence'); ?>" class="btn btn-default btn-sm"><i class="fa fa-repeat"></i> Reload</a>
</div>
<?php echo form_open(admin_url('usi_smartchoice_seo/document_search'), ['class' => 'mtop15']); ?>
<div class="row">
  <div class="col-md-8"><input type="text" name="query" class="form-control" value="<?php echo html_escape($query ?? ($filters['q'] ?? '')); ?>" placeholder="Search document text, scopes, permits, contracts, estimates, photos notes, or customer history..."></div>
  <div class="col-md-4"><button type="submit" class="btn btn-info btn-block"><i class="fa fa-search"></i> Search Documents</button></div>
</div>
<?php echo form_close(); ?>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_documents')); ?>
<div class="table-responsive mtop20">
<table class="table table-bordered table-striped sc-table">
<thead><tr><th width="35"><input type="checkbox" onclick="$('.sc-doc-check').prop('checked', this.checked);"></th><th>Title</th><th>Type</th><th>Source</th><th>Customer</th><th>Project</th><th>Status</th><th>Updated</th><th width="150">Actions</th></tr></thead>
<tbody>
<?php if (!empty($documents)) { foreach ($documents as $doc) { ?>
<tr>
<td><input type="checkbox" class="sc-doc-check" name="ids[]" value="<?php echo (int)$doc['id']; ?>"></td>
<td><strong><?php echo html_escape($doc['document_title']); ?></strong><br><small class="text-muted"><?php echo html_escape($doc['ai_summary']); ?></small></td>
<td><?php echo html_escape($doc['document_type']); ?></td>
<td><?php echo html_escape($doc['source_module']); ?> #<?php echo (int)$doc['source_id']; ?></td>
<td><?php echo (int)$doc['customer_id']; ?></td>
<td><?php echo (int)$doc['project_id']; ?></td>
<td><span class="label label-default"><?php echo html_escape($doc['document_status']); ?></span></td>
<td><?php echo html_escape($doc['updated_at']); ?></td>
<td>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_document_item/' . (int)$doc['id']); ?>"><i class="fa fa-eye"></i> View</a>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/document_item/' . (int)$doc['id']); ?>"><i class="fa fa-pencil"></i></a>
<a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_document_item/' . (int)$doc['id']); ?>"><i class="fa fa-trash"></i></a>
</td>
</tr>
<?php } } else { ?><tr><td colspan="9" class="text-center text-muted">No documents indexed yet. Click Rebuild Document Index.</td></tr><?php } ?>
</tbody></table></div>
<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button>
<?php echo form_close(); ?>
<hr><h5>Recent Document Index Runs</h5>
<div class="table-responsive"><table class="table table-condensed table-bordered sc-table"><thead><tr><th>ID</th><th>Status</th><th>Documents</th><th>Chunks</th><th>Summary</th><th>Finished</th></tr></thead><tbody>
<?php if (!empty($runs)) { foreach ($runs as $run) { ?><tr><td><?php echo (int)$run['id']; ?></td><td><?php echo html_escape($run['run_status']); ?></td><td><?php echo (int)$run['documents_indexed']; ?></td><td><?php echo (int)$run['chunks_created']; ?></td><td><?php echo html_escape($run['source_summary']); ?></td><td><?php echo html_escape($run['finished_at']); ?></td></tr><?php } } else { ?><tr><td colspan="6" class="text-center text-muted">No document index run has been logged yet.</td></tr><?php } ?>
</tbody></table></div>
</div></div></div></div></div></div>
<?php init_tail(); ?>
