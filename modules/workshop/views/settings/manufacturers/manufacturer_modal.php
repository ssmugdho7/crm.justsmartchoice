    <div class="modal fade z-index-none" id="manufacturerModal">
        <div class="modal-dialog setting-transaction-table">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($manufacturer)){
                    $id = $manufacturer->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_manufacturer/'.$id), array('id' => 'add_edit_manufacturer', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $url = '';
                $support_url = '';
                $phone = '';
                $email = '';
                $manufacture_image = '';

                if(isset($manufacturer)){
                    $name = $manufacturer->name;
                    $url = $manufacturer->url;
                    $support_url = $manufacturer->support_url;
                    $phone = $manufacturer->phone;
                    $email = $manufacturer->email;
                    $manufacture_image = $manufacturer->manufacture_image;
                }

                if ($manufacture_image != '' && file_exists(MANUFACTURER_IMAGES_FOLDER.$id.'/'.$manufacture_image)) {
                    $bundle_image = '<img class="picture-src" id="wizardPicturePreview" src="' . site_url('modules/workshop/uploads/manufacturers/'.$id.'/'.$manufacture_image) . '" alt="'.$manufacture_image.'" >';
                    $bundle_image .= '<input type="file" id="wizard-picture" class="form-control d-block  hide" name="manufacture_image" id="manufacture_image" accept="image/*">';
                }else{
                    $bundle_image = '<img class="picture-src" id="wizardPicturePreview" src="' . site_url('modules/workshop/assets/images/upload-image-icon.png') . '" >';
                    $bundle_image .= '<input type="file" id="wizard-picture" class="form-control d-block" name="manufacture_image" id="manufacture_image" accept="image/*">';
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">
                    <div class="row article_image">
                        <div class="col-md-12 mbot15">
                            <div class="form-group">
                                <h5><?php echo _l('wshop_image'); ?></h5>
                                <div class="picture-container pull-left">
                                    <div class="picture pull-left">
                                        <?php echo new_html_entity_decode($bundle_image); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo render_textarea('name', 'wshop_name', $name, ['rows' => 2, 'placeholder' => _l('wshop_name'),] ); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo  render_input('url', 'wshop_url', $url, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('support_url', 'wshop_support_url', $support_url, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('phone', 'wshop_support_phone', $phone, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo  render_input('email', 'wshop_support_email', $email, 'text', [], [], ''); ?>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info manufacturer_submit_button"><?php echo _l('submit'); ?></button>
                </div>

            </div>

            <?php echo form_close(); ?>
        </div>
    </div>

    <?php require 'modules/workshop/assets/js/settings/manufacturers/manufacturer_modal_js.php';  ?>
