<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{

    public function up()
    {

        $CI = &get_instance();


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
                                  KEY `sequence_id` (`sequence_id`),
                                  KEY `is_stard` (`is_stard`),
                                  KEY `is_trush` (`is_trush`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                        ");

        }


    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }


}
