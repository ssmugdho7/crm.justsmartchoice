<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link href="<?php echo module_dir_url('si_todo','assets/css/si_todo_style.css'); ?>" rel="stylesheet">
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-7">
							<h4><?php echo _l('si_todo_category_menu'); ?></h4>
							</div>
							<div class="col-md-5 _buttons text-right si-todo-actions-bar">
								<a href="#si_todo_category_modal" data-toggle="modal" class="btn btn-info btn-sm">
									<?php echo _l('si_todo_add_category_title'); ?>
								</a>
								<a href="<?php echo admin_url('si_todo')?>" class="btn btn-default btn-sm">
									<?php echo _l('back'); ?>
								</a>
                                <a href="<?php echo admin_url('si_todo/settings'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_settings_menu'); ?></a>
                                <a href="<?php echo admin_url('si_todo/health'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_health_check'); ?></a>
                                <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><?php echo _l('refresh'); ?></a>
							</div>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="row">
							<div class="col-md-12">
								<div class="panel_s events animated fadeIn">
									<div class="panel-body todo-body">
										<h4 class="todo-title primary-bg"><i class="fa fa-tasks"></i>
										<?php echo _l('si_todo_category'); ?></h4>
										<ul class="list-unstyled todo todos-category si-todos-category-sortable">
											<li class="padding no-todos hide ui-state-disabled">
												<?php echo _l('si_todo_no_category_found'); ?>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div><!--row-->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('add_category'); ?>
<?php init_tail(); ?>
<script>
	var si_todo_alphanumeric_validation = '<?php echo _l('si_todo_alphanumeric_validation'); ?>';
	var si_todo_delete_validation = '<?php echo _l('si_todo_delete_validation'); ?>';
</script>
<script src="<?php echo module_dir_url('si_todo','assets/js/si_todo_category.js'); ?>"></script>
</body>
</html>