<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();?>

<?php $has_tab = false; ?>

<div id="wrapper" >

    <div class="content">

        <div class="row" id="email_template_manage_tabs">

            <div class="col-md-3">

                <div class="panel_s mbot5">
                    <div class="">
                        <a onclick="email_template_manage_compose_email()" class="btn btn-info display-block">
                            <i class="fa fa-envelope"></i>
                            <?php echo _l('email_template_manage_send_mail'); ?>
                        </a>

                    </div>
                </div>

                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">

                    <?php foreach ( $tabs as $tab ) {

                        if ( $tab['slug'] == $active_tab )
                            $has_tab = true;

                        ?>

                        <li class="<?php echo $tab['slug'] == $active_tab ? 'active' : ''?>">

                            <a href="<?php echo admin_url('email_template_manage/inbox?tab='.$tab['slug'] ); ?>"

                               data-group="">

                                <i class="<?php echo $tab['icon']?> menu-icon"></i>

                                <?php echo $tab['text']?>

                            </a>

                        </li>

                    <?php } ?>


                </ul>


            </div>

            <div class="col-md-9" id="es_inbox_content">

                <?php


                if ( $has_tab )
                {
                    $this->load->view('mailbox/tabs/v_inbox_'.$active_tab);
                }
                else
                {

                    echo "Not found setting";

                }

                ?>

            </div>

        </div>


        <div class="row"  id="email_template_manage_content">

        </div>


    </div>

</div>

<div class="modal fade" id="email_template_manage_mail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content" id="email_template_manage_mail_content">


        </div>

    </div>

</div>

<div class="modal fade" id="template_definition" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog modal-xl" role="document">

        <div class="modal-content" id="template_definition_content"  >


        </div>

    </div>

</div>

<?php init_tail(); ?>

