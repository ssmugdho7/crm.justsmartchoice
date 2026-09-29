<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php $k = $keyword ?: []; echo form_open(current_url()); ?>
<div class="panel_s"><div class="panel-body"><div class="usi-toolbar"><h4><?php echo html_escape($title); ?></h4><div><a href="<?php echo admin_url('usi_smartchoice_seo/keywords'); ?>" class="btn btn-sm btn-default"><?php echo _l('usi_smartchoice_seo_back_to_keywords'); ?></a><a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/keywords'); ?>" class="btn btn-sm btn-default"><?php echo _l('usi_smartchoice_seo_sample_header'); ?></a></div></div>
<div class="row"><div class="col-md-6"><?php echo render_input('keyword','usi_smartchoice_seo_keyword',$k['keyword'] ?? ''); ?></div><div class="col-md-6"><?php echo render_input('intent','usi_smartchoice_seo_intent',$k['intent'] ?? 'local_service'); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_input('city','usi_smartchoice_seo_city',$k['city'] ?? ''); ?></div><div class="col-md-4"><?php echo render_select('priority', [['id'=>'low','name'=>'Low'],['id'=>'medium','name'=>'Medium'],['id'=>'high','name'=>'High'],['id'=>'urgent','name'=>'Urgent']], ['id','name'], 'priority', $k['priority'] ?? 'medium'); ?></div><div class="col-md-4"><?php echo render_select('status', [['id'=>'planned','name'=>'Planned'],['id'=>'researching','name'=>'Researching'],['id'=>'writing','name'=>'Writing'],['id'=>'optimized','name'=>'Optimized'],['id'=>'published','name'=>'Published']], ['id','name'], 'status', $k['status'] ?? 'planned'); ?></div></div>
<?php echo render_textarea('notes','notes',$k['notes'] ?? '', ['rows'=>7]); ?>
<div class="text-right"><a href="<?php echo admin_url('usi_smartchoice_seo/keywords'); ?>" class="btn btn-sm btn-default"><?php echo _l('cancel'); ?></a> <button class="btn btn-sm btn-primary" type="submit"><?php echo _l('save'); ?></button></div>
</div></div><?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?></body></html>
