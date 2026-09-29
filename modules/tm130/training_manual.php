<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Training Manual
Description: Quality ★★★★★.  Training Manual is the Smart Choice Contractors USA staff knowledge base, Wiki Books, and training article system for Perfex CRM. Quality: ★★★★★. It includes stylish article cards, manuals, Customer Books for the customer portal, public links, creator control, bookmarks, clone tools, export, import, refresh, mass delete, pipeline view, language/style presets, external CSS training manual support, undo/redo editor tools, Health Check, submenu icons, and PHP 8.5 safe upgrades, inline editor-safe manual styling, compact manual cards, and improved rendered article design.
Version: 1.3.0
Requires at least: 3.4.1
Author URI: https://justsmartchoice.com/webdeveloper.php
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('TRAINING_MANUAL_MODULE_NAME', 'training_manual');
define('TRAINING_MANUAL_ASSETS_PATH', 'modules/training_manual/assets');

$CI = &get_instance();

hooks()->add_action('admin_init', 'training_manual_module_menu_admin_items');
hooks()->add_action('admin_init', 'training_manual_permissions');
hooks()->add_action('admin_init', 'training_manual_setup_menu_items');
hooks()->add_action('clients_init', 'training_manual_client_menu_items');

/**
* Load the module helper
*/
$CI->load->helper(TRAINING_MANUAL_MODULE_NAME . '/training_manual');

function training_manual_module_menu_admin_items()
{
  $CI = &get_instance();

  $has_permission_books = true;
  $has_permission_articles = true;
  
  if($has_permission_books || $has_permission_articles){

    $CI->app_menu->add_sidebar_menu_item('training-manual-main-menu', [
        'name'     => _l('training_manual'),
        'href'     => 'javascript:void(0);',
        'position' => 2,
        'icon'     => 'fa fa-book',
    ]);

  }
  
  if($has_permission_books){
    $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => _l('training_manuals'),
      'href'     => admin_url('training_manual/books'),
      'position' => 1,
      'slug'     => 'training-manual-books',
      'icon'     => 'fa fa-book',
    ]);
  }

  if($has_permission_articles){

    $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => _l('articles'),
      'href'     => admin_url('training_manual/articles'),
      'position' => 2,
      'slug'     => 'training-manual-articles',
      'icon'     => 'fa fa-newspaper-o',
    ]);

    $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => _l('created_by_me'),
      'href'     => admin_url('training_manual/articles?filter_is_owner=1'),
      'position' => 3,
      'slug'     => 'training-manual-created-by-me',
      'icon'     => 'fa fa-user',
    ]);



    if(has_permission('training_manual_books','','view') || has_permission('training_manual_books','','view_own') || is_admin()){
      $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => _l('training_manual_customer_books'),
      'href'     => admin_url('training_manual/books/customer'),
      'position' => 4,
      'slug'     => 'training-manual-customer-books',
      'icon'     => 'fa fa-book',
    ]);
    }

    $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => 'Health Check',
      'href'     => admin_url('training_manual/settings/health'),
      'position' => 6,
      'slug'     => 'training-manual-health',
      'icon'     => 'fa fa-heartbeat',
    ]);

    $CI->app_menu->add_sidebar_children_item('training-manual-main-menu', [
      'name'     => _l('bookmarks'),
      'href'     => admin_url('training_manual/articles?filter_is_bookmark=1'),
      'position' => 5,
      'slug'     => 'training-manual-bookmarks',
      'icon'     => 'fa fa-bookmark',
    ]);
  }
  
}


function training_manual_client_menu_items()
{
    if (!is_client_logged_in()) {
        return;
    }

    if (function_exists('add_theme_menu_item')) {
        add_theme_menu_item('training-manual-customer-books', [
            'name'     => _l('training_manual_training_library'),
            'href'     => site_url('training_manual/customer-books'),
            'position' => 35,
            'icon'     => 'fa fa-book',
        ]);
    }
}

function training_manual_setup_menu_items()
{
    $CI = &get_instance();
    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('training-manual-settings', [
            'name'     => 'Training Manual Settings',
            'icon'     => 'fa fa-cogs',
            'href'     => admin_url('training_manual/settings'),
            'position' => 62,
        ]);
    }
}

