<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$solarAllowedTabs = ['general', 'production', 'panels', 'financial', 'google', 'enphase', 'portal'];
$solarActiveTab = preg_replace('/[^a-z_]/', '', (string) $this->input->get('solar_tab', true));
if (!in_array($solarActiveTab, $solarAllowedTabs, true)) {
    $solarHash = '';
    $solarActiveTab = 'general';
}
?>
<div class="solar-settings-wrap">
  <div class="solar-settings-banner">
    <i class="fa-solid fa-solar-panel"></i>
    <div>
      <strong><?php echo _l('solar_pro_settings'); ?></strong>
      <span><?php echo _l('solar_pro_settings_subtitle'); ?></span>
    </div>
  </div>

  <div class="alert alert-info mtop15">
    <i class="fa-solid fa-circle-info"></i>
    <?php echo _l('solar_pro_settings_native_help'); ?>
  </div>
  <div class="solar-settings-portal-link"><a href="<?php echo site_url('solar_pro/estimate'); ?>" target="_blank" class="btn btn-info btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo _l('solar_pro_open_public_portal'); ?></a></div>

  <ul class="nav nav-tabs solar-settings-tabs" role="tablist">
    <li class="<?php echo $solarActiveTab === 'general' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=general#solar_general'); ?>" data-solar-tab="general"><i class="fa-solid fa-sliders"></i> <?php echo _l('solar_pro_general'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'production' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=production#solar_production'); ?>" data-solar-tab="production"><i class="fa-solid fa-sun"></i> <?php echo _l('solar_pro_production_shading'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'panels' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=panels#solar_panels'); ?>" data-solar-tab="panels"><i class="fa-solid fa-solar-panel"></i> <?php echo _l('solar_pro_panels_pricing'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'financial' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=financial#solar_financial'); ?>" data-solar-tab="financial"><i class="fa-solid fa-chart-line"></i> <?php echo _l('solar_pro_financial'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'google' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=google#solar_google'); ?>" data-solar-tab="google"><i class="fa-brands fa-google"></i> <?php echo _l('solar_pro_google'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'enphase' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=enphase#solar_enphase'); ?>" data-solar-tab="enphase"><i class="fa-solid fa-plug"></i> <?php echo _l('solar_pro_enphase'); ?></a></li>
    <li class="<?php echo $solarActiveTab === 'portal' ? 'active' : ''; ?>"><a href="<?php echo admin_url('settings?group=solar_pro&solar_tab=portal#solar_portal'); ?>" data-solar-tab="portal"><i class="fa-solid fa-globe"></i> <?php echo _l('solar_pro_portal'); ?></a></li>
  </ul>

  <input type="hidden" name="solar_active_tab" id="solar_active_tab" value="<?php echo html_escape($solarActiveTab); ?>">
  <div class="tab-content mtop20">
    <div class="tab-pane <?php echo $solarActiveTab === 'general' ? 'active in' : ''; ?>" id="solar_general">
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_default_state]', 'solar_pro_default_state', get_option('solar_pro_default_state')); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_default_energy_rate]', 'solar_pro_default_energy_rate', get_option('solar_pro_default_energy_rate'), 'number', ['step'=>'0.000001','min'=>'0']); ?></div>
      </div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_default_customer_charge]', 'solar_pro_default_customer_charge', get_option('solar_pro_default_customer_charge'), 'number', ['step'=>'0.01','min'=>'0']); ?></div>
        <div class="col-md-6">
          <label><?php echo _l('solar_pro_manage_utility_rates'); ?></label><br>
          <a href="<?php echo admin_url('solar_pro/utilities'); ?>" class="btn btn-default btn-sm"><i class="fa-solid fa-bolt"></i> <?php echo _l('solar_pro_open_utilities'); ?></a>
        </div>
      </div>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'production' ? 'active in' : ''; ?>" id="solar_production">
      <div class="alert alert-warning"><?php echo _l('solar_pro_production_shading_help'); ?></div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_target_offset_pct]', 'solar_pro_target_offset_pct', get_option('solar_pro_target_offset_pct'), 'number', ['step'=>'0.01','min'=>'1','max'=>'200']); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_system_loss_pct]', 'solar_pro_system_loss_pct', get_option('solar_pro_system_loss_pct'), 'number', ['step'=>'0.01','min'=>'0','max'=>'50']); ?></div>
      </div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_shading_loss_pct]', 'solar_pro_shading_loss_pct', get_option('solar_pro_shading_loss_pct'), 'number', ['step'=>'0.01','min'=>'0','max'=>'50']); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_production_multiplier_pct]', 'solar_pro_production_multiplier_pct', get_option('solar_pro_production_multiplier_pct'), 'number', ['step'=>'0.01','min'=>'10','max'=>'200']); ?></div>
      </div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_daily_kwh_400]', 'solar_pro_daily_kwh_400', get_option('solar_pro_daily_kwh_400'), 'number', ['step'=>'0.01','min'=>'0.01']); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_daily_kwh_440]', 'solar_pro_daily_kwh_440', get_option('solar_pro_daily_kwh_440'), 'number', ['step'=>'0.01','min'=>'0.01']); ?></div>
      </div>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'panels' ? 'active in' : ''; ?>" id="solar_panels">
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_default_panel_watts]', 'solar_pro_default_panel_watts', get_option('solar_pro_default_panel_watts'), 'number', ['min'=>'1']); ?></div>
        <div class="col-md-6">
          <div class="form-group">
            <label><?php echo _l('solar_pro_pricing_mode'); ?></label>
            <select name="settings[solar_pro_pricing_mode]" class="form-control selectpicker">
              <option value="panel" <?php echo get_option('solar_pro_pricing_mode') === 'panel' ? 'selected' : ''; ?>><?php echo _l('solar_pro_per_panel'); ?></option>
              <option value="watt" <?php echo get_option('solar_pro_pricing_mode') === 'watt' ? 'selected' : ''; ?>><?php echo _l('solar_pro_per_watt'); ?></option>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_price_per_panel]', 'solar_pro_price_per_panel', get_option('solar_pro_price_per_panel'), 'number', ['step'=>'0.01','min'=>'0']); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_price_per_watt]', 'solar_pro_price_per_watt', get_option('solar_pro_price_per_watt'), 'number', ['step'=>'0.0001','min'=>'0']); ?></div>
      </div>
      <a href="<?php echo admin_url('solar_pro/equipment'); ?>" class="btn btn-default btn-sm"><i class="fa-solid fa-solar-panel"></i> <?php echo _l('solar_pro_manage_panels_equipment'); ?></a>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'financial' ? 'active in' : ''; ?>" id="solar_financial">
      <div class="row">
        <div class="col-md-4"><?php echo render_input('settings[solar_pro_utility_escalation_pct]', 'solar_pro_utility_escalation_pct', get_option('solar_pro_utility_escalation_pct'), 'number', ['step'=>'0.01']); ?></div>
        <div class="col-md-4"><?php echo render_input('settings[solar_pro_solar_degradation_pct]', 'solar_pro_solar_degradation_pct', get_option('solar_pro_solar_degradation_pct'), 'number', ['step'=>'0.01','min'=>'0','max'=>'10']); ?></div>
        <div class="col-md-4"><?php echo render_input('settings[solar_pro_financial_years]', 'solar_pro_financial_years', get_option('solar_pro_financial_years'), 'number', ['min'=>'1','max'=>'40']); ?></div>
      </div>
      <hr><h5><?php echo _l('solar_pro_customer_financing'); ?></h5><div class="row"><div class="col-md-4"><?php echo render_input('settings[solar_pro_finance_apr_pct]','solar_pro_finance_apr_pct',get_option('solar_pro_finance_apr_pct'),'number',['step'=>'0.01','min'=>'0']); ?></div><div class="col-md-4"><?php echo render_input('settings[solar_pro_finance_term_years]','solar_pro_finance_term_years',get_option('solar_pro_finance_term_years'),'number',['min'=>'1','max'=>'40']); ?></div><div class="col-md-4"><?php echo render_input('settings[solar_pro_finance_down_payment]','solar_pro_finance_down_payment',get_option('solar_pro_finance_down_payment'),'number',['step'=>'0.01','min'=>'0']); ?></div></div>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'google' ? 'active in' : ''; ?>" id="solar_google">
      <div class="alert alert-info"><?php echo _l('solar_pro_google_settings_help'); ?></div>
      <?php echo render_input('settings[solar_pro_google_api_key]', 'solar_pro_google_api_key', '', 'password', ['autocomplete'=>'new-password','placeholder'=>_l('solar_pro_secret_keep_existing')]); ?>
      <?php echo render_input('settings[solar_pro_google_geocoding_api_key]', 'solar_pro_google_geocoding_api_key', '', 'password', ['autocomplete'=>'new-password','placeholder'=>_l('solar_pro_secret_keep_existing')]); ?>
      <div class="form-group"><label><?php echo _l('solar_pro_google_required_quality'); ?></label><select name="settings[solar_pro_google_required_quality]" class="form-control selectpicker"><option value="BASE" <?php echo get_option('solar_pro_google_required_quality')==='BASE'?'selected':''; ?>>BASE</option><option value="MEDIUM" <?php echo get_option('solar_pro_google_required_quality')==='MEDIUM'?'selected':''; ?>>MEDIUM</option><option value="HIGH" <?php echo get_option('solar_pro_google_required_quality')==='HIGH'?'selected':''; ?>>HIGH</option></select></div>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'enphase' ? 'active in' : ''; ?>" id="solar_enphase">
      <div class="alert alert-info"><?php echo _l('solar_pro_enphase_settings_help'); ?></div>
      <?php echo render_input('settings[solar_pro_enphase_api_key]', 'solar_pro_enphase_api_key', '', 'password', ['autocomplete'=>'new-password','placeholder'=>_l('solar_pro_secret_keep_existing')]); ?>
      <?php echo render_input('settings[solar_pro_enphase_client_id]', 'solar_pro_enphase_client_id', get_option('solar_pro_enphase_client_id')); ?>
      <?php echo render_input('settings[solar_pro_enphase_client_secret]', 'solar_pro_enphase_client_secret', '', 'password', ['autocomplete'=>'new-password','placeholder'=>_l('solar_pro_secret_keep_existing')]); ?>
      <?php echo render_input('settings[solar_pro_enphase_base_url]', 'solar_pro_enphase_base_url', get_option('solar_pro_enphase_base_url')); ?>
      <?php echo render_input('settings[solar_pro_enphase_organization]', 'solar_pro_enphase_organization', get_option('solar_pro_enphase_organization')); ?>
      <?php echo render_input('settings[solar_pro_enphase_model]', 'solar_pro_enphase_model', get_option('solar_pro_enphase_model')); ?>
    </div>

    <div class="tab-pane <?php echo $solarActiveTab === 'portal' ? 'active in' : ''; ?>" id="solar_portal">
      <input type="hidden" name="settings[solar_pro_public_calculator_enabled]" value="0">
      <div class="checkbox checkbox-primary"><input type="checkbox" name="settings[solar_pro_public_calculator_enabled]" id="solar_pro_public_calculator_enabled" value="1" <?php echo get_option('solar_pro_public_calculator_enabled')==='1'?'checked':''; ?>><label for="solar_pro_public_calculator_enabled"><?php echo _l('solar_pro_public_calculator_enabled'); ?></label></div>
      <input type="hidden" name="settings[solar_pro_public_create_lead]" value="0">
      <div class="checkbox checkbox-primary"><input type="checkbox" name="settings[solar_pro_public_create_lead]" id="solar_pro_public_create_lead" value="1" <?php echo get_option('solar_pro_public_create_lead')==='1'?'checked':''; ?>><label for="solar_pro_public_create_lead"><?php echo _l('solar_pro_public_create_lead'); ?></label></div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_brand_primary]', 'solar_pro_brand_primary', get_option('solar_pro_brand_primary')); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_brand_secondary]', 'solar_pro_brand_secondary', get_option('solar_pro_brand_secondary')); ?></div>
      </div>
      <div class="row">
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_brand_success]', 'solar_pro_brand_success', get_option('solar_pro_brand_success')); ?></div>
        <div class="col-md-6"><?php echo render_input('settings[solar_pro_brand_dark]', 'solar_pro_brand_dark', get_option('solar_pro_brand_dark')); ?></div>
      </div>
      <?php echo render_input('settings[solar_pro_appointment_url]', 'solar_pro_appointment_url', get_option('solar_pro_appointment_url'), 'url', ['placeholder'=>'https://...']); ?>
      <p class="text-muted"><?php echo _l('solar_pro_appointment_url_help'); ?></p>
      <p><strong><?php echo _l('solar_pro_public_url'); ?>:</strong> <code><?php echo site_url('solar_pro/estimate'); ?></code></p>
    </div>
  </div>
