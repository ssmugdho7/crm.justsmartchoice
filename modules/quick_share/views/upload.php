<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <?php echo form_open_multipart(admin_url('quick_share/uploadFile'), ['id' => 'quickshare-form', 'class' => 'dropzone dropzone-manual']); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                            <?php echo $title; ?>
                        </h4>

                        <div class="col-md-6">

                                <div id="dropzoneDragArea" class="dz-default dz-message">
                                    <span><?php echo _l('qs_attach_file_placeholder'); ?></span>
                                </div>
                                <div class="dropzone-previews"></div>
                                <hr class="hr-panel-separator"/>

                                <?php echo render_textarea('file_message', 'contract_description', '', ['rows' => 10], [], '', 'tinymce'); ?>

                        </div>

                        <div class="col-md-6">

                            <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1 email-list hidden" data-toggle="tooltip"
                               data-title="<?php echo _l('qs_protect_email_list_tooltip'); ?>"></i>

                            <?php echo render_input('send_emails_to', 'qs_send_to_emails', '', 'text', [], [], 'email-list hidden'); ?>

                            <?php render_yes_no_option('share_link_type', 'share_link_type_label', 'share_link_type_tooltip', _l('qs_send_using_email'), _l('qs_get_sharable_link')); ?>
                            <?php render_yes_no_option('enable_self_destruct', 'enable_self_destruct_label', 'enable_self_destruct_tooltip'); ?>

                            <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
                               data-title="<?php echo _l('qs_protect_file_with_password_tooltip'); ?>"></i>
                            <label for="password"
                                   class="control-label"><?php echo _l('qs_protect_file_with_password_label'); ?></label>
                            <div class="input-group">
                                <input type="password" class="form-control password" name="password"
                                       autocomplete="off">
                                <span class="input-group-addon tw-border-l-0">
                                        <a href="#password" class="show_password"
                                           onclick="showPassword('password'); return false;"><i
                                                    class="fa fa-eye"></i></a>
                                    </span>
                                <span class="input-group-addon">
                                        <a href="#" class="generate_password"
                                           onclick="generatePassword(this);return false;"><i
                                                    class="fa fa-refresh"></i></a>
                                    </span>
                            </div>

                            <div class="btn-bottom-toolbar text-right">
                                <button type="submit"
                                        class="btn btn-primary"><?php echo _l('qs_start_upload'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>

    Dropzone.options.quickshareForm = false;
    var expenseDropzone;

    $(function () {

        $('input[name="settings[share_link_type]"]').on('change', function () {

            var selectedValue = $(this).val();

            if (selectedValue == 0) {
                $('.email-list').addClass('hidden');
            }

            if (selectedValue == 1) {
                $('.email-list').removeClass('hidden');
            }

        });


        if ($('#dropzoneDragArea').length > 0) {
            expenseDropzone = new Dropzone("#quickshare-form", appCreateDropzoneOptions({
                autoProcessQueue: false,
                clickable: '#dropzoneDragArea',
                previewsContainer: '.dropzone-previews',
                addRemoveLinks: true,
                maxFiles: 1,
                success: function (file, response) {
                    response = JSON.parse(response);
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length ===
                        0) {
                        window.location.assign(response.url);
                    }
                },
            }));
        }

        appValidateForm($('#quickshare-form'), {
            'settings[share_link_type]': 'required',
            'settings[enable_self_destruct]': 'required'
        }, quickShareSubmitHandler);

    });

    function quickShareSubmitHandler(form) {

        $.post(form.action, $(form).serialize()).done(function (response) {
            response = JSON.parse(response);

            if (response.uploadid) {
                if (typeof (expenseDropzone) !== 'undefined') {
                    if (expenseDropzone.getQueuedFiles().length > 0) {
                        expenseDropzone.options.url = admin_url + 'quick_share/queue_uploaded_file/' + response
                            .uploadid;
                        expenseDropzone.processQueue();
                    } else {
                        window.location.assign(response.url);
                    }
                } else {
                    window.location.assign(response.url);
                }
            } else {
                window.location.assign(response.url);
            }

        });
        return false;
    }

</script>
</body>

</html>
