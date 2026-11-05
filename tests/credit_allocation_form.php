<?php
define('BASEPATH',__DIR__);function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}function _l($k,...$x){return $k;}function admin_url($p){return '/admin/'.$p;}function form_open($u,$a){return '<form id="'.$a['id'].'" action="'.$u.'">';}function form_close(){return '</form>';}function format_invoice_number($id){return 'INV-'.$id;}function format_credit_note_number($id){return 'CN-'.$id;}function _d($v){return $v;}function app_format_money($v,$currency){return $currency.' '.$v;}function get_decimal_places(){return 2;}
$credit_note=(object)['id'=>1,'status'=>1,'remaining_credits'=>'100.00','currency_name'=>'USD','currencyid'=>1];$available_creditable_invoices=[['id'=>2,'date'=>'2026-11-05','total'=>'100','currency_name'=>'USD','total_left_to_pay'=>'40.00'],['id'=>3,'date'=>'2026-11-05','total'=>'300','currency_name'=>'USD','total_left_to_pay'=>'200.00']];
ob_start();include dirname(__DIR__).'/application/views/admin/credit_notes/apply_credits_to_invoices.php';$html=ob_get_clean();
if(!preg_match('/name="amount\[2\]"[^>]*min="0"[^>]*step="0.01"[^>]*max="40"/',$html))throw new RuntimeException('Invoice cap missing');
if(!preg_match('/name="amount\[3\]"[^>]*max="100"/',$html))throw new RuntimeException('Credit cap missing');
if(strpos($html,".on('input', '.apply-credits-field'")===false)throw new RuntimeException('Instant calculation missing');
echo "PASS native allocation form: invoice/credit max, nonnegative decimal steps and immediate calculation hook\n";
