<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_156 extends App_module_migration
{
    public function up(): void
    {
        update_option('usi_smartchoice_seo_enabled', '1');
        update_option('usi_smartchoice_ai_enabled', '1');
        update_option('usi_smartchoice_ai_footer_chat_enabled', '1');
        if (get_option('usi_smartchoice_ai_voice_rate') === '') { update_option('usi_smartchoice_ai_voice_rate', '0.92'); }
        if (get_option('usi_smartchoice_ai_voice_pitch') === '') { update_option('usi_smartchoice_ai_voice_pitch', '1.02'); }
    }
    public function down(): void {}
}
