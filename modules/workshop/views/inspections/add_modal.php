    <div class="modal fade z-index-none" id="inspectionModal">
        <div class="modal-dialog setting-inspection-table modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($inspection)){
                    $id = $inspection->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_inspection/'.$id), array('id' => 'add_edit_inspection', 'autocomplete'=>'off')); ?>
                <?php 

                $name = '';
                $inspection_type_id = '';
                $device_id = '';
                $inspection_template_id = '';
                $client_id = '';
                $person_in_charge = get_staff_user_id();
                $start_date = _d(date('Y-m-d'));
                $end_date = '';
                $interval_id = '';
                $next_inspection_date = '';
                $next_inspection_alert = '';
                $due_date_date_alert = '';
                $description = '';
                $visible_to_customer = 'checked';
                $repair_job_id = isset($_repair_job_id) ? $_repair_job_id : '';

                if(isset($_device_id)){
                    $device_id = $_device_id;
                }
               
                if(isset($inspection)){
                    $visible_to_customer = '';
                    $inspection_type_id = $inspection->inspection_type_id;
                    $device_id = $inspection->device_id;
                    $inspection_template_id = $inspection->inspection_template_id;
                    $client_id = $inspection->client_id;
                    $person_in_charge = $inspection->person_in_charge;
                    $start_date = $inspection->start_date ? _d($inspection->start_date) : _d(date('Y-m-d'));
                    $end_date = $inspection->end_date ? _d($inspection->end_date) : NULL;
                    $interval_id = $inspection->interval_id;
                    $next_inspection_date = _d($inspection->next_inspection_date);
                    $next_inspection_alert = $inspection->next_inspection_alert;
                    $due_date_date_alert = $inspection->due_date_date_alert;
                    $description = $inspection->description;
                    $repair_job_id = $inspection->repair_job_id;
                    if($inspection->visible_to_customer == 1){
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
                            <?php

                            $next_inspection_number = get_option('wshop_inspection_number');
                            $format               = get_option('wshop_inspection_number_format');

                            if (isset($inspection)) {
                                $format = $inspection->number_format;
                            }

                            $prefix = get_option('wshop_inspection_prefix');

                            if ($format == 1) {
                                $__number = $next_inspection_number;
                                if (isset($inspection)) {
                                    $__number = $inspection->number;
                                    $prefix   = '<span id="prefix">' . $inspection->prefix . '</span>';
                                }
                            } elseif ($format == 2) {
                                if (isset($inspection)) {
                                    $__number = $inspection->number;
                                    $prefix   = $inspection->prefix;
                                    $prefix   = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($inspection->datecreated)) . '</span>/';
                                } else {
                                    $__number = $next_inspection_number;
                                    $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                                }
                            } elseif ($format == 3) {
                                if (isset($inspection)) {
                                    $yy       = date('y', strtotime($inspection->datecreated));
                                    $__number = $inspection->number;
                                    $prefix   = '<span id="prefix">' . $inspection->prefix . '</span>';
                                } else {
                                    $yy       = date('y');
                                    $__number = $next_inspection_number;
                                }
                            } elseif ($format == 4) {
                                if (isset($inspection)) {
                                    $yyyy     = date('Y', strtotime($inspection->datecreated));
                                    $mm       = date('m', strtotime($inspection->datecreated));
                                    $__number = $inspection->number;
                                    $prefix   = '<span id="prefix">' . $inspection->prefix . '</span>';
                                } else {
                                    $yyyy     = date('Y');
                                    $mm       = date('m');
                                    $__number = $next_inspection_number;
                                }
                            }

                            $_inspection_number     = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                            $isedit               = isset($inspection) ? 'true' : 'false';
                            $data_original_number = isset($inspection) ? $inspection->number : 'false';
                            ?>
                            <div class="form-group">
                                <label for="number"><?php echo _l('wshop_inspection_id'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-addon">

                                        <?php 
                                        echo new_html_entity_decode($prefix);
                                        ?>
                                    </span>
                                    <input type="text" name="number" class="form-control" value="<?php echo e($_inspection_number); ?>"
                                    data-isedit="<?php echo e($isedit); ?>"
                                    data-original-number="<?php echo e($data_original_number); ?>">
                                    <?php if ($format == 3) { ?>
                                        <span class="input-group-addon">
                                            <span id="prefix_year" class="format-n-yy"><?php echo e($yy); ?></span>
                                        </span>
                                    <?php } elseif ($format == 4) { ?>
                                        <span class="input-group-addon">
                                            <span id="prefix_month" class="format-mm-yyyy"><?php echo e($mm); ?></span>
                                            /
                                            <span id="prefix_year" class="format-mm-yyyy"><?php echo e($yyyy); ?></span>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('inspection_type_id', $inspection_types, ['id', 'name'], 'wshop_Inspection_Type', $inspection_type_id); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('repair_job_id', $repair_jobs, ['id', ['job_tracking_number','name']], 'wshop_repair_job', $repair_job_id, $repair_job_id_attr); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('inspection_template_id', $inspection_templates, ['id', 'name'], 'wshop_inspection_template', $inspection_template_id); ?>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="rj_client_id">
                                <div class="form-group select-placeholder">
                                    <label for="client_id" class="control-label"><?php echo _l('client'); ?></label>
                                    <select id="client_id" name="client_id" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($inspection) && empty($inspection->client_id)) {
                                        echo ' customer-removed';
                                    } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php $selected = (isset($inspection) ? $inspection->client_id : '');
                                    if ($selected == '') {
                                        $selected = (isset($customer_id) ? $customer_id: '');
                                    }
                                    if ($selected != '') {
                                        $rel_data = get_relation_data('customer', $selected);
                                        $rel_val  = get_relation_values($rel_data, 'customer');
                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>

                        </div>
                         <div class="col-md-3">
                            <?php echo render_select('device_id', $devices, ['id', ['name']], 'wshop_device', $device_id, $repair_job_id_attr); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_select('person_in_charge', $staffs, ['staffid', ['firstname', 'lastname']], 'wshop_person_in_charge', $person_in_charge); ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="tw-font-semibold"><?php echo _l('wshop_branch_phone'); ?></p>
                                    </div>

                                </div>
                                <address>
                                    <span class="client_phonenumber">
                                        <?php $phonenumber = (isset($inspection) ? $inspection->phonenumber : '--'); ?>
                                        <?php $phonenumber = ($phonenumber == '' ? '--' :$phonenumber); ?>
                                        <?php echo new_html_entity_decode($phonenumber); ?></span><br>
                                    </address>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="tw-font-semibold"><?php echo _l('wshop_contact_name'); ?></p>
                                        </div>
                                        
                                    </div>
                                    <address>
                                        <span class="contact_name">
                                            <?php $contact_name = (isset($inspection) ? $inspection->contact_name : '--'); ?>
                                            <?php $contact_name = ($contact_name == '' ? '--' :$contact_name); ?>
                                            <?php echo new_html_entity_decode($contact_name); ?></span><br>
                                        </address>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="tw-font-semibold"><?php echo _l('wshop_contact_email'); ?></p>
                                            </div>
                                            
                                        </div>
                                        <address>
                                            <span class="contact_email">
                                                <?php $contact_email = (isset($inspection) ? $inspection->contact_email : '--'); ?>
                                                <?php $contact_email = ($contact_email == '' ? '--' :$contact_email); ?>
                                                <?php echo new_html_entity_decode($contact_email); ?></span><br>
                                            </address>
                                        </div>
                                    </div>
                                    <input type="hidden" name="phonenumber" value="<?php echo html_entity_decode($phonenumber); ?>">
                                    <input type="hidden" name="contact_name" value="<?php echo html_entity_decode($contact_name); ?>">
                                    <input type="hidden" name="contact_email" value="<?php echo html_entity_decode($contact_email); ?>">
                        <div class="col-md-3">
                            <?php echo render_datetime_input('start_date', 'wshop_start_date', $start_date); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_datetime_input('end_date', 'wshop_due_date', $end_date); ?>
                        </div>     
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo render_select('interval_id', $intervals, ['id', ['name']], 'wshop_interval', $interval_id); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_date_input('next_inspection_date', 'wshop_next_inspection_date', $next_inspection_date); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('next_inspection_alert', 'wshop_next_inspection_alert', $next_inspection_alert, 'number') ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('due_date_date_alert', 'wshop_due_date_date_alert', $due_date_date_alert, 'number') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 hide">
                            <?php
                            $currency_attr = ['data-show-subtext' => true];
                            $currency_attr = apply_filters_deprecated('estimate_currency_disabled', [$currency_attr], '2.3.0', 'estimate_currency_attributes');
                            foreach ($currencies as $currency) {
                                if ($currency['isdefault'] == 1) {
                                    $currency_attr['data-base'] = $currency['id'];
                                }

                                if ($currency['isdefault'] == 1) {
                                    $selected = $currency['id'];
                                }
                            }
                            $currency_attr = hooks()->apply_filters('estimate_currency_attributes', $currency_attr);
                            ?>

                            <?php echo render_select('currency', $currencies, ['id', 'name', 'symbol'], 'estimate_add_edit_currency', $selected, $currency_attr, [], '', ''); ?>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo _l('wshop_visible_to_customer'); ?></label>
                                <div class="onoffswitch">
                                    <input type="checkbox" name="visible_to_customer" class="onoffswitch-checkbox" id="c_inspection" <?php echo html_entity_decode($visible_to_customer); ?>>
                                    <label class="onoffswitch-label" for="c_inspection"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php   echo render_textarea('description','wshop_description', $description, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($inspection) || isset($inspection) && $inspection->description == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>
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
                                <?php if( isset($inspection_attachments) && count($inspection_attachments) > 0){ ?>
                                    <?php foreach ($inspection_attachments as $product_attachment) { ?>

                                        <div class="pdf_attachment dz-preview dz-image-preview image_old <?php echo new_html_entity_decode($product_attachment['id']) ?>">
                                            <div class="dz-image">
                                                <?php if(is_image(INSPECTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                                <?php if(file_exists(INSPECTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>

                                                    <img class="dz-image" src="<?php echo site_url('modules/workshop/uploads/inspections/' . $product_attachment['rel_id'] . '/' . $product_attachment['file_name']) . '" alt="' . $product_attachment['file_name'] ?>" >

                                                <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <?php if(!is_image(INSPECTION_FOLDER . $product_attachment['rel_id'] . '/' . $product_attachment['file_name'])){ ?>
                                            <div class="dz-details dz-active"><div class="dz-size"><span data-dz-size=""></span></div>    <div class="dz-filename"><span data-dz-name=""><?php echo html_entity_decode($product_attachment['file_name']); ?></span></div>  </div>
                                                <?php } ?>



                                            <div class="dz-error-mark">
                                                <a class="dz-remove" data-dz-remove>Remove file</a>
                                            </div>
                                            <div class="remove_file">
                                                <a href="#" class="text-danger" onclick="delete_inspection_attachment(this,<?php echo new_html_entity_decode($product_attachment['id']); ?>); return false;"><i class="fa fa fa-times"></i></a>
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
                    <button type="submit" class="btn btn-info inspection_submit_button"><?php echo _l('submit'); ?></button>
                </div>
            </div>

        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php require 'modules/workshop/assets/js/inspections/add_modal_js.php';  ?>
