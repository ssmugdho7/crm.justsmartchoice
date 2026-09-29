<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head();
?>

<div id="wrapper">
     <div class="content">
          <div class="row main_row">
               <div class="col-md-12">
                    <div class="panel_s">
                         <div class="panel-body">
                              <div class="_buttons">
                                   <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs pull-right hidden" id="toggleTableBtn" onclick="toggle_appointment_table_view(); return false;" data-toggle="tooltip" title="" data-original-title="Toggle Table">
                                        <i class="fa fa-angle-double-right"></i>
                                   </a>
                                   <a href="<?= admin_url('appointly/appointments'); ?>" class="btn btn-primary pull-left display-block">
                                        <?= ucfirst(_l('appointments')); ?>
                                   </a>
                              </div>
                              <div class="clearfix"></div>
                              <hr class="hr-panel-heading" />
                              <div class="table-responsive">
                                   <table class="table table-appointments-history">
                                        <thead>
                                             <tr>
                                                  <th><?= _l('id'); ?></th>
                                                  <th><?= _l('appointment_subject'); ?></th>
                                                  <th><?= _l('appointment_feedback_label'); ?></th>
                                                  <th><?= _l('appointment_meeting_date'); ?></th>
                                                  <th><?= _l('appointment_initiated_by'); ?></th>
                                                  <th><?= _l('appointment_description'); ?></th>
                                                  <th><?= _l('appointment_source'); ?></th>
                                             </tr>
                                        </thead>
                                        <tbody></tbody>
                                   </table>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</div>
<?php init_tail(); ?>
<?php require('modules/appointly/assets/js/history_main_js.php'); ?>
</body>

</html>
