<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if ( !empty( $client->userid ) ) { ?>

    <h4 class="customer-profile-group-heading">
        <?php echo _l('email_template_manage_email_log')?>
    </h4>


    <div class="row">

        <div class="col-md-12">

            <div class="top-lead-menu">

                <div class="horizontal-tabs">

                    <ul class="nav-tabs-horizontal nav nav-tabs" role="tablist">

                        <li role="presentation" class="active">

                            <a href="#etm_sending_emails" aria-controls="etm_sending_emails" role="tab"

                               data-toggle="tab">

                                <?php echo _l('email_template_manage_sent_emails'); ?>

                            </a>

                        </li>

                        <li role="presentation" class="">

                            <a href="#etm_income_emails" aria-controls="etm_income_emails" role="tab"

                               data-toggle="tab">

                                <?php echo _l('email_template_manage_inbox'); ?>

                            </a>

                        </li>

                    </ul>

                </div>


                <div class="tab-content">


                    <div role="tabpanel" class="tab-pane active" id="etm_sending_emails">

                        <!-- Sent emails box -->

                        <div class="row">
                            <div class="col-md-4 mbot20">
                                <a class="btn btn-primary" onclick="email_template_manage_send_mail( 'customer' , <?php echo $client->userid ?> ); return false;">
                                    <span class="fa fa-envelope"></span>
                                    <?php echo _l('email_template_manage_send_mail')?>
                                </a>
                                <br />
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="row">

                            <div class="col-md-12">

                                <div class="table-responsive s_table">

                                    <table class="table table-mail-records">
                                        <thead>
                                        <tr>
                                            <th><?php echo _l('id')?></th>
                                            <th><?php echo _l('staff')?></th>
                                            <th><?php echo _l("email_template_manage_log_table_head_subject")?></th>
                                            <th><?php echo _l("email_template_manage_log_table_head_date")?></th>
                                            <th><?php echo _l("email_template_manage_log_table_head_status")?></th>
                                            <th><?php echo _l("email_template_manage_opened")?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div>

                            </div>

                        </div>


                    </div>

                    <div role="tabpanel" class="tab-pane" id="etm_income_emails">


                        <!-- inbox mails -->


                        <div class="row">

                            <div class="col-md-12">

                                <div class="table-responsive s_table">

                                    <table class="table table-income-records">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('id')?></th>
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

            </div>


        </div>


    </div>





    <div class="modal fade" id="email_template_manage_mail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

        <div class="modal-dialog modal-lg" role="document">

            <div class="modal-content" id="email_template_manage_mail_content">


            </div>

        </div>

    </div>

    <input type="hidden" name="email_template_manage_rel_type" value="customer">
    <input type="hidden" name="email_template_manage_rel_id" value="<?php echo $client->userid?>">

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            var mail_post_data = {};

            mail_post_data['rel_type']  = '[name="email_template_manage_rel_type"]';
            mail_post_data['rel_id']    = '[name="email_template_manage_rel_id"]';

            initDataTable('.table-mail-records', admin_url + 'email_template_manage/mail_log_lists', false, false, mail_post_data , [0,"desc"]);



            initDataTable('.table-income-records', admin_url + 'email_template_manage/mail_log_lists_income', false, false, mail_post_data , [0,"desc"]);



            window.email_template_manage_update_delete = function (email_id) {
                $.post(admin_url + "email_template_manage/inbox_delete", { email_id: email_id }).done(function () {
                    alert_float("success", "<?php echo _l('email_template_manage_success')?>");
                    $(".table-income-records").DataTable().ajax.reload();
                });
            };


            window.email_template_manage_update_star = function (email_id, star_status) {
                $.post(admin_url + "email_template_manage/inbox_star", { email_id: email_id, star_status: star_status, }).done(function () {
                    alert_float("success", "<?php echo _l('email_template_manage_success')?>");
                    $(".table-income-records").DataTable().ajax.reload();
                });
            };

        });

    </script>

<?php } ?>
