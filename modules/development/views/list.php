<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="tw-mb-4"><h4 class="tw-m-0" style="color:#174d37;font-weight:700"><?=_l('dev_development')?></h4></div>
<style>
#development-repairs-table_wrapper .dev-search-column {display:flex;justify-content:flex-end;align-items:center;padding-right:15px;min-width:0}
#development-repairs-table_wrapper .dataTables_filter {display:flex;justify-content:flex-end;width:100%;margin:0 0 12px;text-align:right}
#development-repairs-table_wrapper .dataTables_filter label {position:relative;display:block;width:240px;max-width:100%;margin:0!important;padding:0!important;border:0!important;box-shadow:none!important;background:transparent!important;font-weight:normal}
#development-repairs-table_wrapper .dev-search-icon {position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#64748b;z-index:1}
#development-repairs-table_wrapper .dataTables_filter input[type=search] {display:block!important;box-sizing:border-box;width:100%!important;min-width:0;margin:0!important;padding:6px 12px 6px 36px!important;height:34px;border:1px solid #cbd5e1!important;border-radius:6px!important;background:#fff!important;box-shadow:none!important;outline:none!important}
#development-repairs-table_wrapper .dataTables_filter input[type=search]:focus {border-color:#64748b!important;box-shadow:none!important;outline:none!important}
#development-repairs-table_wrapper .dt-buttons {display:inline-flex;flex-wrap:wrap;align-items:center;gap:6px}
#development-repairs-table td,#development-repairs-table th{padding:6px;overflow-wrap:anywhere}
#development-repairs-table{width:100%!important}
#development-repairs-table td:last-child{min-width:100px}
#development-repairs-table .btn-xs{padding:2px 5px}
#development-repairs-table thead th.no-sort{cursor:default;background-image:none!important}
#development-repairs-table thead th.no-sort::before,#development-repairs-table thead th.no-sort::after{content:none!important}
@media(max-width:767px){#development-repairs-table_wrapper .dataTables_filter{text-align:right;width:100%}}
</style>
<table class="table" id="development-repairs-table"><thead><tr><th data-orderable="false" class="not-export no-sort" style="width:35px"><input type="checkbox" id="dev-select-all" aria-label="Select all repair notes"></th><th>#</th><th><?=_l('dev_problem')?></th><th><?=_l('dev_assigned_to')?></th><th><?=_l('dev_status')?></th><th><?=_l('dev_estimated_time')?></th><th><?=_l('dev_updated')?></th><th><?=_l('dev_done')?></th><th data-orderable="false" class="no-sort"><?=_l('dev_actions')?></th></tr></thead><tbody>
<?php foreach($items as $x){$as=$this->development_model->assignees($x['id']);?><tr><td><?php if(is_admin()||staff_can('delete','development')){?><input type="checkbox" class="dev-select-row" value="<?=(int)$x['id']?>" aria-label="Select repair <?=(int)$x['id']?>"><?php }?></td><td><?=$x['id']?></td><td><a href="<?=admin_url('development/view/'.$x['id'])?>"><?=html_escape($x['title'])?></a></td><td><?php foreach($as as $a){?><div><?=staff_profile_image((int)$a['staff_id'],['staff-profile-image-small'])?> <?=html_escape(trim($a['firstname'].' '.$a['lastname']))?></div><?php }?></td><td><span class="label <?=$x['status']==='completed'?'label-success':'label-info'?>"><?=html_escape(ucfirst($x['status']))?></span></td><td><?=$x['estimated_hours']?html_escape($x['estimated_hours']).' h':'—'?></td><td><?=html_escape($x['updated_at'])?></td><td><?=$x['status']==='completed'?'✓':'—'?></td><td><div style="display:flex;align-items:center;gap:3px;flex-wrap:wrap">
<a class="btn btn-info btn-xs" href="<?=admin_url('development/view/'.$x['id'])?>"><?=_l('dev_view')?></a>
<?php if(is_admin()||staff_can('edit','development')){?><a class="btn btn-default btn-xs" href="<?=admin_url('development/edit/'.$x['id'])?>"><?=_l('dev_edit')?></a><?php }?>
<?php if((is_admin()||staff_can('approve','development')) && $x['status']==='completed'){ echo form_open(admin_url('development/approve/'.(int)$x['id']), ['style'=>'display:inline;margin:0']); ?><button type="submit" class="btn btn-success btn-xs"><?=_l('dev_approve')?></button><?php echo form_close(); } ?>
<?php if(is_admin()||staff_can('delete','development')){ echo form_open(admin_url('development/delete/'.(int)$x['id']), ['class'=>'development-delete-form', 'style'=>'display:inline;margin:0', 'onsubmit'=>'return confirm('.html_escape(json_encode(_l('dev_confirm_delete'))).');']); ?><button type="submit" class="btn btn-danger btn-xs"><?=_l('dev_delete')?></button><?php echo form_close(); } ?>
</div></td></tr><?php }?>
</tbody></table>
<?php if(is_admin()||staff_can('delete','development')){ echo form_open(admin_url('development/bulk_delete'), ['id'=>'dev-bulk-delete-form','style'=>'display:none']); ?><input type="hidden" name="ids" id="dev-bulk-ids" value=""><?php echo form_close(); } ?>
</div></div></div></div><?php init_tail(); ?>
<script>
(function($){
 $(function(){
  var table=$('#development-repairs-table');
  var language=$.extend(true,{},app.lang.datatables);
  // One DataTables input, with a native Bootstrap/Font Awesome magnifier.
  language.search=language.sSearch='<i class="fa fa-search dev-search-icon" aria-hidden="true"></i>_INPUT_';
  appDataTableInline(table, {
   supportsButtons:true, supportsLoading:true, autoWidth:false,
   language:language,
   dom:"<'row'><'row'<'col-md-6'lB><'col-md-6 dev-search-column'f>r>t<'row'<'col-md-4'i><'col-md-8 dataTables_paging'<'#colvis'><'.dt-page-jump'>p>>",
   order:[[1,'asc']],
   columnDefs:[{targets:[0,8],orderable:false,searchable:false}]
  });
  var wrap=table.closest('.dataTables_wrapper');
  var toolbar=wrap.find('.dt-buttons').first();
  if(!toolbar.length){toolbar=$('<div class="dt-buttons"></div>').insertBefore(table.parent());}
  <?php if(is_admin()||staff_can('delete','development')){ ?>
  var button=$('<button type="button" class="btn btn-danger btn-sm" id="dev-bulk-delete-button"></button>').text(<?=json_encode(_l('dev_bulk_delete'))?>);
  toolbar.append(button);
  <?php } ?>
  <?php if(is_admin()||staff_can('create','development')){ ?>
  toolbar.append($('<a class="btn btn-primary btn-sm"></a>').attr('href',<?=json_encode(admin_url('development/create'))?>).text(<?=json_encode(_l('dev_new_repair'))?>));
  <?php } ?>
  <?php if(is_admin()||staff_can('delete','development')){ ?>
  $(document).on('change','#dev-select-all',function(){table.find('tbody .dev-select-row').prop('checked',this.checked);});
  table.on('draw.dt',function(){ $('#dev-select-all').prop('checked',false); });
  $(document).on('click','#dev-bulk-delete-button',function(){
   var selected=table.find('tbody .dev-select-row:checked').map(function(){return this.value;}).get();
   if(!selected.length){alert(<?=json_encode(_l('dev_bulk_select_first'))?>);return;}
   if(!confirm(<?=json_encode(_l('dev_bulk_confirm'))?>))return;
   $('#dev-bulk-ids').val(selected.join(','));$('#dev-bulk-delete-form').trigger('submit');
  });
  <?php } ?>
 });
})(jQuery);
</script>