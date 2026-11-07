<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Development
Description: Internal CRM repair and development tracker with media, assignments, signed work notes, next steps and completion audit.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice CRM
*/
define('DEVELOPMENT_MODULE_NAME','development');
register_activation_hook(DEVELOPMENT_MODULE_NAME,'development_activate');
function development_activate(){ require_once __DIR__.'/install.php'; }
register_language_files(DEVELOPMENT_MODULE_NAME,[DEVELOPMENT_MODULE_NAME]);
register_staff_capabilities('development',['capabilities'=>[
 'view'=>'View','create'=>'Create','edit'=>'Edit','assign'=>'Assign','notes'=>'Add Notes','complete'=>'Complete / Reopen','delete'=>'Delete'
]],'Development');
hooks()->add_action('admin_init','development_admin_init');
function development_admin_init(){
 development_ensure_schema(); $CI=&get_instance();
 if(is_admin() || staff_can('view','development')) $CI->app_menu->add_sidebar_menu_item('development',['name'=>_l('dev_development'),'href'=>admin_url('development'),'icon'=>'fa-solid fa-code','position'=>36]);
}
function development_ensure_schema(){
 $CI=&get_instance(); $p=db_prefix(); $c=$CI->db->char_set; $co=$CI->db->dbcollat;
 $CI->db->query("CREATE TABLE IF NOT EXISTS `{$p}development_items` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`title` VARCHAR(191) NOT NULL,`description` LONGTEXT NULL,`reference_url` TEXT NULL,`status` VARCHAR(30) NOT NULL DEFAULT 'open',`estimated_hours` DECIMAL(8,2) NULL,`created_by` INT UNSIGNED NOT NULL,`created_at` DATETIME NOT NULL,`updated_at` DATETIME NOT NULL,`completed_by` INT UNSIGNED NULL,`completed_at` DATETIME NULL,PRIMARY KEY (`id`),KEY `status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET={$c} COLLATE={$co}");
 $CI->db->query("CREATE TABLE IF NOT EXISTS `{$p}development_assignees` (`item_id` INT UNSIGNED NOT NULL,`staff_id` INT UNSIGNED NOT NULL,`assigned_by` INT UNSIGNED NOT NULL,`assigned_at` DATETIME NOT NULL,PRIMARY KEY (`item_id`,`staff_id`),KEY `staff_id` (`staff_id`)) ENGINE=InnoDB DEFAULT CHARSET={$c} COLLATE={$co}");
 $CI->db->query("CREATE TABLE IF NOT EXISTS `{$p}development_notes` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,`item_id` INT UNSIGNED NOT NULL,`staff_id` INT UNSIGNED NOT NULL,`note` LONGTEXT NOT NULL,`next_step` LONGTEXT NULL,`created_at` DATETIME NOT NULL,PRIMARY KEY (`id`),KEY `item_id` (`item_id`,`id`)) ENGINE=InnoDB DEFAULT CHARSET={$c} COLLATE={$co}");
 $CI->db->query("CREATE TABLE IF NOT EXISTS `{$p}development_attachments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,`item_id` INT UNSIGNED NOT NULL,`note_id` BIGINT UNSIGNED NULL,`uploaded_by` INT UNSIGNED NOT NULL,`original_name` VARCHAR(255) NOT NULL,`stored_name` VARCHAR(255) NOT NULL,`mime_type` VARCHAR(120) NULL,`file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,`created_at` DATETIME NOT NULL,PRIMARY KEY (`id`),KEY `item_id` (`item_id`)) ENGINE=InnoDB DEFAULT CHARSET={$c} COLLATE={$co}");
}
