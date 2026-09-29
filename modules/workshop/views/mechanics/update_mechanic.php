<div class="modal fade" id="appointmentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <?php
                $title = '';
                $staffid = '';

                if (isset($mechanic)) {
                    $title .= _l('wshop_update_mechanic');
                    $staffid = $mechanic->staffid;
                    echo form_hidden('memberid', $staffid);
                    echo form_hidden('isedit');

                } else {

                    $title .= _l('add_staff_profile');
                }
                ?>
                <h4 class="modal-title"><?php echo new_html_entity_decode($title); ?></h4>
            </div>

            <?php echo form_open_multipart(admin_url('workshop/add_edit_mechanic/' . $staffid), array('id' => 'add_edit_mechanic')); ?>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
                            <?php echo _l('staff_profile_string'); ?>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                  <div class="manage_staff hide">
                    <?php
                    if (isset($manage_staff)) {
                        echo form_hidden('manage_staff');
                    }
                    ?>
                </div>
                <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">

                    <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'two-factor-authentication', 'active' => 0)) == 0) {?>
                     <div class="checkbox checkbox-primary">
                        <input type="checkbox" value="1" name="two_factor_auth_enabled" id="two_factor_auth_enabled"<?php if (isset($mechanic) && $mechanic->two_factor_auth_enabled == 1) {echo ' checked';}?>>
                        <label for="two_factor_auth_enabled"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('two_factor_authentication_info'); ?>"></i>
                            <?php echo _l('enable_two_factor_authentication'); ?></label>
                        </div>
                    <?php }?>

                    <div class="col-md-12">
                        <div class="picture-container pull-left">
                            <div class="picture pull-left">
                                <?php echo staff_profile_image($mechanic->staffid, array('img', 'img-responsive', 'staff-profile-image-thumb', 'picture-src'), 'thumb', ['id' => 'wizardPicturePreview']); ?>
                                <input type="file" name="profile_image" class="form-control" id="profile_image" accept=".png, .jpg, .jpeg">
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <br>
                    <div class="clearfix"></div>

                    <div class="row">
                        <?php $value = (isset($mechanic) ? $mechanic->firstname : '');?>
                        <?php $lastname = (isset($mechanic) ? $mechanic->lastname : '');?>
                        <?php $attrs = (isset($mechanic) ? array() : array('autofocus' => true));?>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('firstname', 'staff_add_edit_firstname', $value, 'text', $attrs); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_input('lastname', 'staff_add_edit_lastname', $lastname, 'text', $attrs); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php $value = (isset($mechanic) ? $mechanic->email : '');?>
                            <div class="form-group" app-field-wrapper="email">
                                <label for="email" class="control-label"><?php echo _l('staff_add_edit_email'); ?></label>
                                <input type="email" id="email" name="email" class="form-control" autocomplete="off" value="<?php echo new_html_entity_decode($value) ?>" <?php if (!is_admin() && !has_permission('workshop_mechanic', '', 'edit') && !has_permission('workshop_mechanic', '', 'create')) {echo 'disabled';}?>>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <?php $value = (isset($mechanic) ? $mechanic->phonenumber : '');?>
                            <?php echo render_input('phonenumber', 'staff_add_edit_phonenumber', $value); ?>
                        </div>
                    </div>

                    <?php if (is_admin() || has_permission('workshop_mechanic', '', 'edit') || has_permission('workshop_mechanic', '', 'create')) {
                        ?>
                        <?php
                        hooks()->do_action('staff_render_permissions');
                        $selected = '';
                        foreach ($roles_value as $role_value) {
                          if (isset($mechanic)) {
                           if ($mechanic->role == $role_value['roleid']) {
                            $selected = $role_value['roleid'];
                        }
                    } else {
                        $default_staff_role = $mechanic_role;
                        if ($default_staff_role == $role_value['roleid']) {
                            $selected = $role_value['roleid'];
                        }
                    }
                }
                ?>
                <div class="hide">
                    <?php echo render_select('role_v', $roles_value, array('roleid', 'name'), 'staff_add_edit_role', $selected); ?>
                </div>
            <?php }?>

            <div class="row">
                <div class="col-md-6" id='div_hourly_rate'>
                    <div class="form-group">
                     <label for="hourly_rate"><?php echo _l('staff_hourly_rate'); ?></label>
                     <div class="input-group">
                       <input type="number" name="hourly_rate" value="<?php if (isset($mechanic)) {echo new_html_entity_decode($mechanic->hourly_rate);} else {echo 0;}?>" id="hourly_rate" class="form-control">
                       <span class="input-group-addon">
                           <?php echo new_html_entity_decode($base_currency->symbol); ?>
                       </span>
                   </div>
               </div>
           </div>
           <div class="col-md-6">
               <div class="form-group">
                 <label for="skype" class="control-label"><i class="fa-brands fa-skype"></i> <?php echo _l('staff_add_edit_skype'); ?></label>
                 <input type="text" class="form-control" name="skype" value="<?php if (isset($mechanic)) {echo new_html_entity_decode($mechanic->skype);}?>">
             </div>
         </div>

     </div>

     <div class="row">
        <div class="col-md-6">
          <div class="form-group">
              <label for="facebook" class="control-label"><i class="fa-brands fa-facebook"></i> <?php echo _l('staff_add_edit_facebook'); ?></label>
              <input type="text" class="form-control" name="facebook" value="<?php if (isset($mechanic)) {echo new_html_entity_decode($mechanic->facebook);}?>">
          </div>
      </div>
      <div class="col-md-6">
       <div class="form-group">
          <label for="linkedin" class="control-label"><i class="fa-brands fa-linkedin"></i> <?php echo _l('staff_add_edit_linkedin'); ?></label>
          <input type="text" class="form-control" name="linkedin" value="<?php if (isset($mechanic)) {echo new_html_entity_decode($mechanic->linkedin);}?>">
      </div>
  </div>
