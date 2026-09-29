<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
     <?php
     if(isset($dmn))
     {
      echo form_hidden('is_edit','true');
    }
    ?>
    <?php 
    if(isset($dmn)){
      echo form_open_multipart($this->uri->uri_string(),array('id'=>'dmn-detail-form')) ;
    }else{
      echo form_open_multipart($this->uri->uri_string(),array('id'=>'dmn-detail-form')) ;
    }
    ?>
    <input type="hidden" name="staffid" value="<?= get_staff_user_id();?>">
    <div class="col-lg-12">
      <div class="panel_s">
        <div class="panel-body">
          <h4 class="no-margin"><?= isset($dmn) ? $dmn->title : _l('dmn_create_new').' '._l('dmn') ?>
            <input type="hidden" name="id" value="<?php echo isset($dmn) ? $dmn->id: ''?>">
          </h4>
          <hr class="hr-panel-heading" />
          <div class="row">
            <div class="col-md-12">
              <?php $value = (isset($dmn) ? $dmn->title : ''); 
              echo render_input('title',_l('dmn_title'),$value);
              $value = (isset($dmn) ? $dmn->description : ''); 
              echo render_textarea('description',_l('clients_dmn_description'),$value,array('rows'=>4),array());
              $selected = (isset($dmn) ? $dmn->project_id : '');
              echo render_select('project_id',$projects,array('id','name'),'project_group',$selected);
              ?>
            </div>
          </div>
          <div class="btn-bottom-toolbar text-right">
            <button type="button" id="dmn-detail-form-btn" class="btn btn-info"><?php echo _l('submit'); ?></button>
          </div>
        </div>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>
<?php init_tail(); ?>
<script>
  "use strict";
  function validate_dmn_form(){
    appValidateForm($('#dmn-detail-form'), {
      title: 'required',
      description : 'required',
      project_id : 'required',
    });
  }
  $('body').on('click','button#dmn-detail-form-btn', function() {
    $('form#dmn-detail-form').submit();
  });
  validate_dmn_form();
</script>
</body>
</html>