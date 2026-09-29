<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$CI = &get_instance();
?>
<div class="row">
	<div class="col-md-6">
		<h4><?php echo _l('wshop_returns'); ?></h4>
	</div>
	<div class="col-md-6">
		<?php if(has_permission('workshop_inspection', '', 'create')){ ?>
			<?php if(!isset($returns) || count($returns) == 0){ ?>
				<a href="#" onclick="transaction_modal(0, <?php echo html_entity_decode($repair_job->id); ?>, 'return'); return false;" class="btn btn-info pull-right display-block">
					<?php echo _l('wshop_new'); ?>
				</a>
			<?php } ?>
		<?php } ?>

		<?php if(isset($returns) && count($returns) > 0){ ?>

			<?php if((has_permission('workshop_repair_job', '', 'delete'))){ ?>
				<a href="#" onclick="delete_transaction(<?php echo html_entity_decode($returns[0]['id']) ?>, 'return'); return false;" class="btn btn-danger pull-right display-block" data-toggle="tooltip" data-original-title="<?php echo _l('wshop_delete_return'); ?>">
					<i class="fa fa-remove"></i>
				</a>
			<?php } ?>

			<?php if((has_permission('workshop_repair_job', '', 'edit')  || is_admin())){ ?>
				<a href="#" onclick="transaction_modal(<?php echo html_entity_decode($returns[0]['id']) ?>, <?php echo html_entity_decode($returns[0]['repair_job_id']); ?>, '<?php echo html_entity_decode($returns[0]['transaction_type']); ?>'); return false;" class="btn btn-info pull-right display-block mright5" data-toggle="tooltip" data-original-title="<?php echo _l('wshop_edit_return'); ?>">
					<i class="fa-regular fa-pen-to-square"></i>
				</a>
			<?php } ?>

			<?php if(has_permission('workshop_inspection', '', 'create')){ ?>
				<a href="#" onclick="note_modal(0, <?php echo html_entity_decode($repair_job->id); ?>, <?php echo html_entity_decode($returns[0]['id']); ?>, 'return'); return false;" class="btn btn-info pull-right display-block mright5">
					<?php echo _l('wshop_add_note'); ?>
				</a>
			<?php } ?>

		<?php } ?>
	</div>

</div>

<div class="clearfix"></div>
<hr>

