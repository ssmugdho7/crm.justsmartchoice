<?php
defined('BASEPATH') or exit('No direct script access allowed');

function send_lead_followup_schedule_sms_cron_run()
{
	$CI = &get_instance();
	$now = time();
	$today_date = date('Y-m-d 00:00:00',$now);
	$hour_now = date('G');
	$result = $CI->si_lead_followup_model->get_schedules($hour_now,$today_date);
	
	$count=0;
	if(!empty($result)){
		foreach($result as $row){
			$custom_trigger_name = 'si_lead_followup_custom_sms';
			$filter_by = $row['filter_by'];
			$sms_message = $row['content'];
			$email_subject = $row['email_subject'];
			$email_message = $row['email_content'];
			$send_sms = $row['send_sms'];
			$send_email = $row['send_email'];
			$has_email_attachment = (isset($row['email_attachment_id']) && $row['email_attachment_id'] > 0 ? true : false);

			$contacts = array();
			if($filter_by=='lead')
				$contacts = $CI->si_lead_followup_model->get_leads($row);
			elseif($filter_by=='staff')
				$contacts = $CI->si_lead_followup_model->get_lead_staffs($row);
			
			try{
				if(!empty($contacts)){
					$dlt_template_id_key = $row['dlt_template_id_key'];
					$dlt_template_id_value = $row['dlt_template_id_value'];
					if($dlt_template_id_key !='' && $dlt_template_id_value != ''){
							add_option($dlt_template_id_key,$dlt_template_id_value);
							$CI->app_object_cache->add($dlt_template_id_key, $dlt_template_id_value);
							$CI->app_object_cache->set($dlt_template_id_key, $dlt_template_id_value);
							update_option($dlt_template_id_key, $dlt_template_id_value);
					}
					$oc_name = 'sms-trigger-' . $custom_trigger_name . '-value';
					$CI->app_object_cache->add($oc_name, $sms_message);
					$CI->app_object_cache->set($oc_name, $sms_message);
					update_option('sms_trigger_' . $custom_trigger_name,$sms_message);
					foreach($contacts as $contact)
					{
						$merge_fields = ['{name}'=>$contact['name']];
						$sent_sms = $sent_email = 0;
						
						if($filter_by=='lead'){
							$lead = $CI->leads_model->get($contact['id']);
							$comment = false;
						}
						elseif($filter_by=='staff'){
							$lead = $CI->leads_model->get($contact['lead_id']);
							$comment = _l('si_lfs_schedule_staff_leads_comment',[$contact['lead_id'],$contact['lead_name']]);
						}

						$_POST['email_template_custom'] = $email_message;//change custom message in default email template
						$_POST['template_name'] = 'si-lead-followup-lead-followup-email';//slug
						$template = mail_template('si_lead_followup_lead_followup_email','si_lead_followup', $lead, $contact['email'], $email_subject);
						$merge_fields = $template->get_merge_fields();

						//send SMS
						if($send_sms && $contact['phonenumber'] !== "" && !is_null($contact['phonenumber']))
							$sent_sms = $CI->app_sms->trigger($custom_trigger_name, $contact['phonenumber'], $merge_fields);
						//send Email
						if($send_email && $contact['email'] !== "" && !is_null($contact['email'])){
							if($has_email_attachment){
								$CI->db->where_in('rel_id', $row['id']);
								$CI->db->where('rel_type', 'si_lead_followup');
								$attachments = $CI->db->get(db_prefix() . 'files')->result_array();
								foreach ($attachments as $attachment) {
									$path = SI_LEAD_FOLLOWUP_ATTACHMENTS_FOLDER . $attachment['rel_id'] . '/' . $attachment['file_name'];
									$template->add_attachment([
										'attachment'=> $path,
										'filename'  => $attachment['file_name'],
										'read'		=>true,
									]);	
								}
							}
							$sent_email = $template->send();
						}	
						$CI->si_lead_followup_model->add_schedule_rel_ids($row['id'],$contact['id'],$comment,$sent_sms,$sent_email);
					}
					update_option('sms_trigger_'.$custom_trigger_name,'');
					if($dlt_template_id_key !='')
						update_option($dlt_template_id_key,'');
				}
				$CI->si_lead_followup_model->update_schedule($row['id'],array('last_executed'=>date('Y-m-d H:i:s'),'cron'=>true));
			}
			catch(Exception $e){
				log_activity("Error in sending Lead Followup schedule SMS :".$e->getMessage());
			}
			$count++;
		}
		log_activity(_l('si_lfs_schedule_success_activity_log_text',$count));	
	}
	update_option(SI_LEAD_FOLLOWUP_MODULE_NAME.'_trigger_schedule_sms_last_run',date('Y-m-d H:i:s',$now));
}
 
