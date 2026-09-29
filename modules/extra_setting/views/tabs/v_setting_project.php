<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('project') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <!-- Status change date -->
            <?php echo extra_setting_checkbox_option( 'es_show_status_change_date_for_project','es_show_status_change_date_on_list' )?>

            <?php echo extra_setting_table_field_options( 'es_show_status_change_date_for_project' )?>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_project' )?>

            <hr />


        </div>

    </div>

</div>