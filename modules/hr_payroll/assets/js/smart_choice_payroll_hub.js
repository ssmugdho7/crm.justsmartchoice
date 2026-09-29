
(function($){
'use strict';
$(function(){
  if (!$('a[href*="hr_payroll"], form[action*="hr_payroll"], .payroll-hub-settings').length) return;
  $('body').addClass('smart-choice-payroll-page');
  $(document).on('click','.sc-template-card',function(){
    var title=$(this).data('title')||'Payslip Template';
    var style=$(this).data('style')||'Executive';
    var html='<div class="modal fade" id="scPayrollTemplatePreview"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button class="close" data-dismiss="modal">&times;</button><h4>'+title+'</h4></div><div class="modal-body"><div style="border:1px solid #ddd;padding:28px;border-radius:10px"><div style="display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #169179;padding-bottom:15px"><div><h2 style="margin:0;color:#1f3c88">Smart Choice Contractors USA</h2><small>'+style+' Payroll Statement</small></div><div style="font-weight:bold;color:#d96b00">PAYSLIP</div></div><div class="row mtop20"><div class="col-md-6"><strong>Employee</strong><br>Sample Employee<br>CRM Staff ID: 001</div><div class="col-md-6 text-right"><strong>Pay Period</strong><br>Biweekly<br>Payment Date: 07/31/2026</div></div><table class="table table-bordered mtop20"><thead><tr><th>Earnings</th><th>Hours</th><th>Rate</th><th>Amount</th></tr></thead><tbody><tr><td>Regular Pay</td><td>80</td><td>$25.00</td><td>$2,000.00</td></tr><tr><td>Performance Incentives</td><td>-</td><td>-</td><td>$250.00</td></tr></tbody></table><div class="text-right"><strong>Net Pay: $1,842.50</strong></div></div></div></div></div></div>';
    $('#scPayrollTemplatePreview').remove(); $('body').append(html); $('#scPayrollTemplatePreview').modal('show');
  });
});
})(jQuery);
