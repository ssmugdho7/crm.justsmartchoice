<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('predix_openai_secret_key', '');
add_option('predix_text_limit', '100');
add_option('predix_chat_model', 'gpt-3.5-turbo');
add_option('predix_audio_transcription_model', 'whisper-1');
add_option('predix_audio_transcription_max_size', '100000');
add_option('predix_audio_transcription_allowed_extensions', '.mp3,.m4a');
add_option('predix_audio_translation_model', 'whisper-1');
add_option('predix_audio_translation_max_size', '100000');
add_option('predix_audio_translation_allowed_extensions', '.mp3,.m4a');
add_option('predix_image_generator_maximum_images_generate', '1');
add_option('predix_image_generator_allowed_image_sizes', '256x256,512x512,1024x1024');

if (!$CI->db->table_exists(db_prefix() . 'predix_chat')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "predix_chat` (
  `id` int(11) NOT NULL,
  `user_id` int(11),
  `human_message` text,
  `ai_response` text, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_chat`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'predix_images')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "predix_images` (
  `id` int(11) NOT NULL,
  `user_id` int(11),
  `image_url` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_images`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'predix_translated_audio')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "predix_translated_audio` (
  `id` int(11) NOT NULL,
  `user_id` int(11),
  `audio_file_path` text,
  `translated_text` text,
  `audio_file_size` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_translated_audio`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_translated_audio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'predix_audio_transcription')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "predix_audio_transcription` (
  `id` int(11) NOT NULL,
  `user_id` int(11),
  `audio_file_path` text,
  `transcription_text` text,
  `audio_file_size` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_audio_transcription`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'predix_audio_transcription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
