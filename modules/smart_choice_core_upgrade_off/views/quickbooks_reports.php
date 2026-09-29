<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin">QuickBooks Desktop Reports</h4>
        <p class="text-muted">QuickBooks Desktop Enterprise integration is designed around IIF import/export files, not QuickBooks Online.</p>
        <hr />
        <div class="alert alert-info">
          Use Accounting Hub as the central exchange point. Sales Hub records export as income transactions. Purchasing Hub bills and purchase orders export as accounts payable or expense transactions. Project and customer data should be mapped before export.
        </div>
        <table class="table table-bordered table-striped">
          <thead><tr><th>QuickBooks Area</th><th>CRM Source</th><th>Expected Action</th></tr></thead>
          <tbody>
            <tr><td>Customers</td><td>Clients / Leads Converted To Customers</td><td>Export customer list IIF.</td></tr>
            <tr><td>Items</td><td>Sales Items / Purchasing Items</td><td>Export item list IIF.</td></tr>
            <tr><td>Invoices</td><td>Invoices / Sales Hub</td><td>Export invoice IIF-ready records.</td></tr>
            <tr><td>Bills</td><td>Accounting Hub / Purchasing Hub Accounts Payable</td><td>Export bill and payable records.</td></tr>
            <tr><td>Purchase Orders</td><td>Purchasing Hub</td><td>Export purchase order records when supported by workflow.</td></tr>
            <tr><td>Classes / Jobs</td><td>Projects</td><td>Map projects to QuickBooks jobs or classes.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
