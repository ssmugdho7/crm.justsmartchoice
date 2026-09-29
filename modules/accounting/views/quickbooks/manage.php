<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body smart-choice-compact-page">
        <div class="smart-choice-page-header">
          <h4 class="no-margin font-bold"><i class="fa fa-file-text-o"></i> <?php echo _l('quickbooks_desktop_enterprise_integration'); ?></h4>
          <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a>
        </div>
        <hr />
        <div class="alert alert-info"><?php echo _l('quickbooks_desktop_iif_notice'); ?></div>
        <div class="row">
          <div class="col-md-6"><div class="alert alert-<?php echo !empty($purchase_hub_enabled) ? 'success' : 'warning'; ?> no-margin">Purchase Hub Integration: <?php echo !empty($purchase_hub_enabled) ? 'Enabled And Detected' : 'Disabled Or Not Detected'; ?></div></div>
          <div class="col-md-6"><div class="alert alert-<?php echo !empty($sales_hub_enabled) ? 'success' : 'warning'; ?> no-margin">Sales Hub Integration: <?php echo !empty($sales_hub_enabled) ? 'Enabled And Detected' : 'Disabled Or Not Detected'; ?></div></div>
        </div><br />
        <div class="row">
          <div class="col-md-4"><div class="panel_s"><div class="panel-body">
            <h5 class="bold"><?php echo _l('quickbooks_export_headers'); ?></h5>
            <p class="quickbooks-small-note"><?php echo _l('quickbooks_export_headers_note'); ?></p>
            <div class="btn-group-vertical full-width">
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/chart_of_accounts'); ?>">Export Chart Of Accounts IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/vendors'); ?>">Export Vendors IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/items'); ?>">Export Items IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/payment_modes'); ?>">Export Payment Modes IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/expense_categories'); ?>">Export Expense Categories IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/taxes'); ?>">Export Taxes IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/currencies'); ?>">Export Currencies IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/purchase_orders'); ?>">Export Purchase Hub Purchase Orders IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/bills'); ?>">Export Purchase Hub Bills IIF</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_export/sales_hub'); ?>">Export Sales Hub Transactions IIF</a>
            </div>
          </div></div></div>
          <div class="col-md-4"><div class="panel_s"><div class="panel-body">
            <h5 class="bold"><?php echo _l('sample_files'); ?></h5>
            <p class="quickbooks-small-note"><?php echo _l('quickbooks_sample_files_note'); ?></p>
            <div class="btn-group-vertical full-width">
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/chart_of_accounts'); ?>">Sample Chart Of Accounts</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/vendors'); ?>">Sample Vendors</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/customers'); ?>">Sample Customers</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/items'); ?>">Sample Items</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/payment_modes'); ?>">Sample Payment Modes</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/expense_categories'); ?>">Sample Expense Categories</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/taxes'); ?>">Sample Taxes</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/currencies'); ?>">Sample Currencies</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/bills'); ?>">Sample Bills</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/invoices'); ?>">Sample Invoices</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/purchase_orders'); ?>">Sample Purchase Orders</a>
              <a class="btn btn-default btn-xs" href="<?php echo admin_url('accounting/quickbooks_download_sample/sales_hub'); ?>">Sample Sales Hub</a>
            </div>
          </div></div></div>
          <div class="col-md-4"><div class="panel_s"><div class="panel-body">
            <h5 class="bold"><?php echo _l('quickbooks_import_preview'); ?></h5>
            <?php echo form_open_multipart(admin_url('accounting/quickbooks_preview_import'), ['id' => 'quickbooks-preview-form']); ?>
              <input type="file" name="quickbooks_file" class="form-control" accept=".iif,.txt,.tsv,.csv" />
              <br><button type="submit" class="btn btn-info btn-xs"><i class="fa fa-search"></i> Preview Import</button>
            <?php echo form_close(); ?>
            <div id="quickbooks-preview-result" class="mtop15"></div>
          </div></div></div>
        </div>
        
        <div class="panel_s"><div class="panel-body">
          <h5 class="bold">Finance Setup Sync Sources</h5>
          <div class="row">
            <div class="col-md-3"><div class="alert alert-info no-margin">Taxes → QuickBooks Tax Items</div></div>
            <div class="col-md-3"><div class="alert alert-info no-margin">Currencies → QuickBooks Currency List</div></div>
            <div class="col-md-3"><div class="alert alert-info no-margin">Expense Categories → Expense Accounts</div></div>
            <div class="col-md-3"><div class="alert alert-info no-margin">Payment Modes → Income/Deposit Mapping</div></div>
          </div>
        </div></div>
        <div class="panel_s"><div class="panel-body">
          <h5 class="bold"><?php echo _l('quickbooks_workflow'); ?></h5>
          <div class="smart-choice-flow">
            <div>CRM Tables</div><span>→</span><div>IIF Export</div><span>→</span><div>QuickBooks Desktop Enterprise</div><span>→</span><div>IIF Import Preview</div><span>→</span><div>CRM Accounting Tables</div>
          </div>
        </div></div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
(function($){
  $('#quickbooks-preview-form').on('submit', function(e){
    e.preventDefault();
    var data = new FormData(this);
    $.ajax({url: $(this).attr('action'), method:'POST', data:data, processData:false, contentType:false, dataType:'json'}).done(function(res){
      var html = '<div class="alert '+(res.success?'alert-success':'alert-danger')+'">'+res.message+'</div>';
      if(res.rows && res.rows.length){
        html += '<div class="table-responsive"><table class="table table-bordered table-condensed"><tbody>';
        $.each(res.rows, function(i,row){ html += '<tr>'; $.each(row, function(j,cell){ html += '<td>'+String(cell).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];})+'</td>'; }); html += '</tr>'; });
        html += '</tbody></table></div><button class="btn btn-info btn-xs" type="button">Accept And Import</button>';
      }
      $('#quickbooks-preview-result').html(html);
    });
  });
})(jQuery);
</script>
</body></html>
