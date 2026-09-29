<?php echo form_open('admin/opportunity/add_opportunity_attachment', ['class' => 'dropzone mtop15 mbot15', 'id' => 'opportunity-attachment-upload']); ?>
<input type="hidden" name="id" value="<?= $opportunity_details->id ?>">
<?php echo form_close(); ?>

<?php if (get_option('dropbox_app_key') != '') { ?>
    <hr/>
    <div class=" pull-left">
        <?php if (count($opportunity_details->attachments) > 0) { ?>
            <a href="<?php echo admin_url('opportunity/download_files/' . $opportunity_details->id); ?>" class="bold">
                <?php echo _l('download_all'); ?> (.zip)
            </a>
        <?php } ?>
    </div>
    <div class="tw-flex tw-justify-end tw-items-center tw-space-x-2" id="opportunity-modal">
        <button class="gpicker">
            <i class="fa-brands fa-google" aria-hidden="true"></i>
            <?php echo _l('choose_from_google_drive'); ?>
        </button>
        <div id="dropbox-chooser-opportunity"></div>
    </div>
    <div class=" clearfix"></div>
<?php } ?>
<?php
if (count($opportunity_details->attachments) > 0) { ?>
    <div class="mtop20" id="opportunity_attachments">
        <?php $this->load->view('opportunity/opportunity_details/opportunity_attachments_template', ['attachments' => $opportunity_details->attachments]); ?>
    </div>
<?php } ?>

<script type="text/javascript">
    taskAttachmentDropzone = new Dropzone("#opportunity-attachment-upload", appCreateDropzoneOptions({
        uploadMultiple: true,
        parallelUploads: 20,
        maxFiles: 20,
        paramName: 'file',
        sending: function (file, xhr, formData) {
            formData.append("opportunity_id", '<?php echo $opportunity_details->id; ?>');
        },
        success: function (files, response) {
            window.location.reload();
        }
    }));
</script>
