<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('zillapage_smart_choice_seed')) {
    function zillapage_smart_choice_seed($CI)
    {
        $settings_table = db_prefix() . 'landing_page_settings';
        if ($CI->db->table_exists($settings_table)) {
            $settings = [
                'sc_company_name' => 'Smart Choice Contractors USA',
                'sc_phone' => '727-755-3786',
                'sc_email' => 'admin@justsmartchoice.com',
                'sc_website_url' => 'https://justsmartchoice.com/',
                'sc_crm_portal_url' => 'https://crm.justsmartchoice.com/client',
                'sc_recruiting_url' => 'https://crm.justsmartchoice.com/recruitment/recruitment_portal',
                'sc_appointment_url' => 'https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2',
                'sc_logo_url' => 'https://justsmartchoice.com/images/logo-638x310.png',
                'sc_google_analytics_id' => 'G-CLX99EJXW2',
                'sc_google_tag_manager_id' => '',
                'sc_bing_tracking_id' => '',
                'sc_ahrefs_key' => '9gWoEoaoQSTW3cXLEcA/Qg',
                'sc_default_language' => 'english',
                'sc_enable_ai_chatbot' => '1',
                'sc_enable_website_header' => '1',
                'sc_enable_website_footer' => '1',
            ];
            foreach ($settings as $key => $value) {
                $row = $CI->db->where('key', $key)->get($settings_table)->row();
                if ($row) {
                    $CI->db->where('id', $row->id)->update($settings_table, ['value' => $value]);
                } else {
                    $CI->db->insert($settings_table, ['key' => $key, 'value' => $value]);
                }
            }
        }

        $templates_table = db_prefix() . 'landing_page_templates';
        $json_path = __DIR__ . '/smart_choice_templates.json';
        if ($CI->db->table_exists($templates_table) && file_exists($json_path)) {
            $templates = json_decode(file_get_contents($json_path), true);
            if (is_array($templates)) {
                foreach ($templates as $tpl) {
                    if (!isset($tpl['name'])) {
                        continue;
                    }
                    $data = [
                        'name' => $tpl['name'],
                        'thumb' => $tpl['thumb'] ?? null,
                        'thank_you_page' => $tpl['thank_you_page'] ?? '',
                        'content' => $tpl['content'] ?? '',
                        'style' => $tpl['style'] ?? '',
                        'active' => isset($tpl['active']) ? (int) $tpl['active'] : 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $row = $CI->db->where('name', $tpl['name'])->get($templates_table)->row();
                    if ($row) {
                        $CI->db->where('id', $row->id)->update($templates_table, $data);
                    } else {
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $CI->db->insert($templates_table, $data);
                    }
                }
            }
        }
    }
}
