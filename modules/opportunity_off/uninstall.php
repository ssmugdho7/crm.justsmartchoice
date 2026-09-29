<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_comments`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_email`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_items`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_mettings`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_pipelines`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_source`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_stages`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_activity_log`;");
$CI->db->query("DROP TABLE IF EXISTS `" . db_prefix() . "opportunity_calls`;");


