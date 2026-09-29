<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div> 

	<div class="row">
		<div class="col-md-6">
			<h4><?php echo _l('wshop_appointment_types'); ?></h4>
		</div>
		<?php if(has_permission('workshop_setting', '', 'create')){ ?>
			<div class="col-md-6">
				<a href="#" onclick="appointment_type_modal(0); return false;" class="btn btn-info pull-right display-block">
					<?php echo _l('wshop_new'); ?>
				</a>
			</div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>
	<hr>

	<?php 
	render_datatable(
		array(
			_l('id'),
			_l('wshop_code'),
			_l('wshop_name'),
			_l('wshop_labour_products'),
			_l('wshop_status'),
			_l('wshop_estimated_hours'),
			_l('wshop_description'),
			_l('options'),
		),'appointment_type_table'
	);
	?>
</body>
</html>