</div>


<div class="row">
  <div class="col-md-6">
    <?php if (get_option('disable_language') == 0) {
     ?>
     <div class="form-group">
         <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?></label>
         <select name="default_language" data-live-search="true" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
           <option value=""><?php echo _l('system_default_string'); ?></option>
           <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
              $selected = '';
              if (isset($mechanic)) {
               if ($mechanic->default_language == $availableLanguage) {
                $selected = 'selected';
            }
        }
        ?>
        <option value="<?php echo new_html_entity_decode($availableLanguage); ?>" <?php echo new_html_entity_decode($selected); ?>><?php echo ucfirst($availableLanguage); ?></option>
    <?php }?>
</select>
</div>
<?php }?>
</div>

<div class="col-md-6">
 <div class="form-group">
   <label for="direction"><?php echo _l('document_direction'); ?></label>
   <select class="selectpicker" data-none-selected-text="<?php echo _l('system_default_string'); ?>" data-width="100%" name="direction" id="direction">
     <option value="" <?php if (isset($mechanic) && empty($mechanic->direction)) {echo 'selected';}?>></option>
     <option value="ltr" <?php if (isset($mechanic) && $mechanic->direction == 'ltr') {echo 'selected';}?>>LTR</option>
     <option value="rtl" <?php if (isset($mechanic) && $mechanic->direction == 'rtl') {echo 'selected';}?>>RTL</option>
 </select>
</div>
</div>
</div>


<?php if (is_admin() || has_permission('workshop_mechanic', '', 'edit')) {
    ?>
    <div class="form-group">
      <div class="row">
        <div class="col-md-12">
           <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('staff_email_signature_help'); ?>"></i>
           <?php $value = (isset($mechanic) ? $mechanic->email_signature : '');?>
           <?php echo render_textarea('email_signature', 'settings_email_signature', $value, ['data-entities-encode' => 'true']); ?>
       </div>

   </div>

   <br>
   <?php if (count($departments) > 0) {?>
     <label for="departments"><?php echo _l('staff_add_edit_departments'); ?></label>
 <?php }?>

 <?php foreach ($departments as $department) {
  ?>
  <div class="checkbox checkbox-primary">
    <?php
    $checked = '';
    if (isset($mechanic)) {
       foreach ($staff_departments as $staff_department) {
        if ($staff_department['departmentid'] == $department['departmentid']) {
         $checked = ' checked';
     }
 }
}
?>
<input type="checkbox" id="dep_<?php echo new_html_entity_decode($department['departmentid']); ?>" name="departments[]" value="<?php echo new_html_entity_decode($department['departmentid']); ?>"<?php echo new_html_entity_decode($checked); ?>>
<label for="dep_<?php echo new_html_entity_decode($department['departmentid']); ?>"><?php echo new_html_entity_decode($department['name']); ?></label>
</div>
<?php }?>
</div>
<?php }?>

<?php $rel_id = (isset($mechanic) ? $mechanic->staffid : false);?>
<?php echo render_custom_fields('staff', $rel_id); ?>

