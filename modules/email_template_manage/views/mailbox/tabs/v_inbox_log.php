<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('email_template_manage_email_log') ?>

</h4>

<div class="panel_s">

    <div class="panel-body">

        <div class="col-md-2">
            <input type="hidden" name="from_date" id="from_date" value="">
            <input type="hidden" name="to_date" id="to_date" value="">
            <?php $this->load->view('_statement_period_select', ['onChange' => "reload_the_table('mail-records')"]); ?>
        </div>

        <div class="col-md-3">
            <?php echo render_select( 'template_id' , $templates , [ 'id' , [ 'template_name'] ] ,  '' , '' , ['onchange' => "reload_the_table('mail-records')" , 'data-none-selected-text' => _l('email_template_manage') ] ); ?>
        </div>

        <div class="col-md-3">
            <?php echo render_select( 'staff_id' , $staff , [ 'staffid' , [ 'firstname' , 'lastname' ] ] ,  '' , '' , ['onchange' => "reload_the_table('mail-records')" , 'data-none-selected-text' => _l('staff') ] ); ?>
        </div>

        <div class="col-md-2">
            <?php echo render_select( 'send_rel_type' , $related_types , [ 'id' , [ 'value' ] ] ,  '' , '' , ['onchange' => "reload_the_table('mail-records')" , 'data-none-selected-text' => _l('email_template_manage_related_type') ] ); ?>
        </div>

        <div class="col-md-2 text-right">

            <a class="btn btn-danger _delete"

               href="<?php echo admin_url('email_template_manage/mail_log_clear'); ?>"><?php echo _l('clear_activity_log'); ?></a>

        </div>

        <div class="col-md-12">

            <div class="table-responsive ">

                <table class="table table-mail-records">
                    <thead>
                    <tr>
                        <th><?php echo _l('id')?></th>
                        <th><?php echo _l('staff')?></th>
                        <th><?php echo _l("email_template_manage_log_table_head_from")?></th>
                        <th><?php echo _l("email_template_manage_log_table_head_email")?></th>

                        <th><?php echo _l('email_template_manage_use_smtp')?></th>
                        <th><?php echo _l('email_template_manage_template_name')?></th>
                        <th><?php echo _l('email_template_manage_related_type')?></th>

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
