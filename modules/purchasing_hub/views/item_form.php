<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <?php echo form_open_multipart(admin_url('purchasing_hub/item/'.($item['id'] ?? ''))); ?>
  <div class="row">
    <div class="col-md-4"><?php echo render_input('item_code', 'Item Code', $item['item_code'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('item_name', 'Item Name', $item['item_name'] ?? '', 'text', ['required'=>true]); ?></div>
    <div class="col-md-4"><?php echo render_input('sku', 'SKU', $item['sku'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('barcode', 'Barcode', $item['barcode'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('category', 'Category', $item['category'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('sub_category', 'Sub Category', $item['sub_category'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('unit_name', 'Unit', $item['unit_name'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('purchase_price', 'Purchase Price', $item['purchase_price'] ?? '0.00', 'number', ['step'=>'0.01','required'=>true]); ?></div>
    <div class="col-md-4"><?php echo render_input('sales_rate', 'Sales Rate', $item['sales_rate'] ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
    <div class="col-md-4"><?php echo render_input('tax_1', 'Tax 1', $item['tax_1'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('tax_2', 'Tax 2', $item['tax_2'] ?? ''); ?></div>
    <div class="col-md-4"><label>Item Image</label><input type="file" name="item_image" class="form-control" accept="image/*"><small class="text-muted">JPG, PNG, GIF, or WebP up to 5 MB.</small></div>
    <div class="col-md-12"><?php echo render_textarea('description', 'Description', $item['description'] ?? ''); ?></div>
  </div>
  <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" id="is_active" <?php echo !isset($item['is_active']) || $item['is_active'] ? 'checked' : ''; ?>><label for="is_active">Active</label></div>
  <button type="submit" class="btn btn-primary">Save Item</button>
  <a href="<?php echo admin_url('purchasing_hub/items'); ?>" class="btn btn-default">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
