<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$body = $row['body'] ?? '';
if ($body !== '') { $body = $CI->purchasing_hub_model->apply_merge_fields($body, $type, $row); }
$docTitle = $title ?? 'Purchasing Document';
$footer = $row['footer_note'] ?? '';
$lines = method_exists($CI->purchasing_hub_model, 'get_document_items') && !empty($row['id']) ? $CI->purchasing_hub_model->get_document_items($type, $row['id']) : [];
$vendorName = '';
if (!empty($row['vendor']) || !empty($row['vendor_id'])) {
    $vendor = $CI->purchasing_hub_model->get_vendor($row['vendor'] ?? $row['vendor_id']);
    $vendorName = $vendor['vendor_name'] ?? '';
}
$company = get_option('companyname');
$currency = get_base_currency();
$docNumber = $row['order_number'] ?? $row['quote_number'] ?? $row['bill_number'] ?? $row['subject'] ?? ('#' . ($row['id'] ?? ''));
$total = $row['total'] ?? $row['amount'] ?? $row['contract_value'] ?? 0;
?>
<style>
.ph-pdf{font-family:Arial,sans-serif;color:#1f2937;font-size:10.5px;line-height:1.35}.ph-pdf h1{font-size:20px;margin:0;color:#0f705e}.ph-pdf h3{font-size:12px;margin:0 0 6px;color:#0f705e}.ph-pdf .top{border-bottom:3px solid #f47b20;padding-bottom:10px;margin-bottom:12px}.ph-pdf .doc-title{float:right;text-align:right}.ph-pdf .box{border:1px solid #d9e2ec;border-radius:6px;padding:9px;margin-bottom:10px}.ph-pdf .cols{width:100%;border-collapse:collapse;margin-bottom:10px}.ph-pdf .cols td{width:50%;vertical-align:top;border:0;padding:0 6px}.ph-pdf table.items{width:100%;border-collapse:collapse;margin:8px 0}.ph-pdf table.items th{background:#0f705e;color:#fff;border:1px solid #0f705e;padding:6px;font-size:9.5px}.ph-pdf table.items td{border:1px solid #d9e2ec;padding:5px;font-size:9.5px}.ph-pdf .total{text-align:right;font-size:13px;font-weight:bold;color:#0f705e}.paid-stamp{border:3px solid #198754;color:#198754;font-size:22px;font-weight:bold;display:inline-block;padding:6px 14px;transform:rotate(-8deg);margin:8px 0}.pending-stamp{border:3px solid #f0ad4e;color:#f0ad4e;font-size:18px;font-weight:bold;display:inline-block;padding:6px 14px;margin:8px 0}.ph-footer-note{border-top:2px solid #0f705e;margin-top:12px;padding-top:8px;font-size:9.5px}.muted{color:#6b7280}.clear{clear:both}
</style>
<div class="ph-pdf">
  <div class="top">
    <div class="doc-title"><h1><?php echo html_escape($docTitle); ?></h1><div class="muted"><?php echo html_escape((string)$docNumber); ?></div></div>
    <h1><?php echo html_escape($company); ?></h1>
    <div><?php echo html_escape(get_option('company_phonenumber')); ?> | <?php echo html_escape(get_option('smtp_email') ?: get_option('company_email')); ?></div>
    <div><?php echo html_escape(get_option('companyaddress')); ?></div>
    <div class="clear"></div>
  </div>

  <table class="cols"><tr>
    <td><div class="box"><h3>Smart Choice Information</h3>
      <strong>Project:</strong> <?php echo html_escape($row['project_name'] ?? ''); ?><br>
      <strong>Client:</strong> <?php echo html_escape($row['client_name'] ?? ''); ?><br>
      <strong>Invoice:</strong> <?php echo html_escape($row['invoice_number'] ?? ''); ?><br>
      <strong>Date:</strong> <?php echo html_escape($row['order_date'] ?? $row['quote_date'] ?? $row['bill_date'] ?? date('Y-m-d')); ?>
    </div></td>
    <td><div class="box"><h3>Vendor Information</h3>
      <strong>Vendor:</strong> <?php echo html_escape($vendorName ?: ($row['vendor_name'] ?? 'Selected Vendor')); ?><br>
      <strong>Status:</strong> <?php echo html_escape($row['status'] ?? 'Draft'); ?><br>
      <strong>Expected:</strong> <?php echo html_escape($row['expected_date'] ?? ''); ?><br>
      <?php if($type === 'bills'){ echo (($row['payment_status'] ?? 'Pending') === 'Paid') ? '<span class="paid-stamp">PAID</span>' : '<span class="pending-stamp">PENDING</span>'; } ?>
    </div></td>
  </tr></table>

  <?php if(!empty($lines)){ ?>
    <table class="items">
      <thead><tr><th>Item Code</th><th>SKU</th><th>Description</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
      <tbody><?php foreach($lines as $line){ ?><tr>
        <td><?php echo html_escape($line['item_code'] ?? ''); ?></td>
        <td><?php echo html_escape($line['sku'] ?? ''); ?></td>
        <td><?php echo html_escape($line['description'] ?? ''); ?></td>
        <td><?php echo html_escape($line['unit'] ?? ''); ?></td>
        <td><?php echo html_escape((string)($line['quantity'] ?? '')); ?></td>
        <td><?php echo app_format_money((float)($line['unit_price'] ?? 0), $currency); ?></td>
        <td><?php echo app_format_money((float)($line['line_total'] ?? 0), $currency); ?></td>
      </tr><?php } ?></tbody>
    </table>
  <?php } else { ?>
    <div class="box"><strong>Summary:</strong> <?php echo html_escape($row['notes'] ?? 'No item lines were added.'); ?></div>
  <?php } ?>
  <div class="total">Total: <?php echo app_format_money((float)$total, $currency); ?></div>

  <?php if($body !== ''){ ?><div class="box"><?php echo $body; ?></div><?php } ?>
  <?php if($footer !== ''){ ?><div class="ph-footer-note"><strong>Vendor Instructions:</strong><br><?php echo nl2br(html_escape($footer)); ?></div><?php } ?>
</div>
