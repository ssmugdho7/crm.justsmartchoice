<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
  <div class="panel-body">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
      <div>
        <h4 class="tw-m-0 tw-font-semibold"><i class="fa-solid fa-wand-magic-sparkles tw-mr-2"></i>AI Integration Settings</h4>
        <p class="text-muted mtop10 mbot0">Configure the CRM AI provider and reuse the API key already stored in the database. Saving this page does not generate or replace a key unless you type a new value.</p>
      </div>
    </div>
    <hr class="hr-panel-separator" />
    <?php render_yes_no_option('sc_ai_enabled', 'Enable AI Integration'); ?>
    <div class="row">
      <div class="col-md-6">
        <?= render_select('settings[sc_ai_provider]', [
          ['id'=>'openai','name'=>'OpenAI'],['id'=>'anthropic','name'=>'Anthropic Claude'],['id'=>'gemini','name'=>'Google Gemini'],['id'=>'azure_openai','name'=>'Azure OpenAI'],['id'=>'custom','name'=>'Custom / OpenAI-Compatible']
        ], ['id','name'], 'AI Provider', get_option('sc_ai_provider')); ?>
      </div>
      <div class="col-md-6">
        <?= render_input('settings[sc_ai_model]', 'AI Model', get_option('sc_ai_model')); ?>
      </div>
    </div>
    <?= render_input('settings[sc_ai_api_key]', 'AI API Key', get_option('sc_ai_api_key'), 'password', ['autocomplete'=>'new-password']); ?>
    <p class="text-muted"><i class="fa-solid fa-shield-halved"></i> The saved value is read from the CRM options table. Leave it unchanged to preserve the current key.</p>
    <div class="row">
      <div class="col-md-6"><?= render_input('settings[sc_ai_base_url]', 'AI API Base URL', get_option('sc_ai_base_url')); ?></div>
      <div class="col-md-6"><?= render_input('settings[sc_ai_organization]', 'AI Organization / Project ID', get_option('sc_ai_organization')); ?></div>
    </div>
    <div class="alert alert-info mtop15 mbot0"><strong>Direct link:</strong> <?= admin_url('settings?group=ai'); ?></div>
  </div>
</div>
