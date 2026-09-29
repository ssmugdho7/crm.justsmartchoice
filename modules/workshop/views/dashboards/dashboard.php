
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" >
				<div class="panel_s">
					<div class="panel-body wshop-dashboard">
						<div class="row">
							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" class=" cs-portal-a-pause tw-flex tw-flex-wrap tw-items-center tw-justify-space-around" >
									<div><i class="fa-solid fa-dollar-sign tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-11">
										<h4 class="bold text-success cs-portal-h-cancelled" >
											<?php echo _l('wshop_today_repair_job'); ?>
										</h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_money(isset($repair_job_by_time_range['today_repair_job']) ? $repair_job_by_time_range['today_repair_job'] : 0 , $baseCurrency) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status statement-bg  projects-status">
								<a href="javascrip:void(0)" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-cancelled" >
									<div><i class="fa-solid fa-dollar-sign tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-11">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_past_week_repair_job') ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_money(isset($repair_job_by_time_range['past_week_repair_job']) ? $repair_job_by_time_range['past_week_repair_job'] : 0 , $baseCurrency) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-renewal" >
									<div><i class="fa-solid fa-dollar-sign tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-11">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_past_month_repair_job'); ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_money(isset($repair_job_by_time_range['past_month_repair_job']) ? $repair_job_by_time_range['past_month_repair_job'] : 0 , $baseCurrency) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-expired">
									<div><i class="fa-solid fa-dollar-sign tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-11">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_registration_booking'); ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($repair_job_by_time_range['registration_booking']) ? $repair_job_by_time_range['registration_booking'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-complete">
									<div><i class="fa-solid fa-check-to-slot tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold text-success cs-portal-h-cancelled" ><?php echo _l('wshop_total_repair_job'); ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($repair_job_by_time_range['total_repair_job']) ? $repair_job_by_time_range['total_repair_job'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-activate" >
									<div><i class="fa-solid fa-check-to-slot tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold text-success cs-portal-h-cancelled" ><?php echo _l('wshop_unassigned_repair_job') ; ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($repair_job_by_time_range['unassigned_repair_job']) ? $repair_job_by_time_range['unassigned_repair_job'] : 0 , true) ?></span>
									</div>
								</a>
							</div>
						</div>
						<div class="clearfix"></div>
                        <hr>
						<div class="row mtop15">
							<div class="col-md-3">
								<div class="form-group" id="report-time">
									<label for="mo_months-report"><?php echo _l('period_datepicker'); ?></label><br />
									<select class="selectpicker" name="mo_months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
										<option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
										<option value="this_month"><?php echo _l('this_month'); ?></option>
										<option value="1"><?php echo _l('last_month'); ?></option>
										<option value="this_year"><?php echo _l('this_year'); ?></option>
										<option value="last_year"><?php echo _l('last_year'); ?></option>
										<option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
										<option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
										<option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
										<option value="custom"><?php echo _l('period_datepicker'); ?></option>
									</select>
								</div>
							</div>

							<div id="mo_date-range" class="hide ">
								<div class="row">
									<div class="col-md-3">
										<label for="mo_report-from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
										<div class="input-group date">
											<input type="text" class="form-control datepicker" id="mo_report-from" autocomplete="off" name="mo_report-from">
											<div class="input-group-addon">
												<i class="fa fa-calendar calendar-icon"></i>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<label for="mo_report-to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
										<div class="input-group date">
											<input type="text" class="form-control datepicker" disabled="disabled" autocomplete="off" id="mo_report-to" name="mo_report-to">
											<div class="input-group-addon">
												<i class="fa fa-calendar calendar-icon"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>


						<div class="row">
							<div class="col-md-12">
								<h4 class="tw-font-semibold"><?php echo _l('wshop_repair_jobs'); ?></h4>
							</div>

							<div class="col-md-8">
								<div id="report_by_repair_job_weekly">
								</div>
							</div>
							<div class="col-md-12">
								<div id="report_by_repair_job_month">
								</div>
							</div>
							<div class="col-md-12">
								<div id="report_by_mechanic_performance">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<h4 class="tw-font-semibold"><?php echo _l('wshop_inspections'); ?></h4>
							</div>
						</div>

						<div class="row">
							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('Open'); return false;" class=" cs-portal-a-pause tw-flex tw-flex-wrap tw-items-center tw-justify-space-around" >
									<div><i class="fa-solid fa-magnifying-glass tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold text-success cs-portal-h-cancelled" >
											<?php echo _l('wshop_Open'); ?>
										</h4>
										<br>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['Open']) ? $count_inspection_by_status['Open'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status statement-bg  projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('In_Progress'); return false;" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-renewal" >
									<div><i class="fa-solid fa-spinner tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_In_Progress') ?></h4>
										<br>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['In_Progress']) ? $count_inspection_by_status['In_Progress'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('Waiting_For_Approval'); return false;" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-cancelled" >
									<div><i class="fa-solid fa-thumbs-up tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_Waiting_For_Approval'); ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['Waiting_For_Approval']) ? $count_inspection_by_status['Waiting_For_Approval'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('Complete_Awaiting_Finalise'); return false;" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-expired">
									<div><i class="fa-solid fa-clipboard-list tw-text-white tw-text-xl"></i></div>
									<div class="col-md-11">
										<h4 class="bold cs-portal-h-cancelled" ><?php echo _l('wshop_Complete_Awaiting_Finalise'); ?></h4>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['Complete_Awaiting_Finalise']) ? $count_inspection_by_status['Complete_Awaiting_Finalise'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('Completed'); return false;" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-complete">
									<div><i class="fa-solid fa-check-to-slot tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold text-success cs-portal-h-cancelled" ><?php echo _l('wshop_Completed'); ?></h4><br>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['Completed']) ? $count_inspection_by_status['Completed'] : 0 , true) ?></span>
									</div>
								</a>
							</div>

							<div class="col-md-2 list-status projects-status">
								<a href="javascrip:void(0)" onclick="inspection_status_filter('Overdue'); return false;" class="tw-flex tw-flex-wrap tw-items-center tw-justify-space-around cs-portal-a-activate" >
									<div><i class="fa-solid fa-expand tw-text-white tw-text-2xl"></i></div>
									<div class="col-md-10">
										<h4 class="bold text-success cs-portal-h-cancelled" ><?php echo _l('wshop_Overdue') ; ?></h4><br>
										<span class="bold cs-portal-h-cancelled" ><?php echo app_format_number(isset($count_inspection_by_status['Overdue']) ? $count_inspection_by_status['Overdue'] : 0 , true) ?></span>
									</div>
								</a>
							</div>
						</div>
						<div class="clearfix"></div>
                        <hr>
						<div class="row">
							<div class="col-md-12">
							<?php 
							render_datatable(
								array(
									_l('id'),
									[
										'name'  => _l('wshop_code'),
										'th_attrs' => [
											'style' => 'min-width:150px',
										],
									],
									_l('wshop_inspection_type'),
									_l('wshop_inspection_template'),
									_l('wshop_devices'),
									[
										'name'  => _l('client'),
										'th_attrs' => [
											'style' => 'min-width:200px',
										],
									],
									[
										'name'  => _l('wshop_repair_job'),
										'th_attrs' => [
											'style' => 'min-width:200px',
										],
									],
									_l('wshop_start_date'),
									_l('wshop_due_date'),
									_l('wshop_interval'),
									_l('wshop_next_inspection_date'),
									_l('wshop_next_inspection_alert'),
									_l('wshop_status'),
									_l('wshop_visible_to_customer'),
								),'inspection_table'
							);
							?>
						</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="inspection_status_filter">

<?php init_tail(); ?>
<?php 
require('modules/workshop/assets/js/dashboards/dashboard_js.php');
?>

</body>
</html>
