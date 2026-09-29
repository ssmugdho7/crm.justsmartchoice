<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('accounting/update_hub_integration_settings'), ['id' => 'accounting-hub-integrations-form']); ?>
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-info">
      <strong>Accounting Hub Integration Control</strong><br>
      Enable these options only when the related module is installed. Accounting Hub will then read available tables from Purchase Hub and Sales Hub for reports, QuickBooks Desktop Enterprise IIF export, and accounting synchronization.
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold">Purchase Hub Module</h5>
      <p>Detected Status: <span class="label label-<?php echo !empty($purchase_hub_detected) ? 'success' : 'default'; ?>"><?php echo !empty($purchase_hub_detected) ? 'Installed' : 'Not Installed'; ?></span></p>
      <div class="checkbox checkbox-primary">
        <input type="checkbox" id="acc_purchase_hub_installed" name="acc_purchase_hub_installed" value="1" <?php echo get_option('acc_purchase_hub_installed') === '1' ? 'checked' : ''; ?>>
        <label for="acc_purchase_hub_installed">Is The Purchase Hub Module Installed?</label>
      </div>
      <p class="text-muted">Reads vendors, purchase orders, account payables, expenses, and vendor activity when matching tables are available.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold">Sales Hub Module</h5>
      <p>Detected Status: <span class="label label-<?php echo !empty($sales_hub_detected) ? 'success' : 'default'; ?>"><?php echo !empty($sales_hub_detected) ? 'Installed' : 'Not Installed'; ?></span></p>
      <div class="checkbox checkbox-primary">
        <input type="checkbox" id="acc_sales_hub_installed" name="acc_sales_hub_installed" value="1" <?php echo get_option('acc_sales_hub_installed') === '1' ? 'checked' : ''; ?>>
        <label for="acc_sales_hub_installed">Is The Sales Hub Module Installed?</label>
      </div>
      <p class="text-muted">Reads sales records, customer activity, invoices, estimates, proposals, and payment-related records when matching tables are available.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold">QuickBooks Desktop Enterprise</h5>
      <div class="checkbox checkbox-primary">
        <input type="checkbox" id="acc_quickbooks_desktop_enterprise_enabled" name="acc_quickbooks_desktop_enterprise_enabled" value="1" <?php echo get_option('acc_quickbooks_desktop_enterprise_enabled') === '1' ? 'checked' : ''; ?>>
        <label for="acc_quickbooks_desktop_enterprise_enabled">Enable QuickBooks Desktop IIF Import And Export</label>
      </div>
      <p class="text-muted">Uses tab-delimited IIF files for QuickBooks Desktop Enterprise import/export workflows.</p>
    </div></div>
  </div>

  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold">Finance Setup Sync</h5>
      <div class="checkbox checkbox-primary">
        <input type="checkbox" id="acc_finance_setup_sync_enabled" name="acc_finance_setup_sync_enabled" value="1" <?php echo get_option('acc_finance_setup_sync_enabled') === '1' ? 'checked' : ''; ?>>
        <label for="acc_finance_setup_sync_enabled">Sync Taxes, Currencies, Expense Categories And Payment Modes</label>
      </div>
      <p class="text-muted">Reads Perfex Finance Setup tables and maps them into Accounting Hub and QuickBooks Desktop export workflows without duplicate typing.</p>
      <p class="small text-muted">Payment Modes: <?php echo (int)($finance_sync_status['payment_modes'] ?? 0); ?> · Expense Categories: <?php echo (int)($finance_sync_status['expense_categories'] ?? 0); ?> · Taxes: <?php echo (int)($finance_sync_status['taxes'] ?? 0); ?> · Currencies: <?php echo (int)($finance_sync_status['currencies'] ?? 0); ?></p>
      <a href="<?php echo admin_url('accounting/sync_finance_setup_now'); ?>" class="btn btn-info btn-xs"><i class="fa fa-refresh"></i> Sync Now</a>
    </div></div>
  </div>
</div>
<div class="btn-bottom-toolbar text-right">
  <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i> Refresh</a>
  <a href="<?php echo admin_url('accounting/setting?group=backup_safe_mode'); ?>" class="btn btn-default btn-xs"><i class="fa fa-database"></i> Backup And Database Check</a>
  <button type="submit" class="btn btn-info btn-xs"><i class="fa fa-save"></i> Save Settings</button>
</div>
<?php echo form_close(); ?>
