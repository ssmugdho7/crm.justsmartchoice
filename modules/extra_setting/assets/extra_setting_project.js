
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/projects";


        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-projects').length == 1 && es_table_default_order_for_project == 1 )
        {

            var es_table_default_order_for_project_uploaded = 0;

            $('.table-projects').on('draw.dt', function() {

                if ( es_table_default_order_for_project_uploaded == 0 )
                {

                    var columnCount = $('.table-projects').DataTable().columns().header().length;

                    if( es_table_default_order_for_project_row < columnCount )
                    {

                        es_table_default_order_for_project_uploaded = 1;

                        setTimeout( function (){

                            $('.table-projects').DataTable().order([ es_table_default_order_for_project_row , es_table_default_order_for_project_order ]).draw();

                        } , 250 );

                    }

                }



            });

        }


    });


})(jQuery);
