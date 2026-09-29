<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-microphone"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_commands'); ?>">Command Log</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">Estimate Drafts</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/ai_commands'); ?>">Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/ai_commands'); ?>">Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/voice_assistant'); ?>">Reload</a>
</div></div>
<div class="row">
  <div class="col-md-7">
    <div class="sc-voice-box" id="scVoiceConfig" data-preview-url="<?php echo admin_url('usi_smartchoice_seo/voice_preview'); ?>" data-save-url="<?php echo admin_url('usi_smartchoice_seo/voice_save'); ?>" data-execute-url="<?php echo admin_url('usi_smartchoice_seo/voice_execute'); ?>" data-run-estimate-url="<?php echo admin_url('usi_smartchoice_seo/voice_run_estimate'); ?>" data-continuous="<?php echo get_option('usi_smartchoice_ai_voice_continuous') === '0' ? '0' : '1'; ?>" data-language="<?php echo html_escape(get_option('usi_smartchoice_ai_voice_language') ?: 'en-US'); ?>" data-listen-seconds="<?php echo (int)(get_option('usi_smartchoice_ai_listen_seconds') ?: 0); ?>">
      <h4>Voice Command Console</h4>
      <p class="text-muted">Press Turn Microphone On once. The microphone will stay active until you press Turn Microphone Off. Use Run Estimate when the command is for a job estimate.</p>
      <div class="sc-voice-status" id="scVoiceStatus">Microphone Off</div>
      <div class="sc-mobile-button-grid sc-voice-button-grid mtop10">
        <button type="button" class="btn btn-primary btn-sm" id="scVoiceStart"><i class="fa fa-microphone"></i> Turn Microphone On</button>
        <button type="button" class="btn btn-default btn-sm" id="scVoiceStop"><i class="fa fa-stop"></i> Turn Microphone Off</button>
        <button type="button" class="btn btn-default btn-sm" id="scVoicePreview"><i class="fa fa-eye"></i> Preview</button>
        <button type="button" class="btn btn-success btn-sm" id="scVoiceSave"><i class="fa fa-save"></i> Save Command</button>
        <button type="button" class="btn btn-info btn-sm" id="scVoiceRun"><i class="fa fa-play"></i> Run Command</button>
        <button type="button" class="btn btn-warning btn-sm" id="scVoiceRunEstimate"><i class="fa fa-calculator"></i> Run Estimate</button>
      </div>
      <input type="hidden" id="scVoiceCommandId" value="0">
      <div class="form-group mtop15">
        <label>Captured Command</label>
        <textarea id="scVoiceText" class="form-control" rows="6" placeholder="Example: I want to make an estimate for Samuel Cabrera for drywall repair in the living room, about 120 square feet, with paint and finishing."></textarea>
      </div>
      <div class="form-group">
        <label>AI Preview / Result</label>
        <div id="scVoiceResult" class="sc-result-box">No command captured yet.</div>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="sc-card">
      <h4>Commands You Can Test Now</h4>
      <ul>
        <li>Create a lead named Samuel Cabrera with phone 813-555-1212 for kitchen remodeling.</li>
        <li>I would like to make an estimate for Samuel Cabrera for bathroom tile, 80 square feet.</li>
        <li>Open leads.</li>
        <li>Create a task for this customer to call tomorrow.</li>
        <li>Add note to this project that the customer approved the estimate.</li>
      </ul>
      <div class="alert alert-info">Create Lead can create a CRM lead. Run Estimate creates a draft estimate record for estimator review.</div>
    </div>
    <div class="sc-card mtop15 sc-voice-recent-card">
      <h4>Recent Voice Commands</h4>
      <div class="sc-table-fit"><table class="table table-condensed sc-table sc-voice-recent-table"><thead><tr><th class="sc-left sc-command-col">Command</th><th class="sc-left sc-status-col">Status</th><th class="sc-left sc-voice-action-col">Action</th></tr></thead><tbody>
      <?php foreach (array_slice($recent_commands, 0, 5) as $command) { ?>
      <tr>
        <td class="sc-left sc-command-text"><?php echo html_escape($command['command_text']); ?></td>
        <td class="sc-left sc-status-col"><?php echo html_escape($command['action_status']); ?></td>
        <td class="sc-left sc-voice-action-col"><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_ai_command/' . (int)$command['id']); ?>">View</a> <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/ai_command/' . (int)$command['id']); ?>">Edit</a></td>
      </tr>
      <?php } ?>
      <?php if (empty($recent_commands)) { ?><tr><td colspan="3" class="text-center text-muted">No voice commands yet.</td></tr><?php } ?>
      </tbody></table></div>
    </div>
  </div>
</div>

</div></div></div></div><?php init_tail(); ?>
