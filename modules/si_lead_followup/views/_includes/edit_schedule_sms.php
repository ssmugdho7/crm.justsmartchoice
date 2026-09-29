<?php defined('BASEPATH') or exit('No direct script access allowed');?>
			<?php echo form_open_multipart(admin_url('si_lead_followup/save_edit_schedule/'.$schedule['id']),array('id'=>'si_lead_followup_edit_schedule_form')); ?>	
				<div class="row" id="si_lead_followup_edit_send_wrapper" data-wait-text="<?php echo '<i class=\'fa fa-spinner fa-pulse\'></i> '._l('wait_text'); ?>" data-original-text="<?php echo _l('submit'); ?>">
					<div class="col-md-12 border-right">
						<?php echo render_input('name','si_lfs_name',$schedule['name'],'text',array('maxlength'=>100));?>
					</div>
					<div class="col-md-6 border-right">
						<?php echo render_select('status',$lead_statuses,array('id','name'),'lead_status',$schedule['status'],array('required'=>true)); ?>
					</div>
					<div class="col-md-6 border-right">
						<?php echo render_select('source',$lead_sources,array('id','name'),'lead_source',$schedule['source']); ?>
					</div>
					<div class="col-md-6">
						<?php echo render_input('schedule_days','si_lfs_schedule_days',$schedule['schedule_days'],'number',array('min'=>0,'max'=>100)); ?>
					</div>
					<div class="col-md-6">
						<?php echo render_input('schedule_hour','si_lfs_schedule_hour',$schedule['schedule_hour'],'number',array('data-toggle'=>'tooltip','data-title'=>_l('hour_of_day_perform_auto_operations').". "._l('hour_of_day_perform_auto_operations_format'),'min'=>0,'max'=>23)); ?>
					</div>
					<div class="col-md-12 mbot15">
						<label><?php echo _l('contract_send_to');?></label><br/>
						<div class="radio radio-inline radio-primary">
							<input type="radio" id="si_send_to_2" name="filter_by" value="lead" <?php if($schedule['filter_by']=='lead') echo 'checked';?> disabled>
							<label for="filter_by_2"><?php echo _l('leads'); ?></label>
						</div>
						<div class="radio radio-inline radio-primary">
							<input type="radio" id="si_send_to_3" name="filter_by" value="staff" <?php if($schedule['filter_by']=='staff') echo 'checked';?> disabled>
							<label for="filter_by_3" data-toggle="tooltip" data-title="<?php echo _l('si_lfs_staff_assigned');?>"><?php echo _l('staff_members'); ?></label>
						</div>
					</div>
					<div class="col-md-12">
						<hr/>
						<label><?php echo _l('send');?></label> 
						<div class="checkbox checkbox-inline checkbox-primary">
							<input type="checkbox" id="si_send_sms" name="send_sms" value="1" <?php if($schedule['send_sms']== 1) echo 'checked';?>>
							<label for="send_sms"><?php echo 'SMS'; ?></label>
						</div>
						<div class="checkbox checkbox-inline checkbox-primary">
							<input type="checkbox" id="si_send_email" name="send_email" value="1" <?php if($schedule['send_email']== 1) echo 'checked';?>>
							<label for="send_email"><?php echo _l('leads_dt_email'); ?></label>
						</div>
					</div>
					<div class="col-md-12">
						<a href="#" onclick="slideToggle('#si_lead_followup_edit_custom_merge_fields'); return false;" class="pull-right"><small><?php echo _l('available_merge_fields')?></small></a>
						<?php echo render_textarea('sms_content','si_lfs_text',nl2br($schedule['content']));?>
						<?php if($merge_fields != ''){?>
						<div id="si_lead_followup_edit_custom_merge_fields" class="hide mbot10">
							<?php echo ($merge_fields);?>
						</div>
						<?php }?>
						<div id="div_dlt_template">
							<?php 
							$trigger_name = SI_LEAD_FOLLOWUP_MODULE_NAME.'_custom_sms';
							$trigger_opts = [];
							hooks()->do_action('after_sms_trigger_textarea_content', ['name' => $trigger_name, 'options' => $trigger_opts]);?>
						</div>
					</div>
					<div class="col-md-12">
						<a href="#" onclick="slideToggle('#si_lead_followup_edit_custom_merge_fields'); return false;" class="pull-right"><small><?php echo _l('available_merge_fields')?></small></a>
						<?php echo render_input('email_subject','send_file_subject',$schedule['email_subject'],'text',array('maxlength'=>250));?>
						<?php echo render_textarea('edit_email_template_custom', 'email_template', $schedule['email_content'], [], [], '', 'tinymce-si-edit')?>
						<?php if($schedule['email_attachment_id'] > 0){?>
							<div class="row">
								<div class="col-md-12">
									<label><?php echo _l('si_lfs_attachment'); ?> </label>
								</div>
								<div class="col-md-12">
									<div class="col-md-10">
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
									<?php if($schedule['attachment_added_from'] == get_staff_user_id() || is_admin()){ ?>
									<div class="col-md-2 text-right">
										<a class="_delete text-danger" onclick="return delete_schedule_attachment('<?php echo $schedule['id']; ?>','<?php echo $schedule['email_attachment_id']; ?>');" href="#" data-toggle="tooltip" data-title="<?php echo _l('delete'); ?>"><i class="fa fa fa-times"></i></a>
									</div>
									<?php } ?>

								</div>
							</div>
						<?php }else{ ?>
							<span class="text-info pull-right">(<?php echo SI_LEAD_FOLLOWUP_ACCEPT_FILE_TYPES;?>)</span>
							<?php echo render_input('file','si_lfs_attachment','','file',array('accept'=>SI_LEAD_FOLLOWUP_ACCEPT_FILE_TYPES));?>
						<?php } ?>
					</div>
					
				</div>
			<?php echo form_close();?>