<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$CI = &get_instance();
if (!isset($settings) || !is_array($settings)) {
    $CI->load->model('superman/superman_model');
    $settings = $CI->superman_model->get_settings_payload();
}
if (!isset($profiles) || !is_array($profiles)) {
    $profiles = [];
}
?>
<div id="wrapper">
  <div class="content superman-wrap superman-settings-page">
    <div class="superman-settings-hero">
      <div>
        <h1><i class="fa fa-bolt"></i> <?php echo _l('superman_settings_title'); ?></h1>
        <p><?php echo _l('superman_settings_help'); ?></p>
      </div>
      <div class="superman-hero-actions">
        <a href="<?php echo admin_url('superman'); ?>" class="btn superman-btn-light"><i class="fa fa-diagram-project"></i> <?php echo _l('superman_control_center'); ?></a>
        <a href="<?php echo admin_url('superman/merge_fields'); ?>" class="btn superman-btn-light"><i class="fa fa-search"></i> <?php echo _l('superman_merge_catalog'); ?></a>
        <a href="<?php echo admin_url('superman/health'); ?>" class="btn superman-btn-dark"><i class="fa fa-heartbeat"></i> <?php echo _l('superman_health_check'); ?></a>
      </div>
    </div>

    <?php $CI->load->view('superman/partials/nav'); ?>

    <div class="row">
      <div class="col-md-8">
        <div class="panel_s superman-panel"><div class="panel-body">
          <?php echo form_open(admin_url('superman/settings_save')); ?>
          <h4><?php echo _l('superman_automation_controls'); ?></h4>
          <div class="row">
            <?php foreach(['superman_enable_automation'=>'superman_enable_automation','superman_enable_form_autofill'=>'superman_enable_form_autofill','superman_enable_visual_builder'=>'superman_enable_visual_builder','superman_enable_merge_search'=>'superman_enable_merge_search','superman_enable_rollback_profiles'=>'superman_enable_rollback_profiles','superman_overwrite_existing'=>'superman_overwrite_existing'] as $key=>$label){ ?>
            <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" <?php echo isset($settings[$key]) && $settings[$key]=='1'?'checked':''; ?>><label for="<?php echo $key; ?>"><?php echo _l($label); ?></label></div></div>
            <?php } ?>
          </div>
          <hr>
          <h4><?php echo _l('superman_sleek_design_controls'); ?></h4>
          <div class="row">
            <div class="col-md-4"><?php echo render_input('superman_primary_color','superman_primary_color',$settings['superman_primary_color'] ?? '#169179','color'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_secondary_color','superman_secondary_color',$settings['superman_secondary_color'] ?? '#0057b8','color'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_accent_color','superman_accent_color',$settings['superman_accent_color'] ?? '#f47c20','color'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_dark_color','superman_dark_color',$settings['superman_dark_color'] ?? '#263238','color'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_surface_color','superman_surface_color',$settings['superman_surface_color'] ?? '#f5f7f8','color'); ?></div>
          </div>
          <div class="row">
            <div class="col-md-4"><?php echo render_input('superman_sidebar_width','superman_sidebar_width',$settings['superman_sidebar_width'] ?? '260','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_topbar_height','superman_topbar_height',$settings['superman_topbar_height'] ?? '64','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_logo_max_height','superman_logo_max_height',$settings['superman_logo_max_height'] ?? '54','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_menu_font_size','superman_menu_font_size',$settings['superman_menu_font_size'] ?? '14','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_mobile_menu_font_size','superman_mobile_menu_font_size',$settings['superman_mobile_menu_font_size'] ?? '16','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_table_name_width','superman_table_name_width',$settings['superman_table_name_width'] ?? '180','number'); ?></div>
            <div class="col-md-4"><?php echo render_input('superman_table_email_width','superman_table_email_width',$settings['superman_table_email_width'] ?? '160','number'); ?></div>
            <div class="col-md-4"><label><?php echo _l('superman_client_login_button_size'); ?></label><select name="superman_client_login_button_size" class="selectpicker" data-width="100%"><option value="small" <?php echo (($settings['superman_client_login_button_size'] ?? '')=='small')?'selected':''; ?>><?php echo _l('superman_small'); ?></option><option value="medium" <?php echo (($settings['superman_client_login_button_size'] ?? '')=='medium')?'selected':''; ?>><?php echo _l('superman_medium'); ?></option><option value="compact" <?php echo (($settings['superman_client_login_button_size'] ?? '')=='compact')?'selected':''; ?>><?php echo _l('superman_compact'); ?></option></select></div>
          </div>
          <div class="row">
            <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="superman_dropdown_single_scrollbar" id="superman_dropdown_single_scrollbar" <?php echo isset($settings['superman_dropdown_single_scrollbar']) && $settings['superman_dropdown_single_scrollbar']=='1'?'checked':''; ?>><label for="superman_dropdown_single_scrollbar"><?php echo _l('superman_dropdown_single_scrollbar'); ?></label></div></div>
            <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="superman_gradient_white_text" id="superman_gradient_white_text" <?php echo isset($settings['superman_gradient_white_text']) && $settings['superman_gradient_white_text']=='1'?'checked':''; ?>><label for="superman_gradient_white_text"><?php echo _l('superman_gradient_white_text'); ?></label></div></div>
            <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="superman_sleek_mode" id="superman_sleek_mode" <?php echo isset($settings['superman_sleek_mode']) && $settings['superman_sleek_mode']=='1'?'checked':''; ?>><label for="superman_sleek_mode"><?php echo _l('superman_sleek_mode'); ?></label></div></div>
          </div>
          <div class="superman-preview-card" id="superman-live-preview">
            <div class="superman-preview-top"><span class="superman-preview-logo">Smart Choice</span><span class="superman-preview-menu">Dashboard · Leads · Estimates</span><button type="button">Login</button></div>
            <div class="superman-preview-body"><div></div><div></div><div></div></div>
          </div>
          <button type="submit" class="btn superman-btn"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
          <?php echo form_close(); ?>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="panel_s superman-panel"><div class="panel-heading"><h4><?php echo _l('superman_backup_restore'); ?></h4></div><div class="panel-body">
          <?php echo form_open(admin_url('superman/save_profile')); ?>
          <?php echo render_input('profile_name', 'superman_profile_name', _l('superman_safe_design_backup') . ' ' . date('Y-m-d H:i')); ?>
          <button class="btn superman-btn btn-block"><i class="fa fa-shield"></i> <?php echo _l('superman_save_current_profile'); ?></button>
          <?php echo form_close(); ?>
          <hr>
          <?php if(empty($profiles)){ ?><p class="text-muted"><?php echo _l('superman_no_profiles'); ?></p><?php } ?>
          <?php foreach($profiles as $profile){ ?>
            <div class="superman-profile-row"><span><?php echo html_escape(ucwords(str_replace(['_', '-'], ' ', (string) $profile['profile_name']))); ?></span><a href="<?php echo admin_url('superman/restore_profile/'.$profile['id']); ?>" class="btn btn-default btn-xs"><?php echo _l('superman_restore'); ?></a></div>
          <?php } ?>
        </div></div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
