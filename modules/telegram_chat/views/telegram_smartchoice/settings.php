<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<div class="telegram-sc-header"><div><h1>Telegram CRM Connect</h1><p>Lead alerts, project communication, payments, support tickets, documents, inspections, permits, and CRM commands through Telegram.</p></div><a href="<?php echo admin_url('telegram_crm_connect/health'); ?>" class="btn btn-default">Health Check</a></div>
<?php echo form_open(admin_url('telegram_crm_connect/settings')); ?>
<div class="row">
<div class="col-md-6"><div class="panel_s telegram-sc-card"><div class="panel-body"><h4>Telegram Bot and Chat Settings</h4>
<?php echo render_input('telegram_bot_token','Bot Token',get_option('telegram_bot_token')); ?>
<?php echo render_input('telegram_owner_chat_id','Owner Chat ID',get_option('telegram_owner_chat_id')); ?>
<?php echo render_input('telegram_crm_alerts_group_id','CRM Alerts Group ID',get_option('telegram_crm_alerts_group_id')); ?>
<?php echo render_input('telegram_project_alerts_group_id','Project Alerts Group ID',get_option('telegram_project_alerts_group_id')); ?>
<?php echo render_input('telegram_payments_group_id','Payments Group ID',get_option('telegram_payments_group_id')); ?>
<?php echo render_input('telegram_support_group_id','Support Group ID',get_option('telegram_support_group_id')); ?>
<?php echo render_input('telegram_default_chat_id','Default Chat ID',get_option('telegram_default_chat_id')); ?>
</div></div></div>
<div class="col-md-6"><div class="panel_s telegram-sc-card"><div class="panel-body"><h4>Notification Toggles</h4>
<?php $toggles=['telegram_notify_new_leads'=>'New Leads','telegram_notify_lead_assignment'=>'Lead Assignment','telegram_notify_lead_status_changes'=>'Lead Status Changes','telegram_notify_new_customers'=>'New Customers','telegram_notify_new_projects'=>'New Projects','telegram_notify_project_status_changes'=>'Project Status Changes','telegram_notify_task_assignment'=>'Task Assignment','telegram_notify_completed_tasks'=>'Completed Tasks','telegram_notify_overdue_tasks'=>'Overdue Tasks','telegram_notify_estimates_created'=>'Estimates Created','telegram_notify_estimates_accepted'=>'Estimates Accepted','telegram_notify_invoices_created'=>'Invoices Created','telegram_notify_invoices_paid'=>'Invoices Paid','telegram_notify_payments_received'=>'Payments Received','telegram_notify_support_tickets'=>'Support Tickets','telegram_notify_ticket_replies'=>'Ticket Replies','telegram_notify_proposals_accepted'=>'Proposals Accepted','telegram_notify_contracts_signed'=>'Contracts Signed','telegram_notify_permits'=>'Permits','telegram_notify_inspections'=>'Inspections','telegram_notify_document_uploads'=>'Document Uploads']; foreach($toggles as $key=>$label){ ?>
<div class="checkbox checkbox-primary"><input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php echo get_option($key)=='1'?'checked':''; ?>><label for="<?php echo $key; ?>"><?php echo $label; ?></label></div>
<?php } ?></div></div></div></div>
<div class="panel_s telegram-sc-card"><div class="panel-body"><h4>Message Templates</h4><div class="row">
<div class="col-md-4"><?php echo render_textarea('templates[new_lead]','New Lead Template',get_option('telegram_template_new_lead') ?: 'New lead: {lead_name}'); ?></div>
<div class="col-md-4"><?php echo render_textarea('templates[new_project]','New Project Template',get_option('telegram_template_new_project') ?: 'New project: {project_name}'); ?></div>
<div class="col-md-4"><?php echo render_textarea('templates[payment_received]','Payment Received Template',get_option('telegram_template_payment_received') ?: 'Payment received: {amount}'); ?></div>
</div></div></div>
<div class="panel_s telegram-sc-card"><div class="panel-body"><h4>Test and Discover Chat IDs</h4>
<div class="row"><div class="col-md-4"><input type="text" id="telegram_test_chat_id" class="form-control" placeholder="Chat or Group ID"></div><div class="col-md-8"><button type="button" class="btn btn-info" onclick="telegramTestChat()">Send Test Message</button> <button type="button" class="btn btn-warning" onclick="telegramLoadChats()">Load Recent Telegram Chat IDs</button></div></div><div id="telegram-chat-results" class="mtop15"></div>
</div></div>
<button type="submit" class="btn btn-primary">Save Telegram Settings</button>
<?php echo form_close(); ?></div></div>
<script>
function telegramTestChat(){var chatId=$('#telegram_test_chat_id').val();$.post(admin_url+'telegram_crm_connect/test_chat',{chat_id:chatId},function(resp){alert('Test sent. Response: '+resp);});}
function telegramLoadChats(){$('#telegram-chat-results').html('<p>Loading recent chat IDs...</p>');$.get(admin_url+'telegram_crm_connect/recent_chats',function(resp){var data={};try{data=JSON.parse(resp);}catch(e){} if(!data.chats||!data.chats.length){$('#telegram-chat-results').html('<div class="alert alert-warning">No chat IDs found. Send a message to your bot first.</div>');return;} var html='<table class="table table-bordered"><thead><tr><th>Chat ID</th><th>Type</th><th>Name</th><th>Username</th></tr></thead><tbody>';data.chats.forEach(function(c){html+='<tr><td>'+c.id+'</td><td>'+c.type+'</td><td>'+c.title+'</td><td>'+c.username+'</td></tr>';});html+='</tbody></table>';$('#telegram-chat-results').html(html);});}
</script>
<?php init_tail(); ?>
