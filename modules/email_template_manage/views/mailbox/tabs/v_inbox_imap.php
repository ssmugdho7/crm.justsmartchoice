<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('email_template_manage_imap_setting') ?>

    <a style="float: right" href="#" onclick="fnc_imap_setting_dlg( 0 ) " class="btn btn-primary" > <i class="fa fa-add"> </i> <?php echo _l('email_template_manage_new_imap')?> </a>

</h4>

<br>

<div class="panel_s">


    <div class="panel-body">

        <?php if ( !extension_loaded('imap') ) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <?php echo _l('email_template_manage_imap_extension_error')?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="clearfix"></div>

        <div class="table-responsive ">

            <table class="table table-templates">
                <thead>
                    <tr>
                        <th><?php echo _l('id')?></th>
                        <th><?php echo _l('settings_general_company_name')?></th>
                        <th><?php echo _l('settings_email')?></th>
                        <th><?php echo _l('email_template_manage_status')?></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>

    </div>

</div>

