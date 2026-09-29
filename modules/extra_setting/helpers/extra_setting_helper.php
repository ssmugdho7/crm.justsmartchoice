<?php


function extra_setting_checkbox_option( $opt_name = '' , $lang = '' )
{

    $opt_val = extra_setting_get_option_value( $opt_name );


    $field_lang = $opt_name;
    if ( $lang != '' )
        $field_lang = $lang;


    $opt_html = '<div class="checkbox checkbox-primary">';

        $opt_html .= '<input type="checkbox" '. ( $opt_val == 1 ? 'checked' : '' ).' class="extra_setting_checkbox" value="1" id="'.$opt_name.'" name="'.$opt_name.'">';

        $opt_html .= '<label for="'.$opt_name.'">'.  _l($field_lang).'</label>';

    $opt_html .= '</div>';

    return $opt_html;

}

function extra_setting_input_option( $opt_name = '' , $type = 'input' , $def_val = '' , $lang = '' )
{

    $opt_val = extra_setting_get_option_value( $opt_name );

    if ( empty( $opt_val ) )
        $opt_val = $def_val;


    $field_lang = $opt_name;
    if ( $lang != '' )
        $field_lang = $lang;


    return render_input( $opt_name , $field_lang , $opt_val , $type , [] , [] , '' , 'extra_setting_input');

}


function extra_setting_table_field_options( $option_field )
{

    $content = '<div class="row es_setting_row">';

        $content .= '<div class="col-md-6">';

            $content .= extra_setting_input_option( $option_field.'_row' , 'number' , -1 , 'es_table_added_index_row');

        $content .= '</div>';


        /* For export option.
        $content .= '<div class="col-md-6">';

            $content .= extra_setting_checkbox_option( $option_field.'_export' , 'es_table_added_index_export' );

        $content .= '</div>';
        */

    $content .= '</div>';

    return $content;

}


function extra_setting_table_default_order_options( $option_field )
{

    $content = extra_setting_checkbox_option( $option_field , 'es_table_default_order_for' );

    $content .= '<div class="row es_setting_row">';

        $content .= '<div class="col-md-6">';

            $content .= extra_setting_input_option( $option_field.'_row' , 'number' , 0 , 'es_table_default_order_for_row');

        $content .= '</div>';


        $opt_val = extra_setting_get_option_value( $option_field.'_order' );

        $content .= '<div class="col-md-6">';

            $content .= '<div class="select-placeholder form-group">';

                $content .= '<label class="control-label">'._l('es_table_default_order_for_order').'</label>';

                $content .= '<select class="selectpicker form-control extra_setting_select" id="'.$option_field.'_order">';

                    $content .= '<option '.( $opt_val != 'desc' ? 'selected' : '' ).' value="asc">'._l('dt_sort_ascending').'</option>';
                    $content .= '<option '.( $opt_val == 'desc' ? 'selected' : '' ).' value="desc">'._l('dt_sort_descending').'</option>';

                $content .= '</select>';

            $content .= '</div>';

        $content .= '</div>';


    $content .= '</div>';

    return $content;

}


function extra_setting_get_option_value( $opt_name , $def_val = '' )
{

    $info = get_instance()->db->select('value')->from(db_prefix().'options')->where('name',$opt_name)->get()->row();

    if ( !empty( $info->value ) )
        return $info->value;

    return $def_val;

}


function extra_setting_update_option_value( $opt_name , $value )
{

    $CI = &get_instance();

    $info = $CI->db->select('id')->from(db_prefix().'options')->where('name',$opt_name)->get()->row();

    if ( empty( $info->id ) )
    {
        // New Record

        $CI->db->insert( db_prefix().'options' , [
            'name' => $opt_name ,
            'value' => $value ,
            'autoload' => 0
        ] );

    }
    else
    {

        // Upload

        $CI->db->where('id',$info->id)->update( db_prefix().'options' , [
            'value' => $value ,
            'autoload' => 0
        ] );

    }


}


/**
 * combobox option
 */

function extra_setting_new_required_fields_options( $option_field , $options = [] )
{

    $opt_val = extra_setting_get_option_value( $option_field );

    if ( !empty( $opt_val ) )
        $opt_val = json_decode( $opt_val , true);

    $content = '<div class="row">';

        $content .= '<div class="col-md-12">';

            $content .= '<div class="select-placeholder form-group">';

                $content .= '<label class="control-label">'._l('es_form_new_required_fields').'</label>';

                $content .= '<select class="selectpicker form-control extra_setting_select_required" multiple="true" name="'.$option_field.'[]" id="'.$option_field.'">';

                    if ( empty( $options ) )
                    {
                        $content .= '<option value=""></option>';
                    }
                    else
                    {

                        foreach ( $options as $option )
                        {

                            $selected = '';

                            if ( is_array( $opt_val ) && in_array( $option['value'] , $opt_val ) )
                                $selected = 'selected';

                            $content .= '<option '.$selected.' value="'.$option['value'].'">'._l($option['text']).'</option>';

                        }

                    }


                $content .= '</select>';

            $content .= '</div>';

        $content .= '</div>';

    $content .= '</div>';


    return $content;

}


function extra_setting_number_cast( $text = '' )
{

    return preg_replace('/[^0-9]/', '', $text);

}
