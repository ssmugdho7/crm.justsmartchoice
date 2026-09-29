<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <?php echo form_open(admin_url('quick_share/settings'), ['id' => 'quicksharesettings-form']); ?>
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-6">
                            <?php echo render_input('settings[qs_max_upload_size]', 'qs_settings_maximum_kb_size', get_option('qs_max_upload_size')); ?>
                        </div>

                        <div class="col-md-6">
                            <?php echo render_input('settings[qs_allowed_file_extensions]', 'qs_settings_allowed_file_extensions', get_option('qs_allowed_file_extensions')); ?>
                        </div>

                        <div class="col-md-3">
                            <?php echo render_input('settings[qs_aws_key]', 'qs_settings_aws_key', get_option('qs_aws_key')); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('settings[qs_aws_secret]', 'qs_settings_aws_secret', get_option('qs_aws_secret'),'password'); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('settings[qs_aws_region]', 'qs_settings_aws_region', get_option('qs_aws_region')); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo render_input('settings[qs_aws_bucket]', 'qs_settings_aws_bucket', get_option('qs_aws_bucket')); ?>
                        </div>

                        <div class="col-md-12">
                            <label for="mail_engine"><?php echo _l('qs_settings_selected_storage_engine'); ?></label><br/>
                            <div class="radio radio-inline radio-primary">
                                <input type="radio" name="settings[qs_storage_engine]" id="server"
                                       value="server" <?php if (get_option('qs_storage_engine') == 'server') {
                                    echo 'checked';
                                } ?>>
                                <label for="phpmailer"><?php echo _l('qs_settings_selected_storage_engine_1'); ?></label>
                            </div>

                            <div class="radio radio-inline radio-primary">
                                <input type="radio" name="settings[qs_storage_engine]" id="s3"
                                       value="s3" <?php if (get_option('qs_storage_engine') == 's3') {
                                    echo 'checked';
                                } ?>>
                                <label for="codeigniter"><?php echo _l('qs_settings_selected_storage_engine_2'); ?></label>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
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
</body>

</html>
