<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<?php
$row = isset($row) && is_array($row) ? $row : [];
$action = !empty($row['id']) ? admin_url('purchasing_hub/simple/'.$type.'/'.$row['id']) : admin_url('purchasing_hub/simple/'.$type);
$footer = $row['footer_note'] ?? ($default_po_footer ?? '');
?>
<div class="panel_s"><div class="panel-body">
  <?php echo form_open($action); ?>
  <div class="row">
    <?php if($type==='orders'){ ?>
      <div class="col-md-3"><?php echo render_input('order_number', 'Order Number', $row['order_number'] ?? ''); ?></div>
      <div class="col-md-3">
        <label>Project</label>
        <select name="project_id" class="form-control selectpicker" data-live-search="true">
          <option value="">Select Project</option>
          <?php foreach(($projects ?? []) as $p){ ?><option value="<?php echo (int)$p['id']; ?>" <?php echo ((int)($row['project_id'] ?? 0) === (int)$p['id']) ? 'selected' : ''; ?> data-name="<?php echo html_escape($p['name']); ?>"><?php echo html_escape($p['name']); ?></option><?php } ?>
        </select>
        <input type="hidden" name="project_name" id="ph_project_name" value="<?php echo html_escape($row['project_name'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label>Client</label>
        <select name="client_id" class="form-control selectpicker" data-live-search="true">
          <option value="">Select Client</option>
          <?php foreach(($clients ?? []) as $c){ ?><option value="<?php echo (int)$c['userid']; ?>" <?php echo ((int)($row['client_id'] ?? 0) === (int)$c['userid']) ? 'selected' : ''; ?> data-name="<?php echo html_escape($c['company']); ?>"><?php echo html_escape($c['company']); ?></option><?php } ?>
        </select>
        <input type="hidden" name="client_name" id="ph_client_name" value="<?php echo html_escape($row['client_name'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label>Invoice</label>
        <select name="invoice_id" class="form-control selectpicker" data-live-search="true">
          <option value="">No Invoice</option>
          <?php foreach(($invoices ?? []) as $inv){ $label = format_invoice_number($inv['id']); ?><option value="<?php echo (int)$inv['id']; ?>" <?php echo ((int)($row['invoice_id'] ?? 0) === (int)$inv['id']) ? 'selected' : ''; ?> data-name="<?php echo html_escape($label); ?>"><?php echo html_escape($label); ?> - <?php echo app_format_money($inv['total'], get_base_currency()); ?></option><?php } ?>
        </select>
        <input type="hidden" name="invoice_number" id="ph_invoice_number" value="<?php echo html_escape($row['invoice_number'] ?? ''); ?>">
      </div>
      <div class="col-md-3"><?php echo render_input('status', 'Status', $row['status'] ?? 'Draft'); ?></div>
      <div class="col-md-3"><?php echo render_date_input('order_date', 'Order Date', $row['order_date'] ?? ''); ?></div>
      <div class="col-md-3"><?php echo render_date_input('expected_date', 'Expected Date', $row['expected_date'] ?? ''); ?></div>
      <div class="col-md-3"><?php echo render_input('total', 'Total', $row['total'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
      <div class="col-md-6"><?php echo render_textarea('shipping_address', 'Shipping Address', $row['shipping_address'] ?? '', ['rows'=>4]); ?></div>
      <div class="col-md-6"><?php echo render_textarea('footer_note', 'Vendor Footer Note', $footer, ['rows'=>4]); ?></div>
      <div class="col-md-12"><div class="checkbox checkbox-primary"><input type="checkbox" name="receipt_requested" id="receipt_requested" value="1" <?php echo !empty($row['receipt_requested']) ? 'checked' : ''; ?>><label for="receipt_requested">Ask vendor to confirm the email/order was received</label></div></div>
    <?php } elseif($type==='bills'){ ?>
      <div class="col-md-4"><?php echo render_input('bill_number', 'Bill Number', $row['bill_number'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('project_name', 'Project', $row['project_name'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('status', 'Status', $row['status'] ?? 'Open'); ?></div>
      <div class="col-md-4"><label>Payment Status</label><select name="payment_status" class="form-control"><option value="Pending" <?php echo (($row['payment_status'] ?? '') !== 'Paid') ? 'selected' : ''; ?>>Pending</option><option value="Paid" <?php echo (($row['payment_status'] ?? '') === 'Paid') ? 'selected' : ''; ?>>Paid</option></select></div>
      <div class="col-md-4"><?php echo render_date_input('bill_date', 'Bill Date', $row['bill_date'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_date_input('due_date', 'Due Date', $row['due_date'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('amount', 'Amount', $row['amount'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
      <div class="col-md-4"><?php echo render_input('amount_paid', 'Amount Paid', $row['amount_paid'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
    <?php } elseif($type==='quotes'){ ?>
      <div class="col-md-4"><?php echo render_input('quote_number', 'Quote Number', $row['quote_number'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('project_name', 'Project', $row['project_name'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('status', 'Status', $row['status'] ?? 'Received'); ?></div>
      <div class="col-md-4"><?php echo render_date_input('quote_date', 'Quote Date', $row['quote_date'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('amount', 'Amount', $row['amount'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
    <?php } else { ?>
      <div class="col-md-4"><?php echo render_input('subject', 'Subject', $row['subject'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('project_name', 'Project', $row['project_name'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('contract_type', 'Contract Type', $row['contract_type'] ?? ''); ?></div>
      <div class="col-md-4"><?php echo render_input('status', 'Status', $row['status'] ?? 'Draft'); ?></div>
      <div class="col-md-4"><?php echo render_input('contract_value', 'Contract Value', $row['contract_value'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
    <?php } ?>
    <div class="col-md-12"><?php echo render_textarea('notes', 'Notes', $row['notes'] ?? '', ['rows'=>4]); ?></div>
    <?php if($type==='contracts' || $type==='orders'){ ?>
      <div class="col-md-12">
        <label>Document Text / Contract Body</label>
        <div class="alert alert-info">Use merge fields from the list below. Example: <strong>{company_name}</strong>, <strong>{vendor_vendor_name}</strong>, <strong>{project_name}</strong>, <strong>{client_name}</strong>, <strong>{invoice_number}</strong>, <strong>{current_date}</strong>.</div>
        <?php echo render_textarea('body', '', $row['body'] ?? '', ['class'=>'tinymce', 'rows'=>18]); ?>
      </div>
      <div class="col-md-12">
        <h4>Available Merge Fields</h4>
        <div class="purchasing-merge-fields">
          <?php foreach(($merge_fields ?? []) as $field => $value){ ?>
            <button type="button" class="btn btn-default btn-xs purchasing-merge-field" data-field="<?php echo html_escape($field); ?>"><?php echo html_escape($field); ?></button>
          <?php } ?>
        </div>
      </div>
    <?php } ?>
  </div>
  <button type="submit" class="btn btn-primary btn-sm">Save</button>
  <a href="<?php echo admin_url('purchasing_hub/' . ['orders'=>'purchase_orders','bills'=>'accounts_payable','quotes'=>'vendor_quotes','contracts'=>'contracts'][$type]); ?>" class="btn btn-default btn-sm">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<script>
document.addEventListener('click', function(e){
  if(!e.target.classList.contains('purchasing-merge-field')) return;
  var field = e.target.getAttribute('data-field');
  if (typeof tinymce !== 'undefined' && tinymce.activeEditor) { tinymce.activeEditor.execCommand('mceInsertContent', false, field); return; }
  var textarea = document.querySelector('textarea[name="body"]');
  if (textarea) { textarea.value += field; }
});
function phSyncSelect(selectName, targetId){
  var select = document.querySelector('select[name="'+selectName+'"]');
  var target = document.getElementById(targetId);
  if(!select || !target) return;
  var option = select.options[select.selectedIndex];
  target.value = option ? (option.getAttribute('data-name') || option.text || '') : '';
}
document.addEventListener('change', function(e){
  if(e.target.name === 'project_id') phSyncSelect('project_id','ph_project_name');
  if(e.target.name === 'client_id') phSyncSelect('client_id','ph_client_name');
  if(e.target.name === 'invoice_id') phSyncSelect('invoice_id','ph_invoice_number');
});
</script>
<?php $this->load->view('purchasing_hub/_footer'); ?>
