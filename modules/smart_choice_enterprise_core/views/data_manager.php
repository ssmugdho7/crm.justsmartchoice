<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div id="sc-enterprise-app"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="sc-enterprise-header"><div><h1><?php echo html_escape($title); ?></h1><p><?php echo html_escape(ucwords(str_replace('_',' ',preg_replace('/^sce_/','',$suffix)))); ?></p></div><div class="sc-toolbar">
<a class="btn btn-default btn-sm" href="<?php echo current_url(); ?>"><i class="fa-solid fa-rotate"></i> <?php echo _l('reload'); ?></a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('smart_choice_enterprise_core/sample_header/'.$suffix); ?>"><i class="fa-solid fa-file-arrow-down"></i> <?php echo _l('enterprise_sample_header'); ?></a>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('smart_choice_enterprise_core/export_table/'.$suffix); ?>"><i class="fa-solid fa-file-export"></i> <?php echo _l('export'); ?></a>
</div></div>
<?php if(!$exists){ ?><div class="alert alert-warning"><?php echo _l('action_required'); ?></div><?php } else { ?>
<div class="row"><div class="col-md-6"><form method="post" enctype="multipart/form-data" action="<?php echo admin_url('smart_choice_enterprise_core/import_table/'.$suffix); ?>" class="form-inline m-bottom-15">
<?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<input type="file" name="import_file" accept=".csv,text/csv" required class="form-control input-sm"> <button class="btn btn-primary btn-sm"><i class="fa-solid fa-file-import"></i> <?php echo _l('enterprise_import'); ?></button></form></div></div>
<form method="post" action="<?php echo admin_url('smart_choice_enterprise_core/mass_delete_table/'.$suffix); ?>" onsubmit="return confirm_delete();">
<?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
<div class="table-responsive sc-enterprise-data-wrap"><table class="table table-striped table-hover sc-enterprise-table"><thead><tr><th style="width:35px"><input type="checkbox" onclick="$('.sc-row-check').prop('checked',this.checked)"></th><?php foreach($fields as $field){ ?><th><?php echo html_escape(ucwords(str_replace('_',' ',$field))); ?></th><?php } ?></tr></thead><tbody>
<?php foreach($rows as $row){ ?><tr><td><?php if(isset($row['id'])){ ?><input class="sc-row-check" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"><?php } ?></td><?php foreach($fields as $field){ $value=$row[$field]??''; if(is_string($value)&&strlen($value)>180)$value=substr($value,0,180).'…'; ?><td class="sc-cell-wrap"><?php echo html_escape($value); ?></td><?php } ?></tr><?php } ?>
</tbody></table></div>
<?php if(has_permission(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME,'','delete')){ ?><button class="btn btn-danger btn-sm mtop15"><i class="fa-solid fa-trash"></i> <?php echo _l('enterprise_mass_delete'); ?></button><?php } ?>
</form><?php } ?>
</div></div></div></div></div><?php init_tail(); ?>
