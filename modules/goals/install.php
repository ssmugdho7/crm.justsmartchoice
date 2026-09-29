<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$table = db_prefix() . 'goals';

if (!$CI->db->table_exists($table)) {
    $CI->db->query('CREATE TABLE `' . $table . '` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `subject` varchar(191) NOT NULL,
      `description` text NOT NULL,
      `start_date` date NOT NULL,
      `end_date` date NOT NULL,
      `goal_type` int(11) NOT NULL,
      `contract_type` int(11) NOT NULL DEFAULT 0,
      `achievement` decimal(15,2) NOT NULL DEFAULT 0,
      `notify_when_fail` tinyint(1) NOT NULL DEFAULT 1,
      `notify_when_achieve` tinyint(1) NOT NULL DEFAULT 1,
      `notified` int(11) NOT NULL DEFAULT 0,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `priority` varchar(20) NOT NULL DEFAULT "medium",
      `department_id` int(11) NOT NULL DEFAULT 0,
      `goal_status` varchar(20) NOT NULL DEFAULT "active",
      `weight` decimal(8,2) NOT NULL DEFAULT 100.00,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `department_id` (`department_id`),
      KEY `goal_status` (`goal_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$fields = [
    'priority' => 'VARCHAR(20) NOT NULL DEFAULT "medium"',
    'department_id' => 'INT(11) NOT NULL DEFAULT 0',
    'goal_status' => 'VARCHAR(20) NOT NULL DEFAULT "active"',
    'weight' => 'DECIMAL(8,2) NOT NULL DEFAULT 100.00',
];
foreach ($fields as $field => $definition) {
    if (!$CI->db->field_exists($field, $table)) {
        $CI->db->query('ALTER TABLE `' . $table . '` ADD `' . $field . '` ' . $definition);
    }
}
update_option('goals_smart_choice_version', '2.5.1');
update_option('goals_smart_choice_enabled', 1);
