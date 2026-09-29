<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <input type="hidden" name="isedit">

                        <div class="row">
                            <div class="col-md-6">
                                <?php if(is_numeric($inspection->invoice_id)){ ?>
                                    <span class="tw-font-semibold tw-text-sm">
                                        <a href="<?php echo site_url('invoice/'.$inspection->invoice_id.'/'.workshop_get_invoice_hash($inspection->invoice_id)); ?>" target="_blank"><?php echo format_invoice_number($inspection->invoice_id); ?></a>
                                    </span><br>
                                <?php } ?>
                                <span class="tw-font-semibold tw-text-xl"><?php echo html_entity_decode( format_inspection_number($inspection->id).' - '.$inspection->inspection_template_name); ?></span>
                                
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo site_url('workshop/client/inspection_detail/'.$inspection->id.'?tab=detail'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <input type="hidden" name="inspection_id" value="<?php echo html_entity_decode($inspection->id); ?>">

                        <div class="row">
                            <div class="col-md-12">
                                <table class="table border no-mtop">
                                    <tbody>
                                        <tr class="project-overview text-center tw-font-semibold">
                                            <td class="border-right border-left" width="30%"><?php echo _l('wshop_inspection_type'); ?></td>
                                            <td class="border-right"><?php echo _l('wshop_person_in_charge') ; ?></td>
                                            <td class="border-right"><?php echo _l('wshop_created_by') ; ?></td>
                                            <td class="border-right"><?php echo _l('wshop_commpleted_date') ; ?></td>
                                        </tr>
                                        <tr class="project-overview text-center tw-font-semibold">
                                            <td class="border-right"><?php echo wshop_get_category_name($inspection->inspection_type_id); ?></td>
                                            <td class="border-right"><?php echo get_staff_full_name($inspection->person_in_charge) ; ?></td>
                                            <td class="border-right"><?php echo get_staff_full_name($inspection->staffid) ; ?></td>
                                            <td class="border-right"><?php echo _d($inspection->commpleted_date ?? '') ; ?></td>
                                        </tr>
                                        <tr class="project-overview">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php 
                        $CI = & get_instance();

                        ?>
                        <?php if(!$check_change_inspection_status){ ?>
                            <div class="col-md-6 pull-right">
                                <div class="form-group tw-flex tw-justify-between mtop10 pull-right inspection_approval_<?php echo html_entity_decode($inspection->id.'_0'); ?>">
                                    <a href="javascript:void(0)" onclick="inspection_reject(<?php echo html_entity_decode($inspection->id) ?>); return false;" class="btn btn-danger pull-right  mright5">
                                        <?php echo _l('wshop_reject'); ?>
                                    </a>
                                    <a href="javascript:void(0)" onclick="inspection_approve(<?php echo html_entity_decode($inspection->id) ?>); return false;" class="btn btn-success pull-right  mright5">
                                        <?php echo _l('wshop_approve'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                        <?php foreach ($inspection_forms as $key => $inspection_form) { ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="">
                                        <table class="table items4 items-preview invoice-items-preview">
                                            <thead class="">
                                                <tr>
                                                    <th colspan="2" width="76%" align="left"><?php echo new_html_entity_decode($inspection_form['name']); ?></th>
                                                    <th width="12%" align="center"><?php echo _l('wshop_good'); ?></th>
                                                    <th width="12%" align="center"><?php echo _l('wshop_repair'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $belongs_to = 'form_fieldset_'.$inspection_form['id'];
                                                $rel_id = $inspection->id;
                                                $items_pr = true;
                                                $inspection_form_id = 0;

                                                $CI->db->where('active', 1);
                                                $CI->db->where('fieldto', $belongs_to);
                                                $CI->db->order_by('field_order', 'asc');
                                                $fields = $CI->db->get(db_prefix() . 'wshop_inspection_form_details')->result_array();

                                                if (count($fields)) {
                                                    foreach ($fields as $field) {
                                                        $field['name'] = _wshop_maybe_translate_inspection_form_field_name($field['name'], $field['slug']);

                                                        $value = '';
                                                        $inspection_result = '';
                                                        $inspection_comment = '';
                                                        $inspection_approve = null;
                                                        $inspection_question_approval = '';
                                                        $inspection_form_id = $field['inspection_form_id'];
                                                        $inspection_hide_comment = true;
                                                        $inspection_result_good = '';
                                                        $inspection_result_repair = '';

                                                        $value = wshop_get_inspection_form_field_value($rel_id, $field['id'], ($items_pr ? 'items_pr' : $belongs_to), false);
                                                        $wshop_get_inspection_form_field_result = wshop_get_inspection_form_field_result($rel_id, $field['id'], ($items_pr ? 'items_pr' : $belongs_to), false);
                                                        $inspection_result = $wshop_get_inspection_form_field_result['result'];
                                                        $inspection_comment = $wshop_get_inspection_form_field_result['comment'];
                                                        $inspection_approve = $wshop_get_inspection_form_field_result['approve'];
                                                        $inspection_approved_date = $wshop_get_inspection_form_field_result['approved_date'];

                                                        if($inspection_approve == 'approved'){
                                                            $inspection_question_approval = '<br><br><span class="text-success tw-font-semibold">'.$inspection_approve.' on '._dt($inspection_approved_date).'</span>';
                                                        }elseif($inspection_approve == 'rejected'){
                                                            $inspection_question_approval = '<br><br><span class="text-danger tw-font-semibold">'.$inspection_approve.' on '._dt($inspection_approved_date).'</span>';
                                                        }

                                                        if($inspection_result == 'good'){
                                                            $inspection_result_good = '<i class="fa-regular fa-square-check fa-2xl color_74C0FC"></i>';
                                                            $inspection_result_repair = '';
                                                            $inspection_hide_comment = true;
                                                        }
                                                        if($inspection_result == 'repair'){
                                                            $inspection_hide_comment = false;
                                                            $inspection_result_good = '';
                                                            $inspection_result_repair = '<i class="fa-regular fa-square-check fa-2xl color_74C0FC"></i>';
                                                        }

                                                        if($field['required'] == 1 && ($field['type'] != 'select' && $field['type'] != 'multiselect')){
                                                            $field_name = '<html><small class="req text-danger">* </small>'.$field['name'].'</html>';
                                                        }else{
                                                            $field_name = '<html>'.$field['name'].'</html>';
                                                        }

                                                        if ($field['type'] == 'input' || $field['type'] == 'number') {
                                                            $t = $field['type'] == 'input' ? 'text' : 'number';
                                                        } elseif ($field['type'] == 'date_picker') {
                                                            $value = _d($value);
                                                        } elseif ($field['type'] == 'date_picker_time') {
                                                            $value = _dt($value);

                                                        } elseif ($field['type'] == 'textarea') {
                                                        } elseif ($field['type'] == 'colorpicker') {
                                                        } elseif ($field['type'] == 'attachment') {
                                                            $value = wshop_get_inspection_form_attachment_value($rel_id, $field['id'], ($items_pr ? 'items_pr' : $belongs_to), false, false);
                                                        } elseif ($field['type'] == 'select' || $field['type'] == 'multiselect') {

                                                        } elseif ($field['type'] == 'checkbox') {
                                                            $options = new_strlen($field['options']) ? json_decode($field['options']) : null;

                                                        } elseif ($field['type'] == 'link') {
                                                            if (startsWith($value, 'http')) {
                                                                $value = '<a href="' . $value . '" target="_blank">' . $value . '</a>';
                                                            }
                                                        }

                                                        ?>
                                                        <tr class="project-overview ">
                                                            <td class="" width="2%"></td>
                                                            <td class="border-right border-left" width="68%">
                                                                <?php echo new_html_entity_decode($field_name ?? ''); ?>
                                                                <span class="mleft10"><?php echo new_html_entity_decode($value ?? ''); ?></span>
                                                                <?php if(!$inspection_hide_comment){ ?>
                                                                    <br><br>
                                                                    <span class="mleft10 tw-font-semibold"><?php echo _l('wshop_comment'); ?></span>: <span><?php echo new_html_entity_decode($inspection_comment); ?></span>
                                                                <?php } ?>
                                                            </td>
                                                            <td class="border-right" align="center"><?php echo new_html_entity_decode($inspection_result_good); ?></td>
                                                            <td class="border-right" align="center"><?php echo new_html_entity_decode($inspection_result_repair); ?>
                                                            <?php if($inspection_result == 'repair' && is_null($inspection_approve)){ ?>
                                                                <div class="row form-group tw-flex tw-justify-between mtop10 inspection_approval_<?php echo html_entity_decode($inspection->id.'_'.$field['id']); ?>">
                                                                    <a href="javascript:void(0)" onclick="check_list_reject(<?php echo html_entity_decode($field['id']) ?>); return false;" class="btn btn-danger pull-right  mright5">
                                                                        <?php echo _l('wshop_reject'); ?>
                                                                    </a>
                                                                    <a href="javascript:void(0)" onclick="check_list_approve(<?php echo html_entity_decode($field['id']) ?>); return false;" class="btn btn-success pull-right  mright5">
                                                                        <?php echo _l('wshop_approve'); ?>
                                                                    </a>
                                                                </div>
                                                            <?php } ?>
                                                            <?php echo html_entity_decode($inspection_question_approval); ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } ?>


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php 
                        $labour_product_row_template = '';
                        $part_row_template = '';

                        $CI->db->from(db_prefix() . 'wshop_repair_job_labour_products');
                        $CI->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_products.inspection_form_detail_id', 'left');
                        $CI->db->where(db_prefix() . 'wshop_repair_job_labour_products.inspection_id', $rel_id);
                        $CI->db->where(db_prefix() . 'wshop_repair_job_labour_products.inspection_form_id', $inspection_form_id);
                        $CI->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
                        $inspection_labour_products = $CI->db->get()->result_array();


                        $CI->db->from(db_prefix() . 'wshop_repair_job_labour_materials');
                        $CI->db->join(db_prefix() . 'wshop_inspection_values', '' . db_prefix() . 'wshop_inspection_values.inspection_form_detail_id = ' . db_prefix() . 'wshop_repair_job_labour_materials.inspection_form_detail_id', 'left');
                        $CI->db->where(db_prefix() . 'wshop_repair_job_labour_materials.inspection_id', $rel_id);
                        $CI->db->where(db_prefix() . 'wshop_repair_job_labour_materials.inspection_form_id', $inspection_form_id);
                        $CI->db->where('('.db_prefix() . 'wshop_inspection_values.inspection_result = "good" OR ('.db_prefix() .'wshop_inspection_values.inspection_result = "repair" AND '.db_prefix().'wshop_inspection_values.approve = "approved"))');
                        $inspection_parts = $CI->db->get()->result_array();

                        if(count($inspection_labour_products) > 0){ ?>
                            <hr class="hr-panel-separator no-margin" />
                            <!-- labour product section -->
                            <div class="table-responsive s_table">
                                <div class="row">
                                    <div class="_buttons col-md-12">
                                        <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_labour_products'); ?></span>
                                    </div>
                                </div>

                                <div class="table-responsive s_table" >
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
                                                </tr>
                                            </thead>

                                            <tbody class="">
                                                <?php 
                                                $labour_index = 0;
                                                foreach ($inspection_labour_products as $key => $labour_product) {
                                                    $labour_index++;
                                                    ?>
                                                    <tr>
                                                        <td width="35%"><?php echo new_html_entity_decode($labour_product['name']) ?></td>
                                                        <td width="10%" align="right"><?php echo app_format_money($labour_product['unit_price'], $inspection->currency) ?></td>
                                                        <td width="10%" align="right"><?php echo new_html_entity_decode($labour_product['estimated_hours']) ?></td>
                                                        <td width="10%" align="right"><?php echo new_html_entity_decode($labour_product['qty']) ?></td>
                                                        <td width="10%" align="right"><?php echo new_html_entity_decode($labour_product['tax_name']) ?></td>
                                                        <td width="11%" align="right"><?php echo app_format_money($labour_product['subtotal'], $inspection->currency) ?></td>
                                                    </tr>
                                                <?php } ?>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            <?php } ?>

                            <?php if(count($inspection_parts) > 0){ ?>
                                <hr class="hr-panel-separator no-margin" />
                                <!-- labour product section -->
                                <div class="table-responsive s_table">
                                    <div class="row">
                                        <div class="_buttons col-md-12">
                                            <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_labour_products'); ?></span>
                                        </div>
                                    </div>

                                    <div class="table-responsive s_table" >
                                        <table class=" table labour_product-items-table2 items2 table-main-labour_product-edit has-calculations1 no-mtop no-mbot">
                                            <thead class="header_bg">
                                                <tr>
                                                    <th colspan="1" width="35%" class="product" align="left"><?php echo _l('wshop_product'); ?></th>
                                                    <th width="26%" class="description hide" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                                                        data-toggle="tooltip"
                                                        data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                                                        <?php echo _l('wshop_description'); ?></th>

                                                        <th width="10%" class="unit_price" align="right"><?php echo _l('wshop_unit_price'); ?></th>
                                                        <th width="10%" class="estimated_hours hide" align="right"><?php echo _l('wshop_estimated_quantity'); ?></th>
                                                        <th width="10%" class="estimated_hours" align="right"><?php echo _l('wshop_actual_qty'); ?></th>
                                                        <th width="10%" class="vat" align="right"><?php echo _l('wshop_vat'); ?></th> 
                                                        <th width="9%" class="discount hide" align="right"><?php echo _l('wshop_discount_percent'); ?></th> 
                                                        <th width="11%" class="sub_total" align="right"><?php echo _l('wshop_sub_total'); ?></th>
                                                    </tr>
                                                </thead>

                                                <tbody class="">
                                                    <?php 
                                                    $part_index = 0;
                                                    foreach ($inspection_parts as $key => $inspection_part) {
                                                        $part_index++;
                                                        ?>
                                                        <tr>
                                                            <td width="35%"><?php echo new_html_entity_decode($inspection_part['name']) ?></td>
                                                            <td width="10%" align="right"><?php echo app_format_money($inspection_part['rate'], $inspection->currency) ?></td>
                                                            <td width="10%" align="right"><?php echo new_html_entity_decode($inspection_part['qty']) ?></td>
                                                            <td width="10%" align="right"><?php echo new_html_entity_decode($inspection_part['tax_name']) ?></td>
                                                            <td width="11%" align="right"><?php echo app_format_money($inspection_part['subtotal'], $inspection->currency) ?></td>
                                                        </tr>
                                                    <?php } ?>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                <?php } ?>
                            <?php } ?>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="inspection_id" value="<?php echo html_entity_decode($inspection->id); ?>">

    <div id="modal_wrapper"></div>

    <div class="modal fade" id="inspection_approval_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <span class="add-title"><?php echo _l('wshop_inspection_approval'); ?></span>
                    </h4>
                </div>
                <?php echo form_open_multipart(admin_url('workshop/inspection_approval_form'), array('id' => 'inspection_approval_form', 'autocomplete'=>'off')); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="approve">
                            <input type="hidden" name="inspection_form_detail_id">
                            <input type="hidden" name="inspection_id" value="<?php echo html_entity_decode($inspection->id); ?>">
                            <?php echo render_textarea('approve_comment', 'wshop_comment'); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                    <button type="submit" class="btn btn-success inspection_approval_submit_button"><?php echo _l('confirm'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>

            <div id="box-loading"></div>
        </div>
    </div>


   <?php workshop_client_init_tail(); ?>

    <?php 
    require('modules/workshop/assets/js/inspections/inspection_template_forms/inspection_form_detail_js.php');
    ?>

