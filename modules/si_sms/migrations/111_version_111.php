<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Define the module name if it's not already defined
if (!defined('SI_SMS_MODULE_NAME')) {
    define('SI_SMS_MODULE_NAME', 'si_sms'); // Replace 'si_sms' with the actual name of your module if different
}

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {   
        $option_name = SI_SMS_MODULE_NAME . '_skip_draft_status_when_create';
        
        // Check if the option already exists before adding it
        if (get_option($option_name) === false) {
            add_option($option_name, 1);
        }
    }
}
