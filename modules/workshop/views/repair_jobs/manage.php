<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php  init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('wshop_repair_jobs'); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <?php if(has_permission('workshop_repair_job', '', 'create')){ ?>
                                    <a href="<?php echo admin_url('workshop/add_edit_repair_job'); ?>" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('workshop/repair_job_calendar'); ?>" class="btn btn-default mright5 pull-right hidden-xs" data-toggle="tooltip" data-placement="top" data-title="Switch to Calendar" data-original-title="" title="">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </a>
                            </div>
                        </div>

                        <?php 
                        $warranty_status = wshop_warranty_status();
                         ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_date_input('from_date_filter', '', '', ['placeholder' => _l('wshop_from_date')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('to_date_filter', '', '', ['placeholder' => _l('wshop_to_date')]); ?>
                            </div>
                            
                            <div class="col-md-3">
                                <?php echo render_select('client_filter', $clients, ['userid', 'company'], '', '', ['data-none-selected-text' => _l('client')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('appointment_type_filter', $appointment_types, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_appointment_type')]); ?>
                            </div>
                            
                            
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                _l('wshop_job_tracking_number'),
                                _l('wshop_repair_job_id'),
                                array(
                                    'name'=>_l('wshop_appointment_date'),
                                    'th_attrs' => [
                                        'style' => 'max-width:95px',
                                    ],
                                ),
                                array(
                                    'name'=>_l('wshop_estimated_completion_date'),
                                    'th_attrs' => [
                                        'style' => 'max-width:150px',
                                    ],
                                ),
                                _l('wshop_appointment_type'),
                                array(
                                    'name'=>_l('client'),
                                    'th_attrs' => [
                                        'style' => 'min-width:210px',
                                    ],
                                ),
                                _l('wshop_branch_phone'),
                                array(
                                    'name'=>_l('wshop_device'),
                                    'th_attrs' => [
                                        'style' => 'min-width:210px',
                                    ],
                                ),
                                _l('wshop_model'),
                                _l('wshop_mechanic'),
                                _l('wshop_total'),
                                _l('wshop_estimated_hours'),
                                _l('wshop_status'),
                                _l('invoice'),
                            ),'repair_job_table'
                        );
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal_wrapper"></div>

<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/repair_jobs/manage_js.php');
?>
</body>
</html>
