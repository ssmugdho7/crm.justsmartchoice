<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="scfcMappingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document"><div class="modal-content scfc-modal">
    <?php echo form_open(admin_url('smart_choice_field_connector/save_mapping')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_add_mapping'); ?></h4></div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-6"><?php echo render_select('source_module', array_map(function($k,$v){return ['id'=>$k,'name'=>$v];}, array_keys($modules), $modules), ['id','name'], 'Source Module'); ?></div>
        <div class="col-md-6"><?php echo render_input('source_field', 'Source Field'); ?></div>
        <div class="col-md-6"><?php echo render_select('destination_module', array_map(function($k,$v){return ['id'=>$k,'name'=>$v];}, array_keys($modules), $modules), ['id','name'], 'Destination Module'); ?></div>
        <div class="col-md-6"><?php echo render_input('destination_field', 'Destination Field'); ?></div>
        <div class="col-md-12"><?php echo render_input('merge_tag', 'Merge Tag / Token', '', 'text', ['placeholder'=>'{customer_email}']); ?></div>
        <div class="col-md-12"><?php echo render_textarea('notes', 'Notes'); ?></div>
        <div class="col-md-12"><div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="map_active"><label for="map_active">Active</label></div></div>
      </div>
    </div>
    <div class="modal-footer"><button class="btn scfc-btn" type="submit">Save Mapping</button></div>
    <?php echo form_close(); ?>
  </div></div>
</div>

<div class="modal fade" id="scfcTokenModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document"><div class="modal-content scfc-modal">
    <?php echo form_open(admin_url('smart_choice_field_connector/save_token')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('scfc_add_token'); ?></h4></div>
    <div class="modal-body">
      <?php echo render_input('token_name', 'Token Name', '', 'text', ['placeholder'=>'Customer Internet Connection Number']); ?>
      <?php echo render_input('token_key', 'Token Key', '', 'text', ['placeholder'=>'customer_internet_connection_number']); ?>
      <?php echo render_select('source_module', array_map(function($k,$v){return ['id'=>$k,'name'=>$v];}, array_keys($modules), $modules), ['id','name'], 'Source Module'); ?>
      <?php echo render_input('source_field', 'Source Field'); ?>
      <?php echo render_textarea('description', 'Description'); ?>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" checked id="token_active"><label for="token_active">Active</label></div>
    </div>
    <div class="modal-footer"><button class="btn scfc-btn" type="submit">Save Token</button></div>
    <?php echo form_close(); ?>
  </div></div>
</div>
