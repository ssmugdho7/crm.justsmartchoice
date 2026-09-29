<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4><i class="fa fa-cog"></i> Estimating Hub AI Settings</h4><?php $this->load->view('estimating_hub_ai/partials/nav',['nav'=>$nav]); ?>
<?php echo form_open(admin_url('estimating_hub_ai/settings')); ?><?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="row">
<div class="col-md-6"><div class="scie-card"><h4>AI And Learning</h4>
<label>Use Existing CRM OpenAI Key</label><select name="estimating_hub_ai_use_existing_openai_key" class="form-control"><option value="1" <?php echo get_option('estimating_hub_ai_use_existing_openai_key')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo get_option('estimating_hub_ai_use_existing_openai_key')==='0'?'selected':''; ?>>No</option></select>
<label>OpenAI Key Override</label><input name="estimating_hub_ai_openai_api_key" type="password" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_openai_api_key')); ?>">
<label>Default Margin Percent</label><input name="estimating_hub_ai_default_margin" class="form-control scie-money" value="<?php echo html_escape(get_option('estimating_hub_ai_default_margin')); ?>">
<label>Default Overhead Percent</label><input name="estimating_hub_ai_default_overhead" class="form-control scie-money" value="<?php echo html_escape(get_option('estimating_hub_ai_default_overhead')); ?>">
</div></div>
<div class="col-md-6"><div class="scie-card"><h4>Material Price Collector API</h4>
<label>Enable Collector API</label><select name="estimating_hub_ai_collector_enabled" class="form-control"><option value="1" <?php echo get_option('estimating_hub_ai_collector_enabled')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo get_option('estimating_hub_ai_collector_enabled')==='0'?'selected':''; ?>>No</option></select>
<label>Collector API Token</label><input name="estimating_hub_ai_collector_token" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_collector_token')); ?>">
<p class="text-muted">Python collector sends this token in the X-SCIE-Token header.</p>
<label>Default ZIP Code</label><input name="estimating_hub_ai_collector_default_zip" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_collector_default_zip')); ?>">
<label>Safe Delay Between Requests</label><input name="estimating_hub_ai_collector_rate_limit_seconds" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_collector_rate_limit_seconds')); ?>">
</div></div>
</div>
<div class="row"><div class="col-md-6"><div class="scie-card"><h4>CRM Source Sync</h4>
<label><input type="checkbox" name="estimating_hub_ai_sync_estimates" value="1" <?php echo get_option('estimating_hub_ai_sync_estimates')==='1'?'checked':''; ?>> Read Existing Estimates</label><br>
<label><input type="checkbox" name="estimating_hub_ai_sync_invoices" value="1" <?php echo get_option('estimating_hub_ai_sync_invoices')==='1'?'checked':''; ?>> Read Existing Invoices</label><br>
<label><input type="checkbox" name="estimating_hub_ai_sync_proposals" value="1" <?php echo get_option('estimating_hub_ai_sync_proposals')==='1'?'checked':''; ?>> Read Existing Proposals</label>
</div></div>
<div class="col-md-6"><div class="scie-card"><h4>Print And Company Details</h4>
<label>Company Phone For Print</label><input name="estimating_hub_ai_company_phone" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_company_phone')); ?>">
<label>Admin Email For Print</label><input name="estimating_hub_ai_admin_email" class="form-control" value="<?php echo html_escape(get_option('estimating_hub_ai_admin_email')); ?>">
<label>Home Depot Public Collection</label><select name="estimating_hub_ai_home_depot_public_enabled" class="form-control"><option value="1" <?php echo get_option('estimating_hub_ai_home_depot_public_enabled')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo get_option('estimating_hub_ai_home_depot_public_enabled')==='0'?'selected':''; ?>>Disabled</option></select>
<label>Homewyse Public Cost Page Collection</label><select name="estimating_hub_ai_homewyse_enabled" class="form-control"><option value="1" <?php echo get_option('estimating_hub_ai_homewyse_enabled')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo get_option('estimating_hub_ai_homewyse_enabled')==='0'?'selected':''; ?>>Disabled</option></select>
</div></div></div>
<br><button class="btn btn-success">Save Settings</button><?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?>