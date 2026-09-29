

/* Smart Choice 3.8.6 project upload/table and sales item numeric repair. */
(function($){
  'use strict';
  $(document).on('submit', 'form.invoice-form, form.estimate-form, form.proposal-form, form[data-type="invoice"], form[data-type="estimate"]', function(){
    $(this).find('input[name*="[rate]"],input[name*="[qty]"]').each(function(){
      var v=String($(this).val()||'').trim().replace(/[$\s]/g,'');
      if (v.indexOf(',')>-1 && v.indexOf('.')===-1) v=v.replace(',','.'); else v=v.replace(/,/g,'');
      if (v!=='' && !isNaN(Number(v))) $(this).val(Number(v));
    });
  });
  $(document).ajaxComplete(function(e,xhr,settings){
    if(settings && /projects\/upload_file\//.test(settings.url||'')){
      try { var r=typeof xhr.responseJSON==='object'?xhr.responseJSON:JSON.parse(xhr.responseText); if(r&&r.success){ setTimeout(function(){window.location.reload();},250); } } catch(ignore){}
    }
  });
})(jQuery);
