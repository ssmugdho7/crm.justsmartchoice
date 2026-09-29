<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('proposal') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php echo extra_setting_checkbox_option( 'es_enable_multi_currency_for_proposal' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_target_currency_for_proposal','es_enable_target_currency_for_record' )?>

            <small class="text-info"><?php echo _l('es_enable_target_currency_information')?></small>

            <?php echo extra_setting_table_field_options( 'es_enable_target_currency_for_proposal' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_target_assigned_for_proposal','es_enable_target_assigned_for_record' )?>

            <?php echo extra_setting_table_field_options( 'es_enable_target_assigned_for_proposal' )?>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_proposal' )?>

            <hr />


        </div>

    </div>

</div>
