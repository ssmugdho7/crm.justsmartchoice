<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="mtop15 preview-top-wrapper">
	<div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <?php if(is_numeric($inspection->invoice_id)){ ?>
                                    <span class="tw-font-semibold tw-text-sm">
                                        <a href="<?php echo site_url('invoice/'.$inspection->invoice_id.'/'.workshop_get_invoice_hash($inspection->invoice_id)); ?>" target="_blank"><?php echo format_invoice_number($inspection->invoice_id); ?></a>
                                    </span><br>
                                <?php } ?>
                                <span class="tw-font-semibold tw-text-xl"><?php echo html_entity_decode( format_inspection_number($inspection->id).' - '.$inspection->inspection_template_name); ?></span>
                            </div>
                            <div class="col-md-4">
                                <a href="<?php echo site_url('workshop/client/inspections'); ?>" class="btn btn-default pull-right display-block mright5">
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
                                        
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane <?php if(!$this->input->get('tab') || $this->input->get('tab') == 'detail'){echo 'active';} ?>" id="detail">
                                    <div class="col-md-12">
                                        <?php $this->load->view('inspections/_template'); ?>
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
<div class="clearfix"></div>


<?php workshop_client_init_tail(); ?>
<?php 
require('modules/workshop/assets/js/clients/repair_jobs/repair_job_detail_js.php');
	
 ?>