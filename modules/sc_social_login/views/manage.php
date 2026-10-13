<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
    <h1 class="h3"><?= _l('scsl_title'); ?></h1><p class="text-muted"><?= _l('scsl_subtitle'); ?></p>
    <ul class="nav nav-tabs" role="navigation" aria-label="<?= html_escape(_l('scsl_title')); ?>">
    <?php foreach (['connections'=>scsl_can('view') || scsl_can('view_own') || scsl_can('create'),'settings'=>scsl_can('manage_settings'),'help'=>true] as $item=>$allowed): if(!$allowed)continue; ?>
        <li class="<?= $page===$item?'active':''; ?>"><a href="<?= admin_url('sc_social_login/'.$item); ?>" <?= $page===$item?'aria-current="page"':''; ?>><?= _l('scsl_'.$item); ?></a></li>
    <?php endforeach; ?></ul>
    <?php if(!scsl_license()): ?><div class="alert alert-warning"><?= _l('scsl_license_required'); ?></div><?php endif; ?>
    <?php if($page==='connections'): $this->load->view('sc_social_login/connections',['audience'=>$audience,'identities'=>$identities]); ?>
    <?php elseif($page==='settings'): ?>
        <p><?= _l('scsl_settings_hint'); ?></p>
        <?= form_open(admin_url('sc_social_login/settings'),['autocomplete'=>'off']); ?>
        <?php foreach(['google','facebook','apple'] as $provider): ?>
        <fieldset class="mbot20"><legend class="h4"><?= _l('scsl_provider_'.$provider); ?></legend>
            <p><?= _l('scsl_callback'); ?>: <code><?= html_escape(site_url('sc_social_login/oauth/callback/'.$provider)); ?></code></p>
            <?= render_input($provider.'_client_id','scsl_client_id',get_option('scsl_'.$provider.'_client_id')); ?>
            <?= render_input($provider.'_client_secret','scsl_client_secret','','password',['autocomplete'=>'new-password']); ?>
            <p class="text-muted"><?= _l(get_option('scsl_'.$provider.'_client_secret')?'scsl_secret_saved':'scsl_secret_missing'); ?></p>
            <?php foreach(['staff','client'] as $type): ?><label class="checkbox-inline"><input type="checkbox" name="<?= $provider.'_'.$type; ?>" value="1" <?= get_option('scsl_'.$provider.'_'.$type)==='1'?'checked':''; ?>><?= _l('scsl_enable_'.$type); ?></label><?php endforeach; ?>
        </fieldset><?php endforeach; ?>
        <?= render_input('graph_version','scsl_graph_version',get_option('scsl_facebook_graph_version')); ?>
        <label><input type="checkbox" name="client_registration" value="1" <?= get_option('scsl_client_registration')==='1'?'checked':''; ?>> <?= _l('scsl_allow_registration'); ?></label>
        <p class="text-muted"><?= _l('scsl_apple_hint'); ?></p><button class="btn btn-primary" type="submit"><?= _l('scsl_save'); ?></button><?= form_close(); ?>
    <?php else: ?><h2 class="h4"><?= _l('scsl_help_title'); ?></h2><p><?= _l('scsl_help_accounts'); ?></p><p><?= _l('scsl_help_providers'); ?></p><p><?= _l('scsl_help_apple'); ?></p><?php endif; ?>
</div></div></div></div>
<?php init_tail(); ?>
