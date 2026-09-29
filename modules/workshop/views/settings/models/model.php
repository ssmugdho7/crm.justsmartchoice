<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div> 

	<div class="row">
		<div class="col-md-6">
			<h4><?php echo _l('wshop_models'); ?></h4>
		</div>
		<?php if(has_permission('workshop_setting', '', 'create')){ ?>
			<div class="col-md-6">
				<a href="#" onclick="new_model(); return false;" class="btn btn-info pull-right display-block">
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
			_l('wshop_name'),
			_l('wshop_model_no'),
			_l('wshop_category'),
			_l('wshop_description'),
			_l('wshop_status'),
			_l('options'),
		),'model_table'
	);
	?>

	<div class="modal fade" id="model" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<?php echo form_open_multipart(admin_url('workshop/model'), array('id'=>'add_edit_model')); ?>

			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">
						<span class="edit-title"><?php echo _l('wshop_edit_model'); ?></span>
						<span class="add-title"><?php echo _l('wshop_add_model'); ?></span>
					</h4>
				</div>

				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div id="model_additional"></div>
							<?php echo render_input('name', 'wshop_name'); ?>
							<?php echo render_select('manufacturer_id', $manufacturers, ['id', 'name'], 'wshop_manufacturer'); ?>
							<?php echo render_select('category_id', $categories, ['id', 'name'], 'wshop_category'); ?>
							<?php echo render_input('model_no', 'wshop_model_no'); ?>

							<?php echo render_select('fieldset_id', $fieldsets, ['id', 'name'], 'wshop_fieldset'); ?>

							<?php echo render_textarea('description', 'wshop_description'); ?>
							
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
					<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
				</div>
			</div><!-- /.modal-content -->
			<?php echo form_close(); ?>
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


</body>
</html>
