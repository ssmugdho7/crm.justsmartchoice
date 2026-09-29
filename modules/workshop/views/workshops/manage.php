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
                                <h4><?php echo _l('wshop_workshops'); ?></h4>
                            </div>
                            <?php if(has_permission('workshop_workshop', '', 'create')){ ?>
                                <div class="col-md-6">
                                    <a href="#" onclick="workshop_modal(0); return false;" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>

                        <?php 
                        $warranty_status = wshop_warranty_status();
                         ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_select('repair_job_filter', $repair_jobs, ['id', ['job_tracking_number', 'name']], '', '', ['data-none-selected-text' => _l('wshop_repair_job')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('report_type_filter', $report_types, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_Report_Type')]); ?>

                            </div>
                            
                            <div class="col-md-3">
                                <?php echo render_select('report_status_filter', $report_statuses, ['id', ['name']], '', '', ['data-none-selected-text' => _l('wshop_Report_Status')]); ?>
                                
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                _l('wshop_name'),
                                _l('wshop_repair_job'),
                                _l('wshop_Report_Type'),
                                _l('wshop_Report_Status'),
                                _l('wshop_mechanic'),
                                _l('wshop_from_date'),
                                _l('wshop_to_date'),
                                _l('wshop_parts_information'),
                                _l('wshop_notes'),
                                _l('wshop_visible_to_customer'),
                            ),'workshop_table'
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
require('modules/workshop/assets/js/workshops/manage_js.php');
?>
</body>
</html>
