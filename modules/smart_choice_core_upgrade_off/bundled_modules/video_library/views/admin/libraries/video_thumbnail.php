<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php echo form_open_multipart($this->uri->uri_string(''), array('id' => 'video_library_thumbnail',)); ?>
            <?php $login_image = get_option('thumbnail_image'); ?>
            <?php if ($login_image != '') { ?>
              <div class="row">
                <div class="col-md-4">
                  <?php echo _l('vl_thumbnail'); ?> <br /> <br />
                  <img src="<?php echo base_url('uploads/company/' . $login_image); ?>" class="img img-responsive" height="300" width="300">
                </div>
                <?php if (has_permission('settings', '', 'delete')) { ?>
                  <div class="col-md-8 text-left">
                    <a href="<?php echo base_url('video_library/remove_thumbnail_image'); ?>" data-toggle="tooltip" title="<?php echo _l('remove_thumbnail_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                  </div>
                <?php } ?>
              </div>
              <div class="clearfix"></div>
            <?php } else { ?>
              <div class="form-group">
                <label for="company_logo" class="control-label"><?php echo _l('vl_thumbnail'); ?></label>
                <input type="file" name="thumbnail_image" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>">
              </div>
            <?php } ?>
            <div class="btn-bottom-toolbar text-right">
              <button type="submit" class="btn btn-info">Save</button>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>