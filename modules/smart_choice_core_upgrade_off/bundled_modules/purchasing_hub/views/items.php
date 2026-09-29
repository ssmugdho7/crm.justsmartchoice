<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <div class="ph-action-row purchasing-hub-action-row ph-primary-action-row"><a href="<?php echo admin_url('purchasing_hub/item'); ?>" class="btn btn-primary btn-sm ph-btn-compact">Add Item</a> <a href="<?php echo admin_url('purchasing_hub/sample/items'); ?>" class="btn btn-info btn-sm ph-btn-compact">Sample Import File</a></div>
  <?php echo form_open(admin_url('purchasing_hub/items'), ['method'=>'get','class'=>'form-inline m-b-md ph-filter-row']); ?>
    <input type="text" name="q" class="form-control input-sm" placeholder="Search items" value="<?php echo html_escape($this->input->get('q')); ?>">
    <input type="text" name="project_name" class="form-control input-sm" placeholder="Project" value="<?php echo html_escape($this->input->get('project_name')); ?>">
    <input type="text" name="created_from" class="form-control input-sm datepicker" placeholder="Created From" value="<?php echo html_escape($this->input->get('created_from')); ?>">
    <input type="text" name="created_to" class="form-control input-sm datepicker" placeholder="Created To" value="<?php echo html_escape($this->input->get('created_to')); ?>">
    <button type="submit" class="btn btn-default btn-sm ph-btn-compact">Filter</button>
  <?php echo form_close(); ?>
  <?php echo form_open(admin_url('purchasing_hub/massive_delete/items'), ['id'=>'items-mass-delete']); ?>
  <div class="ph-dt-actions" data-ph-dt-actions="1" aria-label="Table actions">
    <a href="<?php echo admin_url('purchasing_hub/import/items'); ?>" class="btn btn-default btn-sm ph-btn-compact">Import</a>
    <a href="<?php echo admin_url('purchasing_hub/items'); ?>" class="btn btn-default btn-sm ph-btn-compact">Refresh</a>
    <button type="submit" form="items-mass-delete" class="btn btn-danger btn-sm ph-btn-compact" onclick="return confirm('Delete selected items?');">Massive Delete</button>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered dt-table purchasing-hub-table">
      <thead><tr><th><input type="checkbox" onclick="$('.ph-item-check').prop('checked', this.checked);"></th><th>Item Code</th><th>Item Name</th><th>SKU</th><th>Category</th><th>Unit</th><th>Purchase Price</th><th>Sales Rate</th><th>Options</th></tr></thead>
      <tbody><?php foreach($items as $item){ ?><tr>
        <td><input class="ph-item-check" type="checkbox" name="ids[]" value="<?php echo (int)$item['id']; ?>"></td>
        <td><?php echo html_escape($item['item_code']); ?></td>
        <td><?php echo html_escape($item['item_name']); ?></td>
        <td><?php echo html_escape($item['sku']); ?></td>
        <td><?php echo html_escape($item['category']); ?></td>
        <td><?php echo html_escape($item['unit_name']); ?></td>
        <td><?php echo app_format_money($item['purchase_price'], get_base_currency()); ?></td>
        <td><?php echo app_format_money($item['sales_rate'], get_base_currency()); ?></td>
        <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/item/'.$item['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('purchasing_hub/delete/items/'.$item['id']); ?>">Delete</a></td>
      </tr><?php } ?></tbody>
    </table>
  </div>
<?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
