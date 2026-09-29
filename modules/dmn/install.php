<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
add_option('staff_members_create_inline_dmn_group', 1);
if (!$CI->db->table_exists(db_prefix() . 'dmn')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "dmn` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) DEFAULT NULL,
    `description` text,
    `staffid` int(11) DEFAULT '0' ,
    `project_id` int(11) DEFAULT '0' ,
    `dmn_content` text,
    `hash` TEXT NULL DEFAULT NULL,
    `dmnxml` TEXT NULL DEFAULT NULL,
    `dateadded` datetime DEFAULT NULL,
    `dateaupdated` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY (staffid)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'dmn_votes')) {
  $CI->db->query('CREATE TABLE `' . db_prefix() . "dmn_votes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `dmn_id` int(11) NOT NULL,
    `thumb` ENUM('like','dislike') DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `user_type` varchar(100) DEFAULT NULL,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
?>