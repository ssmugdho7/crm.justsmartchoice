<?php


defined('BASEPATH') or exit('No direct script access allowed');

$CI = & get_instance();

if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_templates' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_templates` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `template_name` varchar(150) DEFAULT NULL,
                      `template_content` mediumtext DEFAULT NULL,
                      `template_subject` varchar(255) DEFAULT NULL,
                      `status` tinyint(4) DEFAULT 1,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}



if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_timer' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_timer` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `setting_name` varchar(255) DEFAULT NULL,
                      `template_id` int(11) DEFAULT NULL,
                      `client_groups` varchar(100) DEFAULT NULL,
                      `leads` varchar(255) DEFAULT NULL,
                      `clients` varchar(255) DEFAULT NULL,
                      `sending_date` date DEFAULT NULL,
                      `status` tinyint(4) DEFAULT 1,
                      `send_status` tinyint(4) DEFAULT 0,
                      `sending_hour` tinyint(4) DEFAULT 0,
                      `not_clients` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `template_id` (`template_id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}



if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_sending_logs' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_sending_logs` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `mail_id` int(11) DEFAULT NULL,
                      `client_id` int(11) DEFAULT NULL,
                      `lead_id` int(11) DEFAULT NULL,
                      `status` tinyint(4) DEFAULT 0,
                      `error_message` varchar(500) DEFAULT NULL,
                      `date` datetime DEFAULT NULL,
                      `mail_address` varchar(255) DEFAULT NULL,
                      `mail_company` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `mail_id` (`mail_id`),
                      KEY `client_id` (`client_id`),
                      KEY `lead_id` (`lead_id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}




/**
 * Version 1.0.2
 */

if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_mail_logs' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_mail_logs` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `rel_type` varchar(50) DEFAULT NULL,
                      `rel_id` int(11) DEFAULT NULL,
                      `template_id` int(11) DEFAULT NULL,
                      `company_name` varchar(150) DEFAULT NULL,
                      `company_email` varchar(250) DEFAULT NULL,
                      `company_cc` varchar(250) DEFAULT NULL,
                      `error_message` text DEFAULT NULL,
                      `mail_subject` varchar(255) DEFAULT NULL,
                      `content` text DEFAULT NULL,
                      `date` datetime DEFAULT NULL,
                      `status` tinyint(4) DEFAULT 1,
                      PRIMARY KEY (`id`),
                      KEY `rel_type` (`rel_type`),
                      KEY `rel_id` (`rel_id`),
                      KEY `template_id` (`template_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


if( !$CI->db->field_exists('opened', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `opened` tinyint NULL DEFAULT 0 AFTER `status`,
                                ADD COLUMN `date_opened` datetime NULL AFTER `opened`;');


}



if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_mail_attachments' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_mail_attachments` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `mail_id` int(11) DEFAULT NULL,
                      `file_name` varchar(255) DEFAULT NULL,
                      `file_type` varchar(255) DEFAULT NULL,
                      `file_path` varchar(300) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `mail_id` (`mail_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


/**
 *
 * @version System mail templates
 *
 */

if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_system_templates' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_system_templates` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `template_slug` varchar(100) DEFAULT NULL,
                      `status` tinyint(1) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


if( !$CI->db->field_exists('system_template_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `system_template_id` varchar(100) NULL AFTER `date_opened`,
                                ADD COLUMN `system_template_slug` varchar(100) NULL AFTER `system_template_id`;');

}


/**
 *
 * @version 1.1.1 Triggers
 *
 */

if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_triggers' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_triggers` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `trigger_name` varchar(255) DEFAULT NULL,
                    `template_id` int(11) DEFAULT NULL,
                    `rel_type` varchar(20) DEFAULT NULL,
                    `options` varchar(255) DEFAULT NULL,
                    `staff_active` tinyint(4) DEFAULT 0,
                    `client_active` tinyint(4) DEFAULT 0,
                    `status` tinyint(4) DEFAULT 1,
                    `sending_hour` tinyint(4) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `template_id` (`template_id`),
                    KEY `rel_type` (`rel_type`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_trigger_logs' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_trigger_logs` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `trigger_id` int(11) DEFAULT NULL,
                      `trigger_rel_id` int(11) DEFAULT NULL,
                      `send_rel_type` varchar(25) DEFAULT NULL,
                      `send_rel_id` int(11) DEFAULT 0,
                      `mail_id` int(11) DEFAULT NULL,
                      `date` datetime DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `trigger_id` (`trigger_id`),
                      KEY `send_rel_type` (`send_rel_type`),
                      KEY `send_rel_id` (`send_rel_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


if( !$CI->db->field_exists('trigger_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                               ADD COLUMN `trigger_id` int NULL DEFAULT 0 AFTER `system_template_slug`, ADD INDEX(`trigger_id`);');

}


if( !$CI->db->field_exists('send_rel_type', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `send_rel_type` varchar(50) NULL AFTER `trigger_id`,
                                ADD COLUMN `send_rel_id` int NULL AFTER `send_rel_type`;');

}


if( !$CI->db->field_exists('lead_statuses', db_prefix() .'email_template_manage_timer') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_timer`
                                MODIFY COLUMN `client_groups` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `template_id`,
                                ADD COLUMN `lead_statuses` varchar(150) NULL AFTER `client_groups`,
                                ADD COLUMN `lead_sources` varchar(150) NULL AFTER `lead_statuses`;');

}


/**
 * @version 1.1.2
 */
if( !$CI->db->field_exists('added_staff_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `added_staff_id` int NULL DEFAULT 0 AFTER `date`');

}


if( !$CI->db->field_exists('added_staff_id', db_prefix() .'email_template_manage_templates') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_templates`
                                ADD COLUMN `date` datetime NULL AFTER `status`,
                                ADD COLUMN `added_staff_id` int NULL DEFAULT 0 AFTER `date`,
                                ADD COLUMN `is_public` tinyint NULL DEFAULT 1 AFTER `added_staff_id`;');

}

/**
 * @version 1.1.4
 */

if( !$CI->db->field_exists('related_type', db_prefix() .'email_template_manage_templates') )
{

    $CI->db->query("ALTER TABLE `".db_prefix()."email_template_manage_templates`
                                ADD COLUMN `related_type` varchar(30) NULL DEFAULT 'all' AFTER `template_subject`;");

}




/**
 * @Version 1.1.5
 * New Smtp Settings
 */
if ( !$CI->db->table_exists( db_prefix() . 'email_template_smtp_settings' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_smtp_settings` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `company_name` varchar(200) DEFAULT NULL,
                      `mail_engine` varchar(30) DEFAULT NULL,
                      `email_protocol` varchar(10) DEFAULT NULL,
                      `smtp_encryption` varchar(10) DEFAULT NULL,
                      `smtp_host` varchar(150) DEFAULT NULL,
                      `smtp_port` varchar(20) DEFAULT NULL,
                      `smtp_email` varchar(150) DEFAULT NULL,
                      `smtp_username` varchar(150) DEFAULT NULL,
                      `smtp_password` varchar(100) DEFAULT NULL,
                      `smtp_email_charset` varchar(20) DEFAULT NULL,
                      `is_public` tinyint(4) DEFAULT 1,
                      `status` tinyint(4) DEFAULT 1,
                      `active_staff` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}



if( !$CI->db->field_exists('smtp_setting_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `smtp_setting_id` int NULL DEFAULT 0 AFTER `send_rel_id`;');

}


/**
 * @Version 1.2.1
 * Webhook
 */

if( !$CI->db->field_exists('webhook_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `webhook_id` int NULL DEFAULT 0 AFTER `smtp_setting_id`,
                                ADD INDEX(`smtp_setting_id`),
                                ADD INDEX(`webhook_id`);');

}



if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_webhooks' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_manage_webhooks` (
                       `id` int(11) NOT NULL AUTO_INCREMENT,
                      `webhook_name` varchar(255) DEFAULT NULL,
                      `webhook_trigger` varchar(255) DEFAULT NULL,
                      `template_id` int(11) DEFAULT NULL,
                      `staff_active` tinyint(4) DEFAULT 0,
                      `client_active` tinyint(4) DEFAULT 0,
                      `options` varchar(255) DEFAULT NULL,
                      `status` tinyint(4) DEFAULT 1,
                      PRIMARY KEY (`id`),
                      KEY `template_id` (`template_id`),
                      KEY `webhook_trigger` (`webhook_trigger`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}


/**
 * @Version  1.2.2
 */
if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_special' ) )
{

    $CI->db->query("CREATE TABLE `".db_prefix()."email_template_manage_special` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `special_name` varchar(255) DEFAULT NULL,
                                  `template_id` int(11) DEFAULT NULL,
                                  `rel_type` varchar(20) DEFAULT NULL,
                                  `date_field_name` varchar(100) DEFAULT NULL,
                                  `is_custom_field` tinyint(4) DEFAULT 0,
                                  `status` tinyint(4) DEFAULT 1,
                                  `sending_hour` tinyint(4) DEFAULT NULL,
                                  PRIMARY KEY (`id`),
                                  KEY `template_id` (`template_id`),
                                  KEY `rel_type` (`rel_type`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                        ");

}


if( !$CI->db->field_exists('repeat_every', db_prefix() .'email_template_manage_special') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_special`
                                ADD COLUMN `repeat_every` varchar(15) DEFAULT NULL AFTER `sending_hour` ');

}

if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_special_logs' ) )
{

    $CI->db->query("CREATE TABLE `".db_prefix()."email_template_manage_special_logs` ( 
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `special_id` int(11) DEFAULT NULL,
                                  `send_rel_type` varchar(25) DEFAULT NULL,
                                  `send_rel_id` int(11) DEFAULT 0,
                                  `date` date DEFAULT NULL,
                                  PRIMARY KEY (`id`),
                                  KEY `special_id` (`special_id`),
                                  KEY `send_rel_type` (`send_rel_type`),
                                  KEY `send_rel_id` (`send_rel_id`)
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                        ");

}


if( !$CI->db->field_exists('special_id', db_prefix() .'email_template_manage_mail_logs') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_mail_logs`
                                ADD COLUMN `special_id` int NULL DEFAULT 0 AFTER `webhook_id`; ');

}


/**
 * @Version 1.2.3
 * Imap
 */



if ( !$CI->db->table_exists( db_prefix() . 'email_template_inbox_attachments' ) )
{

    $CI->db->query("
                    CREATE TABLE `".db_prefix()."email_template_inbox_attachments` (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `inbox_id` int(11) DEFAULT NULL,
                                  `file_name` varchar(255) DEFAULT NULL,
                                  `file_type` varchar(255) DEFAULT NULL,
                                  `file_path` varchar(300) DEFAULT NULL,
                                  PRIMARY KEY (`id`),
                                  KEY `inbox_id` (`inbox_id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                ");

}



if ( !$CI->db->table_exists( db_prefix() . 'email_template_imap_settings' ) )
{

    $CI->db->query("CREATE TABLE `".db_prefix()."email_template_imap_settings` (  
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `imap_server` varchar(255) DEFAULT NULL,
                                  `imap_port` int(11) DEFAULT NULL,
                                  `encryption` varchar(50) DEFAULT NULL,
                                  `from_date` date DEFAULT NULL,
                                  `user_name` varchar(255) DEFAULT NULL,
                                  `password` varchar(255) DEFAULT NULL,
                                  `status` tinyint(4) DEFAULT 1,
                                  `company_name` varchar(255) DEFAULT NULL,
                                  `is_public` tinyint(4) DEFAULT NULL,
                                  `active_staff` varchar(255) DEFAULT NULL,
                                  `last_sequence_id` varchar(150) DEFAULT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                        ");

}


if ( !$CI->db->table_exists( db_prefix() . 'email_template_manage_inbox' ) )
{

    $CI->db->query("CREATE TABLE `".db_prefix()."email_template_manage_inbox` (  
                                 `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `from_name` varchar(255) DEFAULT NULL,
                                  `from_email` varchar(255) DEFAULT NULL,
                                  `subject` varchar(500) DEFAULT NULL,
                                  `message` mediumtext DEFAULT NULL,
                                  `unread` tinyint(4) DEFAULT 0,
                                  `is_attachment` tinyint(4) DEFAULT NULL,
                                  `email_size` varchar(20) DEFAULT NULL,
                                  `sequence_id` varchar(100) DEFAULT NULL,
                                  `mail_date` datetime DEFAULT NULL,
                                  `imap_id` int(11) DEFAULT NULL,
                                  `date_received` datetime DEFAULT NULL,
                                  `to` varchar(500) DEFAULT NULL,
                                  `cc` varchar(500) DEFAULT NULL,
                                  `message_id` varchar(100) DEFAULT NULL,
                                  `is_stard` tinyint(4) DEFAULT 0,
                                  `is_trush` tinyint(4) DEFAULT 0,
                                  PRIMARY KEY (`id`),
                                  KEY `imap_id` (`imap_id`),
                                  KEY `is_stard` (`is_stard`),
                                  KEY `sequence_id` (`sequence_id`),
                                  KEY `is_trush` (`is_trush`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                        ");

}


if ( !$CI->db->field_exists('client_tags',db_prefix().'email_template_manage_timer') )
{

    $CI->db->query('ALTER TABLE `'.db_prefix().'email_template_manage_timer` 
                                ADD COLUMN `client_tags` varchar(150) NULL AFTER `not_clients`,
                                ADD COLUMN `lead_tags` varchar(150) NULL AFTER `client_tags`;');

}
