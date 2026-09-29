<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s telegram-smart-panel">
            <div class="panel-body">
                <div class="telegram-header">
                    <div>
                        <h3>Telegram Help / Training</h3>
                        <p>How to use Telegram Chat with the Smart Choice CRM workflow.</p>
                    </div>
                    <div class="telegram-actions">
                        <a href="<?php echo admin_url('telegram_chat'); ?>" class="btn btn-default">Settings</a>
                        <a href="<?php echo admin_url('telegram_chat/messages'); ?>" class="btn btn-info">Message Center</a>
                        <a href="<?php echo admin_url('telegram_chat/health'); ?>" class="btn btn-success">Health Checker</a>
                    </div>
                </div>

                <div class="telegram-training-grid">
                    <div class="telegram-training-card">
                        <h4>1. Configure Telegram</h4>
                        <p>Enter your Bot Token and Chat / Group ID in Telegram Settings. Keep the working token and chat ID that already sends notifications to your phone.</p>
                    </div>
                    <div class="telegram-training-card">
                        <h4>2. Keep Phone Notifications</h4>
                        <p>The module still sends CRM notifications to Telegram. The new Message Center saves a copy inside the CRM for larger-screen review and long-term searching.</p>
                    </div>
                    <div class="telegram-training-card">
                        <h4>3. Review Old Messages</h4>
                        <p>Open Telegram Message Center to search by module, action, text, sender, date, and direction. Use Copy to reuse messages or Delete Selected to clean CRM logs.</p>
                    </div>
                    <div class="telegram-training-card">
                        <h4>4. Sync Incoming Updates</h4>
                        <p>Click Sync Incoming Updates to pull recent messages from Telegram getUpdates into the CRM log table when the Telegram Bot API allows it.</p>
                    </div>
                    <div class="telegram-training-card">
                        <h4>5. Client Portal Notices</h4>
                        <p>Settings include notifications for client login, ticket activity, proposal acceptance, file uploads, and portal message hooks where Perfex exposes those events.</p>
                    </div>
                    <div class="telegram-training-card">
                        <h4>6. Health Checker</h4>
                        <p>Use the Health Checker button after updates to confirm settings, database table readiness, saved messages, and test notifications.</p>
                    </div>
                </div>

                <div class="alert alert-warning mtop20">
                    <strong>Important:</strong> This update does not replace your Telegram bot or change your working token. It adds CRM-side logging, searching, and extra notification hooks.
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
.telegram-training-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;}
.telegram-training-card{background:#f5f7f8;border-radius:14px;padding:22px;border-left:5px solid #f47c20;box-shadow:0 10px 24px rgba(0,0,0,.08);}
.telegram-training-card h4{color:#169179;font-weight:800;margin-top:0;}
.telegram-training-card p{color:#444;line-height:1.65;}
</style>
<?php init_tail(); ?>
