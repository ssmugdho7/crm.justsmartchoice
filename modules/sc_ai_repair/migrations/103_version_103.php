<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'sc_ai_repair_base_url' => 'https://api.openai.com/v1',
            'sc_ai_repair_model' => 'gpt-5-mini',
            'sc_ai_repair_voice_language' => 'en-US',
            'sc_ai_repair_ai_prompt_enabled' => '1',
        ];
        foreach ($defaults as $name => $value) {
            if (get_option($name) === null || trim((string) get_option($name)) === '') {
                add_option($name, $value);
                update_option($name, $value);
            }
        }
    }

    public function down()
    {
        // Upgrade only. Preserve settings and repair history.
    }
}
