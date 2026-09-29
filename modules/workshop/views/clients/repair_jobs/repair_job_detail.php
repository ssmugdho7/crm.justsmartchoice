<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="mtop15 preview-top-wrapper">
	<div class="row">
            <input type="hidden" name="workshop_detail" value="1">
            <input type="hidden" name="_repair_job_id" value="<?php echo new_html_entity_decode($repair_job->id); ?>">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <?php if(is_numeric($repair_job->invoice_id)){ ?>
                                    <span class="tw-font-semibold tw-text-sm">
                                        <a href="<?php echo site_url('invoice/'.$repair_job->invoice_id.'/'.workshop_get_invoice_hash($repair_job->invoice_id)); ?>" target="_blank"><?php echo format_invoice_number($repair_job->invoice_id); ?></a>
                                    </span><br>
                                <?php } ?>

                                <?php if(count($_inspection) > 0){ ?>
                                    <?php if(is_numeric($_inspection[0]['invoice_id'])){ ?>
                                        <span class="tw-font-semibold tw-text-sm">
                                            <a href="<?php echo site_url('invoice/'.$_inspection[0]['invoice_id'].'/'.workshop_get_invoice_hash($_inspection[0]['invoice_id'])); ?>" target="_blank"><?php echo format_invoice_number($_inspection[0]['invoice_id']); ?></a>
                                        </span><br>
                                    <?php } ?>
                                <?php } ?>

                                <span class="tw-font-semibold tw-text-xl">
                                    <?php 
                                    $change_status = false;
                                    ?>
                                    <?php echo _l('wshop_job_tracking_number').': '; ?>
                                    <?php echo html_entity_decode($repair_job->job_tracking_number); ?>
                                    <?php echo render_repair_job_status_html($repair_job->id, '', $repair_job->status, $change_status) ?>
                                </span>

                            </div>
                            <div class="col-md-6">
                                <?php if(has_permission('workshop_repair_job', '', 'create') || has_permission('workshop_repair_job', '', 'edit')){ ?>
                                    <div class=" pull-right">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <?php echo _l('more'); ?> <span class="caret"></span> </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="#" onclick="print_label_preview(); return false;"><?php echo _l('wshop_print_label'); ?></a>
                                            </li>
                                            <li><a href="<?php echo admin_url('workshop/add_edit_repair_job/'.$repair_job->id); ?>" target="_blank"><?php echo _l('wshop_print_report'); ?></a>
                                            </li>
                                        </ul>
                                    </div>

                                <?php } ?>

                                <div class="btn-group pull-right mright5 hide">
                                    <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><?php if (is_mobile()) {
                                        echo ' PDF';
                                    } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li class="hidden-xs"><a href="<?php echo admin_url('block_time/repair_job_pdf/' . $repair_job->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a>
                                        </li>
                                        <li class="hidden-xs"><a href="<?php echo admin_url('block_time/repair_job_pdf/' . $repair_job->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                        <li><a href="<?php echo admin_url('block_time/repair_job_pdf/' . $repair_job->id); ?>"><?php echo _l('download'); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php echo admin_url('block_time/repair_job_pdf/' . $repair_job->id . '?print=true'); ?>"
                                                target="_blank">
                                                <?php echo _l('print'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <a href="<?php echo site_url('workshop/client/repair_jobs'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr class="no-mbot">

                        <div class="row">
                            <div class="horizontal-scrollable-tabs preview-tabs-top">
                                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                <div class="horizontal-tabs">
                                    <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                                        <li role="presentation" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') == 'detail'){echo 'active';} ?>">
                                            <a href="#detail" aria-controls="detail"  class="detail" role="tab" data-toggle="tab">
                                                <span class="fa-brands fa-usps"></span>&nbsp;<?php echo _l('wshop_detail'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') == 'workshop'){echo 'active';} ?>">
                                            <a href="#workshop" aria-controls="workshop"  class="workshop" role="tab" data-toggle="tab">
                                                <span class="fa-solid fa-arrow-down-wide-short"></span>&nbsp;<?php echo _l('wshop_workshops'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') == 'delivery'){echo 'active';} ?>">
                                            <a href="#delivery" aria-controls="delivery" role="tab" data-toggle="tab">
                                                <span class="fa-solid fa-bolt"></span>&nbsp;<?php echo _l('wshop_deliveries'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') == 'return'){echo 'active';} ?>">
                                            <a href="#return" aria-controls="return" role="tab" data-toggle="tab">
                                                <span class="fa fa-history"></span>&nbsp;<?php echo _l('wshop_returns'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="hide">
                                            <a href="#file" aria-controls="file" role="tab" data-toggle="tab">
                                                <span class="fa fa-history"></span>&nbsp;<?php echo _l('wshop_files'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="hide">
                                            <a href="#ticket" aria-controls="ticket" role="tab" data-toggle="tab">
                                                <span class="fa fa-history"></span>&nbsp;<?php echo _l('wshop_tickets'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="hide">
                                            <a href="#contract" aria-controls="contract" role="tab" data-toggle="tab">
                                                <span class="fa fa-history"></span>&nbsp;<?php echo _l('wshop_contracts_and_notes'); ?>
                                            </a>
                                        </li>
                                        
                                    </ul>
                                </div>
                            </div>
                            <br>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'detail'){echo 'active';} ?>"  id="detail">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-7">
                                                 <div class="row">
                                                    <div class="col-md-12">
                                                        <h4 class="tw-font-semibold text-danger"><?php echo _l('client') ?></h4>
                                                        <table class="table border table-striped no-mtop">
                                                            <tbody>
                                                                <tr class="project-overview">
                                                                    <td class="bold" width="30%"><?php echo _l('client'); ?></td>
                                                                    <td><?php echo get_company_name($repair_job->client_id) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_contact_name'); ?></td>
                                                                    <td><?php echo html_entity_decode($repair_job->contact_name) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_contact_email'); ?></td>
                                                                    <td><?php echo html_entity_decode($repair_job->contact_email) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_branch_phone'); ?></td>
                                                                    <td><?php echo html_entity_decode($repair_job->phonenumber) ; ?></td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <h4 class="tw-font-semibold text-danger"><?php echo _l('wshop_device') ?></h4>
                                                        <?php if($device){ ?>
                                                        <table class="table border table-striped no-mtop">
                                                            <tbody>
                                                                <tr class="project-overview">
                                                                    <td class="bold" width="30%"><?php echo _l('wshop_device_name'); ?></td>
                                                                    <td><?php echo html_entity_decode($device->name) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_serial_no'); ?></td>
                                                                    <td><?php echo html_entity_decode($device->serial_no) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_model'); ?></td>
                                                                    <td><?php echo wshop_get_model_name($device->model_id) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_purchase_date'); ?></td>
                                                                    <td><?php echo _d($device->purchase_date ?? '--') ; ?></td>
                                                                </tr>
                                                                
                                                            </tbody>
                                                        </table>
                                                    <?php }else{ ?>
                                                        <h5 class="tw-font-semibold text-danger"><?php echo _l('wshop_device_is_not_exist') ?></h5>

                                                    <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                <h4 class="tw-font-semibold text-danger"><?php echo _l('wshop_appointment') ?></h4>

                                                        <table class="table border table-striped no-mtop">
                                                            <tbody>
                                                                <tr class="project-overview">
                                                                    <td class="bold" width="30%"><?php echo _l('wshop_requested_date'); ?></td>
                                                                    <td><?php echo _dt($repair_job->datecreated) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_appointment_date'); ?></td>
                                                                    <td><?php echo _dt($repair_job->appointment_date) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_estimated_completion_date'); ?></td>
                                                                    <td><?php echo _dt($repair_job->estimated_completion_date) ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_appointment_type'); ?></td>
                                                                    <td><?php echo wshop_get_appointment_type_name($repair_job->appointment_type_id) ; ?></td>
                                                                </tr>

                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_mechanic'); ?></td>
                                                                    <td><?php echo new_html_entity_decode($repair_job->sale_agent) != 0 ? get_staff_full_name($repair_job->sale_agent) : '--' ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_repair_location'); ?></td>
                                                                    <td><?php echo wshop_get_branch_name($repair_job->branch_id); ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_address'); ?></td>
                                                                    <td><?php echo wshop_get_branch_name($repair_job->branch_id, 'address') ; ?></td>
                                                                </tr>
                                                                <tr class="project-overview">
                                                                    <td class="bold"><?php echo _l('wshop_issue_description'); ?></td>
                                                                    <td><?php echo html_entity_decode($repair_job->issue_description ?? '--') ; ?></td>
                                                                </tr>
                                                              
                                                            </tbody>
                                                        </table>
                                                      
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="_buttons col-md-12">
                                                <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_labour_products'); ?></span>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table items items-preview estimate-items-preview no-mtop" data-type="estimate">
                                                <thead>
                                                    <tr>
                                                        <th align="center" width="2%" >#</th>
                                                        <th colspan="1" width="17%" class="product" align="left"><?php echo _l('wshop_product'); ?></th>
                                                        <th width="24%" class="description" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('wshop_description'); ?></th>

                                                        <th width="8%" class="unit_price" align="right"><?php echo _l('wshop_unit_price'); ?></th>
                                                        <th width="10%" class="estimated_hours" align="right"><?php echo _l('wshop_estimated_hours'); ?></th>
                                                        <th width="8%" class="estimated_hours" align="right"><?php echo _l('wshop_quantity'); ?></th>
                                                        <th width="8%" class="vat" align="right"><?php echo _l('wshop_vat'); ?></th> 
                                                        <th width="9%" class="discount " align="right"><?php echo _l('wshop_discount_percent'); ?></th> 
                                                        <th width="11%" class="sub_total" align="right"><?php echo _l('wshop_sub_total'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="ui-sortable">
                                                    <?php if(isset($repair_job->repair_job_labour_products)){ ?>
                                                        <?php foreach ($repair_job->repair_job_labour_products as $key => $labour_product) { ?>
                                                            <tr>
                                                                <td ><?php echo new_html_entity_decode($key+1) ?></td>
                                                                <td ><?php echo new_html_entity_decode($labour_product['name']) ?></td>
                                                                <td ><?php echo new_html_entity_decode($labour_product['description']) ?></td>
                                                                <td align="right" ><?php echo app_format_money($labour_product['unit_price'], $repair_job->currency) ?></td>
                                                                <td align="right" ><?php echo new_html_entity_decode($labour_product['estimated_hours']) ?></td>
                                                                <td align="right" ><?php echo new_html_entity_decode($labour_product['qty']) ?></td>
                                                                <?php echo  wshop_render_taxes_html(wshop_convert_item_taxes($labour_product['tax_id'], $labour_product['tax_rate'], $labour_product['tax_name']), 8); ?>
                                                                <td align="right" ><?php echo new_html_entity_decode($labour_product['discount']) ?></td>
                                                                <td align="right" ><?php echo app_format_money($labour_product['subtotal'], $repair_job->currency) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </tbody>
                                            </table>

                                            <div class="col-md-4 col-md-offset-8">
                                                <table class="table text-right">
                                                    <tbody>
                                                        <tr id="subtotal">
                                                            <td class="bold" width="70%"><?php echo _l('wshop_estimated_labour_sub_total'); ?></td>
                                                            <td><?php echo app_format_money((float)$repair_job->estimated_labour_subtotal, $repair_job->currency); ?></td>
                                                        </tr>
                                                        <?php if(isset($repair_job) && $tax_labour_data['html_currency'] != ''){
                                                            echo html_entity_decode($tax_labour_data['html_currency']);
                                                        } ?>

                                                        <tr id="total_discount">
                                                            <?php
                                                            $estimated_labour_discount_total = isset($repair_job) ?  $repair_job->estimated_labour_discount_total : 0 ;
                                                            ?>
                                                            <td class="bold"><?php echo _l('wshop_discount'); ?></td>
                                                            <td><?php echo app_format_money((float)$estimated_labour_discount_total, $repair_job->currency); ?></td>
                                                        </tr>

                                                        <tr id="totalmoney">
                                                            <?php
                                                            $estimated_labour_total = isset($repair_job) ?  $repair_job->estimated_labour_total : 0 ;
                                                            ?>
                                                            <td class="bold"><?php echo _l('wshop_estimated_labour_total'); ?></td>
                                                            <td><?php echo app_format_money((float)$estimated_labour_total, $repair_job->currency); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="_buttons col-md-12">
                                                <span href="#" class="btn btn-primary nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_parts'); ?></span>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table items items-preview estimate-items-preview no-mtop" data-type="estimate">
                                                <thead>
                                                    <tr>
                                                        <th align="center" width="2%" >#</th>
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
                                                    </tr>
                                                </thead>
                                                <tbody class="ui-sortable">
                                                    <?php if(isset($repair_job->repair_job_labour_materials)){ ?>
                                                        <?php foreach ($repair_job->repair_job_labour_materials as $key => $part) { ?>
                                                            <tr>
                                                                <td ><?php echo new_html_entity_decode($key+1) ?></td>
                                                                <td ><?php echo new_html_entity_decode($part['name']) ?></td>
                                                                <td ><?php echo new_html_entity_decode($part['description']) ?></td>
                                                                <td align="right" ><?php echo app_format_money($part['rate'], $repair_job->currency) ?></td>
                                                                <td align="right" ><?php echo new_html_entity_decode($part['estimated_qty']) ?></td>
                                                                <td align="right" ><?php echo new_html_entity_decode($part['qty']) ?></td>
                                                                <?php echo  wshop_render_taxes_html(wshop_convert_item_taxes($part['tax_id'], $part['tax_rate'], $part['tax_name']), 8); ?>
                                                                <td align="right" ><?php echo new_html_entity_decode($part['discount']) ?></td>
                                                                <td align="right" ><?php echo app_format_money($part['subtotal'], $repair_job->currency) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </tbody>
                                            </table>

                                            <div class="col-md-4 col-md-offset-8">
                                                <table class="table text-right">
                                                    <tbody>
                                                        <tr id="subtotal">
                                                            <td class="bold" width="70%"><?php echo _l('wshop_estimated_part_sub_total'); ?></td>
                                                            <td><?php echo app_format_money((float)$repair_job->estimated_material_subtotal, $repair_job->currency); ?></td>
                                                        </tr>
                                                        <?php if(isset($repair_job) && $tax_part_data['html_currency'] != ''){
                                                            echo html_entity_decode($tax_part_data['html_currency']);
                                                        } ?>

                                                        <tr id="total_discount">
                                                            <?php
                                                            $estimated_material_discount_total = isset($repair_job) ?  $repair_job->estimated_material_discount_total : 0 ;
                                                            ?>
                                                            <td class="bold"><?php echo _l('wshop_discount'); ?></td>
                                                            <td><?php echo app_format_money((float)$estimated_material_discount_total, $repair_job->currency); ?></td>
                                                        </tr>

                                                        <tr id="totalmoney">
                                                            <?php
                                                            $estimated_material_total = isset($repair_job) ?  $repair_job->estimated_material_total : 0 ;
                                                            ?>
                                                            <td class="bold"><?php echo _l('wshop_estimated_part_total'); ?></td>
                                                            <td><?php echo app_format_money((float)$estimated_material_total, $repair_job->currency); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <hr class="no-mbot no-mtop">
                                    </div>

                                    <div class="col-md-4 col-md-offset-8">
                                        <table class="table text-right">
                                            <tbody>
                                                <tr id="subtotal">
                                                    <td class="bold" width="70%"><?php echo _l('wshop_estimated_sub_total'); ?></td>
                                                    <td><?php echo app_format_money((float)$repair_job->subtotal, $repair_job->currency); ?></td>
                                                </tr>
                                                <tr id="total_tax_area">
                                                    <td class="bold" width="70%"><?php echo _l('wshop_total_tax'); ?></td>
                                                    <td><?php echo app_format_money((float)$repair_job->total_tax, $repair_job->currency); ?></td>
                                                </tr>

                                                <tr id="total_discount">
                                                    <?php
                                                    $discount_total = isset($repair_job) ?  $repair_job->discount_total : 0 ;
                                                    ?>
                                                    <td class="bold"><?php echo _l('wshop_discount'); ?></td>
                                                    <td><?php echo app_format_money((float)$discount_total, $repair_job->currency); ?></td>
                                                </tr>

                                                <tr id="totalmoney">
                                                    <?php
                                                    $total = isset($repair_job) ?  $repair_job->total : 0 ;
                                                    ?>
                                                    <td class="bold"><?php echo _l('wshop_estimated_total'); ?></td>
                                                    <td><?php echo app_format_money((float)$total, $repair_job->currency); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="tw-font-semibold"><?php echo _l('wshop_job_description') ?></h4>
                                        <p class=""><?php echo new_html_entity_decode(check_for_links($repair_job->job_description)); ?></p>
                                    </div>
                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="tw-font-semibold"><?php echo _l('wshop_additional_description') ?></h4>
                                        <p class=""><?php echo new_html_entity_decode(check_for_links($repair_job->additional_description)); ?></p>
                                    </div>
                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="tw-font-semibold"><?php echo _l('terms_and_conditions') ?></h4>
                                        <p class=""><?php echo new_html_entity_decode(check_for_links($repair_job->terms)); ?></p>
                                    </div>

                                    <?php if(count($_inspection) > 0 && $_inspection[0]['visible_to_customer'] == 1){ ?>
                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>
                                    <div class="col-md-12">
                                        <h4 class="tw-font-semibold"><?php echo _l('wshop_inspection') ?></h4>
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table items items-preview estimate-items-preview no-mtop" data-type="estimate">
                                            <thead>
                                                <tr>
                                                    <th width="40%" class="unit_price" align="left"><?php echo _l('wshop_template_name'); ?></th>
                                                    <th width="20%" class="hide" align="right"><?php echo _l('wshop_progress'); ?></th>
                                                    <th width="5%" colspan="3" class="estimated_hours" align="right"><?php echo _l('wshop_options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody class="ui-sortable">
                                                <tr>
                                                    <?php if($_inspection[0]['visible_to_customer'] == 1){ ?>
                                                        <td ><a href="<?php echo site_url('workshop/client/inspection_detail/'.$_inspection[0]['id'].'?tab=detail'); ?>"><?php echo format_inspection_number($_inspection[0]['id']) ?></a></td>
                                                    <?php }else{ ?>
                                                        <td ><?php echo format_inspection_number($_inspection[0]['id']) ?></td>
                                                    <?php } ?>

                                                    <td class="hide"><?php echo new_html_entity_decode(2) ?></td>
                                                    <?php 
                                                    $checked = '';
                                                    if ($_inspection[0]['visible_to_customer'] == 1) {
                                                        $checked = 'checked';
                                                    }
                                                    $_inspection_option1 = '<a href="'.site_url('workshop/client/inspection_form/'.$_inspection[0]['id']).'" class="btn btn-info mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_form').'">
                                                    <i class="fa-solid fa-play"></i>
                                                    </a>';
                                                    $_inspection_option2 = '<a href="'.site_url('workshop/client/inspection_form_detail/'.$_inspection[0]['id']).'" class="btn btn-success mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_form_detail').'">
                                                    <i class="fa-solid fa-eye"></i>
                                                    </a>';
                                                    $_inspection_option1 = '';
                                                    ?>
                                                    <td ><?php echo new_html_entity_decode($_inspection_option1) ?></td>
                                                    <td ><?php echo new_html_entity_decode($_inspection_option2) ?></td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                    
                                </div>
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'workshop'){echo 'active';} ?>" id="workshop">
                                    <?php if(has_permission('workshop_workshop', '', 'create')){ ?>
                                        <div class="col-md-12">
                                            <a href="#" onclick="workshop_modal(0, <?php echo html_entity_decode($repair_job->id); ?>); return false;" class="btn btn-info pull-right display-block">
                                                <?php echo _l('wshop_new'); ?>
                                            </a>
                                        </div>
                                    <?php } ?>

                                    <div class="col-md-12">
                                        <?php $this->load->view('repair_jobs/workshops/workshop_template'); ?>

                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'delivery'){echo 'active';} ?>" id="delivery">
                                    <div class="col-md-12">
                                        <?php $this->load->view('repair_jobs/delivery_returns/delivery_template'); ?>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'return'){echo 'active';} ?>" id="return">
                                    <div class="col-md-12">
                                        <?php $this->load->view('repair_jobs/delivery_returns/return_template'); ?>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="file">
                                    <div class="col-md-12">
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="ticket">
                                    <div class="col-md-12">
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="contract">
                                    <div class="col-md-12">
                                    </div>
                                </div>
                                <div id="pdf_file_data"></div>
                                	
                            </div>
                        </div>
                        

                    </div>
                </div>
            </div>
        </div>

</div>
<div class="clearfix"></div>


<?php workshop_client_init_tail(); ?>
<?php 
require('modules/workshop/assets/js/clients/repair_jobs/repair_job_detail_js.php');
	
 ?>