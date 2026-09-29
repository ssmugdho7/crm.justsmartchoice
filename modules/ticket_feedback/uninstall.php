<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->db->query("DROP TABLE IF EXISTS `tblticket_feedback`");
