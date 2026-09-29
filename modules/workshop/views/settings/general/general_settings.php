<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <h4 class="bold" ><?php echo _l('wshop_general_settings')?></h4>
        <hr class="hr-color" >
    </div>
</div>

<?php echo form_open_multipart(admin_url('workshop/general'),array('class'=>'general','autocomplete'=>'off')); ?>

<div class="form-group hide">
    <label for="show_tax_per_item" class="control-label clearfix"><?php echo _l('wshop_public_booking'); ?></label>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_1_wshop_public_booking" name="wshop_public_booking" value="1" <?php if(get_option('wshop_public_booking') == 1 ){ echo 'checked';} ?>>
        <label for="y_opt_1_wshop_public_booking"><?php echo _l('wshop_label_yes'); ?></label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_2_wshop_public_booking" name="wshop_public_booking" value="0" <?php if(get_option('wshop_public_booking') == 0 ){ echo 'checked';} ?>>
        <label for="y_opt_2_wshop_public_booking"><?php echo _l('wshop_label_no'); ?></label>
    </div>
<hr>
</div>
<?php 
$wshop_working_day_value = new_explode(',', get_option('wshop_working_day'));
?>
<div class="row">
    <div class="col-md-12">
        <label class="bold" ><?php echo _l('wshop_working_days_of_the_week')?></label>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_monday" name="wshop_working_day[]" value="1" <?php if(in_array(1, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_monday"><?php echo _l('wshop_monday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_tuesday" name="wshop_working_day[]" value="2" <?php if(in_array(2, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_tuesday"><?php echo _l('wshop_tuesday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_wednesday" name="wshop_working_day[]" value="3" <?php if(in_array(3, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_wednesday"><?php echo _l('wshop_wednesday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_thursday" name="wshop_working_day[]" value="4" <?php if(in_array(4, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_thursday"><?php echo _l('wshop_thursday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_friday" name="wshop_working_day[]" value="5" <?php if(in_array(5, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_friday"><?php echo _l('wshop_friday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_saturday" name="wshop_working_day[]" value="6" <?php if(in_array(6, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_saturday"><?php echo _l('wshop_saturday'); ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox checkbox-primary">
                <input type="checkbox" id="wshop_sunday" name="wshop_working_day[]" value="0" <?php if(in_array(0, $wshop_working_day_value)){echo 'checked';} ?>>
                <label for="wshop_sunday"><?php echo _l('wshop_sunday'); ?></label>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php echo render_input('wshop_shop_opens', 'wshop_shop_opens_label', get_option('wshop_shop_opens')); ?>
    </div>
    <div class="col-md-4">
        <?php echo render_input('wshop_shop_closes', 'wshop_shop_closes_label', get_option('wshop_shop_closes')); ?>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12">
        <?php echo render_textarea('wshop_repair_job_terms', 'wshop_repair_job_terms', get_option('wshop_repair_job_terms'), array(), array(), '', 'tinymce'); ?>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12">
        <?php echo render_textarea('wshop_report_footer', 'wshop_report_footer', get_option('wshop_report_footer')); ?>
    </div>
</div>
<hr>
<div class="row hide">
    <div class="col-md-12">
        <?php echo render_textarea('wshop_loan_terms', 'wshop_loan_terms', get_option('wshop_loan_terms'), array(), array(), '', 'tinymce'); ?>
    </div>
</div>

<div class="clearfix"></div>

<div class="btn-bottom-toolbar text-right">
    <?php if(has_permission('workshop_setting', '', 'create') || has_permission('workshop_setting', '', 'edit') ){ ?>
        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
    <?php } ?>
</div>
<?php echo form_close(); ?>


</body>
</html>


