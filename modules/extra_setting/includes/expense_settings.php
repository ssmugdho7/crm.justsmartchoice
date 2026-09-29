<?php


hooks()->add_filter('expense_currency_attributes', 'extra_setting_expenses_currency_attributes');

function extra_setting_expenses_currency_attributes( $currency_attr = null )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_multi_currency_for_expenses' );

    if ( !empty( $setting_option ) )
    {

        if ( isset( $currency_attr['disabled'] ) )
            unset($currency_attr['disabled']);

    }


    return $currency_attr;

}



hooks()->add_filter('expenses_table_columns','extra_setting_expenses_table_columns');

function extra_setting_expenses_table_columns( $table_data )
{

    $select_tax = false;

    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_tax_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('expenses_report_total_tax');

        //$table_column = [ 'name'     => _l('expenses_report_total_tax'), 'th_attrs' => ['class' => 'not-export' ] ];

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );

        $select_tax = true;

    }

    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_without_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_without_tax_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_amount_without_tax');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


        $select_tax = true;

    }

    $_SESSION['es_tax_information'] = null;
    if ( $select_tax )
    {

        $taxes = get_instance()->db->select('id,taxrate')->from(db_prefix().'taxes')->get()->result();

        $tax_data = [];

        if ( !empty( $taxes ) )
        {

            foreach ( $taxes as $tax )
            {

                if ( $tax->taxrate > 0 )
                {

                    $tax_data[ $tax->id ] = $tax->taxrate;

                }

            }

            if ( !empty( $tax_data ) )
                $_SESSION['es_tax_information'] = json_encode( $tax_data );

        }


    }


    return $table_data;
}


hooks()->add_filter('expenses_table_sql_columns','extra_setting_expenses_table_sql_columns');

function extra_setting_expenses_table_sql_columns( $aColumns )
{

    $es_tax_information = [];

    if ( !empty( $_SESSION['es_tax_information'] ) )
    {

        $es_tax_information = json_decode( $_SESSION['es_tax_information'] );

    }

    $expenses_table = db_prefix().'expenses';


    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_tax_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = '';

        if ( !empty( $es_tax_information ) )
        {

            foreach ( $es_tax_information  as $tax_id => $tax_rate )
            {

                if ( $sql != '' )
                    $sql .= ' + ';

                $sql .= ' ( ( CASE WHEN '.$expenses_table.'.tax = '.$tax_id.' THEN '.$tax_rate.' ELSE 0 END ) * amount / 100 ) + ';
                $sql .= ' ( ( CASE WHEN '.$expenses_table.'.tax2 = '.$tax_id.' THEN '.$tax_rate.' ELSE 0 END ) * amount / 100 ) ';

            }

        }

        if ( empty( $sql ) )
            $sql = ' 0 ';

        $sql =  '( '.$sql.') as expenses_total_tax' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );


    }

    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_without_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_without_tax_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = '( '.$expenses_table.'.amount ) as expenses_total_without_tax' ;

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;
}

hooks()->add_filter('expenses_table_row_data','extra_setting_expenses_table_row_data',10,2);

function extra_setting_expenses_table_row_data( $row , $aRow )
{

    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_tax_row' );

    if ( !empty( $setting_option ) )
    {


        /**
         * Maybe Perfex CRM core codes have been interfered with.
         */
        if( empty( $aRow["currency_name"] ) )
            $aRow["currency_name"] = get_base_currency();

        $value = app_format_money( $aRow['expenses_total_tax'] , $aRow['currency_name'] );


        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }


    $setting_option = extra_setting_get_option_value( 'es_show_expenses_total_without_tax' );
    $setting_index  = extra_setting_get_option_value( 'es_show_expenses_total_without_tax_row' );

    if ( !empty( $setting_option ) )
    {

        /**
         * Maybe Perfex CRM core codes have been interfered with.
         */
        if( empty( $aRow["currency_name"] ) )
            $aRow["currency_name"] = get_base_currency();


        $value = app_format_money( $aRow['expenses_total_without_tax'] , $aRow['currency_name'] );

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }



    return $row;
}
