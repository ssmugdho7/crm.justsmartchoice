
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/expenses";

        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-expenses').length == 1 && es_table_default_order_for_expenses == 1 )
        {

            var es_table_default_order_for_expenses_uploaded = 0;

            $('.table-expenses').on('draw.dt', function() {

                if ( es_table_default_order_for_expenses_uploaded == 0 )
                {

                    var columnCount = $('.table-expenses').DataTable().columns().header().length;

                    if( es_table_default_order_for_expenses_row < columnCount )
                    {

                        es_table_default_order_for_expenses_uploaded = 1;

                        setTimeout( function (){

                            $('.table-expenses').DataTable().order([ es_table_default_order_for_expenses_row , es_table_default_order_for_expenses_order ]).draw();

                        } , 250 );

                    }

                }


            });

        }


    });


})(jQuery);
