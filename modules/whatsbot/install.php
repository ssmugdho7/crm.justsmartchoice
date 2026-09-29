<?php

defined('BASEPATH') || exit('No direct script access allowed');

$CI = &get_instance();

get_instance()->config->load('whatsbot' . '/config');

add_option('wac_verify_token', app_generate_hash());

if (!$CI->db->table_exists(db_prefix() . 'wtc_bot')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_bot` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `reply_text` text NOT NULL,
            `reply_type` int NOT NULL,
            `trigger` text,
            `bot_header` varchar(65) DEFAULT NULL,
            `bot_footer` varchar(65) DEFAULT NULL,
            `button1` varchar(25) DEFAULT NULL,
            `button1_id` varchar(258) DEFAULT NULL,
            `button2` varchar(25) DEFAULT NULL,
            `button2_id` varchar(258) DEFAULT NULL,
            `button3` varchar(25) DEFAULT NULL,
            `button3_id` varchar(258) DEFAULT NULL,
            `button_name` varchar(25) DEFAULT NULL,
            `button_url` varchar(255) DEFAULT NULL,
            `addedfrom` int NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_bot_active` tinyint(1) NOT NULL DEFAULT "1",
            `sending_count` int DEFAULT "0",
            `filename` text DEFAULT NULL,
            `personal_assistants` int DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_templates')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_templates` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `template_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `template_name` VARCHAR(255) NOT NULL ,
            `language` VARCHAR(50) NOT NULL ,
            `status` VARCHAR(50) NOT NULL ,
            `category` VARCHAR(100) NOT NULL ,
            `header_data_format` VARCHAR(10) NOT NULL ,
            `header_data_text` TEXT ,
            `header_params_count` INT NOT NULL ,
            `body_data` TEXT NOT NULL ,
            `body_params_count` INT NOT NULL ,
            `footer_data` TEXT,
            `footer_params_count` INT NOT NULL ,
            `buttons_data` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`id`),
            UNIQUE KEY `template_id` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}


