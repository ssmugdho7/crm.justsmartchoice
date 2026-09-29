    <div class="modal fade z-index-none" id="custom_fieldModal">
        <div class="modal-dialog setting-transaction-table">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo html_entity_decode($title) ?></h4>
                </div>
                <?php 
                $id = '';
                
                if(isset($custom_field)){
                    $id = $custom_field->id;
                }

                ?>
                <?php echo form_open_multipart(admin_url('workshop/add_edit_custom_field/'.$id), array('id' => 'add_edit_custom_field', 'autocomplete'=>'off')); ?>
                
                <input type="hidden" name="id" value="<?php echo html_entity_decode($id); ?>">
                <input type="hidden" name="fieldset_id" value="<?php echo html_entity_decode($fieldset_id) ?>">

                <div class="modal-body">

                    <div class="company_field_info mbot25 alert alert-info<?php if (isset($custom_field) && $custom_field->fieldto != 'company' || !isset($custom_field)) {
                        echo ' hide';
                    } ?>">
                    <?php echo _l('custom_field_info_format_embed_info', [
                        _l('custom_field_company'),
                        '<a href="' . admin_url('settings?group=company#settings[company_info_format]') . '" target="_blank">' . admin_url('settings?group=company') . '</a>',
                    ]); ?>
                </div>
                <div class="customers_field_info mbot25 alert alert-info<?php if (isset($custom_field) && $custom_field->fieldto != 'customers' || !isset($custom_field)) {
                    echo ' hide';
                } ?>">
                <?php echo _l('custom_field_info_format_embed_info', [
                    _l('clients'),
                    '<a href="' . admin_url('settings?group=clients#settings[customer_info_format]') . '" target="_blank">' . admin_url('settings?group=clients') . '</a>',
                ]); ?>
            </div>
            <div class="items_field_info mbot25 alert alert-warning<?php if (isset($custom_field) && $custom_field->fieldto != 'items' || !isset($custom_field)) {
                echo ' hide';
            } ?>">
            Custom fields for items can't be included in calculation of totals.
        </div>
        <div class="proposal_field_info mbot25 alert alert-info<?php if (isset($custom_field) && $custom_field->fieldto != 'proposal' || !isset($custom_field)) {
            echo ' hide';
        } ?>">
        <?php echo _l('custom_field_info_format_embed_info', [
            _l('proposals'),
            '<a href="' . admin_url('settings?group=sales&tab=proposals#settings[proposal_info_format]') . '" target="_blank">' . admin_url('settings?group=sales&tab=proposals') . '</a>',
        ]); ?>
    </div>


    <?php
    $disable = '';
    if (isset($custom_field)) {
        if (total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $custom_field->id, 'fieldto' => $custom_field->fieldto]) > 0) {
            $disable = 'disabled';
        }
    }
    ?>

    <?php echo render_select('fieldto', $fieldsets, ['fieldid', 'name'], 'custom_field_add_edit_belongs_top', 'fieldset_'.$fieldset_id, ['data-action-boxs' => 'true'], [], '', '' ); ?>

    
