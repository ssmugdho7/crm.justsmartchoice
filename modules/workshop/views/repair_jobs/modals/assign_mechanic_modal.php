<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="assign_mechanic" tabindex="-1" role="dialog">
    <?php echo form_open_multipart(admin_url('workshop/reassign_mechanic/'.$repair_job->id), array('id' => 'reassign_mechanic', 'autocomplete'=>'off')); ?>
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('wshop_assign_mechanic'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_select('assign_mechanic', $staffs, ['staffid', ['firstname', 'lastname']], 'wshop_mechanic', ''); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-danger"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <div id="box-loading"></div>
    </div>
    <?php echo form_close(); ?>

</div>

