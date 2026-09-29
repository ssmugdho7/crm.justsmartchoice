<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php 	
$sent = '<i class="fa fa-check text-success"></i>';
$not_sent = '<i class="fa fa-close text-danger"></i>';
?>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_name'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo htmlspecialchars($schedule['name']);?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('lead_status'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo ($this->leads_model->get_status($schedule['status'])->name);?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('lead_source'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php if($schedule['source'] > 0) echo ($this->leads_model->get_source($schedule['source'])->name);?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('contract_send_to'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php 
						$filter_by = _l('leads');
						if($schedule['filter_by'] == 'staff') $filter_by = _l('staff_members');
						echo $filter_by;?>
					</div>
				</div>
				<?php if($schedule['send_sms']){?>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_text'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo nl2br($schedule['content']);?>
					</div>
				</div>
				<?php }?>
				<?php if($schedule['send_email']){?>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('send_file_subject'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo nl2br($schedule['email_subject']);?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('email_template'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo nl2br($schedule['email_content']);?>
					</div>
				</div>
				<?php if($schedule['email_attachment_id'] > 0){?>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_attachment'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php
						$type = 'si_lead_followup';
						$upload_path        = get_upload_path_by_type('si_lead_followup');
						$url                = admin_url() . 'si_lead_followup/download/';
						
						$path               = $upload_path . $schedule['id'] . '/' . $schedule['attachment'];
						$is_image           = false;
						
						$attachment_url = $url . $schedule['email_attachment_id'];
						$is_image       = is_image($path);
						$img_url        = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $schedule['filetype']);
						$lightBoxUrl    = site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type=' . $schedule['filetype']);
						if ($is_image) {
							echo '<div class="preview_image">';
						} ?>
							<a href="<?php if($is_image) { echo isset($lightBoxUrl) ? $lightBoxUrl : $img_url;} else { echo $attachment_url;} ?>" <?php if ($is_image) { ?> data-lightbox="customer-profile" <?php } ?> class="display-block mbot5">
							<?php if ($is_image) { ?>
							<div class="table-image">
								<img src="<?php echo $img_url; ?>">
							</div>
							<?php } else { ?>
							<i class="<?php echo get_mime_class($schedule['filetype']); ?>"></i>
							<?php echo $schedule['attachment']; ?>
							<?php } ?>
						</a>
						<?php if ($is_image) {
						echo '</div>';
						} ?>
					</div>
				</div>
				<?php }?>
				<?php }?>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_schedule_days'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo htmlspecialchars($schedule['schedule_days']).' '._l('days');?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_schedule_hour'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo date('h A',strtotime('01-01-1970 '.$schedule['schedule_hour'].":00:00"));?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('task_created_at'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php echo _d($schedule['dateadded']);?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('task_created_by'); ?> :</p>
					</div>
					<div class="col-md-9">
						<a data-toggle="tooltip" data-title="<?php echo get_staff_full_name($schedule['staff_id']) ?>" href="<?php echo admin_url('profile/' . $schedule['staff_id'])?>">
						<?php echo staff_profile_image($schedule['staff_id'], ['staff-profile-image-small',])?>
						</a>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<p class="bold"><?php echo _l('si_lfs_schedule_last_executed'); ?> :</p>
					</div>
					<div class="col-md-9">
						<?php if(!is_null($schedule['last_executed'])) echo _d($schedule['last_executed']);?>
					</div>
				</div>
				<hr />
				<div class="row">
					<div class="col-md-12">
						<p class="bold"><?php 
						echo _l('si_lfs_schedule_executed_list_info',$filter_by);
						?>
						</p>
					</div>
					<div class="col-md-12">
						<?php if(!empty($contacts)){?>
						<table class=" no-mtop table table-hover table-bordered">
							<thead>
								<tr>
									<th><?php echo _l('the_number_sign')?></th>
									<th><?php echo _l('name')?></th>
									<th><?php echo 'SMS'?></th>
									<th><?php echo _l('leads_dt_email')?></th>
									<?php if($schedule['filter_by']=='staff'){?>
									<th><?php echo _l('comment_string')?></th>
									<?php }?>
									<th><?php echo _l('view_date')?></th>
								</tr>	
							</thead>
							<tbody>
							<?php foreach($contacts as $contact){?>
								<tr>
									<td><?php echo $contact['rel_id'];?></td>
									<td><?php echo $contact['name'];?></td>
									<td><?php echo ($contact['sent_sms'] ? $sent : $not_sent);?></td>
									<td><?php echo ($contact['sent_email'] ? $sent : $not_sent);?></td>
									<?php if($schedule['filter_by']=='staff'){?>
									<td><?php echo $contact['comment']?></td>
									<?php }?>
									<td><?php echo _d($contact['dateadded']);?></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
						<?php }
							else
								echo _l('si_lfs_sent_error_message');	
						?>
						
					</div>
				</div>