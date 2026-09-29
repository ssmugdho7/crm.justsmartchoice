<?php

defined('BASEPATH') or exit('No direct script access allowed');

define('PERFEX_POPUP_DEFAULT_DATA_PATH', 'modules/perfex_popup/default_data');

// create popups_templates table 
if (!$CI->db->table_exists(db_prefix() . 'popups_templates')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "popups_templates` (
      `id` bigint(20) UNSIGNED NOT NULL,
      `code` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
      `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `html` text COLLATE utf8mb4_unicode_ci,
      `css` text COLLATE utf8mb4_unicode_ci,
      `html_components` text COLLATE utf8mb4_unicode_ci,
      `css_styles` text COLLATE utf8mb4_unicode_ci,
      `thank_you_html` text COLLATE utf8mb4_unicode_ci,
      `thank_you_css` text COLLATE utf8mb4_unicode_ci,
      `thank_you_html_components` text COLLATE utf8mb4_unicode_ci,
      `thank_you_css_styles` text COLLATE utf8mb4_unicode_ci,
      `width` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '600',
      `height` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '300',
      `fields` text COLLATE utf8mb4_unicode_ci,
      `active` tinyint(1) NOT NULL DEFAULT '0',
      `is_premium` tinyint(1) NOT NULL DEFAULT '0',
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_templates`
      ADD PRIMARY KEY (`id`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_templates`
      MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;');

    // Default Data
    $CI->db->query('INSERT INTO `' . db_prefix() . 'popups_templates`'. file_get_contents(PERFEX_POPUP_DEFAULT_DATA_PATH.'/popups_templates.sql'));
    
    $upload_path = FCPATH.'uploads/perfex_popup';
    $default_template_path = FCPATH.'modules/perfex_popup/default_data/perfex_popup';
    if (!file_exists($upload_path))
    {
        perfex_recurse_copy($default_template_path, $upload_path);
    }
}
// create popups_popups table 
if (!$CI->db->table_exists(db_prefix() . 'popups_popups')) {

    $CI->db->query('CREATE TABLE `' . db_prefix() . "popups_popups` (
      `id` bigint(20) UNSIGNED NOT NULL,
      `code` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
      `template_id` bigint(20) DEFAULT NULL,
      `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
      `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `css` text COLLATE utf8mb4_unicode_ci,
      `html` text COLLATE utf8mb4_unicode_ci,
      `html_components` text COLLATE utf8mb4_unicode_ci,
      `css_styles` text COLLATE utf8mb4_unicode_ci,
      `main_page_script` longtext COLLATE utf8mb4_unicode_ci,
      `thank_you_css` text COLLATE utf8mb4_unicode_ci,
      `thank_you_html` text COLLATE utf8mb4_unicode_ci,
      `thank_you_html_components` text COLLATE utf8mb4_unicode_ci,
      `thank_you_css_styles` text COLLATE utf8mb4_unicode_ci,
      `width` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
      `height` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
      `type_form_submit` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'thank_you_page',
      `redirect_url` text COLLATE utf8mb4_unicode_ci,
      `settings` text COLLATE utf8mb4_unicode_ci,
      `last_action_date` timestamp NULL DEFAULT NULL,
      `popup_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
      `script` text COLLATE utf8mb4_unicode_ci,
      `style` text COLLATE utf8mb4_unicode_ci,
      `is_enabled` tinyint(4) NOT NULL DEFAULT '0',
      `notify_lead_imported` int(11) NOT NULL DEFAULT '1',
      `notify_type` varchar(20) DEFAULT 'assigned',
      `notify_ids` mediumtext,
      `responsible` int(11) NOT NULL DEFAULT '0',
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL
     
   ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_popups`
      ADD PRIMARY KEY (`id`);
    ');


    $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_popups`
      MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;');
}
// create popups_subscribers
if (!$CI->db->table_exists(db_prefix() . 'popups_subscribers')) {

  $CI->db->query('CREATE TABLE `' . db_prefix() . "popups_subscribers` (
    `id` bigint(20) UNSIGNED NOT NULL,
    `popup_id` bigint(20) NOT NULL,
    `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `url` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_subscribers`
    ADD PRIMARY KEY (`id`);
  ');

  $CI->db->query('ALTER TABLE `' . db_prefix() . 'popups_subscribers`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;');
}