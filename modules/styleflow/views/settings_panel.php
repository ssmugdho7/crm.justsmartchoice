<?php defined('BASEPATH') or exit('No direct script access allowed');
styleflow_ensure_ready();
$templates = styleflow_get_templates();
$fonts = styleflow_supported_fonts();
$staffRows = styleflow_staff_options();
$photoModes = styleflow_staff_photo_mode_options();
?>
<div class="panel_s"><div class="panel-body">
<h4 class="tw-mt-0"><i class="fa fa-palette"></i> <?php echo _l('styleflow_settings'); ?></h4>
<p class="text-muted"><?php echo _l('styleflow_settings_panel_help'); ?></p>
<hr class="hr-panel-separator">
<h4><?php echo _l('styleflow_document_assignment'); ?></h4>
<p class="text-muted"><?php echo _l('styleflow_document_assignment_help'); ?></p>
<?php echo form_open(admin_url('styleflow/save_document_assignments')); ?>
<div class="row">
<?php foreach (['invoice','estimate','proposal'] as $sfType):
    $sfRows=[];
    foreach (styleflow_get_templates($sfType) as $sfTpl) { if ($sfTpl['slug'] === 'default') continue; $sfRows[]=['id'=>$sfTpl['slug'],'name'=>styleflow_template_display_name($sfTpl)]; }
    array_unshift($sfRows, ['id'=>'default','name'=>_l('styleflow_template_default')]);
?>
<div class="col-md-4"><?php echo render_select('styleflow_selected_'.$sfType.'_template', $sfRows, ['id','name'], _l('styleflow_'.$sfType), styleflow_active_template($sfType)); ?></div>
<?php endforeach; ?>
</div>
<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo _l('save'); ?></button>
<?php echo form_close(); ?>
</div></div>

