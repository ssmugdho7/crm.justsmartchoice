<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Directory</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111111;margin:24px;background:#fff}.print{margin-bottom:15px}.header{display:flex;align-items:center;justify-content:space-between;border-bottom:4px solid #00A651;padding:0 0 14px;margin-bottom:16px}.brand-wrap{display:flex;align-items:center;gap:12px}.brand-logo{max-height:54px;max-width:220px}.brand{font-size:21px;font-weight:800;color:#007A3D;line-height:1.15}.meta{text-align:right;font-size:11px;color:#555555}.accent{height:6px;background:linear-gradient(90deg,#0077CC,#2CA8FF,#00A651,#F5B400,#F96302);border-radius:999px;margin-bottom:16px}.summary{background:#F5F5F5;border-left:4px solid #F96302;padding:8px 10px;margin-bottom:12px;font-size:12px}table{width:100%;border-collapse:collapse;font-size:10.5px}th{background:#0077CC;color:#fff;padding:7px;text-align:left;border-right:1px solid rgba(255,255,255,.35)}td{border-bottom:1px solid #CFCFCF;padding:6px;vertical-align:top}tr:nth-child(even) td{background:#F5F5F5}.badge{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;padding:2px 5px;border-radius:10px;font-size:9px}.footer{margin-top:18px;font-size:10px;color:#555555;border-top:1px solid #CFCFCF;padding-top:8px}@media print{.print{display:none}body{margin:0}.header{break-inside:avoid}table{page-break-inside:auto}tr{page-break-inside:avoid;page-break-after:auto}}
    </style>
</head>
<body>
<button class="print" onclick="window.print()">Print / Save PDF</button>
<div class="header">
    <div class="brand-wrap">
        <?php $logo = get_option('company_logo'); ?>
        <?php if (!empty($logo)) { ?><img class="brand-logo" src="<?php echo base_url('uploads/company/' . $logo); ?>" alt="Smart Choice Contractors USA"><?php } ?>
        <div class="brand">Smart Choice Contractors USA<br><span style="font-size:13px;color:#555555;font-weight:700;">Supplier Directory</span></div>
    </div>
    <div class="meta">Generated <?php echo date('m/d/Y h:i A'); ?><br><?php echo (int)($selected_count ?? count($suppliers)); ?> supplier records<br>CRM Supplier Module</div>
</div>
<div class="accent"></div>
<div class="summary">This supplier export was generated from the Smart Choice CRM supplier directory. Selected records are included when rows were checked before export.</div>
<table>
    <thead><tr><th>Supplier</th><th>Website</th><th>Phone</th><th>Email</th><th>Trade</th><th>Description / Notes</th></tr></thead>
    <tbody><?php foreach($suppliers as $s){ ?><tr><td><strong><?php echo html_escape($s['supplier_name']); ?></strong><br><span class="badge"><?php echo html_escape($s['supplier_type']); ?></span></td><td><?php echo html_escape($s['website']); ?></td><td><?php echo html_escape($s['phone']); ?></td><td><?php echo html_escape($s['email']); ?></td><td><?php echo html_escape($s['trade']); ?></td><td><?php echo html_escape($s['short_description']); ?><br><small><?php echo html_escape($s['notes']); ?></small></td></tr><?php } ?></tbody>
</table>
<div class="footer">Smart Choice Contractors USA | Supplier Directory Export</div>
</body>
</html>
