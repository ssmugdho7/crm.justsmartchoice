<?php

if ( isset( $es_target_currency_preview ) && $es_target_currency_preview == 1 )
{

    /**
     * Record preview content
     */

    $CI = &get_instance();

    $es_target_currency_name = $es_target_currency;
    $es_target_currency_db = get_instance()->db->select('name')->from(db_prefix().'currencies')->where('id',$es_target_currency)->get()->row();

    if ( !empty( $es_target_currency_db->name ) )
        $es_target_currency_name = $es_target_currency_db->name;

    $es_target_currency_amount = '
                                    <tr>
                
                                        <td>
                
                                            <span class="bold tw-text-neutral-700">'. _l('es_target_total_in',$es_target_currency_name) .' </span>
                                            <input type="hidden" id="es_target_currency_amount" name="es_target_currency_amount" value="'.$es_target_currency_amount.'">
                                            
                                        </td>
                
                                        <td class="es_target_currency_amount">
                                            '.app_format_money($es_target_currency_amount,$es_target_currency).'
                                        </td>
                
                                    </tr>';

    ?>

    <script>
        <?php if ( !empty( $es_target_currency_preview_inc ) ) { ?>

            document.addEventListener("DOMContentLoaded", function() {

                $(document).ready(function (){


                    if ( $('#subtotal').parents('table').length > 0 )
                        $('#subtotal').parents('table').find('tbody').eq(0).append(`<?php echo $es_target_currency_amount?>`);

                })

            })

        <?php } else { ?>

            $(document).ready(function (){


                if ( $('#subtotal').parents('table').length > 0 )
                    $('#subtotal').parents('table').find('tbody').eq(0).append(`<?php echo $es_target_currency_amount?>`);

            })

        <?php } ?>


    </script>

<?php
}
else
{

    /**
     * Record detail content
     */


    $CI = &get_instance();

    if ( !class_exists( 'Currencies_model' , false ) )
        $CI->load->model('currencies_model');

    $currencies = $CI->currencies_model->get();

    $es_target_currency_content = '<div class="row es_target_currency_content_remove">
    
                                        <div class="col-md-6">
                                        
                                            <div class="row">
                                                <div class="col-md-6">
                                                    '. render_select('es_target_currency', $currencies, ['id', 'name', 'symbol'], 'es_target_currency' , $es_target_currency ) .'                   
                                                </div>
                                    
                                                <div class="col-md-6">
                                                    '. render_input('es_target_exchange_rate' , 'es_target_exchange_rate' , $es_target_exchange_rate , 'number' , [ 'step' => '0.000001' ] ) .'
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                
                                    </div>';


    $es_target_currency_amount = '
                                    <tr class="es_target_currency_content_remove">
                
                                        <td>
                
                                            <span class="bold tw-text-neutral-700">'. _l('es_target_currency_amount') .' :</span>
                                            <input type="hidden" id="es_target_currency_amount" name="es_target_currency_amount" value="'.$es_target_currency_amount.'">
                                            
                                        </td>
                
                                        <td class="es_target_currency_amount">
                                            '.app_format_money($es_target_currency_amount,$es_target_currency).'
                                        </td>
                
                                    </tr>';

    ?>

    <script>


        // URL içinde aramak
        if ( window.location.href.includes('admin/proposal'))
        {

            /**
             * Proposal convert to estimate screan
             */
            $(document).ready(function (){

                if( $('#proposal_convert_to_invoice_form').length == 1 )
                {

                    var proposal_id = $('#proposal_convert_to_invoice_form').attr('action').split('/').pop();


                    requestGetJSON( admin_url+"extra_setting/setting/second_currency/proposals/"+proposal_id ).done(function ( response ){

                        $('.es_target_currency_content_remove').remove();
                        $('.es_target_currency_content_remove').remove();

                        if ( $('#discount_area').parents('tbody').length > 0 )
                            $('#discount_area').parents('tbody').eq(0).append(response.es_target_currency_amount);


                        if ( $('#duedate').parents('div.panel-body').length > 0 )
                        {

                            $('#duedate').parents('div.panel-body').eq(0).append(response.es_target_currency_content).promise().done(function (){

                                es_calculate_target_currency_properties();

                            });

                        }


                    });

                }

            });


        }
        else
        {

            document.addEventListener("DOMContentLoaded", function() {


                if ( $('#es_target_currency_hidden').parents('div.panel-body').length > 0 )
                    $('#es_target_currency_hidden').parents('div.panel-body').eq(0).append(`<?php echo $es_target_currency_content?>`);


                if ( $('#discount_area').parents('tbody').length > 0 )
                    $('#discount_area').parents('tbody').eq(0).append(`<?php echo $es_target_currency_amount?>`);


                es_calculate_target_currency_properties();


            });

        }





    </script>

<?php } ?>
