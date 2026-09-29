
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/estimates";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-estimates').length == 1 && es_table_default_order_for_estimate == 1 )
        {

            var es_table_default_order_for_estimate_uploaded = 0;

            $('.table-estimates').on('draw.dt', function() {

                if ( es_table_default_order_for_estimate_uploaded == 0 )
                {

                    var columnCount = $('.table-estimates').DataTable().columns().header().length;

                    if( es_table_default_order_for_estimate_row < columnCount )
                    {

                        es_table_default_order_for_estimate_uploaded = 1;

                        setTimeout( function (){

                            $('.table-estimates').DataTable().order([ es_table_default_order_for_estimate_row , es_table_default_order_for_estimate_order ]).draw();

                        } , 250 );

                    }

                }


            });

        }


        var extra_setting_search = "dmin/estimates/estimat";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('#estimate-form').length == 1  && es_enable_target_currency_for_estimate == 1 )
        {

            var es_estimate_id = 0 ;
            if ( $('input[name="isedit"]').length == 1 )
                es_estimate_id = $('#estimate-form').attr('action').split('/').pop();


            requestGetJSON( admin_url+"extra_setting/setting/second_currency/estimates/"+es_estimate_id ).done(function ( response ){

                $('.es_target_currency_content_remove').remove();
                $('.es_target_currency_content_remove').remove();


                if ( $('#discount_area').parents('tbody').length > 0 )
                    $('#discount_area').parents('tbody').eq(0).append(response.es_target_currency_amount);


                if ( $('#expirydate').parents('div.panel-body').length > 0 )
                {

                    $('#expirydate').parents('div.panel-body').eq(0).append(response.es_target_currency_content).promise().done(function (){

                        es_calculate_target_currency_properties();

                    });

                }


            });


        }



    });


    /**
     * Proposal convert to estimate screan
     */

    if ( es_enable_target_currency_for_estimate == 1 )
    {


        $(document).on('shown.bs.modal', '.proposal-convert-modal', function () {

            if( $(this).attr('id') == 'convert_to_estimate' )
            {

                var proposal_id = $('#proposal_convert_to_estimate_form').attr('action').split('/').pop();


                requestGetJSON( admin_url+"extra_setting/setting/second_currency/proposals/"+proposal_id ).done(function ( response ){

                    $('.es_target_currency_content_remove').remove();
                    $('.es_target_currency_content_remove').remove();



                    if ( $('#discount_area').parents('tbody').length > 0 )
                        $('#discount_area').parents('tbody').eq(0).append(response.es_target_currency_amount);


                    if ( $('#expirydate').parents('div.panel-body').length > 0 )
                    {

                        $('#expirydate').parents('div.panel-body').eq(0).append(response.es_target_currency_content).promise().done(function (){

                            es_calculate_target_currency_properties();

                        });

                    }


                });

            }

        });


    }

})(jQuery);
