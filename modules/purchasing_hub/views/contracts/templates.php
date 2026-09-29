<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="row">
  <div class="col-md-5">
    <div class="panel_s"><div class="panel-body">
      <h4><?php echo !empty($row['id']) ? 'Edit Contract Template' : 'New Contract Template'; ?></h4>
      <?php echo form_open(admin_url('purchasing_hub/contract_templates'.(!empty($row['id']) ? '/'.$row['id'] : ''))); ?>
        <?php echo render_input('template_name','Template Name',$row['template_name'] ?? ''); ?>
        <?php echo render_input('template_type','Template Type',$row['template_type'] ?? 'Vendor Contract'); ?>
        <?php echo render_textarea('body','Template Body',$row['body'] ?? '', ['class'=>'tinymce','rows'=>14]); ?>
        <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" id="is_active" value="1" <?php echo !isset($row['is_active']) || !empty($row['is_active']) ? 'checked' : ''; ?>><label for="is_active">Active</label></div>
        <button class="btn btn-primary btn-sm" type="submit">Save Template</button>
      <?php echo form_close(); ?>
    </div></div>
  </div>
  <div class="col-md-7">
    <div class="panel_s"><div class="panel-body">
      <h4>Available Templates</h4>
      <div class="table-responsive"><table class="table table-bordered dt-table purchasing-hub-table">
        <thead><tr><th>Name</th><th>Type</th><th>Status</th><th>Options</th></tr></thead>
        <tbody><?php foreach($templates as $tpl){ ?><tr>
          <td><?php echo html_escape($tpl['template_name']); ?></td>
          <td><?php echo html_escape($tpl['template_type']); ?></td>
          <td><?php echo !empty($tpl['is_active']) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
          <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/contract_templates/'.$tpl['id']); ?>">Edit</a></td>
        </tr><?php } ?></tbody>
      </table></div>
    </div></div>
  </div>
</div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
