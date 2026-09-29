<?php

$CI = &get_instance();
$CI->db->query('SET foreign_key_checks = 0;');
$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'import_tasks_history`');
$CI->db->query('SET foreign_key_checks = 1;');
