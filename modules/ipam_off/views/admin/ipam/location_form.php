<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <?php echo breadcrumbs([
      _l('ipam_module_name')         => admin_url('ipam/locations'),
      isset($location) 
        ? _l('ipam_edit_location') 
        : _l('ipam_add_location')   => ''
    ]); ?>

    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <?= form_open(
             admin_url('ipam/location' . (isset($location) ? '/'.$location['id'] : '')),
             ['id'=>'location-form']
           ); ?>
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="bold mbot15">
              <?= isset($location) 
                   ? _l('ipam_edit_location') 
                   : _l('ipam_add_location'); ?>
            </h4>
            <?= csrf_input(); ?>

            <div class="form-group">
              <label for="location_name"><?= _l('ipam_location_name'); ?></label>
              <input
                type="text"
                id="location_name"
                name="location_name"
                class="form-control"
                required
                value="<?= set_value('location_name', $location['location_name'] ?? ''); ?>"
              >
            </div>

            <div class="form-group">
              <label for="address"><?= _l('ipam_address'); ?></label>
              <input
                type="text"
                id="address"
                name="address"
                class="form-control"
                value="<?= set_value('address', $location['address'] ?? ''); ?>"
              >
            </div>

            <div class="form-group">
              <label for="notes"><?= _l('ipam_notes'); ?></label>
              <textarea
                id="notes"
                name="notes"
                rows="4"
                class="form-control"
              ><?= set_value('notes', $location['notes'] ?? ''); ?></textarea>
            </div>

            <div class="text-right">
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> <?= _l('save'); ?>
              </button>
              <a href="<?= admin_url('ipam/locations'); ?>" class="btn btn-default">
                <i class="fa fa-times"></i> <?= _l('cancel'); ?>
              </a>
            </div>
          </div>
        </div>
        <?= form_close(); ?>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

<script>
  _validate_form($('#location-form'), {
    location_name: 'required',
  });
</script>
