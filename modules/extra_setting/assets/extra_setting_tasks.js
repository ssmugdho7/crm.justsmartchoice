
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/tasks";


        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-tasks').length == 1 && es_table_default_order_for_tasks == 1 )
        {

            var es_table_default_order_for_tasks_uploaded = 0;

            $('.table-tasks').on('draw.dt', function() {

                if ( es_table_default_order_for_tasks_uploaded == 0 )
                {

                    var columnCount = $('.table-tasks').DataTable().columns().header().length;

                    if( es_table_default_order_for_tasks_row < columnCount )
                    {

                        es_table_default_order_for_tasks_uploaded = 1;

                        setTimeout( function (){

                            $('.table-tasks').DataTable().order([ es_table_default_order_for_tasks_row , es_table_default_order_for_tasks_order ]).draw();

                        } , 250 );

                    }

                }



            });

        }


    });


})(jQuery);
