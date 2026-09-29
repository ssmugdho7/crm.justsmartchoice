<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4>
                                    <?php echo html_entity_decode($labour_product->code .' '. $labour_product->name); ?>
                                    <?php if($labour_product->status == 1){ ?>
                                        <span class="label label-success"><?php echo _l('wshop_active_label') ?></span>
                                    <?php } ?>
                                </h4>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo admin_url('workshop/labour_products'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="no-mbot">

                        <div class="row">


                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-5">
                                        <table class="table border table-striped no-mtop">
                                            <tbody>

                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_code'); ?></td>
                                                    <td><?php echo html_entity_decode($labour_product->code) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_name'); ?></td>
                                                    <td><?php echo html_entity_decode($labour_product->name) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_category'); ?></td>
                                                    <td><?php echo wshop_get_category_name($labour_product->category_id) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_labour_rate'); ?></td>
                                                    <?php 
                                                    $baseCurrency = get_base_currency();
                                                    if($labour_product->labour_type == 'fixed'){
                                                        $labour_rate = app_format_money($labour_product->labour_cost, $baseCurrency).' ('. _l('wshop_fixed_price').')';
                                                    }else{
                                                        $labour_rate = app_format_money($labour_product->labour_cost, $baseCurrency). ' ('. _l('wshop_hours').')';
                                                    }
                                                    ?>
                                                    <td><?php echo html_entity_decode($labour_rate) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <?php 
                                                    $tax1_name = '';
                                                    $tax2_name = '';
                                                    $tax1 = get_tax_by_id($labour_product->tax ?? 0);
                                                    $tax2 = get_tax_by_id($labour_product->tax2 ?? 0);
                                                    if($tax1){
                                                        $tax1_name = $tax1->name . '|' . $tax1->taxrate;
                                                    }
                                                    if($tax2){
                                                        $tax2_name = $tax2->name . '|' . $tax2->taxrate;
                                                    }
                                                    
                                                    ?>
                                                    <td class="bold"><?php echo _l('wshop_tax1'); ?></td>
                                                    <td><?php echo html_entity_decode($tax1_name) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_tax2'); ?></td>
                                                    <td><?php echo html_entity_decode($tax2_name) ; ?></td>
                                                </tr>
                                                <tr class="project-overview hide">
                                                    <td class="bold"><?php echo _l('wshop_assign_staff'); ?></td>
                                                    <td><?php echo html_entity_decode(get_staff_full_name($labour_product->assign_staff)) ; ?></td>
                                                </tr>
                                                <tr class="project-overview">
                                                    <td class="bold"><?php echo _l('wshop_status'); ?></td>
                                                    <?php 
                                                    if($labour_product->status == 1){
                                                        $labour_product_staus = '<span class="label label-success">'. _l('wshop_active') .'</span>';
                                                    } else{
                                                        $labour_product_staus = '<span class="label label-warning">'. _l('wshop_unactive') .'</span>';
                                                    }
                                                    ?>
                                                    <td><?php echo html_entity_decode($labour_product_staus) ; ?></td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="row mtop5">
                                            <div class="col-md-12">

                                                <div class="row">
                                                    <div class="col-md-6 ">
                                                        <h4><?php echo _l('wshop_materials'); ?></h4>
                                                    </div>
                                                    <?php if(has_permission('workshop_labour_product', '', 'create')){ ?>
                                                        <div class="col-md-6">
                                                            <a href="#" onclick="material_modal(0, <?php echo html_entity_decode($labour_product->id) ?>); return false;" class="btn btn-info pull-right display-block">
                                                                <?php echo _l('wshop_new'); ?>
                                                            </a>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="clearfix"></div>
                                                <?php 
                                                render_datatable(
                                                    array(
                                                        _l('id'),
                                                        _l('wshop_name'),
                                                        _l('wshop_quantity'),
                                                        _l('wshop_unit'),
                                                        _l('options'),
                                                    ),'material_table'
                                                );
                                                ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="no-mbot">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('wshop_description') ?></h4>
                                <p class=""><?php echo new_html_entity_decode(check_for_links($labour_product->description)); ?></p>
                            </div>

                        </div>
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_wrapper"></div>
<input type="hidden" name="labour_product_id" value="<?php echo html_entity_decode($labour_product->id); ?>">

<?php init_tail(); ?>

<?php 
require 'modules/workshop/assets/js/labour_products/labour_product_detail_js.php';

?>
</body>
</html>
