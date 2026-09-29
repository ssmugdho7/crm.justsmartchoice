<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="cancel_repair_job" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('wshop_cancel_repair_job'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="tw-text-center text-danger">
                            <?php echo _l('wshop_confirm_cancel_Repair_job'); ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="button" onclick="repair_job_status_mark_as('Cancelled',<?php echo html_entity_decode($repair_job->id) ?>,''); return false;" class="btn btn-danger"><?php echo _l('wshop_confirm'); ?></button>
        </div>
        </div>
        <div id="box-loading"></div>
    </div>

</div>

