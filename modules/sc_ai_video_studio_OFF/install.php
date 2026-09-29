<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once module_dir_path('sc_ai_video_studio', 'helpers/sc_ai_video_studio_helper.php');
sc_ai_video_studio_ensure_schema();
sc_ai_video_studio_seed_defaults();
