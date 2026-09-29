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
                                <h4><?php echo _l('wshop_labour_products'); ?></h4>
                            </div>
                            <?php if(has_permission('workshop_labour_product', '', 'create')){ ?>
                                <div class="col-md-6">
                                    <a href="#" onclick="labour_product_modal(0); return false;" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <?php 
                        $status = [];
                        $status[] = [
                            'id' => '-1',
                            'label' => _l('wshop_unactive'),
                        ];
                        $status[] = [
                            'id' => '1',
                            'label' => _l('wshop_active'),
                        ];
                        
                        ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_select('category_filter', $categories, ['id', 'name'], '', '', ['data-none-selected-text' => _l('wshop_category')]); ?>
                            </div>
                            
                            <div class="col-md-3">
                                <?php echo render_select('status_filter', $status, ['id', 'label'], '', '', ['data-none-selected-text' => _l('wshop_status')]); ?>
                            </div>
                            <div class="col-md-3 hide">
                                <?php echo render_select('assign_staff_filter', $staffs, ['staffid', ['firstname', 'lastname']], '', '', ['data-none-selected-text' => _l('wshop_assign_staff')]); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <?php 
                        render_datatable(
                            array(
                                _l('id'),
                                _l('wshop_code'),
                                _l('wshop_name'),
                                _l('wshop_category'),
                                _l('wshop_standard_time'),
                                _l('wshop_labour_rate'),
                                _l('wshop_tax1'),
                                _l('wshop_tax2'),
                                _l('wshop_assign_staff'),
                                _l('wshop_status'),
                                _l('options'),
                            ),'labour_product_table'
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
require('modules/workshop/assets/js/labour_products/manage_js.php');
?>
</body>
</html>