<script>

    function email_template_manage_compose_email()
    {

        tinymce.remove();
        requestGet('email_template_manage/send_mail_modal/0/compose' ).done(function(response) {

            $('#email_template_manage_modal').html(response);

            $('#email_template_manage_modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });

            tinymce.init({
                selector: "textarea"
            });

        }).fail(function(data) {

            alert_float('danger', data.responseText);

        });


    }


    $(document).ready(function (){

        if ( $(".table-emails").length > 0  ){

            initDataTable('.table-emails', admin_url + 'email_template_manage/inbox_lists?tab=<?php echo $active_tab;?>' , [0] , [0] , {
                "from_date": '#from_date',
                "to_date": '#to_date',
                "imap_id": '#imap_id',
                "inbox_type": '#inbox_type',
            } , [4,"desc"] );

            $('.table-emails').on('init.dt', function (e, settings) {

                var tm_dt_table_button ='<button onclick="email_template_manage_mass( \'add_star\' )" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_add_star')?>" class="btn btn-default buttons-collection btn-sm btn-default-dt-options"> ' +
                    '<a class="fa fa-star text-success"></a> ' +
                    '</button> '
                    +
                    '<button onclick="email_template_manage_mass( \'remove_star\' )" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_remove_star')?>" class="btn btn-default buttons-collection btn-sm btn-default-dt-options"> ' +
                    '<a class="fa fa-star text-default"></a> ' +
                    '</button> '
                    +
                    '<button onclick="email_template_manage_mass( \'unread\' )" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_mark_as_unread')?>" class="btn btn-default buttons-collection btn-sm btn-default-dt-options"> ' +
                    '<a class="fa fa-envelope text-warning"></a> ' +
                    '</button> '
                    +
                    '<button onclick="email_template_manage_mass( \'read\' )" data-toggle="tooltip" title="" data-original-title="<?php echo _l('email_template_manage_mark_as_read')?>" class="btn btn-default buttons-collection btn-sm btn-default-dt-options"> ' +
                    '<a class="fa fa-envelope-open text-default"></a> ' +
                    '</button> '
                    +
                    '<button onclick="email_template_manage_mass( \'delete\' )" data-toggle="tooltip" title="" data-original-title="<?php echo _l('delete')?>" class="btn btn-default buttons-collection btn-sm btn-default-dt-options"> ' +
                    '<a class="fa fa-trash text-danger"></a> ' +
                    '</button> ';


                $('.table-emails').parents('.dataTables_wrapper').find('.dt-buttons').append(tm_dt_table_button);



            })

        }
        else if ( $(".table-mail-records").length > 0 ){

            initDataTable('.table-mail-records', admin_url + 'email_template_manage/mail_log_lists', [], [],
                {
                    "from_date": '#from_date',
                    "to_date": '#to_date',
                    "template_id": '#template_id',
                    "staff_id": '#staff_id',
                    "send_rel_type": '#send_rel_type',
                } , [0,"desc"] );

        }else if ( $(".table-templates").length > 0 ){

            initDataTable('.table-templates', admin_url + 'email_template_manage/imap_setting_lists' , false , false , [] , [1,"desc"] );


        }


    });


    function reload_the_table(table_name)
    {

        var $statementPeriod = $('#range');
        var value = $statementPeriod.selectpicker('val');
        var period = new Array();

        if (value != 'period')
        {
            period = JSON.parse(value);
        }
        else
        {
            period[0] = $('input[name="period-from"]').val();
            period[1] = $('input[name="period-to"]').val();

            if (period[0] == '' || period[1] == '') {
                return false;
            }
        }

        $('#from_date').val(period[0]);
        $('#to_date').val(period[1]);


        $('.table-' + table_name).DataTable().ajax.reload();

    }

    function email_template_manage_update_delete( email_id )
    {

        $.post(admin_url+'email_template_manage/inbox_delete', { email_id:email_id } ).done(function (){

            alert_float('success' , '<?php echo _l('email_template_manage_success')?>');

            $('.table-emails').DataTable().ajax.reload();

        })

    }

    function email_template_manage_update_star( email_id , star_status )
    {

        $.post(admin_url+'email_template_manage/inbox_star', { email_id:email_id , star_status:star_status } ).done(function (){

            alert_float('success' , '<?php echo _l('email_template_manage_success')?>');

            $('.table-emails').DataTable().ajax.reload();

        })

    }

    function email_template_manage_mass( action )
    {



        if ( $('.table-emails').find('.etm_inbox_checkbox:checked').length > 0 )
        {

            if (confirm_delete())
            {

                var inbox_rows = $('.table-emails').find('.etm_inbox_checkbox:checked');

                var inbox_mails = [];


                $.each(inbox_rows, function() {

                    inbox_mails.push( $(this).val() )

                });


                $.post(admin_url+'email_template_manage/inbox_mass', { action:action , inbox_mails } ).done(function (){

                    alert_float('success' , '<?php echo _l('email_template_manage_success')?>');

                    $('.table-emails').DataTable().ajax.reload();

                })
            }


        }

    }

    function fnc_imap_setting_dlg( record_id )
    {

        $('#template_definition').modal();

        $('#template_definition_content').html(' <div style="margin: 20px; padding: 20px;"> <div class="email-template-loading-spinner"></div> </div>');


        requestGet( admin_url+"email_template_manage/imap_detail/"+record_id ).done(function ( response ){

            $('#template_definition_content').html( response ).promise().done(function (){

                init_selectpicker();

            });

        }).fail(function(error) {

            $('#template_definition').modal('hide');

            var response = JSON.parse(error.responseText);

            alert_float('danger', response.message);

        });

    }


    function fnc_email_template_mail_content( mail_record_id )
    {

        requestGet( admin_url+'email_template_manage/inbox_detail_inc/'+mail_record_id ).done(function ( response ){

            $('#email_template_manage_tabs').hide();
            $('#email_template_manage_content').show();
            $('#email_template_manage_content').html(response);

        })

    }

    function email_template_manage_back_to_list()
    {

        $('#email_template_manage_tabs').show();
        $('#email_template_manage_content').hide();

    }

</script>

<style>

    .tmp_unread{
        font-weight: bold;
        color: #333333;
    }

    .text-default{
        color: #dae1e8;
    }

</style>
