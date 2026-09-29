<?php
defined('BASEPATH') or exit('No direct script access allowed');
function sccc_do_uninstall(){
 $CI=&get_instance(); $p=db_prefix();
 foreach(['calls','campaigns','lists','list_contacts','extensions','greetings','callbacks','recordings','dispositions','ai_agents'] as $t){$CI->db->query("DROP TABLE IF EXISTS `{$p}sccc_{$t}`");}
 $opts=['sccc_twilio_account_sid','sccc_twilio_auth_token','sccc_twiml_app_sid','sccc_api_key_sid','sccc_api_key_secret','sccc_main_number','sccc_outbound_caller_id','sccc_default_country','sccc_default_timezone','sccc_record_calls','sccc_recording_announcement','sccc_main_greeting_id','sccc_after_hours_greeting_id','sccc_business_start','sccc_business_end','sccc_dialpad_color','sccc_dialpad_key_color','sccc_dialpad_text_color','sccc_enable_callbacks','sccc_callback_key','sccc_directory_key','sccc_operator_key','sccc_operator_extension','sccc_ai_enabled','sccc_ai_provider','sccc_ai_api_key','sccc_ai_websocket_url','sccc_max_campaign_cps','sccc_call_retention_days','sccc_allow_download_recordings'];
 foreach($opts as $o){delete_option($o);} 
 $dir=FCPATH.'uploads/smart_choice_call_center/'; if(is_dir($dir) && function_exists('delete_dir')) @delete_dir($dir);
}
