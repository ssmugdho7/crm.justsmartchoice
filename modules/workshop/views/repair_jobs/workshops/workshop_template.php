<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$CI = &get_instance();
?>
<?php if(isset($workshops) && count($workshops) > 0){ ?>
	<h4 class="tw-font-semibold"><?php echo _l('wshop_workshops') ?></h4>

	<div class="activity-feed">
		<?php foreach($workshops as $log){ ?>

			<div class="feed-item">
				<div class="date">
					<span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['datecreated']); ?>">
						<?php echo time_ago($log['datecreated']); ?>
					</span>
					<?php if($log['staffid'] == get_staff_user_id() || is_admin() || has_permission('wshop_workshops','','delete')){ ?>
						<a href="#" class="btn btn-sm btn-danger pull-right text-danger" onclick="delete_workshop(<?php echo new_html_entity_decode($log['id']); ?>);return false;"><i class="fa fa fa-times"></i></a>
					<?php } ?>
					<?php if($log['staffid'] == get_staff_user_id() || is_admin() || has_permission('wshop_workshops','','edit')){ ?>
						<a href="#" class="btn btn-sm btn-info pull-right mright5" onclick="workshop_modal(<?php echo new_html_entity_decode($log['id']); ?>, <?php echo new_html_entity_decode($log['repair_job_id']); ?>);return false;"><i class="fa-regular fa-pen-to-square"></i></a>
					<?php } ?>
				</div>
				<div class="text">
					<?php if($log['sale_agent'] != 0){ ?>
						<a href="<?php echo admin_url('profile/'.$log["sale_agent"]); ?>">
							<?php echo staff_profile_image($log['sale_agent'],array('staff-profile-xs-image pull-left mright5'));
							?>
						</a>
						<?php
					}

					echo '<span class="tw-font-semibold">'. get_staff_full_name($log['sale_agent']) . ' - '. $log['name'].'</span>';
					
					?>
				</div>
				<div class="text mtop10">
					<span class="tw-font-semibold"><?php echo _l('wshop_Report_Type'); ?></span>: <?php echo wshop_get_category_name($log['report_type_id']) ?></br>
					<span class="tw-font-semibold"><?php echo _l('wshop_Report_Status'); ?></span>: <?php echo wshop_get_category_name($log['report_status_id']) ?></br>
					<span class="tw-font-semibold"><?php echo _l('wshop_parts_information'); ?></span>: <?php echo html_entity_decode($log['parts_information']) ?></br>
					<span class="tw-font-semibold"><?php echo _l('wshop_notes'); ?></span>: <?php echo html_entity_decode($log['description']) ?></br>
					<span class="tw-font-semibold"><?php echo _l('wshop_from_date'); ?></span>: <?php echo _dt($log['from_date']) ?></br>
					<span class="tw-font-semibold"><?php echo _l('wshop_to_date'); ?></span>: <?php echo _dt($log['to_date']) ?>
				</div>

				<?php 
	                                    // get note attachments
				$note_att = $CI->workshop_model->get_attachment_file($log['id'], 'wshop_workshop');
				if(count($note_att) > 0){
					$data = '<div class="row" id="attachment_file">';
					foreach($note_att as $attachment) {
						$data .= '<div class="col-md-12 pdf_attachment">';
						$href_url = site_url('modules/workshop/uploads/workshops/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
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
						if($log['staffid'] == get_staff_user_id() || is_admin() || has_permission('workshop_workshop', '', 'delete') ){
							$data .= '<a href="#" class="text-danger btn btn-sm" onclick="delete_workshop_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
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