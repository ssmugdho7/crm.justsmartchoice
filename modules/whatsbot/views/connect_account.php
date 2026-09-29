<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $health_status = json_decode(get_option('wb_health_data')); ?>
<div id="wrapper">
    <div class="content">
        <div class="row mbot15">
            <div class="col-md-7">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo _l('connect_whatsapp_business_account'); ?>
                </h4>
            </div>
            <?php if ($is_connected && isset($default_number)) { ?>
                <div class="col-md-5 text-right">
                    <button class="btn btn-info btn-block" data-toggle="modal" data-target="#qrCodeModal">
                        <span class="tw-white"><?php echo _l('click_to_get_qr_code'); ?></span>
                    </button>
                </div>
            <?php } ?>
        </div>
        <div class="row">
            <div class="col-md-7">
                <div class="panel tw-p-6 tw-bg-black/5">
                    <!-- step 1 -->
                    <?php echo form_open(admin_url('whatsbot/connect_account'), ['id' => 'connect_form'], []); ?>
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('facebook_developer_account_facebook_app'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?= render_input('wb_fb_app_id', 'fb_app_id', get_option('wb_fb_app_id')); ?>
                                    <?= render_input('wb_fb_app_secret', 'fb_app_secret', get_option('wb_fb_app_secret')); ?>
                                </div>
                            </div>
                            <div class="mtop15 hide after_connect">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="tw-flex tw-items-center">
                                            <?php if (get_option('wb_webhook_configure') == 1) { ?>
                                                <i class="fa fa-xl fa-check-circle text-success tw-mr-1 "></i>
                                                <span class="font-size-14"><?= _l('webhook_configured'); ?></span>
                                            <?php } else { ?>
                                                <i class="fa fa-xl fa-xmark-circle text-danger tw-mr-1 "></i>
                                                <span class="font-size-14"><?= _l('webhook_configured'); ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <?php if (get_option('wb_webhook_configure') == 1) { ?>
                                <button type="submit" name="submit" value="submit" class="btn btn-success"><i class="fa-regular fa-pen-to-square tw-mr-1"></i><?php echo _l('update_details'); ?></button>
                                <a href="<?= admin_url('whatsbot/disconnect_webhook'); ?>" class="btn btn-danger"><i class="fa-solid fa-link-slash tw-mr-1"></i><?= _l('disconnect_webhook'); ?></a>
                            <?php } else { ?>
                                <button type="submit" name="submit" value="submit" class="btn btn-success"><?php echo _l('save'); ?></button>
                                <a href="<?= admin_url('whatsbot/connect_webhook'); ?>" class="btn btn-success"><i class="fa-solid fa-link tw-mr-1"></i><?= _l('connect_webhook'); ?></a>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- step 2 -->
                    <div class="panel">
                        <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                            <h4 class="no-margin text-primary"><?= _l('whatsApp_integration_setup'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('business_account_id_description'); ?>" data-placement="left"></i>
                                            <?php echo render_input('wac_business_account_id', _l('whatsapp_business_account_id'), get_option('wac_business_account_id')); ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip" data-title="<?php echo _l('access_token_description'); ?>"></i>
                                                <label for="wac_access_token" class="control-label"><?php echo _l('whatsapp_access_token'); ?></label>
                                                <div class="input-group">
                                                    <input id="wac_access_token" name="wac_access_token" class="form-control" value="<?= get_option('wac_access_token'); ?>" oninput="updateLink()">
                                                    <span class="input-group-addon tw-cursor-pointer btn btn-primary" target="_blank" id="debugTokenButton" onclick="openDebugLink()">
                                                        <?= _l('debug_token'); ?> <i class="fas fa-external-link-alt"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <?php if (!$is_connected) { ?>
                                <button type="submit" name="submit" value="submit" class="btn btn-success" id="submitForm"><i class="fa-solid fa-link tw-mr-1"></i><?php echo _l('connect'); ?></button>
                            <?php } else { ?>
                                <button type="submit" name="submit" value="update" class="btn btn-success"><i class="fa-regular fa-pen-to-square tw-mr-1"></i><?php echo _l('update_details'); ?></button>
                                <button type="submit" class="btn btn-danger" formaction="<?php echo admin_url('whatsbot/disconnect'); ?>"><i class="fa-solid fa-link-slash tw-mr-1"></i><?php echo _l('disconnect'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                    <!-- step 3 -->
                    <?php if ($is_connected && isset($tocken_info)) { ?>
                        <div class="panel hide after_connect">
                            <div class="panel-heading tw-bg-white tw-flex tw-items-center tw-gap-1">
                                <h4 class="no-margin text-primary"><?= _l('access_token_information'); ?></h4>
                            </div>
                            <div class="panel-body tw-flex tw-gap-3">
                                <div class="tw-flex tw-flex-col tw-gap-3">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?= _l('permission_scopes'); ?></label>
                                        <span class="tw-text-black/50"></i><?= implode(', ', $tocken_info->scopes); ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?= _l('issued_at'); ?></label>
                                        <span class="tw-text-black/50"><?= $tocken_info->issued_at; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?= _l('expiry_at'); ?></label>
                                        <span class="tw-text-black/50"><?= empty($tocken_info->expires_at) ? 'N/A' : $tocken_info->expires_at; ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty(get_option('wac_access_token'))) { ?>
                                <div class="panel-footer tw-bg-white">
                                    <a href="<?= "https://developers.facebook.com/tools/debug/accesstoken/?access_token=" . get_option('wac_access_token'); ?>" class="btn btn-secondary btn-sm" target="_blank"><?= _l('debug_token'); ?> <i class="fas fa-external-link-alt"></i></a>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

            </div>
            <div class="col-md-5 hide after_connect">
                <div class="panel tw-p-6 tw-bg-black/5">
                    <?php foreach ($phone_numbers as $phone) {
                        $isDefault = ($phone->id == get_option('wac_phone_number_id')); ?>
                        <div class="panel">
                            <div class="panel-heading tw-bg-white">
                                <h4 class="no-margin text-primary"><?php echo _l('phone_numbers'); ?></h4>
                            </div>
                            <div class="panel-body tw-flex tw-gap-3">
                                <div class="tw-flex tw-flex-col tw-gap-3">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?php echo _l('display_phone_number'); ?></label>
                                        <span class="tw-text-black/50"></i><?php echo $phone->display_phone_number; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?php echo _l('verified_name'); ?></label>
                                        <span class="tw-text-black/50"><?php echo $phone->verified_name; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?php echo _l('number_id'); ?></label>
                                        <span class="tw-text-black/50"><?php echo $phone->id; ?></span>
                                    </div>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-3">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?php echo _l('quality'); ?></label>
                                        <span id="qualityRating"><?php echo $phone->quality_rating; ?></span>
                                    </div>
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?php echo _l('status'); ?></label>
                                        <span class="tw-text-black/50"><?php echo $phone->code_verification_status; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer tw-bg-white">
                                <?php if ($isDefault) { ?>
                                    <a href="<?= 'https://business.facebook.com/wa/manage/phone-numbers/?waba_id=' . get_option('wac_business_account_id'); ?>" class="btn btn-primary" target="_blank"><?php echo _l('manage_phone_numbers'); ?><i class="fas fa-external-link-alt tw-ml-1"></i></a>
                                <?php } else { ?>
                                    <a href="#" class="btn btn-info mark_as_default" data-phone_number_id="<?php echo $phone->id; ?>" data-default-phone-number="<?php echo $phone->display_phone_number; ?>">
                                        <i class="fa-solid fa-check tw-mr-1"></i>
                                        <?php echo _l('mark_as_default'); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-heading tw-bg-white">
                                <h4 class="no-margin text-primary"><?= _l('overall_health'); ?></h4>
                            </div>
                            <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label"><?= _l('whatsApp_business_id'); ?></label>
                                    <span class="tw-text-black/50"><?= get_option('wac_business_account_id'); ?></span>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label"><?= _l('status_as_at'); ?></label>
                                    <span class="tw-text-black/50"><?= get_option('wb_health_check_time'); ?></span>
                                </div>
                                <div class="tw-flex tw-flex-col">
                                    <label class="control-label"><?= _l('overall_health_send_message'); ?></label>
                                    <span class="tw-text-black/50"><?= $health_status->health_status->can_send_message; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        foreach ($health_status->health_status->entities as $entity) { ?>
                            <div class="panel">
                                <div class="panel-heading tw-bg-white">
                                    <h4 class="no-margin text-primary"><?= htmlspecialchars($entity->entity_type) . ' - ' . htmlspecialchars($entity->id); ?></h4>
                                </div>
                                <div class="panel-body tw-flex tw-flex-col tw-gap-3">
                                    <div class="tw-flex tw-flex-col">
                                        <label class="control-label"><?= _l('can_send_message'); ?></label>
                                        <span class="tw-text-black/50"><?= htmlspecialchars($entity->can_send_message); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <a href="<?= admin_url('whatsbot/get_health_status'); ?>" class="btn btn-success"><?= _l('refresh_status'); ?></a>
                    <?php
                    } ?>
                </div>
            </div>
        </div>

        <!-- Qr code modal Start -->
        <?php if ($is_connected && isset($default_number)) { ?>
            <div class="modal fade" id="qrCodeModal" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo _l('scan_qr_code_to_start_chat'); ?></h4>
                        </div>
                        <div class="modal-body tw-bg-black/5">
                            <div class="tw-bg-black/80 tw-flex tw-items-center tw-justify-center tw-p-4 tw-rounded">
                                <span class="tw-text-white"><?php echo _l('use_qr_code_to_invite'); ?></span>
                            </div>
                            <div class="panel tw-mt-6">
                                <div class="panel-heading tw-bg-white tw-flex tw-justify-center">
                                    <h4 class="no-margin text-primary"><?= $default_number->verified_name . ' (' .  $default_number->display_phone_number . ')'; ?></h4>
                                </div>
                                <div class="panel-body tw-flex tw-flex-col tw-gap-2 tw-justify-center tw-items-center">
                                    <img src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/qrcode.png'); ?>" alt="qr code" style="height:160px">
                                    <span class="tw-text-base tw-text-black/50"><?= _l('phone_number'); ?></span>
                                    <span class="text-success tw-text-base"><?= $default_number->display_phone_number; ?></span>
                                    <div class="col-md-12">
                                        <h5><?php echo _l('url_for_qr_image'); ?></h5>
                                        <a class="copyText" href="<?= module_dir_url('whatsbot', 'assets/images/qrcode.png'); ?>"><?php echo module_dir_url('whatsbot', 'assets/images/qrcode.png'); ?></a>
                                        <span class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn copyBtn"><?php echo _l('copy'); ?></span>
                                    </div>
                                    <div class="col-md-12">
                                        <h5><?php echo _l('whatsapp_url'); ?></h5>
                                        <a class="copyText" href="<?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?>"><?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?></a>
                                        <span class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn copyBtn"><?php echo _l('copy'); ?></span>
                                        <a href="<?= 'https://api.whatsapp.com/send?phone=' . get_option('wac_default_phone_number'); ?>" class="badge rounded-circle tw-mt-0.5 tw-mr-1 pull-right btn"><?php echo _l('whatsapp_now'); ?></a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- Qr code modal End -->
    </div>

</div>
</div>

<?php init_tail(); ?>
<script>
    "use strict";
    $(function() {
        $('.copyBtn').on('click', function() {
            var textToCopy = $(this).prev('.copyText').text();
            var tempInput = $('<textarea>');
            tempInput.val(textToCopy);
            $('body').append(tempInput);
            tempInput.select();
            tempInput[0].setSelectionRange(0, 99999);
            document.execCommand('copy');
            tempInput.remove();
            $(this).text('<?php echo _l('copied'); ?>');
            setTimeout(() => {
                $(this).text('<?php echo _l('copy'); ?>');
            }, 1000);
        });

        $('.mark_as_default').on('click', function() {
            $.ajax({
                url: `${admin_url}whatsbot/set_default_number_phone_number_id`,
                data: {
                    wac_phone_number_id: $(this).data('phone_number_id'),
                    wac_default_phone_number: $(this).data('default-phone-number')
                },
                dataType: 'json',
                type: 'POST'
            }).done(function(res) {
                location.reload();
            });
        });

        <?php if (get_option('wb_account_connected') == 1) { ?>
            $('.after_connect').removeClass('hide');
        <?php } ?>

    });

    function updateLink() {
        var inputValue = document.getElementById('wac_access_token').value;
        var debugLink = "https://developers.facebook.com/tools/debug/accesstoken/?access_token=" + encodeURIComponent(inputValue);
        document.getElementById('debugTokenButton').setAttribute('data-url', debugLink);
    }

    function openDebugLink() {
        var debugLink = document.getElementById('debugTokenButton').getAttribute('data-url');
        window.open(debugLink, '_blank');
    }

    // Initialize the button with the current input value when the page loads
    updateLink();
    $(document).ready(function() {
        const qualityRatingElement = $("#qualityRating");
        const qualityRatingValue = qualityRatingElement.text().trim();
        qualityRatingElement.css("color", qualityRatingValue);
    });
</script>
