(function ($) {
 'use strict';
 var features=['invoices','estimates','proposals','contracts','credit_notes','payments'];
 var pending=new WeakSet();
 function featureFor(table){var input=table.querySelector('.sc-bulk-row[data-feature],.sc-bulk-select-all[data-feature]');return input?input.dataset.feature:'';}
 function tableFor(source){
  var bar=source.closest('.sc-bulk-actions');
  if(bar&&bar._scBulkTable&&bar._scBulkTable.isConnected)return bar._scBulkTable;
  var scope=source.closest('.dataTables_wrapper,.panel-body');
  if(!scope)return null;
  return Array.from(scope.querySelectorAll('table')).find(function(table){return features.includes(featureFor(table));})||null;
 }
 function notify(type,text){if(window.SCAF&&SCAF.toast)SCAF.toast(type,text);else if(typeof alert_float==='function')alert_float(type,text);}
 function update(table){
  var bar=table._scBulkBar;if(!bar)return;
  var rows=Array.from(table.querySelectorAll('.sc-bulk-row')).filter(function(row){return row.dataset.feature===featureFor(table);});
  var count=rows.filter(function(row){return row.checked;}).length;
  bar.querySelector('.sc-bulk-count').textContent=count?count+' selected':'';
  bar.querySelector('.sc-bulk-select-all').checked=rows.length>0&&count===rows.length;
  bar.querySelector('.sc-bulk-select-all').indeterminate=count>0&&count<rows.length;
 }
 async function deleteSelected(source){
  var table=tableFor(source);if(!table)return;
  var feature=featureFor(table);if(!features.includes(feature)||pending.has(table))return;
  var ids=Array.from(new Set(Array.from(table.querySelectorAll('.sc-bulk-row:checked')).filter(function(cb){return cb.dataset.feature===feature;}).map(function(cb){return cb.value;}).filter(function(id){return /^[1-9]\d*$/.test(id);})));
  if(!ids.length){notify('warning','Select at least one record first.');return;}
  pending.add(table);
  var release=window.SCAF&&SCAF.busy?SCAF.busy(source):function(){};
  try{
   if(!window.SCAF||!SCAF.confirm){notify('error','Confirmation is unavailable. Refresh the page before trying again.');return;}
   if(!await SCAF.confirm({title:'Delete selected records?',message:ids.length+' selected records. Existing server safeguards still apply.',typed:'DELETE',actionLabel:'Delete'}))return;
   var result=await $.ajax({url:admin_url+'smart_choice_bulk/delete/'+feature,type:'POST',data:{ids:ids,delete_confirmation:'DELETE'},dataType:'json'});
   if(!result||result.success!==true||!Number.isInteger(result.deleted)||result.deleted<1){notify('error','No deletion was confirmed.');return;}
   var failed=Array.isArray(result.failed)?result.failed.length:Math.max(0,ids.length-result.deleted);
   notify(failed?'warning':'success',result.deleted+' deleted'+(failed?' · '+failed+' not deleted':''));
   if($.fn.DataTable&&$.fn.DataTable.isDataTable(table))$(table).DataTable().ajax.reload(null,false);
  }catch(_){notify('error','Unable to confirm deletion. Refresh before retrying.');}
  finally{pending.delete(table);release();}
 }
 window.scBulkActions={deleteSelected:deleteSelected};
 function install(table){
  if(table._scBulkBar)return;
  var feature=featureFor(table);if(!features.includes(feature))return;
  var bar=document.createElement('div');bar.className='sc-bulk-actions tw-mb-2 tw-flex tw-items-center tw-gap-2';bar.id='sc-bulk-'+feature;
  bar.innerHTML='<label class="tw-flex tw-items-center tw-gap-1 tw-mb-0"><input type="checkbox" class="sc-bulk-select-all"> Select all</label><button type="button" class="btn btn-danger btn-sm sc-bulk-delete">Delete selected</button><span class="text-muted sc-bulk-count" role="status"></span>';
  bar.querySelector('input').dataset.feature=feature;bar._scBulkTable=table;table._scBulkBar=bar;
  var wrap=table.closest('.dataTables_wrapper');(wrap||table.parentNode).prepend(bar);update(table);
 }
 document.addEventListener('click',function(event){
  var source=event.target.closest('.sc-bulk-delete,.sales-center-mass-delete');
  if(!source||!tableFor(source))return;
  event.preventDefault();event.stopImmediatePropagation();deleteSelected(source);
 },true);
 $(document).on('change','.sc-bulk-select-all',function(){
  var table=this.closest('table')||tableFor(this);if(!table)return;
  var checked=this.checked,feature=featureFor(table);
  table.querySelectorAll('.sc-bulk-row').forEach(function(cb){if(cb.dataset.feature===feature)cb.checked=checked;});update(table);
 });
 $(document).on('change','.sc-bulk-row',function(){update(this.closest('table'));});
 function scan(){$('table').each(function(){if(this.querySelector('.sc-bulk-row,.sc-bulk-select-all')){install(this);update(this);}});}
 $(document).on('init.dt draw.dt',function(){setTimeout(scan,0);});$(scan);
})(jQuery);
