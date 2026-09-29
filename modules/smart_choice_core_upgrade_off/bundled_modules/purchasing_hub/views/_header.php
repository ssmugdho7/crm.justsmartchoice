<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content purchasing-hub-page">
    <div class="row">
      <div class="col-md-12">
        <div class="purchasing-hub-hero">
          <div>
            <h1><?php echo html_escape($title ?? 'Purchasing Hub'); ?></h1>
            <p>Purchasing, vendors, material costs, purchase orders, accounts payable, reports, imports, and CRM-linked procurement controls.</p>
          </div>
          <div class="purchasing-hub-actions">
            <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Refresh</a>
            <a href="<?php echo admin_url('purchasing_hub/settings'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> Settings</a>
            <a href="<?php echo admin_url('purchasing_hub/health_check'); ?>" class="btn btn-info btn-sm"><i class="fa fa-heartbeat"></i> Team Health Check</a>
            <a href="<?php echo admin_url('purchasing_hub/help_training'); ?>" class="btn btn-success btn-sm"><i class="fa fa-life-ring"></i> Health Guide</a>
          </div>
        </div>
        <div class="purchasing-hub-mini-nav">
          <a href="<?php echo admin_url('purchasing_hub'); ?>">Dashboard</a>
          <a href="<?php echo admin_url('purchasing_hub/items'); ?>">Items</a>
          <a href="<?php echo admin_url('purchasing_hub/vendors'); ?>">Vendors</a>
          <a href="<?php echo admin_url('purchasing_hub/purchase_orders'); ?>">Purchase Orders</a>
          <a href="<?php echo admin_url('purchasing_hub/accounts_payable'); ?>">Accounts Payable</a>
          <a href="<?php echo admin_url('purchasing_hub/vendor_quotes'); ?>">Vendor Quotes</a>
          <a href="<?php echo admin_url('purchasing_hub/contracts'); ?>">Contracts</a>
          <a href="<?php echo admin_url('purchasing_hub/reports'); ?>">Purchasing Reports</a>
        </div>
