<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= html_escape($title); ?></title>
<link rel="stylesheet" href="<?= module_dir_url('cabinet_maker', 'assets/css/cabinet_maker.css'); ?>">
<style>body{margin:0;background:#f4f6f8;font-family:Arial,sans-serif}.cm-public{max-width:1200px;margin:30px auto;padding:0 16px}.cm-public-card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 4px 18px rgba(0,0,0,.08)}table{width:100%;border-collapse:collapse}th,td{padding:9px;border-bottom:1px solid #ddd;text-align:left}canvas{max-width:100%;border:1px solid #ddd}</style>
</head>
<body>
<div class="cm-public"><div class="cm-public-card">
<h1><?= html_escape($design->name); ?></h1>
<p>Smart Choice Contractors USA Cabinet Design</p>
<canvas id="cmCanvas" width="1100" height="620"></canvas>
<h2>Material List</h2>
<div style="overflow:auto"><table><thead><tr><th>Cabinet</th><th>Part</th><th>Qty</th><th>Width</th><th>Length</th><th>Thickness</th></tr></thead><tbody>
<?php foreach ($parts as $part) { ?>
<tr><td><?= html_escape($part->cabinet_uid); ?></td><td><?= html_escape($part->part_name); ?></td><td><?= (int) $part->qty; ?></td><td><?= html_escape($part->width); ?></td><td><?= html_escape($part->length); ?></td><td><?= html_escape($part->thickness); ?></td></tr>
<?php } ?>
</tbody></table></div>
</div></div>
<script>window.CABINET_MAKER_STATE=<?= $design->design_json ?: '{}'; ?>;</script>
<script src="<?= module_dir_url('cabinet_maker', 'assets/js/cabinet_maker.js'); ?>"></script>
</body></html>
