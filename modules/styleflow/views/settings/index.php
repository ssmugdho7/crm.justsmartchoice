<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<div class="tw-flex tw-justify-between tw-items-center tw-mb-4"><h4 class="tw-m-0"><?php echo _l('styleflow_settings'); ?></h4><a href="<?php echo admin_url('styleflow/manage_templates'); ?>" class="btn btn-default"><i class="fa fa-palette"></i> <?php echo _l('styleflow_document_templates'); ?></a></div>
<?php $this->load->view('settings/general'); ?>
</div></div></div></div>
<?php init_tail(); ?>
