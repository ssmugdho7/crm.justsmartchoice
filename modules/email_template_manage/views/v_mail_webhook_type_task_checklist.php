<div class="col-md-6">

    <div class="form-group">

        <div class="checkbox checkbox-primary">

            <input type="checkbox" id="send_to_staff" name="send_to_staff" <?php echo !empty( $record_data->staff_active ) || empty( $record_data ) ? 'checked' : '' ?> value="1">

            <label for="send_to_staff">
                <?php echo _l('email_template_manage_send_to_assigned') ?> ,
                <?php echo _l('email_template_manage_send_to_followers') ?>
            </label>

        </div>

    </div>

</div>