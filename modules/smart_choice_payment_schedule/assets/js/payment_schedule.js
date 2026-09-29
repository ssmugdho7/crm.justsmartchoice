(function($){'use strict';
function moneyNumber(text){var n=parseFloat(String(text||'').replace(/[^0-9.-]/g,''));return isNaN(n)?0:n;}
function update(){ $('.scps-panel').each(function(){var $p=$(this),pct=parseFloat($p.find('[name="scps_deposit_percent"]').val())||0,total=0;var candidates=['.total','[data-total]','.invoice-total','.estimate-total','.proposal-total'];for(var i=0;i<candidates.length;i++){var $e=$(candidates[i]).last();if($e.length){total=moneyNumber($e.data('total')||$e.text());if(total)break;}}var due=total*(Math.max(0,Math.min(100,pct))/100),rem=total-due;$p.find('.scps-contract-total').text(total?total.toFixed(2):'—');$p.find('.scps-due-now').text(total?due.toFixed(2):'—');$p.find('.scps-remaining').text(total?rem.toFixed(2):'—');});}
$(document).on('change keyup','[name="scps_deposit_percent"],input[name^="newitems"],input[name^="items"]',update);
$(document).on('change','.scps-discount-reason',function(){$(this).closest('.scps-panel').find('.scps-other-wrap').toggleClass('hide',this.value!=='other');});
$(function(){update();setTimeout(update,700);setTimeout(update,1800);});
})(jQuery);
(function($){
$(function(){
 if($('body').find('form').length && /admin\/credit_notes\/(credit_note|edit|new)/.test(window.location.pathname) && !$('.scps-panel').length){
   var html='<div class="scps-panel"><div class="scps-title"><i class="fa-solid fa-tag"></i> Discount Information</div><div class="row"><div class="col-md-6"><label>Discount Reason</label><select name="scps_discount_reason" class="form-control"><option value="">Select</option><option value="senior_citizen">Senior Citizen</option><option value="veteran">Veteran</option><option value="disability">Disability</option><option value="retiree">Retiree</option><option value="customer_referral">Customer Referral</option><option value="loyalty_customer">Loyalty Customer</option><option value="promotional">Promotional</option><option value="other">Other</option></select></div><div class="col-md-6"><label>Custom Discount Reason</label><input class="form-control" name="scps_discount_reason_other"></div></div></div>';
   var $target=$('.accounting-template .panel-body').first(); if($target.length){$target.append(html);}
 }
});
})(jQuery);
