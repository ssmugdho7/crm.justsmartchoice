<?php

hooks()->add_filter('projects_table_columns','extra_setting_projects_table_columns');

function extra_setting_projects_table_columns( $table_data )
{

    /**
     * Status change
     */
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_project' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_project_row' );

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


hooks()->add_filter('projects_table_sql_columns','extra_setting_projects_table_sql_columns');

function extra_setting_projects_table_sql_columns( $aColumns )
{
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_project' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_project_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'projects.es_status_change_date as es_status_change_date';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    return $aColumns;
}


hooks()->add_filter('projects_table_row_data','extra_setting_projects_table_row_data',10,2);

function extra_setting_projects_table_row_data( $row , $aRow )
{
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_project' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_project_row' );

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


hooks()->add_action("project_status_changed", function( $data ) {

    if ( empty( $data['project_id'] ) )
        return true;

    $CI = &get_instance();

    $project_id = $data['project_id'];

    $CI->db->set('es_status_change_date', date('Y-m-d H:i:s') )->where('id',$project_id)->update(db_prefix().'projects');

});

