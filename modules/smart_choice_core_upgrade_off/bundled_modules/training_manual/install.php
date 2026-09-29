<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'wiki_books')) {
    $CI->db->query("
    CREATE TABLE " . db_prefix() . "wiki_books (
    id                    INT(11) NOT NULL,
    name                  VARCHAR(191) DEFAULT NULL,
    short_description     TEXT DEFAULT NULL,
    assign_type           VARCHAR(20) DEFAULT 'specific_staff',
    assign_ids            MEDIUMTEXT,
    author_id             INT(11) NOT NULL DEFAULT '0',
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
  ");
    
    $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_books
    ADD PRIMARY KEY (id);
  ");
    
    $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_books
    MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1
  ");
}

if (!$CI->db->table_exists(db_prefix() . 'wiki_articles')) {
    $CI->db->query("
    CREATE TABLE " . db_prefix() . "wiki_articles (
    id                    INT(11) NOT NULL,
    title                 VARCHAR(191) DEFAULT NULL,
    description           TEXT DEFAULT NULL,
    content               LONGTEXT DEFAULT NULL,
    is_bookmark           TINYINT(3) DEFAULT 0,
    view_counter          INT(11) NOT NULL DEFAULT 0,
    author_id             INT(11) DEFAULT NULL,
    book_id               INT(11) DEFAULT NULL,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
  ");
    
    $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_articles
    ADD PRIMARY KEY (id);
  ");
    
    $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_articles
    MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1
  ");
}

// version 1.0.2
$tblwiki_articles = db_prefix() . 'wiki_articles';

if (!$CI->db->field_exists('is_publish', $tblwiki_articles)) {
    $CI->db->query("
        ALTER TABLE " . $tblwiki_articles . "
          ADD is_publish TINYINT(1) NOT NULL DEFAULT '0';
      ");
}

if (!$CI->db->field_exists('slug', $tblwiki_articles)) {
    $CI->db->query("
        ALTER TABLE " . $tblwiki_articles . "
          ADD slug VARCHAR(191) DEFAULT NULL;
      ");
    
    $CI->db->query("
        UPDATE " . $tblwiki_articles . " TBLArticles
        SET
          TBLArticles.slug = UUID()
        WHERE TBLArticles.slug IS NULL
      ");
}

$tblwiki_staff_article = db_prefix() . 'wiki_staff_article';

if (!$CI->db->table_exists($tblwiki_staff_article)) {
    $CI->db->query("
        CREATE TABLE " . $tblwiki_staff_article . " (
          id                    INT(11) NOT NULL,
          staff_id              INT(11) NOT NULL DEFAULT '0',
          article_id            INT(11) NOT NULL DEFAULT '0',
          updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
          created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";
        ");
    
    $CI->db->query("
          ALTER TABLE " . $tblwiki_staff_article . "
          ADD PRIMARY KEY (id);
        ");
    
    $CI->db->query("
          ALTER TABLE " . $tblwiki_staff_article . "
            MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1
        ");
}

// version 1.0.3
if(!$CI->db->field_exists('type', db_prefix() . "wiki_articles")){
  $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_articles" . "
      ADD type VARCHAR(191) DEFAULT NULL;
  ");
}

if(!$CI->db->field_exists('mindmap_content', db_prefix() . "wiki_articles")){
  $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_articles" . "
      ADD mindmap_content TEXT DEFAULT NULL;
  ");
}

if(!$CI->db->field_exists('mindmap_thumb', db_prefix() . "wiki_articles")){
  $CI->db->query("
    ALTER TABLE " . db_prefix() . "wiki_articles" . "
      ADD mindmap_thumb VARCHAR(191) DEFAULT NULL;
  ");
}

// Training Manual v1.1.3 compatibility upgrades using original data tables.
$tbl_articles = db_prefix() . 'wiki_articles';
$tbl_books = db_prefix() . 'wiki_books';
$tbl_staff_article = db_prefix() . 'wiki_staff_article';

if ($CI->db->table_exists($tbl_articles)) {
    $fields = [
        'created_by' => "INT(11) NULL DEFAULT NULL AFTER `author_id`",
        'updated_by' => "INT(11) NULL DEFAULT NULL AFTER `created_by`",
        'thumbnail' => "VARCHAR(255) NULL DEFAULT NULL AFTER `description`",
        'short_code' => "VARCHAR(64) NULL DEFAULT NULL AFTER `slug`",
        'visibility' => "VARCHAR(30) NOT NULL DEFAULT 'manual_permissions' AFTER `is_publish`"
    ];
    foreach ($fields as $field => $definition) {
        if (!$CI->db->field_exists($field, $tbl_articles)) {
            $CI->db->query("ALTER TABLE `{$tbl_articles}` ADD `{$field}` {$definition}");
        }
    }
    $CI->db->query("UPDATE `{$tbl_articles}` SET created_by = author_id WHERE created_by IS NULL AND author_id IS NOT NULL");
    $CI->db->query("UPDATE `{$tbl_articles}` SET short_code = id WHERE (short_code IS NULL OR short_code = '')");
}

if ($CI->db->table_exists($tbl_books)) {
    $fields = [
        'created_by' => "INT(11) NULL DEFAULT NULL AFTER `author_id`",
        'updated_by' => "INT(11) NULL DEFAULT NULL AFTER `created_by`"
    ];
    foreach ($fields as $field => $definition) {
        if (!$CI->db->field_exists($field, $tbl_books)) {
            $CI->db->query("ALTER TABLE `{$tbl_books}` ADD `{$field}` {$definition}");
        }
    }
    $CI->db->query("UPDATE `{$tbl_books}` SET created_by = author_id WHERE created_by IS NULL AND author_id IS NOT NULL");
}

if ($CI->db->table_exists($tbl_staff_article)) {
    if (!$CI->db->field_exists('created_by', $tbl_staff_article)) {
        $CI->db->query("ALTER TABLE `{$tbl_staff_article}` ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `article_id`");
    }
    if (!$CI->db->field_exists('bookmark_note', $tbl_staff_article)) {
        $CI->db->query("ALTER TABLE `{$tbl_staff_article}` ADD `bookmark_note` VARCHAR(255) NULL DEFAULT NULL AFTER `created_by`");
    }
}

add_option('training_manual_enable_public_links', '1');
add_option('training_manual_thumbnail_style', 'rounded_square');
add_option('training_manual_database_mode', 'legacy_compatible');
$manual_upload_path = FCPATH . 'uploads/training_manual';
if (!is_dir($manual_upload_path)) { @mkdir($manual_upload_path, 0755, true); }


// Training Manual v1.1.5 Smart Choice article controls and style support.
$tbl_articles = db_prefix() . 'wiki_articles';
if ($CI->db->table_exists($tbl_articles)) {
    $fields = [
        'language_code' => "VARCHAR(20) NULL DEFAULT 'en' AFTER `visibility`",
        'style_preset' => "VARCHAR(50) NULL DEFAULT 'smart_choice' AFTER `language_code`",
        'last_creator_change_by' => "INT(11) NULL DEFAULT NULL AFTER `updated_by`",
        'last_creator_change_at' => "DATETIME NULL DEFAULT NULL AFTER `last_creator_change_by`",
        'audience' => "VARCHAR(30) NOT NULL DEFAULT 'internal' AFTER `style_preset`"
    ];
    foreach ($fields as $field => $definition) {
        if (!$CI->db->field_exists($field, $tbl_articles)) {
            $CI->db->query("ALTER TABLE `{$tbl_articles}` ADD `{$field}` {$definition}");
        }
    }
}
add_option('training_manual_default_language', 'en');
add_option('training_manual_default_style', 'smart_choice');
add_option('training_manual_external_css_enabled', '1');


    $extra_article_customer_video_fields = [
        'content_kind' => "VARCHAR(30) NOT NULL DEFAULT 'article' AFTER `audience`"
    ];
    foreach ($extra_article_customer_video_fields as $field => $definition) {
        if ($CI->db->table_exists($tbl_articles) && !$CI->db->field_exists($field, $tbl_articles)) {
            $CI->db->query("ALTER TABLE `{$tbl_articles}` ADD `{$field}` {$definition}");
        }
    }
