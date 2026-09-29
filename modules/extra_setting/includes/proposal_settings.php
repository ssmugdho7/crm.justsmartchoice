<?php

hooks()->add_filter('proposal_currency_attributes', 'extra_setting_proposal_currency_attributes');

function extra_setting_proposal_currency_attributes( $currency_attr = null )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_multi_currency_for_proposal' );

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

        $currency_attr['multi-currency'] = 'extrasetting';

    }


    return $currency_attr;

}



hooks()->add_filter('proposals_table_columns',function ( $table_data )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_target_currency_amount');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    $setting_option = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('proposal_assigned');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    return $table_data;

});


hooks()->add_filter('proposals_table_sql_columns', function( $aColumns )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = 'es_target_currency_amount' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

        $aColumns[] = 'es_target_currency';

    }

    $setting_option = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'proposals.assigned as assigned';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;

});



hooks()->add_filter('proposals_table_row_data',function( $row , $aRow )
{


    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal_row' );

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

    $setting_option = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal' );
    $setting_index  = extra_setting_get_option_value( 'es_enable_target_assigned_for_proposal_row' );

    if ( !empty( $setting_option ) )
    {

        $userid = $aRow['assigned'] ;

        $saleAgentOutput = '';

        if ($userid != 0) {

            $sale_agent_name = get_instance()->db->select("CONCAT(firstname, ' ' , lastname) as assigned")
                ->from(db_prefix() . 'staff')
                ->where('staffid', $userid )
                ->get()
                ->row();

            $full_name = $sale_agent_name->assigned;



            $saleAgentOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [

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


hooks()->add_action('proposal_html_viewed', function ( $proposal_id ){

    $setting_option = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal' );

    if ( !empty( $setting_option ) )
    {

        $estimate = get_instance()->db->select('es_target_currency,es_target_currency_amount')->from(db_prefix().'proposals')->where('id',$proposal_id)->get()->row() ;

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