<?php if(isset($returns) && count($returns) > 0){ ?>
	<div class="row">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<table class="table border table-striped no-mtop">
						<tbody>
							<tr class="project-overview">
								<td class="bold" width="30%"><?php echo _l('wshop_return_name'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['name']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('client'); ?></td>
								<td><?php echo get_company_name($repair_job->client_id) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_contact_email'); ?></td>
								<td><?php echo html_entity_decode($repair_job->contact_email) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_branch_phone'); ?></td>
								<td><?php echo html_entity_decode($repair_job->phonenumber) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_sender_address'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['billing_street']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('billing_city'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['billing_city']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('billing_state'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['billing_state']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('billing_zip'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['billing_zip']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('billing_country'); ?></td>
								<td><?php echo get_country_name($returns[0]['billing_country']) ; ?></td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">

					<table class="table border table-striped no-mtop">
						<tbody>
							<tr class="project-overview">
								<td class="bold" width="30%"><?php echo _l('wshop_delivery_method'); ?></td>
								<td><?php echo wshop_get_category_name($returns[0]['delivery_method_id']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_job_tracking_number'); ?></td>
								<td><?php echo html_entity_decode($repair_job->job_tracking_number) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_expected_delivery_date'); ?></td>
								<td><?php echo _dt($returns[0]['expected_delivery_date']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_status'); ?></td>
								<td><?php echo render_transaction_status_html($returns[0]['id'], '', $returns[0]['status']); ; ?></td>
							</tr>

							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_receipt_address'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['shipping_street']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('shipping_city'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['shipping_city']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('shipping_state'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['shipping_state']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('shipping_zip'); ?></td>
								<td><?php echo html_entity_decode($returns[0]['shipping_zip']) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('shipping_country'); ?></td>
								<td><?php echo get_country_name($returns[0]['shipping_country']) ; ?></td>
							</tr>


						</tbody>
					</table>

				</div>

			</div>
		</div>
	</div>

	<h4 class="tw-font-semibold"><?php echo _l('wshop_description') ?></h4>
	<p class=""><?php echo new_html_entity_decode(check_for_links($returns[0]['description'])); ?></p>
	<!-- attachments -->
	<?php if(isset($return_attachments) && count($return_attachments) > 0){ ?>
		<div class="row">
			<div class="col-md-12">
				<h4 class="tw-font-semibold">
					<?php echo _l('wshop_attachments'); ?>
				</h4>
				<div id="contract_attachments" class="mtop30 ">

					<?php
					$data = '<div class="row" id="attachment_file">';
					foreach($return_attachments as $attachment) {
						$data .= '<div class="col-md-4 pdf_attachment">';
						$href_url = site_url('modules/workshop/uploads/return_deliveries/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
						if(!empty($attachment['external'])){
							$href_url = $attachment['external_link'];
						}
						$data .= '<div class="col-md-9">';

						$data .= '<div>';
						$data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
						$data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
						$data .= '</div>';
						$data .= '</div>';
						$data .= '<div class="col-md-3 text-right">';
						$data .= '<a class="btn btn-sm" name="preview-btn" onclick="preview_file(this); return false;" rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" data-toggle="tooltip" title data-original-title="'._l("preview_file").'"><i class="fa fa-eye"></i></a>';
						if(is_admin() || has_permission('workshop_repair_job', '', 'delete') ){
							$data .= '<a href="#" class="text-danger btn btn-sm" onclick="delete_transaction_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
						}
						$data .= '</div>';
						$data .= '<div class="clearfix"></div><hr class="mtop1 mbot5">';
						$data .= '</div>';
					}
					$data .= '</div>';
					echo new_html_entity_decode($data);
					?>

					<!-- check if edit contract => display attachment file end-->
				</div>
			</div>
		</div>
	<?php } ?>
	<!-- NOTE -->
	<?php if(isset($return_notes) && count($return_notes) > 0){ ?>
		<h4 class="tw-font-semibold"><?php echo _l('wshop_notes') ?></h4>

		<div class="activity-feed">
			<?php foreach($return_notes as $log){ ?>

				<div class="feed-item">
					<div class="date">
						<span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['datecreated']); ?>">
							<?php echo time_ago($log['datecreated']); ?>
						</span>
						<?php if($log['staffid'] == get_staff_user_id() || is_admin() || has_permission('wh_stock_export','','delete')){ ?>
							<a href="#" class="pull-right text-danger" onclick="delete_note(<?php echo new_html_entity_decode($log['id']); ?>, '<?php echo new_html_entity_decode($log['transaction_type']); ?>');return false;"><i class="fa fa fa-times"></i></a>
						<?php } ?>
					</div>
					<div class="text">
						<?php if($log['staffid'] != 0){ ?>
							<a href="<?php echo admin_url('profile/'.$log["staffid"]); ?>">
								<?php echo staff_profile_image($log['staffid'],array('staff-profile-xs-image pull-left mright5'));
								?>
							</a>
							<?php
						}

						echo get_staff_full_name($log['staffid']) . ' - ';
						echo _l($log['description']);
						?>
					</div>


					<?php 
	                                    // get note attachments
					$note_att = $CI->workshop_model->get_attachment_file($log['id'], 'wshop_note');
					if(count($note_att) > 0){
						$data = '<div class="row" id="attachment_file">';
						foreach($note_att as $attachment) {
							$data .= '<div class="col-md-12 pdf_attachment">';
							$href_url = site_url('modules/workshop/uploads/notes/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
							if(!empty($attachment['external'])){
								$href_url = $attachment['external_link'];
							}
							$data .= '<div class="col-md-9">';

							$data .= '<div>';
							$data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
							$data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
							$data .= '</div>';
							$data .= '</div>';
							$data .= '<div class="col-md-3 text-right">';
							$data .= '<a class="btn btn-sm" name="preview-btn" onclick="preview_file(this); return false;" rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" data-toggle="tooltip" title data-original-title="'._l("preview_file").'"><i class="fa fa-eye"></i></a>';
							if(is_admin() || has_permission('workshop_repair_job', '', 'delete') ){
								$data .= '<a href="#" class="text-danger btn btn-sm" onclick="delete_transaction_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
							}
							$data .= '</div>';
							$data .= '<div class="clearfix"></div><hr class="mtop1 mbot5">';
							$data .= '</div>';
						}
						$data .= '</div>';
						echo new_html_entity_decode($data);
						?>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

<?php } ?>


<?php if(1==2){ ?>
	<div class="hide">
		<?php 
		render_datatable(
			array(
				_l('id'),
				_l('wshop_name'),
				_l('client'),
				_l('wshop_job_tracking_number'),
				_l('wshop_delivery_method'),
				_l('wshop_expected_delivery_date'),
				_l('wshop_status'),
				_l('options'),
			),'return_table'
		);
		?>
	</div>
	<?php } ?>