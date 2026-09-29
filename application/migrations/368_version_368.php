<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_368 extends CI_Migration
{
    public function up()
    {
        $defaults = [
            'sc_ai_enabled' => '0',
            'sc_ai_provider' => 'openai',
            'sc_ai_api_key' => '',
            'sc_ai_model' => 'gpt-4.1-mini',
            'sc_ai_base_url' => '',
            'sc_ai_organization' => '',
        ];
        foreach ($defaults as $name => $value) {
            add_option($name, $value);
        }
        if ((string) get_option('sc_ai_api_key') === '') {
            $legacy = ['openai_api_key','ai_api_key','smart_choice_ai_api_key','chatgpt_api_key','gpt_api_key','open_ai_api_key','gemini_api_key','anthropic_api_key','openai_key','ai_openai_api_key'];
            foreach ($legacy as $name) {
                $value = trim((string) get_option($name));
                if ($value !== '') {
                    update_option('sc_ai_api_key', $value);
                    break;
                }
            }
        }
        update_option('smart_choice_crm_build', '3.6.8 SC');
        update_option('smart_choice_core_upgrade_applied', '368');
        update_option('smart_choice_ai_settings_restored', '1');
        update_option('smart_choice_florida_clock_enabled', '1');
        update_option('smart_choice_kb_portal_design_version', '368');
        update_option('smart_choice_invoice_footer_summary_version', '368');
    }
}
