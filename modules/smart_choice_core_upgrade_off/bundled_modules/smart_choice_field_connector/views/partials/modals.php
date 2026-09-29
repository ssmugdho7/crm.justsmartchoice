<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$moduleOptions = [];
foreach ($modules as $key => $label) {
    $moduleOptions[] = ['id' => $key, 'name' => $label];
}
$groupOptions = [['id' => '', 'name' => _l('scfc_no_group')]];
foreach ($groups as $group) {
    $groupOptions[] = ['id' => $group['id'], 'name' => $group['group_name']];
}
?>
<div class="modal fade" id="scfcGroupModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content scfc-modal">
      <?php echo form_open(admin_url('smart_choice_field_connector/save_group'), ['id' => 'scfcGroupForm']); ?>
      <input type="hidden" name="id" id="scfc_group_id" value="">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_group'); ?></h4></div>
      <div class="modal-body">
        <?php echo render_input('group_name', 'scfc_group_name', '', 'text', ['id' => 'scfc_group_name']); ?>
        <?php echo render_input('group_key', 'scfc_group_key', '', 'text', ['id' => 'scfc_group_key']); ?>
        <?php echo render_textarea('description', 'scfc_description', '', ['id' => 'scfc_group_description']); ?>
        <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="scfc_group_active"><label for="scfc_group_active"><?php echo _l('scfc_active'); ?></label></div>
      </div>
      <div class="modal-footer"><button class="btn scfc-btn" type="submit"><?php echo _l('submit'); ?></button></div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" id="scfcMappingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content scfc-modal">
      <?php echo form_open(admin_url('smart_choice_field_connector/save_mapping'), ['id' => 'scfcMappingForm']); ?>
      <input type="hidden" name="id" id="scfc_mapping_id" value="">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_mapping'); ?></h4></div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12"><?php echo render_select('group_id', $groupOptions, ['id', 'name'], 'scfc_group', '', ['id' => 'scfc_mapping_group_id']); ?></div>
          <div class="col-md-6"><?php echo render_select('source_module', $moduleOptions, ['id', 'name'], 'scfc_source_module', '', ['id' => 'scfc_mapping_source_module']); ?></div>
          <div class="col-md-6"><?php echo render_input('source_field', 'scfc_source_field', '', 'text', ['id' => 'scfc_mapping_source_field']); ?></div>
          <div class="col-md-6"><?php echo render_select('destination_module', $moduleOptions, ['id', 'name'], 'scfc_destination_module', '', ['id' => 'scfc_mapping_destination_module']); ?></div>
          <div class="col-md-6"><?php echo render_input('destination_field', 'scfc_destination_field', '', 'text', ['id' => 'scfc_mapping_destination_field']); ?></div>
          <div class="col-md-12"><?php echo render_input('merge_tag', 'scfc_merge_tag', '', 'text', ['id' => 'scfc_mapping_merge_tag', 'placeholder' => '{customer_email}']); ?></div>
          <div class="col-md-12"><?php echo render_textarea('notes', 'scfc_notes', '', ['id' => 'scfc_mapping_notes']); ?></div>
          <div class="col-md-12"><div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="scfc_mapping_active"><label for="scfc_mapping_active"><?php echo _l('scfc_active'); ?></label></div></div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn scfc-btn" type="submit"><?php echo _l('submit'); ?></button></div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" id="scfcTokenModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content scfc-modal">
      <?php echo form_open(admin_url('smart_choice_field_connector/save_token'), ['id' => 'scfcTokenForm']); ?>
      <input type="hidden" name="id" id="scfc_token_id" value="">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_custom_token'); ?></h4></div>
      <div class="modal-body">
        <?php echo render_select('group_id', $groupOptions, ['id', 'name'], 'scfc_group', '', ['id' => 'scfc_token_group_id']); ?>
        <?php echo render_input('token_name', 'scfc_token_name', '', 'text', ['id' => 'scfc_token_name']); ?>
        <?php echo render_input('token_key', 'scfc_token_key', '', 'text', ['id' => 'scfc_token_key', 'placeholder' => 'customer_internet_connection_number']); ?>
        <?php echo render_select('source_module', $moduleOptions, ['id', 'name'], 'scfc_source_module', '', ['id' => 'scfc_token_source_module']); ?>
        <?php echo render_input('source_field', 'scfc_source_field', '', 'text', ['id' => 'scfc_token_source_field']); ?>
        <?php echo render_textarea('description', 'scfc_description', '', ['id' => 'scfc_token_description']); ?>
        <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="scfc_token_active"><label for="scfc_token_active"><?php echo _l('scfc_active'); ?></label></div>
      </div>
      <div class="modal-footer"><button class="btn scfc-btn" type="submit"><?php echo _l('submit'); ?></button></div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" id="scfcCombinationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content scfc-modal">
      <?php echo form_open(admin_url('smart_choice_field_connector/save_combination'), ['id' => 'scfcCombinationForm']); ?>
      <input type="hidden" name="id" id="scfc_combination_id" value="">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_combination'); ?></h4></div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6"><?php echo render_input('combination_name', 'scfc_combination_name', '', 'text', ['id' => 'scfc_combination_name']); ?></div>
          <div class="col-md-6"><?php echo render_input('combination_key', 'scfc_combination_key', '', 'text', ['id' => 'scfc_combination_key']); ?></div>
          <div class="col-md-12"><?php echo render_select('group_id', $groupOptions, ['id', 'name'], 'scfc_group', '', ['id' => 'scfc_combination_group_id']); ?></div>
          <div class="col-md-12"><?php echo render_textarea('template', 'scfc_template', '', ['id' => 'scfc_combination_template', 'rows' => 7]); ?></div>
          <div class="col-md-12"><?php echo render_textarea('description', 'scfc_description', '', ['id' => 'scfc_combination_description']); ?></div>
          <div class="col-md-12"><div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="scfc_combination_active"><label for="scfc_combination_active"><?php echo _l('scfc_active'); ?></label></div></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn scfc-btn-blue" id="scfcTestCombinationModal"><i class="fa fa-play"></i> <?php echo _l('scfc_test'); ?></button><button class="btn scfc-btn" type="submit"><?php echo _l('submit'); ?></button></div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" id="scfcPreviewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content scfc-modal">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_test_result'); ?></h4></div>
      <div class="modal-body"><div id="scfcPreviewContent" class="scfc-preview-content"></div></div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button></div>
    </div>
  </div>
</div>
