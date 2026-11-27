<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); $selected=$item?array_column($item['assignees'],'staff_id'):[]; ?>
<div id="wrapper"><div class="content"><nav aria-label="Breadcrumb" class="mbot15"><ol class="breadcrumb"><li><a href="<?=admin_url()?>">Dashboard</a></li><li><a href="<?=admin_url('development')?>">CRM Repair Notes</a></li><li class="active" aria-current="page"><?=html_escape($title ?? $item['title'] ?? 'Note')?></li></ol><a class="btn btn-default btn-sm" href="<?=admin_url('development')?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to CRM Repair Notes</a></nav><div class="panel_s"><div class="panel-body"><h4 style="color:#174d37;font-weight:700;font-size:23px"><?=html_escape($title)?></h4><hr>
<?=form_open_multipart($item?admin_url('development/edit/'.$item['id']):admin_url('development/create'))?>
<div class="form-group"><label><?=_l('dev_title')?></label><input name="title" class="form-control" required value="<?=html_escape($item['title']??'')?>"></div>
<div class="form-group"><label><?=_l('dev_description')?></label><textarea id="development-description" name="description" class="form-control tinymce" rows="10" placeholder="<?=_l('dev_describe_problem')?>"><?=html_escape($item['description']??'')?></textarea></div>
<div class="form-group"><label><?=_l('dev_reference_url')?></label>
<div id="development-links">
<?php $links=$item?($item['reference_links']??[]):[]; if(!$links)$links=[''];foreach($links as $link){?>
<div class="input-group" style="margin-bottom:8px"><input type="url" name="reference_links[]" class="form-control" value="<?=html_escape($link)?>" placeholder="https://..."><span class="input-group-btn"><button type="button" class="btn btn-default dev-remove-link" aria-label="Remove link">&times;</button></span></div>
<?php }?>
</div><button type="button" id="development-add-link" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> <?=_l('dev_add_link')?></button>
</div>
<div class="form-group"><label><?=_l('dev_estimated_time')?></label><input type="number" min="0" step="0.25" name="estimated_hours" class="form-control" value="<?=html_escape($item['estimated_hours']??'')?>" placeholder="Hours"></div>
<?php if(is_admin()||staff_can('assign','development')){?><div class="form-group"><label><?=_l('dev_assigned_to')?></label><select name="assignees[]" class="selectpicker" multiple data-width="100%" data-live-search="true"><?php foreach($staff as $s){?><option value="<?=$s['staffid']?>" <?=in_array($s['staffid'],$selected)?'selected':''?>><?=html_escape(trim($s['firstname'].' '.$s['lastname']))?></option><?php }?></select></div><?php }?>
<div class="form-group"><label><?=_l('dev_attachments')?></label><input type="file" name="attachments[]" multiple class="form-control" ><small class="text-muted"><?=_l('dev_attachment_help')?></small></div>
<?php if($item && in_array($item['status'],['completed','approved'],true)){?><div class="form-group"><label><?=_l('dev_completed_by')?></label><select name="completed_by" class="form-control" required><option value="">—</option><?php foreach($staff as $s){?><option value="<?=(int)$s['staffid']?>" <?=((int)$s['staffid']===(int)$item['completed_by'])?'selected':''?>><?=html_escape($s['firstname'].' '.$s['lastname'])?></option><?php }?></select></div><?php }?><button class="btn btn-primary"><?=_l('dev_save')?></button> <a href="<?=admin_url('development')?>" class="btn btn-default"><?=_l('dev_cancel')?></a><?=form_close()?></div></div></div></div><script>
(function(){
var wrap=document.getElementById('development-links');var add=document.getElementById('development-add-link');
add.addEventListener('click',function(){if(wrap.querySelectorAll('input').length>=30)return;var div=document.createElement('div');div.className='input-group';div.style.marginBottom='8px';div.innerHTML='<input type="url" name="reference_links[]" class="form-control" placeholder="https://..."><span class="input-group-btn"><button type="button" class="btn btn-default dev-remove-link" aria-label="Remove link">&times;</button></span>';wrap.appendChild(div);});
wrap.addEventListener('click',function(e){var b=e.target.closest('.dev-remove-link');if(!b)return;var row=b.closest('.input-group');if(wrap.querySelectorAll('.input-group').length===1)row.querySelector('input').value='';else row.remove();});
})();
</script>
<script>
$(function(){
 if(typeof init_editor === 'function') { init_editor('#development-description'); }
});
</script><?php init_tail(); ?>