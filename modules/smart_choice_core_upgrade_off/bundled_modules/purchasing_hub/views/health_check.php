<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <table class="table table-bordered purchasing-hub-table"><thead><tr><th>Check</th><th>Status</th></tr></thead><tbody>
  <?php foreach($checks as $check){ ?><tr><td><?php echo html_escape($check['label']); ?></td><td><span class="label label-<?php echo $check['status']==='OK' ? 'success':'warning'; ?>"><?php echo html_escape($check['status']); ?></span></td></tr><?php } ?>
  </tbody></table>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
