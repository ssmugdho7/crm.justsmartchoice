<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><?php $this->load->view('feedbacks/_navigation'); ?><div class="row"><div class="col-md-12">
<div class="panel_s"><div class="panel-body">
<div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2"><div><h4 class="no-margin"><?php echo _l('fb_template_library'); ?></h4><p class="text-muted mtop5"><?php echo _l('fb_template_library_desc'); ?></p></div><a class="btn btn-default" href="<?php echo admin_url('feedbacks'); ?>"><i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?></a></div>
<div class="row mtop20">
<?php foreach ($templates as $index => $template) { ?>
<div class="col-md-6 col-lg-4"><div class="panel_s"><div class="panel-body" style="min-height:245px;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 3px 12px rgba(0,0,0,.04)">
<span class="label label-info"><?php echo html_escape($template['audience']); ?></span><h4 class="bold mtop15"><?php echo html_escape($template['title']); ?></h4><p class="text-muted"><?php echo html_escape($template['description']); ?></p><p><i class="fa fa-list-ol"></i> <?php echo count($template['questions']); ?> <?php echo _l('fb_template_questions'); ?></p>
<?php if (has_permission('feedbacks', '', 'create')) { ?><a class="btn btn-primary btn-block" href="<?php echo admin_url('feedbacks/create_from_template/' . $index); ?>"><?php echo _l('fb_use_template'); ?></a><?php } ?>
</div></div></div>
<?php } ?>
</div></div></div></div></div></div><?php init_tail(); ?></body></html>
