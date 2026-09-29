<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s smart-choice-purchasing-settings">
    <div class="panel-body">
        <h4 class="tw-font-bold">Purchasing Hub Settings</h4>
        <p class="text-muted">Default controls for purchase orders, vendor communication, tax defaults, terms, and CRM item integration.</p>
        <?php echo form_open(admin_url('settings?group=purchasing_hub_settings')); ?>
        <div class="row">
            <div class="col-md-4"><?php echo render_input('settings[purchasing_hub_default_tax_rate]', 'Default Tax Rate %', get_option('purchasing_hub_default_tax_rate') ?: '0', 'number', ['step'=>'0.01']); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('purchasing_hub_use_crm_items', 'Allow CRM Sales / Invoice Items in Purchase Orders'); ?></div>
            <div class="col-md-4"><?php echo render_yes_no_option('purchasing_hub_require_vendor_email', 'Require Vendor Email Before Sending'); ?></div>
            <div class="col-md-4"><?php echo render_input('settings[purchasing_hub_po_prefix]', 'Purchase Order Prefix', get_option('purchasing_hub_po_prefix') ?: 'PO-'); ?></div>
            <div class="col-md-4"><?php echo render_input('settings[purchasing_hub_default_due_days]', 'Default Due Days', get_option('purchasing_hub_default_due_days') ?: '15', 'number'); ?></div>
            <div class="col-md-4"><?php echo render_input('settings[purchasing_hub_default_currency]', 'Default Currency', get_option('purchasing_hub_default_currency') ?: 'USD'); ?></div>
            <div class="col-md-12"><?php echo render_textarea('settings[purchasing_hub_default_terms]', 'Default Purchase Order Terms', get_option('purchasing_hub_default_terms') ?: 'All materials must match approved specifications. Delivery tickets and invoices must reference the purchase order number.'); ?></div>
            <div class="col-md-12"><?php echo render_textarea('settings[purchasing_hub_default_email_message]', 'Default Vendor Email Message', get_option('purchasing_hub_default_email_message') ?: 'Please review the attached purchase order and confirm availability, pricing, and delivery schedule.'); ?></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
        <?php echo form_close(); ?>
    </div>
</div>
