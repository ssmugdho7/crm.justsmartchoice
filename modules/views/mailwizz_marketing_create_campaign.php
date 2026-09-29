<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-5 left-column">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('mailwizz_marketing_create_campaign'); ?></h4>
            <hr class="hr-panel-heading">
            <?php echo form_open('mailwizz_marketing/campaign/mailwizz_marketing_add_campaign', array('id' => 'meeting-submit-form')); ?>
            <div class="row">
              <div class="col-md-12">

              <?php

echo render_input('mailwizz_marketing_campaign_name', 'mailwizz_marketing_campaign_name', '', 'text', array('required' => 'true')); ?>
              </div>
            </div>

            <div class="row">
            <div class="col-md-12">
                <br>
                <?php

echo render_input('mailwizz_marketing_subject', 'mailwizz_marketing_subject', '', 'text', array('required' => 'true')); ?>
                </div>

              </div>
            <div class="row">
              <div class="col-md-12">
              <label for="type" class="control-label">Select Type</label>
                  <select required class="selectpicker form-control" id="type" name="type">
                  <option>Select Type</option>
                  <option value="regular">Regular</option>
                  <option value="autoresponder">Autoresponder</option>
                  </select>
              </div>
            </div>
            <br>

            <div class="row">
              <div class="col-md-12">
              <label for="type" class="control-label">Select List</label>
              <a href="#" onclick="location.href='create_list'"><i class="fa fa-plus"></i>Add New List</a>
                  <select class="selectpicker form-control" id="lisd_id" name="lisd_id" required>
                  <option>Select List</option>
                  <?php

for ($i = 0; $i < sizeof($list); $i++) {

    $val = $list['records'][$i]['general']['list_uid'];
    if ($val != '') {
        ?>
                                    <option value="<?php echo $list['records'][$i]['general']['list_uid']; ?>"><?php echo $list['records'][$i]['general']['name']; ?></option>

                                    <?php
}
}?>
                 </select>

              </div>

            </div>

            <div class="row">
            <div class="col-md-12">
                <br>
                <?php

echo render_input('mailwizz_marketing_from_name', 'mailwizz_marketing_from_name', '', 'text', array('required' => 'true')); ?>
                </div>

              </div>
              <div class="row">
            <div class="col-md-12">
                <br>
                <?php

echo render_input('mailwizz_marketing_from_email', 'mailwizz_marketing_from_email', '', 'email', array('required' => 'true')); ?>
                </div>

              </div>
              <div class="row">
            <div class="col-md-12">
                <br>
                <?php

echo render_input('mailwizz_marketing_to_email', 'mailwizz_marketing_to_email', '', 'email', array('required' => 'true')); ?>
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