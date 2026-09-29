<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('wshop_repair_jobs'); ?></h4>
                            </div>
                            <div class="col-md-6">
                                <?php if(has_permission('workshop_repair_job', '', 'create')){ ?>
                                    <a href="<?php echo admin_url('workshop/add_edit_repair_job'); ?>" class="btn btn-info pull-right display-block">
                                        <?php echo _l('wshop_new'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('workshop/repair_jobs'); ?>" class="btn btn-default mright5 pull-right hidden-xs" data-toggle="tooltip" data-placement="top" data-title="Switch to Gird" data-original-title="" title="">
                                    <i class="fa-solid fa-table-cells"></i>
                                </a>
                            </div>
                        </div>
						<div class="dt-loader hide"></div>
						<?php $this->load->view('repair_jobs/calendar_filters'); ?>
						<div id="repair_job_calendar"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php hooks()->do_action('after_repair_job_calendar_loaded');?>

<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/repair_jobs/calendar_js.php');
 ?>
</body>
</html>
