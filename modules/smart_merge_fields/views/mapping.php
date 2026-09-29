<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s smf-panel"><div class="panel-body">
<h4 class="smf-title"><?php echo html_escape($title); ?></h4>
<?php $this->load->view('smart_merge_fields/_nav'); ?>
<?php echo form_open(admin_url('smart_merge_fields/mapping/' . ($mapping['id'] ?? ''))); ?>
<input type="hidden" name="id" value="<?php echo html_escape($mapping['id'] ?? ''); ?>">
<div class="row">
<div class="col-md-12"><?php echo render_input('name', 'Mapping Name', $mapping['name'] ?? ''); ?></div>
<div class="col-md-6"><label>Source Field</label><select name="source_combo" class="form-control smf-combo" data-table-target="source_table" data-field-target="source_field"><option value="">Select source field</option><?php foreach($fields as $field){ $value=$field['table_name'].'||'.$field['field_name']; ?><option value="<?php echo html_escape($value); ?>" <?php echo (($mapping['source_table'] ?? '').'||'.($mapping['source_field'] ?? '')===$value)?'selected':''; ?>><?php echo html_escape(smart_merge_fields_human_name($field['table_name']).' · '.smart_merge_fields_human_name($field['field_name'])); ?></option><?php } ?></select><input type="hidden" name="source_table" value="<?php echo html_escape($mapping['source_table'] ?? ''); ?>"><input type="hidden" name="source_field" value="<?php echo html_escape($mapping['source_field'] ?? ''); ?>"></div>
<div class="col-md-6"><label>Target Field</label><select name="target_combo" class="form-control smf-combo" data-table-target="target_table" data-field-target="target_field"><option value="">Select target field</option><?php foreach($fields as $field){ $value=$field['table_name'].'||'.$field['field_name']; ?><option value="<?php echo html_escape($value); ?>" <?php echo (($mapping['target_table'] ?? '').'||'.($mapping['target_field'] ?? '')===$value)?'selected':''; ?>><?php echo html_escape(smart_merge_fields_human_name($field['table_name']).' · '.smart_merge_fields_human_name($field['field_name'])); ?></option><?php } ?></select><input type="hidden" name="target_table" value="<?php echo html_escape($mapping['target_table'] ?? ''); ?>"><input type="hidden" name="target_field" value="<?php echo html_escape($mapping['target_field'] ?? ''); ?>"></div>
<div class="col-md-6"><?php echo render_input('relation_source_field', 'Source Relation Field', $mapping['relation_source_field'] ?? ''); ?></div>
<div class="col-md-6"><?php echo render_input('relation_target_field', 'Target Relation Field', $mapping['relation_target_field'] ?? ''); ?></div>
<div class="col-md-6"><label>Status</label><select name="status" class="form-control"><option value="active" <?php echo (($mapping['status'] ?? '')==='active')?'selected':''; ?>>Active</option><option value="paused" <?php echo (($mapping['status'] ?? '')==='paused')?'selected':''; ?>>Paused</option></select></div>
<div class="col-md-6"><label>Direction</label><select name="direction" class="form-control"><option value="source_to_target" <?php echo (($mapping['direction'] ?? '')==='source_to_target')?'selected':''; ?>>Source To Target</option></select></div>
</div>
<div class="smf-form-actions"><button type="submit" class="btn btn-info smf-btn">Save Mapping</button></div>
<?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>
