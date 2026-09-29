<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CRM Chat Module Settings
 */

// Get all settings values
$chat_enabled = get_option('pusher_chat_enabled');
$chat_client_enabled = get_option('chat_client_enabled');
$chat_staff_can_access_clients_val = get_option('chat_staff_can_access_clients');
$chat_staff_can_access_clients = ($chat_staff_can_access_clients_val !== '' && $chat_staff_can_access_clients_val !== false) ? $chat_staff_can_access_clients_val : '1';
$chat_members_can_create_groups = get_option('chat_members_can_create_groups');
$allow_to_convert_tickets = get_option('chat_allow_staff_to_create_tickets');
$notification_option = get_option('chat_desktop_messages_notifications');
$floating_notifications_val = get_option('chat_floating_notifications_enabled');
$floating_notifications_enabled = ($floating_notifications_val !== '' && $floating_notifications_val !== false) ? $floating_notifications_val : '1';
$toggled_chat_val = get_option('prchat_toggled_chat_disabled');
$toggled_chat_enabled = ($toggled_chat_val !== '' && $toggled_chat_val !== false) ? $toggled_chat_val : '1';
$prchat_max_upload_size_kb = get_option('prchat_max_upload_size_kb') ?: '204800';
$prchat_copy_group_uploads_to_project_media = get_option('prchat_copy_group_uploads_to_project_media') ?: '1';
$smart_sound_enabled = get_option('prchat_smart_message_sound_enabled') ?: '1';
$smart_toast_enabled = get_option('prchat_smart_center_toast_enabled') ?: '1';
$smart_task_enabled = get_option('prchat_smart_task_from_message_enabled') ?: '1';
$smart_project_photo_enabled = get_option('prchat_smart_project_photo_actions_enabled') ?: '1';
$twilio_enabled = get_option('prchat_twilio_enabled') ?: '0';
$twilio_account_sid = get_option('prchat_twilio_account_sid') ?: '';
$twilio_auth_token = get_option('prchat_twilio_auth_token') ?: '';
$twilio_phone_number = get_option('prchat_twilio_phone_number') ?: '';
$twilio_voice_webhook = get_option('prchat_twilio_voice_webhook') ?: '';
$twilio_video_enabled = get_option('prchat_twilio_video_enabled') ?: '0';
$prchat_staff_voice_call_mode = get_option('prchat_staff_voice_call_mode') ?: 'internal';

// Smart Choice Template UI settings
$prchat_template_ui_enabled = get_option('prchat_template_ui_enabled') ?: '1';
$prchat_template_navbar_height = get_option('prchat_template_navbar_height') ?: '58';
$prchat_template_logo_max_height = get_option('prchat_template_logo_max_height') ?: '52';
$prchat_template_hamburger_size = get_option('prchat_template_hamburger_size') ?: '22';
$prchat_template_dropdown_max_height = get_option('prchat_template_dropdown_max_height') ?: '300';
$prchat_template_table_font_size = get_option('prchat_template_table_font_size') ?: '12';
$prchat_template_full_name_width = get_option('prchat_template_full_name_width') ?: '170';
$prchat_template_email_width = get_option('prchat_template_email_width') ?: '145';
$prchat_template_client_login_font_size = get_option('prchat_template_client_login_font_size') ?: '12';
$prchat_template_client_login_padding = get_option('prchat_template_client_login_padding') ?: '7';
$prchat_template_gradient_start = get_option('prchat_template_gradient_start') ?: '#0f766e';
$prchat_template_gradient_end = get_option('prchat_template_gradient_end') ?: '#1d4ed8';
$prchat_template_gradient_text = get_option('prchat_template_gradient_text') ?: '#ffffff';
$prchat_template_hover_text = get_option('prchat_template_hover_text') ?: '#111827';
$prchat_template_button_bg = get_option('prchat_template_button_bg') ?: '#169179';
$prchat_template_button_text = get_option('prchat_template_button_text') ?: '#ffffff';
$prchat_template_saved_profile = get_option('prchat_template_saved_profile') ?: '';
$prchat_ai_api_key = get_option('prchat_ai_api_key') ?: '';
$prchat_ai_model = get_option('prchat_ai_model') ?: 'gpt-4o-mini';
$prchat_speech_language = get_option('prchat_speech_language') ?: 'auto';
$prchat_voice_to_text_enabled = get_option('prchat_voice_to_text_enabled') ?: '1';
$prchat_ai_improve_enabled = get_option('prchat_ai_improve_enabled') ?: '1';
$prchat_inactivity_seconds = get_option('prchat_inactivity_seconds') ?: '10';



// Client Widget settings - use ?? instead of ?: since '0' is a valid value
$widget_position = get_option('chat_client_widget_position') ?: 'right';
$primary_color = get_option('chat_client_widget_primary_color') ?: '#4f46e5';
$show_logo_val = get_option('chat_client_widget_show_logo');
$show_logo = ($show_logo_val !== '' && $show_logo_val !== false) ? $show_logo_val : '1';
$show_names_val = get_option('chat_client_widget_show_staff_names');
$show_names = ($show_names_val !== '' && $show_names_val !== false) ? $show_names_val : '1';
$show_roles_val = get_option('chat_client_widget_show_staff_roles');
$show_roles = ($show_roles_val !== '' && $show_roles_val !== false) ? $show_roles_val : '1';
$staff_visibility = get_option('chat_client_staff_visibility') ?: 'assigned_and_responded';

