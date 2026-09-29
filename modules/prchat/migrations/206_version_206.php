<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_206 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $widgetKey = '44ed6803c32d9b16cee9e825b9e0ecaf';
        $now = date('Y-m-d H:i:s');

        update_option('prchat_version', '2.0.6');
        update_option('chatbot_openai_api_key_option_support', '1');
        update_option('prchat_public_widget_key', $widgetKey);
        update_option('prchat_public_widget_domain', 'justsmartchoice.com');
        update_option('chat_client_enabled', '1');
        update_option('pusher_chat_enabled', get_option('pusher_chat_enabled') ?: '1');

        if ($CI->db->table_exists($prefix . 'chatbots')) {
            $appearance = [
                'primary_color' => '#1f8f4d',
                'header_bg_color' => '#1f8f4d',
                'position' => 'bottom-right',
                'distance_from_bottom' => 20,
                'distance_from_side' => 20,
                'icon_type' => 'chat',
                'custom_icon_url' => null,
                'welcome_message' => 'Hi, this is Smart Choice Contractors USA. How can we help you with your project today?',
                'input_placeholder' => 'Type your message or request an appointment...',
                'intro_subtitle' => 'Construction, remodeling, estimates, appointments, and project support',
                'display_name_mode' => 'chatbot_only',
                'agent_avatar_url' => null,
                'header_image_url' => null,
                'widget_language' => 'english',
                'proactive_enabled' => true,
                'proactive_delay_seconds' => 4,
            ];
            $leadFields = [
                'email' => ['enabled' => true, 'required' => true, 'label' => 'Email'],
                'name'  => ['enabled' => true, 'required' => true, 'label' => 'Name'],
                'phone' => ['enabled' => true, 'required' => false, 'label' => 'Phone'],
            ];
            $allowedDomains = ['justsmartchoice.com', 'www.justsmartchoice.com', 'crm.justsmartchoice.com'];
            $row = $CI->db->where('widget_key', $widgetKey)->get($prefix . 'chatbots')->row();
            $data = [
                'name' => 'Smart Choice AI Assistant',
                'widget_key' => $widgetKey,
                'enabled' => 1,
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-4o-mini',
                'system_prompt' => 'You are the Smart Choice Contractors USA AI assistant. Help website visitors with construction, remodeling, estimates, service questions, appointments, lead intake, and project support. Be professional, concise, and collect name, phone, email, project address, service needed, and preferred appointment time when relevant.',
                'max_output_tokens' => 700,
                'temperature' => 0.55,
                'appearance' => json_encode($appearance),
                'allowed_domains' => json_encode($allowedDomains),
                'max_messages' => 30,
                'context_window' => 12,
                'auto_close_timeout' => 30,
                'capture_leads' => 1,
                'lead_fields' => json_encode($leadFields),
                'lead_custom_fields' => null,
                'lead_capture_success_message' => 'Thank you. Smart Choice Contractors USA received your request and will follow up shortly.',
                'escalation_enabled' => 1,
                'escalation_message' => 'I can connect this request with our office team for follow-up.',
                'csat_enabled' => 1,
                'updated_at' => $now,
            ];
            if ($row) {
                $CI->db->where('id', $row->id)->update($prefix . 'chatbots', $data);
            } else {
                $data['created_at'] = $now;
                $CI->db->insert($prefix . 'chatbots', $data);
            }
        }
    }
}
