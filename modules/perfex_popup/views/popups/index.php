<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                     <?php if(has_permission('popups','','create')){ ?>
                     <div class="_buttons">
                        <a href="<?php echo admin_url('perfex_popup/popups/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_popup'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php } ?>
                    
                       <?php render_datatable(array(
                        _l('name'),
                        _l('is_enabled'),
                        _l('created_at'),
                        _l('updated_at'),
                        _l('actions'),
                        ),'popups'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('popups/modal_install'); ?>
<?php init_tail(); ?>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/index.js'); ?>"></script>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/popups/js/install_modal.js'); ?>"></script>

</body>
</html>
