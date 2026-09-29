
(function(){
  function normalize(){
    var $w=window.jQuery||window.$; if(!$w) return;
    $w('.table-responsive,.dataTables_wrapper,.panel-table-full').css({'max-width':'100%','overflow-x':'hidden'});
    $w('table.dataTable, table.table').css({'width':'100%','max-width':'100%'});
    $w('a[target="_blank"][href*="/admin/"]').removeAttr('target');
    $w('.btn').each(function(){this.style.backgroundImage='none';});
  }
  if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',normalize);}else{normalize();}
  setTimeout(normalize,800); setTimeout(normalize,2000);
})();