if (!$CI->db->table_exists('wtc_flows')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_flows` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `flow_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `flow_name` VARCHAR(255) NOT NULL ,
            `status` VARCHAR(50) NOT NULL ,
            `category` TEXT NOT NULL ,
            `preview_url` TEXT NULL DEFAULT NULL ,
            `preview_expiry` BIGINT NULL DEFAULT NULL ,
            `flow_json` MEDIUMTEXT NULL DEFAULT NULL ,
            `automation` MEDIUMTEXT NULL DEFAULT NULL ,
            PRIMARY KEY (`id`),
            UNIQUE KEY `flow_id` (`flow_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}


if (!$CI->db->table_exists('wtc_flows_response')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_flows_response` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `flow_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `receiver_id` varchar(20) NOT NULL,
            `name` varchar(100) NOT NULL,
            `response_data` text,
            `submit_time` timestamp NULL DEFAULT NULL,
            `wa_no` varchar(20) DEFAULT NULL,
            `wa_no_id` varchar(20) DEFAULT NULL,
            `type` varchar(500) DEFAULT NULL,
            `type_id` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_campaigns')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_campaigns` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `template_id` int DEFAULT NULL,
            `scheduled_send_time` timestamp NULL DEFAULT NULL,
            `send_now` tinyint NOT NULL DEFAULT "0",
            `header_params` text,
            `body_params` text,
            `footer_params` text,
            `filename` text DEFAULT NULL,
            `pause_campaign` tinyint(1) NOT NULL DEFAULT "0",
            `select_all` tinyint(1) NOT NULL DEFAULT "0",
            `trigger` text,
            `bot_type` int NOT NULL DEFAULT 0,
            `is_bot_active` int NOT NULL DEFAULT 1,
            `is_bot` int NOT NULL DEFAULT 0,
            `is_sent` tinyint(1) NOT NULL DEFAULT "0",
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
             `sending_count` int DEFAULT "0",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_campaign_data')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_campaign_data` (
            `id` int NOT NULL AUTO_INCREMENT,
            `campaign_id` int NOT NULL,
            `rel_id` int DEFAULT NULL,
            `rel_type` varchar(50) NOT NULL,
            `header_message` text DEFAULT NULL,
            `body_message` text DEFAULT NULL,
            `footer_message` text DEFAULT NULL,
            `status` int DEFAULT NULL,
            `response_message` TEXT NULL DEFAULT NULL,
            `whatsapp_id` TEXT NULL DEFAULT NULL,
            `message_status` varchar(25) NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_interactions')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_interactions` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `receiver_id` varchar(20) NOT NULL,
                `last_message` text,
                `last_msg_time` datetime DEFAULT NULL,
                `wa_no` varchar(20) DEFAULT NULL,
                `wa_no_id` varchar(20) DEFAULT NULL,
                `time_sent` datetime NOT NULL,
                `type` varchar(500) DEFAULT NULL,
                `type_id` varchar(500) DEFAULT NULL,
                `agent` text,
                `label` int DEFAULT NULL,
                `is_ai_chat` tinyint(1) NOT NULL DEFAULT "0",
                `ai_message_json` text,
                PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists('wtc_interaction_messages')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_interaction_messages` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
            `interaction_id` int unsigned NOT NULL,
            `sender_id` varchar(20) NOT NULL,
            `url` varchar(255) DEFAULT NULL,
            `message` longtext NOT NULL,
            `status` varchar(20) DEFAULT NULL,
            `time_sent` datetime NOT NULL,
            `message_id` varchar(500) DEFAULT NULL,
            `staff_id` varchar(500) DEFAULT NULL,
            `type` varchar(20) DEFAULT NULL,
            `is_read` tinyint(1) NOT NULL DEFAULT "0",
            `ref_message_id` text,
            `ref_msg_id` text,
            `is_ai_chat` tinyint(1) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id`),
            FOREIGN KEY (`interaction_id`) REFERENCES `' . db_prefix() . 'wtc_interactions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_activity_log')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number_id` varchar(255) NULL DEFAULT NULL,
            `access_token` TEXT NULL DEFAULT NULL,
            `business_account_id` varchar(255) NULL DEFAULT NULL,
            `response_code` varchar(4) NOT NULL,
            `response_data` text NOT NULL,
            `category` varchar(50) NOT NULL,
            `category_id` int(11) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `rel_id` int(11) NOT NULL,
            `category_params` longtext NOT NULL,
            `raw_data` TEXT NOT NULL,
            `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if ($CI->db->table_exists('wtc_bot')) {
    if (get_instance()->db->field_exists('trigger', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` CHANGE `trigger` `trigger` TEXT ;");
    }
}
if ($CI->db->table_exists('wtc_campaigns')) {
    if (get_instance()->db->field_exists('trigger', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` CHANGE `trigger` `trigger` TEXT ;");
    }
}
if ($CI->db->table_exists('wtc_interactions')) {
    if (!get_instance()->db->field_exists('agent', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `agent` TEXT NULL ;");
    }
}

$chatOptions = set_chat_header();
$content = (!empty($chatOptions['chat_header']) && !empty($chatOptions['chat_footer'])) ? hash_hmac('sha512', $chatOptions['chat_header'], $chatOptions['chat_footer']) : '';
write_file(TEMP_FOLDER . $chatOptions['chat_content'] . '.lic', $content);

// v1.3.0

if ($CI->db->table_exists('wtc_interaction_messages')) {
    if (!get_instance()->db->field_exists('ref_message_id', db_prefix() . 'wtc_interaction_messages')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `ref_message_id` TEXT NULL;");
    }
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_canned_reply')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_canned_reply` (
        `id` int NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `is_public` tinyint(1) NOT NULL DEFAULT "0",
        `added_from` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_ai_prompts')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_ai_prompts` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `action` text NOT NULL,
        `added_from` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_bot_flow')) {
    $CI->db->query(
        'CREATE TABLE `' . db_prefix() . 'wtc_bot_flow` (
            `id` int NOT NULL AUTO_INCREMENT,
            `flow_name` varchar(50) NOT NULL,
            `flow_data` longtext NOT NULL,
            `is_active` tinyint(1) DEFAULT "1",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';'
    );
}

// v1.3.2
if ($CI->db->table_exists('wtc_campaigns')) {
    if (!get_instance()->db->field_exists('rel_data', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `rel_data` TEXT NULL DEFAULT NULL;");
    }
}

if ($CI->db->table_exists('wtc_templates')) {
    if (get_instance()->db->field_exists('buttons_data', db_prefix() . 'wtc_templates')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_templates` CHANGE `buttons_data` `buttons_data` TEXT NOT NULL;");
    }
}

// v1.4.2
add_option('whatsapp_auto_lead_settings', 1, 0);
add_option('enable_wtc_notification_sound', 1, 0);

// v2.0.0
if (!$CI->db->table_exists(db_prefix() . 'wtc_personal_assistants')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'wtc_personal_assistants` (
        `id` int NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'wtc_pa_files')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'wtc_pa_files` (
        `id` int NOT NULL AUTO_INCREMENT,
        `pa_id` int NOT NULL,
        `file_name` text NOT NULL,
        `filetype` text NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_interactions')) {
    if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
    }
    if (!get_instance()->db->field_exists('ai_message_json', db_prefix() . 'wtc_interactions')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interactions` ADD `ai_message_json` TEXT NULL DEFAULT NULL ;");
    }
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_interaction_messages')) {
    if (!get_instance()->db->field_exists('is_ai_chat', db_prefix() . 'wtc_interaction_messages')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_interaction_messages` ADD `is_ai_chat` TINYINT(1) NOT NULL DEFAULT '0' ;");
    }
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
    if (!get_instance()->db->field_exists('personal_assistants', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `personal_assistants` INT NULL DEFAULT NULL;");
    }
}

add_option('pa_temperature', 0.5, 0);
add_option('pa_max_token', 200, 0);

// v2.1.0
if (get_instance()->db->table_exists(db_prefix() . 'wtc_campaigns')) {
    if (!get_instance()->db->field_exists('sender_phone', db_prefix() . 'wtc_campaigns')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_campaigns` ADD `sender_phone` VARCHAR(25) NULL DEFAULT NULL AFTER `name`;");
    }
}

if (get_instance()->db->table_exists(db_prefix() . 'wtc_bot')) {
    if (!get_instance()->db->field_exists('option', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `option` tinyint DEFAULT '1';");
    }
    if (!get_instance()->db->field_exists('sections', db_prefix() . 'wtc_bot')) {
        get_instance()->db->query("ALTER TABLE `" . db_prefix() . "wtc_bot` ADD `sections` mediumtext NULL DEFAULT NULL;");
    }
}

/* Add MY_ files at the time of module installation, If not exists */
$my_files_list = [
    VIEWPATH . 'themes/perfex/views/my_single_ticket.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/themes/perfex/views/my_single_ticket.php'),
    VIEWPATH . 'themes/perfex/template_parts/projects/project_flow_response.php' => module_dir_path(WHATSBOT_MODULE, '/resources/application/views/themes/perfex/template_parts/projects/project_flow_response.php'),

];

// Copy each file in $my_files_list to its actual path if it doesn't already exist
foreach ($my_files_list as $actual_path => $resource_path) {
    if (!file_exists($actual_path)) {
        copy($resource_path, $actual_path);
    }
}
