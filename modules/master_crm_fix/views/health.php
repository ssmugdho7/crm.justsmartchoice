<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><?php $this->load->view('partials/nav'); ?>
<div class="panel_s"><div class="panel-body"><h4><?php echo _l('master_crm_fix_health_check'); ?></h4>
<table class="table table-striped"><thead><tr><th><?php echo _l('master_crm_fix_check'); ?></th><th><?php echo _l('master_crm_fix_result'); ?></th></tr></thead><tbody>
<?php foreach($checks as $n=>$r): ?><tr><td><?php echo html_escape($n); ?></td><td><span class="label label-<?php echo $r==='OK'?'success':'warning'; ?>"><?php echo html_escape($r); ?></span></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div></div></div><?php init_tail(); ?>