// Call settings - use proper null checks since '0' is valid
$calls_clients_val = get_option('chat_client_calls_enabled');
$calls_enabled_clients = ($calls_clients_val !== '' && $calls_clients_val !== false) ? $calls_clients_val : '0';
$calls_staff_val = get_option('chat_staff_calls_enabled');
$calls_enabled_staff = ($calls_staff_val !== '' && $calls_staff_val !== false) ? $calls_staff_val : '1';
$video_calls_val = get_option('chat_calls_video_enabled');
$video_calls_enabled = ($video_calls_val !== '' && $video_calls_val !== false) ? $video_calls_val : '1';

// TURN server settings (Cloudflare)
$cf_turn_token_id = get_option('chat_calls_cf_turn_token_id') ?: '';
$cf_turn_api_token = get_option('chat_calls_cf_turn_api_token') ?: '';

// Custom TURN (non-Cloudflare)
$turn_url = get_option('chat_calls_turn_url') ?: '';
$turn_username = get_option('chat_calls_turn_username') ?: '';
$turn_credential = get_option('chat_calls_turn_credential') ?: '';
?>

<div class="panel panel-default" style="border-color:#d9e8e4;margin-bottom:18px"><div class="panel-heading" style="background:#f0faf7"><strong><i class="fa fa-wand-magic-sparkles"></i> <?php echo _l('chat_ai_voice_settings'); ?></strong></div><div class="panel-body"><div class="row"><div class="col-md-6"><label><?php echo _l('chat_ai_api_key'); ?></label><input type="password" class="form-control" name="settings[prchat_ai_api_key]" value="<?php echo html_escape($prchat_ai_api_key); ?>" autocomplete="new-password"></div><div class="col-md-3"><label><?php echo _l('chat_ai_model'); ?></label><input type="text" class="form-control" name="settings[prchat_ai_model]" value="<?php echo html_escape($prchat_ai_model); ?>"></div><div class="col-md-3"><label><?php echo _l('chat_speech_language'); ?></label><select class="form-control" name="settings[prchat_speech_language]"><option value="auto" <?php echo $prchat_speech_language==='auto'?'selected':''; ?>>Auto</option><option value="en" <?php echo $prchat_speech_language==='en'?'selected':''; ?>>English</option><option value="es" <?php echo $prchat_speech_language==='es'?'selected':''; ?>>Español</option></select></div></div><div class="row mtop15"><div class="col-md-4"><label><?php echo _l('chat_voice_to_text'); ?></label><select class="form-control" name="settings[prchat_voice_to_text_enabled]"><option value="1" <?php echo $prchat_voice_to_text_enabled==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo $prchat_voice_to_text_enabled!=='1'?'selected':''; ?>>Disabled</option></select></div><div class="col-md-4"><label><?php echo _l('chat_improve_with_ai'); ?></label><select class="form-control" name="settings[prchat_ai_improve_enabled]"><option value="1" <?php echo $prchat_ai_improve_enabled==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo $prchat_ai_improve_enabled!=='1'?'selected':''; ?>>Disabled</option></select></div><div class="col-md-4"><label><?php echo _l('chat_inactivity_seconds'); ?></label><input type="number" min="5" max="300" class="form-control" name="settings[prchat_inactivity_seconds]" value="<?php echo (int)$prchat_inactivity_seconds; ?>"></div></div><p class="text-muted mtop10"><?php echo _l('chat_ai_settings_note'); ?></p></div></div>
<!-- Settings Tabs -->
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#chat_general" aria-controls="chat_general" role="tab" data-toggle="tab">
                    <i class="fa fa-cog"></i> <?php echo _l('chat_tab_general'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_widget" aria-controls="chat_widget" role="tab" data-toggle="tab">
                    <i class="fa fa-paint-brush"></i> <?php echo _l('chat_tab_widget'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_calls" aria-controls="chat_calls" role="tab" data-toggle="tab">
                    <i class="fa fa-phone"></i> <?php echo _l('chat_tab_calls'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_template_ui" aria-controls="chat_template_ui" role="tab" data-toggle="tab">
                    <i class="fa fa-sliders"></i> Template UI
                </a>
            </li>
            <li role="presentation">
                <a href="#chat_danger" aria-controls="chat_danger" role="tab" data-toggle="tab">
                    <i class="fa fa-exclamation-triangle text-danger"></i> <?php echo _l('chat_tab_danger'); ?>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content mtop15">
    <!-- General Settings Tab -->
    <div role="tabpanel" class="tab-pane active" id="chat_general">
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_enable_option'); ?></label>
            <p class="text-muted"><?php echo _l('chat_enable_option_help'); ?></p>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="chat_enabled_yes" name="settings[pusher_chat_enabled]" value="1" <?php echo ($chat_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="chat_enabled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="chat_enabled_no" name="settings[pusher_chat_enabled]" value="0" <?php echo ($chat_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="chat_enabled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_client_module_enabled'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="client_enabled_yes" name="settings[chat_client_enabled]" value="1" <?php echo ($chat_client_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="client_enabled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="client_enabled_no" name="settings[chat_client_enabled]" value="0" <?php echo ($chat_client_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="client_enabled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_show_desktop_messages_notifications'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="notif_yes" name="settings[chat_desktop_messages_notifications]" value="1"
                        <?php echo ($notification_option == '1') ? 'checked' : ''; ?>>
                    <label for="notif_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="notif_no" name="settings[chat_desktop_messages_notifications]" value="0"
                        <?php echo ($notification_option != '1') ? 'checked' : ''; ?>>
                    <label for="notif_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>

        <hr />
        <div class="form-group">
            <label class="control-label">Maximum Chat Upload Size</label>
            <p class="text-muted">Enter the maximum upload size in KB. Default is 204800 KB. Bluehost/PHP may still enforce a lower server limit.</p>
            <input type="number" min="10240" step="1024" class="form-control" name="settings[prchat_max_upload_size_kb]" value="<?php echo html_escape($prchat_max_upload_size_kb); ?>">
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label">Copy Group Uploads To Project Media Folder</label>
            <p class="text-muted">When enabled, uploaded group files are copied to uploads/media/projects/chat_groups/group_ID so they can be reused from the CRM media project folders.</p>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="copy_group_media_yes" name="settings[prchat_copy_group_uploads_to_project_media]" value="1" <?php echo ($prchat_copy_group_uploads_to_project_media == '1') ? 'checked' : ''; ?>>
                    <label for="copy_group_media_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="copy_group_media_no" name="settings[prchat_copy_group_uploads_to_project_media]" value="0" <?php echo ($prchat_copy_group_uploads_to_project_media != '1') ? 'checked' : ''; ?>>
                    <label for="copy_group_media_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>

        <hr />
        <div class="panel panel-default" style="border-color:#d1fae5;">
            <div class="panel-heading" style="background:#ecfdf5;border-color:#d1fae5;color:#065f46;">
                <h4 class="panel-title" style="font-size:14px;font-weight:700;"><i class="fa fa-bell"></i> Smart Choice Message Actions & Notifications</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="control-label">Message Sound</label>
                        <select name="settings[prchat_smart_message_sound_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($smart_sound_enabled == '1') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo ($smart_sound_enabled != '1') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Center Toast Popup</label>
                        <select name="settings[prchat_smart_center_toast_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($smart_toast_enabled == '1') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo ($smart_toast_enabled != '1') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Create Tasks From Chat</label>
                        <select name="settings[prchat_smart_task_from_message_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($smart_task_enabled == '1') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo ($smart_task_enabled != '1') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Project Photo Actions</label>
                        <select name="settings[prchat_smart_project_photo_actions_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($smart_project_photo_enabled == '1') ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo ($smart_project_photo_enabled != '1') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                </div>
                <p class="text-muted mtop10" style="font-size:12px;">Plays the Smart Choice sound and shows a small center popup when staff receive messages, group messages, or photos. The message menu can create tasks and attach comments/photos to CRM projects.</p>
            </div>
        </div>

        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_show_toggled_chat'); ?></label>
            <p class="text-muted"><?php echo _l('chat_show_toggled_chat_desc'); ?></p>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="toggled_yes" name="settings[prchat_toggled_chat_disabled]" value="1" <?php echo ($toggled_chat_enabled == '1') ? 'checked' : ''; ?>>
                    <label for="toggled_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="toggled_no" name="settings[prchat_toggled_chat_disabled]" value="0" <?php echo ($toggled_chat_enabled != '1') ? 'checked' : ''; ?>>
                    <label for="toggled_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_staff_can_create_groups'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="groups_yes" name="settings[chat_members_can_create_groups]" value="1" <?php echo ($chat_members_can_create_groups == '1') ? 'checked' : ''; ?>>
                    <label for="groups_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="groups_no" name="settings[chat_members_can_create_groups]" value="0" <?php echo ($chat_members_can_create_groups != '1') ? 'checked' : ''; ?>>
                    <label for="groups_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="control-label"><?php echo _l('chat_allow_staff_to_create_tickets'); ?></label>
            <div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="tickets_yes" name="settings[chat_allow_staff_to_create_tickets]" value="1"
                        <?php echo ($allow_to_convert_tickets == '1') ? 'checked' : ''; ?>>
                    <label for="tickets_yes"><?php echo _l('settings_yes'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="tickets_no" name="settings[chat_allow_staff_to_create_tickets]" value="0"
                        <?php echo ($allow_to_convert_tickets != '1') ? 'checked' : ''; ?>>
                    <label for="tickets_no"><?php echo _l('settings_no'); ?></label>
                </div>
            </div>
        </div>
        <hr />
        <div class="alert alert-info">
            <a href="<?php echo admin_url('staff'); ?>" target="_blank">
                <i class="fa fa-info-circle"></i> <?php echo _l('chat_permissions_info'); ?>
            </a>
        </div>
    </div>

    <!-- Client Widget Tab -->
    <div role="tabpanel" class="tab-pane" id="chat_widget">
        <p class="text-muted"><?php echo _l('chat_widget_customization_desc'); ?></p>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_widget_position"
                        class="control-label"><?php echo _l('chat_widget_position'); ?></label>
                    <select id="chat_client_widget_position" name="settings[chat_client_widget_position]"
                        class="form-control selectpicker" data-width="100%">
                        <option value="right" <?php echo ($widget_position == 'right') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_widget_position_right'); ?></option>
                        <option value="left" <?php echo ($widget_position == 'left') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_widget_position_left'); ?></option>
                    </select>
                </div>
                <hr />
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_logo'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="logo_yes" name="settings[chat_client_widget_show_logo]" value="1"
                                <?php echo ($show_logo == '1') ? 'checked' : ''; ?>>
                            <label for="logo_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="logo_no" name="settings[chat_client_widget_show_logo]" value="0"
                                <?php echo ($show_logo != '1') ? 'checked' : ''; ?>>
                            <label for="logo_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_widget_primary_color"
                        class="control-label"><?php echo _l('chat_widget_primary_color'); ?></label>
                    <input type="color" id="chat_client_widget_primary_color" class="form-control"
                        name="settings[chat_client_widget_primary_color]"
                        value="<?php echo htmlspecialchars($primary_color); ?>" style="height: 38px; padding: 2px;">
                </div>
                <hr />
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_staff_names'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="names_yes" name="settings[chat_client_widget_show_staff_names]"
                                value="1" <?php echo ($show_names == '1') ? 'checked' : ''; ?>>
                            <label for="names_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="names_no" name="settings[chat_client_widget_show_staff_names]"
                                value="0" <?php echo ($show_names != '1') ? 'checked' : ''; ?>>
                            <label for="names_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-6">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_widget_show_staff_roles'); ?></label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="roles_yes" name="settings[chat_client_widget_show_staff_roles]"
                                value="1" <?php echo ($show_roles == '1') ? 'checked' : ''; ?>>
                            <label for="roles_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="roles_no" name="settings[chat_client_widget_show_staff_roles]"
                                value="0" <?php echo ($show_roles != '1') ? 'checked' : ''; ?>>
                            <label for="roles_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo _l('chat_staff_can_access_clients'); ?>
                        <i class="fa fa-info-circle text-info" data-toggle="tooltip" data-placement="right"
                            title="<?php echo htmlspecialchars(_l('chat_staff_can_access_clients_help')); ?>"></i>
                    </label>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="staff_access_clients_yes" name="settings[chat_staff_can_access_clients]"
                                value="1" <?php echo ($chat_staff_can_access_clients == '1') ? 'checked' : ''; ?>>
                            <label for="staff_access_clients_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="staff_access_clients_no" name="settings[chat_staff_can_access_clients]"
                                value="0" <?php echo ($chat_staff_can_access_clients != '1') ? 'checked' : ''; ?>>
                            <label for="staff_access_clients_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="chat_client_staff_visibility"
                        class="control-label">
                        <?php echo _l('chat_staff_visibility'); ?>
                        <i class="fa fa-info-circle text-info" data-toggle="tooltip" data-placement="right"
                            title="<?php echo htmlspecialchars(_l('chat_staff_visibility_desc')); ?>"></i>
                    </label>
                    <select id="chat_client_staff_visibility" name="settings[chat_client_staff_visibility]"
                        class="form-control selectpicker" data-width="100%">
                        <option value="assigned_and_responded" <?php echo ($staff_visibility == 'assigned_and_responded' || $staff_visibility == 'assigned_only') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_staff_visibility_assigned_and_responded'); ?></option>
                        <option value="all_staff" <?php echo ($staff_visibility == 'all_staff') ? 'selected' : ''; ?>>
                            <?php echo _l('chat_staff_visibility_all_staff'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="alert alert-info mtop10" style="font-size: 13px; line-height: 1.7;">
            <?php echo _l('chat_client_settings_explanation'); ?>
        </div>
    </div>

    <!-- Calls Settings Tab -->
    <div role="tabpanel" class="tab-pane" id="chat_calls">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <?php echo _l('chat_calls_webrtc_info'); ?>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_clients'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_clients_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_clients_yes" name="settings[chat_client_calls_enabled]"
                                value="1" <?php echo ($calls_enabled_clients == '1') ? 'checked' : ''; ?>>
                            <label for="calls_clients_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_clients_no" name="settings[chat_client_calls_enabled]"
                                value="0" <?php echo ($calls_enabled_clients != '1') ? 'checked' : ''; ?>>
                            <label for="calls_clients_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_staff'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_staff_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_staff_yes" name="settings[chat_staff_calls_enabled]" value="1"
                                <?php echo ($calls_enabled_staff == '1') ? 'checked' : ''; ?>>
                            <label for="calls_staff_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="calls_staff_no" name="settings[chat_staff_calls_enabled]" value="0"
                                <?php echo ($calls_enabled_staff != '1') ? 'checked' : ''; ?>>
                            <label for="calls_staff_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label"><?php echo _l('chat_calls_enable_video'); ?></label>
                    <p class="text-muted"><small><?php echo _l('chat_calls_enable_video_desc'); ?></small></p>
                    <div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="video_yes" name="settings[chat_calls_video_enabled]" value="1" <?php echo ($video_calls_enabled == '1') ? 'checked' : ''; ?>>
                            <label for="video_yes"><?php echo _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="video_no" name="settings[chat_calls_video_enabled]" value="0" <?php echo ($video_calls_enabled != '1') ? 'checked' : ''; ?>>
                            <label for="video_no"><?php echo _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="panel panel-default" style="border-color: #d1d5db;">
            <div class="panel-heading" style="background: #f9fafb; border-color: #d1d5db;">
                <h4 class="panel-title"><i class="fa fa-server"></i> <?php echo _l('chat_calls_turn_title'); ?></h4>
            </div>
            <div class="panel-body">
                <div class="alert alert-warning" style="font-size: 13px; line-height: 1.7;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong><?php echo _l('chat_calls_turn_why_title'); ?></strong><br>
                    <?php echo _l('chat_calls_turn_why_desc'); ?>
                </div>

                <h5 style="font-weight: 700; margin: 18px 0 10px;"><i class="fa fa-cloud"></i> <?php echo _l('chat_calls_turn_cf_title'); ?></h5>
                <p class="text-muted" style="font-size: 12px; margin-bottom: 12px;"><?php echo _l('chat_calls_turn_cf_desc'); ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chat_calls_cf_turn_token_id" class="control-label"><?php echo _l('chat_calls_turn_cf_token_id'); ?></label>
                            <input type="text" id="chat_calls_cf_turn_token_id" name="settings[chat_calls_cf_turn_token_id]" class="form-control" value="<?php echo htmlspecialchars($cf_turn_token_id); ?>" placeholder="582a2b846e289dabf4136cc01c9ca4ef">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chat_calls_cf_turn_api_token" class="control-label"><?php echo _l('chat_calls_turn_cf_api_token'); ?></label>
                            <input type="password" id="chat_calls_cf_turn_api_token" name="settings[chat_calls_cf_turn_api_token]" class="form-control" value="<?php echo htmlspecialchars($cf_turn_api_token); ?>" placeholder="">
                        </div>
                    </div>
                </div>
                <div class="alert alert-info" style="font-size: 12px; line-height: 1.7;">
                    <i class="fa fa-lightbulb-o"></i>
                    <strong><?php echo _l('chat_calls_turn_recommend_title'); ?></strong><br>
                    <?php echo _l('chat_calls_turn_recommend_desc'); ?>
                </div>


                <div class="panel panel-default" style="border-color:#d9e8e4;">
<div class="panel-heading" style="background:#f0faf7;"><strong><i class="fa fa-phone"></i> Staff Voice Call Mode</strong></div>
<div class="panel-body">
<div class="row"><div class="col-md-6"><label>Voice call button behavior</label><select name="settings[prchat_staff_voice_call_mode]" class="form-control selectpicker" data-width="100%"><option value="internal" <?php echo $prchat_staff_voice_call_mode === 'internal' ? 'selected' : ''; ?>>Internal PRChat browser call (WebRTC)</option><option value="phone" <?php echo $prchat_staff_voice_call_mode === 'phone' ? 'selected' : ''; ?>>Open employee CRM phone number (tel:)</option><option value="twilio" <?php echo $prchat_staff_voice_call_mode === 'twilio' ? 'selected' : ''; ?>>Real phone call through Twilio Voice bridge</option></select></div></div>
<p class="text-muted mtop10"><strong>Internal PRChat call:</strong> rings the employee only inside an open CRM browser session and requires Pusher, microphone permission, and WebRTC/TURN. <strong>Phone-number mode:</strong> opens the employee number in the computer or mobile device's configured phone/softphone application. <strong>Twilio Voice bridge:</strong> places a real carrier call to the employee and then bridges it to the logged-in caller's CRM phone number. Twilio credentials alone do not turn the browser call button into a telephone line without a Twilio Voice application and approved routing.</p>
</div></div>
<h5 style="font-weight:700;margin:18px 0 10px;"><i class="fa fa-commenting"></i> Employee SMS</h5>
<div class="row"><div class="col-md-6"><label>Enable employee SMS</label><select name="settings[prchat_sms_enabled]" class="form-control selectpicker" data-width="100%"><option value="1" <?php echo get_option('prchat_sms_enabled') == '1' ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo get_option('prchat_sms_enabled') != '1' ? 'selected' : ''; ?>>No</option></select></div><div class="col-md-6"><label>Twilio status callback URL</label><input type="url" class="form-control" name="settings[prchat_sms_status_callback]" value="<?php echo html_escape(get_option('prchat_sms_status_callback')); ?>" placeholder="Optional HTTPS callback URL"></div></div>
<p class="text-muted mtop10">SMS uses the employee phone number saved in the CRM staff profile. Every attempt is recorded in Messaging Chat → SMS Log, including failed Twilio requests.</p>
<h5 style="font-weight: 700; margin: 18px 0 10px;"><i class="fa fa-phone-square"></i> Twilio Voice / Video Verification</h5>
                <p class="text-muted" style="font-size:12px;">Optional verification settings for Twilio-powered voice/video workflows. This does not force calls to Twilio; it stores credentials and health-check information so the CRM can verify the integration.</p>
                <div class="row">
                    <div class="col-md-3">
                        <label class="control-label">Enable Twilio</label>
                        <select name="settings[prchat_twilio_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($twilio_enabled == '1') ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo ($twilio_enabled != '1') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Twilio Video</label>
                        <select name="settings[prchat_twilio_video_enabled]" class="form-control selectpicker" data-width="100%">
                            <option value="1" <?php echo ($twilio_video_enabled == '1') ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo ($twilio_video_enabled != '1') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label">Twilio Phone Number</label>
                        <input type="text" class="form-control" name="settings[prchat_twilio_phone_number]" value="<?php echo html_escape($twilio_phone_number); ?>" placeholder="+17277553786">
                    </div>
                </div>
                <div class="row mtop10">
                    <div class="col-md-4">
                        <label class="control-label">Account SID</label>
                        <input type="text" class="form-control" name="settings[prchat_twilio_account_sid]" value="<?php echo html_escape($twilio_account_sid); ?>" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="col-md-4">
                        <label class="control-label">Auth Token</label>
                        <input type="password" class="form-control" name="settings[prchat_twilio_auth_token]" value="<?php echo html_escape($twilio_auth_token); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="control-label">Voice Webhook URL</label>
                        <input type="text" class="form-control" name="settings[prchat_twilio_voice_webhook]" value="<?php echo html_escape($twilio_voice_webhook); ?>" placeholder="https://crm.justsmartchoice.com/...">
                    </div>
                </div>
                <div class="alert alert-info mtop10" style="font-size:12px;">Use the Health Check page to verify whether Twilio settings are present. Live inbound/outbound phone calling still requires a valid Twilio account and approved routing.</div>

                <h5 style="font-weight: 700; margin: 18px 0 10px;"><i class="fa fa-cogs"></i> <?php echo _l('chat_calls_turn_custom_title'); ?></h5>
                <p class="text-muted" style="font-size: 12px; margin-bottom: 12px;"><?php echo _l('chat_calls_turn_custom_desc'); ?></p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="chat_calls_turn_url" class="control-label"><?php echo _l('chat_calls_turn_url_label'); ?></label>
                            <input type="text" id="chat_calls_turn_url" name="settings[chat_calls_turn_url]" class="form-control" value="<?php echo htmlspecialchars($turn_url); ?>" placeholder="turn:your-server.com:3478">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chat_calls_turn_username" class="control-label"><?php echo _l('chat_calls_turn_username_label'); ?></label>
                            <input type="text" id="chat_calls_turn_username" name="settings[chat_calls_turn_username]" class="form-control" value="<?php echo htmlspecialchars($turn_username); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chat_calls_turn_credential" class="control-label"><?php echo _l('chat_calls_turn_credential_label'); ?></label>
                            <input type="password" id="chat_calls_turn_credential" name="settings[chat_calls_turn_credential]" class="form-control" value="<?php echo htmlspecialchars($turn_credential); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone Tab -->

    <div role="tabpanel" class="tab-pane" id="chat_template_ui">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Smart Choice Template UI Controls</strong><br>
            These settings control the CRM navigation bar, logo size, mobile hamburger color, dropdown scroll behavior, table compact view, client login button size, and gradient text rule. Use this area to tune the look without editing code again.
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-paint-brush"></i> Navigation And Mobile Controls</h4></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3"><label>Enable UI Repair</label><select name="settings[prchat_template_ui_enabled]" class="form-control selectpicker" data-width="100%"><option value="1" <?php echo ($prchat_template_ui_enabled == '1') ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo ($prchat_template_ui_enabled != '1') ? 'selected' : ''; ?>>Disabled</option></select></div>
                            <div class="col-md-3"><label>Navigation Height</label><input type="number" min="44" max="90" class="form-control prchat-ui-live" name="settings[prchat_template_navbar_height]" value="<?php echo html_escape($prchat_template_navbar_height); ?>" data-css="--demo-navbar-height" data-unit="px"></div>
                            <div class="col-md-3"><label>Logo Max Height</label><input type="number" min="34" max="84" class="form-control prchat-ui-live" name="settings[prchat_template_logo_max_height]" value="<?php echo html_escape($prchat_template_logo_max_height); ?>" data-css="--demo-logo-height" data-unit="px"></div>
                            <div class="col-md-3"><label>Hamburger Size</label><input type="number" min="16" max="36" class="form-control prchat-ui-live" name="settings[prchat_template_hamburger_size]" value="<?php echo html_escape($prchat_template_hamburger_size); ?>" data-css="--demo-hamburger-size" data-unit="px"></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3"><label>Dropdown Max Height</label><input type="number" min="160" max="620" class="form-control prchat-ui-live" name="settings[prchat_template_dropdown_max_height]" value="<?php echo html_escape($prchat_template_dropdown_max_height); ?>" data-css="--demo-dropdown-height" data-unit="px"></div>
                            <div class="col-md-3"><label>Table Font Size</label><input type="number" min="10" max="16" class="form-control prchat-ui-live" name="settings[prchat_template_table_font_size]" value="<?php echo html_escape($prchat_template_table_font_size); ?>" data-css="--demo-table-font" data-unit="px"></div>
                            <div class="col-md-3"><label>Full Name Column</label><input type="number" min="120" max="320" class="form-control prchat-ui-live" name="settings[prchat_template_full_name_width]" value="<?php echo html_escape($prchat_template_full_name_width); ?>" data-css="--demo-name-width" data-unit="px"></div>
                            <div class="col-md-3"><label>Email Column</label><input type="number" min="90" max="260" class="form-control prchat-ui-live" name="settings[prchat_template_email_width]" value="<?php echo html_escape($prchat_template_email_width); ?>" data-css="--demo-email-width" data-unit="px"></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3"><label>Client Login Font</label><input type="number" min="10" max="16" class="form-control prchat-ui-live" name="settings[prchat_template_client_login_font_size]" value="<?php echo html_escape($prchat_template_client_login_font_size); ?>" data-css="--demo-login-font" data-unit="px"></div>
                            <div class="col-md-3"><label>Client Login Padding</label><input type="number" min="4" max="16" class="form-control prchat-ui-live" name="settings[prchat_template_client_login_padding]" value="<?php echo html_escape($prchat_template_client_login_padding); ?>" data-css="--demo-login-padding" data-unit="px"></div>
                            <div class="col-md-3"><label>Button Background</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_button_bg]" value="<?php echo html_escape($prchat_template_button_bg); ?>" data-css="--demo-button-bg"></div>
                            <div class="col-md-3"><label>Button Text</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_button_text]" value="<?php echo html_escape($prchat_template_button_text); ?>" data-css="--demo-button-text"></div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-fill-drip"></i> Gradient And Text Rules</h4></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3"><label>Gradient Start</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_gradient_start]" value="<?php echo html_escape($prchat_template_gradient_start); ?>" data-css="--demo-gradient-start"></div>
                            <div class="col-md-3"><label>Gradient End</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_gradient_end]" value="<?php echo html_escape($prchat_template_gradient_end); ?>" data-css="--demo-gradient-end"></div>
                            <div class="col-md-3"><label>Gradient Text</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_gradient_text]" value="<?php echo html_escape($prchat_template_gradient_text); ?>" data-css="--demo-gradient-text"></div>
                            <div class="col-md-3"><label>Hover Text</label><input type="color" class="form-control prchat-ui-live" name="settings[prchat_template_hover_text]" value="<?php echo html_escape($prchat_template_hover_text); ?>" data-css="--demo-hover-text"></div>
                        </div>
                        <p class="text-muted mtop10">Rule applied globally: anything with a green/blue gradient keeps white text. Hover text becomes black or the hover color selected here.</p>
                    </div>
                </div>

                <div class="panel panel-warning">
                    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-undo"></i> Save And Restore Design Profile</h4></div>
                    <div class="panel-body">
                        <p class="text-muted">Click Save Current Profile before experimenting. If you do not like the next look, paste the saved values back or use the browser back/restore workflow. The saved profile is kept permanently in the CRM options table.</p>
                        <textarea class="form-control" rows="5" name="settings[prchat_template_saved_profile]" id="prchat_template_saved_profile"><?php echo html_escape($prchat_template_saved_profile); ?></textarea>
                        <div class="mtop10">
                            <button type="button" class="btn btn-default" id="prchat_save_profile_now"><i class="fa fa-save"></i> Save Current Profile In Box</button>
                            <button type="button" class="btn btn-warning" id="prchat_restore_profile_now"><i class="fa fa-undo"></i> Restore Box Values To Fields</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-success">
                    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-eye"></i> Live Sample Preview</h4></div>
                    <div class="panel-body">
                        <div id="prchat-ui-preview" style="--demo-navbar-height:<?php echo (int)$prchat_template_navbar_height; ?>px;--demo-logo-height:<?php echo (int)$prchat_template_logo_max_height; ?>px;--demo-hamburger-size:<?php echo (int)$prchat_template_hamburger_size; ?>px;--demo-dropdown-height:<?php echo (int)$prchat_template_dropdown_max_height; ?>px;--demo-table-font:<?php echo (int)$prchat_template_table_font_size; ?>px;--demo-name-width:<?php echo (int)$prchat_template_full_name_width; ?>px;--demo-email-width:<?php echo (int)$prchat_template_email_width; ?>px;--demo-login-font:<?php echo (int)$prchat_template_client_login_font_size; ?>px;--demo-login-padding:<?php echo (int)$prchat_template_client_login_padding; ?>px;--demo-gradient-start:<?php echo html_escape($prchat_template_gradient_start); ?>;--demo-gradient-end:<?php echo html_escape($prchat_template_gradient_end); ?>;--demo-gradient-text:<?php echo html_escape($prchat_template_gradient_text); ?>;--demo-hover-text:<?php echo html_escape($prchat_template_hover_text); ?>;--demo-button-bg:<?php echo html_escape($prchat_template_button_bg); ?>;--demo-button-text:<?php echo html_escape($prchat_template_button_text); ?>;">
                            <div class="sc-demo-nav"><span class="sc-demo-hamb">☰</span><span class="sc-demo-logo">SMART CHOICE</span><button>Login</button></div>
                            <div class="sc-demo-gradient">Gradient bar text stays white. Hover becomes dark.</div>
                            <div class="sc-demo-table"><div style="width:var(--demo-name-width)">Full name column</div><div style="width:var(--demo-email-width)">Email column</div><div>Facebook</div><div>Telegram</div><div>LinkedIn</div><div>Instagram</div></div>
                            <div class="sc-demo-select"><strong>Dropdown</strong><br>One clean scroll bar only.</div>
                        </div>
                        <style>
                            #prchat-ui-preview .sc-demo-nav{height:var(--demo-navbar-height);background:#111827;border-radius:10px;display:flex;align-items:center;gap:10px;padding:8px;color:#fff}#prchat-ui-preview .sc-demo-hamb{font-size:var(--demo-hamburger-size);color:#fff}#prchat-ui-preview .sc-demo-logo{height:var(--demo-logo-height);max-height:var(--demo-logo-height);display:flex;align-items:center;font-weight:800;background:#fff;color:#169179;border-radius:8px;padding:0 10px}#prchat-ui-preview button{margin-left:auto;background:var(--demo-button-bg);color:var(--demo-button-text);font-size:var(--demo-login-font);padding:var(--demo-login-padding);border:0;border-radius:8px}#prchat-ui-preview .sc-demo-gradient{margin-top:12px;padding:12px;border-radius:10px;background:linear-gradient(90deg,var(--demo-gradient-start),var(--demo-gradient-end));color:var(--demo-gradient-text);font-weight:700}#prchat-ui-preview .sc-demo-gradient:hover{color:var(--demo-hover-text)}#prchat-ui-preview .sc-demo-table{margin-top:12px;display:flex;gap:4px;font-size:var(--demo-table-font);overflow:hidden}#prchat-ui-preview .sc-demo-table div{background:#f3f4f6;border:1px solid #e5e7eb;border-radius:6px;padding:7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#prchat-ui-preview .sc-demo-select{margin-top:12px;max-height:var(--demo-dropdown-height);overflow-y:auto;border:1px solid #d1d5db;border-radius:10px;padding:10px;background:#fff}
                        </style>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="chat_danger">
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> <strong><?php echo _l('chat_danger_zone_warning'); ?></strong>
        </div>

        <div class="row">
            <!-- Legacy Chat Data -->
            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo _l('chat_danger_delete_old_conversations'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_delete_old_desc'); ?></small></p>

                        <!-- Staff History -->
                        <div class="mbot15"
                            style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-users text-muted"></i>
                                        <?php echo _l("chat_purge_staff_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeStaffHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_staff'); ?>
                            </p>
                        </div>

                        <!-- Client History -->
                        <div class="mbot15"
                            style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-user text-muted"></i>
                                        <?php echo _l("chat_purge_clients_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeClientsHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_clients'); ?>
                            </p>
                        </div>

                        <!-- Group History -->
                        <div style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-comments text-muted"></i>
                                        <?php echo _l("chat_purge_groups_label"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeGroupsHistory()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_groups'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Chatbot & Full Wipe -->
            <div class="col-md-6">
                <!-- AI Chatbot Data -->
                <div class="panel panel-danger mbot20">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo _l('chat_danger_ai_chatbot_data'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_ai_chatbot_desc'); ?></small></p>
                        <div style="padding: 10px; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px;"><i class="fa fa-robot text-muted"></i>
                                        <?php echo _l('chat_danger_purge_chatbot'); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button"
                                        onclick="purgeChatbotConversations()">
                                        <i class="fa fa-trash"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_chatbot'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Complete Wipe -->
                <div class="panel panel-danger">
                    <div class="panel-heading" style="background-color: #f2dede;">
                        <h4 class="panel-title" style="color: #a94442;"><i class="fa fa-bomb"></i>
                            <?php echo _l("chat_delete_all_data"); ?></h4>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted mbot15"><small><?php echo _l('chat_danger_full_reset_desc'); ?></small></p>
                        <div style="padding: 10px; background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 3px;">
                            <div class="clearfix mbot10">
                                <div class="pull-left">
                                    <strong style="font-size: 13px; color: #a94442;"><i
                                            class="fa fa-exclamation-circle"></i>
                                        <?php echo _l("chat_purge_everything"); ?></strong>
                                </div>
                                <div class="pull-right">
                                    <button class="btn btn-danger btn-xs" type="button" onclick="purgeAllHistory()">
                                        <i class="fa fa-bomb"></i> <?php echo _l('chatbot_delete'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted" style="margin: 0; font-size: 11px; line-height: 1.5;">
                                <strong><?php echo _l('chat_danger_deletes_label'); ?></strong>
                                <?php echo _l('chat_danger_deletes_all'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const settingsGroupLink = document.querySelector('.settings-group-perfex_chat_settings a');
        settingsGroupLink?.classList.remove('tw-text-neutral-800');
        settingsGroupLink?.classList.add('tw-text-black');


        const liveInputs = document.querySelectorAll('.prchat-ui-live');
        const preview = document.getElementById('prchat-ui-preview');
        liveInputs.forEach((input) => {
            input.addEventListener('input', () => {
                if (!preview || !input.dataset.css) return;
                preview.style.setProperty(input.dataset.css, input.value + (input.dataset.unit || ''));
            });
        });
        const profileFields = [
            'prchat_template_ui_enabled','prchat_template_navbar_height','prchat_template_logo_max_height','prchat_template_hamburger_size','prchat_template_dropdown_max_height','prchat_template_table_font_size','prchat_template_full_name_width','prchat_template_email_width','prchat_template_client_login_font_size','prchat_template_client_login_padding','prchat_template_gradient_start','prchat_template_gradient_end','prchat_template_gradient_text','prchat_template_hover_text','prchat_template_button_bg','prchat_template_button_text'
        ];
        document.getElementById('prchat_save_profile_now')?.addEventListener('click', () => {
            const data = {};
            profileFields.forEach((name) => {
                const field = document.querySelector('[name="settings[' + name + ']"]');
                if (field) data[name] = field.value;
            });
            const box = document.getElementById('prchat_template_saved_profile');
            if (box) box.value = JSON.stringify(data, null, 2);
        });
        document.getElementById('prchat_restore_profile_now')?.addEventListener('click', () => {
            const box = document.getElementById('prchat_template_saved_profile');
            if (!box || !box.value.trim()) return;
            try {
                const data = JSON.parse(box.value);
                Object.keys(data).forEach((name) => {
                    const field = document.querySelector('[name="settings[' + name + ']"]');
                    if (field) {
                        field.value = data[name];
                        field.dispatchEvent(new Event('input', {bubbles:true}));
                        if (typeof jQuery !== 'undefined' && jQuery(field).hasClass('selectpicker')) {
                            jQuery(field).selectpicker('refresh');
                        }
                    }
                });
            } catch (e) {
                alert('Saved profile is not valid JSON.');
            }
        });

        if (typeof jQuery !== 'undefined') {
            jQuery('#chat_widget [data-toggle="tooltip"], #chat_general [data-toggle="tooltip"]').tooltip({container: 'body', trigger: 'hover focus'});
        }
    });
</script>
