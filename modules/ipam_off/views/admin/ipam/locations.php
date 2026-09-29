<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">

    <?php echo breadcrumbs([
      _l('ipam_module_name') => admin_url('ipam/locations'),
      _l('ipam_locations')   => '',
    ]); ?>

    <div class="panel_s ipam-panel">
      <div class="panel-heading">
        <h4 class="panel-title"><?= _l('ipam_locations'); ?></h4>
      </div>
      <div class="panel-body">

        <div class="text-right mbot15">
          <a href="<?= admin_url('ipam/location'); ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> <?= _l('ipam_add_location'); ?>
          </a>
        </div>

        <div class="table-responsive ipam-table-wrapper">
          <?= render_datatable([
               _l('ID'),
               _l('ipam_location_name'),
               _l('ipam_address'),
               _l('ipam_notes'),
               _l('ipam_created_at'),
               _l('options')
             ], 'locations-table'); ?>
        </div>

      </div>
    </div>

  </div>
</div>
<?php init_tail(); ?>

<script>
  $(function(){
    initDataTable('.table-locations-table', window.location.href, 'locations-table', [], [], {
      order: [[0, 'desc']]
    });
  });
</script>
