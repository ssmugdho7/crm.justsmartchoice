<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo html_escape($contract->subject); ?></title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;color:#17211b;margin:0;background:#f5f2ea;line-height:1.35}.contract-wrap{max-width:960px;margin:22px auto;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.12)}.ssc-cover-page{min-height:auto;background:linear-gradient(135deg,#173c2e,#5d8f6d);color:#fff;text-align:center;padding:12px 20px;margin:0 0 10px}.ssc-cover-card{border:2px solid rgba(255,255,255,.45);padding:10px 16px;background:rgba(255,255,255,.08)}.ssc-cover-logo{width:180px;max-height:180px;max-width:180px;height:auto;object-fit:contain;background:#fff;border-radius:10px;padding:4px;margin:0 auto 6px;display:block}.ssc-cover-card h1{font-size:21px;text-transform:uppercase;margin:0 0 3px}.ssc-cover-card h2{font-size:17px;margin:0 0 4px}.ssc-cover-card p{font-size:12px;margin:1px 0;line-height:1.2}.doc-body{padding:24px 38px}.doc-body h1,.doc-body h2,.doc-body h3{color:#245f46;page-break-after:avoid}.doc-body p{text-align:justify;margin:5px 0;line-height:1.35}.signature-section{margin-top:24px;border-top:2px solid #245f46;padding-top:18px}.signature-grid{display:table;width:100%;table-layout:fixed}.signature-cell{display:table-cell;width:50%;padding:10px;border:1px solid #ccc;vertical-align:top}.signature-line{height:58px;border-bottom:1px solid #111;margin-bottom:8px}.initial-line{height:28px;border-bottom:1px solid #111;width:120px}.no-print{text-align:center;padding:20px}@media print{body{background:#fff}.contract-wrap{margin:0;box-shadow:none;max-width:none}.no-print{display:none}}
</style>
</head>
<body>
<div class="contract-wrap">
    <div><?php echo $cover; ?></div>
    <div class="doc-body">
        <?php echo $content; ?>
        <div class="signature-section">
            <h2>Signatures And Initials</h2>
            <div class="signature-grid">
                <div class="signature-cell"><strong>Smart Choice Contractors USA</strong><div class="signature-line"><?php echo smartsource_signature_img($contract->company_signature ?? '', 'Company Signature'); ?></div><p>Initials: <?php echo html_escape($contract->company_initials ?? ''); ?></p><div class="initial-line"></div><p>Date: ______________________</p></div>
                <div class="signature-cell"><strong>Subcontractor</strong><div class="signature-line"><?php echo smartsource_signature_img($contract->subcontractor_signature ?? '', 'Subcontractor Signature'); ?></div><p>Initials: <?php echo html_escape($contract->subcontractor_initials ?? ''); ?></p><div class="initial-line"></div><p>Date: ______________________</p></div>
            </div>
        </div>
    </div>
    <p class="no-print"><button onclick="window.print()">Print / Save PDF</button></p>
</div>
</body>
</html>
