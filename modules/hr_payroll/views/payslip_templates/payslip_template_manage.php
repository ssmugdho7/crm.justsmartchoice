<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="payroll-hub-page"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="ph-hero"><div><h3><?= _l('payroll_template_gallery'); ?></h3><p><?= _l('payroll_template_gallery_help'); ?></p></div><a href="#" onclick="new_payslip_template();return false;" class="btn btn-default"><?= _l('new_payslip_template'); ?></a></div>
<div class="ph-template-grid">
<?php $styles=['Executive Blue','Contractor Green','Orange Ledger','Classic QuickBooks','Modern Payroll','Direct Deposit','Weekly Crew','Biweekly Professional','Salary Executive','Construction Field','Digital Minimal','Digital Gradient','Digital Compact','Digital Detailed','Digital Bilingual','Digital Project Cost','Digital Commission','Digital 1099','Digital Insurance','Digital Year To Date']; foreach($styles as $i=>$name): $colors=['#1f3c88','#169179','#f97316','#0e6f5b','#3598db']; $c=$colors[$i%count($colors)]; ?>
<div class="ph-template-card"><div class="ph-template-thumb" data-title="<?= html_escape($name); ?>"><div class="ph-template-sheet" style="--accent:<?= $c ?>"><div class="bar"></div><b>SMART CHOICE CONTRACTORS USA</b><div class="line"></div><div class="line" style="width:65%"></div><hr><b><?= _l('employee'); ?> / <?= _l('department'); ?> / ID</b><div class="line"></div><div class="line"></div><div class="totals"><b><?= _l('earnings'); ?> | <?= _l('deductions'); ?> | YTD | NET PAY</b><div class="line"></div></div></div></div><div class="ph-template-info"><strong><?= html_escape($name); ?></strong><small><?= $i<10 ? 'QuickBooks Print' : 'Digital PDF'; ?></small></div></div>
<?php endforeach; ?>
</div><hr>
<h4><?= _l('existing_payroll_templates'); ?></h4>
<?php render_datatable([_l('id'),_l('templates_name'),_l('staff_id_created'),_l('date_created')],'payslip-template-table'); ?>
</div></div></div></div>
<div class="modal fade" id="phTemplatePreviewModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 id="phTemplatePreviewTitle"></h4></div><div class="modal-body" id="phTemplatePreviewBody"></div></div></div></div>
<?php init_tail(); ?>
<?php require 'modules/hr_payroll/assets/js/payslip_templates/payslip_template_manage_js.php'; ?>
</body></html>