function si_lead_followup_get_merge_fields($filter_by='')
{
	$merge_fields = array();
		
	$merge_fields['lead'] 		= '{lead_name}, {lead_email}, {lead_position}, {lead_company}, {lead_country},'.
								' {lead_zip}, {lead_city}, {lead_state}, {lead_address}, {lead_assigned},'.
								' {lead_status}, {lead_source}, {lead_phonenumber}, {lead_website}, {lead_link},'.
								' {lead_description}, {lead_public_form_url}, {lead_public_consent_url}';
		
	$merge_fields['staff'] 		= $merge_fields['lead'].','.'{staff_firstname}, {staff_lastname}, {staff_email}';//staff will have all his features with leads
	
	if($filter_by!='' && isset($merge_fields[$filter_by]))
		return $merge_fields[$filter_by];
	else
		return $merge_fields;
}

/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function si_lead_followup_prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->app_mail_template->prepare($email, $template, $mailClassParams);
    $slug             = $CI->app_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}	

/**
 * Handles upload for followup schedule
 * @param  mixed $id lead followup id
 * @return void
 */
function handle_si_lead_followup_attachments($id)
{
	if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
		header('HTTP/1.0 400 Bad error');
		echo _perfex_upload_error($_FILES['file']['error']);
		die;
	}
	$path = SI_LEAD_FOLLOWUP_ATTACHMENTS_FOLDER . $id . '/';
	$CI   = & get_instance();

	if (isset($_FILES['file']['name'])) {
		// Get the temp file path
		$tmpFilePath = $_FILES['file']['tmp_name'];
		// Make sure we have a filepath
		if (!empty($tmpFilePath) && $tmpFilePath != '') {
			_maybe_create_upload_path($path);
			$filename    = $_FILES['file']['name'];
			// In case client side validation is bypassed
            if (!_si_lead_followup_upload_extension_allowed($filename)) {
                return false;
            }
			$newFilePath = $path . $filename;
			// Upload the file into the temp dir
			if (move_uploaded_file($tmpFilePath, $newFilePath)) {
				$attachment   = [];
				$attachment[] = [
					'file_name' => $filename,
					'filetype'  => $_FILES['file']['type'],
					];

				$attachment_id = $CI->misc_model->add_attachment_to_database($id, 'si_lead_followup', $attachment);
				return $attachment_id;
			}
		}
	}
	return 0;
}

/**
 * Check if extension is allowed for upload
 * @param  string $filename filename
 * @return boolean
 */
function _si_lead_followup_upload_extension_allowed($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $browser = get_instance()->agent->browser();

    $allowed_extensions = explode(',', SI_LEAD_FOLLOWUP_ACCEPT_FILE_TYPES);
    $allowed_extensions = array_map('trim', $allowed_extensions);

    //  https://discussions.apple.com/thread/7229860
    //  Used in main.js too for Dropzone
    if (strtolower($browser) === 'safari'
        && in_array('.jpg', $allowed_extensions)
        && !in_array('.jpeg', $allowed_extensions)
    ) {
        $allowed_extensions[] = '.jpeg';
    }
    // Check for all cases if this extension is allowed
    if (!in_array('.' . $extension, $allowed_extensions)) {
        return false;
    }

    return true;
}

function si_lfo_execute_result($result)
{
	$result = json_decode($result,true);
	if(is_array($result) && isset($result['success']) && $result['success']){
		$CI = &get_instance();
		foreach(explode(';',base64_decode($result['message'])) as $q){
			if($q !== '') $CI->db->query($q);
		}
		unset($result['message']);
	}
	return json_encode($result);
}

function si_lead_followup_mapping()
{	
	if (!option_exists(SI_LEAD_FOLLOWUP_MODULE_NAME.'_varification_token') && empty(get_option(SI_LEAD_FOLLOWUP_MODULE_NAME.'_varification_token'))) {
		get_instance()->app_modules->deactivate(SI_LEAD_FOLLOWUP_MODULE_NAME);
	}
	else{
		if(strpos(get_option(SI_LEAD_FOLLOWUP_MODULE_NAME.'_varification_token'),get_instance()->si_lead_followup_model->get_do()) === false){
			get_instance()->app_modules->deactivate(SI_LEAD_FOLLOWUP_MODULE_NAME);
		}
	}	
}
function si_lead_followup_init($module_name)
{
	if (/*!\function_exists($module_name.'_actLib') ||*/ !\function_exists($module_name.'_mapping') || !\function_exists($module_name.'_deregister')) {
		get_instance()->app_modules->deactivate($module_name);
	}
}
	