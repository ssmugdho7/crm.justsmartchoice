(function($){
'use strict';
$(function(){
 var m=window.SmartChoiceSalesMeta||{};
 if(!m || !m.contract_total){return;}
 var money=function(v){return parseFloat(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});};
 var discount=[m.discount_category||'',m.discount_reason||''].filter(Boolean).join(' — ');
 var html='<div class="panel_s sc-customer-payment-card"><div class="panel-body">'+
 '<h4>Payment Schedule</h4><div class="sc-customer-payment-grid">'+
 '<div><span>Contract Total</span><strong>'+money(m.contract_total)+'</strong></div>'+
 '<div><span>Down Payment</span><strong>'+money(m.amount_due_now)+' ('+money(m.down_payment_percent)+'%)</strong></div>'+
 '<div><span>Remaining Balance</span><strong>'+money(m.remaining_balance)+'</strong></div>'+
 (discount?'<div><span>Discount</span><strong>'+ $('<div>').text(discount).html()+'</strong></div>':'')+
 '</div></div></div>';
 var $target=$('.panel_s').first();
 if($target.length){$target.after(html);}else{$('body').prepend(html);}
});
})(jQuery);
