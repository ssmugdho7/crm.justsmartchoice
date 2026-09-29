<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa-solid fa-shield-halved"></i> <?= _l('sc_core_manager'); ?></h4><hr>
<?= form_open(admin_url('smart_choice_core_manager')); ?>
<ul class="nav nav-tabs" role="tablist">
<li class="active"><a href="#sc-company" data-toggle="tab">Company &amp; Branding</a></li><li><a href="#sc-localization" data-toggle="tab">Language &amp; Finance</a></li><li><a href="#sc-documents" data-toggle="tab">Documents &amp; Signatures</a></li><li><a href="#sc-system" data-toggle="tab">System Tools</a></li><li><a href="#sc-governance" data-toggle="tab">Module Governance</a></li>
</ul><div class="tab-content mtop20">
<div class="tab-pane active" id="sc-company">
<div class="alert alert-info"><b>Company Logo:</b> Used in admin navigation and general CRM branding. Recommended transparent PNG, 600×180 px maximum, no white background.<br><b>Dark Logo:</b> Used where the interface background is light. Recommended transparent PNG, 600×180 px maximum.<br><b>Favicon:</b> Used in browser tabs and bookmarks. Recommended square PNG or ICO, 512×512 px, transparent or solid background.<br><b>PDF Signature:</b> Used on generated PDF documents. Recommended transparent PNG, 900×300 px maximum.</div>
<div class="row"><div class="col-md-4"><?= render_input('settings[sc_company_ein]','EIN Number',get_option('sc_company_ein'),'text',['inputmode'=>'numeric','pattern'=>'[0-9]*','maxlength'=>9]); ?></div><div class="col-md-4"><?= render_input('settings[sc_crm_portal_url]','CRM Portal Link',get_option('sc_crm_portal_url'),'url'); ?></div><div class="col-md-4"><?= render_input('settings[sc_company_website]','Company Website',get_option('sc_company_website'),'url'); ?></div></div>
<div class="row"><div class="col-md-4"><?= render_input('settings[sc_nav_logo_height_percent]','Navigation Logo Height (%)',get_option('sc_nav_logo_height_percent'),'number',['min'=>40,'max'=>95]); ?></div><div class="col-md-4"><?= render_input('settings[sc_menu_background_color]','Menu Background Color',get_option('sc_menu_background_color'),'color'); ?></div><div class="col-md-4"><?= render_input('settings[sc_menu_text_color]','Menu Text Color',get_option('sc_menu_text_color'),'color'); ?></div></div>
<?= render_select('settings[sc_login_background_mode]',[['id'=>'cover','name'=>'Fill / Cover'],['id'=>'contain','name'=>'Fit Entire Image'],['id'=>'100% 100%','name'=>'Stretch to Page']],['id','name'],'Login Background Display',get_option('sc_login_background_mode')); ?>
</div>
<div class="tab-pane" id="sc-localization">
<div class="alert alert-success">English is the CRM base language. Spanish is the only additional enabled language. Staff and customers should see only “English” or “Spanish”.</div>
<?= render_input('settings[sc_enabled_languages]','Enabled Languages',get_option('sc_enabled_languages'),'text',['readonly'=>true]); ?>
<?= render_select('settings[sc_pdf_client_language]',[['id'=>'1','name'=>'Yes'],['id'=>'0','name'=>'No']],['id','name'],'Output client PDF documents from admin area in the client language',get_option('sc_pdf_client_language')); ?>
<div class="row"><div class="col-md-6"><?= render_input('settings[sc_currency_prefix]','Money Prefix / Symbol',get_option('sc_currency_prefix')); ?></div><div class="col-md-6"><?= render_input('settings[sc_currency_suffix]','Money Suffix / Currency Code',get_option('sc_currency_suffix')); ?></div></div>
</div>
<div class="tab-pane" id="sc-documents">
<?= render_select('settings[sc_allow_html_document_terms]',[['id'=>'1','name'=>'Enabled'],['id'=>'0','name'=>'Disabled']],['id','name'],'Allow HTML in default notes and terms',get_option('sc_allow_html_document_terms')); ?>
<?= render_select('settings[sc_signature_initials_enabled]',[['id'=>'1','name'=>'Enabled'],['id'=>'0','name'=>'Disabled']],['id','name'],'Contract initials and customer signature merge fields',get_option('sc_signature_initials_enabled')); ?>
<?= render_input('settings[sc_discount_types]','Available Discount Types',get_option('sc_discount_types')); ?>
<?= render_select('settings[sc_show_appointment_link]',[['id'=>'1','name'=>'Show'],['id'=>'0','name'=>'Hide']],['id','name'],'Show Make an Appointment in Customer Area',get_option('sc_show_appointment_link')); ?>
<?= render_input('settings[sc_appointment_url]','Appointment Link',get_option('sc_appointment_url'),'url'); ?>
</div>
<div class="tab-pane" id="sc-system">
<div class="row"><div class="col-md-6"><?= render_select('settings[sc_environment_mode]',[['id'=>'production','name'=>'Production Mode'],['id'=>'development','name'=>'Debug / Development Mode']],['id','name'],'CRM Environment Mode',get_option('sc_environment_mode')); ?></div><div class="col-md-6"><?= render_select('settings[sc_client_portal_enabled]',[['id'=>'1','name'=>'Enabled'],['id'=>'0','name'=>'Disabled']],['id','name'],'Client Portal',get_option('sc_client_portal_enabled')); ?></div></div>
<p><a class="btn btn-info" href="<?= admin_url('smart_choice_core_manager/cron_test'); ?>"><i class="fa fa-clock"></i> Test Cron Configuration</a> <a class="btn btn-default" href="<?= admin_url('smart_choice_core_manager/speed_test'); ?>"><i class="fa fa-gauge-high"></i> Run Server Speed Diagnostic</a> <a class="btn btn-warning" onclick="return confirm('Clear CRM cache files now?')" href="<?= admin_url('smart_choice_core_manager/clear_cache'); ?>"><i class="fa fa-broom"></i> Clean CRM Cache</a></p>
<div class="well">Last cron test: <?= e(get_option('sc_last_cron_test')?:'Not tested'); ?><br>Last speed diagnostic: <?= e(get_option('sc_last_speed_test')?:'Not tested'); ?></div>
</div>
<div class="tab-pane" id="sc-governance">
<?= render_select('settings[sc_require_module_manifest]',[['id'=>'1','name'=>'Required'],['id'=>'0','name'=>'Not Required']],['id','name'],'Require Module Impact Manifest',get_option('sc_require_module_manifest')); ?>
<div class="alert alert-warning">Every new module should declare its database tables, core-file changes, global CSS selectors, JavaScript globals, permissions, and uninstall behavior in <code>smart_choice_module_manifest.json</code>. The manager warns before activation when this file is missing.</div>
<p><a class="btn btn-primary" href="<?= admin_url('smart_choice_core_manager/download_core_manifest'); ?>"><i class="fa fa-download"></i> Download CRM Build Manifest</a></p>
<div class="well">Current managed build: <b><?= e(get_option('sc_core_build_version')); ?></b></div>
</div></div>
<button type="submit" class="btn btn-primary">Save Settings</button><?= form_close(); ?>
</div></div></div></div><?php init_tail(); ?>
