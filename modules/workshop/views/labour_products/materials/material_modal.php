    <div class="modal fade z-index-none" id="materialModal">
        <div class="modal-dialog setting-transaction-table modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($material)){
                    $id = $material->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_material/'.$id), array('id' => 'add_edit_material', 'autocomplete'=>'off')); ?>
                <?php 

                $item_id = '';
                $quantity = '';

                if(isset($material)){
                    $item_id = $material->item_id;
                    $quantity = $material->quantity;
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <input type="hidden" name="labour_product_id" value="<?php echo html_entity_decode($labour_product_id); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo render_select('item_id', $items, ['itemid', 'description'], 'wshop_material', $item_id); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('quantity', 'wshop_quantity', $quantity, 'number', ['step' => 'any', 'min' => '0.0'], [], ''); ?>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info material_submit_button"><?php echo _l('submit'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/labour_products/material_modal_js.php';  ?>
