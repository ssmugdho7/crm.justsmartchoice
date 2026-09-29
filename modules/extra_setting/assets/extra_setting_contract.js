
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/contracts/contrac";


        var extra_setting_sign_field = '{customer_sign_image}';

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.avilable_merge_fields').length > 0 )
        {

            if ( extra_setting_sign_option == 1  )
            {

                var extra_setting_merge_field = '<li class="list-group-item"><b>' +extra_setting_sign_name+ '</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false">' +extra_setting_sign_field+ '</a></li>';

                $('.avilable_merge_fields').find('ul.list-group').append(extra_setting_merge_field);

            }

            var extra_setting_new_line = '<li class="list-group-item"><b>' +extra_setting_new_line_name+ '</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false"> {contract_new_line} </a></li>';

            $('.avilable_merge_fields').find('ul.list-group').append(extra_setting_new_line);


            requestGet( admin_url+"extra_setting/setting/custom_fields/contracts" ).done(function ( response ){

                if ( response )
                    $('.avilable_merge_fields').find('ul.list-group').append(response);

            });


        }



        var extra_setting_search = "dmin/contracts";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-contracts').length == 1 && es_table_default_order_for_contract == 1 )
        {

            var es_table_default_order_for_contract_uploaded = 0;

            $('.table-contracts').on('draw.dt', function() {

                if ( es_table_default_order_for_contract_uploaded == 0 )
                {

                    var columnCount = $('.table-contracts').DataTable().columns().header().length;

                    if( es_table_default_order_for_contract_row < columnCount )
                    {

                        es_table_default_order_for_contract_uploaded = 1;

                        setTimeout( function (){

                            $('.table-contracts').DataTable().order([ es_table_default_order_for_contract_row , es_table_default_order_for_contract_order ]).draw();

                        } , 250 );

                    }

                }


            });

        }

        /**
         * Multi currency
         */
        if ( window.location.href.indexOf("admin/contracts/contract") !== -1 && $('#contract-form').length == 1 && es_enable_multi_currency_for_contract == 1 )
        {

            var es_contract_id = $('#contract-form').attr('action').split('/').pop();

            if ( es_contract_id == 'contract' )
                es_contract_id = 0;

            requestGetJSON( admin_url+"extra_setting/setting/contract_currency/"+es_contract_id ).done(function ( response ){

                $('input[name="contract_value"]').parents('.form-group').html(response.es_currency_content).promise().done(function (){

                    init_selectpicker();

                });

            });

        }


    });


})(jQuery);
