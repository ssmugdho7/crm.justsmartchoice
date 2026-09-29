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
                                <h4><?php echo _l('wshop_inspections'); ?></h4>
                            </div>
                            <?php if(has_permission('workshop_inspection', '', 'create')){ ?>
                                <div class="col-md-6">
                                    <a href="#" onclick="inspection_modal(0); return false;" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

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
                                <?php echo render_select('inspection_type_filter', $inspection_types, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_inspection_type')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('device_filter', $devices, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_device')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('status_filter', $statuses, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_status')]); ?>
                            </div>
                            
                            <div class="col-md-3">
                                <?php echo render_select('repair_job_filter', $repair_jobs, ['id', ['job_tracking_number', 'name']], '', '', ['data-none-selected-text' => _l('wshop_repair_job')]); ?>
                            </div>
                           
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                [
                                    'name'  => _l('wshop_code'),
                                    'th_attrs' => [
                                        'style' => 'min-width:150px',
                                    ],
                                ],
                                _l('wshop_inspection_type'),
                                _l('wshop_inspection_template'),
                                _l('wshop_devices'),
                                [
                                    'name'  => _l('client'),
                                    'th_attrs' => [
                                        'style' => 'min-width:200px',
                                    ],
                                ],
                                [
                                    'name'  => _l('wshop_repair_job'),
                                    'th_attrs' => [
                                        'style' => 'min-width:200px',
                                    ],
                                ],
                                _l('wshop_start_date'),
                                _l('wshop_due_date'),
                                _l('wshop_interval'),
                                _l('wshop_next_inspection_date'),
                                _l('wshop_next_inspection_alert'),
                                _l('wshop_status'),
                                _l('wshop_visible_to_customer'),
                            ),'inspection_table'
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
require('modules/workshop/assets/js/inspections/manage_js.php');
?>
</body>
</html>
