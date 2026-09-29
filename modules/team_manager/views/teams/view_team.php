<!-- modules/team_manager/views/teams/view_team.php -->
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <!-- Team Details Panel -->
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row mbot15">
                     <div class="col-md-8">
                        <h4 class="no-margin font-bold">
                           <?php echo _l('team_details'); ?>: <?php echo $team->name; ?>
                        </h4>
                     </div>
                     <div class="col-md-4 text-right">
                        <a href="<?php echo admin_url('team_manager/edit_team/'.$team->id); ?>" class="btn btn-warning">
                           <i class="fa fa-edit"></i> <?php echo _l('edit_team'); ?>
                        </a>
                        <a href="<?php echo admin_url('team_manager/add_member/'.$team->id); ?>" class="btn btn-primary">
                           <i class="fa fa-plus"></i> <?php echo _l('add_team_member'); ?>
                        </a>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <div class="row">
                     <div class="col-md-6">
                        <p><strong><?php echo _l('leader'); ?>:</strong> <?php echo isset($staff_dropdown[$team->leader]) ? $staff_dropdown[$team->leader] : $team->leader; ?></p>
                     </div>
                     <div class="col-md-6">
                        <p><strong><?php echo _l('subleader'); ?>:</strong> <?php echo ($team->subleader && isset($staff_dropdown[$team->subleader])) ? $staff_dropdown[$team->subleader] : _l('not_set'); ?></p>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <p><strong><?php echo _l('description'); ?>:</strong></p>
                        <p><?php echo nl2br($team->description); ?></p>
                     </div>
                  </div>
               </div>
            </div>
            <!-- Team Members Panel -->
            <div class="panel_s">
               <div class="panel-heading">
                  <h5 class="panel-title"><?php echo _l('team_members'); ?></h5>
               </div>
               <div class="panel-body">
                  <div class="table-responsive">
                     <table class="table dt-table table-striped" data-order-col="0" data-order-type="desc">
                        <thead>
                           <tr>
                              <th><?php echo _l('id'); ?></th>
                              <th><?php echo _l('staff'); ?></th>
                              <th><?php echo _l('options'); ?></th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php if(count($members) > 0) { ?>
                              <?php foreach($members as $member): ?>
                                 <tr>
                                    <td><?php echo $member['id']; ?></td>
                                    <td><?php echo isset($staff_dropdown[$member['staff_id']]) ? $staff_dropdown[$member['staff_id']] : $member['staff_id']; ?></td>
                                    <td>
                                       <a href="<?php echo admin_url('team_manager/edit_member/'.$member['id']); ?>" class="btn btn-warning btn-icon">
                                          <i class="fa fa-edit"></i>
                                       </a>
                                       <a href="<?php echo admin_url('team_manager/delete_member/'.$member['id']); ?>" class="btn btn-danger btn-icon _delete">
                                          <i class="fa fa-remove"></i>
                                       </a>
                                    </td>
                                 </tr>
                              <?php endforeach; ?>
                           <?php } else { ?>
                              <tr>
                                 <td colspan="3"><?php echo _l('no_records_found'); ?></td>
                              </tr>
                           <?php } ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <a href="<?php echo admin_url('team_manager'); ?>" class="btn btn-default">
               <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
            </a>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
