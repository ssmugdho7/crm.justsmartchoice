
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/invoices";


        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-invoices').length == 1 && es_table_default_order_for_invoice == 1 )
        {

            var es_table_default_order_for_invoice_uploaded = 0;

            $('.table-invoices').on('draw.dt', function() {

                if ( es_table_default_order_for_invoice_uploaded == 0 )
                {

                    var columnCount = $('.table-invoices').DataTable().columns().header().length;

                    if( es_table_default_order_for_invoice_row < columnCount )
                    {

                        es_table_default_order_for_invoice_uploaded = 1;

                        setTimeout( function (){

                            $('.table-invoices').DataTable().order([ es_table_default_order_for_invoice_row , es_table_default_order_for_invoice_order ]).draw();

                        } , 250 );

                    }

                }



            });

        }


    });


})(jQuery);
