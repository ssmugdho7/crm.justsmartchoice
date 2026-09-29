<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body"><div class="usi-toolbar"><h4><?php echo _l('usi_smartchoice_seo_view_keyword'); ?></h4><div><a class="btn btn-sm btn-default" href="<?php echo admin_url('usi_smartchoice_seo/keywords'); ?>"><?php echo _l('usi_smartchoice_seo_back_to_keywords'); ?></a><?php if (!empty($keyword['id']) && has_permission('usi_smartchoice_seo','','edit')) { ?><a class="btn btn-sm btn-primary" href="<?php echo admin_url('usi_smartchoice_seo/keyword/' . (int)$keyword['id']); ?>"><?php echo _l('edit'); ?></a><?php } ?></div></div>
<?php if (!$keyword) { ?><p class="text-muted"><?php echo _l('no_results_found'); ?></p><?php } else { ?>
<table class="table table-condensed usi-detail-table"><tbody>
<tr><th><?php echo _l('usi_smartchoice_seo_keyword'); ?></th><td><?php echo html_escape($keyword['keyword']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_intent'); ?></th><td><?php echo html_escape($keyword['intent']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_city'); ?></th><td><?php echo html_escape($keyword['city']); ?></td></tr>
<tr><th><?php echo _l('priority'); ?></th><td><?php echo html_escape(ucfirst($keyword['priority'])); ?></td></tr>
<tr><th><?php echo _l('status'); ?></th><td><?php echo html_escape(ucfirst($keyword['status'])); ?></td></tr>
<tr><th><?php echo _l('notes'); ?></th><td><?php echo nl2br(html_escape($keyword['notes'])); ?></td></tr>
</tbody></table><?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?></body></html>
