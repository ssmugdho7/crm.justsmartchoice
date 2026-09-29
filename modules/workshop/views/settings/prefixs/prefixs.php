<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open_multipart(admin_url('workshop/prefix_number'),array('class'=>'prefix_number','autocomplete'=>'off')); ?>

<div class="row">
	<div class="col-md-12">
		<h4 class="bold"><?php echo _l('wshop_prefixs') ?></h4>
		<hr class="hr-color">
	</div>
</div>

<h4 class="bold"><?php echo _l('wshop_repair_job') ?></h4>
<div class="form-group">
	<label><?php echo _l('wshop_repair_job_prefix'); ?></label>
	<div  class="form-group" app-field-wrapper="wshop_repair_job_prefix">
		<input type="text" id="wshop_repair_job_prefix" name="wshop_repair_job_prefix" class="form-control" value="<?php echo get_option('wshop_repair_job_prefix'); ?>">
	</div>
</div>

<div class="form-group">
	<label><?php echo _l('wshop_repair_job_number'); ?></label>
	<i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('settings_sales_next_invoice_number_tooltip'); ?>"></i>
	<div  class="form-group" app-field-wrapper="wshop_repair_job_number">
		<input type="number" min="0" id="wshop_repair_job_number" name="wshop_repair_job_number" class="form-control" value="<?php echo get_option('wshop_repair_job_number'); ?>">
	</div>
</div>

<div class="form-group">
	<label for="wshop_repair_job_number_format" class="control-label clearfix"><?php echo _l('wshop_repair_job_number_format'); ?></label>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_repair_job_number_format" value="1" id="repair_job_number_based" <?php if (get_option('wshop_repair_job_number_format') == '1') {
			echo 'checked';
		} ?>>
		<label for="repair_job_number_based"><?php echo _l('settings_sales_estimate_number_format_number_based'); ?></label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_repair_job_number_format" value="2" id="repair_job_year_based" <?php if (get_option('wshop_repair_job_number_format') == '2') {
			echo 'checked';
		} ?>>
		<label for="repair_job_year_based"><?php echo _l('settings_sales_estimate_number_format_year_based'); ?>(YYYY/000001)</label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_repair_job_number_format" value="3" id="repair_job_short_year_based" <?php if (get_option('wshop_repair_job_number_format') == '3') {
			echo 'checked';
		} ?>>
		<label for="repair_job_short_year_based">000001-YY</label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_repair_job_number_format" value="4" id="repair_job_year_month_based" <?php if (get_option('wshop_repair_job_number_format') == '4') {
			echo 'checked';
		} ?>>
		<label for="repair_job_year_month_based">000001/MM/YYYY</label>
	</div>
	<hr />
</div>

<h4 class="bold"><?php echo _l('wshop_inspection') ?></h4>
<div class="form-group">
	<label><?php echo _l('wshop_inspection_prefix'); ?></label>
	<div  class="form-group" app-field-wrapper="wshop_inspection_prefix">
		<input type="text" id="wshop_inspection_prefix" name="wshop_inspection_prefix" class="form-control" value="<?php echo get_option('wshop_inspection_prefix'); ?>">
	</div>
</div>

<div class="form-group">
	<label><?php echo _l('wshop_inspection_number'); ?></label>
	<i class="fa fa-question-circle i_tooltip" data-toggle="tooltip" title="" data-original-title="<?php echo _l('settings_sales_next_invoice_number_tooltip'); ?>"></i>
	<div  class="form-group" app-field-wrapper="wshop_inspection_number">
		<input type="number" min="0" id="wshop_inspection_number" name="wshop_inspection_number" class="form-control" value="<?php echo get_option('wshop_inspection_number'); ?>">
	</div>
</div>

<div class="form-group">
	<label for="wshop_inspection_number_format" class="control-label clearfix"><?php echo _l('wshop_inspection_number_format'); ?></label>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_inspection_number_format" value="1" id="inspection_number_based" <?php if (get_option('wshop_inspection_number_format') == '1') {
			echo 'checked';
		} ?>>
		<label for="inspection_number_based"><?php echo _l('settings_sales_estimate_number_format_number_based'); ?></label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_inspection_number_format" value="2" id="inspection_year_based" <?php if (get_option('wshop_inspection_number_format') == '2') {
			echo 'checked';
		} ?>>
		<label for="inspection_year_based"><?php echo _l('settings_sales_estimate_number_format_year_based'); ?>(YYYY/000001)</label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_inspection_number_format" value="3" id="inspection_short_year_based" <?php if (get_option('wshop_inspection_number_format') == '3') {
			echo 'checked';
		} ?>>
		<label for="inspection_short_year_based">000001-YY</label>
	</div>
	<div class="radio radio-primary radio-inline">
		<input type="radio" name="wshop_inspection_number_format" value="4" id="inspection_year_month_based" <?php if (get_option('wshop_inspection_number_format') == '4') {
			echo 'checked';
		} ?>>
		<label for="inspection_year_month_based">000001/MM/YYYY</label>
	</div>
	<hr />
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


