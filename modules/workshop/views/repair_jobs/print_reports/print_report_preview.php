<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="print_report_preview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('wshop_print_report'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                     <a class="btn btn-lg btn-info display-block" href="<?php echo admin_url('workshop/repair_job_print_report_80_pdf/' . $repair_job->id . '?print=true'); ?>"
                        target="_blank">
                        <?php echo _l('wshop_receipt_report_80'); ?>
                    </a>
                </div>
                <div class="col-md-6">
                 <a class="btn btn-lg btn-info display-block" href="<?php echo admin_url('workshop/repair_job_print_a4_report_pdf/' . $repair_job->id . '?print=true'); ?>"
                    target="_blank">
                    <?php echo _l('wshop_a4_report'); ?>
                </a>
            </div>


        </div>
    </div>
    <div class="modal-footer">
        
    </div>
</div>
<div id="box-loading"></div>
</div>
</div>
