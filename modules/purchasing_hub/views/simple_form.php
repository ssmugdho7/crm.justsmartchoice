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
      <div class="col-md-12">
        <label>Contract Template</label>
        <select id="ph_contract_template" class="form-control selectpicker" data-live-search="true">
          <option value="">Select Template To Insert</option>
          <?php foreach(($contract_templates ?? []) as $tpl){ ?>
            <option value="<?php echo (int)$tpl['id']; ?>" data-body="<?php echo html_escape($tpl['body']); ?>"><?php echo html_escape($tpl['template_name']); ?></option>
          <?php } ?>
        </select>
      </div>
    <?php } ?>
    <?php if(in_array($type, ['orders','quotes'], true)){ ?>
      <div class="col-md-12">
        <h4 class="ph-section-title"><i class="fa fa-list"></i> Order / Quote Items</h4>
        <p class="text-muted">Select products from CRM Items or Purchasing Hub Items, then enter quantity and vendor pricing. This table is used for the PDF, vendor email, and purchasing reports.</p>
        <div class="table-responsive">
          <table class="table table-bordered ph-item-lines" id="ph_item_lines_table">
            <thead>
              <tr>
                <th style="width:26%">CRM Product</th>
                <th style="width:12%">Item Code</th>
                <th style="width:12%">SKU</th>
                <th>Description</th>
                <th style="width:8%">Unit</th>
                <th style="width:8%">Qty</th>
                <th style="width:10%">Unit Price</th>
                <th style="width:10%">Total</th>
                <th style="width:4%"></th>
              </tr>
            </thead>
            <tbody>
              <?php $lines = !empty($document_items) ? $document_items : [[]]; foreach($lines as $i => $line){ ?>
              <tr>
                <td>
                  <select name="items[<?php echo $i; ?>][crm_item_id]" class="form-control ph-item-select" data-live-search="true">
                    <option value="">Manual Entry</option>
                    <?php foreach(($crm_items ?? []) as $it){ ?>
                      <option value="<?php echo html_escape((string)$it['id']); ?>" data-code="<?php echo html_escape($it['item_code']); ?>" data-sku="<?php echo html_escape($it['sku']); ?>" data-desc="<?php echo html_escape($it['description']); ?>" data-unit="<?php echo html_escape($it['unit']); ?>" data-price="<?php echo html_escape((string)$it['unit_price']); ?>" <?php echo ((string)($line['crm_item_id'] ?? '') === (string)$it['id']) ? 'selected' : ''; ?>><?php echo html_escape($it['description']); ?> — <?php echo html_escape($it['source']); ?></option>
                    <?php } ?>
                  </select>
                </td>
                <td><input name="items[<?php echo $i; ?>][item_code]" class="form-control input-sm ph-code" value="<?php echo html_escape($line['item_code'] ?? ''); ?>"></td>
                <td><input name="items[<?php echo $i; ?>][sku]" class="form-control input-sm ph-sku" value="<?php echo html_escape($line['sku'] ?? ''); ?>"></td>
                <td><input name="items[<?php echo $i; ?>][description]" class="form-control input-sm ph-desc" value="<?php echo html_escape($line['description'] ?? ''); ?>"></td>
                <td><input name="items[<?php echo $i; ?>][unit]" class="form-control input-sm ph-unit" value="<?php echo html_escape($line['unit'] ?? 'ea'); ?>"></td>
                <td><input name="items[<?php echo $i; ?>][quantity]" type="number" step="0.01" class="form-control input-sm ph-qty" value="<?php echo html_escape($line['quantity'] ?? '1'); ?>"></td>
                <td><input name="items[<?php echo $i; ?>][unit_price]" type="number" step="0.01" class="form-control input-sm ph-price" value="<?php echo html_escape($line['unit_price'] ?? '0.00'); ?>"></td>
                <td><input class="form-control input-sm ph-total" readonly value="<?php echo html_escape($line['line_total'] ?? '0.00'); ?>"></td>
                <td><button type="button" class="btn btn-danger btn-xs ph-remove-line"><i class="fa fa-times"></i></button></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn-default btn-sm" id="ph_add_item_line"><i class="fa fa-plus"></i> Add Item</button>
        <span class="pull-right ph-document-total">Document Total: <strong id="ph_document_total">0.00</strong></span>
      </div>
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

function phReindexRows(){
  document.querySelectorAll('#ph_item_lines_table tbody tr').forEach(function(row, idx){
    row.querySelectorAll('input,select').forEach(function(el){ if(el.name){ el.name = el.name.replace(/items\[\d+\]/, 'items['+idx+']'); } });
  });
}
function phCalcLines(){
  var total=0;
  document.querySelectorAll('#ph_item_lines_table tbody tr').forEach(function(row){
    var q=parseFloat((row.querySelector('.ph-qty')||{}).value || 0);
    var p=parseFloat((row.querySelector('.ph-price')||{}).value || 0);
    var t=q*p; total += t;
    var out=row.querySelector('.ph-total'); if(out) out.value=t.toFixed(2);
  });
  var totalEl=document.getElementById('ph_document_total'); if(totalEl) totalEl.textContent=total.toFixed(2);
  var totalInput=document.querySelector('input[name="total"],input[name="amount"]'); if(totalInput && total>0) totalInput.value=total.toFixed(2);
}
document.addEventListener('change', function(e){
  if(e.target.classList.contains('ph-item-select')){
    var opt=e.target.options[e.target.selectedIndex]; var row=e.target.closest('tr'); if(opt && row){
      row.querySelector('.ph-code').value=opt.getAttribute('data-code')||'';
      row.querySelector('.ph-sku').value=opt.getAttribute('data-sku')||'';
      row.querySelector('.ph-desc').value=opt.getAttribute('data-desc')||'';
      row.querySelector('.ph-unit').value=opt.getAttribute('data-unit')||'ea';
      row.querySelector('.ph-price').value=parseFloat(opt.getAttribute('data-price')||0).toFixed(2);
      phCalcLines();
    }
  }
  if(e.target.id==='ph_contract_template'){
    var opt=e.target.options[e.target.selectedIndex]; var body=opt ? opt.getAttribute('data-body') : '';
    if(body){ if(typeof tinymce !== 'undefined' && tinymce.activeEditor){ tinymce.activeEditor.execCommand('mceInsertContent', false, body); } else { var ta=document.querySelector('textarea[name="body"]'); if(ta) ta.value += body; } }
  }
});
document.addEventListener('input', function(e){ if(e.target.classList.contains('ph-qty') || e.target.classList.contains('ph-price')) phCalcLines(); });
document.addEventListener('click', function(e){
  var remove=e.target.closest('.ph-remove-line'); if(remove){ remove.closest('tr').remove(); phReindexRows(); phCalcLines(); }
  if(e.target.id==='ph_add_item_line'){
    var tbody=document.querySelector('#ph_item_lines_table tbody'); var first=tbody ? tbody.querySelector('tr') : null; if(first){ var clone=first.cloneNode(true); clone.querySelectorAll('input').forEach(function(i){ i.value = i.classList.contains('ph-qty') ? '1' : (i.classList.contains('ph-unit') ? 'ea' : ''); }); clone.querySelectorAll('select').forEach(function(s){ s.selectedIndex=0; }); tbody.appendChild(clone); phReindexRows(); phCalcLines(); }
  }
});
setTimeout(phCalcLines, 300);

</script>
<?php $this->load->view('purchasing_hub/_footer'); ?>
