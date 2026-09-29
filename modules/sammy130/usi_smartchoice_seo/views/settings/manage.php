<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<?php echo form_open(current_url()); ?><div class="panel_s"><div class="panel-body"><h4><?php echo _l('usi_smartchoice_seo_settings'); ?></h4>
<div class="row">
  <div class="col-md-6">
    <h5>General Settings</h5>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="usi_smartchoice_seo_enabled" id="usi_smartchoice_seo_enabled" value="1" <?php echo get_option('usi_smartchoice_seo_enabled') === '1' ? 'checked' : ''; ?>><label for="usi_smartchoice_seo_enabled"><?php echo _l('usi_smartchoice_seo_enabled'); ?></label></div>
    <?php echo render_input('usi_smartchoice_seo_primary_domain','usi_smartchoice_seo_primary_domain',get_option('usi_smartchoice_seo_primary_domain') ?: 'https://justsmartchoice.com', 'text', ['readonly'=>'readonly']); ?>
    <?php echo render_input('usi_smartchoice_seo_brand_name','usi_smartchoice_seo_brand_name',get_option('usi_smartchoice_seo_brand_name') ?: 'Smart Choice Contractors USA'); ?>
    <?php echo render_select('usi_smartchoice_ai_default_estimate_status', [['id'=>'draft','name'=>'Draft'],['id'=>'calculated','name'=>'Calculated'],['id'=>'review','name'=>'Review'],['id'=>'approved','name'=>'Approved'],['id'=>'converted','name'=>'Converted']], ['id','name'], 'Default Estimate Status', get_option('usi_smartchoice_ai_default_estimate_status') ?: 'draft'); ?>
    <?php echo render_select('usi_smartchoice_ai_command_mode', [['id'=>'review_first','name'=>'Review First'],['id'=>'save_only','name'=>'Save Only'],['id'=>'direct_allowed','name'=>'Direct When Allowed']], ['id','name'], 'Voice Command Mode', get_option('usi_smartchoice_ai_command_mode') ?: 'review_first'); ?>
  </div>
  <div class="col-md-6">
    <h5>AI API Key</h5>
    <?php echo render_select('usi_smartchoice_ai_api_provider', [['id'=>'openai','name'=>'OpenAI'],['id'=>'manual','name'=>'Manual / Disabled']], ['id','name'], 'AI Provider', get_option('usi_smartchoice_ai_api_provider') ?: 'openai'); ?>
    <div class="form-group">
      <label for="usi_smartchoice_ai_api_key">API Key</label>
      <div class="input-group">
        <input type="password" id="usi_smartchoice_ai_api_key" name="usi_smartchoice_ai_api_key" class="form-control" value="<?php echo html_escape(get_option('usi_smartchoice_ai_api_key')); ?>" autocomplete="off">
        <span class="input-group-btn"><button type="button" class="btn btn-default" id="scToggleApiKey">Show</button></span>
      </div>
      <p class="text-muted">Use this field to enter, edit, view, or delete the API key. To delete it, clear the field and save.</p>
    </div>
    <div class="sc-mobile-button-grid"><button type="button" class="btn btn-info btn-sm" id="scCheckApiKey"><i class="fa fa-plug"></i> Check API Key</button><button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Settings</button></div>
    <div id="scApiKeyResult" class="sc-result-box mtop10">API key status not checked yet.</div>
  </div>
</div>
<hr>
<div class="row">
  <div class="col-md-6">
    <h5>Voice Settings</h5>
    <?php echo render_select('usi_smartchoice_ai_voice_continuous', [['id'=>'1','name'=>'Stay On Until I Turn It Off'],['id'=>'0','name'=>'Stop After Each Command']], ['id','name'], 'Microphone Behavior', get_option('usi_smartchoice_ai_voice_continuous') === '0' ? '0' : '1'); ?>
    <?php echo render_select('usi_smartchoice_ai_voice_language', [['id'=>'en-US','name'=>'English US'],['id'=>'es-US','name'=>'Spanish US'],['id'=>'es-ES','name'=>'Spanish']], ['id','name'], 'Voice Language', get_option('usi_smartchoice_ai_voice_language') ?: 'en-US'); ?>
    <?php echo render_input('usi_smartchoice_ai_listen_seconds','Listen Seconds Before Auto Stop',get_option('usi_smartchoice_ai_listen_seconds') ?: '0','number',['min'=>'0','step'=>'1']); ?>
    <p class="text-muted">Use 0 to keep listening until you press Turn Microphone Off.</p>
  </div>
  <div class="col-md-6">
    <h5>Mobile Layout</h5>
    <?php echo render_select('usi_smartchoice_ai_mobile_grid_columns', [['id'=>'3','name'=>'3 Columns'],['id'=>'4','name'=>'4 Columns'],['id'=>'5','name'=>'5 Columns']], ['id','name'], 'Mobile Button Columns', get_option('usi_smartchoice_ai_mobile_grid_columns') ?: '4'); ?>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="usi_smartchoice_ai_voice_enabled" id="usi_smartchoice_ai_voice_enabled" value="1" <?php echo get_option('usi_smartchoice_ai_voice_enabled') !== '0' ? 'checked' : ''; ?>><label for="usi_smartchoice_ai_voice_enabled">Enable Voice Assistant</label></div>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="usi_smartchoice_ai_camera_enabled" id="usi_smartchoice_ai_camera_enabled" value="1" <?php echo get_option('usi_smartchoice_ai_camera_enabled') !== '0' ? 'checked' : ''; ?>><label for="usi_smartchoice_ai_camera_enabled">Enable Camera Intake</label></div>
    <input type="hidden" name="usi_smartchoice_ai_enabled" value="1">
  </div>
</div>
<div class="text-right mtop15"><button class="btn btn-sm btn-primary" type="submit"><?php echo _l('save'); ?></button></div>
</div></div><?php echo form_close(); ?>
<script>
window.usiSmartChoiceApiKeyCheckUrl = '<?php echo admin_url('usi_smartchoice_seo/check_api_key'); ?>';
</script>
</div></div></div></div>
<?php init_tail(); ?></body></html>
