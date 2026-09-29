<table class="table dt-table table-requests" data-order-col="1" data-order-type="desc">
				<thead>
					<tr>
						<th class="th-invoice-number"><?php echo _l('wshop_job_tracking_number'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_repair_job_id'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_appointment_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_estimated_completion_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_appointment_type'); ?></th>
						<th class="th-invoice-number"><?php echo _l('client'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_branch_phone'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_device'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_model'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_mechanic'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_total'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_estimated_hours'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_status'); ?></th>
						<th class="th-invoice-number"><?php echo _l('invoice'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_options'); ?></th>
						
					</tr>
				</thead>
				<tbody>
					<?php foreach($repair_jobs as $repair_job){ ?>
						<?php 
						$CI = &get_instance();
						$device = $CI->workshop_model->get_device($repair_job['device_id']);
						?>
						<tr>
							<td data-order="<?php echo new_html_entity_decode($repair_job['id']); ?>"><a href="<?php echo site_url('workshop/client/repair_job_detail/'.$repair_job['id'].'?tab=detail') ?>"><?php echo new_html_entity_decode($repair_job['job_tracking_number']); ?></a></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['id']); ?>"><?php echo new_html_entity_decode(format_repair_job_number($repair_job['id'])); ?></td>

							<td data-order="<?php echo new_html_entity_decode($repair_job['appointment_date']); ?>"><?php echo  _dt($repair_job['appointment_date']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['estimated_completion_date']); ?>"><?php echo _dt($repair_job['estimated_completion_date']); ?></td>

							<td data-order="<?php echo new_html_entity_decode($repair_job['appointment_type_id']); ?>" ><?php echo wshop_get_appointment_type_name($repair_job['appointment_type_id']); ?></td>
							<td data-order="<?php echo get_company_name($repair_job['client_id']); ?>"><?php echo new_html_entity_decode(get_company_name($repair_job['client_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['phonenumber']); ?>"><?php echo new_html_entity_decode(($repair_job['phonenumber'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['device_id']); ?>"><?php echo new_html_entity_decode(get_device_name($repair_job['device_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($device->model_id); ?>"><?php echo new_html_entity_decode(wshop_get_model_name($device->model_id)); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['sale_agent']); ?>"><?php echo new_html_entity_decode(get_staff_full_name($repair_job['sale_agent'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['total']); ?>"><?php echo new_html_entity_decode(app_format_money($repair_job['total'], $repair_job['currency'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['estimated_hours']); ?>"><?php echo new_html_entity_decode($repair_job['estimated_hours']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($repair_job['estimated_hours']); ?>"><?php echo render_repair_job_status_html($repair_job['id'], '', $repair_job['status'], false); ?></td>
							

							<?php if($repair_job['invoice_id'] != 0){ ?>
								<td data-order="<?php echo new_html_entity_decode($repair_job['invoice_id']); ?>"><a href="<?php echo site_url('invoice/'.$repair_job['invoice_id'].'/'.workshop_get_invoice_hash($repair_job['invoice_id'])) ?>"><?php echo format_invoice_number($repair_job['invoice_id']); ?></a></td>
							<?php }else{ ?>
								<td data-order="<?php echo new_html_entity_decode($repair_job['invoice_id']); ?>"></td>
							<?php } ?>

							<?php 
							$option = '';
							$option .='<a href="'. site_url('workshop/client/repair_job_detail/'.$repair_job['id']).'?tab=detail" class="btn btn-default btn-icon mright5" data-toggle="tooltip" data-title="'._l('view').'" data-placement="bottom"><i class="fa-regular fa-eye"></i></a>';

							$_data = $option;
							?>
							<td data-order="<?php echo new_html_entity_decode($repair_job['id']); ?>"><?php echo new_html_entity_decode($option); ?></td>

						</tr>
					<?php } ?>
				</tbody>
			</table>