<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI=&get_instance();

$options=[
 'publishx_posts_per_page'=>'10','publishx_blog_title'=>'Smart Choice Construction Blog',
 'publishx_blog_description'=>'Construction guidance, project planning, and trade expertise from Smart Choice Contractors USA.',
 'publishx_show_on_client_side'=>'0','publishx_display_on_post_author'=>'1','publishx_selected_blog_theme'=>'smart_choice_pro',
 'publishx_use_crm_openai_key'=>'1','publishx_openai_key'=>'','publishx_default_language'=>'English',
 'publishx_default_website_id'=>'0','publishx_ga_measurement_id'=>'','publishx_logo_source'=>'website',
 'publishx_favicon_source'=>'website','publishx_enable_view_tracking'=>'1','publishx_module_version'=>'1.1.6','publishx_appointment_url'=>'https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2','publishx_allow_youtube'=>'1','publishx_allow_vimeo'=>'1','publishx_allow_video_upload'=>'1','publishx_default_author_name'=>'Smart Choice Contractors USA','publishx_default_cta_text'=>'Schedule a Free Estimate Now','publishx_primary_color'=>'#F28C28','publishx_secondary_color'=>'#3598DB','publishx_accent_color'=>'#0E6F5B','publishx_success_color'=>'#169179','publishx_card_radius'=>'14','publishx_ai_provider'=>'OpenAI','publishx_ai_model'=>'gpt-4.1-mini','publishx_ai_base_url'=>'https://api.openai.com/v1','publishx_ai_organization'=>'','publishx_ai_global_instructions'=>'Write accurate, useful, conversion-focused construction content for Florida property owners.','publishx_ai_disallowed_topics'=>'Unsupported guarantees, fabricated reviews, invented project claims.','publishx_ai_required_facts'=>'Use Smart Choice Contractors USA branding and preserve factual accuracy.','publishx_ai_knowledge_folder'=>'','publishx_default_target_folder'=>'services/engineering'
];
foreach($options as $k=>$v){ add_option($k,$v); }

$charset=$CI->db->char_set ?: 'utf8';
$collate=$CI->db->dbcollat ?: 'utf8_general_ci';
$queries=[];
$queries['publishx_categories']="CREATE TABLE `".db_prefix()."publishx_categories` (`id` INT NOT NULL AUTO_INCREMENT,`category_name` VARCHAR(191) NOT NULL,`slug` VARCHAR(191) NULL,`created_at` DATETIME NULL,PRIMARY KEY (`id`),UNIQUE KEY `uq_publishx_category_slug` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}";
$queries['publishx_languages']="CREATE TABLE `".db_prefix()."publishx_languages` (`id` INT NOT NULL AUTO_INCREMENT,`name` VARCHAR(50) NOT NULL,`code` VARCHAR(10) NOT NULL,`created_at` DATETIME NULL,PRIMARY KEY (`id`),UNIQUE KEY `uq_publishx_language_code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}";
$queries['publishx_websites']="CREATE TABLE `".db_prefix()."publishx_websites` (`id` INT NOT NULL AUTO_INCREMENT,`name` VARCHAR(191) NOT NULL,`base_url` VARCHAR(255) NOT NULL,`document_root` VARCHAR(500) NULL,`default_folder` VARCHAR(255) NULL,`header_include` VARCHAR(500) NULL,`footer_include` VARCHAR(500) NULL,`logo_url` VARCHAR(500) NULL,`favicon_url` VARCHAR(500) NULL,`ga_measurement_id` VARCHAR(32) NULL,`publish_method` VARCHAR(30) NOT NULL DEFAULT 'local',`webhook_url` VARCHAR(500) NULL,`webhook_secret` VARCHAR(255) NULL,`is_default` TINYINT(1) NOT NULL DEFAULT 0,`active` TINYINT(1) NOT NULL DEFAULT 1,`created_at` DATETIME NULL,`updated_at` DATETIME NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}";
$queries['publishx_posts']="CREATE TABLE `".db_prefix()."publishx_posts` (`id` INT NOT NULL AUTO_INCREMENT,`category_id` INT NULL,`author_id` INT NULL,`website_id` INT NULL,`post_title` VARCHAR(255) NOT NULL,`post_slug` VARCHAR(255) NOT NULL,`short_content` TEXT NULL,`full_content` LONGTEXT NULL,`meta_title` VARCHAR(255) NULL,`meta_description` TEXT NULL,`meta_keywords` TEXT NULL,`featured_image` VARCHAR(500) NULL,`hero_video_url` VARCHAR(500) NULL,`media_gallery` LONGTEXT NULL,`template_key` VARCHAR(100) NOT NULL DEFAULT 'smart_choice_pro',`language_id` INT NULL,`post_parent_id` INT NULL,`views` INT NOT NULL DEFAULT 0,`status` TINYINT NOT NULL DEFAULT 1,`scheduled` DATETIME NULL,`target_folder` VARCHAR(255) NULL,`published_url` VARCHAR(500) NULL,`published_file` VARCHAR(500) NULL,`last_published_at` DATETIME NULL,`created_at` DATETIME NULL,`updated_at` DATETIME NULL,PRIMARY KEY (`id`),KEY `idx_publishx_status` (`status`),KEY `idx_publishx_website` (`website_id`),KEY `idx_publishx_slug` (`post_slug`)) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}";
$queries['publishx_views']="CREATE TABLE `".db_prefix()."publishx_views` (`id` BIGINT NOT NULL AUTO_INCREMENT,`post_id` INT NOT NULL,`viewed_at` DATETIME NOT NULL,`ip_hash` CHAR(64) NULL,`referrer` VARCHAR(500) NULL,`source` VARCHAR(191) NULL,`medium` VARCHAR(191) NULL,`country` VARCHAR(100) NULL,`device` VARCHAR(100) NULL,PRIMARY KEY (`id`),KEY `idx_publishx_view_post_date` (`post_id`,`viewed_at`)) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate}";
foreach($queries as $table=>$sql){ if(!$CI->db->table_exists(db_prefix().$table)) $CI->db->query($sql); }

