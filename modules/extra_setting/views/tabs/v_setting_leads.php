<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('leads') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php

            $options =
            [
                [ 'value' => 'tags' , 'text' => 'tags' ] ,
                [ 'value' => 'phonenumber' , 'text' => 'lead_add_edit_phonenumber' ] ,
            ];

            echo extra_setting_new_required_fields_options( 'es_required_fields_for_leads' , $options );
            ?>

            <hr />

            <!-- Status change date -->
            <?php echo extra_setting_checkbox_option( 'es_show_status_change_date_for_leads','es_show_status_change_date_on_list' )?>

            <?php echo extra_setting_table_field_options( 'es_show_status_change_date_for_leads' )?>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_leads' )?>

            <hr />


        </div>

    </div>

</div>
