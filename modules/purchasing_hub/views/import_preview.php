<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <h4><?php echo html_escape($title); ?></h4>
  <div class="alert alert-warning">Review the preview below. Nothing has been imported yet. Click Accept And Import only if the columns are aligned correctly.</div>
  <p><strong><?php echo (int)$preview['count']; ?></strong> rows ready to import.</p>
  <div class="table-responsive">
    <table class="table table-bordered table-condensed">
      <thead><tr><?php foreach($headers as $h){ echo '<th>'.html_escape($h).'</th>'; } ?></tr></thead>
      <tbody>
      <?php foreach(array_slice($preview['rows'], 0, 25) as $row){ ?><tr>
        <?php foreach($headers as $h){ echo '<td>'.html_escape($row[$h] ?? '').'</td>'; } ?>
      </tr><?php } ?>
      </tbody>
    </table>
  </div>
  <?php echo form_open(admin_url('purchasing_hub/confirm_import/'.$type)); ?>
    <input type="hidden" name="preview_token" value="<?php echo html_escape($preview['token']); ?>">
    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Accept And Import</button>
    <a href="<?php echo admin_url('purchasing_hub/import/'.$type); ?>" class="btn btn-default btn-sm">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
