<?php defined('BASEPATH') or exit('No direct script access allowed');
// Native rendering runs in this loader scope so $this, hooks, permissions and CSRF remain intact.
ob_start(); include APPPATH . 'views/' . $scaf_sales_native . '.php'; $scaf_native_html=ob_get_clean();
if($scaf_sales_kind==='credit_preview' && isset($credit_note)) {
 $scaf_status_html=format_credit_note_status($credit_note->status);
 $scaf_native_html=str_replace($scaf_status_html,str_replace('s-status','s-status scaf-credit-status-'.(int)$credit_note->status,$scaf_status_html),$scaf_native_html);
}
ob_start();
if($scaf_sales_kind==='credit_preview' && isset($credit_note)) {
 $scaf_sales_metrics=[$credit_note->currency_name=>['issued'=>$credit_note->total,'applied'=>$credit_note->credits_used,'available'=>(int)$credit_note->status===1?$credit_note->remaining_credits:'0','refunded'=>$credit_note->total_refunds]];
 include __DIR__.'/metrics.php';
}
if($scaf_sales_kind==='invoice_preview' && isset($invoice) && scaf_decimal_cmp((string)$invoice->total_left_to_pay,'0')===0 && (int)$invoice->status===2) { ?>
<p class="scaf-settled-ribbon"><i class="fa fa-check-circle" aria-hidden="true"></i> <?= e(app_format_money('0',$invoice->currency_name).' · '.scaf_sales_label('Balance settled','Saldo liquidado')); ?></p>
<?php }
if(in_array($scaf_sales_kind,['invoices','payments','credits'],true)) {include __DIR__.'/metrics.php';}
if($scaf_sales_kind==='proposal' && isset($proposal) && (int)$proposal->status===3 && !empty($proposal->signature)) { ?>
<section class="scaf-signature-ribbon" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i><div><strong><?= e(scaf_sales_label('Electronically signed acceptance','Aceptación firmada electrónicamente')); ?></strong><dl><dt><?= e(_l('contract_signed_by')); ?></dt><dd><?= e(trim(($proposal->acceptance_firstname??'').' '.($proposal->acceptance_lastname??''))); ?></dd><dt><?= e(_l('proposal_signed_date')); ?></dt><dd><?= e(_dt($proposal->acceptance_date)); ?></dd><dt><?= e(_l('proposal_signed_ip')); ?></dt><dd><?= e($proposal->acceptance_ip); ?></dd></dl></div></section>
<?php }
if($scaf_sales_kind==='receipt' && isset($payment)) { ?>
<div class="scaf-receipt-actions"><a class="btn btn-primary" target="_blank" rel="noopener" href="<?= e(admin_url('payments/pdf/'.(int)$payment->paymentid.'?print=true')); ?>"><i class="fa fa-print" aria-hidden="true"></i> <?= e(_l('print')); ?></a><a class="btn btn-default" href="<?= e(admin_url('payments/pdf/'.(int)$payment->paymentid)); ?>"><?= e(_l('download')); ?> PDF</a></div>
<?php }
$scaf_extra=ob_get_clean();
if($scaf_sales_kind==='invoices') {echo $scaf_native_html,$scaf_extra;}
elseif(in_array($scaf_sales_kind,['payments','credits','receipt'],true)) {
    $scaf_pattern='~(<div\s+class="content"[^>]*>)~';
    echo preg_replace_callback($scaf_pattern,function($m)use($scaf_extra){return $m[1].$scaf_extra;},$scaf_native_html,1);
} elseif(in_array($scaf_sales_kind,['proposal','credit_preview','invoice_preview'],true)) {echo $scaf_extra,$scaf_native_html;}
else {echo $scaf_native_html;}
