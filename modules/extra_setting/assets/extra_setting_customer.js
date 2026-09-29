
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/clients";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-clients').length == 1 && es_table_default_order_for_customer == 1 )
        {

            var es_table_default_order_for_customer_uploaded = 0;

            $('.table-clients').on('draw.dt', function() {

                if ( es_table_default_order_for_customer_uploaded == 0 )
                {

                    var columnCount = $('.table-clients').DataTable().columns().header().length;

                    if( es_table_default_order_for_customer_row < columnCount )
                    {

                        es_table_default_order_for_customer_uploaded = 1;

                        setTimeout( function (){

                            $('.table-clients').DataTable().order([ es_table_default_order_for_customer_row , es_table_default_order_for_customer_order ]).draw();

                        } , 250 );

                    }

                }


            });

        }


    });


})(jQuery);
