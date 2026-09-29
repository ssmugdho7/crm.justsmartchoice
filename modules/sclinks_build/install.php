<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!function_exists('smart_choice_links_ensure_database')) {
    require_once(__DIR__ . '/smart_choice_links.php');
}

smart_choice_links_ensure_database();
