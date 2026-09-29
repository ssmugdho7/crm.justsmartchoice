<hr class="hr-panel-separator no-margin" />
<!-- labour product section -->
<div class="panel-body labour_product-item">
    <div class="row">
        <div class="_buttons col-md-12">
            <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_labour_products'); ?></span>
        </div>
    </div>

    <div class="table-responsive s_table overflow-y-scroll-50">
        <table class=" table labour_product-items-table2 items2 table-main-labour_product-edit has-calculations1 no-mtop no-mbot">
            <thead class="header_bg">
                <tr>
                    <th colspan="1" width="35%" class="product" align="left"><?php echo _l('wshop_product'); ?></th>
                    <th width="26%" class="description hide" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                        data-toggle="tooltip"
                        data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                        <?php echo _l('wshop_description'); ?></th>

                        <th width="10%" class="unit_price" align="right"><?php echo _l('wshop_unit_price'); ?></th>
                        <th width="10%" class="estimated_hours" align="right"><?php echo _l('wshop_estimated_hours'); ?></th>
                        <th width="10%" class="estimated_hours" align="right"><?php echo _l('wshop_quantity'); ?></th>
                        <th width="10%" class="vat" align="right"><?php echo _l('wshop_vat'); ?></th> 
                        <th width="9%" class="discount hide" align="right"><?php echo _l('wshop_discount_percent'); ?></th> 
                        <th width="11%" class="sub_total" align="right"><?php echo _l('wshop_sub_total'); ?></th>
                        <th align="center" width="5%" >
                            <a href="javascript:void(0)" onclick="add_labour_product(0); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i></a>
                        </th>
                    </tr>
                </thead>
            <?php if(is_mobile()){ ?>
                <a href="javascript:void(0)" onclick="add_labour_product(0); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i> <?php echo _l('po_add_item'); ?></a>
            <?php } ?>
            <tbody class="hidden">

            </tbody>
        </table>
    </div>

    <div class="table-responsive s_table overflow-y-scroll-80" >
        <table class="table labour_product-items-table items table-main-labour_product-edit has-calculations no-mtop">
            <tbody>
                <?php if(isset($labour_product_row_template)){ ?>
                    <?php echo html_entity_decode($labour_product_row_template); ?>
                <?php } ?>

            </tbody>
        </table>
        <div class="col-md-7 col-md-offset-5">
            <table class="table text-right">
                <tr id="labour_product_subtotal">
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_labour_sub_total'); ?> :</span>
                    </td>
                    <td class="labour_product_subtotal">
                    </td>
                </tr>
                <tr id="labour_product_discount_area" class="hide">
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_discount'); ?> :</span>
                    </td>
                    <td class="labour_product_discount_area">
                    </td>
                </tr>
                <tr>
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_labour_total'); ?> :</span>
                    </td>
                    <td class="labour_product_total">
                    </td>
                </tr>
            </table>
        </div>
        <div id="removed-labour-product-items"></div>

    </div>

</div>