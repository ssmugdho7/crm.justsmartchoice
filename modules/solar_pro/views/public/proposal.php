<?php
defined('BASEPATH') or exit('No direct script access allowed');
$template = $proposal['template'] ?? [];
$brand = $template['primary_color'] ?? solar_pro_setting('solar_pro_brand_primary', '#0E6F5B');
$secondary = $template['secondary_color'] ?? solar_pro_setting('solar_pro_brand_secondary', '#3598DB');
$accent = $template['accent_color'] ?? '#F28C28';
$company = get_option('companyname');
$panelWarranty = (int)($analysis['panel_equipment']['warranty_years'] ?? 0);
$yearsToShow = [1,2,3,4,5,10,20,25];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo html_escape($proposal['title']); ?></title>
<link rel="stylesheet" href="<?php echo module_dir_url('solar_pro','assets/css/solar-pro-public.css?v='.SOLAR_PRO_VERSION); ?>">
<style>:root{--sp-primary:<?php echo html_escape($brand); ?>;--sp-secondary:<?php echo html_escape($secondary); ?>;--sp-accent:<?php echo html_escape($accent); ?>}</style>
</head>
<body class="sp-magazine-body">
<main class="sp-magazine">
<?php $spError=(string)($this->input->get('error',true)??''); if($spError!==''): ?><div style="margin:20px 20px 0;padding:14px 18px;border-radius:10px;background:#fff0f0;border:1px solid #e6a4a4;color:#9d2424;font-weight:700"><?php echo $spError==='email'?_l('solar_pro_invalid_email'):($spError==='phone'?_l('solar_pro_invalid_phone'):_l('solar_pro_required_fields')); ?></div><?php endif; ?>
<section class="sp-mag-cover">
  <div class="sp-solar-grid"></div>
  <div class="sp-mag-logo"><?php get_dark_company_logo(); ?></div>
  <div class="sp-cover-copy">
    <span><?php echo html_escape($company); ?></span>
    <h1><?php echo html_escape($template['hero_title'] ?? _l('solar_pro_power_your_future')); ?></h1>
    <p><?php echo html_escape(trim($analysis['first_name'].' '.$analysis['last_name'])); ?> · <?php echo html_escape($analysis['address']); ?></p>
    <div class="sp-cover-kpis">
      <b><?php echo number_format($analysis['system_kw'],2); ?> kW</b>
      <b><?php echo (int)$analysis['panel_count']; ?> <?php echo _l('solar_pro_panels'); ?></b>
      <b><?php echo number_format($analysis['solar_offset_pct'],0); ?>% <?php echo _l('solar_pro_offset'); ?></b>
    </div>
    <?php echo $template['intro_html'] ?? ''; ?>
  </div>
</section>

<section class="sp-mag-section sp-financial-section">
  <div class="sp-mag-title"><span>01</span><h2><?php echo _l('solar_pro_financial_future'); ?></h2></div>
  <p class="sp-section-lead"><?php echo _l('solar_pro_financial_story'); ?></p>
  <div class="sp-benefit-grid">
    <article><small><?php echo _l('solar_pro_est_monthly_payment'); ?></small><strong><?php echo app_format_money($finance['monthly_payment'],get_base_currency()); ?></strong></article>
    <article><small><?php echo _l('solar_pro_monthly_savings'); ?></small><strong><?php echo app_format_money($finance['monthly_savings'],get_base_currency()); ?></strong></article>
    <article><small><?php echo _l('solar_pro_annual_savings'); ?></small><strong><?php echo app_format_money($finance['annual_savings'],get_base_currency()); ?></strong></article>
    <article><small>25 <?php echo _l('solar_pro_years'); ?></small><strong><?php echo app_format_money($finance['projections'][25],get_base_currency()); ?></strong></article>
  </div>
  <div class="sp-finance-chart sp-finance-chart-premium">
    <div class="sp-chart-heading"><div><h3><?php echo _l('solar_pro_25_year_money_back'); ?></h3><p><?php echo sprintf(_l('solar_pro_projection_assumption'), number_format($finance['utility_escalation_pct'],1), number_format((float)solar_pro_setting('solar_pro_solar_degradation_pct',.5),1)); ?></p></div></div>
    <div class="sp-year-grid">
      <?php foreach($yearsToShow as $idx=>$y): $row=$finance['yearly'][$y] ?? null; if(!$row) continue; ?>
      <article class="sp-year-card sp-year-color-<?php echo ($idx%4)+1; ?>">
        <span><?php echo _l('solar_pro_year'); ?> <?php echo $y; ?></span>
        <strong><?php echo app_format_money($row['annual_savings'],get_base_currency()); ?></strong>
        <small><?php echo _l('solar_pro_projected_annual_savings'); ?></small>
        <b><?php echo app_format_money($row['cumulative_savings'],get_base_currency()); ?></b>
        <small><?php echo _l('solar_pro_projected_cumulative_savings'); ?></small>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sp-mag-section sp-planet">
  <div class="sp-mag-title"><span>02</span><h2><?php echo _l('solar_pro_planet_impact'); ?></h2></div>
  <p class="sp-section-lead"><?php echo _l('solar_pro_planet_story'); ?></p>
  <div class="sp-impact-visuals sp-photo-metrics">
    <article><img src="<?php echo solar_pro_proposal_asset_url('co2-impact.png'); ?>" alt="CO2"><div class="sp-photo-caption"><b><?php echo number_format($environment['co2_tons'],1); ?></b><span><?php echo _l('solar_pro_metric_tons_co2'); ?></span></div></article>
    <article><img src="<?php echo solar_pro_proposal_asset_url('trees-impact.png'); ?>" alt="Trees"><div class="sp-photo-caption"><b><?php echo number_format($environment['trees'],0); ?></b><span><?php echo _l('solar_pro_tree_equivalent'); ?></span></div></article>
    <article><img src="<?php echo solar_pro_proposal_asset_url('clean-energy.png'); ?>" alt="Clean energy"><div class="sp-photo-caption"><b><?php echo number_format($analysis['annual_production_kwh']); ?></b><span>kWh <?php echo _l('solar_pro_clean_energy_year'); ?></span></div></article>
    <article><img src="<?php echo solar_pro_proposal_asset_url('cars-impact.png'); ?>" alt="Vehicle CO2"><div class="sp-photo-caption"><b><?php echo number_format($environment['cars'] ?? 0,1); ?></b><span><?php echo _l('solar_pro_car_equivalent'); ?></span></div></article>
  </div>
  <p class="sp-disclaimer"><?php echo _l('solar_pro_environment_disclaimer'); ?></p>
