<div class="modal fade z-index-none" id="inspection_template_formModal">
    <div class="modal-dialog setting-transaction-table">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
            </div>
            <?php 
            $id = '';
            
            if(isset($inspection_template_form)){
                $id = $inspection_template_form->id;
            }

            ?>
            <?php echo form_open_multipart(admin_url('workshop/add_edit_inspection_template_form/'.$id), array('id' => 'add_edit_inspection_template_form', 'autocomplete'=>'off')); ?>
            
            <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
            <input type="hidden" name="inspection_template_id" value="<?php echo html_entity_decode($inspection_template_id) ?>">

            <div class="modal-body">

                <div class="clearfix"></div>
                <?php $value = (isset($inspection_template_form) ? $inspection_template_form->name : ''); ?>
                <?php echo render_input('name', 'wshop_name', $value); ?>

                <div class="clearfix"></div>
                <?php $value = (isset($inspection_template_form) ? $inspection_template_form->description : ''); ?>
                <?php echo render_textarea('description', 'wshop_description', $value); ?>
                
                <div class="clearfix"></div>
                <div class="hide">
                    <?php $value = (isset($inspection_template_form) ? $inspection_template_form->form_order : 0); ?>
                    <?php echo render_input('form_order', 'custom_field_add_edit_order', $value, 'number'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info inspection_template_form_submit_button"><?php echo _l('submit'); ?></button>
            </div>

        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php require 'modules/workshop/assets/js/settings/inspection_templates/inspection_template_forms/inspection_template_form_modal_js.php';  ?>
