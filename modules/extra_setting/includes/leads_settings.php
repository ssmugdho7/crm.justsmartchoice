<?php


hooks()->add_filter('leads_table_columns','extra_setting_leads_table_columns');

function extra_setting_leads_table_columns( $table_data )
{

    /**
     * Status change
     */
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_leads' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_leads_row' );

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


hooks()->add_filter('leads_table_sql_columns','extra_setting_leads_table_sql_columns');

function extra_setting_leads_table_sql_columns( $aColumns )
{

    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_leads' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_leads_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'leads.es_status_change_date as es_status_change_date';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;
}


hooks()->add_filter('leads_table_row_data','extra_setting_leads_table_row_data',10,2);

function extra_setting_leads_table_row_data( $row , $aRow )
{

    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_leads' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_leads_row' );

    if ( !empty( $setting_option ) )
    {

        if( !empty( $aRow["es_status_change_date"] ) )
            $value = _d( $aRow['es_status_change_date'] ) ;
        else
            $value = '';


        $DT_RowId = '';
        $DT_RowClass = '';


        if (isset($row['DT_RowId']))
        {
            $DT_RowId = $row['DT_RowId'];
            unset($row['DT_RowId']);
        }

        if (isset($row['DT_RowClass']))
        {
            $DT_RowClass = $row['DT_RowClass'];
            unset($row['DT_RowClass']);
        }


        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );




        if ( $DT_RowId != '' )
            $row['DT_RowId'] = $DT_RowId;

        if ( $DT_RowClass != '' )
            $row['DT_RowClass'] = $DT_RowClass;


    }


    return $row;
}


hooks()->add_action("lead_status_changed", function( $data ) {

    if ( empty( $data['lead_id'] ) )
        return true;

    $CI = &get_instance();

    $lead_id = $data['lead_id'];

    $CI->db->set('es_status_change_date', date('Y-m-d H:i:s') )->where('id',$lead_id)->update(db_prefix().'leads');

});
