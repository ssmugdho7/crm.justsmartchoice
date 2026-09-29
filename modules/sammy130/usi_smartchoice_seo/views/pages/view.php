<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body"><div class="usi-toolbar"><h4><?php echo _l('usi_smartchoice_seo_view_page'); ?></h4><div><a class="btn btn-sm btn-default" href="<?php echo admin_url('usi_smartchoice_seo/pages'); ?>"><?php echo _l('usi_smartchoice_seo_back_to_pages'); ?></a><?php if (!empty($page['id']) && has_permission('usi_smartchoice_seo','','edit')) { ?><a class="btn btn-sm btn-primary" href="<?php echo admin_url('usi_smartchoice_seo/page/' . (int)$page['id']); ?>"><?php echo _l('edit'); ?></a><?php } ?></div></div>
<?php if (!$page) { ?><p class="text-muted"><?php echo _l('no_results_found'); ?></p><?php } else { ?>
<table class="table table-condensed usi-detail-table"><tbody>
<tr><th><?php echo _l('title'); ?></th><td><?php echo html_escape($page['title']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_slug'); ?></th><td><?php echo html_escape($page['slug']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_page_type'); ?></th><td><?php echo html_escape($page['page_type']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_keyword'); ?></th><td><?php echo html_escape($page['primary_keyword']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_city'); ?></th><td><?php echo html_escape($page['city']); ?></td></tr>
<tr><th><?php echo _l('status'); ?></th><td><?php echo html_escape(ucfirst($page['status'])); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_meta_title'); ?></th><td><?php echo html_escape($page['meta_title']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_meta_description'); ?></th><td><?php echo html_escape($page['meta_description']); ?></td></tr>
<tr><th><?php echo _l('usi_smartchoice_seo_content'); ?></th><td><?php echo nl2br(html_escape($page['content'])); ?></td></tr>
</tbody></table><?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?></body></html>