<div class="panel_s solar-card"><div class="panel-body"><h4><?php echo _l('solar_pro_environment_assumptions'); ?></h4><?php echo render_input('settings[solar_pro_co2_kg_per_kwh]','solar_pro_co2_kg_per_kwh',solar_pro_setting('solar_pro_co2_kg_per_kwh','0.386'),'number',['step'=>'0.001']); ?><?php echo render_input('settings[solar_pro_tree_kg_co2_year]','solar_pro_tree_kg_co2_year',solar_pro_setting('solar_pro_tree_kg_co2_year','22'),'number',['step'=>'0.1']); ?><?php echo render_input('settings[solar_pro_car_co2_tons_year]','solar_pro_car_co2_tons_year',solar_pro_setting('solar_pro_car_co2_tons_year','4.6'),'number',['step'=>'0.1']); ?></div></div></div>

<script>
(function(){
  'use strict';
  function activateSolarTab(name, updateUrl) {
    var wrap = document.querySelector('.solar-settings-wrap');
    if (!wrap) return;
    var allowed = ['general','production','panels','financial','google','enphase','portal'];
    if (allowed.indexOf(name) === -1) name = 'general';
    wrap.querySelectorAll('.solar-settings-tabs li').forEach(function(li){li.classList.remove('active');});
    wrap.querySelectorAll('.solar-settings-tabs a[data-solar-tab]').forEach(function(a){
      if (a.getAttribute('data-solar-tab') === name) a.parentElement.classList.add('active');
    });
    wrap.querySelectorAll('.tab-content > .tab-pane').forEach(function(p){p.classList.remove('active','in');p.style.display='none';});
    var pane = document.getElementById('solar_' + name);
    if (pane) {pane.classList.add('active','in');pane.style.display='block';}
    var hidden = document.getElementById('solar_active_tab');
    if (hidden) hidden.value = name;
    if (updateUrl && window.history && window.history.replaceState) {
      var u = new URL(window.location.href);
      u.searchParams.set('group','solar_pro');
      u.searchParams.set('solar_tab',name);
      u.hash = 'solar_' + name;
      window.history.replaceState({}, '', u.toString());
    }
  }
  document.addEventListener('click', function(e){
    var a = e.target.closest('.solar-settings-tabs a[data-solar-tab]');
    if (!a) return;
    e.preventDefault();
    activateSolarTab(a.getAttribute('data-solar-tab'), true);
  });
  var requested = new URL(window.location.href).searchParams.get('solar_tab');
  if (!requested && window.location.hash.indexOf('#solar_') === 0) requested = window.location.hash.replace('#solar_','');
  activateSolarTab(requested || '<?php echo addslashes($solarActiveTab); ?>', false);
})();
</script>
