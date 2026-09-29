<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="col-md-12 px-1">
            <div class="tw-mb-2 sm:tw-mb-4">
                <div class="_buttons">
                <?php if(has_permission('domain_manager', get_staff_user_id(), 'create')){ ?>
                    <a  class="btn btn-primary pull-left display-block new-proposal-btn" href="<?=admin_url('domain_manager/create')?>">
                        <i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('domain_manager_add') ?> 
                    </a>
                <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="row">
                <div class="col-md-12" id="small-table">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="panel-table-full">
                                <?php render_datatable([
                                    _l('id'),
                                    _l('domain_manager_domain_name'),
                                    _l('domain_manager_registrar'),
                                    _l('domain_manager_purchase_date'),
                                    _l('domain_manager_expiry_date'),
                                    _l('domain_manager_dns_hosting'),
                                    _l('domain_manager_status'),
                                    ], 'domain_manager'); ?>
                                </div>
                            </div>

                  
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php $this->load->view('create_edit_modal') ?>
<?php init_tail(); ?>



<script>
$(function() {
    initDataTable('.table-domain_manager', window.location.href);
});

</script>
</body>
</html>