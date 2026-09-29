<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php  init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <?php if(is_numeric($inspection->invoice_id)){ ?>
                                    <span class="tw-font-semibold tw-text-sm">
                                        <a href="<?php echo admin_url('invoices/list_invoices/'.$inspection->invoice_id); ?>" target="_blank"><?php echo format_invoice_number($inspection->invoice_id); ?></a>
                                    </span><br>
                                <?php } ?>
                                <span class="tw-font-semibold tw-text-xl"><?php echo html_entity_decode( format_inspection_number($inspection->id).' - '.$inspection->inspection_template_name); ?></span>
                                <?php if($inspection->status != 'Open' && $inspection->status != 'In_Progress' && $inspection->status != 'Waiting_For_Approval' && is_null($inspection->invoice_id)){ ?>
                                <?php if($check_parts_available['status'] && $allow_create_invoice){ ?>
                                    <a href="<?php echo admin_url('workshop/convert_to_invoice/'.$inspection->id.'/inspection'); ?>" class="btn btn-success mright5" data-toggle="tooltip" data-placement="bottom" data-original-title="<?php echo _l('wshop_part_create_invoice_tooltip'); ?>">
                                        <?php echo _l('wshop_create_invoice'); ?>
                                    </a>
                                <?php }elseif(!$check_parts_available['status'] && $inspection->invoice_id == 0){ ?>
                                    <?php if( 1== 2 && wshop_get_status_modules('warehouse') && wshop_get_status_modules('purchase')){ ?>
                                        <a href="<?php echo admin_url('workshop/repair_jobs'); ?>" class="btn btn-warning mright5" data-toggle="tooltip" data-placement="bottom" data-original-title="<?php echo _l('wshop_part_quantity_not_available_tooltip'); ?>">
                                            <?php echo _l('wshop_create_purchase_request'); ?>
                                        </a>
                                    <?php }elseif(wshop_get_status_modules('warehouse')){ ?>
                                        <?php if($allow_create_invoice){ ?>
                                            <a href="javascript:void(0)" class="btn btn-success mright5" disabled data-toggle="tooltip" data-placement="bottom" data-original-title="<?php echo _l('wshop_part_quantity_not_available_tooltip_wh'); ?>">
                                                <?php echo _l('wshop_create_invoice'); ?>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php } ?>

                            </div>
                            <div class="col-md-4">
                                <?php if (has_permission('workshop_inspection', '', 'edit') && $inspection->status == 'Open') { ?>
                                   
                                <?php } ?>
                                <?php
                                $_tooltip              = _l('estimate_sent_to_email_tooltip');
                                $_tooltip_already_send = '';
                                if ($inspection->sent == 1) {
                                    $_tooltip_already_send = _l('estimate_already_send_to_client_tooltip', time_ago($inspection->datesend));
                                }
                                ?>
                                <?php if (!empty($inspection->client_id)) { ?>
                                    <a href="#" onclick="inspection_send_mail_client(); return false;" class="contract-estimate-send-to-client btn btn-default btn-with-tooltip pull-right mright5"
                                    data-toggle="tooltip" title="<?php echo new_html_entity_decode($_tooltip); ?>" data-placement="bottom"><span
                                    data-toggle="tooltip" data-title="<?php echo new_html_entity_decode($_tooltip_already_send); ?>"><i
                                    class="fa-regular fa-envelope"></i></span></a>
                                <?php } ?>
                                <a href="<?php echo admin_url('workshop/inspections'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>

                            </div>
                        </div>

                        <div class="clearfix"></div>

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
                                        <li role="presentation" onclick="init_rel_tasks_table(<?php echo new_html_entity_decode($inspection->id); ?>,'wshop_inspection'); return false;" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') == 'task'){echo 'active';} ?>">
                                            <a href="#task" aria-controls="task"  class="task" role="tab" data-toggle="tab">
                                                <span class="fa-solid fa-arrow-down-wide-short"></span>&nbsp;<?php echo _l('wshop_tasks'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'detail'){echo 'active';} ?>" id="detail">
                                    <div class="col-md-12">
                                        <?php $this->load->view('inspections/_template'); ?>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'task'){echo 'active';} ?>" id="task">
                                    <div class="col-md-12">
                                     <?php init_relation_tasks_table(array('data-new-rel-id'=>$inspection->id,'data-new-rel-type'=>'wshop_inspection')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="inspection_detail" value="1">
<div id="modal_wrapper"></div>
<?php $this->load->view('inspections/send_mail_to_client'); ?>


<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/inspections/manage_js.php');
?>
</body>
</html>
