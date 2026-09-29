<script>


    function save_company_report_settings()
    {

        if ( $('.company_report_filter_keys:checked').length > 0 )
        {

            var report_types = '1';

            $('.company_report_filter_keys:checked').each(function (){

                report_types += "&report_type[]="+$(this).val();

            })

            requestGet( admin_url+'company_report/report/report_save_changes?'+report_types ).done(function (){

                alert_float( 'success' , "<?php echo _l('company_report_successful')?>" );

                setTimeout(function (){

                    $('#btn_get_report_lists').click();

                },1000);

            });


        }
        else
            alert_float( 'danger' , "<?php echo _l('company_report_select_more')?>");

    }


    function company_report_column_settings_toggle()
    {

        if( $('#column_settings').hasClass('hide') )
            $('#column_settings').removeClass('hide');
        else
            $('#column_settings').addClass('hide');

    }

</script>