<div class="panel_s"><div class="panel-body">
<h4 class="tw-mt-0"><?php echo _l('styleflow_template_settings'); ?></h4>
<p class="text-muted"><?php echo _l('styleflow_settings_help'); ?></p>
<div class="panel-group" id="styleflow-settings-list">
<?php foreach($templates as $tpl): if($tpl['slug']==='default') continue; ?>
<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" href="#sf-<?php echo (int)$tpl['id']; ?>"><?php echo html_escape(styleflow_template_display_name($tpl)); ?></a></h4></div>
<div id="sf-<?php echo (int)$tpl['id']; ?>" class="panel-collapse collapse"><div class="panel-body">
<?php echo form_open(admin_url('styleflow/save_template')); ?><input type="hidden" name="id" value="<?php echo (int)$tpl['id']; ?>"><input type="hidden" name="slug" value="<?php echo html_escape($tpl['slug']); ?>">
<div class="row"><div class="col-md-4"><?php echo render_input('name',_l('styleflow_template_name'),$tpl['name']); ?></div><div class="col-md-2"><?php echo render_input('primary_color',_l('styleflow_primary_color'),$tpl['primary_color'],'color'); ?></div><div class="col-md-2"><?php echo render_input('secondary_color',_l('styleflow_secondary_color'),$tpl['secondary_color'],'color'); ?></div><div class="col-md-2"><?php echo render_input('accent_color',_l('styleflow_accent_color'),$tpl['accent_color'],'color'); ?></div><div class="col-md-2"><?php echo render_input('text_color',_l('styleflow_text_color'),$tpl['text_color'],'color'); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_select('font_family',array_map(function($k,$v){return ['id'=>$k,'name'=>$v];},array_keys($fonts),array_values($fonts)),['id','name'],_l('styleflow_font_family'),$tpl['font_family']); ?></div><div class="col-md-4"><?php echo render_select('table_style',styleflow_table_style_options(),['id','name'],_l('styleflow_table_style'),$tpl['table_style']); ?></div><div class="col-md-4"><?php echo render_select('header_style',styleflow_header_style_options(),['id','name'],_l('styleflow_header_style'),$tpl['header_style']); ?></div></div>
<div class="row"><div class="col-md-6"><?php echo render_select('staff_photo_mode',$photoModes,['id','name'],_l('styleflow_staff_photo_mode'),$tpl['staff_photo_mode'] ?? 'none'); ?><p class="text-muted mtop-5"><?php echo _l('styleflow_staff_photo_help'); ?></p></div><div class="col-md-6"><?php echo render_select('selected_staff_id',$staffRows,['id','name'],_l('styleflow_selected_employee'),(int)($tpl['selected_staff_id'] ?? 0),['data-live-search'=>true]); ?></div></div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="available_invoice" id="ai-<?php echo $tpl['id']; ?>" value="1" <?php echo $tpl['available_invoice']?'checked':''; ?>><label for="ai-<?php echo $tpl['id']; ?>"><?php echo _l('styleflow_available_invoice'); ?></label></div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="available_estimate" id="ae-<?php echo $tpl['id']; ?>" value="1" <?php echo $tpl['available_estimate']?'checked':''; ?>><label for="ae-<?php echo $tpl['id']; ?>"><?php echo _l('styleflow_available_estimate'); ?></label></div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="available_proposal" id="ap-<?php echo $tpl['id']; ?>" value="1" <?php echo $tpl['available_proposal']?'checked':''; ?>><label for="ap-<?php echo $tpl['id']; ?>"><?php echo _l('styleflow_available_proposal'); ?></label></div>
<button class="btn btn-primary" type="submit"><?php echo _l('save'); ?></button><?php echo form_close(); ?>
</div></div></div>
<?php endforeach; ?>
</div>
<hr><h4><?php echo _l('styleflow_create_template'); ?></h4>
<?php echo form_open(admin_url('styleflow/save_template')); ?>
<div class="row"><div class="col-md-4"><?php echo render_input('name',_l('styleflow_template_name')); ?></div><div class="col-md-2"><?php echo render_input('primary_color',_l('styleflow_primary_color'),'#3598DB','color'); ?></div><div class="col-md-2"><?php echo render_input('secondary_color',_l('styleflow_secondary_color'),'#F28C28','color'); ?></div><div class="col-md-2"><?php echo render_input('accent_color',_l('styleflow_accent_color'),'#169179','color'); ?></div><div class="col-md-2"><?php echo render_input('text_color',_l('styleflow_text_color'),'#333333','color'); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_select('font_family',array_map(function($k,$v){return ['id'=>$k,'name'=>$v];},array_keys($fonts),array_values($fonts)),['id','name'],_l('styleflow_font_family'),'helvetica'); ?></div><div class="col-md-4"><?php echo render_select('table_style',styleflow_table_style_options(),['id','name'],_l('styleflow_table_style'),'rounded'); ?></div><div class="col-md-4"><?php echo render_select('header_style',styleflow_header_style_options(),['id','name'],_l('styleflow_header_style'),'band'); ?></div></div>
<div class="row"><div class="col-md-6"><?php echo render_select('staff_photo_mode',$photoModes,['id','name'],_l('styleflow_staff_photo_mode'),'none'); ?></div><div class="col-md-6"><?php echo render_select('selected_staff_id',$staffRows,['id','name'],_l('styleflow_selected_employee'),'', ['data-live-search'=>true]); ?></div></div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="available_invoice" id="new-ai" value="1" checked><label for="new-ai"><?php echo _l('styleflow_available_invoice'); ?></label></div><div class="checkbox checkbox-primary"><input type="checkbox" name="available_estimate" id="new-ae" value="1" checked><label for="new-ae"><?php echo _l('styleflow_available_estimate'); ?></label></div><div class="checkbox checkbox-primary"><input type="checkbox" name="available_proposal" id="new-ap" value="1" checked><label for="new-ap"><?php echo _l('styleflow_available_proposal'); ?></label></div>
<button class="btn btn-primary" type="submit"><?php echo _l('styleflow_create_template'); ?></button><?php echo form_close(); ?>
</div></div>
