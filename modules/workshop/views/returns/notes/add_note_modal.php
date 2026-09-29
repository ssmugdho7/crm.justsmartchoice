    <div class="modal fade z-index-none" id="noteModal">
        <div class="modal-dialog setting-note-table modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($note)){
                    $id = $note->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_note/'.$id), array('id' => 'add_edit_note', 'autocomplete'=>'off')); ?>
                <?php 
                $description = '';

                if(isset($note)){
                    $return_delivery_id = $note->return_delivery_id;
                    $description = $note->description;
                }

                ?>
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <input type="hidden" name="return_delivery_id" value="<?php echo html_entity_decode($return_delivery_id); ?>">
                <input type="hidden" name="repair_job_id" value="<?php echo html_entity_decode($repair_job_id); ?>">
                <input type="hidden" name="transaction_type" value="<?php echo html_entity_decode($transaction_type); ?>">

                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($note) || isset($note) && $note->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
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
                                <?php if( isset($note_attachments) && count($note_attachments) > 0){ ?>
                                    <?php foreach ($note_attachments as $product_attachment) { ?>

                                        <div class="dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                                            <div class="dz-image">
                                                <?php if(is_image(note_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                                <?php if(file_exists(note_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                                                    <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/return_deliveries/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                                                <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <?php if(!is_image(note_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                            <div class="dz-details dz-active"><div class="dz-size"><span data-dz-size=""></span></div>    <div class="dz-filename"><span data-dz-name=""><?php echo html_entity_decode($product_attachment['file_name']); ?></span></div>  </div>
                                                <?php } ?>



                                            <div class="dz-error-mark">
                                                <a class="dz-remove" data-dz-remove>Remove file</a>
                                            </div>
                                            <div class="remove_file">
                                                <a href="#" class="text-danger" onclick="delete_note_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
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
                    <button type="submit" class="btn btn-info note_submit_button"><?php echo _l('submit'); ?></button>
                </div>
            </div>

        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php require 'modules/workshop/assets/js/notes/add_note_modal_js.php';  ?>
