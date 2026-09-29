<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s"><div class="panel-body">
<h4 class="tw-font-semibold tw-mt-0">Smart Choice Core Enhancements</h4>
<hr class="hr-panel-separator" />
<div class="form-group"><label>PDF Signature Scale Percent: <strong id="sigv"><?= html_escape(get_option('smart_choice_pdf_signature_scale_percent') ?: '100'); ?>%</strong></label><input type="range" min="25" max="250" step="5" name="settings[smart_choice_pdf_signature_scale_percent]" value="<?= html_escape(get_option('smart_choice_pdf_signature_scale_percent') ?: '100'); ?>" class="form-control" oninput="document.getElementById('sigv').textContent=this.value+'%' "></div>
<h4>Quick Create Menu</h4>
<p class="text-muted">Change a destination URL, or leave it blank to remove that entry from Quick Create.</p>
<?= render_input('settings[scps_quick_purchase_order_url]','Purchase Order URL',get_option('scps_quick_purchase_order_url')); ?>
<?= render_input('settings[scps_quick_subcontractor_url]','Subcontractor URL',get_option('scps_quick_subcontractor_url')); ?>
<?= render_input('settings[scps_quick_training_manual_url]','Training Manual URL',get_option('scps_quick_training_manual_url')); ?>
<?= render_input('settings[scps_quick_note_url]','Note URL',get_option('scps_quick_note_url')); ?>
<hr class="hr-panel-separator" />
<?= render_textarea('settings[scce_webhooks]','Webhooks (JSON)',get_option('scce_webhooks'),['rows'=>6]); ?>
<?= render_textarea('settings[scce_custom_js_admin]','Custom JavaScript — Admin',get_option('scce_custom_js_admin'),['rows'=>8]); ?>
<?= render_textarea('settings[scce_custom_js_client]','Custom JavaScript — Client',get_option('scce_custom_js_client'),['rows'=>8]); ?>
<a class="btn btn-info" href="<?= admin_url('smart_choice_core_enhancements/structure_xml'); ?>"><i class="fa fa-sitemap"></i> Download CRM Structure XML</a>
</div></div>
