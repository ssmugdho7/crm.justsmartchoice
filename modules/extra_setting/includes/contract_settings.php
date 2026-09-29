<?php

hooks()->add_filter('contract_merge_fields', 'extra_setting_contract_merge_fields');

function extra_setting_contract_merge_fields( $fields = null  )
{

    $setting_option = extra_setting_get_option_value( 'es_add_contract_sign_to_merge_fields' );

    if ( empty( $fields['{contract_id}'] ) )
        return $fields;


    $contract_id = $fields['{contract_id}'];

    $contract = get_instance()->db->select('id,signature,contract_value,es_currency')->from(db_prefix().'contracts')->where('id',$contract_id)->get()->row();

    $sign_image = "";

    if ( !empty( $setting_option ) )
    {

        if( !empty( $contract->signature ) )
        {

            $sign_path = protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $contract->signature);

            $sign_height = extra_setting_get_option_value( 'es_contract_sign_height' );

            if ( empty( $sign_height ) )
                $sign_height = 50;

            $sign_image = '<img height="'.$sign_height.'px" style="height:'.$sign_height.'px" src="'.site_url( 'download/preview_image?path='.$sign_path).'" class="img-responsive" >';

        }

    }

    $fields['{customer_sign_image}'] = $sign_image;


    if ( !empty( $contract->es_currency ) )
        $fields['{contract_contract_value}'] = app_format_money($contract->contract_value, $contract->es_currency);


    $fields['{contract_new_line}'] = '<p pagebreak="true"></p>';


    return $fields;

}


hooks()->add_filter('contracts_table_row_data',

function ( $row , $aRow )
{

    $setting_option = extra_setting_get_option_value( 'es_enable_multi_currency_for_contract' ) ;
    $setting_index  = extra_setting_get_option_value( 'es_enable_multi_currency_row' , 4 );

    if ( !empty( $setting_option ) && !empty( $aRow['id'] ) )
    {

        $contract_id = $aRow['id'];

        $contract_data = get_instance()->db->select('contract_value,es_currency')->from(db_prefix().'contracts')->where('id',$contract_id)->get()->row();

        if ( !empty( $contract_data->es_currency ) && !empty( $row[$setting_index] ) )
        {

            $str = app_format_money( $contract_data->contract_value , $contract_data->es_currency );

            $row[$setting_index] = $str;
        }


    }

    return $row;

} , 10,2 );


