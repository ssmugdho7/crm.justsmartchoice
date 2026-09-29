<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('expenses') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php echo extra_setting_checkbox_option( 'es_enable_multi_currency_for_expenses' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_expenses_total_tax' )?>

            <?php echo extra_setting_table_field_options( 'es_show_expenses_total_tax' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_expenses_total_without_tax' )?>

            <?php echo extra_setting_table_field_options( 'es_show_expenses_total_without_tax' )?>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_expenses' )?>

            <hr />

        </div>

    </div>

</div>
