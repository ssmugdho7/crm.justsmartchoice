<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('invoice') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php echo extra_setting_checkbox_option( 'es_enable_multi_currency_for_invoice' )?>

            <small class="text-danger"><?php echo _l('es_enable_multi_currency_for_invoice_information')?></small>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_target_currency_for_invoice','es_enable_target_currency_for_record' )?>

            <small class="text-info"><?php echo _l('es_enable_target_currency_information')?></small>

            <?php echo extra_setting_table_field_options( 'es_enable_target_currency_for_invoice' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_days_overdue_invoice' )?>

            <?php echo extra_setting_table_field_options( 'es_show_days_overdue_invoice' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_remaining_balance_for_invoice' )?>

            <?php echo extra_setting_table_field_options( 'es_show_remaining_balance_for_invoice' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_target_sale_agent_for_invoice','es_enable_target_sale_agent_for_record' )?>

            <?php echo extra_setting_table_field_options( 'es_enable_target_sale_agent_for_invoice' )?>

            <hr />

            <!-- Status change date -->
            <?php echo extra_setting_checkbox_option( 'es_show_status_change_date_for_invoice','es_show_status_change_date_on_list' )?>

            <?php echo extra_setting_table_field_options( 'es_show_status_change_date_for_invoice' )?>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_invoice' )?>

            <hr />

        </div>

    </div>

</div>
