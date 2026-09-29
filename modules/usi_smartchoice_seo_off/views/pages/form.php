<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php $p = $page ?: []; echo form_open(current_url()); ?>
<div class="panel_s"><div class="panel-body"><div class="usi-toolbar"><h4><?php echo html_escape($title); ?></h4><div><a href="<?php echo admin_url('usi_smartchoice_seo/pages'); ?>" class="btn btn-sm btn-default"><?php echo _l('usi_smartchoice_seo_back_to_pages'); ?></a><a href="<?php echo admin_url('usi_smartchoice_seo/sample_header/pages'); ?>" class="btn btn-sm btn-default"><?php echo _l('usi_smartchoice_seo_sample_header'); ?></a></div></div>
<div class="row"><div class="col-md-6"><?php echo render_input('title','title',$p['title'] ?? ''); ?></div><div class="col-md-6"><?php echo render_input('slug','usi_smartchoice_seo_slug',$p['slug'] ?? ''); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_input('page_type','usi_smartchoice_seo_page_type',$p['page_type'] ?? 'service'); ?></div><div class="col-md-4"><?php echo render_input('primary_keyword','usi_smartchoice_seo_keyword',$p['primary_keyword'] ?? ''); ?></div><div class="col-md-4"><?php echo render_input('city','usi_smartchoice_seo_city',$p['city'] ?? ''); ?></div></div>
<?php echo render_input('meta_title','usi_smartchoice_seo_meta_title',$p['meta_title'] ?? ''); ?>
<?php echo render_textarea('meta_description','usi_smartchoice_seo_meta_description',$p['meta_description'] ?? '', ['rows'=>3]); ?>
<?php echo render_textarea('content','usi_smartchoice_seo_content',$p['content'] ?? '', ['rows'=>10]); ?>
<?php echo render_select('status', [['id'=>'draft','name'=>'Draft'],['id'=>'review','name'=>'Review'],['id'=>'approved','name'=>'Approved'],['id'=>'published','name'=>'Published'],['id'=>'archived','name'=>'Archived']], ['id','name'], 'status', $p['status'] ?? get_option('usi_smartchoice_seo_default_status')); ?>
<div class="text-right"><a href="<?php echo admin_url('usi_smartchoice_seo/pages'); ?>" class="btn btn-sm btn-default"><?php echo _l('cancel'); ?></a> <button class="btn btn-sm btn-primary" type="submit"><?php echo _l('save'); ?></button></div>
</div></div><?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?></body></html>
