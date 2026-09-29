<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if(isset($inspection)){ ?>
	<div class="row">
		<div class="col-md-6">
			<div class="row">
				<div class="col-md-12">
					<table class="table border table-striped no-mtop">
						<tbody>
							<tr class="project-overview">
								<td class="bold" width="30%"><?php echo _l('wshop_code'); ?></td>
								<td><?php echo format_inspection_number($inspection->id) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_inspection_type'); ?></td>
								<td><?php echo wshop_get_category_name($inspection->inspection_type_id) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_inspection_template'); ?></td>
								<td><?php echo wshop_inspection_template_name($inspection->inspection_template_id) ; ?></td>
							</tr>
							
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_datecreated'); ?></td>
								<td><?php echo _dt($inspection->datecreated) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_created_by'); ?></td>
								<td><?php echo get_staff_full_name($inspection->staffid) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_start_date'); ?></td>
								<td><?php echo _d($inspection->start_date) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_end_date'); ?></td>
								<td><?php echo _d($inspection->end_date) ; ?></td>
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
								<td class="bold"><?php echo _l('wshop_person_in_charge'); ?></td>
								<td><?php echo get_staff_full_name($inspection->person_in_charge) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold" width="30%"><?php echo _l('client'); ?></td>
								<td><?php echo get_company_name($inspection->client_id) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_contact_email'); ?></td>
								<td><?php echo html_entity_decode($inspection->contact_email) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_branch_phone'); ?></td>
								<td><?php echo html_entity_decode($inspection->phonenumber) ; ?></td>
							</tr>

							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_next_inspection_date'); ?></td>
								<td><?php echo _d($inspection->next_inspection_date ?? '') ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_interval'); ?></td>
								<td><?php echo get_interval_name($inspection->interval_id) ; ?></td>
							</tr>
							<tr class="project-overview">
								<td class="bold"><?php echo _l('wshop_status'); ?></td>
								<td><?php echo render_inspection_status_html($inspection->id, '', $inspection->status) ; ?></td>
							</tr>
						</tbody>
					</table>

				</div>

			</div>
		</div>
	</div>

	<h4 class="tw-font-semibold"><?php echo _l('wshop_description') ?></h4>
	<p class=""><?php echo new_html_entity_decode(check_for_links($inspection->description)); ?></p>
	<!-- attachments -->
	<?php if(isset($inspection_attachments) && count($inspection_attachments) > 0){ ?>
		<div class="row">
			<div class="col-md-12">
				<h4 class="tw-font-semibold">
					<?php echo _l('wshop_attachments'); ?>
				</h4>
				<div id="contract_attachments" class="mtop30 ">

					<?php
					$data = '<div class="row" id="attachment_file">';
					foreach($inspection_attachments as $attachment) {
						$data .= '<div class="col-md-4 pdf_attachment">';
						$href_url = site_url('modules/workshop/uploads/inspections/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
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
						if(is_admin() || has_permission('wshop_inspections', '', 'delete') ){
							$data .= '<a href="#" class="text-danger btn btn-sm" onclick="delete_inspection_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
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
				<div id="pdf_file_data"></div>
			</div>
		</div>
	<?php } ?>

	<div class="row">
		<div class="col-md-12">
			<hr class="">
		</div>
	</div>
	<div class="row">
	<div class="col-md-8">
		<table class="table items items-preview estimate-items-preview no-mtop" data-type="estimate">
			<thead>
				<tr>
					<th width="40%" class="unit_price" align="left"><?php echo _l('wshop_template_name'); ?></th>
					<th width="20%" class="hide" align="right"><?php echo _l('wshop_progress'); ?></th>
					<th width="5%" colspan="3" class="estimated_hours" align="right"><?php echo _l('wshop_options'); ?></th>
				</tr>
			</thead>
			<tbody class="ui-sortable">
				<tr>
					<td ><?php echo format_inspection_number($inspection->id) ?></td>
					<td class="hide"><?php echo new_html_entity_decode(2) ?></td>
					<?php 
					$checked = '';
					$_inspection_option1 = '';
					$_inspection_option3 = '';
					if ($inspection->visible_to_customer == 1) {
						$checked = 'checked';
					}

					if($inspection->status == 'In_Progress'){
						if(!is_client_logged_in()){
							$_inspection_option1 .= '<a href="'.admin_url('workshop/inspection_form/'.$inspection->id).'" class="btn btn-info mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_form').'">
							<i class="fa-solid fa-play"></i>
							</a>';
						}
					}elseif($inspection->status == 'Waiting_For_Approval' && false){
						$_inspection_option1 .= '<a href="'.admin_url('workshop/inspection_form_detail/'.$inspection->id).'" class="btn btn-warning mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_approval').'">
						<i class="fa-solid fa-thumbs-up"></i>
						</a>';
					}

					if(is_client_logged_in()){
						$_inspection_option2 = '<a href="'.site_url('workshop/client/inspection_form_detail/'.$inspection->id).'" class="btn btn-success mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_form_detail').'">
						<i class="fa-solid fa-eye"></i>
						</a>';
					}else{

						$_inspection_option2 = '<a href="'.admin_url('workshop/inspection_form_detail/'.$inspection->id).'" class="btn btn-success mright5 pull-right" data-toggle="tooltip" data-original-title="'._l('wshop_inspection_form_detail').'">
						<i class="fa-solid fa-eye"></i>
						</a>';
					}

					if(!is_client_logged_in()){
						$_inspection_option3 .= '<div class="onoffswitch">
						<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_inspection', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_inspection_visible" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $inspection->id . '" data-id="' . $inspection->id . '" data-status="' . $inspection->visible_to_customer . '" ' . $checked . '>
						<label class="onoffswitch-label" for="c_' . $inspection->id . '"></label>
						</div>';
					}

					?>
					<td ><?php echo new_html_entity_decode($_inspection_option1) ?></td>
					<td ><?php echo new_html_entity_decode($_inspection_option2) ?></td>
					<td ><?php echo new_html_entity_decode($_inspection_option3) ?></td>

				</tr>
			</tbody>
		</table>
	</div>
	</div>

	<?php } ?>