<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-5 left-column">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('mailwizz_marketing_settings'); ?></h4>
            <hr class="hr-panel-heading">
            <?php echo form_open('mailwizz_marketing/settings/settings_submit', array('id' => 'meeting-submit-form')); ?>
            <div class="row">
              <div class="col-md-12">
                <?php
$mautic_base_url = $settings[0]['mailwizz_marketing_base_url'];

echo render_input('mailwizz_marketing_base_url', 'mailwizz_marketing_base_url', $mautic_base_url, 'text', array('required' => 'true'));?>
              </div>
            </div>
            <?php
$public_key = $settings[0]['public_key'];

echo render_input('public_key', 'mailwizz_marketing_public_key', $public_key, 'text', array('required' => 'true'));?>
            <div class="row">
              <div class="col-md-12">
                <?php
$secret_key = $settings[0]['secret_key'];

echo render_input('secret_key', 'mailwizz_marketing_secret_key', $secret_key, 'password', array('required' => 'true'));?>
              </div>
            </div>

            <div class="btn-bottom-toolbar text-right">
              <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail();?>
</body>
</html>