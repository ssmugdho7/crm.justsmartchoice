<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer"><div class="content">
    <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?><div class="row"><div class="col-md-12">
  <div class="sc-card">
    <h3 class="sc-title"><i class="fa fa-info-circle"></i> <?php echo _l('properties'); ?></h3>
    <a class="btn btn-default" href="<?php echo admin_url('crm_file_explorer_manager'); ?>"><?php echo _l('back'); ?></a>
  </div>
  <div class="row">
    <div class="col-md-5"><div class="sc-card text-center">
      <?php if($file['file_type']==='image'){ ?><img class="modal-preview" src="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>"><?php } elseif($file['file_type']==='video'){ ?><video class="modal-preview" controls src="<?php echo admin_url('crm_file_explorer_manager/preview/'.$file['id']); ?>"></video><?php } else { ?><div class="sc-stat"><strong><?php echo strtoupper($file['extension']); ?></strong><?php echo _l('documents'); ?></div><?php } ?>
    </div></div>
    <div class="col-md-7"><div class="sc-card">
      <table class="table table-bordered">
        <tr><th><?php echo _l('file_name'); ?></th><td><?php echo html_escape($file['file_name']); ?></td></tr>
        <tr><th><?php echo _l('folder_path'); ?></th><td class="sc-path"><?php echo html_escape($file['relative_path']); ?></td></tr>
        <tr><th><?php echo _l('file_type'); ?></th><td><?php echo ucwords($file['file_type']); ?></td></tr>
        <tr><th><?php echo _l('file_size'); ?></th><td><?php echo number_format($file['file_size']); ?> <?php echo _l('bytes'); ?> / <?php echo number_format($file['file_size']/1024,2); ?> KB</td></tr>
        <tr><th><?php echo _l('created_time'); ?></th><td><?php echo html_escape($file['created_time']); ?></td></tr>
        <tr><th><?php echo _l('modified_time'); ?></th><td><?php echo html_escape($file['modified_time']); ?></td></tr>
        <tr><th><?php echo _l('module_or_folder'); ?></th><td><?php echo ucwords(str_replace('_',' ',html_escape($file['module_guess']))); ?></td></tr>
      </table>
    </div></div>
  </div>
  <div class="sc-card">
    <h4 class="sc-title"><?php echo _l('associations'); ?></h4>
    <?php if(empty($associations)){ ?><p><?php echo _l('no_associations_found'); ?></p><?php } else { ?>
    <table class="table table-striped sc-table"><thead><tr><th><?php echo _l('module'); ?></th><th><?php echo _l('table'); ?></th><th><?php echo _l('field'); ?></th><th><?php echo _l('matches'); ?></th></tr></thead><tbody>
      <?php foreach($associations as $a){ ?><tr><td><?php echo html_escape($a['module']); ?></td><td><?php echo html_escape($a['table']); ?></td><td><?php echo html_escape($a['field']); ?></td><td><?php echo (int)$a['matches']; ?></td></tr><?php } ?>
    </tbody></table>
    <?php } ?>
  </div>
</div></div></div></div><?php init_tail(); ?></body></html>
