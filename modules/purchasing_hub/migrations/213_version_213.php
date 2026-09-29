<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_213 extends App_module_migration
{
    public function up()
    {
        add_option('purchasing_hub_default_tax_rate', '0');
        add_option('purchasing_hub_use_crm_items', '1');
        add_option('purchasing_hub_require_vendor_email', '0');
        add_option('purchasing_hub_po_prefix', 'PO-');
        add_option('purchasing_hub_default_due_days', '15');
        add_option('purchasing_hub_default_currency', 'USD');
        add_option('purchasing_hub_default_terms', 'All materials must match approved specifications. Delivery tickets and invoices must reference the purchase order number.');
        add_option('purchasing_hub_default_email_message', 'Please review the attached purchase order and confirm availability, pricing, and delivery schedule.');
    }
    public function down()
    {
        // Safe no-op rollback for Smart Choice patched migration.
    }
}
