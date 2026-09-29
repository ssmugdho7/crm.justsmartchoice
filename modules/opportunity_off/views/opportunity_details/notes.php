<style>
    .note-editor .note-editable {
        height: 150px;
    }
</style>
<?php
if (!empty($opportunity_details)) {
    $id = $opportunity_details->id;
} else {
    $id = null;
}
?>


<?php echo form_open(base_url('admin/opportunity/save_opportunity_notes/' . $id), array('id' => 'opportunity_notes_form', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form')); ?>

<div class="form-group">
    <div class="col-lg-12">
        <?php $contents = '';
        if (isset($opportunity_details)) {
            $contents = $opportunity_details->notes;
        } ?>
        <?php echo render_textarea('notes', '', $contents, [], [], '', 'tinymce'); ?>

    </div>
</div>
<div class="form-group">
    <div class="col-sm-2">
        <button type="submit" id="sbtn" class="btn btn-primary"><?= _l('updates') ?></button>
    </div>
</div>
<?php echo form_close(); ?>