<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">

        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin">
              <?php echo isset($item) ? _l('edit', _l('procurement_item')) : _l('add_new', _l('procurement_item')); ?>
            </h4>
            <hr />

            <?php echo form_open_multipart($this->uri->uri_string()); ?>

            <div class="form-group">
              <label for="project_id" class="control-label"><?php echo _l('project'); ?></label>
              <select name="project_id" id="project_id" class="form-control selectpicker" data-live-search="true">
                <option value=""></option>
                <?php foreach ($projects as $project) { ?>
                  <option value="<?php echo $project['id']; ?>" <?php echo (isset($item) && $item['project_id'] == $project['id'] ? 'selected' : ''); ?>>
                    <?php echo $project['id'] . ' - ' . $project['name']; ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="category" class="control-label"><?php echo _l('category'); ?></label>
              <input type="text" name="category" id="category" class="form-control" value="<?php echo isset($item) ? html_escape($item['category']) : ''; ?>">
            </div>

            <div class="form-group">
              <label for="item_name" class="control-label"><?php echo _l('item'); ?></label>
              <input type="text" name="item_name" id="item_name" class="form-control" required value="<?php echo isset($item) ? html_escape($item['item_name']) : ''; ?>">
            </div>

            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="unit" class="control-label"><?php echo _l('unit'); ?></label>
                  <input type="text" name="unit" id="unit" class="form-control" value="<?php echo isset($item) ? html_escape($item['unit']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="qty" class="control-label"><?php echo _l('qty'); ?></label>
                  <input type="number" step="0.01" name="qty" id="qty" class="form-control" value="<?php echo isset($item) ? html_escape($item['qty']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="quoted_price" class="control-label"><?php echo _l('quoted_price'); ?></label>
                  <input type="number" step="0.01" name="quoted_price" id="quoted_price" class="form-control" value="<?php echo isset($item) ? html_escape($item['quoted_price']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="alt_price" class="control-label"><?php echo _l('alt_price'); ?></label>
                  <input type="number" step="0.01" name="alt_price" id="alt_price" class="form-control" value="<?php echo isset($item) ? html_escape($item['alt_price']) : ''; ?>">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="marketplace_price" class="control-label"><?php echo _l('marketplace_price'); ?></label>
                  <input type="number" step="0.01" name="marketplace_price" id="marketplace_price" class="form-control" value="<?php echo isset($item) ? html_escape($item['marketplace_price']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="supplier_name" class="control-label"><?php echo _l('supplier'); ?></label>
                  <input type="text" name="supplier_name" id="supplier_name" class="form-control" value="<?php echo isset($item) ? html_escape($item['supplier_name']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="supplier_email" class="control-label"><?php echo _l('email'); ?></label>
                  <input type="email" name="supplier_email" id="supplier_email" class="form-control" value="<?php echo isset($item) ? html_escape($item['supplier_email']) : ''; ?>">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="source" class="control-label"><?php echo _l('source'); ?></label>
                  <input type="text" name="source" id="source" class="form-control" value="<?php echo isset($item) ? html_escape($item['source']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="status" class="control-label"><?php echo _l('status'); ?></label>
                  <input type="text" name="status" id="status" class="form-control" value="<?php echo isset($item) ? html_escape($item['status']) : ''; ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="attachment" class="control-label"><?php echo _l('attachment'); ?> (PDF/Quote)</label>
                  <input type="file" name="attachment_file" id="attachment_file" class="form-control">
                  <?php if (isset($item) && !empty($item['attachment'])) { ?>
                    <p class="mtop5">
                      <a href="<?php echo site_url('uploads/procurement/' . $item['attachment']); ?>" target="_blank"><?php echo _l('download'); ?></a>
                    </p>
                  <?php } ?>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="notes" class="control-label"><?php echo _l('notes'); ?></label>
              <textarea name="notes" id="notes" rows="4" class="form-control"><?php echo isset($item) ? html_escape($item['notes']) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            <a href="<?php echo admin_url('procurement_manager'); ?>" class="btn btn-default"><?php echo _l('back_to_list'); ?></a>

            <?php echo form_close(); ?>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
