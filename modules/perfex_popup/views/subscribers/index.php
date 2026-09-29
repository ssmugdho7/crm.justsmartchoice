<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
            <div class="_filters _hidden_inputs hidden">
               <?php
                  foreach($popups as $item){
                     echo form_hidden('popup_id_'.$item['id']);
                  }
                  ?>
            </div>
                <div class="panel_s">

                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-8">
                            <h4 class="no-margin"><?php echo _l('subscribers'); ?></h4>
                        </div>
                        <div class="col-md-4">
                           <div class="_buttons">
                             <div class="visible-xs">
                                <div class="clearfix"></div>
                             </div>
                             <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left">
                                   <li class="active"><a href="#" data-cview="all" onclick="dt_custom_view('','.table-popup-subscribers',''); return false;"><?php echo _l('all'); ?></a>
                                   </li>
                                   <li class="divider"></li>
                                   <?php if(count($popups) > 0){ ?>
                                   <li class="dropdown-submenu pull-left groups">
                                      <a href="#" tabindex="-1"><?php echo _l('popups'); ?></a>
                                      <ul class="dropdown-menu dropdown-menu-left">
                                         <?php foreach($popups as $item){ ?>
                                         <li><a href="#" data-cview="popup_id_<?php echo $item['id']; ?>" onclick="dt_custom_view('popup_id_<?php echo $item['id']; ?>','.table-popup-subscribers','popup_id_<?php echo $item['id']; ?>'); return false;"><?php echo $item['name']; ?></a></li>
                                         <?php } ?>
                                      </ul>
                                   </li>
                                   <div class="clearfix"></div>
                                   <?php } ?>
                                </ul>
                             </div>
                          </div>
                        </div>
                      </div>
                       
                  <div class="clearfix mbot20"></div>
                       <?php render_datatable(array(
                        _l('from_popup'),
                        _l('subscriber_info'),
                        _l('url'),
                        _l('created_at'),
                        _l('actions'),
                        ),'popup-subscribers'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="lead_to_customer"></div>
<div id="data_to_lead"></div>

<?php init_tail(); ?>
<script src="<?php echo base_url(PERFEX_POPUP_ASSETS_PATH.'/subscribers/js/subscribers.js'); ?>"></script>
</body>
</html>
