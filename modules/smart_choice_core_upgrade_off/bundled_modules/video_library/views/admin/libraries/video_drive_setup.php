    <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
    <?php init_head(); ?>
    <div id="wrapper">
        <div class="content">
            <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'upload_video_form')); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"> <?php echo _l('vl_client_heading'); ?></h4>
                            <hr class="hr-panel-heading" />
                            <?php
                            $drive_id = get_option('vl_google_client_id');
                            $drive_secret = get_option('vl_google_client_secret');
                            $drive_url = get_option('vl_google_client_redirect_uri');
                            $drive_check = get_option('is_vl_google_drive');
                            ?>
                            <div class="form-group">
                                <label for="upload_type" class="control-label clearfix">
                                    <?php echo _l('vl_ask_for_upload_gdrive'); ?> </label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" class="upload_type" id="upload-type-file" name="drivecheck" value="yes" <?php if ($drive_check == 'yes') : ?>checked<?php endif; ?>>
                                    <label for="upload-type-file">
                                        <?php echo _l('vl_input_yes'); ?> </label>
                                </div>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" id="upload-type-link" class="upload_type" name="drivecheck" value="no" <?php if ($drive_check == 'no') : ?>checked<?php endif; ?>>
                                    <label for="upload-type-link">
                                        <?php echo _l('vl_input_no'); ?> </label>
                                </div>
                            </div>
                            <?php
                            echo render_input('driveid', _l('vl_client_id'), $drive_id, '', ['placeholder' => _l('vl_client_id_placeholder')]);
                            echo render_input('drivesecret', _l('vl_client_secret'), $drive_secret, '', ['placeholder' => _l('vl_drivesecret_placeholder')]);
                            echo render_textarea('driveurl', _l('vl_client_uri'), $drive_url, ['placeholder' => _l('vl_driveurl_placeholder')],  [], '');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"> <?php echo _l('vl_s3_heading'); ?></h4><p><a target="_blank" href="https://avongboman.medium.com/creating-an-s3-bucket-on-aws-cceca96dec75"> How to configure?</a></p>
                            
                            <hr class="hr-panel-heading" />
                            <?php
                            $s3_check = get_option('is_vl_s3');
                            $vl_s3_bucket = get_option('vl_s3_bucket');
                            $vl_s3_region = get_option('vl_s3_region');
                            $vl_s3_user = get_option('vl_s3_user');
                            $vl_s3_access_key = get_option('vl_s3_access_key');
                            $vl_s3_secret_key = get_option('vl_s3_secret_key');
                            ?>
                            <div class="form-group">
                                <label for="upload_type" class="control-label clearfix">
                                    <?php echo _l('vl_ask_for_upload_s3'); ?> </label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" class="upload_type" id="upload-type-file" name="s3check" value="yes" <?php if ($s3_check == 'yes') : ?>checked<?php endif; ?>>
                                    <label for="upload-type-file">
                                        <?php echo _l('vl_input_yes'); ?> </label>
                                </div>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio" id="upload-type-link" class="upload_type" name="s3check" value="no" <?php if ($s3_check == 'no') : ?>checked<?php endif; ?>>
                                    <label for="upload-type-link">
                                        <?php echo _l('vl_input_no'); ?> </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php
                                    echo render_input('vl_s3_bucket', _l('vl_s3_bucket'), $vl_s3_bucket, '', ['placeholder' => _l('vl_s3_bucket_placeholder')]); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php
                                    echo render_input('vl_s3_region', _l('vl_s3_region'), $vl_s3_region, '', ['placeholder' => _l('vl_s3_region_plceholder')]); ?>
                                </div>
                            </div>
                            <?php
                            echo render_input('vl_s3_user', _l('vl_s3_user'), $vl_s3_user, '', ['placeholder' => _l('vl_s3_user_plceholder')]);
                            echo render_input('vl_s3_access_key', _l('vl_s3_access_key'), $vl_s3_access_key, '', ['placeholder' => _l('vl_s3_access_key_plceholder')]);
                            echo render_input('vl_s3_secret_key', _l('vl_s3_secret_key'), $vl_s3_secret_key, '', ['placeholder' => _l('vl_s3_secret_key_plceholder')]);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('vl_smartchoice_settings'); ?></h4>
                            <hr class="hr-panel-heading" />
                            <div class="row">
                                <div class="col-md-3">
                                    <?php echo render_select('vl_storage_mode', [
                                        ['id' => 'local', 'name' => 'Local Server Upload'],
                                        ['id' => 'google_drive', 'name' => 'Google Drive'],
                                        ['id' => 's3', 'name' => 'Amazon S3'],
                                    ], ['id', 'name'], _l('vl_storage_mode'), get_option('vl_storage_mode') ?: 'local'); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo render_select('vl_default_rel_type', [
                                        ['id' => 'project', 'name' => _l('project')],
                                        ['id' => 'customer', 'name' => _l('customer')],
                                    ], ['id', 'name'], _l('vl_default_rel_type'), get_option('vl_default_rel_type') ?: 'project'); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php echo render_select('vl_show_uploader_card', [
                                        ['id' => 'yes', 'name' => _l('vl_input_yes')],
                                        ['id' => 'no', 'name' => _l('vl_input_no')],
                                    ], ['id', 'name'], _l('vl_show_uploader_card'), get_option('vl_show_uploader_card') ?: 'yes'); ?>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $CI = &get_instance();
                                    $staff_members = $CI->staff_model->get('', ['active' => 1]);
                                    echo render_select('vl_default_upload_owner', $staff_members, ['staffid', ['firstname', 'lastname']], _l('vl_default_upload_owner'), get_option('vl_default_upload_owner') ?: '0', [], [], '', '', false);
                                    ?>
                                </div>
                            </div>
                            <p class="text-muted mtop10">Default recommendation for Smart Choice Contractors USA: keep storage mode set to Local Server Upload unless Google Drive or S3 is fully configured.</p>
                        </div>
                    </div>
                </div>

                <div class="btn-bottom-toolbar text-right ">
                    <button type="submit" class="btn btn-info"><?php echo _l('save'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    </body>

    </html>