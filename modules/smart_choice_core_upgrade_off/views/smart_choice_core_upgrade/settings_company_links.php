<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="alert alert-info">Use this section to control EIN numbers, company domains, CRM domains, and document links shown on estimates, proposals, invoices, payments, contracts, and credit notes.</div>
<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label>EIN Number</label>
      <input type="text" name="settings[smart_choice_company_ein]" value="<?= html_escape(get_option('smart_choice_company_ein') ?: get_option('company_vat')); ?>" class="form-control smart-choice-ein-input" placeholder="777-77-7777" maxlength="11" inputmode="numeric">
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label>Company EIN Number 2</label>
      <input type="text" name="settings[smart_choice_company_ein_2]" value="<?= html_escape(get_option('smart_choice_company_ein_2')); ?>" class="form-control smart-choice-ein-input" placeholder="777-77-7777" maxlength="11" inputmode="numeric">
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6"><div class="form-group"><label>Company Domain</label><input type="url" name="settings[smart_choice_company_domain]" value="<?= html_escape(get_option('smart_choice_company_domain') ?: 'https://justsmartchoice.com'); ?>" class="form-control"></div></div>
  <div class="col-md-6"><div class="form-group"><label>CRM Domain</label><input type="url" name="settings[smart_choice_crm_domain]" value="<?= html_escape(get_option('smart_choice_crm_domain') ?: site_url()); ?>" class="form-control"></div></div>
</div>
<div class="row">
  <div class="col-md-4"><div class="form-group"><label>Company Website</label><input type="url" name="settings[smart_choice_company_website]" value="<?= html_escape(get_option('smart_choice_company_website') ?: 'https://justsmartchoice.com'); ?>" class="form-control"><div class="checkbox checkbox-primary"><input type="checkbox" id="show_company_website" name="settings[smart_choice_show_company_website_link]" value="1" <?= get_option('smart_choice_show_company_website_link') !== '0' ? 'checked' : ''; ?>><label for="show_company_website">Show on documents</label></div></div></div>
  <div class="col-md-4"><div class="form-group"><label>Company Client CRM Website</label><input type="url" name="settings[smart_choice_client_crm_website]" value="<?= html_escape(get_option('smart_choice_client_crm_website') ?: site_url('clients')); ?>" class="form-control"><div class="checkbox checkbox-primary"><input type="checkbox" id="show_client_crm" name="settings[smart_choice_show_client_crm_link]" value="1" <?= get_option('smart_choice_show_client_crm_link') !== '0' ? 'checked' : ''; ?>><label for="show_client_crm">Show on customer documents</label></div></div></div>
  <div class="col-md-4"><div class="form-group"><label>Company Admin CRM Website</label><input type="url" name="settings[smart_choice_admin_crm_website]" value="<?= html_escape(get_option('smart_choice_admin_crm_website') ?: admin_url()); ?>" class="form-control"><div class="checkbox checkbox-primary"><input type="checkbox" id="show_admin_crm" name="settings[smart_choice_show_admin_crm_link]" value="1" <?= get_option('smart_choice_show_admin_crm_link') === '1' ? 'checked' : ''; ?>><label for="show_admin_crm">Show internally only</label></div></div></div>
</div>
<hr>
<div class="row">
  <div class="col-md-6"><div class="form-group"><label>Document Link Label</label><input type="text" name="settings[smart_choice_document_link_label]" value="<?= html_escape(get_option('smart_choice_document_link_label') ?: 'Open Smart Choice Client Portal'); ?>" class="form-control"></div></div>
  <div class="col-md-6"><div class="form-group"><label>Document Link URL</label><input type="url" name="settings[smart_choice_document_link_url]" value="<?= html_escape(get_option('smart_choice_document_link_url') ?: site_url('clients')); ?>" class="form-control"></div></div>
</div>
<div class="row">
<?php foreach (['invoices'=>'Invoices','estimates'=>'Estimates','proposals'=>'Proposals','payments'=>'Payments','contracts'=>'Contracts','credit_notes'=>'Credit Notes'] as $key=>$label): $opt='smart_choice_show_document_link_on_'.$key; ?>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" id="<?= $opt; ?>" name="settings[<?= $opt; ?>]" value="1" <?= get_option($opt) !== '0' ? 'checked' : ''; ?>><label for="<?= $opt; ?>">Show on <?= $label; ?></label></div></div>
<?php endforeach; ?>
</div>
<script>
(function(){function f(v){var d=(v||'').replace(/\D/g,'').slice(0,9);if(d.length>5)return d.slice(0,3)+'-'+d.slice(3,5)+'-'+d.slice(5);if(d.length>3)return d.slice(0,3)+'-'+d.slice(3);return d;}document.querySelectorAll('.smart-choice-ein-input').forEach(function(i){i.value=f(i.value);i.addEventListener('input',function(){this.value=f(this.value);});});})();
</script>