// Add missing columns safely for upgrades.
$fields=[
 'publishx_categories'=>['slug'=>"VARCHAR(191) NULL AFTER `category_name`"],
 'publishx_languages'=>['code'=>"VARCHAR(10) NOT NULL DEFAULT 'en' AFTER `name`"],
 'publishx_posts'=>[
  'website_id'=>'INT NULL AFTER `author_id`','featured_image_url'=>'VARCHAR(500) NULL AFTER `featured_image`','hero_video_url'=>'VARCHAR(500) NULL AFTER `featured_image`','media_gallery'=>'LONGTEXT NULL AFTER `hero_video_url`',
  'template_key'=>"VARCHAR(100) NOT NULL DEFAULT 'smart_choice_pro' AFTER `media_gallery`",'target_folder'=>'VARCHAR(255) NULL AFTER `scheduled`',
  'published_url'=>'VARCHAR(500) NULL AFTER `target_folder`','published_file'=>'VARCHAR(500) NULL AFTER `published_url`','last_published_at'=>'DATETIME NULL AFTER `published_file`'
 ]
];
foreach($fields as $table=>$cols){ foreach($cols as $col=>$definition){ if(!$CI->db->field_exists($col,db_prefix().$table)) $CI->db->query('ALTER TABLE `'.db_prefix().$table.'` ADD `'.$col.'` '.$definition); }}

$langs=[['English','en'],['Spanish','es']];
foreach($langs as $l){ if(!$CI->db->where('code',$l[1])->count_all_results(db_prefix().'publishx_languages')) $CI->db->insert(db_prefix().'publishx_languages',['name'=>$l[0],'code'=>$l[1],'created_at'=>date('Y-m-d H:i:s')]); }
$cats=['Plumbing','Roofing','Electrical','Kitchen Remodeling','Bathroom Remodeling','HVAC','Engineering & Drawings','New Construction','Painting','Flooring'];
foreach($cats as $c){$slug=slug_it($c); if(!$CI->db->where('slug',$slug)->count_all_results(db_prefix().'publishx_categories')) $CI->db->insert(db_prefix().'publishx_categories',['category_name'=>$c,'slug'=>$slug,'created_at'=>date('Y-m-d H:i:s')]);}
// Correct the existing JustSmartChoice.com publishing profile during upgrade.
$websiteDefaults = [
    'name'            => 'Just Smart Choice',
    'base_url'        => 'https://justsmartchoice.com',
    'document_root'   => '/home2/scusawco/public_html/justsmartchoice.com',
    'default_folder'  => 'services/engineering',
    'header_include'  => '/home2/scusawco/public_html/justsmartchoice.com/sections-library/header.php',
    'footer_include'  => '/home2/scusawco/public_html/justsmartchoice.com/sections-library/footer.php',
    'publish_method'  => 'local',
    'is_default'      => 1,
    'active'          => 1,
    'updated_at'      => date('Y-m-d H:i:s'),
];

$existingWebsite = $CI->db
    ->group_start()
    ->where('base_url', 'https://justsmartchoice.com')
    ->or_where('base_url', 'https://www.justsmartchoice.com')
    ->group_end()
    ->get(db_prefix() . 'publishx_websites')
    ->row();

if ($existingWebsite) {
    $CI->db->where('id', $existingWebsite->id)->update(db_prefix() . 'publishx_websites', $websiteDefaults);
} else {
    $websiteDefaults['created_at'] = date('Y-m-d H:i:s');
    $CI->db->insert(db_prefix() . 'publishx_websites', $websiteDefaults);
}

update_option('publishx_module_version','1.1.6');
