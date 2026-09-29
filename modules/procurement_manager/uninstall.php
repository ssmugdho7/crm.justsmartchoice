<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

// Drop tables on uninstall
$CI->dbforge->drop_table(db_prefix() . 'procurement_items', true);
$CI->dbforge->drop_table(db_prefix() . 'procurement_suppliers', true);
