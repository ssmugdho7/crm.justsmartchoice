<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

add_option('staff_members_create_inline_diagramy_group', 1);

$table  = db_prefix() . 'diagramy';
$groups = db_prefix() . 'diagramy_groups';
$charset = $CI->db->char_set ?: 'utf8mb4';

if (!$CI->db->table_exists($groups)) {
    $CI->db->query("CREATE TABLE `{$groups}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `description` text NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset};");
}

if (!$CI->db->table_exists($table)) {
    $CI->db->query("CREATE TABLE `{$table}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) DEFAULT NULL,
        `description` text NULL,
        `related_to` varchar(255) NOT NULL DEFAULT '',
        `rel_id` int(11) NOT NULL DEFAULT 0,
        `staffid` int(11) NOT NULL DEFAULT 0,
        `diagramy_group_id` int(11) NOT NULL DEFAULT 0,
        `diagramy_content` LONGTEXT NULL,
        `diagramy_xml` LONGTEXT NULL,
        `diagramy_slug` varchar(255) DEFAULT NULL,
        `dateadded` datetime DEFAULT NULL,
        `dateaupdated` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `staffid` (`staffid`),
        KEY `diagramy_group_id` (`diagramy_group_id`),
        KEY `rel_lookup` (`related_to`,`rel_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset};");
} else {
    $fields = [
        'title' => "ALTER TABLE `{$table}` ADD `title` varchar(255) DEFAULT NULL AFTER `id`",
        'description' => "ALTER TABLE `{$table}` ADD `description` text NULL AFTER `title`",
        'related_to' => "ALTER TABLE `{$table}` ADD `related_to` varchar(255) NOT NULL DEFAULT '' AFTER `description`",
        'rel_id' => "ALTER TABLE `{$table}` ADD `rel_id` int(11) NOT NULL DEFAULT 0 AFTER `related_to`",
        'staffid' => "ALTER TABLE `{$table}` ADD `staffid` int(11) NOT NULL DEFAULT 0 AFTER `rel_id`",
        'diagramy_group_id' => "ALTER TABLE `{$table}` ADD `diagramy_group_id` int(11) NOT NULL DEFAULT 0 AFTER `staffid`",
        'diagramy_content' => "ALTER TABLE `{$table}` ADD `diagramy_content` LONGTEXT NULL AFTER `diagramy_group_id`",
        'diagramy_xml' => "ALTER TABLE `{$table}` ADD `diagramy_xml` LONGTEXT NULL AFTER `diagramy_content`",
        'diagramy_slug' => "ALTER TABLE `{$table}` ADD `diagramy_slug` varchar(255) DEFAULT NULL AFTER `diagramy_xml`",
        'dateadded' => "ALTER TABLE `{$table}` ADD `dateadded` datetime DEFAULT NULL AFTER `diagramy_slug`",
        'dateaupdated' => "ALTER TABLE `{$table}` ADD `dateaupdated` datetime DEFAULT NULL AFTER `dateadded`",
    ];
    foreach ($fields as $field => $sql) {
        if (!$CI->db->field_exists($field, $table)) {
            $CI->db->query($sql);
        }
    }
    if ($CI->db->field_exists('diagramy_content', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` MODIFY `diagramy_content` LONGTEXT NULL");
    }
    if ($CI->db->field_exists('diagramy_xml', $table)) {
        $CI->db->query("ALTER TABLE `{$table}` MODIFY `diagramy_xml` LONGTEXT NULL");
    }
}

// Add indexes only if missing. This avoids MySQL duplicate key and migration failures.
$indexes = [];
foreach ($CI->db->query("SHOW INDEX FROM `{$table}`")->result_array() as $idx) {
    $indexes[$idx['Key_name']] = true;
}
if (!isset($indexes['staffid']) && $CI->db->field_exists('staffid', $table)) {
    $CI->db->query("ALTER TABLE `{$table}` ADD KEY `staffid` (`staffid`)");
}
if (!isset($indexes['diagramy_group_id']) && $CI->db->field_exists('diagramy_group_id', $table)) {
    $CI->db->query("ALTER TABLE `{$table}` ADD KEY `diagramy_group_id` (`diagramy_group_id`)");
}
if (!isset($indexes['rel_lookup']) && $CI->db->field_exists('related_to', $table) && $CI->db->field_exists('rel_id', $table)) {
    $CI->db->query("ALTER TABLE `{$table}` ADD KEY `rel_lookup` (`related_to`,`rel_id`)");
}

// Never require external activation for this internal Smart Choice Contractors build.
update_option('diagramy_verification_id', 'smartchoice-internal');
update_option('diagramy_last_verification', time());
update_option('diagramy_product_token', 'smartchoice-internal');
delete_option('diagramy_heartbeat');
