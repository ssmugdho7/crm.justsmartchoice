<?php



hooks()->add_filter('customers_table_columns', function( $table_data )
{

    $setting_option = extra_setting_get_option_value( 'es_show_customer_admins_in_list' );
    $setting_index  = extra_setting_get_option_value( 'es_show_customer_admins_in_list_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('customer_admins');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }


    /**
     * Status change
     */
    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_customer' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_customer_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_status_change_date');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    /**
     * Status change
     */
    $setting_option = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists' );
    $setting_index  = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists_row' );

    if ( !empty( $setting_option ) )
    {

        $table_column = _l('es_last_invoice_date');

        if ( $setting_index == -1 || $setting_index == '' )
            $table_data[] = $table_column;
        else
            array_splice($table_data, $setting_index, 0, $table_column );


    }

    return $table_data;

} );


hooks()->add_filter('customers_table_sql_columns', function ( $aColumns )
{

    $setting_option = extra_setting_get_option_value( 'es_show_customer_admins_in_list' );
    $setting_index  = extra_setting_get_option_value( 'es_show_customer_admins_in_list_row' );

    if ( !empty( $setting_option ) )
    {

        $sql = db_prefix().'clients.userid as customer_admins';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_customer' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_customer_row' );

    if ( !empty( $setting_option ) )
    {

        $sql =  db_prefix() . 'clients.es_status_change_date as es_status_change_date';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }

    $setting_option = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists' );
    $setting_index  = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists_row' );

    if ( !empty( $setting_option ) )
    {

        $table_client   = db_prefix().'clients';
        $table_invoice  = db_prefix().'invoices';

        $sql = '( SELECT MAX(date) FROM '.$table_invoice.' inv WHERE inv.clientid = '.$table_client.'.userid AND status != 5 ) as es_last_invoice_date';

        if ( $setting_index == -1 || $setting_index == '' )
            $aColumns[] = $sql;
        else
            array_splice($aColumns, $setting_index, 0, $sql );

    }


    return $aColumns;

} );

hooks()->add_filter('customers_table_row_data', function ( $row , $aRow )
{

    $setting_option = extra_setting_get_option_value( 'es_show_customer_admins_in_list' );
    $setting_index  = extra_setting_get_option_value( 'es_show_customer_admins_in_list_row' );

    if ( !empty( $setting_option ) )
    {

        $userid = $aRow['customer_admins'] ;


        $project_members = get_instance()->db->select('staffid, firstname, lastname')
                                            ->from(db_prefix() . 'customer_admins m')
                                            ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id')
                                            ->where('m.customer_id', $userid )
                                            ->get()
                                            ->result();


        $member_ids = '';
        $member_text = '';

        foreach ($project_members as $project_member) {

            if ($member_ids != '')
                $member_ids .= ',';

            $member_ids .= $project_member->staffid;


            if ($member_text != '')
                $member_text .= ',';

            $member_text .= $project_member->firstname . ' ' . $project_member->lastname;

        }

        $value = format_members_by_ids_and_names($member_ids, $member_text, 'sm');


        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }


    $setting_option = extra_setting_get_option_value( 'es_show_status_change_date_for_customer' );
    $setting_index  = extra_setting_get_option_value( 'es_show_status_change_date_for_customer_row' );

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

    $setting_option = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists' );
    $setting_index  = extra_setting_get_option_value( 'es_show_last_invoice_date_customer_lists_row' );

    if ( !empty( $setting_option ) )
    {

        if( !empty( $aRow["es_last_invoice_date"] ) )
            $value = _d( $aRow['es_last_invoice_date'] ) ;
        else
            $value = '';

        if ( $setting_index == -1 || $setting_index == '' )
            $row[] = $value;
        else
            array_splice($row, $setting_index, 0, $value );

    }


    return $row;

},10,2);


hooks()->add_action("client_status_changed", function( $data ) {

    if ( empty( $data['id'] ) )
        return true;

    $CI = &get_instance();

    $client_id = $data['id'];

    $CI->db->set('es_status_change_date', date('Y-m-d H:i:s') )->where('userid',$client_id)->update(db_prefix().'clients');

});
