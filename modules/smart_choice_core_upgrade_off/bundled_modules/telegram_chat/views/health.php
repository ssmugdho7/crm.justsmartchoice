<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s telegram-smart-panel">
            <div class="panel-body">
                <div class="telegram-header">
                    <div>
                        <h3>Telegram Health Checker</h3>
                        <p>Check whether the module tables, settings, and bot connection are ready.</p>
                    </div>
                    <div class="telegram-actions">
                        <a href="<?php echo admin_url('telegram_chat/send_test'); ?>" class="btn btn-warning">Send Test Notification</a>
                        <a href="<?php echo admin_url('telegram_chat/messages'); ?>" class="btn btn-info">Message Center</a>
                        <a href="<?php echo admin_url('telegram_chat'); ?>" class="btn btn-default">Settings</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="telegram-health-card">
                            <strong>Bot Token</strong>
                            <span><?php echo (!empty($userTeleInfo->bot_token) ? 'Configured' : 'Missing'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="telegram-health-card">
                            <strong>Chat / Group ID</strong>
                            <span><?php echo (!empty($userTeleInfo->chat_id) ? html_escape($userTeleInfo->chat_id) : 'Missing'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="telegram-health-card">
                            <strong>Message Log Table</strong>
                            <span><?php echo $table_exists ? 'Ready' : 'Missing'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mtop20">
                    <strong>Saved CRM Telegram Messages:</strong> <?php echo (int) $messages_count; ?><br>
                    Use the Message Center to search old Telegram notifications, copy messages, sync incoming updates, or delete selected CRM log records.
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
.telegram-health-card{background:#f5f7f8;border-radius:14px;padding:22px;border-top:5px solid #f47c20;box-shadow:0 10px 24px rgba(0,0,0,.08);}
.telegram-health-card strong{display:block;color:#169179;font-size:17px;margin-bottom:8px;}
.telegram-health-card span{color:#263238;font-weight:700;}
</style>
<?php init_tail(); ?>
