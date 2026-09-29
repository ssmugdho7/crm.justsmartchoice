<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?><link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer"><div class="content">
    <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?><div class="row"><div class="col-md-8">
  <div class="sc-card"><h3 class="sc-title"><i class="fa fa-heartbeat"></i> <?php echo _l('health_checker'); ?></h3><table class="table table-striped sc-table"><thead><tr><th><?php echo _l('health_check'); ?></th><th><?php echo _l('health_status'); ?></th></tr></thead><tbody>
    <?php foreach($checks as $c){ ?><tr><td><?php echo html_escape($c['name']); ?></td><td class="<?php echo $c['status']===_l('health_ok')?'sc-ok':'sc-bad'; ?>"><?php echo html_escape($c['status']); ?></td></tr><?php } ?>
  </tbody></table></div>
</div></div></div></div><?php init_tail(); ?></body></html>
