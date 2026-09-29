<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="tw-max-w-4xl tw-mx-auto">
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                <?= e($title); ?>
            </h4>
            <?= form_open_multipart($this->uri->uri_string(), ['id' => 'staff_profile_table', 'autocomplete' => 'off']); ?>

            <div class="panel_s">
                <div class="panel-body">
                    <?php if ($current_user->profile_image == null) { ?>
                    <div class="form-group">
                        <label for="profile_image"
                            class="profile-image"><?= _l('staff_edit_profile_image'); ?></label>
                        <input type="file" name="profile_image" class="form-control" id="profile_image">
                    </div>
                    <?php } ?>
                    <?php if ($current_user->profile_image != null) { ?>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-9">
                                <?= staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-thumb'], 'thumb'); ?>
                            </div>
                            <div class="col-md-3 text-right">
                                <a
                                    href="<?= admin_url('staff/remove_staff_profile_image'); ?>"><i
                                        class="fa fa-remove"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="firstname"
                            class="control-label"><?= _l('staff_add_edit_firstname'); ?></label>
                        <input type="text" class="form-control" name="firstname"
                            value="<?= isset($member) ? e($member->firstname) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastname"
                            class="control-label"><?= _l('staff_add_edit_lastname'); ?></label>
                        <input type="text" class="form-control" name="lastname"
                            value="<?= isset($member) ? e($member->lastname) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"
                            class="control-label"><?= _l('staff_add_edit_email'); ?></label>
                        <input type="email" name="email" class="form-control"
                        value="<?= e($member->email); ?>"
                        id="email">
                    </div>
                    <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                    <?= render_input('phonenumber', 'staff_add_edit_phonenumber', $value); ?>
                    <?php if (! is_language_disabled()) { ?>
                    <div class="form-group select-placeholder">
                        <label for="default_language"
                            class="control-label"><?= _l('localization_default_language'); ?></label>
                        <select name="default_language" data-live-search="true" id="default_language"
                            class="form-control selectpicker"
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            
                            <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                                $selected = '';
                                if (isset($member)) {
                                    if ($member->default_language == $availableLanguage) {
                                        $selected = 'selected';
                                    }
                                } ?>
                            <option
                                value="<?= e($availableLanguage); ?>"
                                <?= e($selected); ?>>
                                <?= e(ucfirst($availableLanguage)); ?>
                            </option>
                            <?php
                            } ?>
                        </select>
                    </div>
                    <?php } ?>
                    <div class="form-group select-placeholder">
                        <label
                            for="direction"><?= _l('document_direction'); ?></label>
                        <select class="selectpicker"
                            data-none-selected-text="<?= _l('system_default_string'); ?>"
                            data-width="100%" name="direction" id="direction">
                            <option value="" <?= isset($member) && empty($member->direction) ? ' selected' : '' ?>>
                            </option>
                            <option value="ltr" <?= isset($member) && $member->direction == 'ltr' ? ' selected' : '' ?>>
                                LTR
                            </option>
                            <option value="rtl" <?= isset($member) && $member->direction == 'rtl' ? ' selected' : '' ?>>
                                RTL
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="facebook" class="control-label"><i class="fa-brands fa-facebook-f"></i>
                            <?= _l('staff_add_edit_facebook'); ?></label>
                        <input type="text" class="form-control" name="facebook"
                            value="<?= isset($member) ? e($member->facebook) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="linkedin" class="control-label"><i class="fa-brands fa-linkedin-in"></i>
                            <?= _l('staff_add_edit_linkedin'); ?></label>
                        <input type="text" class="form-control" name="linkedin"
                            value="<?= isset($member) ? e($member->linkedin) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="skype" class="control-label"><i class="fa-brands fa-whatsapp"></i>
                            WhatsApp</label>
                        <input type="text" class="form-control" name="skype"
                            value="<?= isset($member) ? e($member->skype) : ''; ?>">
                    </div>
                        <div class="form-group">
                            <label for="telegram" class="control-label"><i class="fa-brands fa-telegram"></i> Telegram</label>
                            <input type="text" class="form-control" name="telegram" value="<?= isset($member) && isset($member->telegram) ? e($member->telegram) : ''; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-4"><?= render_input('server_login', 'Server Login', isset($member) && isset($member->server_login) ? $member->server_login : ''); ?></div>
                            <div class="col-md-4"><?= render_input('server_ip', 'Server IP', isset($member) && isset($member->server_ip) ? $member->server_ip : ''); ?></div>
                            <div class="col-md-4"><?= render_input('server_password', 'Server Password', isset($member) && isset($member->server_password) ? $member->server_password : '', 'text'); ?></div>
                        </div>

                    <hr>
                    <h4 class="tw-font-bold" id="sc_staff_self_employment"><i class="fa-solid fa-money-check-dollar"></i> <?= _l('sc_staff_banking'); ?></h4>
                    <div class="row">
                        <div class="col-md-4"><?= render_input('hourly_rate','sc_hourly_wages',isset($member)?($member->hourly_rate ?? 0):0,'number',['step'=>'0.01','min'=>'0']); ?></div>
                        <div class="col-md-4"><?= render_input('bank_account_number','sc_bank_account_number',isset($member)?($member->bank_account_number ?? ''):''); ?></div>
                        <div class="col-md-4"><?= render_select('bank_account_type',[['id'=>'checking','name'=>_l('sc_checking')],['id'=>'savings','name'=>_l('sc_savings')]],['id','name'],'sc_bank_account_type',isset($member)?($member->bank_account_type ?? ''):''); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><?= render_input('bank_routing_number','sc_bank_routing_number',isset($member)?($member->bank_routing_number ?? ''):''); ?></div>
                        <div class="col-md-4"><?= render_input('bank_swift_aba','sc_bank_swift_aba',isset($member)?($member->bank_swift_aba ?? ''):''); ?></div>
                        <div class="col-md-4"><?= render_input('bank_name','sc_bank_name',isset($member)?($member->bank_name ?? ''):''); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><?= render_input('bank_website','sc_bank_website',isset($member)?($member->bank_website ?? ''):'','url'); ?></div>
                        <div class="col-md-4"><?= render_input('bank_account_name','sc_bank_account_name',isset($member)?($member->bank_account_name ?? ''):''); ?></div>
                        <div class="col-md-4"><?= render_input('bank_account_address','sc_bank_account_address',isset($member)?($member->bank_account_address ?? ''):''); ?></div>
                    </div>
                    <hr><h4 class="tw-font-bold"><i class="fa-solid fa-id-card"></i> <?= _l('sc_employment_information'); ?></h4>
                    <?php $scSupervisorOptions=[]; foreach(($staff_members ?? []) as $sm){ if($sm['staffid']!=$member->staffid){$scSupervisorOptions[]=['staffid'=>$sm['staffid'],'name'=>trim($sm['firstname'].' '.$sm['lastname'])];}} ?>
                    <div class="row">
                        <div class="col-md-4"><?= render_input('employee_staff_id','sc_employee_staff_id',isset($member)?($member->employee_staff_id ?? ''):''); ?></div>
                        <div class="col-md-4"><?= render_input('start_date','sc_start_date',isset($member)?($member->start_date ?? ''):'','date'); ?></div>
                        <div class="col-md-4"><?= render_select('employment_type',[['id'=>'employee_full_time','name'=>_l('sc_employee_full_time')],['id'=>'employee_part_time','name'=>_l('sc_employee_part_time')],['id'=>'contractor','name'=>_l('sc_contractor')],['id'=>'freelancer','name'=>_l('sc_freelancer')]],['id','name'],'sc_employment_type',isset($member)?($member->employment_type ?? ''):''); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><?= render_select('supervisor_staff_id',$scSupervisorOptions,['staffid','name'],'sc_supervisor_manager',isset($member)?($member->supervisor_staff_id ?? ''):'',['data-live-search'=>true]); ?></div>
                        <?php $scTeamDepartments=[]; foreach(($departments ?? []) as $dep){$scTeamDepartments[]=['id'=>$dep['departmentid'],'name'=>$dep['name']];} ?>
                        <div class="col-md-4"><?= render_select('team',$scTeamDepartments,['id','name'],'sc_team',isset($member)?($member->team ?? ''):'',['data-live-search'=>true]); ?></div>
                        <div class="col-md-4"><?= render_input('probation_end_date','sc_probation_end_date',isset($member)?($member->probation_end_date ?? ''):'','date'); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><?= render_input('contract_expiration_date','sc_contract_expiration_date',isset($member)?($member->contract_expiration_date ?? ''):'','date'); ?></div>
                        <div class="col-md-4"><?= render_input('emergency_contact_name','sc_emergency_contact_name',isset($member)?($member->emergency_contact_name ?? ''):'','text',['required'=>true]); ?></div>
                        <div class="col-md-4"><?= render_input('emergency_contact_relationship','sc_emergency_contact_relationship',isset($member)?($member->emergency_contact_relationship ?? ''):'','text',['required'=>true]); ?></div>
                    </div>
                    <div class="row"><div class="col-md-4"><?= render_input('emergency_contact_phone','sc_emergency_contact_phone',isset($member)?($member->emergency_contact_phone ?? ''):'','tel',['required'=>true]); ?></div></div>
                    <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                        data-title="<?= _l('staff_email_signature_help'); ?>"></i>
                    <?php $value = (isset($member) ? $member->email_signature : ''); ?>
                    <?= render_textarea('email_signature', 'settings_email_signature', $value, ['data-entities-encode' => 'true']); ?>
                    <?php if (count($staff_departments) > 0) { ?>
                    <div class="form-group">
                        <label
                            for="departments"><?= _l('staff_edit_profile_your_departments'); ?></label>
                        <div class="clearfix"></div>
                        <?php foreach ($departments as $department) { ?>
                        <?php foreach ($staff_departments as $staff_department) { ?>
                        <?php if ($staff_department['departmentid'] == $department['departmentid']) { ?>
                        <div class="label label-primary">
                            <?= e($staff_department['name']); ?>
                        </div>
                        <?php } ?>
                        <?php } ?>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
            <?= form_close(); ?>

            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                <?= _l('staff_edit_profile_change_your_password'); ?>
            </h4>

            <?= form_open('admin/staff/change_password_profile', ['id' => 'staff_password_change_form']); ?>

            <div class="panel_s">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="oldpassword"
                            class="control-label"><?= _l('staff_edit_profile_change_old_password'); ?></label>
                        <input type="password" class="form-control" name="oldpassword" id="oldpassword">
                    </div>
                    <div class="form-group">
                        <label for="newpassword"
                            class="control-label"><?= _l('staff_edit_profile_change_new_password'); ?></label>
                        <input type="password" class="form-control" id="newpassword" name="newpassword">
                    </div>
                    <div class="form-group">
                        <label for="newpasswordr"
                            class="control-label"><?= _l('staff_edit_profile_change_repeat_new_password'); ?></label>
                        <input type="password" class="form-control" id="newpasswordr" name="newpasswordr">
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="tw-flex tw-justify-between">
                        <span>
                            <?php if ($member->last_password_change != null) { ?>
                            <?= _l('staff_add_edit_password_last_changed'); ?>:
                            <span class="text-has-action" data-toggle="tooltip"
                                data-title="<?= e(_dt($member->last_password_change)); ?>">
                                <?= e(time_ago($member->last_password_change)); ?>
                            </span>
                            <?php } ?>
                        </span>
                        <button type="submit"
                            class="btn btn-primary"><?= _l('submit'); ?></button>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>


            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700 mtop30">
                <?= _l('staff_two_factor_authentication'); ?>
            </h4>

            <?= form_open('admin/staff/update_two_factor', ['id' => 'two_factor_auth_form']); ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div class="radio radio-primary">
                        <input type="radio" id="two_factor_auth_disabled" name="two_factor_auth" value="off"
                            class="custom-control-input"
                            <?= ($current_user->two_factor_auth_enabled == 0) ? 'checked' : '' ?>>
                        <label class="custom-control-label"
                            for="two_factor_auth_disabled"><?= _l('two_factor_authentication_disabed'); ?></label>
                    </div>
                    <?php if (is_email_template_active('two-factor-authentication')) { ?>
                    <div class="radio radio-primary">
                        <input type="radio" id="two_factor_auth_enabled" name="two_factor_auth" value="email"
                            class="custom-control-input"
                            <?= ($current_user->two_factor_auth_enabled == 1) ? 'checked' : '' ?>>
                        <label for="two_factor_auth_enabled">
                            <i class="fa-regular fa-circle-question" data-placement="right" data-toggle="tooltip"
                                data-title="<?= _l('two_factor_authentication_info'); ?>"></i>
                            <?= _l('enable_two_factor_authentication'); ?>
                        </label>
                    </div>
                    <?php } ?>
                    <div class="radio radio-primary">
                        <input type="radio" id="google_two_factor_auth_enabled" name="two_factor_auth" value="google"
                            class="custom-control-input"
                            <?= ($current_user->two_factor_auth_enabled == 2) ? 'checked' : '' ?>>
                        <label class="custom-control-label"
                            for="google_two_factor_auth_enabled"><?= _l('enable_google_two_factor_authentication'); ?></label>
                    </div>
                    <?php if (! extension_loaded('imagick')) { ?>
                    <div id="imagick_error" class="alert alert-danger mtop15" style="display: none;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Error:</strong> The PHP imagick extension is required for Google Two-Factor
                        Authentication but is not installed on this server. Please contact your system administrator to
                        install the imagick extension.
                    </div>
                    <?php } ?>
                    <div id="qr_image" class="mtop30 card">
                    </div>


                </div>
                <div class="panel-footer text-right">
                    <button id="submit_2fa" type="submit" class="btn btn-primary">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>
        $(function() {
            var qr_loaded = 0;
            var is_g2fa_enabled =
                "<?= $current_user->two_factor_auth_enabled ?>";
            var
                imagick_available = <?= extension_loaded('imagick') ? 'true' : 'false' ?> ;

            $('input[type=radio][name="two_factor_auth"]').change(function() {
                if (this.value == 'google') {
                    if (!imagick_available) {
                        $('#imagick_error').show();
                        $('#submit_2fa').prop("disabled", true);
                        $('#qr_image').hide();
                        return;
                    }

                    $('#imagick_error').hide();

                    if (is_g2fa_enabled == 2) {
                        $('#submit_2fa').prop("disabled", false);
                        return;
                    }

                    if (qr_loaded == 0) {
                        $('#qr_image').load(admin_url + 'authentication/get_qr', {}, function(response,
                            status) {
                            qr_loaded = 1;
                            $('#qr_image').show();
                        });
                    } else {
                        $('#qr_image').show();
                    }
                    $('#submit_2fa').prop("disabled", true);
                } else {
                    $('#imagick_error').hide();
                    $('#qr_image').hide();
                    $('#submit_2fa').prop("disabled", false);
                }
            });

            // Check initial state
            var selectedValue = $('input[name="two_factor_auth"]:checked').val();
            if (selectedValue === 'google' && !imagick_available) {
                $('#imagick_error').show();
            }

            // Prevent form submission if Google 2FA is selected but imagick is not available
            $('#two_factor_auth_form').on('submit', function(e) {
                var selectedValue = $('input[name="two_factor_auth"]:checked').val();
                if (selectedValue === 'google' && !imagick_available) {
                    e.preventDefault();
                    $('#imagick_error').show();
                    return false;
                }
            });

            appValidateForm($('#staff_profile_table'), {
                firstname: 'required',
                lastname: 'required',
                email: 'required'
            });
            appValidateForm($('#staff_password_change_form'), {
                oldpassword: 'required',
                newpassword: 'required',
                newpasswordr: {
                    equalTo: "#newpassword"
                }
            });
            appValidateForm($('#two_factor_auth_form'), {
                two_factor_auth: 'required'
            });
        });
    </script>
    </body>

    </html>