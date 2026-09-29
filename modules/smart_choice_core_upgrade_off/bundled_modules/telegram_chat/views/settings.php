<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s telegram-smart-panel">
                    <div class="panel-body">
                        <div class="telegram-header">
                            <div>
                                <h3>Telegram Chat Settings</h3>
                                <p>Connect your working Telegram bot, choose notification options, and open the CRM Message Center.</p>
                            </div>
                            <div class="telegram-actions">
                                <a href="<?php echo admin_url('telegram_chat/messages'); ?>" class="btn btn-info">Open Message Center</a>
                                <a href="<?php echo admin_url('telegram_chat/health'); ?>" class="btn btn-success">Health Checker</a>
                                <a href="<?php echo admin_url('telegram_chat/training'); ?>" class="btn btn-default">Help / Training</a>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('telegram_chat/addTelegramInfo')); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_input('bot_token', 'Telegram Bot Token', isset($userTeleInfo->bot_token) ? $userTeleInfo->bot_token : '', 'text', ['autocomplete' => 'off']); ?>
                                    <p class="text-muted">Paste the Telegram bot token exactly as BotFather provides it. Do not add extra spaces.</p>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('chat_id', 'Telegram Chat / Group ID', isset($userTeleInfo->chat_id) ? $userTeleInfo->chat_id : '', 'text', ['autocomplete' => 'off']); ?>
                                    <p class="text-muted">Use the chat ID or group ID where CRM notifications should be sent.</p>
                                </div>
                            </div>

                            <hr>

                            <h4>Notification and CRM Log Options</h4>
                            <div class="row telegram-option-grid">
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_message_log', 'Save Telegram messages inside CRM', 'Stores outgoing CRM Telegram notifications in the Message Center.'); ?></div>
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_client_login_notice', 'Notify client login', 'Sends a Telegram notice when a client logs in, when supported by the hook.'); ?></div>
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_ticket_notice', 'Notify ticket activity', 'Sends/logs Telegram notices for ticket activity.'); ?></div>
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_proposal_notice', 'Notify proposal activity', 'Sends Telegram notices for proposal activity and acceptance.'); ?></div>
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_file_notice', 'Notify client file uploads', 'Sends Telegram notices when client file upload hooks are available.'); ?></div>
                                <div class="col-md-4"><?php render_yes_no_option('telegram_chat_enable_portal_message_notice', 'Notify portal messages', 'Prepared for portal message hooks and integrations.'); ?></div>
                            </div>

                            <div class="text-right mtop20">
                                <button type="submit" class="btn btn-primary">Save Telegram Settings</button>
                            </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.telegram-smart-panel{border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(0,0,0,.10);}
.telegram-header{display:flex;align-items:center;justify-content:space-between;gap:18px;background:linear-gradient(135deg,#169179,#0057b8);color:#fff;padding:22px;border-radius:12px;margin-bottom:22px;}
.telegram-header h3{margin:0 0 6px;color:#fff;font-weight:800;}
.telegram-header p{margin:0;color:#eaf4ff;}
.telegram-actions .btn{margin-left:6px;margin-bottom:6px;border-radius:8px;}
.telegram-option-grid .form-group{background:#f5f7f8;border-radius:12px;padding:14px;min-height:120px;border-top:4px solid #f47c20;}
@media(max-width:768px){.telegram-header{display:block}.telegram-actions{margin-top:16px}.telegram-actions .btn{display:block;width:100%;margin:8px 0;}}
</style>
<?php init_tail(); ?>
