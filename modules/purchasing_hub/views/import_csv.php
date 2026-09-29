<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <h4><?php echo html_escape($title); ?></h4>
  <div class="alert alert-info">
    Upload the import file, select the format, and confirm whether the first row contains headers. The system will show a preview table before importing anything.
  </div>
  <p><strong>Supported columns:</strong> <?php echo html_escape(implode(', ', $headers)); ?></p>
  <a class="btn btn-default btn-sm" href="<?php echo admin_url('purchasing_hub/sample/'.$type); ?>"><i class="fa fa-download"></i> Download Sample Import File</a>
  <hr>
  <?php echo form_open_multipart(admin_url('purchasing_hub/import/'.$type)); ?>
    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label>Import File</label>
          <input type="file" name="import_file" class="form-control" accept=".csv,.txt,.xls,.xlsx,.pdf" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>File Format</label>
          <select name="file_format" class="form-control">
            <option value="auto">Auto Detect</option>
            <option value="csv">CSV</option>
            <option value="xlsx">Excel XLSX</option>
            <option value="xls">Excel XLS</option>
            <option value="pdf">PDF</option>
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Header Row</label>
          <select name="has_header" class="form-control">
            <option value="1">File has header row</option>
            <option value="0">File has no header row</option>
          </select>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Preview Import</button>
    <a href="<?php echo admin_url('purchasing_hub/' . ($type === 'items' ? 'items' : ($type === 'vendors' ? 'vendors' : ['orders'=>'purchase_orders','bills'=>'accounts_payable','quotes'=>'vendor_quotes','contracts'=>'contracts'][$type]))); ?>" class="btn btn-default btn-sm">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
