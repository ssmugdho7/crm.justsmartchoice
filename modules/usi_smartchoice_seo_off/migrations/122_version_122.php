<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_122 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php');
        usi_smartchoice_seo_ensure_schema();

        if (function_exists('add_option')) {
            add_option('usi_smartchoice_ai_api_provider', 'openai');
            add_option('usi_smartchoice_ai_api_key', '');
            add_option('usi_smartchoice_ai_voice_continuous', '1');
            add_option('usi_smartchoice_ai_voice_language', 'en-US');
            add_option('usi_smartchoice_ai_listen_seconds', '0');
            add_option('usi_smartchoice_ai_mobile_grid_columns', '4');
        }

        if (function_exists('update_option')) {
            update_option('usi_smartchoice_ai_voice_continuous', get_option('usi_smartchoice_ai_voice_continuous') === '0' ? '0' : '1');
            update_option('usi_smartchoice_ai_voice_language', get_option('usi_smartchoice_ai_voice_language') ?: 'en-US');
            update_option('usi_smartchoice_ai_listen_seconds', get_option('usi_smartchoice_ai_listen_seconds') ?: '0');
            update_option('usi_smartchoice_ai_mobile_grid_columns', get_option('usi_smartchoice_ai_mobile_grid_columns') ?: '4');
        }
    }

    public function down()
    {
    }
}
