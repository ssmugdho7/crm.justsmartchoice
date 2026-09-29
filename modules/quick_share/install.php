<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('qs_max_upload_size', '100000');
add_option('qs_allowed_file_extensions', '.docx,.php,.png,.jpg,.pdf,.php,.txt,.xlsx,.doc,.docx,.mp4,.mov,.php,.mp3,.zip');
add_option('qs_aws_key', '');
add_option('qs_aws_secret', '');
add_option('qs_aws_region', '');
add_option('qs_aws_bucket', '');
add_option('qs_storage_engine', 'server');

if (!$CI->db->table_exists(db_prefix() . 'quick_share_downloads')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "quick_share_downloads` (
  `id` int(11) NOT NULL,
  `download_id` int(11),
  `email` text,
  `ip` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_downloads`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'quick_share_uploads')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "quick_share_uploads` (
  `id` int(11) NOT NULL,
  `user_id` int(11),
  `file_path` text,
  `file_size` text,
  `file_key` text,
  `file_message` text,
  `send_emails_to` text,
  `auto_destroy` int(11) DEFAULT 0,
  `password` text,
  `ip` text,
  `share_type` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 0,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_uploads`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

$CI->db->query('ALTER TABLE `' . db_prefix() . 'quick_share_uploads` ADD `destination` INT DEFAULT 0 AFTER `ip`;');

