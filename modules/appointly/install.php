<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$CI->load->helper([APPOINTLY_MODULE_NAME . '/appointly_database']);

init_appointly_install_sequence();
