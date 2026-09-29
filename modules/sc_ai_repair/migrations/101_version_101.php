<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'sc_ai_repair_provider'         => 'openai',
            'sc_ai_repair_model'            => 'gpt-5.2',
            'sc_ai_repair_base_url'         => 'https://api.openai.com/v1',
            'sc_ai_repair_crm_url'          => 'https://crm.smartchoice.com',
            'sc_ai_repair_crm_root'         => rtrim(FCPATH, DIRECTORY_SEPARATOR),
            'sc_ai_repair_organization'     => '',
            'sc_ai_repair_api_key'          => '',
            'sc_ai_repair_auto_backup'      => '1',
            'sc_ai_repair_require_approval' => '1',
            'sc_ai_repair_max_file_bytes'   => '2097152',
            'sc_ai_repair_scan_vendor'      => '0',
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === null) {
                add_option($key, $value);
            }
        }
    }

    public function down()
    {
        // Upgrade only. Preserve settings.
    }
}
