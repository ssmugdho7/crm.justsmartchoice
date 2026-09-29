<?php defined('BASEPATH') or exit('No direct script access allowed');
$scpsType = $scps_type ?? 'invoice';
$scpsId = (int) ($scps_id ?? 0);
$meta = $scpsId ? scps_get_meta($scpsType, $scpsId) : null;
$percent = $meta ? (float) $meta->deposit_percent : 100;
$stage = $meta ? (string) $meta->payment_stage : 'full';
$discountMode = $meta && isset($meta->discount_mode) ? (string) $meta->discount_mode : 'percent';
$discountAmount = $meta && isset($meta->discount_amount) ? (float) $meta->discount_amount : 0;
$reasons = [
    '' => 'Nothing Selected', 'Senior Citizen' => 'Senior Citizen', 'AAA Member' => 'AAA Member',
    'Veteran' => 'Veteran', 'First Responder' => 'First Responder', 'Single Mother' => 'Single Mother',
    'Single Parent' => 'Single Parent', 'Disability' => 'Disability', 'Retiree' => 'Retiree',
    'Customer Referral' => 'Customer Referral', 'Repeat Customer' => 'Repeat Customer',
    'Loyalty Customer' => 'Loyalty Customer', 'Promotional' => 'Promotional', 'Employee' => 'Employee',
    'Contractor Courtesy' => 'Contractor Courtesy', 'Other' => 'Other'
];
?>
<div class="panel_s scps-panel" data-document-type="<?= e($scpsType); ?>">
  <div class="panel-body">
    <h4 class="scps-title"><i class="fa fa-percent"></i> Payment and Discounts</h4>
    <div class="row">
      <div class="col-md-3"><?= render_input('scps_deposit_percent', 'Deposit Percentage', $percent, 'number', ['min'=>0,'max'=>100,'step'=>'0.01']); ?></div>
      <div class="col-md-3"><div class="form-group"><label for="scps_payment_stage">Payment Stage</label><select id="scps_payment_stage" name="scps_payment_stage" class="selectpicker" data-width="100%"><option value="full" <?= $stage==='full'?'selected':''; ?>>Full Payment</option><option value="deposit" <?= $stage==='deposit'?'selected':''; ?>>Down Payment</option><option value="progress" <?= $stage==='progress'?'selected':''; ?>>Progress Payment</option><option value="final" <?= $stage==='final'?'selected':''; ?>>Final Payment</option></select></div></div>
      <div class="col-md-3"><div class="form-group"><label for="scps_discount_reason">Discount Type</label><select id="scps_discount_reason" name="scps_discount_reason" class="selectpicker" data-width="100%" data-live-search="true"><?php foreach($reasons as $value=>$label){ ?><option value="<?= e($value); ?>" <?= $meta && $meta->discount_reason===$value?'selected':''; ?>><?= e($label); ?></option><?php } ?></select></div></div>
      <div class="col-md-3"><div class="form-group"><label for="scps_discount_mode">Discount Calculation</label><select id="scps_discount_mode" name="scps_discount_mode" class="selectpicker" data-width="100%"><option value="percent" <?= $discountMode==='percent'?'selected':''; ?>>Percentage</option><option value="fixed" <?= $discountMode==='fixed'?'selected':''; ?>>Fixed Amount</option></select></div></div>
    </div>
    <div class="row">
      <div class="col-md-3"><?= render_input('scps_discount_amount', 'Discount Amount', $discountAmount, 'number', ['min'=>0,'step'=>'0.01']); ?></div>
      <div class="col-md-4"><?= render_input('scps_discount_title', 'Discount Title', $meta ? $meta->discount_title : '', 'text', ['placeholder'=>'Example: Veteran Appreciation Discount']); ?></div>
      <div class="col-md-5"><?= render_textarea('scps_discount_description', 'Discount Description', $meta ? $meta->discount_description : '', ['placeholder'=>'Explain why this discount is being provided']); ?></div>
    </div>
    <div class="checkbox checkbox-primary"><input type="checkbox" value="1" name="scps_auto_create_balance" id="scps_auto_create_balance_<?= e($scpsType); ?>" <?= !$meta || $meta->auto_create_balance ? 'checked' : ''; ?>><label for="scps_auto_create_balance_<?= e($scpsType); ?>">Create the remaining-balance invoice after the initial invoice is fully paid</label></div>
    <div class="scps-summary"><div><span>Contract Total</span><strong class="scps-contract">0.00</strong></div><div><span>Amount Due Now</span><strong class="scps-due">0.00</strong></div><div><span>Remaining Balance</span><strong class="scps-remaining">0.00</strong></div></div>
    <input type="hidden" name="scps_contract_total"><input type="hidden" name="scps_due_now"><input type="hidden" name="scps_remaining_balance">
  </div>
</div>
