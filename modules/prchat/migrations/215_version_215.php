<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_215 extends App_module_migration
{
    public function up()
    {
        if (get_option('prchat_ai_improve_enabled') === '') {
            add_option('prchat_ai_improve_enabled', '1');
        }
        if (get_option('prchat_voice_to_text_enabled') === '') {
            add_option('prchat_voice_to_text_enabled', '1');
        }
        update_option('prchat_version', '2.1.5');
    }

    public function down()
    {
        // Preserve production configuration and data.
    }
}
