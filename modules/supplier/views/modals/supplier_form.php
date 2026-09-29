<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="supplier-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart(admin_url('supplier/save'), ['id'=>'supplier-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('supplier_form_title'); ?></h4>
            </div>
            <div class="modal-body"><div id="supplier-modal-content"></div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
