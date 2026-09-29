<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade superman-modal" id="supermanMappingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
    <?php echo form_open(admin_url('superman/save_mapping')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('superman_add_mapping'); ?></h4></div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-6"><label><?php echo _l('superman_source_module'); ?></label><select name="source_module" class="selectpicker superman-field-source" data-width="100%" data-live-search="true"><?php foreach($modules as $key=>$label){ ?><option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option><?php } ?></select></div>
        <div class="col-md-6"><label><?php echo _l('superman_source_field'); ?></label><input name="source_field" class="form-control" placeholder="Email"></div>
        <div class="col-md-6 mtop15"><label><?php echo _l('superman_destination_module'); ?></label><select name="destination_module" class="selectpicker" data-width="100%" data-live-search="true"><?php foreach($modules as $key=>$label){ ?><option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option><?php } ?></select></div>
        <div class="col-md-6 mtop15"><label><?php echo _l('superman_destination_field'); ?></label><input name="destination_field" class="form-control" placeholder="Customer Email"></div>
        <div class="col-md-6 mtop15"><label><?php echo _l('superman_merge_tag'); ?></label><input name="merge_tag" class="form-control" placeholder="{client_email}"></div>
        <div class="col-md-6 mtop15"><label><?php echo _l('superman_group'); ?></label><input name="map_group" class="form-control" value="Custom Flow"></div>
        <div class="col-md-6 mtop15"><label><?php echo _l('superman_priority'); ?></label><input name="priority" type="number" class="form-control" value="10"></div>
        <div class="col-md-6 mtop15"><div class="checkbox checkbox-primary"><input type="checkbox" name="overwrite_existing" id="overwrite_existing"><label for="overwrite_existing"><?php echo _l('superman_overwrite_existing'); ?></label></div><div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" id="map_active" checked><label for="map_active"><?php echo _l('superman_active'); ?></label></div></div>
        <div class="col-md-12 mtop15"><label><?php echo _l('superman_notes'); ?></label><textarea name="notes" class="form-control" rows="3"></textarea></div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn superman-btn"><?php echo _l('save'); ?></button></div>
    <?php echo form_close(); ?>
  </div></div>
</div>


<div class="modal fade superman-modal superman-preview-modal" id="supermanPreviewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header superman-preview-modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4><i class="fa fa-eye"></i> <?php echo _l('superman_preview_combination'); ?></h4>
      </div>
      <div class="modal-body">
        <div id="superman-preview-modal-content" class="superman-preview-result superman-preview-empty-state">
          <div class="superman-preview-icon"><i class="fa fa-link"></i></div>
          <h4><?php echo _l('superman_preview_combination'); ?></h4>
          <p><?php echo _l('superman_no_fields_selected'); ?></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn superman-btn" data-dismiss="modal"><?php echo _l('close'); ?></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade superman-modal" id="supermanTokenModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document"><div class="modal-content">
    <?php echo form_open(admin_url('superman/save_token')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('superman_add_token'); ?></h4></div>
    <div class="modal-body">
      <?php echo render_input('token_name', 'superman_token_name'); ?>
      <?php echo render_input('token_key', 'superman_token_key', '{customer_project_address}'); ?>
      <label><?php echo _l('superman_source_module'); ?></label><select name="source_module" class="selectpicker" data-width="100%" data-live-search="true"><?php foreach($modules as $key=>$label){ ?><option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option><?php } ?></select>
      <?php echo render_input('source_field', 'superman_source_field'); ?>
      <?php echo render_textarea('description', 'description'); ?>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" id="token_active" checked><label for="token_active"><?php echo _l('superman_active'); ?></label></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn superman-btn"><?php echo _l('save'); ?></button></div>
    <?php echo form_close(); ?>
  </div></div>
</div>


<div class="modal fade superman-modal" id="supermanImportModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document"><div class="modal-content">
    <?php echo form_open_multipart(admin_url('superman/import_mappings')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4><?php echo _l('import'); ?> <?php echo _l('superman_mappings'); ?></h4></div>
    <div class="modal-body">
      <p class="text-muted"><?php echo _l('superman_import_help'); ?></p>
      <input type="file" name="mapping_file" class="form-control" accept=".csv" required>
      <p class="mtop10"><a href="<?php echo admin_url('superman/sample_header'); ?>"><i class="fa fa-table"></i> <?php echo _l('superman_sample_header'); ?></a></p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn superman-btn"><?php echo _l('import'); ?></button></div>
    <?php echo form_close(); ?>
  </div></div>
</div>