<div class="row">
   <div class="col-md-12">
      <hr class="hr-10" />


      <?php if (!isset($mechanic) && total_rows(db_prefix() . 'emailtemplates', array('slug' => 'new-staff-created', 'active' => 0)) === 0) {?>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="send_welcome_email" id="send_welcome_email" checked>
            <label for="send_welcome_email"><?php echo _l('staff_send_welcome_email'); ?></label>
        </div>
    <?php }?>
</div>
</div>

<?php if (!isset($mechanic) || is_admin() || !is_admin() && $mechanic->admin == 0) {?>
 <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
 <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1"/>
 <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>
 <div class="clearfix form-group"></div>
 <label for="password" class="control-label"><?php echo _l('staff_add_edit_password'); ?></label>
 <div class="input-group">
  <input type="password" class="form-control password" name="password" autocomplete="off">
  <span class="input-group-addon">
    <a href="#password" class="show_password" onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
</span>
<span class="input-group-addon">
    <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
</span>
</div>
<?php if (isset($mechanic)) {?>
 <p class="text-muted"><?php echo _l('staff_add_edit_password_note'); ?></p>
 <?php if ($mechanic->last_password_change != NULL) {?>
   <?php echo _l('staff_add_edit_password_last_changed'); ?>:
   <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($mechanic->last_password_change); ?>">
    <?php echo time_ago($mechanic->last_password_change); ?>
</span>
<?php }}?>
<?php }?>

</div>

<div role="tabpanel" class="tab-pane hide" id="staff_permissions">
    <div class="table-responsive">

        <table class="table table-bordered roles no-margin">
          <thead>
           <tr>
            <th>Feature</th>
            <th>Capabilities</th>
        </tr>
    </thead>
    <tbody>
     <?php
     if (isset($mechanic)) {
         $is_admin = is_admin($mechanic->staffid);
     }

     foreach (get_available_staff_permissions($funcData) as $feature => $permission) {
         ?>
         <tr data-name="<?php echo new_html_entity_decode($feature); ?>" >
          <td>
           <b><?php echo new_html_entity_decode($permission['name']); ?></b>
       </td>
       <td>
           <?php
           if (isset($permission['before'])) {
              echo new_html_entity_decode($permission['before']);
          }
          ?>
          <?php foreach ($permission['capabilities'] as $capability => $name) {
              $checked = '';
              $disabled = '';
              if ((isset($is_admin) && $is_admin) ||
               (is_array($name) && isset($name['not_applicable']) && $name['not_applicable']) ||
               (
                ($capability == 'view_own' || $capability == 'view'
                 && array_key_exists('view_own', $permission['capabilities']) && array_key_exists('view', $permission['capabilities']))
                &&
                ((isset($mechanic)
                 && staff_can(($capability == 'view' ? 'view_own' : 'view'), $feature, $mechanic->staffid))
                ||
                (isset($role)
                  && has_role_permission($role->roleid, ($capability == 'view' ? 'view_own' : 'view'), $feature))
            )
            )
           ) {
               $disabled = ' disabled ';
       } else if ((isset($mechanic) && staff_can($capability, $feature, $mechanic->staffid))
           || isset($role) && has_role_permission($role->roleid, $capability, $feature)) {
           $checked = ' checked ';
       }
       ?>
       <div class="checkbox">
          <input
          <?php if ($capability == 'view') {?> data-can-view <?php }?>
          <?php if ($capability == 'view_own') {?> data-can-view-own <?php }?>
          <?php if (is_array($name) && isset($name['not_applicable']) && $name['not_applicable']) {?> data-not-applicable="true" <?php }?>
          type="checkbox"
          <?php echo new_html_entity_decode($checked); ?>
          class="capability"
          id="<?php echo new_html_entity_decode($feature . '_' . $capability); ?>"
          name="permissions[<?php echo new_html_entity_decode($feature); ?>][]"
          value="<?php echo new_html_entity_decode($capability); ?>"
          <?php echo new_html_entity_decode($disabled); ?>>
          <label for="<?php echo new_html_entity_decode($feature . '_' . $capability); ?>">
            <?php echo !is_array($name) ? $name : $name['name']; ?>
        </label>
        <?php
        if (isset($permission['help']) && array_key_exists($capability, $permission['help'])) {
           echo '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . $permission['help'][$capability] . '"></i>';
       }
       ?>
   </div>
<?php }?>
<?php
if (isset($permission['after'])) {
  echo new_html_entity_decode($permission['after']);
}
?>
</td>
</tr>
<?php }?>
</tbody>
</table>
</div>
</div>



</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default close_btn" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>
</div><!-- /.modal-content -->

</div>
</div>
<?php
require 'modules/workshop/assets/js/mechanics/add_update_mechanic_js.php';
?>
