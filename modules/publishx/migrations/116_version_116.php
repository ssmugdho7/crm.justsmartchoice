<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'publishx_default_author_name'   => 'Smart Choice Contractors USA',
            'publishx_default_cta_text'      => 'Schedule a Free Estimate Now',
            'publishx_primary_color'         => '#F28C28',
            'publishx_secondary_color'       => '#3598DB',
            'publishx_accent_color'          => '#0E6F5B',
            'publishx_success_color'         => '#169179',
            'publishx_card_radius'           => '14',
            'publishx_ai_provider'           => 'OpenAI',
            'publishx_ai_model'              => 'gpt-4.1-mini',
            'publishx_ai_base_url'           => 'https://api.openai.com/v1',
            'publishx_ai_organization'       => '',
            'publishx_ai_global_instructions'=> 'Write accurate, useful, conversion-focused construction content for Florida property owners. Do not invent licenses, prices, warranties, code requirements, or completed projects.',
            'publishx_ai_disallowed_topics'  => 'Unsupported guarantees, fabricated reviews, invented project claims, unverified legal or engineering conclusions.',
            'publishx_ai_required_facts'     => 'Use Smart Choice Contractors USA branding and preserve factual accuracy.',
            'publishx_ai_knowledge_folder'   => '',
            'publishx_default_target_folder' => 'services/engineering',
        ];
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        update_option('publishx_module_version', '1.1.6');
    }

    public function down()
    {
        // Existing settings and published content are preserved.
    }
}
