<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$fields = [
    'is_mpwtl'            => "INT(1) NOT NULL DEFAULT 0",
    'form_color'          => "VARCHAR(15) DEFAULT '#17a2b8'",
    'form_bg_color'       => "VARCHAR(15) DEFAULT '#ffffff'",
    'form_text_color'     => "VARCHAR(15) DEFAULT '#212529'",
    'form_theme'          => "VARCHAR(30) DEFAULT 'elegant'",
    'transition_effect'   => "VARCHAR(30) DEFAULT 'slide_fade'",
    'transition_duration' => "INT(6) NOT NULL DEFAULT 450",
    'created_by'          => "INT(11) NOT NULL DEFAULT 0",
];

foreach ($fields as $name => $definition) {
    if (!$CI->db->field_exists($name, db_prefix() . 'web_to_lead')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'web_to_lead` ADD `' . $name . '` ' . $definition);
    }
}

$mediaTable = db_prefix() . 'mpwtl_media';
if (!$CI->db->table_exists($mediaTable)) {
    $CI->db->query("CREATE TABLE `{$mediaTable}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `form_id` INT(11) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `original_name` VARCHAR(255) NOT NULL,
        `mime_type` VARCHAR(120) DEFAULT NULL,
        `file_type` VARCHAR(20) NOT NULL DEFAULT 'file',
        `addedfrom` INT(11) NOT NULL DEFAULT 0,
        `dateadded` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `form_id` (`form_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
