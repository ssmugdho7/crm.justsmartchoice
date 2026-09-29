<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container">
  <h3 class="mbot20"><?php echo _l('project_signatures_client_menu'); ?></h3>
  <?php echo form_open_multipart(site_url('project_signatures_client/submit')); ?>
    <div class="form-group">
      <label><?php echo _l('project_id'); ?></label>
      <input type="number" class="form-control" name="project_id" required>
    </div>
    <div class="form-group">
      <label><?php echo _l('project_signatures_signature'); ?></label>
      <textarea class="form-control" name="signature" rows="3" required></textarea>
    </div>
    <div class="form-group">
      <label><?php echo _l('project_signatures_notes'); ?></label>
      <textarea class="form-control" name="notes" rows="3"></textarea>
    </div>
    <div class="form-group">
      <label><?php echo _l('project_signatures_images'); ?></label>
      <input type="file" name="images[]" multiple class="form-control">
    </div>
    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
  <?php echo form_close(); ?>
</div>
