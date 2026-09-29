<?php


hooks()->add_filter('invoice_currency_attributes', 'extra_setting_invoice_currency_attributes');

function extra_setting_invoice_currency_attributes( $currency_attr = null )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_multi_currency_for_invoice' );

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

    }


    return $currency_attr;

}



hooks()->add_filter('invoices_table_columns','extra_setting_invoices_table_columns');

function extra_setting_invoices_table_columns( $table_data )
{

    $setting_option = extra_setting_get_option_value( 'es_show_days_overdue_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_days_overdue_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_overdue_days');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );

    }

    $setting_option = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_remaining_balance');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_target_currency_amount');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('sale_agent_string');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }


    /**
     * Status change
     */
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_status_change_date');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    return $table_data;
}


hooks()->add_filter('invoices_table_sql_columns','extra_setting_invoices_table_sql_columns');

function extra_setting_invoices_table_sql_columns( $aColumns )
{

    $setting_option = extra_setting_get_option_value( 'es_show_days_overdue_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_days_overdue_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = 'CURRENT_DATE as overdue_days' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );


    }

    $setting_option = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = '( SELECT SUM( IFNULL(amount, 0) ) FROM '.db_prefix().'invoicepaymentrecords WHERE '.db_prefix().'invoicepaymentrecords.invoiceid = '.db_prefix().'invoices.id  ) as total_payment' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );


    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = 'es_target_currency_amount' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

        $aColumns[] = 'es_target_currency';

    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'invoices.sale_agent as sale_agent';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'invoices.es_status_change_date as es_status_change_date';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;
}



hooks()->add_filter('invoices_table_row_data','extra_setting_invoices_table_row_data',10,2);

function extra_setting_invoices_table_row_data( $row , $aRow )
{

    $setting_option = extra_setting_get_option_value( 'es_show_days_overdue_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_days_overdue_invoice_row' );

    if ( !empty( $setting_option ) )
    {
        $str = '';

        if( $aRow[db_prefix() . 'invoices.status' ] == Invoices_model::STATUS_OVERDUE && !empty( $aRow['duedate'] ) )
        {

            $datetime   = strtotime("now");

            $date2      = strtotime($aRow['duedate']);

            $holdtotday = intval(($datetime - $date2) / 86400);

            if (1 < $holdtotday)
                $str = $holdtotday . ' '._l('days');
            elseif( $holdtotday == 1 )
                $str = $holdtotday . ' '._l('day');

        }

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $str;
        else
            array_splice($row, $setting_index, 0, $str );

    }


    $setting_option = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_remaining_balance_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        if( empty( $aRow["total_payment"] ) )
            $aRow["total_payment"] = 0;

        /**
         * Maybe Perfex CRM core codes have been interfered with.
         */
        if( empty( $aRow["currency_name"] ) )
            $aRow["currency_name"] = get_base_currency();

        if( empty( $aRow["total"] ) )
            $aRow["total"] = 0;


        $value = app_format_money( $aRow["total"] - $aRow['total_payment'] , $aRow['currency_name'] );

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        if( !empty( $aRow["es_target_currency_amount"] ) && $aRow["es_target_currency_amount"] > 0)
            $value = app_format_money( $aRow["es_target_currency_amount"]  , $aRow['es_target_currency'] );
        else
            $value = '';

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }

    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

       $userid = $aRow['sale_agent'] ;

        $saleAgentOutput = '';

        if ($userid != 0) {

            $sale_agent_name = get_instance()->db->select("CONCAT(firstname, ' ' , lastname) as sale_agent")
                ->from(db_prefix() . 'staff')
                ->where('staffid', $userid )
                ->get()
                ->row();

            $full_name = $sale_agent_name->sale_agent;



            $saleAgentOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['sale_agent']) . '">' . staff_profile_image($aRow['sale_agent'], [

                    'staff-profile-image-small',

                ]) . '</a>';

            $saleAgentOutput .= '<span class="hide">' . $full_name . '</span>';

        }

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $saleAgentOutput;
        else
            array_splice($row, $setting_index, 0, $saleAgentOutput );

    }


    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_invoice_row' );

    if ( !empty( $setting_option ) )
    {

        if( !empty( $aRow["es_status_change_date"] ) )
            $value = _d( $aRow['es_status_change_date'] ) ;
        else
            $value = '';

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }


    return $row;
}



hooks()->add_action('before_render_invoice_template',function ( $invoice ){

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );

    if ( !empty( $setting_option ) )
    {

        $es_target_exchange_rate = '';
        $es_target_currency = '';
        $es_target_currency_amount = 0;

        if ( !empty( $invoice->es_target_exchange_rate ) )
        {

            $es_target_exchange_rate    = $invoice->es_target_exchange_rate;
            $es_target_currency         = $invoice->es_target_currency;
            $es_target_currency_amount  = $invoice->es_target_currency_amount;

        }

        $es_target_currency_preview = 0;

        echo "<div id='es_target_currency_hidden'></div>";

        require_once __DIR__ . '/target_currency.php';

    }


});

hooks()->add_action('after_right_panel_invoice_preview_template','extra_setting_invoice_target_currency');

hooks()->add_action('after_left_panel_invoicehtml','extra_setting_invoice_target_currency');


function  extra_setting_invoice_target_currency( $invoice )
{
    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );

    if ( !empty( $setting_option ) )
    {

        if ( !empty( $invoice->es_target_currency ) )
        {

            $es_target_currency         = $invoice->es_target_currency;
            $es_target_currency_amount  = $invoice->es_target_currency_amount;

            $es_target_currency_preview = 1;


            require_once __DIR__ . '/target_currency.php';

        }

    }
}



hooks()->add_action("invoice_status_changed", function( $data ) {

    if ( empty( $data['invoice_id'] ) )
        return true;

    $CI = &get_instance();

    $invoice_id = $data['invoice_id'];

    $CI->db->set('es_status_change_date', date('Y-m-d H:i:s') )->where('id',$invoice_id)->update(db_prefix().'invoices');

});