</section>

<section class="sp-mag-section sp-system-section">
  <div class="sp-mag-title"><span>03</span><h2><?php echo _l('solar_pro_your_system'); ?></h2></div>
  <p class="sp-section-lead"><?php echo _l('solar_pro_system_story'); ?></p>
  <div class="sp-system-feature sp-system-photo-feature">
    <img src="<?php echo solar_pro_proposal_asset_url('solar-energy-flow.png'); ?>" alt="Solar panels receiving sunlight">
    <div class="sp-system-spec">
      <div><span><?php echo _l('solar_pro_system_size'); ?></span><b><?php echo number_format($analysis['system_kw'],3); ?> kW</b></div>
      <div><span><?php echo _l('solar_pro_annual_generation'); ?></span><b><?php echo number_format($analysis['annual_production_kwh']); ?> kWh</b></div>
      <div><span><?php echo _l('solar_pro_system_price'); ?></span><b><?php echo app_format_money($analysis['system_price'],get_base_currency()); ?></b></div>
      <div><span>APR / <?php echo _l('solar_pro_term'); ?></span><b><?php echo number_format($finance['apr_pct'],2); ?>% / <?php echo $finance['term_years']; ?>y</b></div>
    </div>
  </div>
  <div class="sp-product-grid">
    <article class="sp-product-card"><img src="<?php echo solar_pro_proposal_asset_url('enphase-iq8.png'); ?>" alt="Enphase IQ8"><div><h3><?php echo _l('solar_pro_iq8_title'); ?></h3><p><?php echo _l('solar_pro_iq8_description'); ?></p></div></article>
    <article class="sp-product-card"><img src="<?php echo solar_pro_proposal_asset_url('enphase-gateway.png'); ?>" alt="Enphase IQ Gateway"><div><h3><?php echo _l('solar_pro_gateway_title'); ?></h3><p><?php echo _l('solar_pro_gateway_description'); ?></p></div></article>
  </div>
  <div class="sp-warranty-panel">
    <h3><i class="fa fa-shield"></i> <?php echo _l('solar_pro_warranty_title'); ?></h3>
    <ul>
      <li><?php echo _l('solar_pro_iq8_warranty'); ?></li>
      <li><?php echo _l('solar_pro_gateway_warranty'); ?></li>
      <?php if($panelWarranty>0): ?><li><?php echo sprintf(_l('solar_pro_panel_warranty_dynamic'),$panelWarranty); ?></li><?php endif; ?>
    </ul>
    <p><?php echo _l('solar_pro_enphase_product_note'); ?></p>
  </div>
</section>

