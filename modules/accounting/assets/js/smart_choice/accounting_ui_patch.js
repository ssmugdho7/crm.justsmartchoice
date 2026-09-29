(function($){
  'use strict';
  function esc(s){return String(s||'').replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
  function scButton(label, icon, cls, href){href=href||'#';return $('<a/>',{href:href,'class':'btn btn-default btn-xs '+(cls||''),html:'<i class="fa '+icon+'"></i> '+esc(label)});}
  function currentImportUrl(){var p=window.location.pathname + window.location.search;if(p.indexOf('vendors')!==-1){return admin_url+'accounting/vendor_import';}if(p.indexOf('budget')!==-1){return admin_url+'accounting/budget_import';}if(p.indexOf('banking')!==-1||p.indexOf('transaction')!==-1){return admin_url+'accounting/import_banking';}if(p.indexOf('chart_of_accounts')!==-1){return admin_url+'accounting/accounts_import';}if(p.indexOf('quickbooks')!==-1){return admin_url+'accounting/quickbooks';}return admin_url+'accounting/quickbooks';}
  function addToolbar(){
    $('.dataTables_length').each(function(){
      var $len=$(this); if($len.find('.smart-choice-table-actions').length){return;}
      var $wrap=$('<span class="smart-choice-table-actions"></span>');
      $wrap.append(scButton('Import','fa-upload','smart-choice-import-action',currentImportUrl()));
      $wrap.append(scButton('Sample Header','fa-table','smart-choice-sample-action',admin_url+'accounting/quickbooks'));
      $wrap.append(scButton('Refresh','fa-refresh','smart-choice-refresh-action'));
      $wrap.append(scButton('Mass Activate','fa-check','smart-choice-mass-activate-action'));
      $wrap.append(scButton('Mass Deactivate','fa-ban','smart-choice-mass-deactivate-action'));
      $wrap.append(scButton('Mass Delete','fa-trash','smart-choice-mass-action'));
      $len.append($wrap);
    });
  }
  function addColumnFilters(){
    $('table.dataTable').each(function(){
      var $table=$(this), id=$table.attr('id')||''; if($table.find('thead tr.smart-choice-column-filters').length){return;}
      var $head=$table.find('thead tr:first'); if(!$head.length){return;}
      var $filter=$('<tr class="smart-choice-column-filters"></tr>');
      $head.children('th').each(function(i){
        var title=$.trim($(this).text()).replace(/\s+/g,' ');
        if(!title || title.toLowerCase().indexOf('options')!==-1 || title.toLowerCase().indexOf('actions')!==-1){$filter.append('<th></th>');return;}
        $filter.append('<th><input type="text" class="form-control input-sm" placeholder="Filter '+esc(title)+'" data-column="'+i+'"></th>');
      });
      $table.find('thead').append($filter);
      $table.on('keyup change','thead tr.smart-choice-column-filters input',function(){
        var col=$(this).data('column'), val=this.value;
        try{ var api=$table.DataTable(); api.column(col).search(val).draw(); } catch(e){}
      });
    });
  }
  function showMassMessage(action){
    var modal=$('.bulk_actions:first,.bulk-actions:first,#bulk_actions_modal:first');
    if(modal.length){modal.modal('show');return;}
    if(typeof alert_float==='function'){alert_float('info', action+' is available for selected rows when your role permissions allow it.');}
    else{alert(action+' is available for selected rows when your role permissions allow it.');}
  }
  $(document).on('click','.smart-choice-refresh-action',function(e){e.preventDefault();try{$.fn.dataTable.tables({visible:true,api:true}).ajax.reload(null,false);}catch(err){window.location.reload();}});
  $(document).on('click','.smart-choice-mass-action',function(e){e.preventDefault();showMassMessage('Mass Delete');});
  $(document).on('click','.smart-choice-mass-activate-action',function(e){e.preventDefault();showMassMessage('Mass Activate');});
  $(document).on('click','.smart-choice-mass-deactivate-action',function(e){e.preventDefault();showMassMessage('Mass Deactivate');});
  $(document).ajaxComplete(function(){addToolbar();addColumnFilters();});
  $(document).ready(function(){setTimeout(function(){addToolbar();addColumnFilters();},500);setTimeout(function(){addToolbar();addColumnFilters();},1500);});
})(jQuery);
