<?php


hooks()->add_filter('estimate_currency_attributes', 'extra_setting_estimate_currency_attributes');

function extra_setting_estimate_currency_attributes( $currency_attr = null )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_multi_currency_for_estimate' );

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

    }


    return $currency_attr;

}


hooks()->add_filter('estimates_table_columns',function ( $table_data )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_target_currency_amount');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('sale_agent_string');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    return $table_data;

});


hooks()->add_filter('estimates_table_sql_columns', function( $aColumns )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = 'es_target_currency_amount' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

        $aColumns[] = 'es_target_currency';

    }


    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'estimates.sale_agent as sale_agent';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;

});



hooks()->add_filter('estimates_table_row_data',function( $row , $aRow )
{


    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate_row' );

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

    $setting_option = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_sale_agent_for_estimate_row' );

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

    return $row;

},10,2);





//

hooks()->add_action('after_estimate_view_as_client_link','extra_setting_estimate_target_currency');

function  extra_setting_estimate_target_currency( $estimate )
{
    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );

    if ( !empty( $setting_option ) )
    {

        if ( !empty( $estimate->es_target_currency ) )
        {

            $es_target_currency         = $estimate->es_target_currency;
            $es_target_currency_amount  = $estimate->es_target_currency_amount;

            $es_target_currency_preview = 1;


            require_once __DIR__ . '/target_currency.php';

        }

    }
}


hooks()->add_action('estimate_html_viewed', function ( $estimate_id ){

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );

    if ( !empty( $setting_option ) )
    {

        $estimate = get_instance()->db->select('es_target_currency,es_target_currency_amount')->from(db_prefix().'estimates')->where('id',$estimate_id)->get()->row() ;

        if ( !empty( $estimate->es_target_currency ) )
        {

            $es_target_currency         = $estimate->es_target_currency;
            $es_target_currency_amount  = $estimate->es_target_currency_amount;

            $es_target_currency_preview = 1;
            $es_target_currency_preview_inc = 1;


            require_once __DIR__ . '/target_currency.php';

        }

    }

});

hooks()->add_action('estimate_converted_to_invoice', function ( $data ){

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' );
    $setting_option2 = extra_setting_get_option_value( 'es_enable_target_currency_for_invoice' );

    if ( !empty( $setting_option ) && !empty( $setting_option2 ) && !empty( $data['invoice_id'] ) && !empty( $data['estimate_id'] ) )
    {


        $estimate = get_instance()->db->select('es_target_currency,es_target_currency_amount, es_target_exchange_rate')->from(db_prefix().'estimates')->where('id',$data['estimate_id'])->get()->row() ;

        if ( !empty( $estimate->es_target_currency ) )
        {

            get_instance()->db->set('es_target_currency',$estimate->es_target_currency)
                            ->set('es_target_exchange_rate',$estimate->es_target_exchange_rate)
                            ->set('es_target_currency_amount',$estimate->es_target_currency_amount)
                            ->where('id',$data['invoice_id'])
                            ->update(db_prefix().'invoices');

        }

    }

});




