<?php
$scpsDocumentType = $scpsDocumentType ?? '';
$scpsDocumentId = isset($scpsDocumentId) ? (int) $scpsDocumentId : 0;
$schedule = $scpsDocumentId > 0 ? scps_get_schedule($scpsDocumentType, $scpsDocumentId) : null;
$deposit = $schedule ? (float) $schedule->deposit_percent : 50;
$stage = $schedule ? (string) $schedule->payment_stage : ($scpsDocumentType === 'invoice' ? 'deposit' : 'full');
$reason = $schedule ? (string) $schedule->discount_reason : '';
$reasons = ['senior_citizen','veteran','disability','retiree','customer_referral','loyalty_customer','promotional','employee','contractor_courtesy','other'];
?>
<div class="scps-panel" data-document-type="<?php echo html_escape($scpsDocumentType); ?>">
<div class="scps-title"><i class="fa-solid fa-money-check-dollar"></i> <?php echo _l('scps_payment_discount'); ?></div>
<div class="row">
<div class="col-md-4"><?php echo render_input('scps_deposit_percent','scps_deposit_percent',$deposit,'number',['min'=>0,'max'=>100,'step'=>'0.01']); ?></div>
<div class="col-md-4"><div class="form-group select-placeholder"><label><?php echo _l('scps_payment_stage'); ?></label><select name="scps_payment_stage" class="selectpicker" data-width="100%">
<option value="full" <?php echo $stage==='full'?'selected':''; ?>><?php echo _l('scps_full_payment'); ?></option>
<option value="deposit" <?php echo $stage==='deposit'?'selected':''; ?>><?php echo _l('scps_deposit_payment'); ?></option>
<option value="progress" <?php echo $stage==='progress'?'selected':''; ?>><?php echo _l('scps_progress_payment'); ?></option>
<option value="final" <?php echo $stage==='final'?'selected':''; ?>><?php echo _l('scps_final_payment'); ?></option>
</select></div></div>
<div class="col-md-4"><div class="form-group select-placeholder"><label><?php echo _l('scps_discount_reason'); ?></label><select name="scps_discount_reason" class="selectpicker scps-discount-reason" data-width="100%"><option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
<?php foreach ($reasons as $item) { ?><option value="<?php echo $item; ?>" <?php echo $reason===$item?'selected':''; ?>><?php echo _l('scps_discount_'.$item); ?></option><?php } ?>
</select></div></div>
</div>
<div class="row"><div class="col-md-8 scps-other-wrap <?php echo $reason==='other'?'':'hide'; ?>"><?php echo render_input('scps_discount_reason_other','scps_discount_reason_other',$reason==='other'?'':$reason); ?></div>
<div class="col-md-4"><div class="checkbox checkbox-primary mtop25"><input type="checkbox" name="scps_auto_create_balance" id="scps_auto_create_balance" value="1" <?php echo !$schedule || $schedule->auto_create_balance?'checked':''; ?>><label for="scps_auto_create_balance"><?php echo _l('scps_auto_create_balance'); ?></label></div></div></div>
<div class="scps-live-summary"><span><?php echo _l('scps_contract_total'); ?>: <b class="scps-contract-total">—</b></span><span><?php echo _l('scps_due_now'); ?>: <b class="scps-due-now">—</b></span><span><?php echo _l('scps_remaining_balance'); ?>: <b class="scps-remaining">—</b></span></div>
</div>
