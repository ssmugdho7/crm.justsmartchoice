   
    <div class="modal fade z-index-none" id="inspection_template_form_detailModal">
        <div class="modal-dialog setting-transaction-table">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($inspection_template_form_detail)){
                    $id = $inspection_template_form_detail->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_inspection_template_form_detail/'.$id), array('id' => 'add_edit_inspection_template_form_detail', 'autocomplete'=>'off')); ?>
                
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <input type="hidden" name="inspection_template_form_id" value="<?php echo html_entity_decode($inspection_template_form_id) ?>">
                <input type="hidden" name="fieldto" value="form_fieldset_<?php echo html_entity_decode($inspection_template_form_id) ?>">

                <div class="modal-body">

                    <div class="company_field_info mbot25 alert alert-info<?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->fieldto != 'company' || !isset($inspection_template_form_detail)) {
                        echo ' hide';
                    } ?>">
                    <?php echo _l('inspection_template_form_detail_info_format_embed_info', [
                        _l('inspection_template_form_detail_company'),
                        '<a href="' . admin_url('settings?group=company#settings[company_info_format]') . '" target="_blank">' . admin_url('settings?group=company') . '</a>',
                    ]); ?>
                </div>
                <div class="customers_field_info mbot25 alert alert-info<?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->fieldto != 'customers' || !isset($inspection_template_form_detail)) {
                    echo ' hide';
                } ?>">
                <?php echo _l('inspection_template_form_detail_info_format_embed_info', [
                    _l('clients'),
                    '<a href="' . admin_url('settings?group=clients#settings[customer_info_format]') . '" target="_blank">' . admin_url('settings?group=clients') . '</a>',
                ]); ?>
            </div>
            <div class="items_field_info mbot25 alert alert-warning<?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->fieldto != 'items' || !isset($inspection_template_form_detail)) {
                echo ' hide';
            } ?>">
            Custom fields for items can't be included in calculation of totals.
        </div>
        <div class="proposal_field_info mbot25 alert alert-info<?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->fieldto != 'proposal' || !isset($inspection_template_form_detail)) {
            echo ' hide';
        } ?>">
        <?php echo _l('inspection_template_form_detail_info_format_embed_info', [
            _l('proposals'),
            '<a href="' . admin_url('settings?group=sales&tab=proposals#settings[proposal_info_format]') . '" target="_blank">' . admin_url('settings?group=sales&tab=proposals') . '</a>',
        ]); ?>
    </div>


    <?php
    $disable = '';
    if (isset($inspection_template_form_detail)) {
        if (total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $inspection_template_form_detail->id, 'fieldto' => $inspection_template_form_detail->fieldto]) > 0) {
            $disable = 'disabled';
        }
    }
    ?>


    
<div class="clearfix"></div>
<?php $value = (isset($inspection_template_form_detail) ? $inspection_template_form_detail->name : ''); ?>


<?php   echo render_textarea('name','wshop_question_name', $value, array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($inspection_template_form_detail) || isset($inspection_template_form_detail) && $inspection_template_form_detail->name == '' ? 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' : 'form_init_editor(\'.tinymce\', {height:200, auto_focus: true});' )),array(),'','tinymce'); ?>


<div class="select-placeholder form-group">
    <label for="type"><?php echo _l('inspection_template_form_detail_add_edit_type'); ?></label>
    <select name="type" id="type" class="selectpicker" <?php if (isset($inspection_template_form_detail) && total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $inspection_template_form_detail->id, 'fieldto' => $inspection_template_form_detail->fieldto]) > 0) {
        echo ' disabled';
    } ?> data-width="100%"
    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
    data-hide-disabled="true">
    <option value=""></option>
    <option value="input" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'input') {
        echo 'selected';
    } ?>>Input</option>
    <option value="number" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'number') {
        echo 'selected';
    } ?>>Number</option>
    <option value="textarea" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'textarea') {
        echo 'selected';
    } ?>>Textarea</option>
    <option value="select" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'select') {
        echo 'selected';
    } ?>>Select</option>
    <option value="multiselect" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'multiselect') {
        echo 'selected';
    } ?>>Multi Select</option>
    <option value="checkbox" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'checkbox') {
        echo 'selected';
    } ?>>Checkbox</option>
    <option value="date_picker" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'date_picker') {
        echo 'selected';
    } ?>>Date Picker</option>
    <option value="date_picker_time" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'date_picker_time') {
        echo 'selected';
    } ?>>Datetime Picker</option>
    <option value="attachment" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->type == 'attachment') {
        echo 'selected';
    } ?>>Attachement</option>
    
   
</select>
</div>
<div class="clearfix"></div>
<div id="options_wrapper" class="<?php if (!isset($inspection_template_form_detail) || isset($inspection_template_form_detail) && $inspection_template_form_detail->type != 'select' && $inspection_template_form_detail->type != 'checkbox' && $inspection_template_form_detail->type != 'multiselect') {
    echo 'hide';
} ?>">

