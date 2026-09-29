<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI=&get_instance();
$defaults=[
 'sc_core_manager_version'=>'1.0.0','sc_company_ein'=>'','sc_crm_portal_url'=>site_url(),
 'sc_company_website'=>'https://justsmartchoice.com','sc_enabled_languages'=>'english,spanish',
 'sc_pdf_client_language'=>'1','sc_currency_prefix'=>'$','sc_currency_suffix'=>' USD',
 'sc_show_appointment_link'=>'0','sc_appointment_url'=>'','sc_nav_logo_height_percent'=>'95',
 'sc_menu_background_color'=>'#0f3f5f','sc_menu_text_color'=>'#ffffff',
 'sc_login_background_mode'=>'cover','sc_client_portal_enabled'=>'1','sc_environment_mode'=>'production',
 'sc_require_module_manifest'=>'1','sc_last_cron_test'=>'','sc_last_speed_test'=>'',
 'sc_discount_types'=>'Veteran Discount,Senior Citizen Discount,Accessibility Discount,Promotional Discount,Other',
 'sc_allow_html_document_terms'=>'1','sc_task_editor_templates'=>'1','sc_signature_initials_enabled'=>'1',
 'sc_core_build_version'=>'SC-3.5.4-1.0.0'
];
foreach($defaults as $k=>$v){ if(get_option($k)==='') add_option($k,$v); }
if(!$CI->db->table_exists(db_prefix().'sc_core_audit')){
 $CI->db->query('CREATE TABLE `'.db_prefix().'sc_core_audit` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`staff_id` INT UNSIGNED NULL,`action` VARCHAR(191) NOT NULL,`details` LONGTEXT NULL,`created_at` DATETIME NOT NULL,PRIMARY KEY (`id`),KEY `staff_id` (`staff_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}
