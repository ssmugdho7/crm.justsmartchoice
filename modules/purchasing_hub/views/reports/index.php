<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="row purchasing-hub-cards">
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Open Vendor Bills</h4><strong><?php echo app_format_money($totals['open_bills'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Paid Vendor Bills</h4><strong><?php echo app_format_money($totals['paid_bills'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Purchase Orders</h4><strong><?php echo app_format_money($totals['purchase_orders'], get_base_currency()); ?></strong></div></div></div>
  <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h4>Vendor Quotes</h4><strong><?php echo app_format_money($totals['vendor_quotes'], get_base_currency()); ?></strong></div></div></div>
</div>
<div class="panel_s"><div class="panel-body">
  <div class="clearfix">
    <h3 class="pull-left">QuickBooks Style Purchasing Report Center</h3>
    <a class="btn btn-primary btn-sm pull-right" href="<?php echo admin_url('purchasing_hub/add_report'); ?>"><i class="fa fa-plus"></i> Add Report</a>
  </div>
  <p class="text-muted">Open live purchasing reports or CRM-linked reports from Estimates, Proposals, Invoices, Payments, Credit Notes, Sales, and Projects.</p>
  <hr>
  <div class="row">
    <?php foreach($available_reports as $key=>$label){ ?>
    <div class="col-md-4">
      <a class="purchasing-report-box purchasing-report-link" href="<?php echo admin_url('purchasing_hub/reports/'.$key); ?>">
        <h4><?php echo html_escape($label); ?></h4>
        <p>Open this working report with live module or CRM data.</p>
      </a>
    </div>
    <?php } ?>
  </div>
</div></div>
<?php if(!empty($custom_reports)){ ?>
<div class="panel_s"><div class="panel-body">
  <h4>Saved Purchasing Reports</h4>
  <div class="table-responsive"><table class="table table-bordered dt-table purchasing-hub-table">
    <thead><tr><th>Report Name</th><th>Report Type</th><th>Description</th><th>Status</th><th>Action</th></tr></thead>
    <tbody><?php foreach($custom_reports as $custom){ ?><tr>
      <td><?php echo html_escape($custom['report_name']); ?></td>
      <td><?php echo html_escape($available_reports[$custom['report_type']] ?? ucwords(str_replace('_',' ', $custom['report_type']))); ?></td>
      <td><?php echo html_escape($custom['description']); ?></td>
      <td><?php echo !empty($custom['is_active']) ? 'Active' : 'Inactive'; ?></td>
      <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/reports/'.$custom['report_type']); ?>">View</a></td>
    </tr><?php } ?></tbody>
  </table></div>
</div></div>
<?php } ?>
<?php if($report !== 'center' && $report !== 'custom'){ ?>
<div class="panel_s"><div class="panel-body">
  <h4><?php echo html_escape($available_reports[$report] ?? ucwords(str_replace('_',' ', $report))); ?></h4>
  <div class="table-responsive"><table class="table table-bordered dt-table purchasing-hub-table">
    <thead><tr><?php if(!empty($report_rows)){ foreach(array_keys($report_rows[0]) as $head){ echo '<th>'.html_escape(ucwords(str_replace('_',' ', $head))).'</th>'; } } else { echo '<th>Status</th>'; } ?></tr></thead>
    <tbody><?php if(empty($report_rows)){ ?><tr><td>No records found for this report or the required CRM table is not available.</td></tr><?php } else { foreach($report_rows as $row){ ?><tr><?php foreach($row as $value){ ?><td><?php echo html_escape((string)$value); ?></td><?php } ?></tr><?php }} ?></tbody>
  </table></div>
</div></div>
<?php } ?>
<?php $this->load->view('purchasing_hub/_footer'); ?>
