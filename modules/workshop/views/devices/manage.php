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
                                <h4><?php echo _l('wshop_devices'); ?></h4>
                            </div>
                            <?php if(has_permission('workshop_device', '', 'create')){ ?>
                                <div class="col-md-6">
                                    <a href="#" onclick="device_modal(0); return false;" class="btn btn-info pull-right display-block">
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
                                <?php echo render_select('client_filter', $clients, ['userid', 'company'], '', '', ['data-none-selected-text' => _l('client')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('devices_filter', $devices, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_devices')]); ?>

                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('model_filter', $models, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_models')]); ?>
                                
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('warranty_status_filter', $warranty_status, ['name', 'label'], '', '', ['data-none-selected-text' => _l('wshop_warranty_status')]); ?>
                                
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                _l('wshop_image'),
                                _l('wshop_code'),
                                _l('wshop_name'),
                                _l('wshop_serial_no'),
                                _l('client'),
                                _l('wshop_model'),
                                _l('wshop_category'),
                                _l('wshop_manufacturer'),
                                _l('wshop_purchase_date'),
                                _l('wshop_last_maintenance_date'),
                                _l('wshop_next_maintenance_date'),
                                _l('wshop_warranty_period_months'),
                                _l('wshop_warranty_expiry_date'),
                                _l('wshop_warranty_status'),
                                _l('wshop_status'),
                                [
                                    'name'  => _l('options'),
                                    'th_attrs' => [
                                        'style' => 'min-width:80px',
                                    ],
                                ],
                            ),'device_table'
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
require('modules/workshop/assets/js/devices/manage_js.php');
?>
</body>
</html>
