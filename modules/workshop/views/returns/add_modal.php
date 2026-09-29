    <div class="modal fade z-index-none" id="transactionModal">
        <div class="modal-dialog setting-transaction-table modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($transaction)){
                    $id = $transaction->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_transaction/'.$id), array('id' => 'add_edit_transaction', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $delivery_method_id = '';
                $expected_delivery_date = '';
                $billing_street = '';
                $billing_city = '';
                $billing_state = '';
                $billing_zip = '';
                $billing_country = '';
                $shipping_street = '';
                $shipping_city = '';
                $shipping_state = '';
                $shipping_zip = '';
                $shipping_country = '';
                $description = '';
                $status = 'Pending';
                $countries       = get_all_countries();

                if(isset($transaction)){
                    $name = $transaction->name;
                    $delivery_method_id = $transaction->delivery_method_id;
                    $expected_delivery_date = $transaction->expected_delivery_date;
                    $transaction_type = $transaction->transaction_type;
                    $description = $transaction->description;
                    $status = $transaction->status;
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <input type="hidden" name="transaction_type" value="<?php echo html_entity_decode($transaction_type); ?>">
                <input type="hidden" name="repair_job_id" value="<?php echo html_entity_decode($repair_job_id); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo  render_input('name', 'wshop_name', $name, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('repair_job_id', $repair_jobs, ['id', 'job_tracking_number'], 'wshop_job_tracking_number', $repair_job_id, ['disabled' => true]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('client_id', $clients, ['userid', 'company'], 'client', $client_id, ['disabled' => true]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('delivery_method_id', $categories, ['id', 'name'], 'wshop_delivery_method', $delivery_method_id); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_date_input('expected_delivery_date', 'wshop_expected_delivery_date', $expected_delivery_date); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('status', transaction_status(), ['id', 'name'], 'wshop_status', $status); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php $value = (isset($transaction) ? $transaction->billing_street : ''); ?>
                            <?php echo render_textarea('billing_street', 'wshop_sender_address', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->billing_city : ''); ?>
                            <?php echo render_input('billing_city', 'billing_city', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->billing_state : ''); ?>
                            <?php echo render_input('billing_state', 'billing_state', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->billing_zip : ''); ?>
                            <?php echo render_input('billing_zip', 'billing_zip', $value); ?>
                            <?php $selected = (isset($transaction) ? $transaction->billing_country : ''); ?>
                            <?php echo render_select('billing_country', $countries, [ 'country_id', [ 'short_name']], 'billing_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php $value = (isset($transaction) ? $transaction->shipping_street : ''); ?>
                            <?php echo render_textarea('shipping_street', 'wshop_receipt_address', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->shipping_city : ''); ?>
                            <?php echo render_input('shipping_city', 'shipping_city', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->shipping_state : ''); ?>
                            <?php echo render_input('shipping_state', 'shipping_state', $value); ?>
                            <?php $value = (isset($transaction) ? $transaction->shipping_zip : ''); ?>
                            <?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
                            <?php $selected = (isset($transaction) ? $transaction->shipping_country : ''); ?>
                            <?php echo render_select('shipping_country', $countries, [ 'country_id', [ 'short_name']], 'shipping_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($transaction) || isset($transaction) && $transaction->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <h5><?php echo _l('wshop_attachments'); ?></h5>

                            <div id="dropzoneDragArea" class="dz-default dz-message">
                                <span><?php echo _l('wshop_attachments'); ?></span>
                            </div>
                            <div class="dropzone-previews"></div>

                            <div id="images_old_preview">
                                <?php if( isset($transaction_attachments) && count($transaction_attachments) > 0){ ?>
                                    <?php foreach ($transaction_attachments as $product_attachment) { ?>

                                        <div class="dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                                            <div class="dz-image">
                                                <?php if(is_image(TRANSACTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                                <?php if(file_exists(TRANSACTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                                                    <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/return_deliveries/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                                                <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <?php if(!is_image(TRANSACTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                            <div class="dz-details dz-active"><div class="dz-size"><span data-dz-size=""></span></div>    <div class="dz-filename"><span data-dz-name=""><?php echo html_entity_decode($product_attachment['file_name']); ?></span></div>  </div>
                                                <?php } ?>



                                            <div class="dz-error-mark">
                                                <a class="dz-remove" data-dz-remove>Remove file</a>
                                            </div>
                                            <div class="remove_file">
                                                <a href="#" class="text-danger" onclick="delete_transaction_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
                                            </div>
                                        </div>

                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info transaction_submit_button"><?php echo _l('submit'); ?></button>
                </div>
            </div>

        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php require 'modules/workshop/assets/js/returns/add_modal_js.php';  ?>
