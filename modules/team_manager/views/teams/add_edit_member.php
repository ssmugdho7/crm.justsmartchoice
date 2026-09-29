<!-- modules/team_manager/views/teams/add_edit_member.php -->
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="panel_s">
         <div class="panel-body">
            <div class="row mbot15">
               <div class="col-md-8">
                  <h4 class="no-margin font-bold">
                     <?php echo isset($member) ? _l('edit_team_member') : _l('add_team_member'); ?>
                  </h4>
               </div>
               <div class="col-md-4 text-right">
                  <a href="<?php echo admin_url('team_manager/view_team/' . $team_id); ?>" class="btn btn-default">
                     <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                  </a>
               </div>
            </div>
            <hr class="hr-panel-heading" />
            <?php echo form_open(); ?>
               <div class="form-group">
                  <label for="staff_id"><?php echo _l('staff'); ?></label>
                  <?php echo form_dropdown('staff_id', $staff_dropdown, isset($member) ? $member['staff_id'] : '', ['class' => 'form-control', 'required' => 'required']); ?>
               </div>
               <button type="submit" class="btn btn-primary">
                  <?php echo _l('save'); ?>
               </button>
            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
