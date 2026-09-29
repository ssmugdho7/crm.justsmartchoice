<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s accounting-template estimate">
    <div class="panel-body">
        <div class="row">
            <?php 
            $repair_job_statuses = repair_job_status();
            $job_tracking_number = $generate_job_tracking_number;
            $appointment_type_id = '';
            $name = '';
            $device_id = '';
            $branch_id = '';
            $billing_type_id = '';
            $delivery_type_id = '';
            $collection_type_id = '';
            $repair_job_status = 'In_Progress';
            if(isset($repair_job)){
                $job_tracking_number = $repair_job->job_tracking_number;
                $appointment_type_id = $repair_job->appointment_type_id;
                $name = $repair_job->name;
                $device_id = $repair_job->device_id;
                $branch_id = $repair_job->branch_id;
                $billing_type_id = $repair_job->billing_type_id;
                $delivery_type_id = $repair_job->delivery_type_id;
                $collection_type_id = $repair_job->collection_type_id;
                $repair_job_status = $repair_job->status;
            }
            ?>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('job_tracking_number', 'wshop_job_tracking_number', $job_tracking_number, '', ['readonly' => true]); ?>
                    </div>

                </div>
                <?php

                $next_repair_job_number = get_option('wshop_repair_job_number');
                $format               = get_option('wshop_repair_job_number_format');

                if (isset($repair_job)) {
                    $format = $repair_job->number_format;
                }

                $prefix = get_option('wshop_repair_job_prefix');

                if ($format == 1) {
                    $__number = $next_repair_job_number;
                    if (isset($repair_job)) {
                        $__number = $repair_job->number;
                        $prefix   = '<span id="prefix">' . $repair_job->prefix . '</span>';
                    }
                } elseif ($format == 2) {
                    if (isset($repair_job)) {
                        $__number = $repair_job->number;
                        $prefix   = $repair_job->prefix;
                        $prefix   = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($repair_job->appointment_date)) . '</span>/';
                    } else {
                        $__number = $next_repair_job_number;
                        $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                    }
                } elseif ($format == 3) {
                    if (isset($repair_job)) {
                        $yy       = date('y', strtotime($repair_job->appointment_date));
                        $__number = $repair_job->number;
                        $prefix   = '<span id="prefix">' . $repair_job->prefix . '</span>';
                    } else {
                        $yy       = date('y');
                        $__number = $next_repair_job_number;
                    }
                } elseif ($format == 4) {
                    if (isset($repair_job)) {
                        $yyyy     = date('Y', strtotime($repair_job->appointment_date));
                        $mm       = date('m', strtotime($repair_job->appointment_date));
                        $__number = $repair_job->number;
                        $prefix   = '<span id="prefix">' . $repair_job->prefix . '</span>';
                    } else {
                        $yyyy     = date('Y');
                        $mm       = date('m');
                        $__number = $next_repair_job_number;
                    }
                }

                $_repair_job_number     = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                $isedit               = isset($repair_job) ? 'true' : 'false';
                $data_original_number = isset($repair_job) ? $repair_job->number : 'false';
                ?>
                <div class="form-group">
                    <label for="number"><?php echo _l('wshop_repair_job_id'); ?></label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            
                            <?php 
                            echo new_html_entity_decode($prefix);
                            ?>
                        </span>
                        <input type="text" name="number" class="form-control" value="<?php echo e($_repair_job_number); ?>"
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
                <?php echo render_input('name', 'wshop_repair_job_name', $name); ?>
                <?php echo render_select('appointment_type_id', $appointment_types, ['id', 'name'], 'wshop_appointment_type', $appointment_type_id); ?>
                <div class="rj_client_id">
                    <div class="form-group select-placeholder">
                        <label for="client_id" class="control-label"><?php echo _l('client'); ?></label>
                        <select id="client_id" name="client_id" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($repair_job) && empty($repair_job->client_id)) {
                            echo ' customer-removed';
                        } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php $selected = (isset($repair_job) ? $repair_job->client_id : '');
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

            <div class="row">
                    <?php $this->load->view('repair_jobs/billing_and_shipping_template'); ?>
                    <div class="col-md-6">
                        <p class="tw-font-semibold"><?php echo _l('client_address'); ?></p>
                        <address>
                            <span class="billing_street">
                                <?php $billing_street = (isset($repair_job) ? $repair_job->billing_street : '--'); ?>
                                <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                                <?php echo new_html_entity_decode($billing_street); ?></span><br>
                            <span class="billing_city">
                                <?php $billing_city = (isset($repair_job) ? $repair_job->billing_city : '--'); ?>
                                <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                                <?php echo new_html_entity_decode($billing_city); ?></span>,
                            <span class="billing_state">
                                <?php $billing_state = (isset($repair_job) ? $repair_job->billing_state : '--'); ?>
                                <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                                <?php echo new_html_entity_decode($billing_state); ?></span>
                            <br />
                            <span class="billing_country">
                                <?php $billing_country = (isset($repair_job) ? get_country_short_name($repair_job->billing_country) : '--'); ?>
                                <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                                <?php echo new_html_entity_decode($billing_country); ?></span>,
                            <span class="billing_zip">
                                <?php $billing_zip = (isset($repair_job) ? $repair_job->billing_zip : '--'); ?>
                                <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                                <?php echo new_html_entity_decode($billing_zip); ?></span>
                        </address>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="tw-font-semibold"><?php echo _l('wshop_branch_phone'); ?></p>
                            </div>

                        </div>
                        <address>
                            <span class="client_phonenumber">
                                <?php $phonenumber = (isset($repair_job) ? $repair_job->phonenumber : '--'); ?>
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
                                <?php $contact_name = (isset($repair_job) ? $repair_job->contact_name : '--'); ?>
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
                                <?php $contact_email = (isset($repair_job) ? $repair_job->contact_email : '--'); ?>
                                <?php $contact_email = ($contact_email == '' ? '--' :$contact_email); ?>
                                <?php echo new_html_entity_decode($contact_email); ?></span><br>
                        </address>
                    </div>
                </div>
                <input type="hidden" name="phonenumber" value="<?php echo html_entity_decode($phonenumber); ?>">
                <input type="hidden" name="contact_name" value="<?php echo html_entity_decode($contact_name); ?>">
                <input type="hidden" name="contact_email" value="<?php echo html_entity_decode($contact_email); ?>">
                    
                  
              </div>
              <div class="col-md-6">
                <div class="tw-ml-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?php $value = (isset($repair_job) ? _d($repair_job->appointment_date) : _d(date('Y-m-d H:i:s'))); ?>
                            <?php echo render_datetime_input('appointment_date', 'wshop_appointment_date', $value); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if (isset($repair_job)) {
                                $value = _d($repair_job->estimated_completion_date);
                            } else {
                                if (get_option('estimate_due_after') != 0) {
                                    $value = _d(date('Y-m-d H:i:s', strtotime('+' . get_option('estimate_due_after') . ' DAY', strtotime(date('Y-m-d H:i:s')))));
                                }
                            }
                            echo render_datetime_input('estimated_completion_date', 'wshop_estimated_completion_date', $value); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('device_id', $devices, ['id', 'name'], 'wshop_device', $device_id); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            echo render_select('branch_id', $branches, ['id', 'name'], 'wshop_repair_location', $branch_id);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('billing_type_id', $billing_types, ['id', 'name'], 'wshop_billing_type', $billing_type_id); ?>
                        </div>
                        <div class="col-md-6">
                            
                            <?php
                            echo render_select('delivery_type_id', $delivery_types, ['id', 'name'], 'wshop_delivery_type', $delivery_type_id);
                            ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            echo render_select('collection_type_id', $collection_types, ['id', 'name'], 'wshop_collection_type', $collection_type_id);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $selected = !isset($repair_job) && get_option('automatically_set_logged_in_staff_sales_agent') == '1' ? get_staff_user_id() : '';
                            foreach ($staff as $member) {
                                if (isset($repair_job)) {
                                    if ($repair_job->sale_agent == $member['staffid']) {
                                        $selected = $member['staffid'];
                                    }
                                }
                            }
                            echo render_select('sale_agent', $staff, ['staffid', ['firstname', 'lastname']], 'wshop_mechanic', $selected);
                            ?>
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

                           
                            <div class="col-md-6">
                                <div class="form-group select-placeholder">
                                    <label class="control-label"><?php echo _l('estimate_status'); ?></label>
                                    <select class="selectpicker display-block mbot15" name="status" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <?php foreach ($repair_job_statuses as $status) { ?>
                                            <option value="<?php echo e($status['id']); ?>" <?php if ($repair_job_status == $status['id']) {
                                                echo 'selected';
                                            } ?>><?php echo html_entity_decode($status['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($repair_job) ? $repair_job->reference_no : ''); ?>
                                <?php echo render_input('reference_no', 'reference_no', $value); ?>
                            </div>
                        
                        <div class="col-md-12">
                            <div class="form-group select-placeholder">
                                <label for="discount_type"
                                class="control-label"><?php echo _l('discount_type'); ?></label>
                                <select name="discount_type" class="selectpicker" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value="" selected><?php echo _l('no_discount'); ?></option>
                                <option value="before_tax" <?php
                                if (isset($repair_job)) {
                                  if ($repair_job->discount_type == 'before_tax') {
                                      echo 'selected';
                                  }
                              }?>><?php echo _l('discount_type_before_tax'); ?></option>
                              <option value="after_tax" <?php if (isset($repair_job)) {
                                  if ($repair_job->discount_type == 'after_tax') {
                                      echo 'selected';
                                  }
                              } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                          </select>
                      </div>
                  </div>
              </div>
              <?php $issue_description = (isset($repair_job) ? $repair_job->issue_description : ''); ?>
              <?php echo render_textarea('issue_description', 'wshop_issue_description', $issue_description); ?>

          </div>
      </div>
  </div>
</div>

<hr class="hr-panel-separator no-margin" />
<!-- labour product section -->
<div class="panel-body labour_product-item">
    <div class="row">
        <div class="_buttons col-md-12">
            <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_labour_products'); ?></span>
        </div>
    </div>

    <div class="table-responsive s_table  overflow-y-scroll-50">
        <table class=" table labour_product-items-table2 items2 table-main-labour_product-edit has-calculations1 no-mtop no-mbot">
            <thead class="header_bg">
                <tr>
                    <th colspan="1" width="17%" class="product" align="left"><?php echo _l('wshop_product'); ?></th>
                    <th width="26%" class="description" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                        data-toggle="tooltip"
                        data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                        <?php echo _l('wshop_description'); ?></th>

                        <th width="8%" class="unit_price" align="right"><?php echo _l('wshop_unit_price'); ?></th>
                        <th width="8%" class="estimated_hours" align="right"><?php echo _l('wshop_estimated_hours'); ?></th>
                        <th width="8%" class="estimated_hours" align="right"><?php echo _l('wshop_quantity'); ?></th>
                        <th width="8%" class="vat" align="right"><?php echo _l('wshop_vat'); ?></th> 
                        <th width="9%" class="discount " align="right"><?php echo _l('wshop_discount_percent'); ?></th> 
                        <th width="11%" class="sub_total" align="right"><?php echo _l('wshop_sub_total'); ?></th>
                        <th align="center" width="5%" >
                            <a href="javascript:void(0)" onclick="add_labour_product(); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i></a>
                        </th>
                    </tr>
                </thead>
            <?php if(is_mobile()){ ?>
                <a href="javascript:void(0)" onclick="add_labour_product(); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i> <?php echo _l('po_add_item'); ?></a>
            <?php } ?>
            <tbody class="hidden">

            </tbody>
        </table>
    </div>

    <div class="table-responsive s_table overflow-y-scroll-80" >
        <table class="table labour_product-items-table items table-main-labour_product-edit has-calculations no-mtop">
            <tbody>
                <?php echo html_entity_decode($labour_product_row_template); ?>
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
                <tr id="labour_product_discount_area">
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

<!-- Part section -->
<div class="panel-body part-item">
    <div class="row">
        <div class="_buttons col-md-12">
            <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_parts'); ?></span>
        </div>
    </div>

    <div class="table-responsive s_table overflow-y-scroll-50" >
        <table class=" table part-items-table3 items3 table-main-part-edit has-calculations1 no-mtop no-mbot">
            <thead class="header_bg">
                <tr>
                    <th colspan="1" width="17%" class="product" align="left"><?php echo _l('wshop_product'); ?></th>
                    <th width="26%" class="description" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                        data-toggle="tooltip"
                        data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                        <?php echo _l('wshop_description'); ?></th>

                        <th width="8%" class="unit_price" align="right"><?php echo _l('wshop_unit_price'); ?></th>
                        <th width="8%" class="estimated_hours" align="right"><?php echo _l('wshop_estimated_quantity'); ?></th>
                        <th width="8%" class="estimated_hours" align="right"><?php echo _l('wshop_actual_qty'); ?></th>
                        <th width="8%" class="vat" align="right"><?php echo _l('wshop_vat'); ?></th> 
                        <th width="9%" class="discount " align="right"><?php echo _l('wshop_discount_percent'); ?></th> 
                        <th width="11%" class="sub_total" align="right"><?php echo _l('wshop_sub_total'); ?></th>
                        <th align="center" width="5%" >
                            <a href="javascript:void(0)" onclick="add_part(); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i></a>
                        </th>
                    </tr>
                </thead>
            <?php if(is_mobile()){ ?>
                <a href="javascript:void(0)" onclick="add_part(); return false;" class="btn btn-sm btn-primary pull-right"><i class="fa-solid fa-plus fa-lg"></i> <?php echo _l('po_add_item'); ?></a>
            <?php } ?>
            <tbody class="hidden">

            </tbody>
        </table>
    </div>

    <div class="table-responsive s_table overflow-y-scroll-80" >
        <table class="table part-items-table items table-main-part-edit has-calculations no-mtop">
            <tbody>
                <?php echo html_entity_decode($part_row_template); ?>
            </tbody>
        </table>
        <div class="col-md-7 col-md-offset-5">
            <table class="table text-right">
                <tr id="material_subtotal">
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_part_sub_total'); ?> :</span>
                    </td>
                    <td class="material_subtotal">
                    </td>
                </tr>
                <tr id="material_discount_area">
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_discount'); ?> :</span>
                    </td>
                    <td class="material_discount_area">
                    </td>
                </tr>
                <tr>
                    <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_part_total'); ?> :</span>
                    </td>
                    <td class="material_total">
                    </td>
                </tr>
            </table>
        </div>
        <div id="removed-part-items"></div>

    </div>
</div>

<div class="panel-body">

    <div class="col-md-7 col-md-offset-5">
        <table class="table text-right">
            <tr id="t_subtotal">
                <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_sub_total'); ?> :</span>
                </td>
                <td class="t_subtotal">
                </td>
            </tr>
            <tr id="total_tax_area">
                <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_total_tax'); ?> :</span>
                </td>
                <td class="total_tax_area">
                </td>
            </tr>
            <tr id="discount_area">
                <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_discount'); ?> :</span>
                </td>
                <td class="discount_area">
                </td>
            </tr>
            <tr>
                <td><span class="bold tw-text-neutral-700"><?php echo _l('wshop_estimated_total'); ?> :</span>
                </td>
                <td class="total">
                </td>
            </tr>
        </table>
    </div>
    <?php
    $job_description = (isset($repair_job) ? $repair_job->job_description : '');
    echo render_textarea('job_description', 'wshop_job_description', $job_description);
    $additional_description = (isset($repair_job) ? $repair_job->additional_description : '');
    echo render_textarea('additional_description', 'wshop_additional_description', $additional_description, [], [], 'mtop15');
    $terms = (isset($repair_job) ? $repair_job->terms : get_option('wshop_repair_job_terms'));
    echo render_textarea('terms', 'terms_and_conditions', $terms, [], [], 'mtop15', 'tinymce'); 
    ?>
</div>
</div>

<div class="btn-bottom-pusher"></div>
<div class="btn-bottom-toolbar text-right">
    <a href="<?php echo admin_url('workshop/repair_jobs'); ?>" class="btn-tr btn btn-default">
        <?php echo _l('close'); ?>
    </a>
    <?php if((isset($repair_job) && ($repair_job->status == 'Booked_In' || $repair_job->status == 'In_Progress')) || !isset($repair_job)){ ?>
    <div class="btn-group dropup">
        <button type="button" class="btn-tr btn btn-primary estimate-form-submit repair-submit">
            <?php echo _l('submit'); ?>
        </button>
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right width200 hide">
        <li>
            <a href="#" class="estimate-form-submit save-and-send repair-submit">
                <?php echo _l('save_and_send'); ?>
            </a>
        </li>
        <li>
            <a href="#" class="estimate-form-submit repair-submit save-and-send-later">
                <?php echo _l('save_and_send_later'); ?>
            </a>
        </li>
    </ul>
</div>
<?php } ?>
</div>
