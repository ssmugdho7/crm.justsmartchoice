<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/diagram-js.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-shared.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-drd.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-decision-table.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-decision-table-controls.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn-js-literal-expression.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/dmn.css"/>
<link rel="stylesheet" href="<?php echo base_url();?>modules/dmn/assets/css/preview.css"/>
<script src="<?php echo base_url();?>modules/dmn/assets/js/development.js"></script>
<script src="<?php echo base_url();?>modules/dmn/assets/js/jq-development.js"></script>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php 
      $csrf = get_csrf_for_ajax();
      $value = (isset($dmn) ? $dmn->dmn_content : '');
      echo render_textarea('dmn_content','',$value,[],[],'','hidden');
      echo render_input('csrf_token','',$csrf['hash'],'hidden');
      ?>
      <div class="col-lg-12">
        <div class="panel_s">
          <div class="panel-body">
              <div class="_buttons">
                 <a class="visible-inline m_bt">
                    <?php echo $dmn->title; ?>
                    <input type="hidden" name="id" value="<?php echo isset($dmn->id) ? $dmn->id: ''?>">
                    <span class="like_canvas" onclick="likeCanvas('like');"><i class="fa fa-thumbs-o-up" aria-hidden="true" style="<?= isset($vote) && $vote->thumb=='like' ? 'color: green' : ''?>"></i><span><?=$votes->like;?></span></span>
                    <span class="dislike_canvas" onclick="likeCanvas('dislike');"><i class="fa fa-thumbs-o-down" aria-hidden="true" style="<?= isset($vote) && $vote->thumb=='dislike' ? 'color: red' : ''?>"></i><span><?=$votes->dislike;?></span></span>
                  </a>
                  <div class="btn-group pull-right mleft4 btn-with-tooltip-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa fa-cog" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right width200">
                      <li>
                        <a id="print-svg" href="javascript:void(0);"><?= _l('dmn_print_svg');?></a>
                      </li>
                      <li>
                        <a id="print" href="javascript:void(0);"><?= _l('window_print');?></a>
                      </li>
                    </ul>
                  </div>
              </div>
              <div class="clearfix"></div>
              <hr class="hr-panel-heading" />
              <div class="row">
                <div class="col-md-12">
                  <div id="canvas"></div>
                  <canvas id="c" data-name="<?php echo $dmn->title; ?>"></canvas>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="<?php echo base_url();?>modules/dmn/assets/js/client-preview.js"></script>
</body>
</html>