<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="recruitment-native-settings">
  <div class="panel_s"><div class="panel-body">
    <div class="sc-settings-hero"><i class="fa fa-address-card"></i><div><h4><?php echo _l('recruitment_settings_title'); ?></h4><p><?php echo _l('recruitment_settings_description'); ?></p></div></div>
    <?php echo form_open(admin_url('recruitment/save_native_settings')); ?>
    <?php echo render_input('settings[recruitment_skill_test_email]', 'recruitment_skill_test_recipient', get_option('recruitment_skill_test_email') ?: 'employees@justasmartchoice.com', 'email'); ?>
    <?php echo render_input('settings[smart_choice_recruitment_w4_url]', 'recruitment_w4_url_setting', get_option('smart_choice_recruitment_w4_url') ?: 'https://www.irs.gov/pub/irs-pdf/fw4.pdf', 'url'); ?>
    <?php echo render_input('settings[recruitment_portal_primary_color]', 'recruitment_primary_color', get_option('recruitment_portal_primary_color') ?: '#F28C28', 'color'); ?>
    <?php echo render_input('settings[recruitment_portal_secondary_color]', 'recruitment_secondary_color', get_option('recruitment_portal_secondary_color') ?: '#169179', 'color'); ?>
    <?php echo render_input('settings[recruitment_portal_dark_color]', 'recruitment_dark_color', get_option('recruitment_portal_dark_color') ?: '#0E6F5B', 'color'); ?>
    <h4><?php echo _l('recruitment_portal_extended_colors'); ?></h4>
    <div class="row">
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_page_bg]', 'recruitment_portal_page_bg', get_option('recruitment_portal_page_bg') ?: '#F5F7F6', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_card_bg]', 'recruitment_portal_card_bg', get_option('recruitment_portal_card_bg') ?: '#FFFFFF', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_text_color]', 'recruitment_portal_text_color', get_option('recruitment_portal_text_color') ?: '#263238', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_muted_color]', 'recruitment_portal_muted_color', get_option('recruitment_portal_muted_color') ?: '#607D76', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_border_color]', 'recruitment_portal_border_color', get_option('recruitment_portal_border_color') ?: '#D9E2DF', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_button_color]', 'recruitment_portal_button_color', get_option('recruitment_portal_button_color') ?: '#F28C28', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_button_text]', 'recruitment_portal_button_text', get_option('recruitment_portal_button_text') ?: '#FFFFFF', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_badge_bg]', 'recruitment_portal_badge_bg', get_option('recruitment_portal_badge_bg') ?: '#EEF3F1', 'color'); ?></div>
      <div class="col-md-4"><?php echo render_input('settings[recruitment_portal_badge_text]', 'recruitment_portal_badge_text', get_option('recruitment_portal_badge_text') ?: '#0E6F5B', 'color'); ?></div>
    </div>
    <h4><?php echo _l('recruitment_assessment_settings'); ?></h4>
    <?php foreach ([
      'recruitment_tests_required'=>'recruitment_tests_required',
      'recruitment_test_construction_enabled'=>'recruitment_test_construction',
      'recruitment_test_behavior_enabled'=>'recruitment_test_behavior',
      'recruitment_test_safety_enabled'=>'recruitment_test_safety',
      'recruitment_test_reliability_enabled'=>'recruitment_test_reliability'
    ] as $option=>$label): ?>
    <div class="form-group"><label><?php echo _l($label); ?></label><div class="onoffswitch"><input type="checkbox" name="settings[<?php echo $option; ?>]" class="onoffswitch-checkbox" id="<?php echo $option; ?>" value="1" <?php echo get_option($option) != '0' ? 'checked' : ''; ?>><label class="onoffswitch-label" for="<?php echo $option; ?>"></label></div></div>
    <?php endforeach; ?>
    <div class="form-group"><label><?php echo _l('recruitment_skill_test_enabled'); ?></label><div class="onoffswitch"><input type="checkbox" name="settings[recruitment_skill_test_enabled]" class="onoffswitch-checkbox" id="recruitment_skill_test_enabled" value="1" <?php echo get_option('recruitment_skill_test_enabled') != '0' ? 'checked' : ''; ?>><label class="onoffswitch-label" for="recruitment_skill_test_enabled"></label></div></div>
    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
    <?php echo form_close(); ?>
    <hr>
    <h4><?php echo _l('recruitment_advanced_settings'); ?></h4>
    <p><?php echo _l('recruitment_advanced_settings_help'); ?></p>
    <div class="sc-settings-grid">
    <?php foreach(['job_position','evaluation_criteria','evaluation_form','tranfer_personnel','skills','company_list','industry_list','recruitment_campaign_setting','recruitment_portal_branding'] as $group): ?>
      <a class="sc-settings-card" href="<?php echo admin_url('recruitment/setting?group='.$group); ?>"><i class="fa fa-cog"></i><span><?php echo _l($group); ?></span></a>
    <?php endforeach; ?>
    </div>
  </div></div>
</div>
<style>.sc-settings-hero{display:flex;gap:14px;align-items:center;background:linear-gradient(135deg,#0E6F5B,#169179);color:#fff;padding:20px;border-radius:14px;margin-bottom:20px}.sc-settings-hero i{font-size:34px}.sc-settings-hero h4,.sc-settings-hero p{color:#fff;margin:0}.sc-settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}.sc-settings-card{display:flex;gap:10px;align-items:center;padding:14px;border:1px solid #dfe8e5;border-radius:12px;background:#fff;color:#0E6F5B;font-weight:700;box-shadow:0 5px 14px rgba(14,111,91,.08)}.sc-settings-card:hover{border-color:#F28C28;color:#F28C28;transform:translateY(-1px)}</style>
