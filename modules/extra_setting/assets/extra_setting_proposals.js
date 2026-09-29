
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/proposals";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-proposals').length == 1 && es_table_default_order_for_proposal == 1 )
        {

            var es_table_default_order_for_proposal_uploaded = 0;

            $('.table-proposals').on('draw.dt', function() {

                if ( es_table_default_order_for_proposal_uploaded == 0 )
                {

                    var columnCount = $('.table-proposals').DataTable().columns().header().length;

                    if( es_table_default_order_for_proposal_row < columnCount )
                    {

                        es_table_default_order_for_proposal_uploaded = 1;

                        setTimeout( function (){

                            $('.table-proposals').DataTable().order([ es_table_default_order_for_proposal_row , es_table_default_order_for_proposal_order ]).draw();

                        } , 250 );

                    }

                }


            });

        }


        var extra_setting_search = "dmin/proposals/proposal";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('#proposal-form').length == 1  && es_enable_target_currency_for_proposal == 1 )
        {

            var es_proposal_id = 0 ;
            if ( $('input[name="isedit"]').length == 1 )
                es_proposal_id = $('#proposal-form').attr('action').split('/').pop();


            requestGetJSON( admin_url+"extra_setting/setting/second_currency/proposals/"+es_proposal_id ).done(function ( response ){

                $('.es_target_currency_content_remove').remove();
                $('.es_target_currency_content_remove').remove();


                if ( $('#discount_area').parents('tbody').length > 0 )
                    $('#discount_area').parents('tbody').eq(0).append(response.es_target_currency_amount);


                if ( $('#currency').parents('div.panel-body').length > 0 )
                {

                    $('#currency').parents('div.panel-body').eq(0).append(response.es_target_currency_content).promise().done(function (){

                        es_calculate_target_currency_properties();

                    });

                }


            });


        }


    });


})(jQuery);
