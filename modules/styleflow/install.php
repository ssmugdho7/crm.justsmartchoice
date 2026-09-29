<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->helper('styleflow/styleflow');
styleflow_install_or_repair_schema();
styleflow_seed_templates();

add_option('styleflow_selected_invoice_template', 'default');
add_option('styleflow_selected_estimate_template', 'default');
add_option('styleflow_selected_proposal_template', 'default');
add_option('styleflow_version', STYLEFLOW_VERSION);
