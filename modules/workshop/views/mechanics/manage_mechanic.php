<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php  init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (is_admin() || has_permission('workshop_mechanic','','create') || has_permission('workshop_mechanic','','edit')) { ?>
                                <a href="<?php echo admin_url('workshop/new_mechanic'); ?>" class="btn mright5 btn-info pull-left display-block "><?php echo _l('wshop_new_mechanic'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row">

                            <div class="col-md-3  pull-right  hide">
                                <select name="mechanic_role[]" class="selectpicker" id="mechanic_role" data-width="100%"  data-live-search="true" data-none-selected-text="<?php echo _l('acs_roles'); ?>" multiple="true"> 
                                    <?php 
                                    foreach ($roles as $role) { ?>
                                        <option value="<?php echo new_html_entity_decode($role['roleid']); ?>"><?php echo new_html_entity_decode($role['name']) ?></option>
                                    <?php }
                                    ?>              
                                </select>
                            </div>

                            <div class="col-md-3  pull-right">
                                <select name="deparment" class="selectpicker" id="deparment" data-width="100%"  data-live-search="true" data-none-selected-text="<?php echo _l('departments'); ?>"> 
                                    <option value=""></option>
                                    <?php 
                                    foreach ($departments as $value) { ?>
                                        <option value="<?php echo new_html_entity_decode($value['departmentid']); ?>"><?php echo new_html_entity_decode($value['name']) ?></option>
                                    <?php }
                                    ?>              
                                </select>
                            </div>
                        </div>
                        <br>

                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $table_data = array(
                                    '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="table_staff"><label></label></div>',
                                    _l('staff_dt_name'),
                                    _l('staff_dt_email'),
                                    _l('departments'),       
                                    _l('role'),
                                    _l('wshop_active_label'),
                                );
                                $custom_fields = get_custom_fields('staff',array('show_on_table'=>1));
                                foreach($custom_fields as $field){
                                    array_push($table_data,$field['name']);
                                }

                                render_datatable($table_data,'table_staff',
                                    array('customizable-table'),
                                    array(
                                        'id'=>'table-table_staff',
                                        'data-last-order-identifier'=>'table_staff',
                                        'data-default-order'=>get_table_last_order('table_staff'),
                                    )); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="delete_staff" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <?php echo form_open(admin_url('workshop/delete_staff',array('delete_staff_form'))); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="delete_id">
                        <?php echo form_hidden('id'); ?>
                    </div>
                    <p><?php echo _l('delete_staff_info'); ?></p>
                    <?php
                    echo render_select('transfer_data_to',$staff_members,array('staffid',array('firstname','lastname')),'staff_member',get_staff_user_id(),array(),array(),'','',false);
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div id="modal_wrapper"></div>
    <?php init_tail(); ?>
    <?php 
    require('modules/workshop/assets/js/mechanics/mechanic_js.php');
    ?>
</body>
</html>
