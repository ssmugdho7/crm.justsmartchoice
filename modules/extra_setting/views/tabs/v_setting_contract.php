<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('contract') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php echo extra_setting_checkbox_option( 'es_add_contract_sign_to_merge_fields' )?>

            <div class="col-md-4 es_setting_row">
                <?php echo extra_setting_input_option( 'es_contract_sign_height' , 'number' , 50 )?>
            </div>

            <div class="clearfix"></div>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_custom_fields_on_merge_fields_contracts' , 'es_show_custom_fields_on_merge_fields' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_multi_currency_for_contract' )?>

            <div class="col-md-4 es_setting_row">
                <?php echo extra_setting_input_option( 'es_enable_multi_currency_row' , 'number' , 4 )?>
            </div>

            <div class="clearfix"></div>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_contract' )?>

            <hr />

        </div>

    </div>

</div>
