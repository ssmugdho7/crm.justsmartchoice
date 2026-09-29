<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_002 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'sc_ai_repair_crm_root' => rtrim(FCPATH, DIRECTORY_SEPARATOR),
            'sc_ai_repair_crm_url'  => 'https://crm.smartchoice.com',
            'sc_ai_repair_max_file_bytes' => '2097152',
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === null) {
                add_option($key, $value);
            }
        }
    }

    public function down()
    {
        // Upgrade-only migration. Preserve settings and repair history.
    }
}
