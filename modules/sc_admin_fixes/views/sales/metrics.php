<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="scaf-sales-metrics" aria-label="<?= e(scaf_sales_label('Financial overview','Resumen financiero')); ?>">
<?php if($scaf_sales_kind==='invoices') { ?>
<h3><?= e(scaf_sales_label('Financial velocity · Fiscal year','Velocidad financiera · Año fiscal').' '.implode(', ',$scaf_sales_metrics['years'])); ?></h3>
<p class="text-muted"><?= e(scaf_sales_label('Invoice-date cohort. Cash received excludes credit allocations; current includes balances not yet due and up to 30 days overdue.','Cohorte por fecha de factura. El efectivo recibido excluye créditos; actual incluye saldos aún no vencidos y vencidos hasta 30 días.')); ?></p>
<?php $scaf_groups=$scaf_sales_metrics['groups']; } else {$scaf_groups=$scaf_sales_metrics;}
foreach($scaf_groups as $currency=>$g) { ?>
<div class="scaf-currency-section"><h4><?= e($currency); ?></h4><div class="scaf-sales-kpis">
<?php
if($scaf_sales_kind==='invoices') {
 $cards=[[_l('paid_invoices'),$g['paid']], [scaf_sales_label('Past due >30 days','Vencido >30 días'),$g['past_due']],[_l('balance_due'),$g['due']]];
} elseif($scaf_sales_kind==='payments') {$cards=[[scaf_sales_label('Reconciled cash recorded','Efectivo conciliado registrado'),$g['total']]];}
else {$cards=[[scaf_sales_label('Credits issued · non-void','Créditos emitidos · no anulados'),$g['issued']],[_l('credits_used'),$g['applied']],[scaf_sales_label('Unallocated available credit','Crédito disponible sin asignar'),$g['available']],[_l('refunds'),$g['refunded']]];}
foreach($cards as [$label,$amount]) { ?><div class="scaf-sales-kpi"><span><?= e($label); ?></span><strong><?= e(app_format_money($amount,$currency)); ?></strong></div><?php } ?>
<?php if($scaf_sales_kind==='invoices') { ?><div class="scaf-sales-kpi"><span><?= e(scaf_sales_label('Collection velocity','Velocidad de cobro')); ?></span><strong><?= e(scaf_sales_percent($g['paid'],$g['issued'])); ?>%</strong><small><?= e($g['paid_count'].' '.scaf_sales_label('settled','liquidadas').' · '.$g['due_count'].' '.scaf_sales_label('outstanding','pendientes')); ?></small></div><?php } ?>
</div>
<?php if($scaf_sales_kind==='invoices') { $labels=[scaf_sales_label('Current / 0–30 days','Actual / 0–30 días'),'31–60 '.scaf_sales_label('days overdue','días vencidos'),'61–90 '.scaf_sales_label('days overdue','días vencidos'),'90+ '.scaf_sales_label('days critical','días críticos')]; ?>
<div class="scaf-aging" aria-label="<?= e(scaf_sales_label('Accounts receivable aging','Antigüedad de cuentas por cobrar')); ?>"><div class="scaf-aging-bar" aria-hidden="true"><?php foreach($g['aging'] as $i=>$amount){ ?><span class="scaf-aging-<?= $i; ?>" style="width:<?= e(scaf_sales_percent($amount,$g['due'])); ?>%"></span><?php } ?></div><ul><?php foreach($g['aging'] as $i=>$amount){ ?><li><i class="scaf-aging-<?= $i; ?>"></i><?= e($labels[$i]); ?><strong><?= e(app_format_money($amount,$currency)); ?></strong><span><?= e(scaf_sales_percent($amount,$g['due'])); ?>%</span></li><?php } ?></ul></div>
<?php } elseif($scaf_sales_kind==='payments') { ?><p><?= e($g['count'].' '.scaf_sales_label('transactions · recorded ledger amounts; refunds are tracked separately','transacciones · importes del registro; los reembolsos se registran por separado')); ?></p><ul class="scaf-payment-distribution"><?php foreach($g['modes'] as $mode=>$amount){ ?><li><span><?= e($mode); ?></span><strong><?= e(app_format_money($amount,$currency)); ?></strong><span><?= e(scaf_sales_percent($amount,$g['total'])); ?>%</span></li><?php } ?></ul><?php } ?>
</div><?php } if(!$scaf_groups){ ?><p><?= e(scaf_sales_label('No financial records available in your permitted scope.','No hay registros financieros en su ámbito autorizado.')); ?></p><?php } ?>
</section>
