<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();?>

<div id="wrapper" >

    <div class="content">


        <div class="panel_s">

            <div class="panel-heading">

                <div>

                    <strong style="font-size: 20px"> <?php echo _l('email_template_manage_inbox')?> </strong>

                </div>

            </div>


            <div class="panel-body">


                <div class="row">

                    <div class="col-md-3">
                        <input type="hidden" name="from_date" id="from_date" value="">
                        <input type="hidden" name="to_date" id="to_date" value="">
                        <?php $this->load->view('_statement_period_select', ['onChange' => 'reload_the_table()']); ?>
                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <select name="inbox_type" id="inbox_type" class="form-control selectpicker"
                                    data-live-search="false" data-width="100%"
                                    onchange="reload_the_table()"
                                    data-none-selected-text="<?php echo _l('email_template_manage_inbox_type'); ?>">

                                <option value="all" selected><?php echo _l('all')?></option>
                                <option value="unred"> <?php echo _l('email_template_manage_unread_emails')?> </option>
                                <option value="star"> <?php echo _l('email_template_manage_star_emails')?> </option>
                                <option value="trash"> <?php echo _l('email_template_manage_removed_emails')?> </option>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <select name="imap_id" id="imap_id" class="form-control selectpicker"
                                    data-live-search="true" data-width="100%"
                                    onchange="reload_the_table()"
                                    data-none-selected-text="<?php echo _l('settings_general_company_name'); ?>">

                                <option value=""></option>

                                <?php foreach ( $imap_settings as $imap ) { ?>

                                    <option value="<?php echo $imap->id?>"> <?php echo $imap->company_name?> </option>

                                <?php } ?>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="clearfix"></div>



                <div class="table-responsive ">

                    <table class="table table-emails">
                        <thead>
                            <tr>
                                <th>
                                    <span class="hide"> - </span>
                                    <div class="checkbox mass_select_all_wrap">
                                        <input type="checkbox" id="mass_select_all" data-to-table="emails"><label></label>
                                    </div>
                                </th>
                                <th><?php echo _l('settings_general_company_name')?></th>
                                <th><?php echo _l('email_template_manage_from_name')?></th>
                                <th><?php echo _l('email_template_manage_email_subject')?></th>
                                <th><?php echo _l('email_template_manage_date')?></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>

            </div>


        </div>

    </div>

</div>

<?php init_tail(); ?>


<script>


    $(document).ready(function (){

        initDataTable('.table-emails', admin_url + 'email_template_manage/inbox_lists' , [0] , [0] , {
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


    })


    function reload_the_table()
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


        $('.table-emails').DataTable().ajax.reload();

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


</body>



</html>
