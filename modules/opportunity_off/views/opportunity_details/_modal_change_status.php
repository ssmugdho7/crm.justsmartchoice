<?php
if (!empty($status)) { ?>
    <?php echo form_open(base_url('admin/opportunity/changedStatus/' . $id . '/' . $status), array('id' => 'opportunity_notes_form', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form')); ?>

    <div class="panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h3 class="panel-title"><?php
            if (!empty($opportunity_details->title)) {
                echo $opportunity_details->title;
            }
            ?>
        </h3>
    </div>
    <div class="panel-body">
        <?php
        if ($status == 'won') {
            $btn = _l('won');
            ?>
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <i class="fas fa-info-sign"></i> <?= _l('opportunity_won_message') ?>
            </div>
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="create_invoice" id="create_invoice"
                    <?=
                    (!empty($opportunity_details->create_invoice) && $opportunity_details->create_invoice == 1 ? 'checked' : '')
                    ?>
                >
                <label for="create_invoice">
                    <?= _l('create_invoice_for_opportunity', $opportunity_details->title) ?>
                </label>
            </div>

        <?php } elseif ($status == 'lost') {
            $btn = _l('mark_as_lost');
            ?>
            <div class="p">
                <label class=""><?= _l('lost_reason') ?> <span class="text-danger">*</span></label>
                <div class="">

            <textarea style="height:120px" name="lost_reason" class="form-control tinymce"
                      placeholder="<?= _l('lost_reason') ?>"><?php
                if (!empty($opportunity_details->lost_reason)) {
                    echo $opportunity_details->lost_reason;
                }
                ?></textarea>
                    <!-- <?php $contents = '';
                    if (isset($opportunity_details)) {
                        $contents = $opportunity_details->lost_reason;
                    } ?>
            <?php echo render_textarea('lost_reason', '', $contents, [], [], '', 'tinymce'); ?> -->

                </div>
            </div>


        <?php }
        ?>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
            <button type="submit" class="btn btn-primary"><?= $btn ?></button>
        </div>
    </div>
    <?php echo form_close(); ?>
<?php }
?>