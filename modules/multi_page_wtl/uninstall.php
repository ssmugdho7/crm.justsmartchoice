<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

if ($CI->db->table_exists(db_prefix() . 'mpwtl_media')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'mpwtl_media`');
}

foreach (['is_mpwtl','form_color','form_bg_color','form_text_color','form_theme','transition_effect','transition_duration','created_by'] as $field) {
    if ($CI->db->field_exists($field, db_prefix() . 'web_to_lead')) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . 'web_to_lead` DROP COLUMN `' . $field . '`');
    }
}
