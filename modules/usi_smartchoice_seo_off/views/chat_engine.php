<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s sc-sammy-panel"><div class="panel-body">
  <div class="sc-sammy-header">
    <div><h4 class="no-margin">Sammy AI Conversation &amp; Chat Engine</h4><p class="text-muted mtop5">Keep CRM conversations, voice command notes, memory search questions, and AI response history connected to customers, leads, projects, estimates, and Sammy AI records.</p></div>
    <div class="sc-sammy-toolbar">
      <a href="<?php echo admin_url('usi_smartchoice_seo'); ?>" class="btn btn-default btn-sm">Dashboard</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/conversation'); ?>" class="btn btn-success btn-sm">New Conversation</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/chat_engine'); ?>" class="btn btn-default btn-sm">Reload</a>
    </div>
  </div>
  <hr class="hr-panel-heading" />

  <form method="get" action="<?php echo admin_url('usi_smartchoice_seo/chat_engine'); ?>" class="sc-filter-row">
    <select name="status" class="form-control input-sm"><option value="">All Statuses</option><?php foreach (['open'=>'Open','closed'=>'Closed','archived'=>'Archived'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['status']) && $filters['status']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select>
    <select name="source_area" class="form-control input-sm"><option value="">All Sources</option><?php foreach (['manual'=>'Manual','voice_assistant'=>'Voice Assistant','camera_intake'=>'Camera Intake','ai_estimates'=>'AI Estimates','ai_memory_engine'=>'AI Memory Engine','document_intelligence'=>'Document Intelligence','workflow_engine'=>'Workflow Engine'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['source_area']) && $filters['source_area']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select>
    <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" class="form-control input-sm" placeholder="Search title or summary">
    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
  </form>

  <div class="row mtop20">
    <div class="col-md-4">
      <div class="panel_s"><div class="panel-body">
        <h5>Create From Memory Search</h5>
        <?php echo form_open(admin_url('usi_smartchoice_seo/build_conversation_from_memory')); ?>
        <div class="form-group"><label>Question or Search</label><textarea name="memory_query" class="form-control" rows="4" placeholder="Example: Find estimates like a kitchen remodel in Tampa with cabinets and flooring."></textarea></div>
        <button type="submit" class="btn btn-success btn-sm">Build Conversation</button>
        <?php echo form_close(); ?>
      </div></div>
      <div class="panel_s"><div class="panel-body">
        <h5>Recent Messages</h5>
        <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table"><thead><tr><th>Role</th><th>Message</th></tr></thead><tbody>
        <?php if (empty($recent_messages)) { ?><tr><td colspan="2" class="text-center text-muted">No messages found.</td></tr><?php } ?>
        <?php foreach ($recent_messages as $message) { ?><tr><td><?php echo html_escape(ucfirst($message['message_role'])); ?></td><td><?php echo html_escape(mb_substr($message['message_text'], 0, 120)); ?></td></tr><?php } ?>
        </tbody></table></div>
      </div></div>
    </div>
    <div class="col-md-8">
      <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_conversations')); ?>
      <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table">
        <thead><tr><th width="30"><input type="checkbox" onclick="$('.sc-conversation-check').prop('checked', this.checked);"></th><th>Conversation</th><th>Type</th><th>Source</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($conversations)) { ?><tr><td colspan="7" class="text-center text-muted">No conversations found.</td></tr><?php } ?>
        <?php foreach ($conversations as $conversation) { ?>
          <tr>
            <td><input type="checkbox" class="sc-conversation-check" name="ids[]" value="<?php echo (int)$conversation['id']; ?>"></td>
            <td><strong><?php echo html_escape($conversation['conversation_title']); ?></strong><br><span class="text-muted"><?php echo html_escape(mb_substr((string)$conversation['summary'], 0, 160)); ?></span></td>
            <td><?php echo html_escape(ucwords(str_replace('_', ' ', $conversation['conversation_type']))); ?></td>
            <td><?php echo html_escape(ucwords(str_replace('_', ' ', $conversation['source_area']))); ?> #<?php echo (int)$conversation['source_id']; ?></td>
            <td><?php echo html_escape(ucfirst($conversation['status'])); ?></td>
            <td><?php echo html_escape($conversation['updated_at']); ?></td>
            <td>
              <a href="<?php echo admin_url('usi_smartchoice_seo/conversation/' . (int)$conversation['id']); ?>" class="btn btn-default btn-xs">View</a>
              <?php if ($conversation['status'] !== 'closed') { ?><a href="<?php echo admin_url('usi_smartchoice_seo/close_conversation/' . (int)$conversation['id']); ?>" class="btn btn-warning btn-xs">Close</a><?php } else { ?><a href="<?php echo admin_url('usi_smartchoice_seo/reopen_conversation/' . (int)$conversation['id']); ?>" class="btn btn-success btn-xs">Reopen</a><?php } ?>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table></div>
      <button type="submit" class="btn btn-danger btn-sm">Mass Delete</button>
      <?php echo form_close(); ?>
    </div>
  </div>
</div></div>
</div></div></div></div>
<?php init_tail(); ?></body></html>
