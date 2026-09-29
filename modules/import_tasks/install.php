<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();
if (!$CI->db->table_exists(db_prefix() . 'import_tasks_history')) {
  $CI->db->query('CREATE TABLE `'. db_prefix() .'import_tasks_history` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL,
  `tasks_count` INT(10) NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`));
');
}