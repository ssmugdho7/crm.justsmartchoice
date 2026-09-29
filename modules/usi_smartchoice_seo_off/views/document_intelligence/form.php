<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-file-text-o"></i> <?php echo html_escape($title); ?></h4><hr>
<?php echo form_open(current_url()); ?>
<div class="row">
<div class="col-md-8"><?php echo render_input('document_title', 'Document Title', $document['document_title'] ?? ''); ?></div>
<div class="col-md-4"><?php echo render_select('document_type', [['id'=>'manual','name'=>'Manual'],['id'=>'estimate','name'=>'Estimate'],['id'=>'crm_record','name'=>'CRM Record'],['id'=>'contract','name'=>'Contract'],['id'=>'permit','name'=>'Permit'],['id'=>'scope','name'=>'Scope']], ['id','name'], 'Document Type', $document['document_type'] ?? 'manual'); ?></div>
</div>
<div class="row">
<div class="col-md-4"><?php echo render_input('source_module', 'Source Module', $document['source_module'] ?? 'manual'); ?></div>
<div class="col-md-2"><?php echo render_input('source_id', 'Source ID', $document['source_id'] ?? 0, 'number'); ?></div>
<div class="col-md-2"><?php echo render_input('customer_id', 'Customer ID', $document['customer_id'] ?? 0, 'number'); ?></div>
<div class="col-md-2"><?php echo render_input('lead_id', 'Lead ID', $document['lead_id'] ?? 0, 'number'); ?></div>
<div class="col-md-2"><?php echo render_input('project_id', 'Project ID', $document['project_id'] ?? 0, 'number'); ?></div>
</div>
<div class="row">
<div class="col-md-4"><?php echo render_input('file_name', 'File Name', $document['file_name'] ?? ''); ?></div>
<div class="col-md-6"><?php echo render_input('file_path', 'File Path', $document['file_path'] ?? ''); ?></div>
<div class="col-md-2"><?php echo render_input('mime_type', 'MIME Type', $document['mime_type'] ?? ''); ?></div>
</div>
<?php echo render_textarea('document_text', 'Document Text', $document['document_text'] ?? '', ['rows'=>10]); ?>
<?php echo render_textarea('ai_summary', 'AI Summary', $document['ai_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('ai_keywords', 'AI Keywords', $document['ai_keywords'] ?? '', ['rows'=>3]); ?>
<?php echo render_select('document_status', [['id'=>'indexed','name'=>'Indexed'],['id'=>'needs_review','name'=>'Needs Review'],['id'=>'approved','name'=>'Approved'],['id'=>'archived','name'=>'Archived']], ['id','name'], 'Status', $document['document_status'] ?? 'indexed'); ?>
<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Document</button>
<a href="<?php echo admin_url('usi_smartchoice_seo/document_intelligence'); ?>" class="btn btn-default">Back</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
