<table class="table dt-table table-requests" data-order-col="1" data-order-type="desc">
				<thead>
					<tr>
						<th class="th-invoice-number"><?php echo _l('wshop_code'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_inspection_type'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_inspection_template'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_devices'); ?></th>
						<th class="th-invoice-number"><?php echo _l('client'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_repair_job'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_start_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_due_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_interval'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_next_inspection_date'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_next_inspection_alert'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_status'); ?></th>
						<th class="th-invoice-number"><?php echo _l('invoice'); ?></th>
						<th class="th-invoice-number"><?php echo _l('wshop_options'); ?></th>
						
					</tr>
				</thead>
				<tbody>
					<?php foreach($inspections as $inspection){ ?>
						<?php 
						$CI = &get_instance();
						$device = $CI->workshop_model->get_device($inspection['device_id']);
						?>
						<tr>
							<td data-order="<?php echo new_html_entity_decode($inspection['id']); ?>"><a href="<?php echo site_url('workshop/client/inspection_detail/'.$inspection['id'].'?tab=detail') ?>"><?php echo format_inspection_number($inspection['id']); ?></a></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['inspection_type_id']); ?>" ><?php echo wshop_get_category_name($inspection['inspection_type_id']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['inspection_template_id']); ?>" ><?php echo wshop_inspection_template_name($inspection['inspection_template_id']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['device_id']); ?>"><?php echo new_html_entity_decode(get_device_name($inspection['device_id'])); ?></td>
							<td data-order="<?php echo get_company_name($inspection['client_id']); ?>"><?php echo new_html_entity_decode(get_company_name($inspection['client_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['repair_job_id']); ?>"><?php echo get_repair_job_name(($inspection['repair_job_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['start_date']); ?>"><?php echo  _dt($inspection['start_date']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['end_date']); ?>"><?php echo _dt($inspection['end_date']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['interval_id']); ?>"><?php echo new_html_entity_decode(get_interval_name($inspection['interval_id'])); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['next_inspection_date']); ?>"><?php echo  _dt($inspection['next_inspection_date']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['next_inspection_alert']); ?>"><?php echo  new_html_entity_decode($inspection['next_inspection_alert']); ?></td>
							<td data-order="<?php echo new_html_entity_decode($inspection['status']); ?>"><?php echo render_inspection_status_html($inspection['id'], '', $inspection['status'], false); ?></td>

							<?php if($inspection['invoice_id'] != 0){ ?>
								<td data-order="<?php echo new_html_entity_decode($inspection['invoice_id']); ?>"><a href="<?php echo site_url('invoice/'.$inspection['invoice_id'].'/'.workshop_get_invoice_hash($inspection['invoice_id'])) ?>"><?php echo format_invoice_number($inspection['invoice_id']); ?></a></td>
							<?php }else{ ?>
								<td data-order="<?php echo new_html_entity_decode($inspection['invoice_id']); ?>"></td>
							<?php } ?>

							<?php 
							$option = '';
							$option .='<a href="'. site_url('workshop/client/inspection_detail/'.$inspection['id']).'?tab=detail" class="btn btn-default btn-icon mright5" data-toggle="tooltip" data-title="'._l('view').'" data-placement="bottom"><i class="fa-regular fa-eye"></i></a>';

							$_data = $option;
							?>
							<td data-order="<?php echo new_html_entity_decode($inspection['id']); ?>"><?php echo new_html_entity_decode($option); ?></td>

						</tr>
					<?php } ?>
				</tbody>
			</table>