function training_manual_permissions()
{
    $standard_capabilities = [
        'view_own'           => 'View Own',
        'view'               => 'View(Permission Global)',
        'create'             => 'Create',
        'edit'               => 'Edit',
        'delete'             => 'Delete',
        'view_all_templates' => 'View All Templates',
    ];

    register_staff_capabilities('training_manual_books', [
        'capabilities' => $standard_capabilities,
        'help' => [
            'view'     => _l('help_training_manual_book_permissions'),
            'view_own' => _l('permission_training_manual_book_based_on_assignee'),
        ],
    ], _l('training_manuals'));

    register_staff_capabilities('training_manual_articles', [
        'capabilities' => $standard_capabilities,
        'help' => [
            'view'     => _l('help_training_manual_articles_permissions'),
            'view_own' => _l('permission_training_manual_articles_based_on_assignee'),
        ],
    ], _l('training_articles'));
}

/**
* Register activation module hook
*/
register_activation_hook(TRAINING_MANUAL_MODULE_NAME, 'training_manual_module_activation_hook');

function training_manual_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(TRAINING_MANUAL_MODULE_NAME, [TRAINING_MANUAL_MODULE_NAME]);


module_libs_path(TRAINING_MANUAL_MODULE_NAME, 'training_manual_serialize');
function training_manual_serialize($arr, $prefix){
  $s = '';
  foreach ($arr as $value) {
    if(isset($value) && $value != ''){
      $s .= ',' . $prefix . $value;
    }
  }
  return trim($s, " ,");
}

module_libs_path(TRAINING_MANUAL_MODULE_NAME, 'training_manual_unserialize');
function training_manual_unserialize($s, $prefix){
  $arr = [];
  $s = str_replace($prefix, '', $s);
  $splitArr = explode(',', $s);
  return $splitArr;
}

if (!function_exists('training_manual_handle_thumb_mindmap_upload')) {
  function training_manual_handle_thumb_mindmap_upload($content, $prefix_name, $file_old = '')
  {
      if (isset($content) && $content != '') {
          $path  = FCPATH.TRAINING_MANUAL_ASSETS_PATH . "/storage/mindmap/";

          $filename    = $prefix_name . time(). '.png';
          $new_file_path = $path . $filename;

          _maybe_create_upload_path($path);

          $decoded = base64_decode($content);
          file_put_contents($new_file_path, $decoded);

          if ($file_old) {
              $path_old = $path . $file_old;
              if (file_exists($path_old)) {
                  unlink($path_old);
              }
          }

          return $filename;
      }

      return false;
  }
}

if (!function_exists('training_manual_copy_thumb_mindmap')) {
  function training_manual_copy_thumb_mindmap($old_image, $prefix_name)
  {
      if (isset($old_image) && $old_image != '') {
          $path  = FCPATH . TRAINING_MANUAL_ASSETS_PATH . "/storage/mindmap/";

          $filename    = $prefix_name . time(). '.png';
          $new_file_path = $path . $filename;

          $old_file_path = $path . $old_image;

          _maybe_create_upload_path($path);

          if(file_exists($old_file_path)){
            copy($old_file_path, $new_file_path);

            return $filename;
          }
      }

      return false;
  }
}

if (!function_exists('training_manual_remove_thumb_mindmap')) {
  function training_manual_remove_thumb_mindmap($old_image)
  {
      if (isset($old_image) && $old_image != '') {
          $path  = FCPATH . TRAINING_MANUAL_ASSETS_PATH . "/storage/mindmap/";

          $old_file_path = $path . $old_image;

          unlink($old_file_path);
      }

      return false;
  }
}

if (!function_exists('training_manual_copy_default_mindmap_thumb')) {
  function training_manual_copy_default_mindmap_thumb($prefix_name)
  {
    $path  = FCPATH . TRAINING_MANUAL_ASSETS_PATH . "/storage/mindmap/";

    $new_file_name    = $prefix_name . time(). '.png';
    $new_file_path = $path . $new_file_name;

    $default_file_path = FCPATH . TRAINING_MANUAL_ASSETS_PATH . "/builder/ui/default_thumb.png";

    _maybe_create_upload_path($path);

    if(file_exists($default_file_path)){
      copy($default_file_path, $new_file_path);

      return $new_file_name;
    }

    return false;
  }
}