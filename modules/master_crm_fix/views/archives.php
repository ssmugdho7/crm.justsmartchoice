<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><?php $this->load->view('partials/nav'); ?>
<div class="panel_s"><div class="panel-body"><h4><?php echo _l('master_crm_fix_archive_scan'); ?></h4>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('master_crm_fix_path'); ?></th><th><?php echo _l('master_crm_fix_size'); ?></th><th><?php echo _l('master_crm_fix_modified'); ?></th></tr></thead><tbody>
<?php foreach($archives as $a): ?><tr><td><?php echo html_escape($a['path']); ?></td><td><?php echo round($a['size']/1048576,2); ?> MB</td><td><?php echo html_escape($a['modified']); ?></td></tr><?php endforeach; ?>
<?php if(!$archives): ?><tr><td colspan="3" class="text-center"><?php echo _l('master_crm_fix_no_archives'); ?></td></tr><?php endif; ?>
</tbody></table></div></div></div></div></div></div></div><?php init_tail(); ?>