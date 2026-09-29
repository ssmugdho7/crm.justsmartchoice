<!-- modules/team_manager/views/teams/index.php -->
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="panel_s">
         <div class="panel-body">
            <div class="row mbot15">
               <div class="col-md-6">
                  <h4 class="no-margin font-bold"><?php echo _l('team_manager'); ?></h4>
               </div>
               <div class="col-md-6 text-right">
                  <a href="<?php echo admin_url('team_manager/add_team'); ?>" class="btn btn-primary">
                     <i class="fa fa-plus"></i> <?php echo _l('add_team'); ?>
                  </a>
               </div>
            </div>
            <hr class="hr-panel-heading" />
            <div class="table-responsive">
               <table class="table dt-table table-striped" data-order-col="0" data-order-type="desc">
                  <thead>
                     <tr>
                        <th><?php echo _l('id'); ?></th>
                        <th><?php echo _l('team_name'); ?></th>
                        <th><?php echo _l('leader'); ?></th>
                        <th><?php echo _l('subleader'); ?></th>
                        <th><?php echo _l('options'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach($teams as $team): ?>
                     <tr>
                        <td><?php echo $team['id']; ?></td>
                        <td><?php echo $team['name']; ?></td>
                        <td>
                           <?php echo isset($staff_dropdown[$team['leader']]) ? $staff_dropdown[$team['leader']] : $team['leader']; ?>
                        </td>
                        <td>
                           <?php echo ($team['subleader'] && isset($staff_dropdown[$team['subleader']])) ? $staff_dropdown[$team['subleader']] : _l('not_set'); ?>
                        </td>
                        <td>
                           <a href="<?php echo admin_url('team_manager/view_team/'.$team['id']); ?>" class="btn btn-info btn-icon">
                              <i class="fa fa-eye"></i>
                           </a>
                           <a href="<?php echo admin_url('team_manager/edit_team/'.$team['id']); ?>" class="btn btn-warning btn-icon">
                              <i class="fa fa-edit"></i>
                           </a>
                           <a href="<?php echo admin_url('team_manager/delete_team/'.$team['id']); ?>" class="btn btn-danger btn-icon _delete">
                              <i class="fa fa-remove"></i>
                           </a>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