<div class="clearfix"></div>
<?php $value = (isset($custom_field) ? $custom_field->name : ''); ?>
<?php echo render_input('name', 'custom_field_name', $value); ?>
<div class="select-placeholder form-group">
    <label for="type"><?php echo _l('custom_field_add_edit_type'); ?></label>
    <select name="type" id="type" class="selectpicker" <?php if (isset($custom_field) && total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $custom_field->id, 'fieldto' => $custom_field->fieldto]) > 0) {
        echo ' disabled';
    } ?> data-width="100%"
    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
    data-hide-disabled="true">
    <option value=""></option>
    <option value="input" <?php if (isset($custom_field) && $custom_field->type == 'input') {
        echo 'selected';
    } ?>>Input</option>
    <option value="number" <?php if (isset($custom_field) && $custom_field->type == 'number') {
        echo 'selected';
    } ?>>Number</option>
    <option value="textarea" <?php if (isset($custom_field) && $custom_field->type == 'textarea') {
        echo 'selected';
    } ?>>Textarea</option>
    <option value="select" <?php if (isset($custom_field) && $custom_field->type == 'select') {
        echo 'selected';
    } ?>>Select</option>
    <option value="multiselect" <?php if (isset($custom_field) && $custom_field->type == 'multiselect') {
        echo 'selected';
    } ?>>Multi Select</option>
    <option value="checkbox" <?php if (isset($custom_field) && $custom_field->type == 'checkbox') {
        echo 'selected';
    } ?>>Checkbox</option>
    <option value="date_picker" <?php if (isset($custom_field) && $custom_field->type == 'date_picker') {
        echo 'selected';
    } ?>>Date Picker</option>
    <option value="date_picker_time" <?php if (isset($custom_field) && $custom_field->type == 'date_picker_time') {
        echo 'selected';
    } ?>>Datetime Picker</option>
   
</select>
</div>
<div class="clearfix"></div>
<div id="options_wrapper" class="<?php if (!isset($custom_field) || isset($custom_field) && $custom_field->type != 'select' && $custom_field->type != 'checkbox' && $custom_field->type != 'multiselect') {
    echo 'hide';
} ?>">

<label><?php echo _l('custom_field_add_edit_options'); ?></label>
<div class="list-option">
    <?php if(isset($custom_field) && ($custom_field->type == 'select' || $custom_field->type == 'checkbox' || $custom_field->type == 'multiselect') ){ ?>
        <?php 
        $options = json_decode($custom_field->options ?? '');

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
<div id="default-value-field">
    <?php
    $value = (isset($custom_field) ? $custom_field->default_value : '');

    echo render_textarea(
        isset($custom_field) && $custom_field->type === 'textarea' ? 'default_value' : '',
        'custom_field_add_edit_default_value',
        $value,
        [],
        [],
        'default-value-textarea-input' . (isset($custom_field) && ($custom_field->type !== 'textarea' || $custom_field->type === 'link') ? ' hide' : ''),
        'default-value'
    );

    echo render_input(
        isset($custom_field) && !in_array($custom_field->type, ['textarea', 'link']) ? 'default_value' : '',
        'custom_field_add_edit_default_value',
        $value,
        'text',
        [],
        [],
        'default-value-text-input' . (isset($custom_field) && ($custom_field->type == 'link' || $custom_field->type === 'textarea') ? ' hide' : ''),
        'default-value'
    );
    ?>
</div>
<div id="default-value-error" class="hide alert alert-danger"></div>
<?php $value = (isset($custom_field) ? $custom_field->field_order : ''); ?>
<?php echo render_input('field_order', 'custom_field_add_edit_order', $value, 'number'); ?>
<div class="form-group">
    <label for="bs_column"><?php echo _l('custom_field_column'); ?></label>
    <div class="input-group">
        <span class="input-group-addon">col-md-</span>
        <input type="number" max="12" class="form-control" name="bs_column" id="bs_column"
        value="<?php if (!isset($custom_field)) {
            echo 12;
        } else {
            echo new_html_entity_decode($custom_field->bs_column);
        } ?>">
    </div>
</div>
<div class="checkbox checkbox-primary hide">
    <input type="checkbox" name="disabled" id="disabled" <?php if (isset($custom_field) && $custom_field->active == 0) {
        echo 'checked';
    } ?>>
    <label for="disabled"><?php echo _l('custom_field_add_edit_disabled'); ?></label>
