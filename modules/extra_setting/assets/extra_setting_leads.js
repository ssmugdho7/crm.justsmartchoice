
(function($) {
    "use strict";

    $(document).ready(function (){


        var extra_setting_search = "dmin/leads";


        if ( window.location.href.indexOf(extra_setting_search) !== -1 && $('.table-leads').length == 1 && es_table_default_order_for_leads == 1 )
        {

            var es_table_default_order_for_leads_uploaded = 0;

            $('.table-leads').on('draw.dt', function() {

                if ( es_table_default_order_for_leads_uploaded == 0 )
                {

                    var columnCount = $('.table-leads').DataTable().columns().header().length;

                    if( es_table_default_order_for_leads_row < columnCount )
                    {

                        es_table_default_order_for_leads_uploaded = 1;

                        setTimeout( function (){

                            $('.table-leads').DataTable().order([ es_table_default_order_for_leads_row , es_table_default_order_for_leads_order ]).draw();

                        } , 250 );

                    }

                }



            });

        }



        $('#lead-modal').on('shown.bs.modal', function (e) {

            if( es_form_required_lead != '' )
            {

                $(es_form_required_lead).each(function( ind , element ) {

                    if( $('#'+element).length > 0 )
                    {

                        $('#'+element).attr('required','true');

                    }

                })

            }

        });



    });


})(jQuery);
