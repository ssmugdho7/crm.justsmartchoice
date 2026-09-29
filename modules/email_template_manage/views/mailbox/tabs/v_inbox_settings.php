<h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

    <?php echo _l('settings') ?>

</h4>

<?php echo form_open( admin_url().'email_template_manage/save_setting' ) ?>

<div class="panel_s">

    <div class="panel-body">

        <div class="col-md-12">

            <div class="form-group">

                <div class="checkbox checkbox-primary">

                    <?php $email_opt = get_option('etm_add_staff_name_to_from'); ?>

                    <input type="checkbox" <?php echo !empty( $email_opt ) && $email_opt == 1 ? 'checked' : '' ?> id="etm_add_staff_name_to_from" name="etm_add_staff_name_to_from" value="1">

                    <label for="etm_add_staff_name_to_from"><?php echo _l('email_template_manage_add_staff_name_to_from') ?></label>

                </div>

            </div>

        </div>

        <div class="col-md-12">

            <div class="form-group">

                <div class="checkbox checkbox-primary">

                    <?php $email_opt = get_option('etm_staff_see_only_sent_mail'); ?>

                    <input type="checkbox" <?php echo !empty( $email_opt ) && $email_opt == 1 ? 'checked' : '' ?> id="etm_staff_see_only_sent_mail" name="etm_staff_see_only_sent_mail" value="1">

                    <label for="etm_staff_see_only_sent_mail"><?php echo _l('email_template_manage_staff_see_only_sent_mail') ?></label>

                </div>

            </div>

        </div>

    </div>

    <div class="panel-footer">
        <button type="submit" class="btn btn-primary"> <?php echo _l('submit')?> </button>
    </div>

</div>


<?php echo form_close(); ?>
