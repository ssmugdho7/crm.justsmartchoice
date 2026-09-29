    <div class="modal fade z-index-none" id="deviceModal">
        <div class="modal-dialog setting-transaction-table modal-xxl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($device)){
                    $id = $device->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_device/'.$id), array('id' => 'add_edit_device', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $code = '';
                $serial_no = '';
                $model_id = '';
                $prod_date = '';
                $client_id = '';
                $purchase_date = '';
                $warranty_start_date = '';
                $warranty_period_months = '';
                $warranty_expiry_date = '';
                $warranty_expiring_alert = '';
                $description = '';
                $primary_profile_image = '';
                $category_id = '';
                $manufacturer_id = '';
                $warranty_expiring_alerts = wshop_warranty_expiring_alerts();

                if(isset($device)){
                    $name = $device->name;
                    $code = $device->code;
                    $serial_no = $device->serial_no;
                    $model_id = $device->model_id;
                    $prod_date = $device->prod_date;
                    $client_id = $device->client_id;
                    $purchase_date = $device->purchase_date;
                    $warranty_start_date = $device->warranty_start_date;
                    $warranty_period_months = $device->warranty_period_months;
                    $warranty_expiry_date = $device->warranty_expiry_date;
                    $warranty_expiring_alert = $device->warranty_expiring_alert;
                    $description = $device->description;
                    $primary_profile_image = $device->primary_profile_image;
                }

                if ($primary_profile_image != '' && file_exists(MAIN_IMAGE_DEVICES_IMAGES_FOLDER.$id.'/'.$primary_profile_image)) {
                    $bundle_image = '<img class="picture-src" id="wizardPicturePreview" src="' . site_url('modules/workshop/uploads/main_image_devices/'.$id.'/'.$primary_profile_image) . '" alt="'.$primary_profile_image.'" >';
                    $bundle_image .= '<input type="file" id="wizard-picture" class="form-control d-block  hide" name="primary_profile_image" id="primary_profile_image" accept="image/*">';
                }else{
                    $bundle_image = '<img class="picture-src" id="wizardPicturePreview" src="' . site_url('modules/workshop/assets/images/upload-image-icon.png') . '" >';
                    $bundle_image .= '<input type="file" id="wizard-picture" class="form-control d-block" name="primary_profile_image" id="primary_profile_image" accept="image/*">';
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-3">
                            <?php echo  render_input('code', 'wshop_code', $code, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo  render_input('name', 'wshop_name', $name, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo  render_input('serial_no', 'wshop_serial_no', $serial_no, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_select('client_id', $clients, ['userid', 'company'], 'client', $client_id); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                           <?php echo render_select('model_id', $models, ['id', 'name'], 'wshop_model', $model_id); ?>
                       </div>

                       <div class="col-md-3">
                        <?php echo render_date_input('prod_date', 'wshop_prod_date', $prod_date); ?>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-12">
                        <div id="fieldset">
                            <?php 
                            if(isset($fieldset_id)){
                                echo wshop_render_custom_fields('fieldset_'.$fieldset_id, isset($device) ? $device->id : '');
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row" id="customfield">
                    <div class="col-md-12">
                        <?php echo render_custom_fields('wshop_device', isset($device) ? $device->id : ''); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                       <?php echo render_date_input('purchase_date', 'wshop_purchase_date', $purchase_date); ?>
                   </div>
                   <div class="col-md-3">
                       <?php echo render_date_input('warranty_start_date', 'wshop_warranty_start_date', $warranty_start_date); ?>
                   </div>
                   <div class="col-md-3">

                    <div class="form-group">
                        <label for="warranty_period_months"><?php echo _l('wshop_warranty_period_months'); ?></label>
                        <div class="input-group">
                            <input type="number" name="warranty_period_months" class="form-control" value="<?php echo  html_entity_decode($warranty_period_months); ?>" step="1" min="0">
                            <span class="input-group-addon"><?php echo _l('wshop_months'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <?php echo render_date_input('warranty_expiry_date', 'wshop_warranty_expiry_date', $warranty_expiry_date); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?php echo render_select('warranty_expiring_alert', $warranty_expiring_alerts, ['name', 'label'], 'wshop_warranty_expiring_alert', $warranty_expiring_alert); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($device) || isset($device) && $device->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="article_image">
                        <div class="form-group">
                            <h5><?php echo _l('wshop_main_image'); ?></h5>
                            <div class="picture-container pull-left">
                                <div class="picture pull-left">
                                    <?php echo new_html_entity_decode($bundle_image); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <h5><?php echo _l('wshop_device_image'); ?></h5>

                    <div id="dropzoneDragArea" class="dz-default dz-message">
                        <span><?php echo _l('wshop_device_image'); ?></span>
                    </div>
                    <div class="dropzone-previews"></div>

                    <div id="images_old_preview">

                        <?php if( isset($product_attachments) && count($product_attachments) > 0){ ?>
                            <?php foreach ($product_attachments as $product_attachment) { ?>
                                <?php $rel_type = 'real_estate' ;?>

                                <?php if($rel_type != ''){ ?>
                                    <div class="dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                                        <div class="dz-image">
                                            <?php if(file_exists(DEVICES_IMAGES_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                                                <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/devices/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                                            <?php } ?>
                                        </div>

                                        <div class="dz-error-mark">
                                            <a class="dz-remove" data-dz-remove>Remove file</a>
                                        </div>
                                        <div class="remove_file">
                                            <a href="#" class="text-danger" onclick="delete_device_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
                                        </div>
                                    </div>
                                <?php } ?>

                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info device_submit_button"><?php echo _l('submit'); ?></button>
        </div>

    </div>

    <?php echo form_close(); ?>
</div>
</div>

<?php require 'modules/workshop/assets/js/devices/device_modal_js.php';  ?>
