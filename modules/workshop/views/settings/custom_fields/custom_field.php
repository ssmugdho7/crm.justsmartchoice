<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('wshop_customfields'); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <?php if(has_permission('workshop_setting', '', 'create')){ ?>
                                    <a href="#" onclick="custom_field_modal(0); return false;" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('workshop/setting?group=fieldsets'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <?php 

                        render_datatable(
                            array(
                                _l('id'),
                                _l('custom_field_name'),
                                _l('custom_field_add_edit_type'),
                                _l('custom_field_required'),
                                _l('custom_field_add_edit_options'),
                                _l('custom_field_add_edit_order'),
                                _l('wshop_status'),
                                _l('options'),
                            ),'customfield_table'
                        );
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_wrapper"></div>

<input type="hidden" name="fieldset_id" value="<?php echo html_entity_decode($fieldset_id) ?>">
<?php init_tail(); ?>

<?php 
require 'modules/workshop/assets/js/settings/custom_fields/manage_js.php';

?>
</body>
</html>
