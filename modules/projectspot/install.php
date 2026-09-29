<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('projectspot_show_menu_client_side', '1');
add_option('projectspot_should_client_be_logged_in', '1');

if (!$CI->db->table_exists(db_prefix() . 'projectspot_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "projectspot_categories` (
  `id` int(11) NOT NULL,
  `category_name` text,
  `category_description` text,
  `is_enabled` int default 0, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_categories`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'projectspot_gallery_list')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "projectspot_gallery_list` (
  `id` int(11) NOT NULL,
  `category_id` int(11),
  `project_name` text,
  `project_description` text,
  `is_enabled` int default 0,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_gallery_list`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_gallery_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'projectspot_gallery_list_images')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "projectspot_gallery_list_images` (
  `id` int(11) NOT NULL,
  `project_id` int(11),
  `image_url` text,
  `image_order` int default 0,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_gallery_list_images`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projectspot_gallery_list_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
