    <div class="modal fade z-index-none" id="workshopModal">
        <div class="modal-dialog setting-workshop-table modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($workshop)){
                    $id = $workshop->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_workshop/'.$id), array('id' => 'add_edit_workshop', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $report_type_id = '';
                $report_status_id = '';
                $sale_agent = '';
                $from_date = '';
                $to_date = '';
                $parts_information = '';
                $description = '';
                $visible_to_customer = 'checked';
                $repair_job_id = isset($_repair_job_id) ? $_repair_job_id : '';

                if(isset($workshop)){
                    $visible_to_customer = '';
                    $name = $workshop->name;
                    $report_type_id = $workshop->report_type_id;
                    $report_status_id = $workshop->report_status_id;
                    $sale_agent = $workshop->sale_agent;
                    $from_date = $workshop->from_date ? _dt($workshop->from_date) : NULL;
                    $to_date = $workshop->to_date ? _dt($workshop->to_date) : NULL;
                    $description = $workshop->description;
                    $parts_information = $workshop->parts_information;
                    $repair_job_id = $workshop->repair_job_id;
                    if($workshop->visible_to_customer == 1){
                        $visible_to_customer = 'checked';
                    }
                }
                $repair_job_id_attr = [];
                if($repair_job_id != ''){
                    $repair_job_id_attr = ['disabled' => true];
                }

                ?>
               
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo  render_input('name', 'wshop_name', $name, 'text', [], [], ''); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('report_type_id', $report_types, ['id', 'name'], 'wshop_Report_Type', $report_type_id); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('repair_job_id', $repair_jobs, ['id', ['job_tracking_number','name']], 'wshop_job_tracking_number', $repair_job_id, $repair_job_id_attr); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('report_status_id', $report_statuses, ['id', 'name'], 'wshop_Report_Status', $report_status_id); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo render_select('sale_agent', $staffs, ['staffid', ['firstname', 'lastname']], 'wshop_mechanic', $sale_agent); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_datetime_input('from_date', 'wshop_from_date', $from_date); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_datetime_input('to_date', 'wshop_to_date', $to_date); ?>
                        </div>     
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo _l('wshop_visible_to_customer'); ?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="visible_to_customer" class="onoffswitch-checkbox" id="c_workshop" <?php echo html_entity_decode($visible_to_customer); ?>>
                                    <label class="onoffswitch-label" for="c_workshop"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo render_textarea('parts_information','wshop_parts_information', $parts_information); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($workshop) || isset($workshop) && $workshop->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
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
                                <?php if( isset($workshop_attachments) && count($workshop_attachments) > 0){ ?>
                                    <?php foreach ($workshop_attachments as $product_attachment) { ?>

                                        <div class="pdf_attachment dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                                            <div class="dz-image">
                                                <?php if(is_image(WORKSHOP_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                                <?php if(file_exists(WORKSHOP_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                                                    <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/workshops/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                                                <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <?php if(!is_image(WORKSHOP_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                            <div class="dz-details dz-active"><div class="dz-size"><span data-dz-size=""></span></div>    <div class="dz-filename"><span data-dz-name=""><?php echo html_entity_decode($product_attachment['file_name']); ?></span></div>  </div>
                                                <?php } ?>



                                            <div class="dz-error-mark">
                                                <a class="dz-remove" data-dz-remove>Remove file</a>
                                            </div>
                                            <div class="remove_file">
                                                <a href="#" class="text-danger" onclick="delete_workshop_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
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
                    <button type="submit" class="btn btn-info workshop_submit_button"><?php echo _l('submit'); ?></button>
                </div>
            </div>

        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php require 'modules/workshop/assets/js/workshops/add_modal_js.php';  ?>
