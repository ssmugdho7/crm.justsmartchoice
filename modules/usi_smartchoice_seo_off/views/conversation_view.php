<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s sc-sammy-panel"><div class="panel-body">
  <div class="sc-sammy-header"><div><h4 class="no-margin"><?php echo $conversation ? html_escape($conversation['conversation_title']) : 'New Conversation'; ?></h4><p class="text-muted mtop5">Create or review a CRM-connected Sammy AI conversation.</p></div><div class="sc-sammy-toolbar"><a href="<?php echo admin_url('usi_smartchoice_seo/chat_engine'); ?>" class="btn btn-default btn-sm">Back To Conversations</a><?php if ($conversation) { ?><a href="<?php echo admin_url('usi_smartchoice_seo/close_conversation/' . (int)$conversation['id']); ?>" class="btn btn-warning btn-sm">Close</a><?php } ?></div></div>
  <hr class="hr-panel-heading" />
  <div class="row">
    <div class="col-md-4">
      <?php echo form_open(admin_url('usi_smartchoice_seo/conversation/' . (int)($conversation['id'] ?? 0))); ?>
      <div class="form-group"><label>Conversation Title</label><input type="text" name="conversation_title" class="form-control" value="<?php echo html_escape($conversation['conversation_title'] ?? ''); ?>" required></div>
      <div class="form-group"><label>Type</label><select name="conversation_type" class="form-control"><?php foreach (['general'=>'General','command_chat'=>'Command Chat','estimate_chat'=>'Estimate Chat','memory_context'=>'Memory Context','customer_support'=>'Customer Support','project_planning'=>'Project Planning'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($conversation['conversation_type']) && $conversation['conversation_type']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select></div>
      <div class="form-group"><label>Source Area</label><input type="text" name="source_area" class="form-control" value="<?php echo html_escape($conversation['source_area'] ?? 'manual'); ?>"></div>
      <div class="row"><div class="col-md-6"><div class="form-group"><label>Customer ID</label><input type="number" name="customer_id" class="form-control" value="<?php echo (int)($conversation['customer_id'] ?? 0); ?>"></div></div><div class="col-md-6"><div class="form-group"><label>Lead ID</label><input type="number" name="lead_id" class="form-control" value="<?php echo (int)($conversation['lead_id'] ?? 0); ?>"></div></div></div>
      <div class="row"><div class="col-md-6"><div class="form-group"><label>Project ID</label><input type="number" name="project_id" class="form-control" value="<?php echo (int)($conversation['project_id'] ?? 0); ?>"></div></div><div class="col-md-6"><div class="form-group"><label>Estimate ID</label><input type="number" name="estimate_id" class="form-control" value="<?php echo (int)($conversation['estimate_id'] ?? 0); ?>"></div></div></div>
      <div class="form-group"><label>Status</label><select name="status" class="form-control"><?php foreach (['open'=>'Open','closed'=>'Closed','archived'=>'Archived'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($conversation['status']) && $conversation['status']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select></div>
      <div class="form-group"><label>Summary</label><textarea name="summary" class="form-control" rows="4"><?php echo html_escape($conversation['summary'] ?? ''); ?></textarea></div>
      <button type="submit" class="btn btn-success btn-sm">Save Conversation</button>
      <?php echo form_close(); ?>
    </div>
    <div class="col-md-8">
      <?php if ($conversation) { ?>
      <div class="panel_s"><div class="panel-body"><h5>Add Message</h5><?php echo form_open(admin_url('usi_smartchoice_seo/save_conversation_message')); ?><input type="hidden" name="conversation_id" value="<?php echo (int)$conversation['id']; ?>"><div class="row"><div class="col-md-4"><select name="message_role" class="form-control"><option value="user">User</option><option value="assistant">Assistant</option><option value="system">System</option></select></div><div class="col-md-4"><select name="message_source" class="form-control"><option value="typed">Typed</option><option value="voice">Voice</option><option value="memory_query">Memory Query</option><option value="crm_context">CRM Context</option></select></div><div class="col-md-4"><input type="text" name="intent" class="form-control" value="crm_assistant"></div></div><div class="form-group mtop10"><textarea name="message_text" class="form-control" rows="4" placeholder="Type the command, note, response, or context here." required></textarea></div><button type="submit" class="btn btn-success btn-sm">Add Message</button><?php echo form_close(); ?></div></div>
      <h5>Conversation Messages</h5>
      <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table"><thead><tr><th>Role</th><th>Source</th><th>Message</th><th>Created</th></tr></thead><tbody><?php if (empty($messages)) { ?><tr><td colspan="4" class="text-center text-muted">No messages yet.</td></tr><?php } ?><?php foreach ($messages as $message) { ?><tr><td><?php echo html_escape(ucfirst($message['message_role'])); ?></td><td><?php echo html_escape($message['message_source']); ?></td><td><?php echo nl2br(html_escape($message['message_text'])); ?></td><td><?php echo html_escape($message['created_at']); ?></td></tr><?php } ?></tbody></table></div>
      <h5 class="mtop30">Attached Context</h5>
      <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table"><thead><tr><th>Type</th><th>Source</th><th>Title</th><th>Summary</th></tr></thead><tbody><?php if (empty($context)) { ?><tr><td colspan="4" class="text-center text-muted">No context attached.</td></tr><?php } ?><?php foreach ($context as $item) { ?><tr><td><?php echo html_escape($item['context_type']); ?></td><td><?php echo html_escape($item['source_area']); ?> #<?php echo (int)$item['source_id']; ?></td><td><?php echo html_escape($item['context_title']); ?></td><td><?php echo html_escape(mb_substr((string)$item['context_summary'], 0, 220)); ?></td></tr><?php } ?></tbody></table></div>
      <?php } else { ?><div class="alert alert-info">Save the conversation first, then add messages and CRM context.</div><?php } ?>
    </div>
  </div>
</div></div>
</div></div></div></div>
<?php init_tail(); ?></body></html>
