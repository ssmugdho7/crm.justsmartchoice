<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id'=>'form_main')); ?>
                        <?php if(isset($back_url)){ ?>
                            <input type="hidden" name="back_url" value="<?php echo $back_url; ?>">
                        <?php } ?>
                        <?php $attrs = (isset($book) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($book) ? $book->name : ''); ?>
                        <?php echo render_input('name', 'Manual Name', $value, 'text', $attrs); ?>
                        <?php $value = (isset($book) ? $book->short_description : ''); ?>
                        <?php echo render_textarea('short_description', 'Manual Short Description', $value); ?>
                        <div class="form-group">
                            <label><?php echo _l('training_manual_manual_cover_image'); ?></label>
                            <input type="file" name="cover_image_file" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <p class="text-muted small">
                                <?php echo _l('training_manual_manual_cover_image_help'); ?><br>
                                <strong><?php echo _l('training_manual_recommended_dimensions'); ?>:</strong> 1200 × 675 px (16:9).<br>
                                <strong><?php echo _l('training_manual_allowed_formats'); ?>:</strong> JPG, PNG, or WebP.<br>
                                <strong><?php echo _l('training_manual_maximum_file_size'); ?>:</strong> 2 MB.
                            </p>
                            <?php if(isset($book) && !empty($book->cover_image)){ ?>
                                <img src="<?php echo base_url('uploads/training_manual/'.$book->cover_image); ?>" alt="<?php echo html_escape(_l('training_manual_manual_cover_image')); ?>" style="max-width:220px;border-radius:10px;margin-top:10px">
                            <?php } ?>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="customer_visible" id="customer_visible" value="1" <?php echo isset($book) && !empty($book->customer_visible) ? 'checked' : ''; ?>>
                            <label for="customer_visible"><strong><?php echo _l('training_manual_show_in_customer_area'); ?></strong></label>
                            <p class="text-muted small"><?php echo _l('training_manual_show_in_customer_area_help'); ?></p>
                        </div>
                        <?php $area_value=isset($book)&&!empty($book->area_label)?$book->area_label:(isset($book)&&!empty($book->customer_visible)?'Customer Area':'CRM Center'); ?>
                        <?php echo render_input('area_label','Area Display Name',$area_value,'text',['placeholder'=>'Customer Area, CRM Center, or Employee Center']); ?>
                        <p class="text-muted small">Checked books appear for logged-in customers. Unchecked books remain internal.</p>
                        <label for="specific_staff"><?php echo 'Permission For Views'; ?></label>
                        <div class="select-notification-settings">
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="assign_type" value="specific_staff" id="specific_staff" <?php if (isset($book) && $book->assign_type ==  'specific_staff' || !isset($book)) { echo 'checked'; } ?>>
                                <label for="specific_staff"><?php echo 'Specific Staff Members'; ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="assign_type" id="roles" value="roles" <?php if (isset($book) && $book->assign_type == 'roles') {
                                echo 'checked';
                                } ?>>
                                <label for="roles"><?php echo 'Staff With Roles'; ?></label>
                            </div>
                            <div class="clearfix mtop15"></div>
                            <div id="specific_staff_assign" class="types-assign <?php if (isset($book) && $book->assign_type != 'specific_staff') { echo 'hide'; } ?>">
                                <?php
                                    $selected = array();
                                    if (isset($book) && $book->assign_type == 'specific_staff') {
                                        $selected = training_manual_unserialize($book->assign_ids, 'staff_');
                                    }
                                ?>
                                <?php echo render_select('assign_ids_staff[]', $members, array('staffid', array('firstname', 'lastname')), 'Assign To Specific Employee', $selected, array('multiple'=>true)); ?>
                            </div>
                            <div id="roles_assign" class="types-assign <?php if (isset($book) && $book->assign_type != 'roles' || !isset($book)) {
                                echo 'hide';} ?>">
                                <?php
                                    $selected = array();
                                    if (isset($book) && $book->assign_type == 'roles') {
                                        $selected = training_manual_unserialize($book->assign_ids, 'role_');
                                    }
                                ?>
                                <?php echo render_select('assign_ids_roles[]', $roles, array('roleid', array('name')), 'Assign To Staff Roles', $selected, array('multiple'=>true)); ?>
                            </div>
                        </div>

                        <div class="training_manual-form-buttons">
                            <a href="<?php  echo isset($back_url) ? $back_url : admin_url('training_manual/books'); ?>" class="btn btn-primary"><?php echo _l('back'); ?></a>
                            <?php if(isset($book) && has_permission('training_manual_books','','delete')){ ?>
                                <a href="<?php echo admin_url('training_manual/books/delete/' . $book->id); ?>" class="btn btn-danger btn-remove" data-lang="<?php echo _l('training_manual_confirm_delete'); ?>"><?php echo _l('delete'); ?></a>
                            <?php } ?>
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/js/book.js'); ?>"></script>
</body>
</html>
