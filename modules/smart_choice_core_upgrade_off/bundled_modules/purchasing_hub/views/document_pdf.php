<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$body = $row['body'] ?? '';
if ($body !== '') { $body = $CI->purchasing_hub_model->apply_merge_fields($body, $type, $row); }
$docTitle = $title ?? 'Purchasing Document';
$footer = $row['footer_note'] ?? '';
?>
<style>
.ph-pdf{font-family:Arial,sans-serif;color:#222}.ph-pdf h1{color:#0f705e;border-bottom:3px solid #f47b20;padding-bottom:10px}.ph-pdf table{width:100%;border-collapse:collapse;margin:12px 0}.ph-pdf th,.ph-pdf td{border:1px solid #ddd;padding:8px;text-align:left}.ph-pdf th{background:#f6f8f6}.ph-cover{border:2px solid #0f705e;padding:20px;margin-bottom:20px}.paid-stamp{border:4px solid #198754;color:#198754;font-size:34px;font-weight:bold;display:inline-block;padding:8px 18px;transform:rotate(-8deg);margin:10px 0}.pending-stamp{border:4px solid #f0ad4e;color:#f0ad4e;font-size:26px;font-weight:bold;display:inline-block;padding:8px 18px;margin:10px 0}.ph-footer-note{border-top:2px solid #0f705e;margin-top:20px;padding-top:12px;font-size:12px;}
</style>
<div class="ph-pdf">
  <div class="ph-cover">
    <h1><?php echo html_escape($docTitle); ?></h1>
    <p><strong>Company:</strong> <?php echo html_escape(get_option('companyname')); ?></p>
    <p><strong>Date:</strong> <?php echo html_escape(date('Y-m-d')); ?></p>
    <?php if($type === 'bills'){ ?>
      <div><?php echo (($row['payment_status'] ?? 'Pending') === 'Paid') ? '<span class="paid-stamp">PAID</span>' : '<span class="pending-stamp">PENDING PAYMENT</span>'; ?></div>
    <?php } ?>
  </div>
  <table>
    <?php foreach($row as $key => $value){ if(in_array($key, ['body','footer_note'], true) || is_array($value)) continue; ?>
      <tr><th><?php echo html_escape(ucwords(str_replace('_',' ', $key))); ?></th><td><?php echo html_escape((string)$value); ?></td></tr>
    <?php } ?>
  </table>
  <?php if($body !== ''){ ?><div><?php echo $body; ?></div><?php } ?>
  <?php if($footer !== ''){ ?><div class="ph-footer-note"><strong>Vendor Instructions:</strong><br><?php echo nl2br(html_escape($footer)); ?></div><?php } ?>
</div>
