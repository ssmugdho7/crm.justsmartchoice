<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer">
  <div class="content">
    <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?>
    <?php $this->load->view('crm_file_explorer_manager/settings/settings'); ?>
  </div>
</div>
<?php init_tail(); ?>
</body></html>
