(function($){
'use strict';
function featureFor($table){var $c=$table.find('.sc-bulk-row').first();return $c.data('feature')||$table.find('.sc-bulk-select-all').first().data('feature')||'';}
function install($table){
 if($table.data('sc-bulk-ready')) return;
 var feature=featureFor($table); if(!feature) return;
 $table.data('sc-bulk-ready',1);
 var id='sc-bulk-'+feature;
 var $wrap=$table.closest('.dataTables_wrapper');
 var $bar=$('<div class="sc-bulk-actions tw-mb-2 tw-flex tw-items-center tw-gap-2" id="'+id+'"><label class="tw-flex tw-items-center tw-gap-1 tw-mb-0"><input type="checkbox" class="sc-bulk-select-all" data-feature="'+feature+'"> '+(window.scBulkSelectAllText||'Select all')+'</label><button type="button" class="btn btn-danger btn-sm sc-bulk-delete"><i class="fa fa-trash"></i> '+(window.scBulkDeleteText||'Delete selected')+'</button> <span class="text-muted sc-bulk-count"></span></div>');
 ($wrap.length?$wrap:$table.parent()).prepend($bar);
 update($table);
}
function update($table){var n=$table.find('.sc-bulk-row:checked').length;$table.closest('.dataTables_wrapper').find('.sc-bulk-count').text(n?n+' selected':'');}
$(document).on('change','.sc-bulk-select-all',function(){var f=$(this).data('feature');var $t=$(this).closest('table');$t.find('.sc-bulk-row[data-feature="'+f+'"]').prop('checked',this.checked);update($t);});
$(document).on('change','.sc-bulk-row',function(){update($(this).closest('table'));});
$(document).on('click','.sc-bulk-delete',function(){var $bar=$(this).closest('.sc-bulk-actions'),$wrap=$bar.closest('.dataTables_wrapper'),$table=$wrap.find('table').first(),feature=featureFor($table),ids=[];$table.find('.sc-bulk-row:checked').each(function(){ids.push($(this).val());});if(!ids.length)return;if(!confirm(window.scBulkConfirmText||'Delete the selected records?'))return;$.post(admin_url+'smart_choice_bulk/delete/'+feature,{ids:ids},function(r){if(r&&r.success){if(typeof alert_float==='function')alert_float('success',(r.deleted||0)+' deleted');var dt=$table.DataTable();dt.ajax.reload(null,false);}else if(typeof alert_float==='function'){alert_float('danger','Unable to delete selected records');}},'json');});
function scan(){ $('table').each(function(){var $t=$(this);if($t.find('.sc-bulk-row,.sc-bulk-select-all').length) install($t);}); }
$(document).on('init.dt draw.dt',function(){setTimeout(scan,0);});$(scan);
})(jQuery);
