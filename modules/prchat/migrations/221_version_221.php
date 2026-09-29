<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_221 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_staff_voice_call_mode') === false) add_option('prchat_staff_voice_call_mode', 'internal');
        if (get_option('prchat_twilio_voice_bridge_enabled') === false) add_option('prchat_twilio_voice_bridge_enabled', '1');
        update_option('prchat_build', '2.2.1');
    }
}
