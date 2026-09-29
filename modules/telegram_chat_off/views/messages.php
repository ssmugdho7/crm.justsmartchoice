<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s telegram-smart-panel">
            <div class="panel-body">
<?php $this->load->view('telegram_chat/top_navigation'); ?>
                <div class="telegram-header">
                    <div>
                        <h3>Telegram Message Center</h3>
                        <p>Review, search, copy, sync, and clean CRM Telegram notifications from a larger screen.</p>
                    </div>
                    <div class="telegram-actions">
                        <a href="<?php echo admin_url('telegram_chat/sync_updates'); ?>" class="btn btn-info">Sync Incoming Updates</a>
                        <a href="<?php echo admin_url('telegram_chat'); ?>" class="btn btn-default">Settings</a>
                        <a href="<?php echo admin_url('telegram_chat/health'); ?>" class="btn btn-success">Health Checker</a>
                    </div>
                </div>

                <?php echo form_open(admin_url('telegram_chat/messages'), ['method' => 'get']); ?>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="q" class="form-control" placeholder="Search messages, module, action, sender" value="<?php echo html_escape(isset($filters['q']) ? $filters['q'] : ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="module" class="form-control" placeholder="Module" value="<?php echo html_escape(isset($filters['module']) ? $filters['module'] : ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="direction" class="form-control">
                                <option value="">All Directions</option>
                                <option value="outgoing" <?php echo isset($filters['direction']) && $filters['direction'] == 'outgoing' ? 'selected' : ''; ?>>Outgoing</option>
                                <option value="incoming" <?php echo isset($filters['direction']) && $filters['direction'] == 'incoming' ? 'selected' : ''; ?>>Incoming</option>
                                <option value="system" <?php echo isset($filters['direction']) && $filters['direction'] == 'system' ? 'selected' : ''; ?>>System</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" value="<?php echo html_escape(isset($filters['date_from']) ? $filters['date_from'] : ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">Search</button>
                        </div>
                    </div>
                <?php echo form_close(); ?>

                <hr>

                <?php echo form_open(admin_url('telegram_chat/delete_messages')); ?>
                    <div class="table-responsive">
                        <table class="table table-striped telegram-message-table">
                            <thead>
                                <tr>
                                    <th style="width:35px;"><input type="checkbox" onclick="$('.telegram-row-check').prop('checked', this.checked);"></th>
                                    <th>Date</th>
                                    <th>Direction</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>Message</th>
                                    <th style="width:90px;">Copy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($messages)) { foreach ($messages as $message) { ?>
                                    <tr>
                                        <td><input type="checkbox" class="telegram-row-check" name="ids[]" value="<?php echo (int) $message['id']; ?>"></td>
                                        <td><?php echo html_escape($message['created_at']); ?></td>
                                        <td><span class="telegram-pill"><?php echo html_escape($message['message_direction']); ?></span></td>
                                        <td><?php echo html_escape($message['module']); ?></td>
                                        <td><?php echo html_escape($message['action']); ?></td>
                                        <td>
                                            <div class="telegram-message-text" id="telegram-msg-<?php echo (int) $message['id']; ?>"><?php echo nl2br(html_escape($message['message_text'])); ?></div>
                                        </td>
                                        <td><button type="button" class="btn btn-default btn-sm" onclick="telegramCopyText('telegram-msg-<?php echo (int) $message['id']; ?>')">Copy</button></td>
                                    </tr>
                                <?php } } else { ?>
                                    <tr><td colspan="7" class="text-center text-muted">No Telegram log messages found yet.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete selected Telegram log records from CRM?');">Delete Selected</button>
                    <span class="text-muted mleft10">Showing <?php echo count($messages); ?> of <?php echo (int) $total; ?> records.</span>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
function telegramCopyText(id) {
    var el = document.getElementById(id);
    var text = el ? el.innerText : '';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        var area = document.createElement('textarea');
        area.value = text;
        document.body.appendChild(area);
        area.select();
        document.execCommand('copy');
        document.body.removeChild(area);
    }
    alert('Message copied.');
}
</script>
<style>
.telegram-smart-panel{border-radius:14px;overflow:hidden;box-shadow:0 12px 28px rgba(0,0,0,.10);}
.telegram-header{display:flex;align-items:center;justify-content:space-between;gap:18px;background:linear-gradient(135deg,#169179,#0057b8);color:#fff;padding:22px;border-radius:12px;margin-bottom:22px;}
.telegram-header h3{margin:0 0 6px;color:#fff;font-weight:800;}
.telegram-header p{margin:0;color:#eaf4ff;}
.telegram-actions .btn{margin-left:6px;margin-bottom:6px;border-radius:8px;}
.telegram-pill{background:#eaf4ff;color:#0057b8;padding:4px 8px;border-radius:20px;font-weight:700;font-size:12px;}
.telegram-message-text{max-width:620px;white-space:normal;line-height:1.5;}
@media(max-width:768px){.telegram-header{display:block}.telegram-actions{margin-top:16px}.telegram-actions .btn{display:block;width:100%;margin:8px 0;}.telegram-message-table{font-size:12px;}}
</style>
<?php init_tail(); ?>
