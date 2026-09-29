<!-- modules/team_manager/views/teams/add_edit.php -->
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="panel_s">
         <div class="panel-body">
            <div class="row mbot15">
               <div class="col-md-8">
                  <h4 class="no-margin font-bold">
                     <?php echo isset($team) ? _l('edit_team') : _l('add_team'); ?>
                  </h4>
               </div>
               <div class="col-md-4 text-right">
                  <a href="<?php echo admin_url('team_manager'); ?>" class="btn btn-default">
                     <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                  </a>
               </div>
            </div>
            <hr class="hr-panel-heading" />
            <?php echo form_open(); ?>
               <div class="form-group">
                  <label for="name"><?php echo _l('team_name'); ?></label>
                  <input type="text" name="name" class="form-control" value="<?php echo isset($team) ? $team->name : ''; ?>" required>
               </div>
               <div class="form-group">
                  <label for="leader"><?php echo _l('leader'); ?></label>
                  <?php echo form_dropdown('leader', $staff_dropdown, isset($team) ? $team->leader : '', ['class' => 'form-control', 'required' => 'required']); ?>
               </div>
               <div class="form-group">
                  <label for="subleader"><?php echo _l('subleader'); ?> (<?php echo _l('optional'); ?>)</label>
                  <?php echo form_dropdown('subleader', ['' => _l('please_select')] + $staff_dropdown, isset($team) ? $team->subleader : '', ['class' => 'form-control']); ?>
               </div>
               <div class="form-group">
                  <label for="description"><?php echo _l('description'); ?></label>
                  <textarea name="description" class="form-control" rows="4"><?php echo isset($team) ? $team->description : ''; ?></textarea>
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
