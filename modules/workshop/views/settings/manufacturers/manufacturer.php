<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div> 

	<div class="row">
		<div class="col-md-6">
			<h4><?php echo _l('wshop_manufacturers'); ?></h4>
		</div>
		<?php if(has_permission('workshop_setting', '', 'create')){ ?>
			<div class="col-md-6">
				<a href="#" onclick="manufacturer_modal(0); return false;" class="btn btn-info pull-right display-block">
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
			_l('wshop_image'),
			_l('wshop_name'),
			_l('wshop_url'),
			_l('wshop_support_url'),
			_l('wshop_support_phone'),
			_l('wshop_support_email'),
			_l('wshop_device'),
			_l('wshop_status'),
			_l('options'),
		),'manufacturer_table'
	);
	?>

</body>
</html>