</div>
<div class="display-inline-checkbox checkbox checkbox-primary<?php if (!isset($custom_field) || isset($custom_field) && $custom_field->type != 'checkbox') {
    echo ' hide';
} ?>">
<input type="checkbox" value="1" name="display_inline" id="display_inline" <?php if (isset($custom_field) && $custom_field->display_inline == 1) {
    echo 'checked';
} ?>>
<label for="display_inline"><?php echo _l('display_inline'); ?></label>
</div>
<div class="checkbox checkbox-primary  hide">
    <input type="checkbox" name="only_admin" id="only_admin" <?php if (isset($custom_field) && $custom_field->only_admin == 1) {
        echo 'checked';
    } ?> <?php if (isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')) {
        echo 'disabled';
    } ?>>
    <label for="only_admin"><?php echo _l('custom_field_only_admin'); ?></label>
</div>
<div class="checkbox checkbox-primary hide">
<input type="checkbox" name="disalow_client_to_edit" id="disalow_client_to_edit" <?php if (isset($custom_field) && $custom_field->disalow_client_to_edit == 1) {
    echo 'checked';
} ?> <?php if (isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1')) {
    echo 'disabled';
} ?>>
<label for="disalow_client_to_edit">
    <?php echo _l('custom_field_disallow_customer_to_edit'); ?></label>
</div>
<div class="checkbox checkbox-primary" id="required_wrap">
    <input type="checkbox" name="required" id="required" <?php if (isset($custom_field) && $custom_field->required == 1) {
        echo 'checked';
    } ?> <?php if (isset($custom_field) && $custom_field->fieldto == 'company') {
        echo 'disabled';
    } ?>>
    <label for="required"><?php echo _l('custom_field_required'); ?></label>
</div>
<p class="bold  hide"><?php echo _l('custom_field_visibility'); ?></p>
<div class="checkbox checkbox-primary  hide">
    <input type="checkbox" name="show_on_table" id="show_on_table" <?php if (isset($custom_field) && $custom_field->show_on_table == 1) {
        echo 'checked';
    } ?> <?php if (isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')) {
        echo 'disabled';
    } ?>>
    <label for="show_on_table"><?php echo _l('custom_field_show_on_table'); ?></label>
</div>
<div class="checkbox checkbox-primary show-on-pdf hide">
<input type="checkbox" name="show_on_pdf" id="show_on_pdf" <?php if (isset($custom_field) && $custom_field->show_on_pdf == 1) {
    echo 'checked';
} ?> <?php if (isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')) {
    echo 'disabled';
} ?>>
<label for="show_on_pdf"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
    data-title="<?php echo _l('custom_field_pdf_html_help'); ?>"></i>
    <?php echo _l('custom_field_show_on_pdf'); ?></label>
</div>
<div class="checkbox checkbox-primary show-on-client-portal hide">
<input type="checkbox" name="show_on_client_portal" id="show_on_client_portal" <?php if (isset($custom_field) && $custom_field->show_on_client_portal == 1) {
    echo 'checked';
} ?> <?php if (isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1')) {
    echo 'disabled';
} ?>>
<label for="show_on_client_portal"><i class="fa-regular fa-circle-question"
    data-toggle="tooltip"
    data-title="<?php echo _l('custom_field_show_on_client_portal_help'); ?>"></i>
    <?php echo _l('custom_field_show_on_client_portal'); ?></label>
</div>

<div class="show-on-ticket-form checkbox checkbox-primary<?php if (!isset($custom_field) || isset($custom_field) && $custom_field->fieldto != 'tickets') {
    echo ' hide';
} ?>">
<input type="checkbox" value="1" name="show_on_ticket_form" id="show_on_ticket_form" <?php if (isset($custom_field) && $custom_field->show_on_ticket_form == 1) {
    echo 'checked';
} ?>>
<label for="show_on_ticket_form"><?php echo _l('show_on_ticket_form'); ?></label>
</div>


</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-info custom_field_submit_button"><?php echo _l('submit'); ?></button>
</div>

</div>

<?php echo form_close(); ?>
</div>
</div>

<?php require 'modules/workshop/assets/js/settings/custom_fields/custom_field_modal_js.php';  ?>
