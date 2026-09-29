<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php if (empty($document)) { ?><div class="alert alert-warning">Document not found.</div><?php } else { ?>
<h4 class="no-margin"><i class="fa fa-file-text-o"></i> <?php echo html_escape($document['document_title']); ?></h4>
<p class="text-muted mtop10">Type: <?php echo html_escape($document['document_type']); ?> | Source: <?php echo html_escape($document['source_module']); ?> #<?php echo (int)$document['source_id']; ?> | Status: <?php echo html_escape($document['document_status']); ?></p>
<div class="btn-toolbar sc-toolbar"><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/document_item/' . (int)$document['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/document_intelligence'); ?>"><i class="fa fa-arrow-left"></i> Back</a></div>
<hr><h5>AI Summary</h5><div class="well"><?php echo nl2br(html_escape($document['ai_summary'])); ?></div>
<h5>Keywords</h5><div class="well"><?php echo nl2br(html_escape($document['ai_keywords'])); ?></div>
<h5>Document Text</h5><div class="well" style="white-space:pre-wrap;"><?php echo html_escape($document['document_text']); ?></div>
<h5>Document Chunks</h5><div class="table-responsive"><table class="table table-bordered sc-table"><thead><tr><th>Order</th><th>Title</th><th>Summary</th></tr></thead><tbody>
<?php if (!empty($chunks)) { foreach ($chunks as $chunk) { ?><tr><td><?php echo (int)$chunk['chunk_order']; ?></td><td><?php echo html_escape($chunk['chunk_title']); ?></td><td><?php echo html_escape($chunk['ai_summary']); ?></td></tr><?php } } else { ?><tr><td colspan="3" class="text-center text-muted">No chunks found.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
