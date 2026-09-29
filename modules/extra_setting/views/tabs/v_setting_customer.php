<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('client') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="form-group">

            <?php echo extra_setting_checkbox_option( 'es_show_customer_admins_in_list' )?>

            <?php echo extra_setting_table_field_options( 'es_show_customer_admins_in_list' )?>

            <hr />

            <!-- Status change date -->
            <?php echo extra_setting_checkbox_option( 'es_show_status_change_date_for_customer','es_show_status_change_date_on_list' )?>

            <?php echo extra_setting_table_field_options( 'es_show_status_change_date_for_customer' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_show_last_invoice_date_customer_lists' )?>

            <?php echo extra_setting_table_field_options( 'es_show_last_invoice_date_customer_lists' )?>

            <hr />

            <?php echo extra_setting_checkbox_option( 'es_enable_contact_login_using_phone' )?>

            <?php

                $opt_val = extra_setting_get_option_value( 'es_enable_contact_login_using_otp_type' , 'phone' );

                $content = '<div class="col-md-6">';

                    $content .= '<div class="select-placeholder form-group">';

                        $content .= '<select class="selectpicker form-control extra_setting_select_required"  name="es_enable_contact_login_using_otp_type" id="es_enable_contact_login_using_otp_type">';

                            $content .= '<option '.( $opt_val == 'email' ? 'selected' : '' ).' value="email">'._l('client_email').'</option>';
                            $content .= '<option '.( $opt_val == 'phone' ? 'selected' : '' ).' value="phone">'._l('clients_list_phone').'</option>';

                        $content .= '</select>';

                    $content .= '</div>';

                $content .= '</div>';

                echo $content;

            ?>

            <div class="clearfix"></div>

            <hr />

            <?php echo extra_setting_table_default_order_options( 'es_table_default_order_for_customer' )?>

            <hr />

        </div>

    </div>

</div>
