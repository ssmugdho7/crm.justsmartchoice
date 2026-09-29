<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s sc-sammy-panel" id="scVoiceOrchestratorConfig" data-csrf-name="<?php echo $this->security->get_csrf_token_name(); ?>" data-csrf-hash="<?php echo $this->security->get_csrf_hash(); ?>" data-start-url="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator_start'); ?>" data-stop-url="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator_stop'); ?>" data-transcript-url="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator_transcript'); ?>" data-execute-url="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator_execute'); ?>"><div class="panel-body">
  <div class="sc-sammy-header">
    <div>
      <h4 class="no-margin">Sammy AI Voice Orchestrator</h4>
      <p class="text-muted mtop5">Continuous voice capture, transcript routing, command intent detection, and CRM action preview. This page keeps the microphone active until you press Stop.</p>
    </div>
    <div class="sc-sammy-toolbar">
      <a href="<?php echo admin_url('usi_smartchoice_seo/voice_assistant'); ?>" class="btn btn-default btn-sm">Voice Assistant</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/chat_engine'); ?>" class="btn btn-default btn-sm">Conversation &amp; Chat</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator'); ?>" class="btn btn-default btn-sm">Reload</a>
    </div>
  </div>
  <hr class="hr-panel-heading" />

  <div class="row">
    <div class="col-md-5">
      <div class="panel_s"><div class="panel-body">
        <h5>Live Microphone Console</h5>
        <div class="alert alert-info mtop10" id="sammy-voice-status">Microphone is off. Press Start Listening to begin.</div>
        <input type="hidden" id="sammy-voice-session-id" value="0">
        <input type="hidden" id="sammy-last-transcript-id" value="0">
        <div class="form-group">
          <label>Session Title</label>
          <input type="text" id="sammy-session-title" class="form-control" value="Voice Session - <?php echo date('m/d/Y h:i A'); ?>">
        </div>
        <div class="form-group">
          <label>Language</label>
          <select id="sammy-language-code" class="form-control">
            <option value="en-US">English - United States</option>
            <option value="es-US">Spanish - United States</option>
            <option value="es-ES">Spanish - General</option>
          </select>
        </div>
        <div class="form-group">
          <label>Recognized Text</label>
          <textarea id="sammy-live-transcript" class="form-control" rows="6" placeholder="Your voice command will appear here automatically."></textarea>
        </div>
        <div class="sc-mobile-action-grid">
          <button type="button" class="btn btn-success btn-sm" id="sammy-start-listening"><i class="fa fa-microphone"></i> Start Listening</button>
          <button type="button" class="btn btn-danger btn-sm" id="sammy-stop-listening"><i class="fa fa-stop"></i> Stop</button>
          <button type="button" class="btn btn-primary btn-sm" id="sammy-save-transcript"><i class="fa fa-save"></i> Save Command</button>
          <button type="button" class="btn btn-warning btn-sm" id="sammy-run-route"><i class="fa fa-play"></i> Run Route</button>
          <button type="button" class="btn btn-default btn-sm" id="sammy-clear-transcript"><i class="fa fa-eraser"></i> Clear</button>
        </div>
        <div class="alert alert-warning mtop15">
          <strong>Use:</strong> say commands like “Create an estimate for Samuel Cabrera”, “Create a lead”, “Search memory for bathroom remodels”, or “Open customer Samuel Cabrera”.
        </div>
      </div></div>
    </div>

    <div class="col-md-7">
      <div class="panel_s"><div class="panel-body">
        <h5>Command Routes</h5>
        <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table">
          <thead><tr><th>Route</th><th>Trigger</th><th>Intent</th><th>Target</th><th>Confirm</th></tr></thead><tbody>
          <?php if (empty($routes)) { ?><tr><td colspan="5" class="text-center text-muted">No routes found. Run Upgrade Database.</td></tr><?php } ?>
          <?php foreach ($routes as $route) { ?><tr>
            <td><?php echo html_escape($route['route_name']); ?></td>
            <td><?php echo html_escape($route['trigger_phrase']); ?></td>
            <td><?php echo html_escape($route['intent_key']); ?></td>
            <td><?php echo html_escape($route['target_action']); ?></td>
            <td><?php echo (int)$route['requires_confirmation'] === 1 ? 'Yes' : 'No'; ?></td>
          </tr><?php } ?>
          </tbody></table></div>
      </div></div>

      <div class="panel_s"><div class="panel-body">
        <h5>Recent Transcripts</h5>
        <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table">
          <thead><tr><th>Command</th><th>Intent</th><th>Status</th><th>Target</th><th>Created</th></tr></thead><tbody>
          <?php if (empty($recent_transcripts)) { ?><tr><td colspan="5" class="text-center text-muted">No transcripts yet.</td></tr><?php } ?>
          <?php foreach ($recent_transcripts as $row) { ?><tr>
            <td><?php echo html_escape(mb_substr((string)$row['transcript_text'], 0, 140)); ?></td>
            <td><?php echo html_escape($row['detected_intent']); ?></td>
            <td><?php echo html_escape($row['route_status']); ?></td>
            <td><?php echo html_escape($row['route_target']); ?></td>
            <td><?php echo html_escape($row['created_at']); ?></td>
          </tr><?php } ?>
          </tbody></table></div>
      </div></div>
    </div>
  </div>

  <form method="get" action="<?php echo admin_url('usi_smartchoice_seo/voice_orchestrator'); ?>" class="sc-filter-row mtop20">
    <select name="listening_status" class="form-control input-sm"><option value="">All Sessions</option><?php foreach (['listening'=>'Listening','stopped'=>'Stopped'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['listening_status']) && $filters['listening_status']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select>
    <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" class="form-control input-sm" placeholder="Search sessions">
    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
  </form>

  <div class="table-responsive mtop15"><table class="table table-bordered table-striped sc-compact-table">
    <thead><tr><th>Session</th><th>Mode</th><th>Language</th><th>Status</th><th>Commands</th><th>Last Command</th><th>Updated</th></tr></thead><tbody>
    <?php if (empty($sessions)) { ?><tr><td colspan="7" class="text-center text-muted">No voice sessions found.</td></tr><?php } ?>
    <?php foreach ($sessions as $session) { ?><tr>
      <td><strong><?php echo html_escape($session['session_title']); ?></strong></td>
      <td><?php echo html_escape(ucwords(str_replace('_', ' ', $session['session_mode']))); ?></td>
      <td><?php echo html_escape($session['language_code']); ?></td>
      <td><?php echo html_escape(ucfirst($session['listening_status'])); ?></td>
      <td><?php echo (int)$session['command_count']; ?></td>
      <td><?php echo html_escape(mb_substr((string)$session['last_command_text'], 0, 140)); ?></td>
      <td><?php echo html_escape($session['updated_at']); ?></td>
    </tr><?php } ?>
    </tbody></table></div>
</div></div>
</div></div></div></div>

<?php init_tail(); ?></body></html>
