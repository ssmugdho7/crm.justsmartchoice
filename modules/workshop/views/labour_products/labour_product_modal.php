    <div class="modal fade z-index-none" id="labour_productModal">
        <div class="modal-dialog setting-transaction-table modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($labour_product)){
                    $id = $labour_product->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_labour_product/'.$id), array('id' => 'add_edit_labour_product', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $code = '';
                $category_id = '';
                $standard_time = '';
                $labour_type = '';
                $labour_cost = '';
                $tax = '';
                $tax2 = '';
                $assign_staff = '';
                $description = '';
                $labour_fixed = 'checked';
                $labour_rate = '';

                if(isset($labour_product)){
                    $name = $labour_product->name;
                    $code = $labour_product->code;
                    $category_id = $labour_product->category_id;
                    $standard_time = $labour_product->standard_time;
                    $labour_type = $labour_product->labour_type;
                    $labour_cost = $labour_product->labour_cost;
                    $tax = $labour_product->tax;
                    $tax2 = $labour_product->tax2;
                    $assign_staff = $labour_product->assign_staff;
                    $description = $labour_product->description;

                    $labour_fixed = '';
                    $labour_rate = '';
                    if($labour_type == 'fixed'){
                        $labour_fixed = 'checked';
                    }
                    if($labour_type == 'rate'){
                        $labour_rate = 'checked';
                    }
                    
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <?php echo  render_input('code', 'wshop_code', $code, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo  render_input('name', 'wshop_name', $name, 'text', [], [], ''); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('category_id', $categories, ['id', 'name'], 'wshop_category', $category_id); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo  render_input('standard_time', 'wshop_standard_time', $standard_time, 'number', ['step' => 'any', 'min' => '0.0'], [], ''); ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('tax', $taxes, ['id', 'name'], 'wshop_tax1', $tax); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('tax2', $taxes, ['id', 'name'], 'wshop_tax2', $tax2); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="show_tax_per_item" class="control-label clearfix"><?php echo _l('wshop_rate_type'); ?></label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" id="y_opt_1_labour_type" name="labour_type" value="fixed" <?php echo html_entity_decode($labour_fixed); ?>>
                                    <label for="y_opt_1_labour_type"><?php echo _l('wshop_fixed_price') ?></label>
                                </div><br>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" id="y_opt_2_labour_type" name="labour_type" value="rate" <?php echo html_entity_decode($labour_rate); ?>>
                                    <label for="y_opt_2_labour_type"><?php echo _l('wshop_labour_rate').' ('._l('wshop_hours').')'; ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php echo  render_input('labour_cost', 'wshop_rate', $labour_cost, 'number', ['step' => 'any', 'min' => '0.0'], [], ''); ?>
                        </div>
                        <div class="col-md-6 hide">
                            <?php echo render_select('assign_staff', $staffs, ['staffid', ['firstname', 'lastname']], 'wshop_assign_staff', $assign_staff); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($labour_product) || isset($labour_product) && $labour_product->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info labour_product_submit_button"><?php echo _l('submit'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/labour_products/labour_product_modal_js.php';  ?>
