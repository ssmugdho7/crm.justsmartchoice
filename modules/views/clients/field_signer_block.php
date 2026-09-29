<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="field-signer-block" class="panel_s">
    <div class="panel-body">
        <h4><?php echo _l('field_signer_capture'); ?></h4>
        <?php echo form_open_multipart(site_url('field_signer_client/save')); ?>
            <?php echo form_hidden('project_id', $project_id); ?>
            <div class="form-group">
                <label for="title"><?php echo _l('field_signer_title'); ?></label>
                <input type="text" name="title" id="title" class="form-control" />
            </div>
            <div class="form-group">
                <label for="note"><?php echo _l('field_signer_note'); ?></label>
                <textarea name="note" id="note" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label><?php echo _l('field_signer_signature'); ?></label>
                <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                <input type="hidden" name="signature_image" id="signature_image" />
                <div><button type="button" id="clear-signature" class="btn btn-default"><?php echo _l('clear'); ?></button></div>
            </div>

            <div class="form-group">
                <label for="photo"><?php echo _l('field_signer_photo'); ?></label>
                <input type="file" name="photo" id="photo" accept="image/*" class="form-control" />
            </div>

            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
        <?php echo form_close(); ?>
    </div>
</div>