<label><?php echo _l('inspection_template_form_detail_add_edit_options'); ?></label>
<div class="list-option">
    <?php if(isset($inspection_template_form_detail) && ($inspection_template_form_detail->type == 'select' || $inspection_template_form_detail->type == 'checkbox' || $inspection_template_form_detail->type == 'multiselect') ){ ?>
        <?php 
        $options = json_decode($inspection_template_form_detail->options ?? '');

        foreach ($options as $key => $value) {
         ?>
    <div class="row">
        <div class="col-md-10">
            <?php echo render_input('options[]', '', $value, 'text', array('placeholder' => _l('wshop_option'))); ?>
        </div>
        <div class="col-md-2">
            <?php if($key == 0){ ?>
            <button type="button" class="btn btn-success add_new_row">
                <i class="fa fa-plus"></i>
            </button>
        <?php }else{ ?>
            <button type="button" class="btn remove_row btn-danger">
                <i class="fa fa-minus"></i>
            </button>
        <?php } ?>
        </div>
    </div>
<?php } ?>

    <?php }else{ ?>
        <div class="row">
            <div class="col-md-10">
                <?php echo render_input('options[]', '', '', 'text', array('placeholder' => _l('wshop_option'))); ?>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-success add_new_row">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    <?php } ?>

</div>

</div>
<div id="default-value-field" class="hide">
    <?php
    $value = (isset($inspection_template_form_detail) ? $inspection_template_form_detail->default_value : '');

    echo render_textarea(
        isset($inspection_template_form_detail) && $inspection_template_form_detail->type === 'textarea' ? 'default_value' : '',
        'inspection_template_form_detail_add_edit_default_value',
        $value,
        [],
        [],
        'default-value-textarea-input' . (isset($inspection_template_form_detail) && ($inspection_template_form_detail->type !== 'textarea' || $inspection_template_form_detail->type === 'link') ? ' hide' : ''),
        'default-value'
    );

    echo render_input(
        isset($inspection_template_form_detail) && !in_array($inspection_template_form_detail->type, ['textarea', 'link']) ? 'default_value' : '',
        'inspection_template_form_detail_add_edit_default_value',
        $value,
        'text',
        [],
        [],
        'default-value-text-input' . (isset($inspection_template_form_detail) && ($inspection_template_form_detail->type == 'link' || $inspection_template_form_detail->type === 'textarea') ? ' hide' : ''),
        'default-value'
    );
    ?>
</div>
<div id="default-value-error" class="hide alert alert-danger"></div>
<?php $value = (isset($inspection_template_form_detail) ? $inspection_template_form_detail->field_order : ''); ?>
<div class="hide">
    <?php echo render_input('field_order', 'inspection_template_form_detail_add_edit_order', $value, 'number'); ?>
</div>
<div class="form-group hide">
    <label for="bs_column"><?php echo _l('inspection_template_form_detail_column'); ?></label>
    <div class="input-group">
        <span class="input-group-addon">col-md-</span>
        <input type="number" max="12" class="form-control" name="bs_column" id="bs_column"
        value="<?php if (!isset($inspection_template_form_detail)) {
            echo 12;
        } else {
            echo new_html_entity_decode($inspection_template_form_detail->bs_column);
        } ?>">
    </div>
</div>
<div class="checkbox checkbox-primary hide">
    <input type="checkbox" name="disabled" id="disabled" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->active == 0) {
        echo 'checked';
    } ?>>
    <label for="disabled"><?php echo _l('inspection_template_form_detail_add_edit_disabled'); ?></label>
</div>
<div class="display-inline-checkbox checkbox checkbox-primary<?php if (!isset($inspection_template_form_detail) || isset($inspection_template_form_detail) && $inspection_template_form_detail->type != 'checkbox') {
    echo ' hide';
} ?>">
<input type="checkbox" value="1" name="display_inline" id="display_inline" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->display_inline == 1) {
    echo 'checked';
} ?>>
<label for="display_inline"><?php echo _l('display_inline'); ?></label>
</div>

<div class="checkbox checkbox-primary" id="required_wrap">
    <input type="checkbox" name="required" id="required" <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->required == 1) {
        echo 'checked';
    } ?> <?php if (isset($inspection_template_form_detail) && $inspection_template_form_detail->fieldto == 'company') {
        echo 'disabled';
    } ?>>
    <label for="required"><?php echo _l('inspection_template_form_detail_required'); ?></label>
</div>
<p class="bold  hide"><?php echo _l('inspection_template_form_detail_visibility'); ?></p>


</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-info inspection_template_form_detail_submit_button"><?php echo _l('submit'); ?></button>
</div>

</div>

<?php echo form_close(); ?>
</div>
</div>

<?php require 'modules/workshop/assets/js/settings/inspection_templates/inspection_template_forms/inspection_template_form_detail_modal_js.php';  ?>
