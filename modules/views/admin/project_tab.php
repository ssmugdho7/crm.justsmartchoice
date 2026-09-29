<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
  <div class="col-md-12">
    <h4 class="mbot20"><?php echo _l('project_signatures_project_tab'); ?></h4>
    <p class="text-muted"><?php echo get_option('project_signatures_disclaimer'); ?></p>
    <?php echo form_open_multipart(admin_url('project_signatures/save')); ?>
      <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
      <div class="form-group">
        <label><?php echo _l('project_signatures_signature'); ?></label>
        <textarea class="form-control" name="signature" rows="3" placeholder="<?php echo _l('project_signatures_signature_placeholder'); ?>"></textarea>
      </div>
      <div class="form-group">
        <label><?php echo _l('project_signatures_notes'); ?></label>
        <textarea class="form-control" name="notes" rows="3" placeholder="<?php echo _l('project_signatures_notes_placeholder'); ?>"></textarea>
      </div>
      <div class="form-group">
        <label><?php echo _l('project_signatures_images'); ?></label>
        <input type="file" name="images[]" multiple class="form-control">
      </div>
      <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
    <?php echo form_close(); ?>
  </div>
</div>
