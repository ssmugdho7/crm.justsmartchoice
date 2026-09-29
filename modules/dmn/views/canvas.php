<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/diagram-js.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-shared.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-drd.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-decision-table.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-decision-table-controls.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-literal-expression.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/custom.css" />
 
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php
      if(isset($dmn))
      {
        echo form_hidden('is_edit','true');
      }
      ?>
      <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'dmn-form')) ;?>
      <input type="hidden" name="staffid" value="<?= get_staff_user_id();?>">
      <?php 
      $value = (isset($dmn) ? $dmn->dmn_content : ''); 
      echo render_textarea('dmn_content','',$value,[],[],'','hidden');
      echo render_textarea('dmnxml','','',[],[],'','hidden');
      ?>
      <div class="col-lg-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="_buttons row">
              <div class="col-xs-9">
                <a class="visible-inline m_bt">
                  <?php echo $dmn->title; ?>
                  <input type="hidden" name="id" value="<?php echo isset($dmn->id) ? $dmn->id: ''?>">
                  <span class="like_canvas" onclick="likeCanvas('like');"><i class="fa fa-thumbs-o-up" aria-hidden="true" style="<?= isset($vote) && $vote->thumb=='like' ? 'color: green' : ''?>"></i><span><?=$votes->like;?></span></span>
                  <span class="dislike_canvas" onclick="likeCanvas('dislike');"><i class="fa fa-thumbs-o-down" aria-hidden="true" style="<?= isset($vote) && $vote->thumb=='dislike' ? 'color: red' : ''?>"></i><span><?=$votes->dislike;?></span></span>
                </a>
              </div>
              <div class="col-xs-3">
                <div class="btn-group pull-right mleft4 btn-with-tooltip-group ">
                  <button type="button" class="btn btn-primary dropdown-toggle text-center-c" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false ">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-right width200">
                    <li>
                      <a href="javascript:void(0);" onclick="new_dmn();return false;"><?= _l('properties');?></a>
                    </li>
                    <li>
                      <a id="print-svg" href="javascript:void(0);"><?= _l('dmn_print_svg');?></a>
                    </li>
                     <li>
                    <a id="print" href="javascript:void(0);"><?= _l('window_print');?></a>
                  </li>
                    <li>
                      <a id="delete-button" type="button" href="<?php echo admin_url('dmn/delete/'.$dmn->id); ?>"><?= _l('delete');?></a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading" />
            <div class="row">
              <div class="col-md-12">
                <div id="canvas"></div>
                <canvas id="c" data-name="<?php echo $dmn->title; ?>"></canvas>
                <img/>
              </div>
            </div>
            <div class="btn-bottom-toolbar text-right">
             <button type="button" onclick="generateXMLandSubmitForm();" class="btn btn-info dmn-btns"><?php echo _l('submit'); ?></button>
           </div>
          </div>
       </div>
     </div>
     <?php echo form_close(); ?>
    </div>
  </div>
</div>
<div class="modal fade dmn-modal" id="dmn_create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg">
    <?php echo form_open_multipart(admin_url('dmn/dmn_create'),array('id'=>'dmn-form-detail')) ;?>
    <?php echo render_input('staffid','', get_staff_user_id(), 'hidden'); ?>
    <div class="modal-content data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">
          <?php $value = (isset($dmn) ? $dmn->title : ''); ?>
          <span class="add-title"><?php echo $value; ?></span>
        </h4>
      </div>
      <div class="panel-body">
        <?php 
        $value = (isset($dmn) ? $dmn->title : ''); 
        echo render_input('title',_l('dmn_title'),$value);
        $value = (isset($dmn) ? $dmn->description : ''); 
        echo render_textarea('description',_l('dmn_description'),$value,array('rows'=>4),array());
        $selected = (isset($dmn) ? $dmn->project_id : '');
        echo render_select('project_id',$projects,array('id','name'),'project_group',$selected);
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-info dmn-btn" data-loading-text="Please wait..." data-autocomplete="off" data-form="#dmn-form-detail">Save</button>
      </div>
    </div>
    <?php echo form_close();?>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>