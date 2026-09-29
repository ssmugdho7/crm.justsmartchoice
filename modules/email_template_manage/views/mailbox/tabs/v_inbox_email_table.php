<div class="row">

    <div class="col-md-3">
        <input type="hidden" name="from_date" id="from_date" value="">
        <input type="hidden" name="to_date" id="to_date" value="">
        <?php $this->load->view('_statement_period_select', ['onChange' => "reload_the_table('emails')"]); ?>
    </div>

    <div class="col-md-3">

        <div class="form-group">

            <select name="imap_id" id="imap_id" class="form-control selectpicker"
                    data-live-search="true" data-width="100%"
                    onchange="reload_the_table('emails')"
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