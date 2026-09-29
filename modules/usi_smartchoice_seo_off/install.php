<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php');
usi_smartchoice_seo_ensure_schema();

update_option('usi_smartchoice_seo_enabled', '1');
update_option('usi_smartchoice_ai_enabled', '1');
update_option('usi_smartchoice_ai_footer_chat_enabled', '1');
update_option('usi_smartchoice_ai_voice_rate', '0.92');
update_option('usi_smartchoice_ai_voice_pitch', '1.02');
