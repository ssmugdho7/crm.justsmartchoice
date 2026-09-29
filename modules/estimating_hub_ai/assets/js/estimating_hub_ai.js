(function($){
  'use strict';
  window.scieConfirmMassAction = function(formId){
    var form = document.getElementById(formId);
    if(!form || $(form).find('tbody input[type="checkbox"]:checked').length === 0){
      alert('Please select at least one record.');
      return false;
    }
    return confirm('Are you sure you want to delete the selected records?');
  };
  $(document).on('keyup input','.scie-filter',function(){
    var q=$(this).val().toLowerCase().trim();
    var scope=$(this).closest('.panel-body');
    var rows=scope.find('.scie-filter-row');
    rows.each(function(){ $(this).toggle(q==='' || $(this).text().toLowerCase().indexOf(q)!==-1); });
  });
  $(document).on('change','.scie-check-all',function(){
    var table=$(this).closest('table'); table.find('tbody input[type="checkbox"]').prop('checked',this.checked);
  });
  $(document).on('input','.scie-money',function(){
    var v=this.value.replace(/[^0-9.]/g,'');
    var parts=v.split('.');
    if(parts.length>2){v=parts[0]+'.'+parts.slice(1).join('');}
    var n=parseFloat(v||'0');
    if(n>99999.99){v='99999.99';}
    if(v.indexOf('.')>-1){ var p=v.split('.'); v=p[0]+'.'+p[1].slice(0,2); }
    this.value=v;
  });
})(jQuery);