<section class="sp-mag-section sp-install-section">
  <div class="sp-mag-title"><span>04</span><h2><?php echo _l('solar_pro_installation_flow'); ?></h2></div>
  <p class="sp-section-lead"><?php echo _l('solar_pro_installation_story'); ?></p>
  <img class="sp-install-flow" src="<?php echo solar_pro_proposal_asset_url('installation-flow.png'); ?>" alt="<?php echo html_escape(_l('solar_pro_installation_flow')); ?>">
  <div class="sp-flow-steps">
    <?php foreach([
      ['fa-sun', 'solar_pro_flow_sunlight'],
      ['fa-solar-panel', 'solar_pro_flow_panels'],
      ['fa-bolt', 'solar_pro_flow_microinverters'],
      ['fa-toggle-on', 'solar_pro_flow_disconnect'],
      ['fa-chart-line', 'solar_pro_flow_gateway'],
      ['fa-plug', 'solar_pro_flow_main_panel'],
      ['fa-house', 'solar_pro_flow_home'],
    ] as $flow): ?>
      <div class="sp-flow-step"><i class="fa-solid <?php echo $flow[0]; ?>"></i><span><?php echo _l($flow[1]); ?></span></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="discussion" class="sp-mag-section">
  <div class="sp-mag-title"><span>05</span><h2><?php echo _l('solar_pro_discussion'); ?></h2></div>
  <?php foreach($proposal['comments'] as $c): ?><div class="sp-comment"><b><?php echo html_escape($c['staff_id']?get_staff_full_name($c['staff_id']):$c['contact_name']); ?></b><p><?php echo nl2br(html_escape($c['message'])); ?></p></div><?php endforeach; ?>
  <?php echo form_open(site_url('solar_pro/proposal/'.$proposal['public_token'])); ?><input type="hidden" name="action" value="comment"><div class="sp-form-row"><label><span><?php echo _l('solar_pro_full_name'); ?></span><input name="name"></label><label><span><?php echo _l('solar_pro_email_address'); ?></span><input name="email" type="email"></label></div><label><span><?php echo _l('solar_pro_add_comment'); ?></span><textarea name="message" rows="4"></textarea></label><button class="sp-action-primary"><?php echo _l('solar_pro_add_comment'); ?></button><?php echo form_close(); ?>
</section>

<?php if(!empty($template['closing_html'])):?><section class="sp-mag-section sp-closing"><?php echo $template['closing_html']; ?></section><?php endif; ?>

<section class="sp-mag-actions sp-decision-zone">
  <div class="sp-decision-top"><a class="sp-btn sp-pdf-btn" href="<?php echo site_url('solar_pro/proposal/'.$proposal['public_token'].'/pdf'); ?>"> <span>PDF</span> <?php echo _l('solar_pro_download_pdf'); ?></a></div>
  <?php if(!in_array($proposal['status'],['accepted','declined'],true)): ?>
    <div class="sp-decision-card">
      <h3><?php echo _l('solar_pro_customer_information'); ?></h3>
      <p><?php echo _l('solar_pro_proposal_decision_help'); ?></p>
      <?php echo form_open(site_url('solar_pro/proposal/'.$proposal['public_token']), ['class'=>'sp-accept-form','id'=>'sp-proposal-decision-form']); ?>
      <div class="sp-form-row sp-decision-fields">
        <label><span><?php echo _l('solar_pro_full_name'); ?></span><input name="name" required value="<?php echo html_escape(trim($analysis['first_name'].' '.$analysis['last_name'])); ?>"></label>
        <label><span><?php echo _l('solar_pro_email_address'); ?></span><input id="sp-accept-email" name="email" required type="email" value="<?php echo html_escape($analysis['email']); ?>"><small class="sp-field-error" id="sp-email-error"><?php echo _l('solar_pro_invalid_email'); ?></small></label>
        <label><span><?php echo _l('solar_pro_phone_number'); ?></span><input id="sp-accept-phone" name="phone" type="tel" value="<?php echo html_escape($analysis['phone']); ?>" inputmode="tel"><small class="sp-field-error" id="sp-phone-error"><?php echo _l('solar_pro_invalid_phone'); ?></small></label>
      </div>
      <div class="sp-decision-buttons"><button name="action" value="accept" class="sp-btn sp-accept"><?php echo _l('solar_pro_accept'); ?></button><button name="action" value="decline" class="sp-btn sp-decline"><?php echo _l('solar_pro_decline'); ?></button></div>
      <?php echo form_close(); ?>
    </div>
  <?php else: ?><div class="sp-status-pill"><?php echo html_escape(solar_pro_humanize($proposal['status'])); ?></div><?php endif; ?>
</section>
</main>
<script>
(function(){
  var phone=document.getElementById('sp-accept-phone'), email=document.getElementById('sp-accept-email'), form=document.getElementById('sp-proposal-decision-form');
  function normalizePhone(){if(!phone)return true;var digits=phone.value.replace(/\D/g,'');if(digits.length===11&&digits.charAt(0)==='1')digits=digits.slice(1);if(digits.length===10){phone.value='+1 '+digits.slice(0,3)+'-'+digits.slice(3,6)+'-'+digits.slice(6);document.getElementById('sp-phone-error').style.display='none';return true;}if(digits.length===0){return true;}document.getElementById('sp-phone-error').style.display='block';return false;}
  if(phone){phone.addEventListener('blur',normalizePhone);normalizePhone();}
  if(email){email.addEventListener('blur',function(){document.getElementById('sp-email-error').style.display=email.validity.valid?'none':'block';});}
  if(form){form.addEventListener('submit',function(e){var ok=normalizePhone();if(email&&!email.validity.valid){document.getElementById('sp-email-error').style.display='block';ok=false;}if(!ok)e.preventDefault();});}
})();
</script>
</body></html>
